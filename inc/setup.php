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

/**
 * Wrap Elementor Page content in a <main> landmark (D27).
 *
 * Hello Elementor's own template-parts (single.php, archive.php, search.php,
 * 404.php) each print <main id="content" class="site-main">, but its Page
 * templates do not: elementor/modules/page-templates/templates/header-footer.php
 * calls get_header() then get_footer() with nothing in between but Elementor's
 * content, and get_header() only opens <body> and prints the site header, no
 * <main>. The Canvas template (canvas.php) has no header.php/footer.php call
 * at all. Confirmed by reading both files: every Elementor Page on this site,
 * Full Width or Canvas, ships with zero <main> element and no landmark for
 * assistive technology to jump to. That is also why the parent theme's own
 * skip link, which targets #content, points at nothing on these pages.
 *
 * Hooked on the same before/after "content" actions Page Hero
 * (inc/page-hero.php) already uses, at a priority that puts the hero band
 * inside <main> rather than ahead of it, since the hero heading is page
 * content, not chrome. This does not touch any Elementor or parent theme
 * file: the tag is added from the child theme only, the same pattern D25 and
 * D26 already established for this codebase.
 */
function met_hello_child_open_elementor_main() {
	echo '<main id="content" class="site-main">';
}
add_action( 'elementor/page_templates/header-footer/before_content', 'met_hello_child_open_elementor_main', 5 );
add_action( 'elementor/page_templates/canvas/before_content', 'met_hello_child_open_elementor_main', 5 );

/**
 * Close the <main> landmark opened by met_hello_child_open_elementor_main().
 */
function met_hello_child_close_elementor_main() {
	echo '</main>';
}
add_action( 'elementor/page_templates/header-footer/after_content', 'met_hello_child_close_elementor_main', 20 );
add_action( 'elementor/page_templates/canvas/after_content', 'met_hello_child_close_elementor_main', 20 );

/**
 * Whether the given (or current) Page renders through page.php, the block
 * editor template, rather than through Elementor.
 *
 * Elementor owns a Page's front-end rendering whenever its
 * `_elementor_edit_mode` post meta is `builder` (set by the plugin itself,
 * read here, never written by the theme). Anything else, including a Page
 * that has never been touched by Elementor, falls through to page.php. See
 * PLAN/PRD-block-system.md section 4.5 for the full migrate/rollback story.
 *
 * @param int|null $post_id Page ID. Defaults to the current queried object.
 * @return bool
 */
function met_hello_child_is_block_page( $post_id = null ) {
	if ( null === $post_id ) {
		if ( ! is_page() ) {
			return false;
		}
		$post_id = get_queried_object_id();
	}

	return 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true );
}
