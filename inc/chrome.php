<?php
/**
 * Site chrome: the custom header (utility bar, sticky main bar, mega menu,
 * mobile drawer) and footer, shipped behind a Customizer toggle that is off by
 * default. When off, header.php and footer.php fall through to the parent Hello
 * Elementor theme, so the switch is a one-click, instant rollback. See
 * DECISIONS D38.
 *
 * The parent currently renders the site chrome (HFE owns nothing: both
 * get_hfe_header_id() and get_hfe_footer_id() return false). The four HFE
 * filters below are defensive insurance only, added at load time when the
 * toggle is on, in case a header/footer is ever assigned in HFE.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MET_HELLO_CHILD_DIR . 'inc/class-met-hello-child-nav-walker.php';
require_once MET_HELLO_CHILD_DIR . 'inc/class-met-hello-child-drawer-walker.php';

/**
 * Whether the custom chrome is switched on. Default false, mirroring
 * met_hello_child_scroll_top_enabled().
 *
 * @return bool
 */
function met_hello_child_chrome_enabled() {
	return (bool) get_theme_mod( 'met_hello_child_chrome_enabled', false );
}

/**
 * Sanitise the on/off checkbox to a strict boolean.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function met_hello_child_sanitize_chrome_enabled( $value ) {
	return (bool) $value;
}

/**
 * When the chrome is on, tell Header Footer Elementor to render nothing, so the
 * child header.php / footer.php are the only source of site chrome. Insurance
 * only; HFE owns nothing today.
 */
function met_hello_child_disable_hfe_when_chrome_on() {
	if ( ! met_hello_child_chrome_enabled() ) {
		return;
	}

	add_filter( 'hfe_header_enabled', '__return_false' );
	add_filter( 'hfe_footer_enabled', '__return_false' );
	add_filter( 'enable_hfe_render_header', '__return_false' );
	add_filter( 'enable_hfe_render_footer', '__return_false' );
}
add_action( 'after_setup_theme', 'met_hello_child_disable_hfe_when_chrome_on' );

/**
 * Register theme support the chrome needs. custom-logo lets the owner upload the
 * site logo under Appearance, Customize, Site Identity; the header and footer
 * render it in place of the site name. Registered unconditionally so the control
 * is always available, even while the chrome toggle is off.
 */
function met_hello_child_chrome_theme_support() {
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
}
add_action( 'after_setup_theme', 'met_hello_child_chrome_theme_support' );

/**
 * Register the three footer menu locations. The primary menu reuses the
 * parent's menu-1, so no new primary location is registered here.
 */
function met_hello_child_register_footer_menus() {
	register_nav_menus(
		array(
			'met-footer-company'    => __( 'Footer: Company', 'met-hello-child' ),
			'met-footer-governance' => __( 'Footer: Governance', 'met-hello-child' ),
			'met-footer-business'   => __( 'Footer: Our Businesses', 'met-hello-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'met_hello_child_register_footer_menus' );

/**
 * Register the Customizer "Site Header & Footer" section: a single on/off
 * checkbox. Shaped like met_hello_child_customize_register_scroll_top().
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function met_hello_child_customize_register_chrome( $wp_customize ) {
	$wp_customize->add_section(
		'met_hello_child_chrome',
		array(
			'title'       => __( 'Site Header & Footer', 'met-hello-child' ),
			'description' => __( 'Use the theme\'s custom header and footer in place of the default ones. Off by default. Assigning a menu to a location never changes this switch. If a caching plugin is active, purge the cache after changing this.', 'met-hello-child' ),
			'priority'    => 159,
		)
	);

	$wp_customize->add_setting(
		'met_hello_child_chrome_enabled',
		array(
			'type'              => 'theme_mod',
			'default'           => false,
			'sanitize_callback' => 'met_hello_child_sanitize_chrome_enabled',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'met_hello_child_chrome_enabled',
		array(
			'type'    => 'checkbox',
			'section' => 'met_hello_child_chrome',
			'label'   => __( 'Use the custom header and footer', 'met-hello-child' ),
		)
	);

	$wp_customize->add_setting(
		'met_hello_child_anniversary_logo',
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
			'met_hello_child_anniversary_logo',
			array(
				'section'     => 'met_hello_child_chrome',
				'label'       => __( '25th Anniversary logo', 'met-hello-child' ),
				'description' => __( 'Shown next to the site logo in the header and footer. Leave empty to show the plain "25 Years" mark. A transparent PNG or SVG works best.', 'met-hello-child' ),
				'mime_type'   => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'met_hello_child_anniversary_url',
		array(
			'type'              => 'theme_mod',
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'met_hello_child_anniversary_url',
		array(
			'type'        => 'url',
			'section'     => 'met_hello_child_chrome',
			'label'       => __( '25th Anniversary link', 'met-hello-child' ),
			'description' => __( 'Where the anniversary logo links to, for example the 25th Anniversary page.', 'met-hello-child' ),
			'input_attrs' => array(
				'placeholder' => home_url( '/iium-holdings-25th-anniversary/' ),
			),
		)
	);
}
add_action( 'customize_register', 'met_hello_child_customize_register_chrome' );

/**
 * Enqueue the chrome stylesheet and script sitewide, only when switched on.
 * chrome.css reads theme.json custom properties directly (printed sitewide by
 * core), so it needs no stylesheet dependency.
 */
function met_hello_child_enqueue_chrome_assets() {
	if ( ! met_hello_child_chrome_enabled() ) {
		return;
	}

	// Depends on the parent's reset.css ("hello-elementor") and theme.css
	// ("hello-elementor-theme-style") so this file always prints AFTER them.
	// Without the dependency WordPress printed the parent's reset last, and its
	// `[type=button], button { border: 1px solid #c36; display: inline-block }`
	// rule has the same specificity as `.met-menu-btn`, so it won on source
	// order: the hamburger stayed visible at every width and every button wore a
	// pink border. Declaring the dependency, rather than raising specificity rule
	// by rule, fixes the whole class of conflict once.
	wp_enqueue_style(
		'met-hello-child-chrome',
		MET_HELLO_CHILD_URI . 'assets/css/chrome.css',
		array( 'hello-elementor', 'hello-elementor-theme-style' ),
		MET_HELLO_CHILD_VERSION
	);

	// Plain bool for in-footer; WP 6.0 support. See inc/scroll-top.php.
	wp_enqueue_script(
		'met-hello-child-chrome',
		MET_HELLO_CHILD_URI . 'assets/js/chrome.js',
		array(),
		MET_HELLO_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_chrome_assets' );

/**
 * Purge LiteSpeed Cache when the chrome toggle (or any Customizer setting) is
 * saved, so a cached page does not keep the old header/footer. No-op when
 * LiteSpeed is not active.
 */
function met_hello_child_purge_cache_on_customize_save() {
	if ( has_action( 'litespeed_purge_all' ) ) {
		do_action( 'litespeed_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache's own hook, invoked deliberately.
	}
}
add_action( 'customize_save_after', 'met_hello_child_purge_cache_on_customize_save' );

/**
 * The brand lockup used in the header, drawer and footer. Returns trusted
 * markup: the site logo (the Customizer Site Identity logo when set, else the
 * site name) wrapped in the home link, followed by the 25th Anniversary logo
 * linked to its page (or the plain "25 Years" mark when no logo is uploaded).
 *
 * The home link wraps only the site logo, and the anniversary link is a
 * sibling, so there are never nested anchors. Callers therefore print this
 * directly and must not wrap it in their own link.
 *
 * @param bool $on_dark Whether the lockup sits on a dark ground.
 * @return string
 */
function met_hello_child_brand_lockup( $on_dark = false ) {
	$home = esc_url( home_url( '/' ) );
	$name = get_bloginfo( 'name' );

	// Site logo: Site Identity logo when set, else the accented site name.
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_html = wp_get_attachment_image(
			$logo_id,
			'full',
			false,
			array(
				'class'    => 'met-brand__img',
				'alt'      => $name,
				'decoding' => 'async',
			)
		);
	} else {
		$parts     = explode( ' ', $name, 2 );
		$tail      = isset( $parts[1] ) ? ' <b>' . esc_html( $parts[1] ) . '</b>' : '';
		$logo_html = '<span class="met-brand__logo">' . esc_html( $parts[0] ) . $tail . '</span>';
	}

	$out  = '<div class="met-brand">';
	$out .= '<a class="met-brand-link" href="' . $home . '" aria-label="' . esc_attr( $name ) . '">' . $logo_html . '</a>';

	// Anniversary logo, linked to its page, or the plain mark as a fallback.
	$anniv_id  = (int) get_theme_mod( 'met_hello_child_anniversary_logo', 0 );
	$anniv_url = get_theme_mod( 'met_hello_child_anniversary_url', '' );
	if ( $anniv_id ) {
		$anniv_img = wp_get_attachment_image(
			$anniv_id,
			'full',
			false,
			array(
				'class'    => 'met-anniv-img',
				'alt'      => __( '25th Anniversary', 'met-hello-child' ),
				'decoding' => 'async',
			)
		);
		if ( $anniv_url ) {
			$out .= '<a class="met-anniv-link" href="' . esc_url( $anniv_url ) . '">' . $anniv_img . '</a>';
		} else {
			$out .= '<span class="met-anniv-link">' . $anniv_img . '</span>';
		}
	} else {
		$anniv_class = $on_dark ? 'met-anniv met-anniv--on-dark' : 'met-anniv';
		$out        .= '<span class="' . esc_attr( $anniv_class ) . '"><span class="met-anniv__n">25</span> ' . esc_html__( 'Years', 'met-hello-child' ) . '</span>';
	}

	$out .= '</div>';

	return $out;
}
