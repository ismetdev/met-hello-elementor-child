<?php
/**
 * The template for displaying category, tag, and date archives of blog Posts.
 *
 * Renders a compact petrol header band plus a uniform responsive card grid that
 * matches the single-post look. Native-post archives only — Haraka CPT archives
 * are handled by the plugin and never reach this file. Header and footer come
 * from get_header() / get_footer() (Elementor Theme Builder on this site).
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

// Context label + an unwrapped archive title (get_the_archive_title() prefixes
// "Category:" / "Tag:" etc., which we don't want in the design).
if ( is_category() ) {
	$met_archive_label = __( 'Category', 'met-hello-child' );
	$met_archive_title = single_cat_title( '', false );
} elseif ( is_tag() ) {
	$met_archive_label = __( 'Tag', 'met-hello-child' );
	$met_archive_title = single_tag_title( '', false );
} else {
	$met_archive_label = __( 'Newsroom', 'met-hello-child' );
	$met_archive_title = get_the_archive_title();
}

$met_archive_description = get_the_archive_description();
?>

<main id="content" class="site-main met-view met-archive">

	<header class="met-hero">
		<div class="container met-hero__inner">
			<div class="met-hero__eyebrow">
				<span class="eyebrow eyebrow--on-dark"><?php echo esc_html( $met_archive_label ); ?></span>
			</div>
			<h1 class="met-hero__title"><?php echo esc_html( $met_archive_title ); ?></h1>
			<?php if ( $met_archive_description ) : ?>
				<div class="met-hero__desc"><?php echo wp_kses_post( $met_archive_description ); ?></div>
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
					<p><?php echo esc_html__( 'No posts found in this archive yet.', 'met-hello-child' ); ?></p>
				</div>

			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
