<?php
/**
 * Homepage quick actions: two large cards linking to Tenders and Careers.
 * The visual is a brand gradient, so the cards need no image to look finished.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_action_tender_img = met_hello_child_get_home_image_id( 'action_tender' );
$met_action_career_img = met_hello_child_get_home_image_id( 'action_career' );
?>
<section class="met-section">
	<div class="met-container">
		<div class="met-actions met-reveal">
			<a class="met-action met-action--tender" href="<?php echo esc_url( home_url( '/tenders/' ) ); ?>">
				<?php if ( $met_action_tender_img ) : ?>
					<span class="met-action__bg">
					<?php
					echo wp_get_attachment_image(
						$met_action_tender_img,
						'full',
						false,
						array(
							'alt'      => '',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
													</span>
				<?php endif; ?>
				<span class="met-eyebrow met-eyebrow--on-dark"><?php esc_html_e( 'For suppliers and contractors', 'met-hello-child' ); ?></span>
				<h2 class="met-action__title"><?php esc_html_e( 'Tenders', 'met-hello-child' ); ?></h2>
				<p><?php esc_html_e( 'View open procurement opportunities across IIUM Holdings and its companies.', 'met-hello-child' ); ?></p>
				<span class="met-action__go"><?php esc_html_e( 'View open tenders', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></span>
			</a>
			<a class="met-action met-action--career" href="<?php echo esc_url( home_url( '/careers/' ) ); ?>">
				<?php if ( $met_action_career_img ) : ?>
					<span class="met-action__bg">
					<?php
					echo wp_get_attachment_image(
						$met_action_career_img,
						'full',
						false,
						array(
							'alt'      => '',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
													</span>
				<?php endif; ?>
				<span class="met-eyebrow met-eyebrow--on-dark"><?php esc_html_e( 'Join the group', 'met-hello-child' ); ?></span>
				<h2 class="met-action__title"><?php esc_html_e( 'Careers', 'met-hello-child' ); ?></h2>
				<p><?php esc_html_e( 'Explore roles across nine companies in education, facilities, and healthcare.', 'met-hello-child' ); ?></p>
				<span class="met-action__go"><?php esc_html_e( 'See open positions', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></span>
			</a>
		</div>
	</div>
</section>
