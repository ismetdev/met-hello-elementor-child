<?php
/**
 * A single card in a [met_posts] listing. Called from inside the shortcode's
 * WP_Query loop, after the_post(), so it reads the loop globals.
 *
 * @package MetHelloElementorChild
 *
 * @var array{layout:string,is_feature:bool} $args Passed by get_template_part().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_layout     = isset( $args['layout'] ) ? $args['layout'] : 'grid';
$met_is_feature = ! empty( $args['is_feature'] );

$met_card_term  = met_hello_child_get_primary_term();
$met_card_url   = get_permalink();
$met_card_title = get_the_title();

// 'album' cards show the album's external target with an out-link marker; the
// link itself still points at the post, which carries the description and share
// row on our own domain. See DECISIONS D46.
$met_is_album  = ( 'album' === $met_layout );
$met_album_url = $met_is_album ? met_hello_child_get_album_url() : '';

// Image size by layout: list uses the small thumb, everything else the card size.
$met_card_size = ( 'list' === $met_layout ) ? 'met-thumb' : 'met-card';

$met_card_classes = array( 'met-list__card' );
if ( $met_is_feature ) {
	$met_card_classes[] = 'met-list__card--feature';
	$met_card_size      = 'large';
}
if ( $met_is_album ) {
	$met_card_classes[] = 'met-list__card--album';
}
?>
<a class="<?php echo esc_attr( implode( ' ', $met_card_classes ) ); ?>" href="<?php echo esc_url( $met_card_url ); ?>">
	<div class="met-list__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			the_post_thumbnail(
				$met_card_size,
				array(
					'alt'      => $met_card_title,
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			?>
		<?php else : ?>
			<span class="met-list__media-fallback" aria-hidden="true"></span>
		<?php endif; ?>
		<?php if ( $met_is_album && '' !== $met_album_url ) : ?>
			<span class="met-list__album-badge" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" focusable="false"><path d="M7 17L17 7M17 7H9M17 7v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</span>
		<?php endif; ?>
	</div>
	<div class="met-list__body">
		<?php if ( $met_card_term ) : ?>
			<span class="met-list__cat"><?php echo esc_html( $met_card_term->name ); ?></span>
		<?php endif; ?>
		<h3 class="met-list__title"><?php echo esc_html( $met_card_title ); ?></h3>
		<div class="met-list__meta">
			<span class="met-list__date"><?php echo esc_html( get_the_date() ); ?></span>
			<?php if ( $met_is_album && '' !== $met_album_url ) : ?>
				<span class="met-list__meta-sep"></span>
				<span class="met-list__album-note"><?php esc_html_e( 'Photo album', 'met-hello-child' ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $met_is_feature ) : ?>
			<p class="met-list__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
		<?php endif; ?>
	</div>
</a>
