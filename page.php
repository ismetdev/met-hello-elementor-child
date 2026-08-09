<?php
/**
 * Template for WordPress Pages.
 *
 * Hello Elementor ships no page.php, so every Page fell through to
 * template-parts/single.php, a template written for blog posts: it prints its
 * own <h1 class="entry-title">, calls comments_template(), and its <main>
 * carries class="site-main", which the parent's
 * `body:not([class*=elementor-page-]) .site-main { max-width:1140px }` rule
 * constrains once a Page stops being an Elementor page (D5, D27, and
 * PLAN/PRD-block-system.md section 3.7).
 *
 * This template fixes all of that in one file: no class="site-main" (so the
 * parent's width rule cannot match), no comments, and exactly one <h1> per
 * page, either from Page Hero or from a plain visible fallback title when no
 * hero variant is set, so a Page with no hero is never left with no title at
 * all. id="content" is kept so the parent's skip link still resolves.
 *
 * The fallback title is not always the whole intro: a Page without a hero
 * variant can still carry a `_met_hero_eyebrow` value (inc/page-hero.php),
 * printed above the title, since several designs call for a small eyebrow
 * label over a plain heading with no coloured band at all. Not every
 * design's top section is a hero band; forcing one onto a page whose design
 * calls for a plain intro was a first-draft mistake corrected here.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<main id="content" class="met-page">

		<?php if ( met_hello_child_page_has_hero() ) : ?>
			<?php met_hello_child_render_page_hero(); ?>
		<?php else : ?>
			<?php $met_page_eyebrow = get_post_meta( get_the_ID(), '_met_hero_eyebrow', true ); ?>
			<div class="met-page__intro">
				<?php if ( $met_page_eyebrow ) : ?>
					<span class="eyebrow"><?php echo esc_html( $met_page_eyebrow ); ?></span>
				<?php endif; ?>
				<h1 class="met-page__title"><?php the_title(); ?></h1>
			</div>
		<?php endif; ?>

		<div class="met-page__content">
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
		</div>

	</main>

	<?php
endwhile;

get_footer();
