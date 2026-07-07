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
	$met_share_url    = rawurlencode( $met_permalink );
	$met_share_title  = rawurlencode( get_the_title() );
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
							/* translators: %s: post author name. */
							printf( esc_html__( 'By %s', 'met-hello-child' ), esc_html( get_the_author() ) );
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
						<a class="post-back" href="<?php echo esc_url( met_hello_child_back_link_url() ); ?>">
							<svg viewBox="0 0 22 12" fill="none" aria-hidden="true" focusable="false"><path d="M21 6H2M7 1L2 6l5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php echo esc_html__( 'Back to Newsroom', 'met-hello-child' ); ?>
						</a>
						<div class="post-share">
							<span class="post-share__label"><?php echo esc_html__( 'Share', 'met-hello-child' ); ?></span>
							<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $met_share_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Share on Facebook', 'met-hello-child' ); ?>"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h2.5l.5-3H13v-2c0-.6.4-1 1-1z"/></svg></a>
							<a href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $met_share_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Share on LinkedIn', 'met-hello-child' ); ?>"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M6.5 8.5v9H4v-9h2.5zM5.2 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM20 17.5h-2.5v-4.7c0-1.2-.4-2-1.5-2-.8 0-1.3.6-1.5 1.1-.1.2-.1.5-.1.7v4.9H12s.03-8.2 0-9h2.5v1.3c.3-.5 1-1.2 2.3-1.2 1.7 0 3.2 1.1 3.2 3.6v5.3z"/></svg></a>
							<a href="<?php echo esc_url( 'https://api.whatsapp.com/send?text=' . $met_share_title . '%20' . $met_share_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Share on WhatsApp', 'met-hello-child' ); ?>"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M4 20l1.3-4A8 8 0 1112 20a8 8 0 01-4-1L4 20z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5.4 0 .7-.4.7-.8v-1c0-.3-.2-.5-.5-.6l-1-.2c-.3 0-.5.1-.7.3-.6-.3-1.1-.8-1.4-1.4.2-.2.3-.4.3-.7l-.2-1c-.1-.3-.3-.5-.6-.5h-1c-.4 0-.8.3-.8.7z" fill="currentColor"/></svg></a>
						</div>
					</div>

				</div>
			</div>

		</article>
	</main>

	<?php
endwhile;

get_footer();
