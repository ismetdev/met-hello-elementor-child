<?php
/**
 * Site addresses and sections: settings that let one site keep its own
 * addresses and category slugs without any redirect.
 *
 * Every setting is empty by default. An empty setting means "use the built-in
 * default", which equals what the theme did before 1.12.4, so a site that sets
 * nothing sees no change. Each helper also has a filter, for code that needs to
 * set a value without the Customizer.
 *
 * Settings live in Appearance, Customize, "IIUM Holdings: addresses and
 * sections". They are theme mods, so they belong to this theme only.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Built-in defaults. These equal the hard-coded values of 1.12.3 and earlier.
 *
 * @return array{url: array<string,string>, cat: array<string,string>}
 */
function met_hello_child_site_defaults() {
	return array(
		'url' => array(
			'tenders' => '/tenders/',
			'careers' => '/careers/',
			'contact' => '/contact-us/',
			'news'    => '/news-announcement/',
		),
		'cat' => array(
			'announcements'  => 'announcements',
			'press_releases' => 'press-releases',
			'wishes'         => 'wishes',
		),
	);
}

/**
 * Normalise a slug or a path of slugs, for example "iium-schools" or
 * "business/iium-schools". Returns '' when nothing usable is left.
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_slug_path( $value ) {
	$segments = array();

	foreach ( explode( '/', trim( (string) $value ) ) as $segment ) {
		$segment = sanitize_title( trim( $segment ) );
		if ( '' !== $segment ) {
			$segments[] = $segment;
		}
	}

	return implode( '/', $segments );
}

/**
 * Normalise a page address to a path such as "/tender/". Accepts a bare slug, a
 * path, or a full address on this site. Returns '' when nothing usable is left,
 * which means "use the default".
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_page_path( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	$path = wp_parse_url( $value, PHP_URL_PATH );
	if ( ! is_string( $path ) ) {
		$path = $value;
	}

	// A site in a sub-folder: home_url() adds the folder, so do not keep it twice.
	$home_path = wp_parse_url( home_url(), PHP_URL_PATH );
	if ( is_string( $home_path ) && '' !== trim( $home_path, '/' ) ) {
		$prefix = '/' . trim( $home_path, '/' ) . '/';
		if ( 0 === strpos( '/' . ltrim( $path, '/' ), $prefix ) ) {
			$path = substr( '/' . ltrim( $path, '/' ), strlen( $prefix ) - 1 );
		}
	}

	$path = met_hello_child_sanitize_slug_path( $path );

	return '' === $path ? '' : '/' . $path . '/';
}

/**
 * Normalise one category slug. Returns '' for "use the default".
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_category_slug( $value ) {
	return sanitize_title( trim( (string) $value ) );
}

/**
 * Turn a comma or line separated list into a list of slug paths.
 *
 * @param string $value Raw list.
 * @return string[]
 */
function met_hello_child_parse_slug_list( $value ) {
	$slugs = array();

	foreach ( preg_split( '/[\s,]+/', (string) $value ) as $item ) {
		$item = met_hello_child_sanitize_slug_path( $item );
		if ( '' !== $item ) {
			$slugs[] = $item;
		}
	}

	return array_values( array_unique( $slugs ) );
}

/**
 * Sanitise the company slug list for storage.
 *
 * @param string $value Raw list.
 * @return string Comma separated slugs.
 */
function met_hello_child_sanitize_company_slugs( $value ) {
	return implode( ', ', met_hello_child_parse_slug_list( $value ) );
}

/**
 * Full address of one of the pages the theme links to.
 *
 * @param string $key tenders, careers, contact or news.
 * @return string
 */
function met_hello_child_page_url( $key ) {
	$defaults = met_hello_child_site_defaults();
	if ( ! isset( $defaults['url'][ $key ] ) ) {
		return home_url( '/' );
	}

	$path = met_hello_child_sanitize_page_path( get_theme_mod( 'met_hello_child_url_' . $key, '' ) );
	if ( '' === $path ) {
		$path = $defaults['url'][ $key ];
	}

	/**
	 * Filter the address of a page the theme links to.
	 *
	 * @param string $url Full address.
	 * @param string $key tenders, careers, contact or news.
	 */
	return (string) apply_filters( 'met_hello_child_page_url', home_url( $path ), $key );
}

/**
 * Slug of one of the categories the theme reads.
 *
 * @param string $key announcements, press_releases or wishes.
 * @return string
 */
function met_hello_child_category_slug( $key ) {
	$defaults = met_hello_child_site_defaults();
	if ( ! isset( $defaults['cat'][ $key ] ) ) {
		return '';
	}

	$slug = met_hello_child_sanitize_category_slug( get_theme_mod( 'met_hello_child_cat_' . $key, '' ) );
	if ( '' === $slug ) {
		$slug = $defaults['cat'][ $key ];
	}

	/**
	 * Filter the slug of a category the theme reads.
	 *
	 * @param string $slug Category slug.
	 * @param string $key  announcements, press_releases or wishes.
	 */
	return (string) apply_filters( 'met_hello_child_category_slug', $slug, $key );
}

/**
 * Map a built-in category slug to this site's slug. Lets a [met_posts] shortcode
 * copied from another site, such as category="press-releases", still find this
 * site's "press-release" category. Any other slug passes through unchanged.
 *
 * @param string $slug Slug written in a shortcode.
 * @return string
 */
function met_hello_child_map_category_slug( $slug ) {
	$defaults = met_hello_child_site_defaults();
	$key      = array_search( $slug, $defaults['cat'], true );

	return false === $key ? $slug : met_hello_child_category_slug( $key );
}

/**
 * Company page slugs, in the order to show them. Empty means "use the child
 * pages of the business page", which is the built-in behaviour.
 *
 * @return string[]
 */
function met_hello_child_company_slugs() {
	$slugs = met_hello_child_parse_slug_list( get_theme_mod( 'met_hello_child_company_slugs', '' ) );

	/**
	 * Filter the company page slugs.
	 *
	 * @param string[] $slugs Slugs or paths, in display order.
	 */
	$slugs = apply_filters( 'met_hello_child_company_slugs', $slugs );

	if ( ! is_array( $slugs ) ) {
		return array();
	}

	return array_values( array_unique( array_filter( array_map( 'met_hello_child_sanitize_slug_path', $slugs ) ) ) );
}

/**
 * Register the Customizer section, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function met_hello_child_customize_register_site_settings( $wp_customize ) {
	$defaults = met_hello_child_site_defaults();

	$wp_customize->add_section(
		'met_hello_child_site_settings',
		array(
			'title'       => __( 'IIUM Holdings: addresses and sections', 'met-hello-child' ),
			'description' => __( 'Tell the theme where this site keeps its pages and categories. Leave a field empty to use the default shown under it.', 'met-hello-child' ),
			'priority'    => 161,
		)
	);

	$pages = array(
		'tenders' => __( 'Tenders page address', 'met-hello-child' ),
		'careers' => __( 'Careers page address', 'met-hello-child' ),
		'contact' => __( 'Contact page address', 'met-hello-child' ),
		'news'    => __( 'News page address', 'met-hello-child' ),
	);
	foreach ( $pages as $key => $label ) {
		$id = 'met_hello_child_url_' . $key;
		$wp_customize->add_setting(
			$id,
			array(
				'type'              => 'theme_mod',
				'default'           => '',
				'sanitize_callback' => 'met_hello_child_sanitize_page_path',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'type'        => 'text',
				'section'     => 'met_hello_child_site_settings',
				'label'       => $label,
				/* translators: %s: default address, for example /tenders/ */
				'description' => sprintf( __( 'Default: %s', 'met-hello-child' ), $defaults['url'][ $key ] ),
				'input_attrs' => array( 'placeholder' => $defaults['url'][ $key ] ),
			)
		);
	}

	$cats = array(
		'announcements'  => __( 'Announcements category slug', 'met-hello-child' ),
		'press_releases' => __( 'Press releases category slug', 'met-hello-child' ),
		'wishes'         => __( 'Wishes category slug', 'met-hello-child' ),
	);
	foreach ( $cats as $key => $label ) {
		$id = 'met_hello_child_cat_' . $key;
		$wp_customize->add_setting(
			$id,
			array(
				'type'              => 'theme_mod',
				'default'           => '',
				'sanitize_callback' => 'met_hello_child_sanitize_category_slug',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'type'        => 'text',
				'section'     => 'met_hello_child_site_settings',
				'label'       => $label,
				/* translators: %s: default category slug, for example announcements */
				'description' => sprintf( __( 'Default: %s', 'met-hello-child' ), $defaults['cat'][ $key ] ),
				'input_attrs' => array( 'placeholder' => $defaults['cat'][ $key ] ),
			)
		);
	}

	$wp_customize->add_setting(
		'met_hello_child_company_slugs',
		array(
			'type'              => 'theme_mod',
			'default'           => '',
			'sanitize_callback' => 'met_hello_child_sanitize_company_slugs',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'met_hello_child_company_slugs',
		array(
			'type'        => 'textarea',
			'section'     => 'met_hello_child_site_settings',
			'label'       => __( 'Company page slugs', 'met-hello-child' ),
			'description' => __( 'Comma separated page slugs, in the order to show them in the portfolio grid. Default: empty, which shows the child pages of the page "business".', 'met-hello-child' ),
		)
	);
}
add_action( 'customize_register', 'met_hello_child_customize_register_site_settings' );
