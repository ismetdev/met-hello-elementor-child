<?php
/**
 * Homepage announcements: a grid of 4:5 poster cards from the `announcements`
 * category. Renders a designed empty state when the category has no posts.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_ann_posts = met_hello_child_get_announcements( 4 );
?>
<section class="met-section met-band met-ann">
	<div class="met-container">
		<div class="met-ann__head">
			<div class="met-section__head met-reveal">
				<span class="met-eyebrow"><?php esc_html_e( 'Announcements', 'met-hello-child' ); ?></span>
				<h2><?php esc_html_e( 'Latest from the group', 'met-hello-child' ); ?></h2>
				<p><?php esc_html_e( 'Festive greetings, milestones, and group notices.', 'met-hello-child' ); ?></p>
			</div>
			<a class="met-btn met-btn--ghost met-reveal" href="<?php echo esc_url( home_url( '/news-announcement/' ) ); ?>"><?php esc_html_e( 'View all', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></a>
		</div>

		<?php if ( ! empty( $met_ann_posts ) ) : ?>
			<div class="met-ann__grid met-reveal">
				<?php foreach ( $met_ann_posts as $met_ann_post ) : ?>
					<a class="met-poster" href="<?php echo esc_url( get_permalink( $met_ann_post ) ); ?>">
						<div class="met-poster__frame">
							<span class="met-poster__tag"><?php esc_html_e( 'Announcement', 'met-hello-child' ); ?></span>
							<?php met_hello_child_home_media( get_post_thumbnail_id( $met_ann_post->ID ), 'met-poster', '' ); ?>
						</div>
						<div class="met-poster__body">
							<h3 class="met-poster__title"><?php echo esc_html( get_the_title( $met_ann_post ) ); ?></h3>
							<span class="met-poster__date"><?php echo esc_html( get_the_date( '', $met_ann_post ) ); ?></span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="met-empty-note met-reveal"><?php esc_html_e( 'Announcements will appear here soon.', 'met-hello-child' ); ?></p>
		<?php endif; ?>
	</div>
</section>
