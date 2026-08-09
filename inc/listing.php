<?php
/**
 * The [met_posts] shortcode: a token-styled list of standard Posts, filtered by
 * category slug, for use inside an Elementor page body.
 *
 * Why a shortcode and not an addon widget: Elementor and Essential Addons query
 * controls store category *term IDs*, which differ between local and staging, so
 * an imported page lists the wrong posts. A slug survives the move as plain text.
 * See DECISIONS D44 and DOCS/DEPLOY-TO-STAGING.md.
 *
 * The markup mirrors the homepage newsroom partial (template-parts/home/), and
 * listing.css reads theme.json custom properties directly, the way home.css does,
 * not the tokens.css alias layer that theme.css uses. Keeping the two token
 * layers apart is deliberate; mixing them is how D36 happened.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed listing layouts. An unrecognised value falls back to 'grid'.
 *
 * @return string[]
 */
function met_hello_child_listing_layouts() {
	return array( 'grid', 'list', 'album' );
}

/**
 * Register the [met_posts] shortcode.
 */
function met_hello_child_register_listing_shortcode() {
	add_shortcode( 'met_posts', 'met_hello_child_render_listing' );
}
add_action( 'init', 'met_hello_child_register_listing_shortcode' );

/**
 * Render a filtered list of Posts.
 *
 * Returns a string; never echoes. An empty result returns a designed empty-state
 * note, never an empty container. Unknown category slugs resolve to no posts
 * rather than to the whole blog, so a typo never silently dumps every post.
 *
 * @param array<string,string>|string $atts Shortcode attributes.
 * @return string
 */
function met_hello_child_render_listing( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'         => '',
			'exclude_category' => '',
			'count'            => '9',
			'layout'           => 'grid',
			'columns'          => '3',
			'featured'         => 'no',
			'paged'            => 'no',
			'empty'            => '',
		),
		$atts,
		'met_posts'
	);

	$layout   = in_array( $atts['layout'], met_hello_child_listing_layouts(), true ) ? $atts['layout'] : 'grid';
	$columns  = min( 4, max( 2, (int) $atts['columns'] ) );
	$count    = (int) $atts['count'];
	$featured = met_hello_child_listing_is_yes( $atts['featured'] );
	$paged    = met_hello_child_listing_is_yes( $atts['paged'] );
	$empty    = sanitize_text_field( $atts['empty'] );

	// 'album' and 'list' show one column of full-width rows; 'featured' only makes
	// sense in a grid, so it is ignored elsewhere.
	if ( 'grid' !== $layout ) {
		$featured = false;
	}

	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'posts_per_page'      => $count,
	);

	if ( $paged ) {
		$query_args['paged'] = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	} else {
		$query_args['no_found_rows'] = true;
	}

	$tax_query = met_hello_child_listing_tax_query( $atts['category'], $atts['exclude_category'] );
	if ( false === $tax_query ) {
		// A category was named but resolved to no known term. Show nothing rather
		// than falling back to every post.
		return met_hello_child_listing_empty( $empty );
	}
	if ( ! empty( $tax_query ) ) {
		$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- category filter is the whole point of this shortcode.
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return met_hello_child_listing_empty( $empty );
	}

	$classes = array(
		'met-list',
		'met-list--' . $layout,
	);
	if ( 'list' !== $layout ) {
		$classes[] = 'met-list--cols-' . $columns;
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<?php
		$index = 0;
		while ( $query->have_posts() ) :
			$query->the_post();

			$is_feature = $featured && 0 === $index;
			get_template_part(
				'template-parts/listing-card',
				null,
				array(
					'layout'     => $layout,
					'is_feature' => $is_feature,
				)
			);

			++$index;
		endwhile;
		?>
	</div>
	<?php
	if ( $paged ) {
		echo wp_kses_post( met_hello_child_listing_pagination( $query ) );
	}

	wp_reset_postdata();

	return (string) ob_get_clean();
}

/**
 * Build the tax_query clause from category slug lists.
 *
 * @param string $include_slugs Comma-separated slugs to include.
 * @param string $exclude_slugs Comma-separated slugs to exclude.
 * @return array<int,mixed>|false Empty array when no filtering applies, an array
 *                                of clauses when it does, or false when an
 *                                include slug was given but matched no term.
 */
function met_hello_child_listing_tax_query( $include_slugs, $exclude_slugs ) {
	$include_ids = met_hello_child_listing_slugs_to_ids( $include_slugs );
	$exclude_ids = met_hello_child_listing_slugs_to_ids( $exclude_slugs );

	// An include list was requested but nothing resolved: signal "show nothing".
	if ( '' !== trim( (string) $include_slugs ) && empty( $include_ids ) ) {
		return false;
	}

	$clauses = array();

	if ( ! empty( $include_ids ) ) {
		$clauses[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $include_ids,
		);
	}

	if ( ! empty( $exclude_ids ) ) {
		$clauses[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $exclude_ids,
			'operator' => 'NOT IN',
		);
	}

	if ( count( $clauses ) > 1 ) {
		$clauses['relation'] = 'AND';
	}

	return $clauses;
}

/**
 * Resolve a comma-separated slug list to category term IDs, dropping unknowns.
 *
 * @param string $slugs Comma-separated category slugs.
 * @return int[]
 */
function met_hello_child_listing_slugs_to_ids( $slugs ) {
	$ids = array();

	foreach ( explode( ',', (string) $slugs ) as $slug ) {
		$slug = sanitize_title( trim( $slug ) );
		if ( '' === $slug ) {
			continue;
		}

		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) $term->term_id;
		}
	}

	return $ids;
}

/**
 * Whether a yes/no attribute reads as yes.
 *
 * @param string $value Raw value.
 * @return bool
 */
function met_hello_child_listing_is_yes( $value ) {
	return in_array( strtolower( trim( (string) $value ) ), array( 'yes', 'true', '1', 'on' ), true );
}

/**
 * The empty-state note, matching the homepage partials' empty states.
 *
 * @param string $message Custom message, or '' for the default.
 * @return string
 */
function met_hello_child_listing_empty( $message = '' ) {
	if ( '' === $message ) {
		$message = __( 'Nothing here yet. Please check back soon.', 'met-hello-child' );
	}

	return '<p class="met-empty-note met-list__empty">' . esc_html( $message ) . '</p>';
}

/**
 * Pagination markup for a paged listing query.
 *
 * @param WP_Query $query The listing query.
 * @return string
 */
function met_hello_child_listing_pagination( $query ) {
	$links = paginate_links(
		array(
			'total'     => (int) $query->max_num_pages,
			'current'   => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
			'mid_size'  => 1,
			'prev_text' => __( 'Previous', 'met-hello-child' ),
			'next_text' => __( 'Next', 'met-hello-child' ),
			'type'      => 'list',
		)
	);

	if ( ! $links ) {
		return '';
	}

	return '<nav class="met-list__pagination" aria-label="' . esc_attr__( 'Posts navigation', 'met-hello-child' ) . '">' . $links . '</nav>';
}

/**
 * Whether the queried singular object contains a given shortcode, checking both
 * post_content and the Elementor data blob.
 *
 * Elementor stores widget content in the _elementor_data post meta, not in
 * post_content, so has_shortcode() alone misses a shortcode dropped into an
 * Elementor Shortcode widget. This mirrors MetCPT's metcpt_page_has_shortcode()
 * but avoids its early-return bug: both sources are checked before returning.
 *
 * @param string $tag Shortcode tag to look for.
 * @return bool
 */
function met_hello_child_page_has_shortcode( $tag ) {
	if ( ! is_singular() ) {
		return false;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( has_shortcode( (string) $post->post_content, $tag ) ) {
		return true;
	}

	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( is_string( $elementor_data ) && '' !== $elementor_data && false !== strpos( $elementor_data, '[' . $tag ) ) {
		return true;
	}

	return false;
}

/**
 * Enqueue the listing stylesheet, only on a singular view that uses [met_posts].
 *
 * The dependency array is not optional. The parent's reset.css styles bare `a`
 * and `button` at single-class specificity and wins on source order if this file
 * prints before it. See DOCS/STATE.md, "A child stylesheet must declare the
 * parent's stylesheets as dependencies."
 */
function met_hello_child_enqueue_listing_assets() {
	if ( ! met_hello_child_page_has_shortcode( 'met_posts' ) ) {
		return;
	}

	wp_enqueue_style(
		'met-hello-child-listing',
		MET_HELLO_CHILD_URI . 'assets/css/listing.css',
		array( 'hello-elementor', 'hello-elementor-theme-style' ),
		MET_HELLO_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_listing_assets' );
