<?php
/**
 * Homepage hero carousel. Slides come from the met_hero_slide CPT
 * (inc/hero-slides.php). Zero slides falls back to one static slide from the
 * site identity; one slide renders without dots or arrows.
 *
 * The first slide's background is eager with fetchpriority="high" for LCP; the
 * rest are lazy. Dots are server-rendered so they exist without JS. The track
 * is an aria-live region so screen readers hear slide changes.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_hero_slides = met_hello_child_get_hero_slides();

// Fallback: one static slide from the site identity when no slides exist.
if ( empty( $met_hero_slides ) ) {
	$met_hero_slides = array(
		array(
			'id'       => 0,
			'eyebrow'  => __( 'The commercial arm of IIUM', 'met-hello-child' ),
			'headline' => get_bloginfo( 'name' ),
			'body'     => get_bloginfo( 'description' ),
			'image_id' => 0,
			'focus'    => 'center',
			'size'     => 0,
			'ctas'     => array(),
		),
	);
}

$met_hero_focus_map = met_hello_child_slide_focus_choices();
$met_hero_count     = count( $met_hero_slides );
?>
<section class="met-hero-home" aria-label="<?php esc_attr_e( 'Highlights', 'met-hello-child' ); ?>">
	<div class="met-hero-home__track" aria-live="polite">
		<?php foreach ( $met_hero_slides as $met_hero_i => $met_hero_slide ) : ?>
			<?php
			$met_hero_active = 0 === $met_hero_i;
			$met_hero_focus  = isset( $met_hero_focus_map[ $met_hero_slide['focus'] ] ) ? $met_hero_focus_map[ $met_hero_slide['focus'] ] : '50%';
			$met_hero_attrs  = array(
				'class'    => 'met-hero-home__bg',
				'alt'      => '',
				'decoding' => 'async',
				'style'    => 'object-position: center ' . $met_hero_focus . ';',
			);
			if ( $met_hero_active ) {
				$met_hero_attrs['fetchpriority'] = 'high';
			} else {
				$met_hero_attrs['loading'] = 'lazy';
			}
			?>
			<div class="met-hero-home__slide<?php echo $met_hero_active ? ' is-active' : ''; ?>"<?php echo $met_hero_active ? '' : ' aria-hidden="true"'; ?>>
				<?php
				if ( $met_hero_slide['image_id'] ) {
					echo wp_get_attachment_image( (int) $met_hero_slide['image_id'], 'full', false, $met_hero_attrs );
				}
				?>
				<div class="met-container met-hero-home__inner">
					<div class="met-hero-home__copy">
						<?php if ( ! empty( $met_hero_slide['eyebrow'] ) ) : ?>
							<span class="met-eyebrow met-eyebrow--on-dark"><?php echo esc_html( $met_hero_slide['eyebrow'] ); ?></span>
						<?php endif; ?>
						<?php
						// Per-slide headline size override (owner request, 2026-08-11): a
						// long programme or event name can otherwise cover too much of the
						// hero photo. Empty/zero keeps the CSS default in home.css.
						$met_hero_title_style = ! empty( $met_hero_slide['size'] )
							? ' style="--met-hero-title-max:' . esc_attr( $met_hero_slide['size'] ) . 'px"'
							: '';
						?>
						<?php if ( $met_hero_active ) : ?>
							<h1 class="met-hero-home__title"<?php echo $met_hero_title_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from esc_attr() on an absint-sanitised value. ?>><?php echo esc_html( $met_hero_slide['headline'] ); ?></h1>
						<?php else : ?>
							<p class="met-hero-home__title" role="heading" aria-level="2"<?php echo $met_hero_title_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from esc_attr() on an absint-sanitised value. ?>><?php echo esc_html( $met_hero_slide['headline'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $met_hero_slide['body'] ) ) : ?>
							<p class="met-hero-home__body"><?php echo esc_html( $met_hero_slide['body'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $met_hero_slide['ctas'] ) ) : ?>
							<div class="met-hero-home__cta">
								<?php foreach ( $met_hero_slide['ctas'] as $met_hero_ci => $met_hero_cta ) : ?>
									<a class="met-btn <?php echo 0 === $met_hero_ci ? 'met-btn--gold' : 'met-btn--ghost-light'; ?>" href="<?php echo esc_url( $met_hero_cta['url'] ); ?>"><?php echo esc_html( $met_hero_cta['label'] ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $met_hero_count > 1 ) : ?>
		<div class="met-hero-home__ui">
			<div class="met-container met-hero-home__ui-inner">
				<div class="met-hero-home__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose slide', 'met-hello-child' ); ?>">
					<?php for ( $met_hero_d = 0; $met_hero_d < $met_hero_count; $met_hero_d++ ) : ?>
						<button type="button" class="met-hero-home__dot<?php echo 0 === $met_hero_d ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $met_hero_d ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Slide %d', 'met-hello-child' ), $met_hero_d + 1 ) ); ?>"></button>
					<?php endfor; ?>
				</div>
				<div class="met-hero-home__nav">
					<button type="button" class="met-hero-home__arrow-btn" data-dir="prev" aria-label="<?php esc_attr_e( 'Previous slide', 'met-hello-child' ); ?>">&#8249;</button>
					<button type="button" class="met-hero-home__arrow-btn" data-dir="next" aria-label="<?php esc_attr_e( 'Next slide', 'met-hello-child' ); ?>">&#8250;</button>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
