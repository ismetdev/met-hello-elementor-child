<?php
/**
 * Met Hello Elementor Child — theme functions.
 *
 * Scope: native WordPress blog Posts (single.php) and, from Phase 3, their
 * category/tag/date archives (archive.php). Everything here is namespaced with
 * the met_hello_child_ / MET_HELLO_CHILD_ prefix and never touches the parent
 * theme, Elementor pages, or the Haraka plugin.
 *
 * @package MetHelloElementorChild
 */

// No direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version. Bump this together with the header in style.css so browsers
 * and CDNs pick up fresh assets (used for cache-busting in the enqueues below).
 */
define( 'MET_HELLO_CHILD_VERSION', '1.4.2' );

/**
 * Auto-updater. Checks GitHub for newer releases and shows the update on the
 * Appearance > Themes and Dashboard > Updates screens, so the theme updates like
 * any other. Uses the bundled Plugin Update Checker library (YahnisElsts, v5) in
 * theme mode — the same approach as the MetTranslate plugin.
 */
require_once get_stylesheet_directory() . '/libs/plugin-update-checker/plugin-update-checker.php';
$met_hello_child_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/ismetdev/met-hello-elementor-child/',
	get_stylesheet_directory() . '/style.css',
	get_stylesheet()
);
$met_hello_child_update_checker->setBranch( 'main' );

// Authenticate with GitHub only when a token is provided. Not needed while the
// repository is public. Define MET_HELLO_CHILD_GITHUB_TOKEN in wp-config.php to
// use one; it is never committed to the repository.
if ( defined( 'MET_HELLO_CHILD_GITHUB_TOKEN' ) && MET_HELLO_CHILD_GITHUB_TOKEN ) {
	$met_hello_child_update_checker->setAuthentication( MET_HELLO_CHILD_GITHUB_TOKEN );
}

// Deliver updates from the zip attached to each GitHub Release, so the theme
// unpacks into a clean met-hello-elementor-child/ folder.
$met_hello_child_update_checker->getVcsApi()->enableReleaseAssets();

/**
 * Load the theme text domain for translations.
 *
 * Text domain: met-hello-child. All human-readable strings resolve against
 * /languages.
 */
function met_hello_child_load_textdomain() {
	load_child_theme_textdomain( 'met-hello-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'met_hello_child_load_textdomain' );

/**
 * Whether the current request is one of this theme's custom styled views.
 *
 * Covers native single blog Posts (single.php), their category/tag/date archives
 * (archive.php), search results (search.php), author profiles (author.php), and
 * 404s (404.php). Single/archive intentionally use is_singular( 'post' ) and the
 * three specific archive conditionals rather than the broad is_single() /
 * is_archive(), so Haraka CPT singles/archives (Events/Tenders/Careers), Pages,
 * and the blog home stay excluded — no style or class-name collisions.
 *
 * @return bool
 */
function met_hello_child_is_styled_view() {
	return is_singular( 'post' )
		|| met_hello_child_is_post_archive()
		|| is_search()
		|| is_author()
		|| is_404();
}

/**
 * Whether the current request is a native-post category, tag, or date archive.
 *
 * @return bool
 */
function met_hello_child_is_post_archive() {
	return is_category() || is_tag() || is_date();
}

/**
 * Google Fonts stylesheet URL for the editorial type (Geist + Instrument Serif).
 *
 * TODO: self-host fonts. To move off the Google Fonts CDN, drop the font files
 * into /assets/fonts, ship a local @font-face stylesheet, and return its URL
 * from this one function — nothing else needs to change.
 *
 * @return string
 */
function met_hello_child_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap';
}

/**
 * Enqueue the article stylesheet and fonts — only on the theme's article views.
 *
 * Hello Elementor ships its CSS as reset.css (handle "hello-elementor") and
 * theme.css (handle "hello-elementor-theme-style"), both enqueued by the parent
 * itself; its own style.css is effectively empty. The child stylesheet declares
 * those as dependencies so it always loads after them and its overrides win.
 * Loading nothing on other views keeps the rest of the site as plain Hello
 * Elementor and satisfies the "conditional assets" performance rule.
 */
function met_hello_child_enqueue_styles() {
	if ( ! met_hello_child_is_styled_view() ) {
		return;
	}

	// Fonts first (null version: Google serves its own cache headers).
	wp_enqueue_style( 'met-hello-child-fonts', met_hello_child_fonts_url(), array(), null );

	wp_enqueue_style(
		'met-hello-child',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'hello-elementor', 'hello-elementor-theme-style', 'met-hello-child-fonts' ),
		MET_HELLO_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_styles', 20 );

/**
 * Preconnect to the Google Fonts hosts, but only where the fonts actually load.
 *
 * @param array  $urls          Resource-hint URLs for the given relation.
 * @param string $relation_type Current relation type.
 * @return array
 */
function met_hello_child_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && met_hello_child_is_styled_view() ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'met_hello_child_resource_hints', 10, 2 );

/**
 * Add the full-width marker class to the <body> on every styled view (Option A).
 *
 * The theme — not any per-page setting — forces edge-to-edge layout by targeting
 * `body.met-hello-child-fullwidth .site-main.met-view` in style.css to strip
 * Hello Elementor's centered max-width.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function met_hello_child_body_class( $classes ) {
	if ( met_hello_child_is_styled_view() ) {
		$classes[] = 'met-hello-child-fullwidth';
	}

	return $classes;
}
add_filter( 'body_class', 'met_hello_child_body_class' );

/**
 * Estimated reading time in whole minutes (min 1) from a post's word count.
 *
 * @param int|null $post_id Post ID, or null for the current post.
 * @return int
 */
function met_hello_child_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( (string) $content ) );

	return (int) max( 1, (int) ceil( $words / 200 ) );
}

/**
 * The post's primary term for a taxonomy (Yoast primary if set, else the first).
 *
 * @param int|null $post_id  Post ID, or null for the current post.
 * @param string   $taxonomy Taxonomy name.
 * @return WP_Term|null
 */
function met_hello_child_get_primary_term( $post_id = null, $taxonomy = 'category' ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	// Respect a Yoast SEO primary term when present.
	$primary_id = get_post_meta( $post_id, '_yoast_wpseo_primary_' . $taxonomy, true );
	if ( $primary_id ) {
		$term = get_term( (int) $primary_id, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}

	$terms = get_the_terms( $post_id, $taxonomy );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		return $terms[0];
	}

	return null;
}

/**
 * Destination for the "Back to Newsroom" link.
 *
 * Defaults to the site's news archive. Filterable so the target can be changed
 * without editing the template.
 *
 * @return string
 */
function met_hello_child_back_link_url() {
	$default = home_url( '/news-announcement/' );

	/**
	 * Filter the "Back to Newsroom" destination URL.
	 *
	 * @param string $default Default archive URL.
	 */
	return apply_filters( 'met_hello_child_back_link_url', $default );
}

/**
 * Author website + social links for the author profile header.
 *
 * Reads the WordPress website field plus any social contact methods (Yoast adds
 * twitter/facebook/linkedin/instagram/youtube/…). Values that are already URLs
 * pass through; bare handles for known networks are expanded. Returns an array
 * of items: array( 'url', 'label', 'icon' ). Degrades to an empty array.
 *
 * @param int $user_id Author user ID.
 * @return array<int,array<string,string>>
 */
function met_hello_child_get_author_links( $user_id ) {
	$links = array();

	$website = get_the_author_meta( 'user_url', $user_id );
	if ( $website ) {
		$links[] = array(
			'url'   => $website,
			'label' => __( 'Website', 'met-hello-child' ),
			'icon'  => 'globe',
		);
	}

	$methods = wp_get_user_contact_methods( null );
	foreach ( $methods as $key => $label ) {
		$value = get_the_author_meta( $key, $user_id );
		if ( ! $value ) {
			continue;
		}
		$url = met_hello_child_normalize_social_url( $key, $value );
		if ( ! $url ) {
			continue;
		}
		$links[] = array(
			'url'   => $url,
			'label' => $label,
			'icon'  => $key,
		);
	}

	return $links;
}

/**
 * Turn a social contact-method value into a full URL.
 *
 * @param string $key   Contact-method key (e.g. "twitter").
 * @param string $value Stored value (URL or bare handle).
 * @return string Full URL, or '' if it can't be resolved.
 */
function met_hello_child_normalize_social_url( $key, $value ) {
	if ( preg_match( '#^https?://#i', $value ) ) {
		return $value;
	}

	$handle = ltrim( $value, '@/' );
	if ( '' === $handle ) {
		return '';
	}

	switch ( $key ) {
		case 'twitter':
			return 'https://twitter.com/' . rawurlencode( $handle );
		case 'facebook':
			return 'https://www.facebook.com/' . rawurlencode( $handle );
		case 'instagram':
			return 'https://www.instagram.com/' . rawurlencode( $handle );
		case 'linkedin':
			return 'https://www.linkedin.com/in/' . rawurlencode( $handle );
		case 'youtube':
			return 'https://www.youtube.com/' . rawurlencode( $handle );
	}

	return '';
}

/**
 * Inline SVG for a social/website icon. Returns trusted static markup.
 *
 * @param string $name Icon key.
 * @return string
 */
function met_hello_child_social_icon( $name ) {
	$icons = array(
		'globe'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18" stroke="currentColor" stroke-width="1.7"/></svg>',
		'twitter'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 3h3l-6.6 7.5L21.8 21h-5.9l-4.2-5.5L6.8 21H3.8l7-8L2.5 3h6l3.8 5 5.2-5z"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h2.5l.5-3H13v-2c0-.6.4-1 1-1z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.7"/><circle cx="16.6" cy="7.4" r="1.1" fill="currentColor"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M6.5 8.5v9H4v-9h2.5zM5.2 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM20 17.5h-2.5v-4.7c0-1.2-.4-2-1.5-2-.8 0-1.3.6-1.5 1.1-.1.2-.1.5-.1.7v4.9H12s.03-8.2 0-9h2.5v1.3c.3-.5 1-1.2 2.3-1.2 1.7 0 3.2 1.1 3.2 3.6v5.3z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="12" rx="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M11 9.5l3.5 2.5L11 14.5v-5z" fill="currentColor"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 3h3l-6.6 7.5L21.8 21h-5.9l-4.2-5.5L6.8 21H3.8l7-8L2.5 3h6l3.8 5 5.2-5z"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M4 20l1.3-4A8 8 0 1112 20a8 8 0 01-4-1L4 20z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5.4 0 .7-.4.7-.8v-1c0-.3-.2-.5-.5-.6l-1-.2c-.3 0-.5.1-.7.3-.6-.3-1.1-.8-1.4-1.4.2-.2.3-.4.3-.7l-.2-1c-.1-.3-.3-.5-.6-.5h-1c-.4 0-.8.3-.8.7z" fill="currentColor"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M21.9 4.3l-3 14.1c-.2 1-.8 1.2-1.7.8l-4.6-3.4-2.2 2.1c-.2.2-.5.4-.9.4l.3-4.7 8.5-7.7c.4-.3-.1-.5-.6-.2L7.4 12.6l-4.5-1.4c-1-.3-1-1 .2-1.4l17.6-6.8c.8-.3 1.5.2 1.2 1.3z"/></svg>',
		'threads'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12.4 2c2.6 0 4.6.8 6 2.5 1.2 1.5 1.9 3.5 2 6v1c-.1 2.5-.8 4.5-2 6-1.4 1.7-3.4 2.5-6 2.5s-4.6-.8-6-2.5c-1.2-1.5-1.9-3.5-2-6v-1c.1-2.5.8-4.5 2-6C7.8 2.8 9.8 2 12.4 2Zm0 1.8c-2.1 0-3.6.6-4.6 1.8-1 1.2-1.5 2.9-1.6 5v.8c.1 2.1.6 3.8 1.6 5 1 1.2 2.5 1.8 4.6 1.8s3.6-.6 4.6-1.8c.4-.5.7-1.1 1-1.9-.3-.2-.7-.4-1.1-.6-.3 1.1-.8 1.9-1.5 2.5-.8.6-1.7.8-2.8.7-1-.1-1.8-.4-2.3-1-.6-.6-.8-1.3-.8-2 .1-1.6 1.5-2.6 3.6-2.6.6 0 1.2 0 1.7.1v-.2c0-.7-.2-1.2-.6-1.6-.4-.4-1-.6-1.7-.6-1 0-1.6.4-2.1 1.1l-1.4-.9c.7-1.1 1.8-1.6 3.3-1.6 1.2 0 2.1.4 2.8 1.1.7.7 1 1.6 1.1 2.8v.5c.4.2.8.5 1.1.7v-.6c-.1-2.1-.6-3.8-1.6-5-1-1.2-2.5-1.8-4.6-1.8Zm.3 8.6c-1.1 0-1.9.5-1.9 1.2 0 .4.1.7.4.9.3.3.7.4 1.2.4.7 0 1.2-.2 1.6-.6.3-.3.5-.8.6-1.4-.6-.1-1.2-.1-1.9-.1Z"/></svg>',
	);

	return isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['globe'];
}

/**
 * Share targets for a single post. Order follows common share-bar convention.
 *
 * @param string $url   Canonical post URL.
 * @param string $title Post title.
 * @return array<int,array<string,string>> Items: array( 'icon', 'label', 'url' ).
 */
function met_hello_child_get_share_links( $url, $title ) {
	$u = rawurlencode( $url );
	$t = rawurlencode( $title );

	return array(
		array(
			'icon'  => 'x',
			'label' => __( 'Share on X', 'met-hello-child' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . $u . '&text=' . $t,
		),
		array(
			'icon'  => 'facebook',
			'label' => __( 'Share on Facebook', 'met-hello-child' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $u,
		),
		array(
			'icon'  => 'linkedin',
			'label' => __( 'Share on LinkedIn', 'met-hello-child' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u,
		),
		array(
			'icon'  => 'whatsapp',
			'label' => __( 'Share on WhatsApp', 'met-hello-child' ),
			'url'   => 'https://api.whatsapp.com/send?text=' . $t . '%20' . $u,
		),
		array(
			'icon'  => 'telegram',
			'label' => __( 'Share on Telegram', 'met-hello-child' ),
			'url'   => 'https://t.me/share/url?url=' . $u . '&text=' . $t,
		),
		array(
			'icon'  => 'threads',
			'label' => __( 'Share on Threads', 'met-hello-child' ),
			'url'   => 'https://www.threads.net/intent/post?text=' . $t . '%20' . $u,
		),
	);
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

	$template = get_stylesheet_directory() . '/maintenance-template.php';
	if ( file_exists( $template ) ) {
		require $template;
	}
	exit;
}
add_action( 'template_redirect', 'met_hello_child_maybe_maintenance' );

/**
 * Route HTML wp_die() calls through our handler so front-end 403s are styled.
 *
 * The `wp_die_handler` filter is only used for the HTML death path — AJAX, JSON,
 * REST, and XML-RPC use their own handlers — so wp-admin AJAX and the REST API
 * are unaffected. Non-403 deaths fall back to the WordPress default handler.
 *
 * @param callable $handler Current handler.
 * @return callable
 */
function met_hello_child_set_wp_die_handler( $handler ) {
	return 'met_hello_child_wp_die_handler';
}
add_filter( 'wp_die_handler', 'met_hello_child_set_wp_die_handler' );

/**
 * Styled wp_die() handler: renders our 403 page for front-end 403s only.
 *
 * @param string|WP_Error $message Death message.
 * @param string          $title   Death title.
 * @param array           $args    Death args (may include 'response').
 * @return void
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
 * Used by maintenance-template.php and the styled 403 handler. Runs with
 * WordPress loaded, but inlines all CSS because the enqueued stylesheet is not
 * present in these contexts. No header/footer (per the design decision).
 *
 * @param array $args {
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
	<title><?php echo esc_html( $args['title'] . ' — ' . $site_name ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
