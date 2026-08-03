<?php
/**
 * Page Hero band for Elementor Pages: standard or business variant, driven by
 * the post meta saved in inc/page-hero.php's meta box.
 *
 * Printed via elementor/page_templates/*\/before_content, so no loop is active;
 * everything reads from the queried object id. The wrapping element carries
 * `met-view` so the shared .met-hero / .eyebrow rules in assets/css/theme.css
 * apply without modification.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$met_hero_post_id = get_queried_object_id();
$met_hero_variant = get_post_meta( $met_hero_post_id, '_met_hero_variant', true );

if ( ! in_array( $met_hero_variant, met_hello_child_page_hero_variants(), true ) ) {
	return;
}

$met_hero_title    = get_the_title( $met_hero_post_id );
$met_hero_eyebrow  = get_post_meta( $met_hero_post_id, '_met_hero_eyebrow', true );
$met_hero_subtitle = get_post_meta( $met_hero_post_id, '_met_hero_subtitle', true );

// Eyebrow falls back to the parent page title, then to nothing.
if ( ! $met_hero_eyebrow ) {
	$met_hero_parent_id = wp_get_post_parent_id( $met_hero_post_id );
	if ( $met_hero_parent_id ) {
		$met_hero_eyebrow = get_the_title( $met_hero_parent_id );
	}
}
?>
<div class="met-view met-page-hero met-page-hero--<?php echo esc_attr( $met_hero_variant ); ?>">

	<?php if ( 'business' === $met_hero_variant ) : ?>

		<?php
		$met_hero_ctas = array();
		foreach ( array( 1, 2 ) as $met_hero_slot ) {
			$met_hero_label = get_post_meta( $met_hero_post_id, '_met_hero_cta' . $met_hero_slot . '_label', true );
			$met_hero_url   = get_post_meta( $met_hero_post_id, '_met_hero_cta' . $met_hero_slot . '_url', true );
			if ( $met_hero_label && $met_hero_url ) {
				$met_hero_ctas[] = array(
					'label' => $met_hero_label,
					'url'   => $met_hero_url,
				);
			}
		}
		?>
		<section class="page-hero-business">
			<div class="container page-hero-business__inner">

				<?php if ( $met_hero_eyebrow ) : ?>
					<span class="page-hero-business__tag"><?php echo esc_html( $met_hero_eyebrow ); ?></span>
				<?php endif; ?>

				<h1 class="page-hero-business__name"><?php echo esc_html( $met_hero_title ); ?></h1>

				<?php if ( $met_hero_subtitle ) : ?>
					<p class="page-hero-business__line"><?php echo esc_html( $met_hero_subtitle ); ?></p>
				<?php endif; ?>

				<?php if ( $met_hero_ctas ) : ?>
					<div class="page-hero-business__actions">
						<?php foreach ( $met_hero_ctas as $met_hero_index => $met_hero_cta ) : ?>
							<a class="page-hero-business__btn<?php echo 0 === $met_hero_index ? ' page-hero-business__btn--primary' : ''; ?>" href="<?php echo esc_url( $met_hero_cta['url'] ); ?>">
								<?php echo esc_html( $met_hero_cta['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
		</section>

	<?php else : ?>

		<header class="met-hero">
			<div class="container met-hero__inner">

				<?php if ( $met_hero_eyebrow ) : ?>
					<div class="met-hero__eyebrow">
						<span class="eyebrow eyebrow--on-dark"><?php echo esc_html( $met_hero_eyebrow ); ?></span>
					</div>
				<?php endif; ?>

				<h1 class="met-hero__title"><?php echo esc_html( $met_hero_title ); ?></h1>

				<?php if ( $met_hero_subtitle ) : ?>
					<div class="met-hero__desc"><?php echo esc_html( $met_hero_subtitle ); ?></div>
				<?php endif; ?>

			</div>
		</header>

	<?php endif; ?>

</div>
