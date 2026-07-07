<?php
/**
 * The template for displaying 404 (not found) pages.
 *
 * Reuses the shared petrol hero band, then offers ways forward: home, the
 * newsroom archive, and a search form. Header and footer come from get_header()
 * / get_footer() (Elementor Theme Builder on this site).
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();
?>

<main id="content" class="site-main met-view met-404">

	<header class="met-hero">
		<div class="container met-hero__inner">
			<div class="met-hero__eyebrow">
				<span class="eyebrow eyebrow--on-dark"><?php echo esc_html__( 'Error 404', 'met-hello-child' ); ?></span>
			</div>
			<h1 class="met-hero__title"><?php echo esc_html__( 'Page not found', 'met-hello-child' ); ?></h1>
			<div class="met-hero__desc">
				<?php echo esc_html__( 'The page you are looking for may have moved, been renamed, or no longer exists.', 'met-hello-child' ); ?>
			</div>
		</div>
	</header>

	<section class="met-body">
		<div class="container">
			<div class="met-404__actions">
				<a class="met-404__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo esc_html__( 'Back to homepage', 'met-hello-child' ); ?>
				</a>
				<a class="met-404__btn met-404__btn--ghost" href="<?php echo esc_url( met_hello_child_back_link_url() ); ?>">
					<svg viewBox="0 0 22 12" fill="none" aria-hidden="true" focusable="false"><path d="M21 6H2M7 1L2 6l5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<?php echo esc_html__( 'Back to Newsroom', 'met-hello-child' ); ?>
				</a>
			</div>

			<p class="met-404__text"><?php echo esc_html__( 'Or try searching for what you need:', 'met-hello-child' ); ?></p>
			<?php get_search_form(); ?>
		</div>
	</section>

</main>

<?php
get_footer();
