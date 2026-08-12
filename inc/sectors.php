<?php
/**
 * Business sector: one Page meta key that tags a /business/ child Page with the
 * division it belongs to. Read by the homepage companies section and the header
 * mega menu, which colour-code cards and columns by sector.
 *
 * A single meta key, not a taxonomy. Content shape (a real taxonomy with its own
 * archive) belongs in the MetCPT plugin, not this presentation theme. See
 * DECISIONS D33.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed sector slugs. Each maps to a --wp--preset--color--sector-* token and
 * to the three columns of the design's mega menu. An unrecognised or empty
 * value means "no sector".
 *
 * @return string[]
 */
function met_hello_child_sectors() {
	return array( 'education', 'facilities', 'healthcare' );
}

/**
 * The ID of the top-level "business" Page, resolved by slug.
 *
 * Resolved by slug, never hardcoded, because the page ID differs between sites
 * (172 on local, 33 on staging). The homepage companies grid and the sector
 * backfill both read this, so a hardcoded ID would leave the companies section
 * empty after a move. Filterable for the rare site where the slug differs.
 *
 * @return int Parent page ID, or 0 when no such page exists.
 */
function met_hello_child_business_parent_id() {
	$met_business_parent = get_page_by_path( 'business' );
	$met_business_id     = $met_business_parent ? (int) $met_business_parent->ID : 0;

	/**
	 * Filter the business parent Page ID.
	 *
	 * @param int $met_business_id Resolved parent page ID.
	 */
	return (int) apply_filters( 'met_hello_child_business_parent_id', $met_business_id );
}

/**
 * Human label for a sector slug, for the badge and column heading.
 *
 * @param string $sector Sector slug.
 * @return string
 */
function met_hello_child_sector_label( $sector ) {
	$labels = array(
		'education'  => __( 'Education', 'met-hello-child' ),
		'facilities' => __( 'Facilities', 'met-hello-child' ),
		'healthcare' => __( 'Healthcare', 'met-hello-child' ),
	);

	return isset( $labels[ $sector ] ) ? $labels[ $sector ] : '';
}

/**
 * Sanitise a submitted sector against the allowed list.
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_sector( $value ) {
	$value = sanitize_key( $value );

	return in_array( $value, met_hello_child_sectors(), true ) ? $value : '';
}

/**
 * Resolve a Page's sector.
 *
 * Order: the saved `_met_sector` meta, then a parse of the Page Hero eyebrow
 * ("Education Division" -> education), then ''. The eyebrow fallback means the
 * nine /business/ Pages render with the right colour on day one with zero data
 * entry; the one-time backfill in inc/migration-tools.php then writes the real
 * meta so the fallback is no longer relied on.
 *
 * @param int $post_id Page ID.
 * @return string Sector slug, or '' if none.
 */
function met_hello_child_get_page_sector( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return '';
	}

	$saved = get_post_meta( $post_id, '_met_sector', true );
	if ( in_array( $saved, met_hello_child_sectors(), true ) ) {
		return $saved;
	}

	return met_hello_child_sector_from_eyebrow( get_post_meta( $post_id, '_met_hero_eyebrow', true ) );
}

/**
 * Parse a sector slug out of a Page Hero eyebrow string, case-insensitively.
 * "Education Division" -> education. Returns '' when nothing matches.
 *
 * "Infrastructure" is kept as a legacy alias for "facilities" (see DECISIONS
 * D49): any page whose hero eyebrow still reads the pre-rename text resolves
 * correctly until that copy is edited by hand.
 *
 * @param string $eyebrow Eyebrow text.
 * @return string
 */
function met_hello_child_sector_from_eyebrow( $eyebrow ) {
	if ( ! is_string( $eyebrow ) || '' === $eyebrow ) {
		return '';
	}

	$eyebrow = strtolower( $eyebrow );

	if ( false !== strpos( $eyebrow, 'infrastructure' ) ) {
		return 'facilities';
	}

	foreach ( met_hello_child_sectors() as $sector ) {
		if ( false !== strpos( $eyebrow, $sector ) ) {
			return $sector;
		}
	}

	return '';
}

/**
 * Register the "Business Sector" meta box on the Page edit screen.
 */
function met_hello_child_add_sector_meta_box() {
	add_meta_box(
		'met_hello_child_sector',
		__( 'Business Sector', 'met-hello-child' ),
		'met_hello_child_render_sector_meta_box',
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'met_hello_child_add_sector_meta_box' );

/**
 * Render the "Business Sector" meta box.
 *
 * @param WP_Post $post Current Page being edited.
 */
function met_hello_child_render_sector_meta_box( $post ) {
	wp_nonce_field( 'met_hello_child_sector_save', 'met_hello_child_sector_nonce' );

	$met_sector = get_post_meta( $post->ID, '_met_sector', true );
	?>
	<p>
		<label for="met_sector"><?php esc_html_e( 'Division this company belongs to. Colours its card and mega-menu column on the homepage and header.', 'met-hello-child' ); ?></label>
	</p>
	<p>
		<select name="met_sector" id="met_sector" class="widefat">
			<option value=""><?php esc_html_e( 'None', 'met-hello-child' ); ?></option>
			<?php foreach ( met_hello_child_sectors() as $met_sector_slug ) : ?>
				<option value="<?php echo esc_attr( $met_sector_slug ); ?>" <?php selected( $met_sector, $met_sector_slug ); ?>>
					<?php echo esc_html( met_hello_child_sector_label( $met_sector_slug ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

/**
 * Save the "Business Sector" meta box.
 *
 * @param int $post_id Page ID being saved.
 */
function met_hello_child_save_sector_meta( $post_id ) {
	if ( ! isset( $_POST['met_hello_child_sector_nonce'] ) ) {
		return;
	}

	$met_sector_nonce = sanitize_text_field( wp_unslash( $_POST['met_hello_child_sector_nonce'] ) );
	if ( ! wp_verify_nonce( $met_sector_nonce, 'met_hello_child_sector_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['met_sector'] ) ) {
		return;
	}

	// met_hello_child_sanitize_sector() validates against the allowed list; the
	// sniff cannot see that through the function call.
	$met_sector_value = met_hello_child_sanitize_sector( wp_unslash( $_POST['met_sector'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( '' === $met_sector_value ) {
		delete_post_meta( $post_id, '_met_sector' );
	} else {
		update_post_meta( $post_id, '_met_sector', $met_sector_value );
	}
}
add_action( 'save_post_page', 'met_hello_child_save_sector_meta' );
