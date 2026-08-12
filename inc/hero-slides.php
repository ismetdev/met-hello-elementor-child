<?php
/**
 * Hero slides: a non-public custom post type that supplies the homepage hero
 * carousel. One slide per post. The featured image is the background; the copy
 * lives in meta fields shaped like the Page Hero fields in inc/page-hero.php.
 *
 * Non-public theme furniture, unlike MetCPT's public Events/Tenders/Careers:
 * public=false, show_in_rest=false (so the classic editor and this classic meta
 * box are the right tools), no single view, no archive. See DECISIONS D32.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the met_hero_slide post type.
 */
function met_hello_child_register_hero_slide_cpt() {
	register_post_type(
		'met_hero_slide',
		array(
			'labels'              => array(
				'name'               => __( 'Hero Slides', 'met-hello-child' ),
				'singular_name'      => __( 'Hero Slide', 'met-hello-child' ),
				'add_new'            => __( 'Add New', 'met-hello-child' ),
				'add_new_item'       => __( 'Add New Hero Slide', 'met-hello-child' ),
				'edit_item'          => __( 'Edit Hero Slide', 'met-hello-child' ),
				'new_item'           => __( 'New Hero Slide', 'met-hello-child' ),
				'view_item'          => __( 'View Hero Slide', 'met-hello-child' ),
				'search_items'       => __( 'Search Hero Slides', 'met-hello-child' ),
				'not_found'          => __( 'No hero slides yet.', 'met-hello-child' ),
				'not_found_in_trash' => __( 'No hero slides in trash.', 'met-hello-child' ),
				'all_items'          => __( 'Hero Slides', 'met-hello-child' ),
				'menu_name'          => __( 'Hero Slides', 'met-hello-child' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'query_var'           => false,
			'menu_icon'           => 'dashicons-images-alt2',
			'menu_position'       => 26,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'met_hello_child_register_hero_slide_cpt' );

/**
 * Meta fields the hero slide box manages: post meta key => sanitiser. Mirrors
 * the map pattern in met_hello_child_page_hero_fields().
 *
 * @return array<string, callable>
 */
function met_hello_child_hero_slide_fields() {
	return array(
		'_met_slide_eyebrow'    => 'sanitize_text_field',
		'_met_slide_headline'   => 'sanitize_text_field',
		'_met_slide_body'       => 'sanitize_textarea_field',
		'_met_slide_cta1_label' => 'sanitize_text_field',
		'_met_slide_cta1_url'   => 'esc_url_raw',
		'_met_slide_cta2_label' => 'sanitize_text_field',
		'_met_slide_cta2_url'   => 'esc_url_raw',
		'_met_slide_focus'      => 'met_hello_child_sanitize_slide_focus',
		'_met_slide_size'       => 'met_hello_child_sanitize_slide_size',
	);
}

/**
 * Sanitise the headline size field: an integer pixel value clamped to 28-72,
 * or '' (the default size) when left blank or zero. Owner request 2026-08-11:
 * a long programme or event name can otherwise cover too much of the hero
 * image, so the size is a per-slide number, not fixed.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_slide_size( $value ) {
	$met_size = absint( $value );

	if ( ! $met_size ) {
		return '';
	}

	$met_size = max( 28, min( 72, $met_size ) );

	return (string) $met_size;
}

/**
 * The allowed vertical focal points for a slide image, mapped to the CSS
 * object-position percentage that keeps that part of the photo in view.
 *
 * @return array<string,string>
 */
function met_hello_child_slide_focus_choices() {
	return array(
		'top'    => '0%',
		'upper'  => '25%',
		'center' => '50%',
		'lower'  => '75%',
		'bottom' => '100%',
	);
}

/**
 * Sanitise the focal-point value against the allowed keys, default center.
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_slide_focus( $value ) {
	$value = sanitize_key( $value );

	return array_key_exists( $value, met_hello_child_slide_focus_choices() ) ? $value : 'center';
}

/**
 * Register the slide content meta box.
 */
function met_hello_child_add_hero_slide_meta_box() {
	add_meta_box(
		'met_hello_child_hero_slide',
		__( 'Slide Content', 'met-hello-child' ),
		'met_hello_child_render_hero_slide_meta_box',
		'met_hero_slide',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'met_hello_child_add_hero_slide_meta_box' );

/**
 * Render the slide content meta box.
 *
 * @param WP_Post $post Current slide being edited.
 */
function met_hello_child_render_hero_slide_meta_box( $post ) {
	wp_nonce_field( 'met_hello_child_hero_slide_save', 'met_hello_child_hero_slide_nonce' );

	$met_slide_eyebrow    = get_post_meta( $post->ID, '_met_slide_eyebrow', true );
	$met_slide_headline   = get_post_meta( $post->ID, '_met_slide_headline', true );
	$met_slide_body       = get_post_meta( $post->ID, '_met_slide_body', true );
	$met_slide_cta1_label = get_post_meta( $post->ID, '_met_slide_cta1_label', true );
	$met_slide_cta1_url   = get_post_meta( $post->ID, '_met_slide_cta1_url', true );
	$met_slide_cta2_label = get_post_meta( $post->ID, '_met_slide_cta2_label', true );
	$met_slide_cta2_url   = get_post_meta( $post->ID, '_met_slide_cta2_url', true );
	$met_slide_focus      = get_post_meta( $post->ID, '_met_slide_focus', true );
	if ( '' === $met_slide_focus ) {
		$met_slide_focus = 'center';
	}
	$met_slide_size = get_post_meta( $post->ID, '_met_slide_size', true );
	?>
	<p class="description">
		<?php esc_html_e( 'Set the background with the Featured Image. Order the slides with the Order field under Page Attributes. A button shows only when it has both a label and a URL.', 'met-hello-child' ); ?>
	</p>
	<p class="description">
		<?php esc_html_e( 'Recommended image: 1920 x 1080 pixels or larger, landscape (16:9). WebP, JPG or PNG. The image is shown at full quality and cropped to fit by the browser; use the focal point below to keep the main subject in view.', 'met-hello-child' ); ?>
	</p>
	<p>
		<label for="met_slide_eyebrow"><strong><?php esc_html_e( 'Eyebrow', 'met-hello-child' ); ?></strong></label><br>
		<input type="text" class="widefat" id="met_slide_eyebrow" name="met_slide_eyebrow" value="<?php echo esc_attr( $met_slide_eyebrow ); ?>">
	</p>
	<p>
		<label for="met_slide_headline"><strong><?php esc_html_e( 'Headline', 'met-hello-child' ); ?></strong></label><br>
		<input type="text" class="widefat" id="met_slide_headline" name="met_slide_headline" value="<?php echo esc_attr( $met_slide_headline ); ?>">
		<span class="description"><?php esc_html_e( 'Falls back to the slide title if left empty.', 'met-hello-child' ); ?></span>
	</p>
	<p>
		<label for="met_slide_size"><strong><?php esc_html_e( 'Headline size (pixels)', 'met-hello-child' ); ?></strong></label><br>
		<input type="number" min="28" max="72" step="1" id="met_slide_size" name="met_slide_size" value="<?php echo esc_attr( $met_slide_size ); ?>" style="width:120px;">
		<span class="description"><?php esc_html_e( 'Leave empty for the default size. Lower it for a long programme or event name so it does not cover the photo. Range 28-72; still scales down on small screens.', 'met-hello-child' ); ?></span>
	</p>
	<p>
		<label for="met_slide_body"><strong><?php esc_html_e( 'Body', 'met-hello-child' ); ?></strong></label><br>
		<textarea class="widefat" rows="2" id="met_slide_body" name="met_slide_body"><?php echo esc_textarea( $met_slide_body ); ?></textarea>
	</p>
	<p>
		<strong><?php esc_html_e( 'Button 1', 'met-hello-child' ); ?></strong><br>
		<input type="text" style="width:48%;" placeholder="<?php esc_attr_e( 'Label', 'met-hello-child' ); ?>" name="met_slide_cta1_label" value="<?php echo esc_attr( $met_slide_cta1_label ); ?>">
		<input type="url" style="width:48%;" placeholder="<?php esc_attr_e( 'URL', 'met-hello-child' ); ?>" name="met_slide_cta1_url" value="<?php echo esc_attr( $met_slide_cta1_url ); ?>">
	</p>
	<p>
		<strong><?php esc_html_e( 'Button 2', 'met-hello-child' ); ?></strong><br>
		<input type="text" style="width:48%;" placeholder="<?php esc_attr_e( 'Label', 'met-hello-child' ); ?>" name="met_slide_cta2_label" value="<?php echo esc_attr( $met_slide_cta2_label ); ?>">
		<input type="url" style="width:48%;" placeholder="<?php esc_attr_e( 'URL', 'met-hello-child' ); ?>" name="met_slide_cta2_url" value="<?php echo esc_attr( $met_slide_cta2_url ); ?>">
	</p>
	<p>
		<label for="met_slide_focus"><strong><?php esc_html_e( 'Image focal point (vertical)', 'met-hello-child' ); ?></strong></label><br>
		<select name="met_slide_focus" id="met_slide_focus">
			<option value="top" <?php selected( $met_slide_focus, 'top' ); ?>><?php esc_html_e( 'Top', 'met-hello-child' ); ?></option>
			<option value="upper" <?php selected( $met_slide_focus, 'upper' ); ?>><?php esc_html_e( 'Upper', 'met-hello-child' ); ?></option>
			<option value="center" <?php selected( $met_slide_focus, 'center' ); ?>><?php esc_html_e( 'Center', 'met-hello-child' ); ?></option>
			<option value="lower" <?php selected( $met_slide_focus, 'lower' ); ?>><?php esc_html_e( 'Lower', 'met-hello-child' ); ?></option>
			<option value="bottom" <?php selected( $met_slide_focus, 'bottom' ); ?>><?php esc_html_e( 'Bottom', 'met-hello-child' ); ?></option>
		</select>
		<span class="description"><?php esc_html_e( 'Which part of the photo stays in view when it is cropped. Pick where your subject sits.', 'met-hello-child' ); ?></span>
	</p>
	<?php
}

/**
 * Save the slide content meta box. Follows the same guard order and delete-on-
 * empty behaviour as met_hello_child_save_page_hero_meta().
 *
 * @param int $post_id Slide ID being saved.
 */
function met_hello_child_save_hero_slide_meta( $post_id ) {
	if ( ! isset( $_POST['met_hello_child_hero_slide_nonce'] ) ) {
		return;
	}

	$met_slide_nonce = sanitize_text_field( wp_unslash( $_POST['met_hello_child_hero_slide_nonce'] ) );
	if ( ! wp_verify_nonce( $met_slide_nonce, 'met_hello_child_hero_slide_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( met_hello_child_hero_slide_fields() as $met_slide_meta_key => $met_slide_sanitizer ) {
		$met_slide_field = ltrim( $met_slide_meta_key, '_' );

		if ( ! isset( $_POST[ $met_slide_field ] ) ) {
			delete_post_meta( $post_id, $met_slide_meta_key );
			continue;
		}

		// The sniff can't see through call_user_func(): $met_slide_sanitizer is
		// one of the fixed callbacks in met_hello_child_hero_slide_fields(), each
		// of which sanitises its input.
		$met_slide_value = call_user_func( $met_slide_sanitizer, wp_unslash( $_POST[ $met_slide_field ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( '' === $met_slide_value ) {
			delete_post_meta( $post_id, $met_slide_meta_key );
		} else {
			update_post_meta( $post_id, $met_slide_meta_key, $met_slide_value );
		}
	}
}
add_action( 'save_post_met_hero_slide', 'met_hello_child_save_hero_slide_meta' );

/**
 * Return the hero slides for rendering, ordered by menu_order then date.
 *
 * Each item: array of id, title, eyebrow, headline, body, image_id, and ctas
 * (a list of label/url pairs, each with both set). When no slides exist, the
 * caller falls back to a single static slide from the site identity; this
 * function itself returns an empty array in that case.
 *
 * @return array<int,array<string,mixed>>
 */
function met_hello_child_get_hero_slides() {
	$met_slide_posts = get_posts(
		array(
			'post_type'   => 'met_hero_slide',
			'post_status' => 'publish',
			'numberposts' => 8,
			'orderby'     => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
		)
	);

	$met_slides = array();
	foreach ( $met_slide_posts as $met_slide_post ) {
		$met_slide_ctas = array();
		foreach ( array( 1, 2 ) as $met_slide_slot ) {
			$met_slide_label = get_post_meta( $met_slide_post->ID, '_met_slide_cta' . $met_slide_slot . '_label', true );
			$met_slide_url   = get_post_meta( $met_slide_post->ID, '_met_slide_cta' . $met_slide_slot . '_url', true );
			if ( $met_slide_label && $met_slide_url ) {
				$met_slide_ctas[] = array(
					'label' => $met_slide_label,
					'url'   => $met_slide_url,
				);
			}
		}

		$met_slide_headline = get_post_meta( $met_slide_post->ID, '_met_slide_headline', true );

		$met_slide_focus = get_post_meta( $met_slide_post->ID, '_met_slide_focus', true );

		$met_slides[] = array(
			'id'       => $met_slide_post->ID,
			'title'    => get_the_title( $met_slide_post ),
			'eyebrow'  => get_post_meta( $met_slide_post->ID, '_met_slide_eyebrow', true ),
			'headline' => $met_slide_headline ? $met_slide_headline : get_the_title( $met_slide_post ),
			'body'     => get_post_meta( $met_slide_post->ID, '_met_slide_body', true ),
			'image_id' => get_post_thumbnail_id( $met_slide_post->ID ),
			'focus'    => array_key_exists( $met_slide_focus, met_hello_child_slide_focus_choices() ) ? $met_slide_focus : 'center',
			'size'     => absint( get_post_meta( $met_slide_post->ID, '_met_slide_size', true ) ),
			'ctas'     => $met_slide_ctas,
		);
	}

	return $met_slides;
}
