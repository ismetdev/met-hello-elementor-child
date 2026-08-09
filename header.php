<?php
/**
 * Site header.
 *
 * When the custom chrome is off (the default), this falls straight through to
 * the parent Hello Elementor header, so behaviour is unchanged and the toggle
 * is an instant, one-click rollback. When on, it emits the document head and
 * the theme's own header, and template-parts/site-header.php takes over.
 *
 * The fallback uses require on an absolute parent path, never locate_template(),
 * which would find this same child file first and recurse. See DECISIONS D38.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'met_hello_child_chrome_enabled' ) || ! met_hello_child_chrome_enabled() ) {
	require get_template_directory() . '/header.php';
	return;
}

// These three filters belong to the parent Hello Elementor theme, reused here
// deliberately so its viewport and skip-link settings still apply when the child
// renders the head. Not our hooks to rename.
$met_viewport      = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
$met_enable_skip   = apply_filters( 'hello_elementor_enable_skip_link', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
$met_skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $met_viewport ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( $met_enable_skip ) : ?>
	<a class="skip-link screen-reader-text" href="<?php echo esc_url( $met_skip_link_url ); ?>"><?php esc_html_e( 'Skip to content', 'met-hello-child' ); ?></a>
<?php endif; ?>
<?php
get_template_part( 'template-parts/site-header' );
get_template_part( 'template-parts/nav-drawer' );
