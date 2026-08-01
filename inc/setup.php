<?php
/**
 * Theme setup: text domain, the styled-view test, and the body class.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the theme text domain for translations.
 *
 * Text domain: met-hello-child. All human-readable strings resolve against
 * /languages.
 */
function met_hello_child_load_textdomain() {
	load_child_theme_textdomain( 'met-hello-child', MET_HELLO_CHILD_DIR . 'languages' );
}
add_action( 'after_setup_theme', 'met_hello_child_load_textdomain' );

/**
 * Whether the current request is one of this theme's custom styled views.
 *
 * Covers native single blog Posts (single.php), their category/tag/date archives
 * (archive.php), search results (search.php), author profiles (author.php), and
 * 404s (404.php). Single/archive intentionally use is_singular( 'post' ) and the
 * three specific archive conditionals rather than the broad is_single() /
 * is_archive(), so MetCPT singles/archives (Events/Tenders/Careers), Pages, and
 * the blog home stay excluded, and there are no style or class-name collisions.
 *
 * This is the single gate for the stylesheet, the font preconnect hints, and the
 * full-width body class. Widening it widens all three.
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
 * Add the full-width marker class to the <body> on every styled view (Option A).
 *
 * The theme, not any per-page setting, forces edge-to-edge layout by targeting
 * `body.met-hello-child-fullwidth .site-main.met-view` in assets/css/theme.css to
 * strip Hello Elementor's centered max-width.
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
