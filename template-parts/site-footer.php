<?php
/**
 * Custom site footer: brand and contact block, three link columns, social
 * icons, and a legal bottom row. Each column renders its assigned menu location
 * when one is set, and a sensible default list otherwise, so the footer looks
 * finished before any menus are assigned.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print a footer column: the assigned menu, or a default link list.
 *
 * @param string                          $location Menu location slug.
 * @param array<int,array<string,string>> $fallback Default links (url, label).
 */
$met_footer_column = static function ( $location, $fallback ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'met-footer__list',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		return;
	}

	echo '<ul class="met-footer__list">';
	foreach ( $fallback as $link ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
};

$met_socials = array(
	array(
		'icon'  => 'facebook',
		'url'   => 'https://www.facebook.com/iiumholdings',
		'label' => __( 'Facebook', 'met-hello-child' ),
	),
	array(
		'icon'  => 'instagram',
		'url'   => 'https://www.instagram.com/iiumholdings/',
		'label' => __( 'Instagram', 'met-hello-child' ),
	),
	array(
		'icon'  => 'linkedin',
		'url'   => 'https://www.linkedin.com/company/iiumholdings/',
		'label' => __( 'LinkedIn', 'met-hello-child' ),
	),
	array(
		'icon'  => 'youtube',
		'url'   => 'https://www.youtube.com/@IIUMHoldings',
		'label' => __( 'YouTube', 'met-hello-child' ),
	),
);
?>
<footer class="met-footer">
	<div class="met-container">
		<div class="met-footer__top">
			<div class="met-footer__brand">
				<?php echo met_hello_child_brand_lockup( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the helper. ?>
				<p class="met-footer__about"><?php esc_html_e( 'Level 3, Muhammad Abdul-Rauf Building, International Islamic University Malaysia, Jalan Gombak, 53100 Kuala Lumpur.', 'met-hello-child' ); ?></p>
				<p class="met-footer__about met-footer__about--tight">
					<a href="mailto:admin@iiumholdings.com.my">admin@iiumholdings.com.my</a><br>
					<a href="tel:+60364214331">+603 6421 4331</a>
				</p>
				<div class="met-footer__socials">
					<?php foreach ( $met_socials as $met_social ) : ?>
						<a href="<?php echo esc_url( $met_social['url'] ); ?>" aria-label="<?php echo esc_attr( $met_social['label'] ); ?>" rel="noopener" target="_blank">
							<?php echo met_hello_child_social_icon( $met_social['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static SVG. ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="met-footer__col">
				<h2 class="met-footer__h"><?php esc_html_e( 'Company', 'met-hello-child' ); ?></h2>
				<?php
				$met_footer_column(
					'met-footer-company',
					array(
						array(
							'url'   => home_url( '/iium-holdings-group-of-companies/' ),
							'label' => __( 'About Us', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/corporate-profile/' ),
							'label' => __( 'Corporate Profile', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/rise2030-strategy-blueprint/' ),
							'label' => __( 'RISE2030', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/news-announcement/' ),
							'label' => __( 'Newsroom', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/careers/' ),
							'label' => __( 'Careers', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/contact-us/' ),
							'label' => __( 'Contact Us', 'met-hello-child' ),
						),
					)
				);
				?>
			</div>

			<div class="met-footer__col">
				<h2 class="met-footer__h"><?php esc_html_e( 'Governance', 'met-hello-child' ); ?></h2>
				<?php
				$met_footer_column(
					'met-footer-governance',
					array(
						array(
							'url'   => home_url( '/board-of-directors/' ),
							'label' => __( 'Board of Directors', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/board-charter/' ),
							'label' => __( 'Board Charter', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/code-of-business-conduct/' ),
							'label' => __( 'Code of Business Conduct', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/whistleblowing/' ),
							'label' => __( 'Whistleblowing', 'met-hello-child' ),
						),
					)
				);
				?>
			</div>

			<div class="met-footer__col">
				<h2 class="met-footer__h"><?php esc_html_e( 'Our Businesses', 'met-hello-child' ); ?></h2>
				<?php
				$met_footer_column(
					'met-footer-business',
					array(
						array(
							'url'   => home_url( '/business/#education' ),
							'label' => __( 'Education', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/business/#facilities' ),
							'label' => __( 'Facilities', 'met-hello-child' ),
						),
						array(
							'url'   => home_url( '/business/#healthcare' ),
							'label' => __( 'Healthcare', 'met-hello-child' ),
						),
					)
				);
				?>
			</div>
		</div>

		<div class="met-footer__bottom">
			<span>
				<?php
				printf(
					/* translators: %s: current year. */
					esc_html__( '© %s IIUM Holdings Sdn. Bhd. All rights reserved.', 'met-hello-child' ),
					esc_html( gmdate( 'Y' ) )
				);
				?>
			</span>
			<span class="met-footer__legal">
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'met-hello-child' ); ?></a> ·
				<a href="<?php echo esc_url( home_url( '/personal-data-protection-notice/' ) ); ?>"><?php esc_html_e( 'PDPA Notice', 'met-hello-child' ); ?></a> ·
				<a href="<?php echo esc_url( home_url( '/terms-of-use/' ) ); ?>"><?php esc_html_e( 'Terms of Use', 'met-hello-child' ); ?></a> ·
				<a href="<?php echo esc_url( home_url( '/sitemap/' ) ); ?>"><?php esc_html_e( 'Sitemap', 'met-hello-child' ); ?></a>
			</span>
		</div>
	</div>
</footer>
