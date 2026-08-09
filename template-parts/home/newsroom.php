<?php
/**
 * Homepage newsroom: one featured post beside a list of recent posts, from the
 * blog (excluding announcements). Renders a designed empty state when there are
 * no posts.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_news_posts = met_hello_child_get_newsroom_posts( 5 );
?>
<section class="met-section met-band met-news">
	<div class="met-container">
		<div class="met-news__head">
			<div class="met-section__head met-reveal">
				<span class="met-eyebrow"><?php esc_html_e( 'Newsroom', 'met-hello-child' ); ?></span>
				<h2><?php esc_html_e( 'News and activities', 'met-hello-child' ); ?></h2>
				<p><?php esc_html_e( 'Updates from across the group, refreshed regularly.', 'met-hello-child' ); ?></p>
			</div>
			<a class="met-btn met-btn--ghost met-reveal" href="<?php echo esc_url( home_url( '/news-announcement/' ) ); ?>"><?php esc_html_e( 'Visit the newsroom', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></a>
		</div>

		<?php if ( ! empty( $met_news_posts ) ) : ?>
			<?php
			$met_news_feat = array_shift( $met_news_posts );
			$met_feat_cats = get_the_category( $met_news_feat->ID );
			?>
			<div class="met-news__grid met-reveal">
				<a class="met-news-feat" href="<?php echo esc_url( get_permalink( $met_news_feat ) ); ?>">
					<div class="met-news-feat__media">
						<?php met_hello_child_home_media( get_post_thumbnail_id( $met_news_feat->ID ), 'met-card', '' ); ?>
					</div>
					<div class="met-news-feat__body">
						<?php if ( ! empty( $met_feat_cats ) ) : ?>
							<span class="met-cat"><?php echo esc_html( $met_feat_cats[0]->name ); ?></span>
						<?php endif; ?>
						<h3 class="met-news-feat__title"><?php echo esc_html( get_the_title( $met_news_feat ) ); ?></h3>
						<p><?php echo esc_html( get_the_excerpt( $met_news_feat ) ); ?></p>
					</div>
				</a>
				<?php if ( ! empty( $met_news_posts ) ) : ?>
					<div class="met-news__list">
						<?php foreach ( $met_news_posts as $met_news_item ) : ?>
							<?php $met_item_cats = get_the_category( $met_news_item->ID ); ?>
							<a class="met-news-item" href="<?php echo esc_url( get_permalink( $met_news_item ) ); ?>">
								<div class="met-news-item__thumb">
									<?php met_hello_child_home_media( get_post_thumbnail_id( $met_news_item->ID ), 'met-thumb', '' ); ?>
								</div>
								<div class="met-news-item__body">
									<?php if ( ! empty( $met_item_cats ) ) : ?>
										<span class="met-cat"><?php echo esc_html( $met_item_cats[0]->name ); ?></span>
									<?php endif; ?>
									<h4 class="met-news-item__title"><?php echo esc_html( get_the_title( $met_news_item ) ); ?></h4>
									<div class="met-news-item__meta"><?php echo esc_html( get_the_date( '', $met_news_item ) ); ?></div>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<p class="met-empty-note met-reveal"><?php esc_html_e( 'News will appear here soon.', 'met-hello-child' ); ?></p>
		<?php endif; ?>
	</div>
</section>
