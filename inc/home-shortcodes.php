<?php
/**
 * The shortcode bridge that lets the homepage move to Elementor while every
 * design the owner already approved (hero slider, announcement cards, the
 * portfolio gallery, the newsroom list) keeps rendering from the exact same
 * code as before. See PLAN/PRD-homepage-elementor.md and DECISIONS D47.
 *
 * The files inc/homepage.php, page-templates/template-homepage.php and every
 * partial in template-parts/home/ are untouched and stay on disk as the
 * rollback: switch
 * the Page back to the Homepage template and they render again exactly as
 * they did. This file only reads from them (the same data helpers) and, for
 * hero/announcements/companies/newsroom, echoes the same template parts or the
 * same markup, so there is nothing to keep in sync by hand.
 *
 * Six shortcodes:
 * - [met_home_hero]            The slider, unchanged (no separate furniture)
 * - [met_home_announcements]   The poster grid only, no eyebrow/heading/button
 * - [met_home_newsroom]        The feature + list only, no eyebrow/heading/button
 * - [met_companies]            The filter bar + grid, with order/exclude/filters
 * - [met_tenders]              New: theme-styled rows over metcpt_tender
 * - [met_careers]              New: theme-styled rows over metcpt_career
 *
 * Tenders and careers read MetCPT's post types and meta keys but render theme
 * markup, so MetCPT itself is never modified and keeps its own release cycle.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All six shortcode tags this file owns, for the shared detection/enqueue
 * logic below.
 *
 * @return string[]
 */
function met_hello_child_home_shortcode_tags() {
	return array(
		'met_home_hero',
		'met_home_announcements',
		'met_home_newsroom',
		'met_companies',
		'met_tenders',
		'met_careers',
	);
}

/**
 * Register the six shortcodes.
 */
function met_hello_child_register_home_shortcodes() {
	add_shortcode( 'met_home_hero', 'met_hello_child_shortcode_home_hero' );
	add_shortcode( 'met_home_announcements', 'met_hello_child_shortcode_home_announcements' );
	add_shortcode( 'met_home_newsroom', 'met_hello_child_shortcode_home_newsroom' );
	add_shortcode( 'met_companies', 'met_hello_child_shortcode_companies' );
	add_shortcode( 'met_tenders', 'met_hello_child_shortcode_tenders' );
	add_shortcode( 'met_careers', 'met_hello_child_shortcode_careers' );
}
add_action( 'init', 'met_hello_child_register_home_shortcodes' );

/**
 * Whether the current singular view carries any of the six shortcodes, in
 * post_content or in Elementor's _elementor_data. Same detection shape as
 * met_hello_child_page_has_shortcode() in inc/listing.php, generalised to a
 * list of tags in one pass.
 *
 * @return bool
 */
function met_hello_child_page_has_any_home_shortcode() {
	if ( ! is_singular() ) {
		return false;
	}

	$met_home_sc_post = get_queried_object();
	if ( ! $met_home_sc_post instanceof WP_Post ) {
		return false;
	}

	$met_home_sc_content = (string) $met_home_sc_post->post_content;
	$met_home_sc_data    = get_post_meta( $met_home_sc_post->ID, '_elementor_data', true );
	$met_home_sc_data    = is_string( $met_home_sc_data ) ? $met_home_sc_data : '';

	foreach ( met_hello_child_home_shortcode_tags() as $met_home_sc_tag ) {
		if ( has_shortcode( $met_home_sc_content, $met_home_sc_tag ) ) {
			return true;
		}
		if ( '' !== $met_home_sc_data && false !== strpos( $met_home_sc_data, '[' . $met_home_sc_tag ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Enqueue home.css and home.js when any of the six shortcodes is present,
 * independent of met_hello_child_enqueue_home_assets() in inc/homepage.php
 * (which stays gated on the old Page Template and is the rollback path).
 *
 * The dependency array matters: see inc/homepage.php's own note and
 * DOCS/STATE.md, "A child stylesheet must declare the parent's stylesheets as
 * dependencies."
 */
function met_hello_child_enqueue_home_shortcode_assets() {
	if ( ! met_hello_child_page_has_any_home_shortcode() ) {
		return;
	}

	wp_enqueue_style(
		'met-hello-child-home',
		MET_HELLO_CHILD_URI . 'assets/css/home.css',
		array( 'hello-elementor', 'hello-elementor-theme-style' ),
		MET_HELLO_CHILD_VERSION
	);

	wp_enqueue_script(
		'met-hello-child-home',
		MET_HELLO_CHILD_URI . 'assets/js/home.js',
		array(),
		MET_HELLO_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_home_shortcode_assets' );

/**
 * Wrap the front page's rendered content in <div class="met-home">.
 *
 * Every rule in home.css is scoped under .met-home, and home.js finds its
 * whole working area with one `document.querySelector( '.met-home' )` before
 * running any of its own querySelectorAll() calls inside it (hero, reveal,
 * company filter). On the old Page Template that element was <main
 * class="met-home"> in page-templates/template-homepage.php. On the Elementor
 * homepage there is no single wrapping element any more; Elementor's own
 * template calls the_content() the same way any Page does (see
 * elementor/modules/page-templates/templates/header-footer.php), so wrapping
 * its filtered output here reproduces the same one-ancestor structure with no
 * changes to home.css or home.js.
 *
 * Scoped tightly: only the front page, and only when a home shortcode is
 * actually present, so this is inert on every other page and inert again if
 * the homepage is ever pointed at different content that uses none of them.
 *
 * @param string $met_home_content Filtered post content.
 * @return string
 */
function met_hello_child_wrap_home_content( $met_home_content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $met_home_content;
	}

	if ( ! is_front_page() || ! met_hello_child_page_has_any_home_shortcode() ) {
		return $met_home_content;
	}

	return '<div class="met-home met-home--js">' . $met_home_content . '</div>';
}
add_filter( 'the_content', 'met_hello_child_wrap_home_content', 20 );

/**
 * [met_home_hero]: the slider, unchanged. No separate furniture; the design
 * has none for this section.
 *
 * @return string
 */
function met_hello_child_shortcode_home_hero() {
	ob_start();
	get_template_part( 'template-parts/home/hero' );
	return ob_get_clean();
}

/**
 * [met_home_announcements atts="count"]: the poster grid (or its empty
 * state) only. The eyebrow, heading, description and "View all" button are
 * Elementor furniture now; this renders the same markup
 * template-parts/home/announcements.php prints inside its
 * `.met-ann__grid`/`.met-empty-note`, which are styled as standalone
 * selectors in home.css, not nested under `.met-ann`.
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function met_hello_child_shortcode_home_announcements( $atts ) {
	$atts = shortcode_atts( array( 'count' => '4' ), $atts, 'met_home_announcements' );

	$met_ann_posts = met_hello_child_get_announcements( max( 1, (int) $atts['count'] ) );

	ob_start();
	if ( ! empty( $met_ann_posts ) ) :
		?>
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
		<?php
	else :
		?>
		<p class="met-empty-note met-reveal"><?php esc_html_e( 'Announcements will appear here soon.', 'met-hello-child' ); ?></p>
		<?php
	endif;
	return ob_get_clean();
}

/**
 * [met_home_newsroom atts="count"]: the featured post + list only, same
 * split as above.
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function met_hello_child_shortcode_home_newsroom( $atts ) {
	$atts = shortcode_atts( array( 'count' => '5' ), $atts, 'met_home_newsroom' );

	$met_news_posts = met_hello_child_get_newsroom_posts( max( 2, (int) $atts['count'] ) );

	ob_start();
	if ( ! empty( $met_news_posts ) ) :
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
		<?php
	else :
		?>
		<p class="met-empty-note met-reveal"><?php esc_html_e( 'News will appear here soon.', 'met-hello-child' ); ?></p>
		<?php
	endif;
	return ob_get_clean();
}

/**
 * [met_companies order="slug,slug,..." exclude="slug,slug" filters="yes|no"]
 *
 * Content only: the filter bar and the card grid, matching
 * template-parts/home/companies.php exactly bar the section head, which is
 * Elementor furniture now.
 *
 * order/exclude take Page slugs, never IDs (D44: IDs differ between local and
 * staging). Slugs named in `order` come first in that exact sequence; every
 * other company keeps its existing relative order and is appended after, so a
 * tenth /business/ child Page never silently disappears because nobody added
 * its slug to the attribute.
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function met_hello_child_shortcode_companies( $atts ) {
	$atts = shortcode_atts(
		array(
			'order'   => '',
			'exclude' => '',
			'filters' => 'yes',
		),
		$atts,
		'met_companies'
	);

	$met_co_companies = met_hello_child_get_companies();
	if ( empty( $met_co_companies ) ) {
		return '';
	}

	foreach ( $met_co_companies as $met_co_key => $met_co_company ) {
		$met_co_companies[ $met_co_key ]['slug'] = get_post_field( 'post_name', $met_co_company['id'] );
	}

	$met_co_exclude = array_filter( array_map( 'sanitize_title', explode( ',', $atts['exclude'] ) ) );
	if ( $met_co_exclude ) {
		$met_co_companies = array_values(
			array_filter(
				$met_co_companies,
				static function ( $met_co_company ) use ( $met_co_exclude ) {
					return ! in_array( $met_co_company['slug'], $met_co_exclude, true );
				}
			)
		);
	}

	$met_co_order = array_filter( array_map( 'sanitize_title', explode( ',', $atts['order'] ) ) );
	if ( $met_co_order && $met_co_companies ) {
		$met_co_by_slug = array();
		foreach ( $met_co_companies as $met_co_company ) {
			$met_co_by_slug[ $met_co_company['slug'] ] = $met_co_company;
		}

		$met_co_ordered = array();
		foreach ( $met_co_order as $met_co_slug ) {
			if ( isset( $met_co_by_slug[ $met_co_slug ] ) ) {
				$met_co_ordered[] = $met_co_by_slug[ $met_co_slug ];
				unset( $met_co_by_slug[ $met_co_slug ] );
			}
		}

		$met_co_companies = array_merge( $met_co_ordered, array_values( $met_co_by_slug ) );
	}

	if ( empty( $met_co_companies ) ) {
		return '';
	}

	$met_co_show_filters = ! in_array( strtolower( (string) $atts['filters'] ), array( 'no', 'false', '0' ), true );

	ob_start();
	if ( $met_co_show_filters ) :
		?>
		<div class="met-companies__filters met-reveal" role="group" aria-label="<?php esc_attr_e( 'Filter by industry', 'met-hello-child' ); ?>" hidden>
			<button type="button" data-filter="all" aria-pressed="true"><?php esc_html_e( 'All', 'met-hello-child' ); ?></button>
			<?php foreach ( met_hello_child_sectors() as $met_co_sector ) : ?>
				<button type="button" data-filter="<?php echo esc_attr( $met_co_sector ); ?>" aria-pressed="false"><span class="met-cdot met-cdot--<?php echo esc_attr( $met_co_sector ); ?>"></span><?php echo esc_html( met_hello_child_sector_label( $met_co_sector ) ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php
	endif;
	?>
	<div class="met-companies__grid">
		<?php foreach ( $met_co_companies as $met_company ) : ?>
			<?php $met_co_mod = $met_company['sector'] ? ' met-co-card--' . $met_company['sector'] : ''; ?>
			<a class="met-co-card<?php echo esc_attr( $met_co_mod ); ?> met-reveal" data-cat="<?php echo esc_attr( $met_company['sector'] ); ?>" href="<?php echo esc_url( $met_company['url'] ); ?>">
				<div class="met-co-card__media">
					<?php if ( $met_company['sector_label'] ) : ?>
						<span class="met-co-badge"><?php echo esc_html( $met_company['sector_label'] ); ?></span>
					<?php endif; ?>
					<?php met_hello_child_home_media( $met_company['image_id'], 'met-card', '', $met_company['sector'] ); ?>
				</div>
				<div class="met-co-card__body">
					<h3 class="met-co-card__title"><?php echo esc_html( $met_company['title'] ); ?></h3>
					<?php if ( $met_company['description'] ) : ?>
						<p class="met-co-card__desc"><?php echo esc_html( $met_company['description'] ); ?></p>
					<?php endif; ?>
					<span class="met-co-card__link"><?php esc_html_e( 'View company', 'met-hello-child' ); ?> <?php echo met_hello_child_home_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?></span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * [met_tenders count="4"]: open/closing-soon tenders from MetCPT's
 * metcpt_tender post type. Reads the plugin's own post type and meta keys
 * (tender_close_date, tender_ref) but renders theme markup and theme tokens,
 * so MetCPT is a data source only, never modified. Same meta_query shape
 * MetCPT's own careers_preview shortcode uses for "still open".
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function met_hello_child_shortcode_tenders( $atts ) {
	if ( ! post_type_exists( 'metcpt_tender' ) ) {
		return '';
	}

	$atts = shortcode_atts( array( 'count' => '4' ), $atts, 'met_tenders' );

	$met_tender_query = new WP_Query(
		array(
			'post_type'      => 'metcpt_tender',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $atts['count'] ),
			'no_found_rows'  => true,
			'meta_key'       => 'tender_close_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small, capped list on the homepage only.
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- "still open" is the whole point of this shortcode.
				array(
					'key'     => 'tender_close_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		)
	);

	ob_start();
	if ( $met_tender_query->have_posts() ) :
		?>
		<div class="met-tender-list met-reveal">
			<?php
			while ( $met_tender_query->have_posts() ) :
				$met_tender_query->the_post();
				$met_tender_close = get_post_meta( get_the_ID(), 'tender_close_date', true );
				$met_tender_ref   = get_post_meta( get_the_ID(), 'tender_ref', true );
				$met_tender_days  = $met_tender_close ? (int) ( ( strtotime( $met_tender_close ) - strtotime( current_time( 'Y-m-d' ) ) ) / DAY_IN_SECONDS ) : null;
				$met_tender_soon  = null !== $met_tender_days && $met_tender_days <= (int) get_option( 'metcpt_closing_soon_days', 7 );
				?>
				<a class="met-tender-row" href="<?php the_permalink(); ?>">
					<span class="met-tender-row__ref"><?php echo esc_html( $met_tender_ref ? $met_tender_ref : '—' ); ?></span>
					<span class="met-tender-row__title"><?php the_title(); ?></span>
					<span class="met-tender-row__badge<?php echo $met_tender_soon ? ' met-tender-row__badge--soon' : ''; ?>">
						<?php echo $met_tender_soon ? esc_html__( 'Closing soon', 'met-hello-child' ) : esc_html__( 'Open', 'met-hello-child' ); ?>
					</span>
					<span class="met-tender-row__date">
						<?php
						/* translators: %s: tender closing date. */
						echo esc_html( sprintf( __( 'Closes %s', 'met-hello-child' ), $met_tender_close ? mysql2date( get_option( 'date_format' ), $met_tender_close ) : __( 'TBC', 'met-hello-child' ) ) );
						?>
					</span>
				</a>
			<?php endwhile; ?>
		</div>
		<?php
		wp_reset_postdata();
	else :
		?>
		<p class="met-empty-note met-reveal"><?php esc_html_e( 'No open tenders at this time.', 'met-hello-child' ); ?></p>
		<?php
	endif;
	return ob_get_clean();
}

/**
 * [met_careers count="4"]: open roles from MetCPT's metcpt_career post type.
 * Same data-source-only relationship to MetCPT as met_tenders above.
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function met_hello_child_shortcode_careers( $atts ) {
	if ( ! post_type_exists( 'metcpt_career' ) ) {
		return '';
	}

	$atts = shortcode_atts( array( 'count' => '4' ), $atts, 'met_careers' );

	$met_career_query = new WP_Query(
		array(
			'post_type'      => 'metcpt_career',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $atts['count'] ),
			'no_found_rows'  => true,
			'meta_key'       => 'career_close_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small, capped list on the homepage only.
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- "still open" is the whole point of this shortcode.
				array(
					'key'     => 'career_close_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		)
	);

	ob_start();
	if ( $met_career_query->have_posts() ) :
		?>
		<div class="met-career-list met-reveal">
			<?php
			while ( $met_career_query->have_posts() ) :
				$met_career_query->the_post();
				$met_career_dept = get_post_meta( get_the_ID(), 'career_department', true );
				$met_career_loc  = get_post_meta( get_the_ID(), 'career_location', true );
				$met_career_type = get_post_meta( get_the_ID(), 'career_type', true );
				$met_career_meta = array_filter( array( $met_career_dept, $met_career_loc, $met_career_type ) );
				?>
				<a class="met-career-row" href="<?php the_permalink(); ?>">
					<span class="met-career-row__title"><?php the_title(); ?></span>
					<?php if ( $met_career_meta ) : ?>
						<span class="met-career-row__meta"><?php echo esc_html( implode( ' · ', $met_career_meta ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endwhile; ?>
		</div>
		<?php
		wp_reset_postdata();
	else :
		?>
		<p class="met-empty-note met-reveal"><?php esc_html_e( 'No open positions at this time.', 'met-hello-child' ); ?></p>
		<?php
	endif;
	return ob_get_clean();
}
