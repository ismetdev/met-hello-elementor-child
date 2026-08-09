<?php
/**
 * External photo album link for Posts.
 *
 * The Group uploads high-resolution event photos to Facebook, not the WordPress
 * media library, to keep hosting disk usage down. A Post in the "gallery"
 * category holds the cover image and description here, and _met_album_url points
 * at the Facebook album. single.php renders a "View the full album" button when
 * it is set, and the [met_posts layout="album"] card marks it. See DECISIONS D46.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The external album URL for a post, or '' when unset.
 *
 * @param int|null $post_id Post ID, or null for the current post.
 * @return string
 */
function met_hello_child_get_album_url( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	return (string) get_post_meta( (int) $post_id, '_met_album_url', true );
}

/**
 * Register the "External album" meta box on the Post edit screen.
 */
function met_hello_child_add_album_meta_box() {
	add_meta_box(
		'met_hello_child_album',
		__( 'External album', 'met-hello-child' ),
		'met_hello_child_render_album_meta_box',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'met_hello_child_add_album_meta_box' );

/**
 * Render the "External album" meta box field.
 *
 * @param WP_Post $post Current Post being edited.
 */
function met_hello_child_render_album_meta_box( $post ) {
	wp_nonce_field( 'met_hello_child_album_save', 'met_hello_child_album_nonce' );

	$met_album_url = get_post_meta( $post->ID, '_met_album_url', true );
	?>
	<p>
		<label for="met_album_url"><strong><?php esc_html_e( 'Album URL', 'met-hello-child' ); ?></strong></label>
	</p>
	<p>
		<input type="url" class="widefat" id="met_album_url" name="met_album_url" value="<?php echo esc_attr( $met_album_url ); ?>" placeholder="https://facebook.com/...">
	</p>
	<p class="description">
		<?php esc_html_e( 'Paste the public link to the Facebook album (or any external gallery). When set, the post shows a "View the full album" button and the gallery listing marks it as a photo album.', 'met-hello-child' ); ?>
	</p>
	<?php
}

/**
 * Save the "External album" meta box field.
 *
 * @param int $post_id Post ID being saved.
 */
function met_hello_child_save_album_meta( $post_id ) {
	if ( ! isset( $_POST['met_hello_child_album_nonce'] ) ) {
		return;
	}

	$met_album_nonce = sanitize_text_field( wp_unslash( $_POST['met_hello_child_album_nonce'] ) );
	if ( ! wp_verify_nonce( $met_album_nonce, 'met_hello_child_album_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['met_album_url'] ) ) {
		delete_post_meta( $post_id, '_met_album_url' );
		return;
	}

	$met_album_url = esc_url_raw( wp_unslash( $_POST['met_album_url'] ), array( 'http', 'https' ) );

	if ( '' === $met_album_url ) {
		delete_post_meta( $post_id, '_met_album_url' );
	} else {
		update_post_meta( $post_id, '_met_album_url', $met_album_url );
	}
}
add_action( 'save_post_post', 'met_hello_child_save_album_meta' );
