<?php
/**
 * Front-end assets: fonts, the design stylesheet, and resource hints.
 *
 * Everything here is gated by met_hello_child_is_styled_view() or, for Elementor
 * Pages carrying a Page Hero, met_hello_child_page_has_hero() (inc/page-hero.php).
 * Any other page stays plain Hello Elementor and loads none of it. Note the
 * full-width body class in inc/setup.php stays tied to the narrower styled-view
 * test only: Elementor Full Width Pages already render edge to edge.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts stylesheet URL for the editorial type (Geist + Instrument Serif).
 *
 * TODO: self-host fonts. To move off the Google Fonts CDN, drop the font files
 * into assets/fonts, ship a local @font-face stylesheet, and return its URL from
 * this one function. Nothing else needs to change.
 *
 * @return string
 */
function met_hello_child_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap';
}

/**
 * Enqueue the design stylesheet and fonts, on the theme's styled views and on
 * any Page carrying a Page Hero (inc/page-hero.php).
 *
 * Hello Elementor ships its CSS as reset.css (handle "hello-elementor") and
 * theme.css (handle "hello-elementor-theme-style"), both enqueued by the parent
 * itself; its own style.css is effectively empty. The child stylesheet declares
 * those as dependencies so it always loads after them and its overrides win.
 *
 * Note: this theme's own style.css holds the theme header only and is never
 * enqueued. The rules live in assets/css/theme.css.
 */
function met_hello_child_enqueue_styles() {
	if ( ! met_hello_child_is_styled_view() && ! met_hello_child_page_has_hero() ) {
		return;
	}

	// Fonts first (null version: Google serves its own cache headers).
	wp_enqueue_style( 'met-hello-child-fonts', met_hello_child_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- null is deliberate, see the comment above.

	wp_enqueue_style(
		'met-hello-child',
		MET_HELLO_CHILD_URI . 'assets/css/theme.css',
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
	if ( 'preconnect' === $relation_type && ( met_hello_child_is_styled_view() || met_hello_child_page_has_hero() ) ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'met_hello_child_resource_hints', 10, 2 );
