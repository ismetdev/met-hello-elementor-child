<?php
/**
 * Mobile navigation drawer: the same primary menu (menu-1) rendered a second
 * time with the drawer walker, plus a scrim. Rendering the menu again is more
 * robust than cloning the desktop DOM. The <details> accordions work with no
 * JavaScript; chrome.js adds the open/close, focus trap and Esc handling.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="met-scrim" id="met-scrim" hidden></div>
<aside class="met-drawer" id="met-drawer" aria-label="<?php esc_attr_e( 'Mobile menu', 'met-hello-child' ); ?>" aria-hidden="true">
	<div class="met-drawer__head">
		<?php echo met_hello_child_brand_lockup( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the helper. ?>
		<button type="button" class="met-drawer__close" id="met-drawer-close" aria-label="<?php esc_attr_e( 'Close menu', 'met-hello-child' ); ?>">&#10005;</button>
	</div>
	<nav class="met-drawer__nav" aria-label="<?php esc_attr_e( 'Mobile primary', 'met-hello-child' ); ?>">
		<?php
		if ( has_nav_menu( 'menu-1' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 3,
					'echo'           => true,
					'fallback_cb'    => false,
					'walker'         => new Met_Hello_Child_Drawer_Walker(),
				)
			);
		}
		?>
		<a class="met-drawer__flat" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'met-hello-child' ); ?></a>
	</nav>
</aside>
