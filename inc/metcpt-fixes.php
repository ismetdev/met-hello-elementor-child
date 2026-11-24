<?php
/**
 * Small fixes for the MetCPT single Tender and Career pages.
 *
 * MetCPT prints those pages from its own templates, so the theme cannot edit
 * them. These filters correct three things from the theme side and do nothing
 * on any other page.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give "Back to Tenders" an address with a trailing slash.
 *
 * MetCPT's single tender template falls back to home_url( '/tenders' ), with
 * no slash, which WordPress then redirects. Only that exact call, on a single
 * tender page, is changed. The target follows the "Tenders" address setting.
 *
 * @param string $url  Full URL.
 * @param string $path Path passed to home_url().
 * @return string
 */
function met_hello_child_fix_tender_back_url( $url, $path ) {
	if ( '/tenders' !== $path || ! did_action( 'template_redirect' ) || ! is_singular( 'metcpt_tender' ) ) {
		return $url;
	}

	return met_hello_child_page_url( 'tenders' );
}
add_filter( 'home_url', 'met_hello_child_fix_tender_back_url', 10, 2 );

/**
 * Give "Back to Careers" an address with a trailing slash.
 *
 * MetCPT reads the option metcpt_careers_page_url and defaults to '/careers'.
 * An empty option or that default now follows the "Careers" address setting.
 * Any other saved value is kept, with a trailing slash added.
 *
 * @param mixed $value Option value, or the default when the option is missing.
 * @return mixed
 */
function met_hello_child_fix_careers_back_url( $value ) {
	if ( is_admin() || ! is_string( $value ) ) {
		return $value;
	}

	$value = trim( $value );

	if ( '' === $value || '/careers' === untrailingslashit( $value ) ) {
		return met_hello_child_page_url( 'careers' );
	}

	// Leave addresses with a query string or anchor alone.
	if ( false !== strpbrk( $value, '?#' ) ) {
		return $value;
	}

	return trailingslashit( $value );
}
add_filter( 'option_metcpt_careers_page_url', 'met_hello_child_fix_careers_back_url' );
add_filter( 'default_option_metcpt_careers_page_url', 'met_hello_child_fix_careers_back_url' );

/**
 * Hide a repeated excerpt on a single career page.
 *
 * MetCPT prints the excerpt above the "Job Description" section. With no
 * hand-written excerpt WordPress builds one from the first words of the
 * description, so the same text shows twice. An empty excerpt, or one that
 * only repeats the start of the description, is blanked. A hand-written
 * excerpt that differs is kept.
 *
 * @param string  $excerpt Excerpt.
 * @param WP_Post $post    Post.
 * @return string
 */
function met_hello_child_hide_repeated_career_excerpt( $excerpt, $post = null ) {
	if ( ! $post instanceof WP_Post || 'metcpt_career' !== $post->post_type || ! is_singular( 'metcpt_career' ) ) {
		return $excerpt;
	}

	// No hand-written excerpt: the text is auto-built from the description.
	if ( '' === trim( $post->post_excerpt ) ) {
		return '';
	}

	$normalise = static function ( $text ) {
		return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( strip_shortcodes( $text ) ), ENT_QUOTES, 'UTF-8' ) ) );
	};

	$written = $normalise( $post->post_excerpt );
	$content = $normalise( $post->post_content );

	if ( '' !== $written && 0 === strpos( $content, rtrim( $written, ". \t\n\r\0\x0B\xE2\x80\xA6" ) ) ) {
		return '';
	}

	return $excerpt;
}
add_filter( 'get_the_excerpt', 'met_hello_child_hide_repeated_career_excerpt', 10, 2 );
