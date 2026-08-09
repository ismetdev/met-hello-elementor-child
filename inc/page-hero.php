<?php
/**
 * Page Hero: an editor-controlled header band for Elementor Full Width / Canvas
 * Pages, reusing the theme's shipped hero design.
 *
 * Opt-in per Page via the "Page Hero" meta box, not a hardcoded slug list.
 * Independent of met_hello_child_is_styled_view() in inc/setup.php: a Page with
 * a hero loads the design stylesheet and font hints, but never gets the
 * full-width body class, since Elementor Full Width already renders edge to edge.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed hero variants. An unrecognised or empty value means no hero.
 *
 * @return string[]
 */
function met_hello_child_page_hero_variants() {
	return array( 'standard', 'business' );
}

/**
 * Sanitise a submitted variant against the allowed list.
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_hero_variant( $value ) {
	$value = sanitize_key( $value );

	return in_array( $value, met_hello_child_page_hero_variants(), true ) ? $value : '';
}

/**
 * Meta fields the "Page Hero" box manages: post meta key => sanitiser.
 *
 * @return array<string, callable>
 */
function met_hello_child_page_hero_fields() {
	return array(
		'_met_hero_variant'    => 'met_hello_child_sanitize_hero_variant',
		'_met_hero_eyebrow'    => 'sanitize_text_field',
		'_met_hero_subtitle'   => 'sanitize_textarea_field',
		'_met_hero_cta1_label' => 'sanitize_text_field',
		'_met_hero_cta1_url'   => 'esc_url_raw',
		'_met_hero_cta2_label' => 'sanitize_text_field',
		'_met_hero_cta2_url'   => 'esc_url_raw',
	);
}

/**
 * Whether the currently queried Page has a hero to render.
 *
 * This is the switch: a Page opts in by having a recognised variant saved in
 * meta. There is no slug list here, so adding a hero to a new Page never needs
 * a code change.
 *
 * @return bool
 */
function met_hello_child_page_has_hero() {
	if ( ! is_page() ) {
		return false;
	}

	$variant = get_post_meta( get_queried_object_id(), '_met_hero_variant', true );

	return in_array( $variant, met_hello_child_page_hero_variants(), true );
}

/**
 * Register the "Page Hero" meta box on the Page edit screen.
 */
function met_hello_child_add_page_hero_meta_box() {
	add_meta_box(
		'met_hello_child_page_hero',
		__( 'Page Hero', 'met-hello-child' ),
		'met_hello_child_render_page_hero_meta_box',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'met_hello_child_add_page_hero_meta_box' );

/**
 * Render the "Page Hero" meta box fields.
 *
 * @param WP_Post $post Current Page being edited.
 */
function met_hello_child_render_page_hero_meta_box( $post ) {
	wp_nonce_field( 'met_hello_child_page_hero_save', 'met_hello_child_page_hero_nonce' );

	$met_hero_variant    = get_post_meta( $post->ID, '_met_hero_variant', true );
	$met_hero_eyebrow    = get_post_meta( $post->ID, '_met_hero_eyebrow', true );
	$met_hero_subtitle   = get_post_meta( $post->ID, '_met_hero_subtitle', true );
	$met_hero_cta1_label = get_post_meta( $post->ID, '_met_hero_cta1_label', true );
	$met_hero_cta1_url   = get_post_meta( $post->ID, '_met_hero_cta1_url', true );
	$met_hero_cta2_label = get_post_meta( $post->ID, '_met_hero_cta2_label', true );
	$met_hero_cta2_url   = get_post_meta( $post->ID, '_met_hero_cta2_url', true );
	?>
	<fieldset>
		<legend><strong><?php esc_html_e( 'Variant', 'met-hello-child' ); ?></strong></legend>
		<p>
			<label><input type="radio" name="met_hero_variant" value="" <?php checked( ! in_array( $met_hero_variant, met_hello_child_page_hero_variants(), true ) ); ?>> <?php esc_html_e( 'None (no hero on this page)', 'met-hello-child' ); ?></label><br>
			<label><input type="radio" name="met_hero_variant" value="standard" <?php checked( $met_hero_variant, 'standard' ); ?>> <?php esc_html_e( 'Standard: compact petrol band', 'met-hello-child' ); ?></label><br>
			<label><input type="radio" name="met_hero_variant" value="business" <?php checked( $met_hero_variant, 'business' ); ?>> <?php esc_html_e( 'Business: tall hero with tag and buttons', 'met-hello-child' ); ?></label>
		</p>
	</fieldset>
	<p class="description">
		<?php esc_html_e( 'The hero only renders when the Page Attributes layout is set to Elementor Full Width or Elementor Canvas.', 'met-hello-child' ); ?>
	</p>
	<p>
		<label for="met_hero_eyebrow"><strong><?php esc_html_e( 'Eyebrow', 'met-hello-child' ); ?></strong></label><br>
		<input type="text" class="widefat" id="met_hero_eyebrow" name="met_hero_eyebrow" value="<?php echo esc_attr( $met_hero_eyebrow ); ?>">
		<span class="description"><?php esc_html_e( 'Falls back to the parent page title, then to nothing.', 'met-hello-child' ); ?></span>
	</p>
	<p>
		<label for="met_hero_subtitle"><strong><?php esc_html_e( 'Subtitle', 'met-hello-child' ); ?></strong></label><br>
		<textarea class="widefat" rows="2" id="met_hero_subtitle" name="met_hero_subtitle"><?php echo esc_textarea( $met_hero_subtitle ); ?></textarea>
	</p>
	<div id="met_hero_business_fields" style="<?php echo 'business' === $met_hero_variant ? '' : 'display:none;'; ?>">
		<p>
			<strong><?php esc_html_e( 'Button 1', 'met-hello-child' ); ?></strong><br>
			<input type="text" style="width:48%;" placeholder="<?php esc_attr_e( 'Label', 'met-hello-child' ); ?>" name="met_hero_cta1_label" value="<?php echo esc_attr( $met_hero_cta1_label ); ?>">
			<input type="url" style="width:48%;" placeholder="<?php esc_attr_e( 'URL', 'met-hello-child' ); ?>" name="met_hero_cta1_url" value="<?php echo esc_attr( $met_hero_cta1_url ); ?>">
		</p>
		<p>
			<strong><?php esc_html_e( 'Button 2', 'met-hello-child' ); ?></strong><br>
			<input type="text" style="width:48%;" placeholder="<?php esc_attr_e( 'Label', 'met-hello-child' ); ?>" name="met_hero_cta2_label" value="<?php echo esc_attr( $met_hero_cta2_label ); ?>">
			<input type="url" style="width:48%;" placeholder="<?php esc_attr_e( 'URL', 'met-hello-child' ); ?>" name="met_hero_cta2_url" value="<?php echo esc_attr( $met_hero_cta2_url ); ?>">
		</p>
	</div>
	<script>
	( function () {
		var radios = document.querySelectorAll( 'input[name="met_hero_variant"]' );
		var fields = document.getElementById( 'met_hero_business_fields' );
		if ( ! radios.length || ! fields ) {
			return;
		}
		function sync() {
			var checked = document.querySelector( 'input[name="met_hero_variant"]:checked' );
			fields.style.display = checked && 'business' === checked.value ? '' : 'none';
		}
		radios.forEach( function ( radio ) {
			radio.addEventListener( 'change', sync );
		} );
	} )();
	</script>
	<?php
}

/**
 * Save the "Page Hero" meta box fields.
 *
 * @param int $post_id Page ID being saved.
 */
function met_hello_child_save_page_hero_meta( $post_id ) {
	if ( ! isset( $_POST['met_hello_child_page_hero_nonce'] ) ) {
		return;
	}

	$met_hero_nonce = sanitize_text_field( wp_unslash( $_POST['met_hello_child_page_hero_nonce'] ) );
	if ( ! wp_verify_nonce( $met_hero_nonce, 'met_hello_child_page_hero_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	foreach ( met_hello_child_page_hero_fields() as $met_hero_meta_key => $met_hero_sanitizer ) {
		$met_hero_field = ltrim( $met_hero_meta_key, '_' );

		if ( ! isset( $_POST[ $met_hero_field ] ) ) {
			delete_post_meta( $post_id, $met_hero_meta_key );
			continue;
		}

		// The sniff can't see through call_user_func(): $met_hero_sanitizer is one
		// of the fixed callbacks in met_hello_child_page_hero_fields() above, and
		// every one of them sanitises its input.
		$met_hero_value = call_user_func( $met_hero_sanitizer, wp_unslash( $_POST[ $met_hero_field ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( '' === $met_hero_value ) {
			delete_post_meta( $post_id, $met_hero_meta_key );
		} else {
			update_post_meta( $post_id, $met_hero_meta_key, $met_hero_value );
		}
	}
}
add_action( 'save_post_page', 'met_hello_child_save_page_hero_meta' );

/**
 * Print the hero partial above Elementor's page content.
 *
 * Bound to both the Full Width and Canvas "before_content" hooks. All 16 launch
 * Pages use Full Width; Canvas is a safety net if a page is ever switched. The
 * static flag stops a double print if both hooks somehow fire for one request.
 */
function met_hello_child_render_page_hero() {
	static $met_hero_rendered = false;

	if ( $met_hero_rendered || ! met_hello_child_page_has_hero() ) {
		return;
	}

	$met_hero_rendered = true;

	get_template_part( 'template-parts/page-hero' );
}
add_action( 'elementor/page_templates/header-footer/before_content', 'met_hello_child_render_page_hero' );
add_action( 'elementor/page_templates/canvas/before_content', 'met_hello_child_render_page_hero' );
