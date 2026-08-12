<?php
/**
 * Site footer.
 *
 * Mirror of header.php: falls through to the parent Hello Elementor footer when
 * the custom header is off, and otherwise emits the closing document tags that
 * this theme's header.php opened.
 *
 * The header and footer are two independent toggles (DECISIONS D48). Header
 * off means the parent rendered the whole page, so its footer.php is the only
 * one that can validly close it. Header on means this file must close what
 * header.php opened, regardless of which footer toggle is set: the footer
 * content itself is either this theme's site-footer, or a footer built in the
 * Header Footer Elementor plugin (bundled with UAE), with the theme footer as
 * the fallback if none is assigned yet.
 *
 * HFE is settings-based, not condition-based per call: hfe_footer_enabled()
 * and hfe_render_footer() both read the one global "type_footer" setting
 * (Appearance/Elementor > Header Footer Builder), which is the elementor-hf
 * post whose `ehf_template_type` meta is 'footer' and whose
 * `ehf_target_include_locations` meta matches the current page. This is a
 * different mechanism from Elementor Pro's Theme Builder
 * (elementor_theme_do_location()), which this plugin does not provide.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'met_hello_child_chrome_enabled' ) || ! met_hello_child_chrome_enabled() ) {
	require get_template_directory() . '/footer.php';
	return;
}

if ( ! met_hello_child_footer_enabled() && function_exists( 'hfe_footer_enabled' ) && hfe_footer_enabled() && function_exists( 'hfe_render_footer' ) ) {
	hfe_render_footer();
} else {
	get_template_part( 'template-parts/site-footer' );
}

wp_footer();
?>
</body>
</html>
