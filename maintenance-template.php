<?php
/**
 * Styled maintenance page shown by the theme's maintenance toggle.
 *
 * Loaded by met_hello_child_maybe_maintenance() (functions.php) with WordPress
 * running. Rendering (and all inlined CSS) is delegated to the shared standalone
 * renderer. No home button — the front end is intentionally unavailable.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

met_hello_child_render_standalone(
	array(
		'code'      => '',
		'title'     => __( 'Scheduled maintenance', 'met-hello-child' ),
		'heading'   => __( 'We’ll be right back', 'met-hello-child' ),
		'message'   => __( 'The site is undergoing scheduled maintenance. Please check back shortly.', 'met-hello-child' ),
		'show_home' => false,
	)
);
