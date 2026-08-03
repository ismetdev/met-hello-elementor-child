<?php
/**
 * Maintenance mode, the styled 403, and the shared standalone page renderer.
 *
 * These pages run where the enqueued stylesheet is not available, so they inline
 * their own CSS and render no header or footer.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether theme maintenance mode is active.
 *
 * Enabled by `define( 'MET_HELLO_CHILD_MAINTENANCE', true )` in wp-config.php or
 * by returning true from the `met_hello_child_maintenance` filter.
 *
 * @return bool
 */
function met_hello_child_maintenance_active() {
	$active = defined( 'MET_HELLO_CHILD_MAINTENANCE' ) && MET_HELLO_CHILD_MAINTENANCE;

	/**
	 * Filter whether the styled maintenance page is served.
	 *
	 * @param bool $active Current state.
	 */
	return (bool) apply_filters( 'met_hello_child_maintenance', $active );
}

/**
 * Serve the styled maintenance page to visitors while admins keep browsing.
 *
 * Runs on template_redirect (front-end only; wp-login.php and wp-admin are not
 * affected, so admins can always log in). Sends a real 503 + no-cache headers so
 * litespeed-cache does not cache the maintenance response.
 */
function met_hello_child_maybe_maintenance() {
	if ( ! met_hello_child_maintenance_active() ) {
		return;
	}

	// Never intercept admin, cron, or CLI; let admins through on the front end.
	if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return;
	}

	nocache_headers();
	header( 'Retry-After: 3600' );
	status_header( 503 );

	$template = MET_HELLO_CHILD_DIR . 'template-parts/maintenance-page.php';
	if ( file_exists( $template ) ) {
		require $template;
	}
	exit;
}
add_action( 'template_redirect', 'met_hello_child_maybe_maintenance' );

/**
 * Route HTML wp_die() calls through our handler so front-end 403s are styled.
 *
 * The `wp_die_handler` filter is only used for the HTML death path. AJAX, JSON,
 * REST, and XML-RPC use their own handlers, so wp-admin AJAX and the REST API are
 * unaffected. Non-403 deaths fall back to the WordPress default handler.
 *
 * @param callable $handler Current handler. Unused: the filter contract requires
 *                          accepting it, but this replacement handler does not
 *                          need to fall back to it.
 * @return callable
 */
function met_hello_child_set_wp_die_handler( $handler ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'met_hello_child_wp_die_handler';
}
add_filter( 'wp_die_handler', 'met_hello_child_set_wp_die_handler' );

/**
 * Styled wp_die() handler: renders our 403 page for front-end 403s only.
 *
 * @param string|WP_Error $message Death message.
 * @param string          $title   Death title.
 * @param array           $args    Death args (may include 'response').
 * @return mixed The default handler's return value, when a 403 is not being
 *               intercepted. The 403 branch exits directly.
 */
function met_hello_child_wp_die_handler( $message, $title = '', $args = array() ) {
	$parsed = wp_parse_args( $args );
	$status = isset( $parsed['response'] ) ? (int) $parsed['response'] : 0;

	if ( 403 === $status && ! is_admin() ) {
		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}
		if ( ! headers_sent() ) {
			nocache_headers();
			status_header( 403 );
		}
		met_hello_child_render_standalone(
			array(
				'code'    => '403',
				'title'   => __( 'Access Denied', 'met-hello-child' ),
				'heading' => __( 'Access Denied', 'met-hello-child' ),
				'message' => $message ? $message : __( 'You don\'t have permission to access this page.', 'met-hello-child' ),
			)
		);
		exit;
	}

	// Anything else: use WordPress's default HTML death handler.
	return call_user_func( '_default_wp_die_handler', $message, $title, $args );
}

/**
 * Render a self-contained, message-only full-screen page (maintenance / 403).
 *
 * Used by template-parts/maintenance-page.php and the styled 403 handler. Runs
 * with WordPress loaded, but inlines all CSS because the enqueued stylesheet is
 * not present in these contexts. No header or footer, by design.
 *
 * @param array $args {
 *     Page content. All keys are optional.
 *
 *     @type string $code      Big status code label (e.g. "403"), optional.
 *     @type string $title     <title> text.
 *     @type string $heading   Main heading.
 *     @type string $message   Body message (plain text).
 *     @type bool   $show_home Whether to show the "Back to homepage" button.
 * }
 * @return void
 */
function met_hello_child_render_standalone( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'code'      => '',
			'title'     => get_bloginfo( 'name' ),
			'heading'   => '',
			'message'   => '',
			'show_home' => true,
		)
	);

	$site_name = get_bloginfo( 'name' );
	$home      = home_url( '/' );
	$pattern   = "url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"60\" height=\"104\" viewBox=\"0 0 60 104\"><g fill=\"none\" stroke=\"%23C99A3A\" stroke-width=\"1\" opacity=\"0.16\"><path d=\"M30 2 L52 15 L52 41 L30 54 L8 41 L8 15 Z\"/><path d=\"M30 50 L52 63 L52 89 L30 102 L8 89 L8 63 Z\"/><path d=\"M0 28 L8 15 M52 15 L60 28 M0 76 L8 63 M52 63 L60 76\"/></g></svg>')";
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $args['title'] . ' - ' . $site_name ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- runs where the enqueue system is not available (see D7 in DOCS/DECISIONS.md). ?>
	<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
	<style>
		*,*::before,*::after{box-sizing:border-box;}
		html,body{height:100%;margin:0;}
		body{font-family:"Geist",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#0E3B40;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:32px;position:relative;overflow:hidden;-webkit-font-smoothing:antialiased;}
		body::before{content:"";position:absolute;inset:0;background:<?php echo $pattern; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> center/60px 104px;pointer-events:none;}
		.wrap{position:relative;max-width:560px;text-align:center;}
		.brand{font-size:19px;font-weight:700;letter-spacing:0.02em;color:#C99A3A;margin-bottom:40px;}
		.code{font-family:"Instrument Serif",Georgia,serif;font-size:clamp(64px,16vw,120px);line-height:1;color:#C99A3A;margin-bottom:8px;}
		h1{font-size:clamp(24px,4vw,34px);font-weight:700;letter-spacing:-0.02em;margin:0 0 16px;}
		p{font-size:16px;line-height:1.7;color:rgba(255,255,255,0.75);margin:0 auto 32px;max-width:44ch;}
		.btn{display:inline-flex;align-items:center;gap:10px;height:48px;padding:0 26px;border-radius:10px;background:#C99A3A;color:#0E3B40;font-weight:600;font-size:15px;text-decoration:none;transition:transform .18s cubic-bezier(.2,.8,.2,1),background .18s;}
		.btn:hover{background:#B98A2E;transform:translateY(-2px);}
	</style>
</head>
<body>
	<div class="wrap">
		<div class="brand"><?php echo esc_html( $site_name ); ?></div>
		<?php if ( $args['code'] ) : ?>
			<div class="code"><?php echo esc_html( $args['code'] ); ?></div>
		<?php endif; ?>
		<?php if ( $args['heading'] ) : ?>
			<h1><?php echo esc_html( $args['heading'] ); ?></h1>
		<?php endif; ?>
		<?php if ( $args['message'] ) : ?>
			<p><?php echo esc_html( $args['message'] ); ?></p>
		<?php endif; ?>
		<?php if ( $args['show_home'] ) : ?>
			<a class="btn" href="<?php echo esc_url( $home ); ?>"><?php echo esc_html__( 'Back to homepage', 'met-hello-child' ); ?></a>
		<?php endif; ?>
	</div>
</body>
</html>
	<?php
}
