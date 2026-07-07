<?php
/**
 * A single post card for the listing grid (archive / search / author).
 *
 * Must be called from inside the loop, after the_post(). Uses the shared
 * `.met-card` styles. Featured image when present, otherwise a petrol fallback
 * block so the grid stays even.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$met_card_link    = get_permalink();
$met_card_term    = met_hello_child_get_primary_term( get_the_ID() );
$met_card_reading = met_hello_child_reading_time( get_the_ID() );
$met_card_excerpt = wp_trim_words( get_the_excerpt(), 24, '&hellip;' );
?>
<article <?php post_class( 'met-card' ); ?>>

	<a class="met-card__media<?php echo has_post_thumbnail() ? '' : ' met-card__media--fallback'; ?>" href="<?php echo esc_url( $met_card_link ); ?>" aria-hidden="true" tabindex="-1">
		<?php
		if ( has_post_thumbnail() ) {
			// the_post_thumbnail() escapes attributes itself — pass raw values.
			the_post_thumbnail(
				'large',
				array(
					'alt'     => get_the_title(),
					'loading' => 'lazy',
				)
			);
		}
		?>
	</a>

	<div class="met-card__body">
		<?php if ( $met_card_term ) : ?>
			<span class="eyebrow met-card__eyebrow"><?php echo esc_html( $met_card_term->name ); ?></span>
		<?php endif; ?>

		<h2 class="met-card__title">
			<a href="<?php echo esc_url( $met_card_link ); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="met-card__meta">
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<span class="met-card__meta-sep"></span>
			<span>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: estimated reading time in minutes. */
						_n( '%s min read', '%s min read', $met_card_reading, 'met-hello-child' ),
						number_format_i18n( $met_card_reading )
					)
				);
				?>
			</span>
		</div>

		<?php if ( $met_card_excerpt ) : ?>
			<p class="met-card__excerpt"><?php echo esc_html( $met_card_excerpt ); ?></p>
		<?php endif; ?>
	</div>

</article>
