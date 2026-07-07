<?php
/**
 * The template for displaying search results.
 *
 * Reuses the shared petrol hero band and card grid. Header and footer come from
 * get_header() / get_footer() (Elementor Theme Builder on this site).
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

global $wp_query;
$met_search_query = get_search_query();
$met_found        = (int) $wp_query->found_posts;
?>

<main id="content" class="site-main met-view met-search">

	<header class="met-hero">
		<div class="container met-hero__inner">
			<div class="met-hero__eyebrow">
				<span class="eyebrow eyebrow--on-dark"><?php echo esc_html__( 'Search', 'met-hello-child' ); ?></span>
			</div>
			<h1 class="met-hero__title">
				<?php
				/* translators: %s: the search query. */
				printf( esc_html__( 'Results for “%s”', 'met-hello-child' ), esc_html( $met_search_query ) );
				?>
			</h1>
		</div>
	</header>

	<section class="met-body">
		<div class="container">
			<?php if ( have_posts() ) : ?>

				<p class="met-search__summary">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: number of results found. */
							_n( '%s result found.', '%s results found.', $met_found, 'met-hello-child' ),
							number_format_i18n( $met_found )
						)
					);
					?>
				</p>

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
						'screen_reader_text' => esc_html__( 'Search results navigation', 'met-hello-child' ),
					)
				);
				?>

			<?php else : ?>

				<div class="met-empty">
					<p><?php echo esc_html__( 'No results matched your search. Try different keywords.', 'met-hello-child' ); ?></p>
					<?php get_search_form(); ?>
				</div>

			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
