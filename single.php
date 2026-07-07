<?php
/**
 * The template for displaying single blog Posts.
 *
 * Applies to native WordPress Posts only. Haraka CPT singles are rendered by the
 * Haraka plugin's own templates and never reach this file. The header and footer
 * come from get_header() / get_footer(), which on this site are produced by the
 * Elementor Theme Builder — so no header/footer markup is hardcoded here.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

while ( have_posts() ) :
	the_post();

	$met_primary_term = met_hello_child_get_primary_term();
	$met_reading_time = met_hello_child_reading_time();
	$met_permalink    = get_permalink();
	$met_author_url   = get_author_posts_url( (int) get_the_author_meta( 'ID' ) );

	// Back button points to the post's category archive; falls back to the
	// filterable Newsroom URL only when the post has no category.
	$met_term_link = $met_primary_term ? get_term_link( $met_primary_term ) : '';
	if ( $met_primary_term && ! is_wp_error( $met_term_link ) ) {
		$met_back_url   = $met_term_link;
		/* translators: %s: category name. */
		$met_back_label = sprintf( __( 'Back to %s', 'met-hello-child' ), $met_primary_term->name );
	} else {
		$met_back_url   = met_hello_child_back_link_url();
		$met_back_label = __( 'Back to Newsroom', 'met-hello-child' );
	}
	?>

	<main id="content" <?php post_class( 'site-main met-view met-single' ); ?>>
		<article class="met-single__article">

			<!-- Post hero -->
			<header class="post-hero">
				<div class="container post-hero__inner">
					<div class="post-hero__eyebrow">
						<span class="eyebrow eyebrow--on-dark">
							<?php
							echo esc_html(
								$met_primary_term
									? $met_primary_term->name
									: __( 'Newsroom', 'met-hello-child' )
							);
							?>
						</span>
					</div>

					<?php the_title( '<h1 class="post-hero__title">', '</h1>' ); ?>

					<div class="post-hero__meta">
						<span class="post-hero__meta-item">
							<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M5 20c0-3.3 3-5.5 7-5.5s7 2.2 7 5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
							<?php
							printf(
								/* translators: %s: post author name, linked to their archive. */
								esc_html__( 'By %s', 'met-hello-child' ),
								'<a class="post-hero__author" href="' . esc_url( $met_author_url ) . '">' . esc_html( get_the_author() ) . '</a>'
							);
							?>
						</span>
						<span class="post-hero__meta-sep"></span>
						<span class="post-hero__meta-item">
							<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><rect x="4" y="5" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M4 9h16M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
							<?php echo esc_html( get_the_date() ); ?>
						</span>
						<span class="post-hero__meta-sep"></span>
						<span class="post-hero__meta-item">
							<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: estimated reading time in minutes. */
									_n( '%s min read', '%s min read', $met_reading_time, 'met-hello-child' ),
									number_format_i18n( $met_reading_time )
								)
							);
							?>
						</span>
					</div>
				</div>
			</header>

			<?php
			// Feature image — render the frame only when a featured image exists.
			// No image means no frame at all: no gap, no placeholder (decision 3).
			if ( has_post_thumbnail() ) :
				$met_thumb_alt = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );
				if ( '' === trim( (string) $met_thumb_alt ) ) {
					$met_thumb_alt = get_the_title();
				}
				?>
				<div class="post-feature">
					<div class="container">
						<div class="post-feature__frame">
							<?php // the_post_thumbnail() escapes the alt attribute itself — pass the raw value. ?>
							<?php the_post_thumbnail( 'large', array( 'alt' => $met_thumb_alt ) ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Article body -->
			<div class="post-body">
				<div class="container">
					<div class="post-body__inner">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="post-body__pages">' . esc_html__( 'Pages:', 'met-hello-child' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>

					<!-- Post end row -->
					<div class="post-end">
						<a class="post-back" href="<?php echo esc_url( $met_back_url ); ?>">
							<svg viewBox="0 0 22 12" fill="none" aria-hidden="true" focusable="false"><path d="M21 6H2M7 1L2 6l5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php echo esc_html( $met_back_label ); ?>
						</a>
						<div class="post-share">
							<span class="post-share__label"><?php echo esc_html__( 'Share', 'met-hello-child' ); ?></span>
							<?php foreach ( met_hello_child_get_share_links( $met_permalink, get_the_title() ) as $met_share ) : ?>
								<a href="<?php echo esc_url( $met_share['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $met_share['label'] ); ?>">
									<?php
									// Trusted static SVG markup.
									echo met_hello_child_social_icon( $met_share['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>

				</div>
			</div>

		</article>
	</main>

	<?php
endwhile;

get_footer();
