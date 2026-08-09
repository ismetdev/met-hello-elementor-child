<?php
/**
 * Template tags used by the theme's templates and partials.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

	// Respect a Yoast SEO primary term when present, but only if the post is
	// still assigned to it. Yoast does not clear this meta when the post's terms
	// change, so it can point at a term the post no longer belongs to (e.g. a post
	// moved off "Uncategorized" still carrying primary = Uncategorized). Without
	// the has_term() check the card and single would show the stale term.
	$primary_id = get_post_meta( $post_id, '_yoast_wpseo_primary_' . $taxonomy, true );
	if ( $primary_id && has_term( (int) $primary_id, $taxonomy, $post_id ) ) {
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
