<?php
/**
 * Site footer.
 *
 * Mirror of header.php: falls through to the parent Hello Elementor footer when
 * the custom chrome is off, and emits the theme's own footer plus the closing
 * document tags when on.
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

get_template_part( 'template-parts/site-footer' );

wp_footer();
?>
</body>
</html>
