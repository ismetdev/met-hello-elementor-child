<?php
/**
 * The template for displaying author profile archives.
 *
 * A petrol hero band with the author's avatar, name, post count, bio, and social
 * links, followed by the shared card grid of their posts. Header and footer come
 * from get_header() / get_footer() (Elementor Theme Builder on this site).
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

$met_author_id    = (int) get_queried_object_id();
$met_author_name  = get_the_author_meta( 'display_name', $met_author_id );
$met_author_bio   = get_the_author_meta( 'description', $met_author_id );
$met_author_count = (int) count_user_posts( $met_author_id, 'post' );
$met_author_links = met_hello_child_get_author_links( $met_author_id );
?>

<main id="content" class="site-main met-view met-author">

	<header class="met-hero">
		<div class="container met-hero__inner">
			<div class="met-author__id">
				<div class="met-author__avatar">
					<?php echo get_avatar( $met_author_id, 96, '', $met_author_name ); ?>
				</div>
				<div>
					<div class="met-hero__eyebrow">
						<span class="eyebrow eyebrow--on-dark"><?php echo esc_html__( 'Author', 'met-hello-child' ); ?></span>
					</div>
					<h1 class="met-hero__title"><?php echo esc_html( $met_author_name ); ?></h1>
					<div class="met-hero__meta">
						<span>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: number of articles by the author. */
									_n( '%s article', '%s articles', $met_author_count, 'met-hello-child' ),
									number_format_i18n( $met_author_count )
								)
							);
							?>
						</span>
					</div>
				</div>
			</div>

			<?php if ( $met_author_bio ) : ?>
				<p class="met-author__bio"><?php echo esc_html( $met_author_bio ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $met_author_links ) ) : ?>
				<div class="met-author__social">
					<?php foreach ( $met_author_links as $met_link ) : ?>
						<a href="<?php echo esc_url( $met_link['url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow" aria-label="<?php echo esc_attr( $met_link['label'] ); ?>">
							<?php
							// Trusted static SVG markup.
							echo met_hello_child_social_icon( $met_link['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<section class="met-body">
		<div class="container">
			<?php if ( have_posts() ) : ?>

				<div class="met-listing">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/met-card' );
					endwhile;
					?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'           => 1,
						'prev_text'          => esc_html__( 'Previous', 'met-hello-child' ),
						'next_text'          => esc_html__( 'Next', 'met-hello-child' ),
						'screen_reader_text' => esc_html__( 'Posts navigation', 'met-hello-child' ),
					)
				);
				?>

			<?php else : ?>

				<div class="met-empty">
					<p><?php echo esc_html__( 'This author has not published any articles yet.', 'met-hello-child' ); ?></p>
				</div>

			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
