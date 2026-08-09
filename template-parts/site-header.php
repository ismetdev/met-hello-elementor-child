<?php
/**
 * Custom site header: a dark utility bar over a sticky main bar with the brand,
 * the primary mega menu (menu-1 via Met_Hello_Child_Nav_Walker), a Contact
 * button, and the drawer toggle.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$met_util_links = array(
	array(
		'url'   => 'https://intranet.iiumholdings.com.my/',
		'label' => __( 'Intranet', 'met-hello-child' ),
	),
	array(
		'url'   => 'https://staff.iiumholdings.com.my/TimeSolution/SignIn.aspx',
		'label' => __( 'HRMIS', 'met-hello-child' ),
	),
	array(
		'url'   => 'https://www.office.com/',
		'label' => __( 'Office 365', 'met-hello-child' ),
	),
	array(
		'url'   => home_url( '/sitemap/' ),
		'label' => __( 'Sitemap', 'met-hello-child' ),
	),
);
?>
<header class="met-header" id="met-header">
	<div class="met-header__utility">
		<div class="met-container met-header__utility-inner">
			<span class="met-header__utility-note"><?php esc_html_e( 'Wholly owned by the International Islamic University Malaysia', 'met-hello-child' ); ?></span>
			<span class="met-header__utility-links">
				<?php foreach ( $met_util_links as $met_util ) : ?>
					<a href="<?php echo esc_url( $met_util['url'] ); ?>"><?php echo esc_html( $met_util['label'] ); ?></a>
				<?php endforeach; ?>
			</span>
		</div>
	</div>

	<div class="met-header__main">
		<div class="met-container met-header__main-inner">
			<?php echo met_hello_child_brand_lockup( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the helper. ?>

			<nav class="met-nav" aria-label="<?php esc_attr_e( 'Primary', 'met-hello-child' ); ?>">
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
							'walker'         => new Met_Hello_Child_Nav_Walker(),
						)
					);
				}
				?>
			</nav>

			<div class="met-header__actions">
				<a class="met-btn met-btn--primary met-header__contact" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'met-hello-child' ); ?></a>
				<button type="button" class="met-menu-btn" id="met-menu-btn" aria-label="<?php esc_attr_e( 'Open menu', 'met-hello-child' ); ?>" aria-expanded="false" aria-controls="met-drawer">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</div>
</header>
