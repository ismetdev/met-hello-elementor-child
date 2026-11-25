<?php
/**
 * SEO output fixes.
 *
 * The parent theme prints its own meta description from the post excerpt.
 * When Yoast SEO is active, Yoast prints the correct one, so a page ends up
 * with two description tags. This file turns the parent's tag off in that case.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turn off the parent theme's meta description tag when Yoast SEO is active.
 *
 * Hello Elementor checks the filter hello_elementor_description_meta_tag before
 * it prints <meta name="description"> from the excerpt. Search engines expect
 * one description per page, so only Yoast's tag should remain. Without Yoast,
 * the incoming value is returned unchanged and the parent keeps its behaviour.
 *
 * @param bool $enabled Whether the parent prints its description tag.
 * @return bool
 */
function met_hello_child_disable_parent_description_meta( $enabled ) {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return false;
	}

	return $enabled;
}
add_filter( 'hello_elementor_description_meta_tag', 'met_hello_child_disable_parent_description_meta', 10, 1 );
