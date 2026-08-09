<?php
/**
 * TEMPORARY. Block-system migration tool for PLAN/PRD-block-system.md.
 *
 * A single admin-only action that flips a Page's `_elementor_edit_mode` meta,
 * the flag that decides whether Elementor or page.php renders it (see
 * met_hello_child_is_block_page(), inc/setup.php, and PRD section 4.5). This
 * exists because the migration is being driven from an environment with no
 * WP-CLI and no database client, only wp-admin itself, and that one meta key
 * is not exposed through the REST API by default.
 *
 * Remove this file and its require_once line in functions.php once phase 4
 * (Elementor removed entirely) ships, or sooner if WP-CLI/DB access becomes
 * available and this stops being the only way to flip the flag.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle the migrate/rollback action. Requires `manage_options`, a valid
 * nonce, and a Page post type. Redirects back to the Page's edit screen with
 * a query arg reporting the result.
 *
 * Two meta keys control this, not one. `_elementor_edit_mode` decides
 * whether Elementor's own admin UI treats the Page as builder content; it
 * does not decide which front-end template file runs. That is
 * `_wp_page_template` (the "Page Attributes > Template" dropdown value,
 * e.g. `elementor_header_footer` or `elementor_canvas`), which WordPress's
 * own template hierarchy reads before it ever tries page.php. Clearing only
 * `_elementor_edit_mode`, as PLAN/PRD-block-system.md section 4.5 originally
 * described, left `_wp_page_template` pointed at a template Elementor no
 * longer claims once edit_mode is cleared, and WordPress fell through past
 * page.php to index.php's generic singular fallback instead. Found and
 * fixed the same day, by reading the actual rendered output rather than
 * trusting the plan's description of the mechanism.
 */
function met_hello_child_handle_migration_toggle() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'met-hello-child' ), 403 );
	}

	check_admin_referer( 'met_hello_child_migration_toggle' );

	$met_migrate_post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$met_migrate_mode    = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';

	if ( ! $met_migrate_post_id || 'page' !== get_post_type( $met_migrate_post_id ) ) {
		wp_die( esc_html__( 'Invalid Page.', 'met-hello-child' ), 400 );
	}

	if ( 'block' === $met_migrate_mode ) {
		// Elementor renders only when this meta is exactly 'builder'. Deleting
		// it (rather than setting some other value) matches what a fresh Page
		// that never opened in Elementor looks like.
		delete_post_meta( $met_migrate_post_id, '_elementor_edit_mode' );

		// Back up whatever Elementor template was in use, so rollback restores
		// the exact original, not a guess, then hand template selection to
		// page.php by clearing the slug.
		$met_migrate_current_template = get_post_meta( $met_migrate_post_id, '_wp_page_template', true );
		if ( $met_migrate_current_template ) {
			update_post_meta( $met_migrate_post_id, '_met_migration_original_template', $met_migrate_current_template );
		}
		update_post_meta( $met_migrate_post_id, '_wp_page_template', 'default' );
	} elseif ( 'elementor' === $met_migrate_mode ) {
		// Rollback: Elementor's own data (_elementor_data) is never touched or
		// deleted by this tool, so restoring both flags is enough to bring the
		// original Elementor page back exactly as it was.
		update_post_meta( $met_migrate_post_id, '_elementor_edit_mode', 'builder' );

		$met_migrate_original_template = get_post_meta( $met_migrate_post_id, '_met_migration_original_template', true );
		if ( $met_migrate_original_template ) {
			update_post_meta( $met_migrate_post_id, '_wp_page_template', $met_migrate_original_template );
			delete_post_meta( $met_migrate_post_id, '_met_migration_original_template' );
		}
	} else {
		wp_die( esc_html__( 'Unknown mode.', 'met-hello-child' ), 400 );
	}

	wp_safe_redirect( add_query_arg( 'met_migrated', $met_migrate_mode, get_edit_post_link( $met_migrate_post_id, 'raw' ) ) );
	exit;
}
add_action( 'admin_post_met_hello_child_migrate', 'met_hello_child_handle_migration_toggle' );

/**
 * Set the Page Hero meta fields (inc/page-hero.php) via the REST-unfriendly
 * classic meta box's own field names and sanitisers, without touching
 * post_content. Same temporary/removal note as the function above.
 */
function met_hello_child_handle_set_hero_meta() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'met-hello-child' ), 403 );
	}

	check_admin_referer( 'met_hello_child_migration_toggle' );

	$met_hero_post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

	if ( ! $met_hero_post_id || 'page' !== get_post_type( $met_hero_post_id ) ) {
		wp_die( esc_html__( 'Invalid Page.', 'met-hello-child' ), 400 );
	}

	foreach ( met_hello_child_page_hero_fields() as $met_hero_meta_key => $met_hero_sanitizer ) {
		$met_hero_field = ltrim( $met_hero_meta_key, '_' );

		if ( ! isset( $_POST[ $met_hero_field ] ) ) {
			continue;
		}

		$met_hero_value = call_user_func( $met_hero_sanitizer, wp_unslash( $_POST[ $met_hero_field ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every sanitiser in met_hello_child_page_hero_fields() sanitises its input.

		if ( '' === $met_hero_value ) {
			delete_post_meta( $met_hero_post_id, $met_hero_meta_key );
		} else {
			update_post_meta( $met_hero_post_id, $met_hero_meta_key, $met_hero_value );
		}
	}

	wp_safe_redirect( add_query_arg( 'met_hero_set', '1', get_edit_post_link( $met_hero_post_id, 'raw' ) ) );
	exit;
}
add_action( 'admin_post_met_hello_child_set_hero_meta', 'met_hello_child_handle_set_hero_meta' );

/**
 * Backfill the `_met_sector` meta on the nine child Pages of /business/
 * (parent resolved by slug) from each Page's Page Hero eyebrow. A one-time convenience
 * so the sector no longer has to be resolved from the eyebrow at render time
 * (inc/sectors.php). Idempotent: re-running writes the same values. Redirects
 * to the Pages list with a count. Same temporary/removal note as the rest of
 * this file.
 */
function met_hello_child_handle_backfill_sectors() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'met-hello-child' ), 403 );
	}

	check_admin_referer( 'met_hello_child_migration_toggle' );

	$met_backfill_parent = met_hello_child_business_parent_id();

	$met_backfill_children = $met_backfill_parent ? get_posts(
		array(
			'post_type'   => 'page',
			'post_parent' => $met_backfill_parent,
			'numberposts' => -1,
			'post_status' => 'any',
			'fields'      => 'ids',
		)
	) : array();

	$met_backfill_count = 0;
	foreach ( $met_backfill_children as $met_backfill_id ) {
		$met_backfill_sector = met_hello_child_sector_from_eyebrow( get_post_meta( $met_backfill_id, '_met_hero_eyebrow', true ) );
		if ( '' !== $met_backfill_sector ) {
			update_post_meta( $met_backfill_id, '_met_sector', $met_backfill_sector );
			++$met_backfill_count;
		}
	}

	wp_safe_redirect( add_query_arg( 'met_sectors_backfilled', $met_backfill_count, admin_url( 'edit.php?post_type=page' ) ) );
	exit;
}
add_action( 'admin_post_met_hello_child_backfill_sectors', 'met_hello_child_handle_backfill_sectors' );

/**
 * Print the nonce the two actions above require, as plain text, for a
 * script driving this tool over HTTP with no UI to read a nonce field from.
 * Same temporary/removal note as the rest of this file.
 */
function met_hello_child_print_migration_nonce() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'met-hello-child' ), 403 );
	}

	header( 'Content-Type: text/plain' );
	echo esc_html( wp_create_nonce( 'met_hello_child_migration_toggle' ) );
	exit;
}
add_action( 'admin_post_met_hello_child_migration_nonce', 'met_hello_child_print_migration_nonce' );
