<?php
/**
 * Homepage: the view test, its assets, the three image sizes, the Customizer
 * "Homepage" section (stats and images), and the data helpers each home partial
 * reads. The markup lives in page-templates/template-homepage.php and
 * template-parts/home/*. See DECISIONS D37 for why this is a Page Template
 * rather than front-page.php.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The homepage Page Template's relative path, used by both the enqueue gate and
 * the template assignment.
 */
const MET_HELLO_CHILD_HOME_TEMPLATE = 'page-templates/template-homepage.php';

/**
 * Whether the current request is the homepage view (the Page carrying the
 * Homepage template, whether or not it is set as the front page).
 *
 * @return bool
 */
function met_hello_child_is_home_view() {
	return is_page_template( MET_HELLO_CHILD_HOME_TEMPLATE );
}

/**
 * Default Customizer values for the stats band and the editorial images.
 *
 * @return array<string,mixed>
 */
function met_hello_child_home_defaults() {
	return array(
		'stat1_number' => '2001',
		'stat1_label'  => __( 'Incorporated', 'met-hello-child' ),
		'stat2_number' => '9',
		'stat2_label'  => __( 'Companies', 'met-hello-child' ),
		'stat3_number' => '3',
		'stat3_label'  => __( 'Industries', 'met-hello-child' ),
		'stat4_number' => '1,000+',
		'stat4_label'  => __( 'Employees', 'met-hello-child' ),
	);
}

/**
 * Register the three image sizes the homepage cards use. Cropped so the design's
 * aspect ratios hold and the browser never has to letterbox.
 */
function met_hello_child_register_home_image_sizes() {
	add_image_size( 'met-poster', 600, 750, true ); // 4:5 announcement posters.
	add_image_size( 'met-card', 700, 440, true );   // 16:10 company / feature cards.
	add_image_size( 'met-thumb', 208, 156, true );  // 4:3 newsroom list thumbnails.
}
add_action( 'after_setup_theme', 'met_hello_child_register_home_image_sizes' );

/**
 * Enqueue the homepage stylesheet and script, only on the homepage view.
 * home.css reads theme.json custom properties directly (printed sitewide by
 * core), so it needs no stylesheet dependency.
 */
function met_hello_child_enqueue_home_assets() {
	if ( ! met_hello_child_is_home_view() ) {
		return;
	}

	// Same dependency reasoning as inc/chrome.php: the parent's reset.css styles
	// bare `a` and `button` elements, and it must print before this file or those
	// rules win on source order.
	wp_enqueue_style(
		'met-hello-child-home',
		MET_HELLO_CHILD_URI . 'assets/css/home.css',
		array( 'hello-elementor', 'hello-elementor-theme-style' ),
		MET_HELLO_CHILD_VERSION
	);

	// A plain bool for in-footer, not the WP 6.3+ array form: this theme states
	// support from WP 6.0. See the same note in inc/scroll-top.php.
	wp_enqueue_script(
		'met-hello-child-home',
		MET_HELLO_CHILD_URI . 'assets/js/home.js',
		array(),
		MET_HELLO_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_home_assets' );

/**
 * A stat pair (number + label) from the Customizer, or null when the number is
 * empty. The caller skips null pairs and hides the band when all four are null.
 *
 * @param int $slot Stat slot, 1-4.
 * @return array{number:string,label:string}|null
 */
function met_hello_child_get_stat( $slot ) {
	$slot     = (int) $slot;
	$defaults = met_hello_child_home_defaults();

	$number = get_theme_mod( 'met_hello_child_home_stat' . $slot . '_number', $defaults[ 'stat' . $slot . '_number' ] );
	$label  = get_theme_mod( 'met_hello_child_home_stat' . $slot . '_label', $defaults[ 'stat' . $slot . '_label' ] );

	$number = trim( (string) $number );
	if ( '' === $number ) {
		return null;
	}

	return array(
		'number' => $number,
		'label'  => trim( (string) $label ),
	);
}

/**
 * All non-empty stat pairs, in order.
 *
 * @return array<int,array{number:string,label:string}>
 */
function met_hello_child_get_stats() {
	$stats = array();
	foreach ( array( 1, 2, 3, 4 ) as $slot ) {
		$stat = met_hello_child_get_stat( $slot );
		if ( null !== $stat ) {
			$stats[] = $stat;
		}
	}

	return $stats;
}

/**
 * A Customizer image ID for one of the editorial slots ('about'), or 0.
 *
 * @param string $slot Slot key.
 * @return int
 */
function met_hello_child_get_home_image_id( $slot ) {
	return (int) get_theme_mod( 'met_hello_child_home_image_' . sanitize_key( $slot ), 0 );
}

/**
 * Announcement posts for the poster grid. Reads the `announcements` category.
 * Returns an empty array when the category has no posts, so the partial can
 * render its designed empty state.
 *
 * @param int $count How many to return.
 * @return WP_Post[]
 */
function met_hello_child_get_announcements( $count = 4 ) {
	$term = get_category_by_slug( 'announcements' );
	if ( ! $term ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => (int) $count,
			'category'    => $term->term_id,
		)
	);
}

/**
 * Newsroom posts (latest published), excluding announcements so the two
 * sections do not repeat each other.
 *
 * @param int $count How many to return.
 * @return WP_Post[]
 */
function met_hello_child_get_newsroom_posts( $count = 5 ) {
	$args = array(
		'post_type'   => 'post',
		'post_status' => 'publish',
		'numberposts' => (int) $count,
	);

	$term = get_category_by_slug( 'announcements' );
	if ( $term ) {
		$args['category__not_in'] = array( $term->term_id );
	}

	return get_posts( $args );
}

/**
 * The nine /business/ child Pages, shaped for the companies grid.
 *
 * Return contract (stable, so a future swap to a real Company CPT is one
 * function): a list of items, each with id, title, url, sector (slug or ''),
 * sector_label, description, and image_id (0 when none). Ordered by menu_order
 * then title, matching the mega menu.
 *
 * @return array<int,array<string,mixed>>
 */
function met_hello_child_get_companies() {
	$met_business_parent = met_hello_child_business_parent_id();
	if ( ! $met_business_parent ) {
		return array();
	}

	$children = get_posts(
		array(
			'post_type'   => 'page',
			'post_parent' => $met_business_parent,
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);

	$companies = array();
	foreach ( $children as $child ) {
		$sector = met_hello_child_get_page_sector( $child->ID );

		$companies[] = array(
			'id'           => $child->ID,
			'title'        => get_the_title( $child ),
			'url'          => get_permalink( $child ),
			'sector'       => $sector,
			'sector_label' => met_hello_child_sector_label( $sector ),
			'description'  => get_the_excerpt( $child ),
			'image_id'     => get_post_thumbnail_id( $child->ID ),
		);
	}

	return $companies;
}

/**
 * The right-arrow icon used on every "view all" link and card CTA. Trusted
 * static markup.
 *
 * @return string
 */
function met_hello_child_home_arrow() {
	return '<svg class="met-home__arrow" viewBox="0 0 22 12" fill="none" aria-hidden="true" focusable="false"><path d="M1 6h19M15 1l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Print a cropped, responsive card image, or a sector-tinted empty state when
 * there is no image. No <img> is emitted in the empty case, so there is never a
 * broken image; the empty state is a gradient over the surface tone, overlaid
 * with the geometric pattern token, coloured by an optional sector modifier.
 *
 * @param int    $image_id Attachment ID, or 0 for the empty state.
 * @param string $size     Registered image size for the real image.
 * @param string $alt      Alt text for the real image (decorative -> '').
 * @param string $sector   Optional sector slug, tints the empty state.
 */
function met_hello_child_home_media( $image_id, $size, $alt = '', $sector = '' ) {
	if ( $image_id ) {
		echo wp_get_attachment_image(
			(int) $image_id,
			$size,
			false,
			array(
				'alt'      => $alt,
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		return;
	}

	$class = 'met-home__empty';
	if ( in_array( $sector, met_hello_child_sectors(), true ) ) {
		$class .= ' met-home__empty--' . $sector;
	}
	printf( '<span class="%s" aria-hidden="true"></span>', esc_attr( $class ) );
}

/**
 * Register the "Homepage" Customizer section: four stat number/label pairs
 * (live-previewed) and the About image. Shaped like the Scroll to Top section
 * in inc/scroll-top.php.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function met_hello_child_customize_register_home( $wp_customize ) {
	$defaults = met_hello_child_home_defaults();

	$wp_customize->add_section(
		'met_hello_child_home',
		array(
			'title'       => __( 'Homepage', 'met-hello-child' ),
			'description' => __( 'The stats band and editorial images on the homepage. Leave a stat number empty to hide that stat; empty all four to hide the band. If a caching plugin is active, purge the cache after changing images.', 'met-hello-child' ),
			'priority'    => 161,
		)
	);

	foreach ( array( 1, 2, 3, 4 ) as $slot ) {
		$wp_customize->add_setting(
			'met_hello_child_home_stat' . $slot . '_number',
			array(
				'type'              => 'theme_mod',
				'default'           => $defaults[ 'stat' . $slot . '_number' ],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'met_hello_child_home_stat' . $slot . '_number',
			array(
				'type'    => 'text',
				'section' => 'met_hello_child_home',
				/* translators: %d: stat slot number. */
				'label'   => sprintf( __( 'Stat %d number', 'met-hello-child' ), $slot ),
			)
		);

		$wp_customize->add_setting(
			'met_hello_child_home_stat' . $slot . '_label',
			array(
				'type'              => 'theme_mod',
				'default'           => $defaults[ 'stat' . $slot . '_label' ],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'met_hello_child_home_stat' . $slot . '_label',
			array(
				'type'    => 'text',
				'section' => 'met_hello_child_home',
				/* translators: %d: stat slot number. */
				'label'   => sprintf( __( 'Stat %d label', 'met-hello-child' ), $slot ),
			)
		);
	}

	$wp_customize->add_setting(
		'met_hello_child_home_image_about',
		array(
			'type'              => 'theme_mod',
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'met_hello_child_home_image_about',
			array(
				'section'     => 'met_hello_child_home',
				'label'       => __( 'About image', 'met-hello-child' ),
				'description' => __( 'The photo in the "Who we are" band. Landscape works best; it is shown at full quality and cropped to fit by the browser, not re-compressed.', 'met-hello-child' ),
				'mime_type'   => 'image',
			)
		)
	);

	foreach ( array(
		'action_tender' => __( 'Tenders card image', 'met-hello-child' ),
		'action_career' => __( 'Careers card image', 'met-hello-child' ),
	) as $met_home_slot => $met_home_label ) {
		$wp_customize->add_setting(
			'met_hello_child_home_image_' . $met_home_slot,
			array(
				'type'              => 'theme_mod',
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'met_hello_child_home_image_' . $met_home_slot,
				array(
					'section'     => 'met_hello_child_home',
					'label'       => $met_home_label,
					'description' => __( 'Optional background photo behind the card. Shown at full quality behind a gradient that keeps the text readable.', 'met-hello-child' ),
					'mime_type'   => 'image',
				)
			)
		);
	}
}
add_action( 'customize_register', 'met_hello_child_customize_register_home' );

/**
 * Enqueue the live-preview bindings inside the Customizer only.
 */
function met_hello_child_enqueue_home_customizer_preview() {
	wp_enqueue_script(
		'met-hello-child-home-customizer',
		MET_HELLO_CHILD_URI . 'assets/js/home-customizer.js',
		array( 'customize-preview' ),
		MET_HELLO_CHILD_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'met_hello_child_enqueue_home_customizer_preview' );
