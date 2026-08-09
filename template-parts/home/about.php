<?php
/**
 * Homepage "Who we are" band: intro copy beside an editorial image with the
 * 25-year emblem. The image comes from the Customizer (Homepage > About image);
 * with none set it renders the designed empty state.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_about_image = met_hello_child_get_home_image_id( 'about' );
?>
<section class="met-section met-band met-about" id="about">
	<div class="met-container">
		<div class="met-about__grid">
			<div class="met-reveal">
				<span class="met-eyebrow"><?php esc_html_e( 'Who we are', 'met-hello-child' ); ?></span>
				<h2 class="met-about__title"><?php esc_html_e( 'The commercial arm of the University.', 'met-hello-child' ); ?></h2>
				<p class="met-about__lead"><?php esc_html_e( 'IIUM Holdings manages the business interests of the International Islamic University Malaysia.', 'met-hello-child' ); ?></p>
				<p class="met-about__body"><?php esc_html_e( 'We operate nine companies across three industries. Our role is to run them well, support the University, and serve the wider community. We were incorporated in 2001, and today the group employs more than a thousand people.', 'met-hello-child' ); ?></p>
				<a class="met-btn met-btn--ghost" href="<?php echo esc_url( home_url( '/iium-holdings-group-of-companies/' ) ); ?>"><?php esc_html_e( 'More about the group', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></a>
			</div>
			<div class="met-about__visual met-reveal">
				<?php met_hello_child_home_media( $met_about_image, 'full', __( 'IIUM Holdings at work', 'met-hello-child' ) ); ?>
				<div class="met-about__emblem">
					<div class="met-about__emblem-n">25</div>
					<div class="met-about__emblem-t"><?php esc_html_e( 'Years · Since 2001', 'met-hello-child' ); ?></div>
				</div>
			</div>
		</div>
	</div>
</section>
