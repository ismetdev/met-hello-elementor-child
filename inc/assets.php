<?php
/**
 * Front-end assets: fonts, the design stylesheet, and resource hints.
 *
 * The Google Fonts and theme.css enqueue below are gated by
 * met_hello_child_is_styled_view() or, for Elementor Pages carrying a Page
 * Hero, met_hello_child_page_has_hero() (inc/page-hero.php). Any other page
 * stays plain Hello Elementor and loads none of it. Note the full-width body
 * class in inc/setup.php stays tied to the narrower styled-view test only:
 * Elementor Full Width Pages already render edge to edge.
 *
 * The token layer enqueued by met_hello_child_enqueue_tokens() is the one
 * exception to that gate, sitewide with no condition, same reasoning as
 * Scroll to Top (inc/scroll-top.php, DECISIONS D26): theme.css is not loaded
 * on the homepage or most Elementor Pages, so a sitewide layer cannot depend
 * on it.
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

	// Depends on met-hello-child-tokens (registered by
	// met_hello_child_enqueue_tokens() above) for the :root custom properties
	// this file's rules read. WP resolves style dependencies at print time,
	// so this works regardless of which enqueue callback runs first.
	wp_enqueue_style(
		'met-hello-child',
		MET_HELLO_CHILD_URI . 'assets/css/theme.css',
		array( 'hello-elementor', 'hello-elementor-theme-style', 'met-hello-child-fonts', 'met-hello-child-tokens' ),
		MET_HELLO_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_styles', 20 );

/**
 * Enqueue the sitewide design tokens and the Elementor base layer.
 *
 * No gate: this is the one stylesheet pair every page of the site loads,
 * including the homepage and every Elementor Page. tokens.css holds only
 * :root custom properties. elementor-base.css is the only theme file that
 * writes selectors targeting Elementor's own class names, and it never uses
 * !important except for the prefers-reduced-motion block, documented inline.
 *
 * Deliberately independent of met_hello_child_enqueue_styles(): theme.css
 * stays gated to the theme's own views, and this pair does not add to or
 * change that gate.
 */
function met_hello_child_enqueue_tokens() {
	wp_enqueue_style(
		'met-hello-child-tokens',
		MET_HELLO_CHILD_URI . 'assets/css/tokens.css',
		array(),
		MET_HELLO_CHILD_VERSION
	);

	wp_enqueue_style(
		'met-hello-child-elementor-base',
		MET_HELLO_CHILD_URI . 'assets/css/elementor-base.css',
		array( 'met-hello-child-tokens' ),
		MET_HELLO_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_tokens' );

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
