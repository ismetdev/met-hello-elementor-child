<?php
/**
 * Homepage closing CTA: a gold band inviting contact.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="met-section met-cta">
	<div class="met-container met-cta__inner">
		<div>
			<h2 class="met-cta__title"><?php esc_html_e( 'Work with the IIUM Holdings group.', 'met-hello-child' ); ?></h2>
			<p class="met-cta__lead"><?php esc_html_e( 'For partnerships, procurement, or general enquiries, our team is ready to help.', 'met-hello-child' ); ?></p>
		</div>
		<a class="met-btn met-btn--dark" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact us', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></a>
	</div>
</section>
