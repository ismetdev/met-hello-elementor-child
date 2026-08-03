<?php
/**
 * Scroll to Top: a sitewide floating button, on/off, side and colour
 * configurable in the Customizer. See DOCS/DECISIONS.md D26 for why a piece of
 * chrome lives in the theme rather than in Elementor.
 *
 * Self-contained: unlike everything else in this theme, this loads on every
 * page, including ones assets/css/theme.css never reaches (that file is gated
 * to styled views and Page Hero pages, see inc/assets.php). So its CSS and JS
 * carry no dependency on theme.css or its :root tokens.
 *
 * @package MetHelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for the three Customizer settings.
 *
 * @return array{enabled: bool, position: string, colour: string}
 */
function met_hello_child_scroll_top_defaults() {
	return array(
		'enabled'  => true,
		'position' => 'right',
		'colour'   => '#0E3B40',
	);
}

/**
 * Whether the button is switched on.
 *
 * @return bool
 */
function met_hello_child_scroll_top_enabled() {
	$defaults = met_hello_child_scroll_top_defaults();

	return (bool) get_theme_mod( 'met_hello_child_scroll_top_enabled', $defaults['enabled'] );
}

/**
 * The button's side, 'right' or 'left'.
 *
 * @return string
 */
function met_hello_child_scroll_top_position() {
	$defaults = met_hello_child_scroll_top_defaults();
	$position = get_theme_mod( 'met_hello_child_scroll_top_position', $defaults['position'] );

	return in_array( $position, array( 'left', 'right' ), true ) ? $position : $defaults['position'];
}

/**
 * The button's accent colour, as a hex string.
 *
 * @return string
 */
function met_hello_child_scroll_top_colour() {
	$defaults = met_hello_child_scroll_top_defaults();
	$colour   = get_theme_mod( 'met_hello_child_scroll_top_colour', $defaults['colour'] );
	$colour   = sanitize_hex_color( $colour );

	return $colour ? $colour : $defaults['colour'];
}

/**
 * Sanitise the on/off checkbox to a strict boolean.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function met_hello_child_sanitize_scroll_top_enabled( $value ) {
	return (bool) $value;
}

/**
 * Sanitise the position radio against the allowed list.
 *
 * @param string $value Raw value.
 * @return string
 */
function met_hello_child_sanitize_scroll_top_position( $value ) {
	$defaults = met_hello_child_scroll_top_defaults();

	return in_array( $value, array( 'left', 'right' ), true ) ? $value : $defaults['position'];
}

/**
 * Register the "Scroll to Top" Customizer section, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function met_hello_child_customize_register_scroll_top( $wp_customize ) {
	$defaults = met_hello_child_scroll_top_defaults();

	$wp_customize->add_section(
		'met_hello_child_scroll_top',
		array(
			'title'       => __( 'Scroll to Top', 'met-hello-child' ),
			'description' => __( 'A floating button, shown on every page after scrolling down, that returns the reader to the top of the page.', 'met-hello-child' ),
			'priority'    => 160,
		)
	);

	$wp_customize->add_setting(
		'met_hello_child_scroll_top_enabled',
		array(
			'type'              => 'theme_mod',
			'default'           => $defaults['enabled'],
			'sanitize_callback' => 'met_hello_child_sanitize_scroll_top_enabled',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'met_hello_child_scroll_top_enabled',
		array(
			'type'    => 'checkbox',
			'section' => 'met_hello_child_scroll_top',
			'label'   => __( 'Show the button', 'met-hello-child' ),
		)
	);

	$wp_customize->add_setting(
		'met_hello_child_scroll_top_position',
		array(
			'type'              => 'theme_mod',
			'default'           => $defaults['position'],
			'sanitize_callback' => 'met_hello_child_sanitize_scroll_top_position',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'met_hello_child_scroll_top_position',
		array(
			'type'    => 'radio',
			'section' => 'met_hello_child_scroll_top',
			'label'   => __( 'Position', 'met-hello-child' ),
			'choices' => array(
				'right' => __( 'Bottom right', 'met-hello-child' ),
				'left'  => __( 'Bottom left', 'met-hello-child' ),
			),
		)
	);

	$wp_customize->add_setting(
		'met_hello_child_scroll_top_colour',
		array(
			'type'              => 'theme_mod',
			'default'           => $defaults['colour'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'met_hello_child_scroll_top_colour',
			array(
				'section'     => 'met_hello_child_scroll_top',
				'label'       => __( 'Colour', 'met-hello-child' ),
				'description' => __( 'Border, arrow, and the fill on hover. Defaults to the brand petrol. If a caching plugin is active, purge the cache after changing this.', 'met-hello-child' ),
			)
		)
	);
}
add_action( 'customize_register', 'met_hello_child_customize_register_scroll_top' );

/**
 * Enqueue the live-preview bindings inside the Customizer only. Never reaches
 * a front-end visitor.
 */
function met_hello_child_enqueue_scroll_top_customizer_preview() {
	wp_enqueue_script(
		'met-hello-child-scroll-top-customizer',
		MET_HELLO_CHILD_URI . 'assets/js/scroll-top-customizer.js',
		array( 'customize-preview' ),
		MET_HELLO_CHILD_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'met_hello_child_enqueue_scroll_top_customizer_preview' );

/**
 * Enqueue the button's stylesheet and script, sitewide, only when switched on.
 */
function met_hello_child_enqueue_scroll_top_assets() {
	if ( ! met_hello_child_scroll_top_enabled() ) {
		return;
	}

	wp_enqueue_style(
		'met-hello-child-scroll-top',
		MET_HELLO_CHILD_URI . 'assets/css/scroll-top.css',
		array(),
		MET_HELLO_CHILD_VERSION
	);

	$met_tt_colour = met_hello_child_scroll_top_colour();
	wp_add_inline_style(
		'met-hello-child-scroll-top',
		sprintf( ':root{--met-tt-accent:%s;}', esc_html( $met_tt_colour ) )
	);

	// A plain bool, not the WP 6.3+ array $args form: this theme states support
	// from WP 6.0. Printing in the footer already means the script runs after
	// the DOM it queries for exists, which is the practical benefit `defer`
	// would add anyway for a script with no other dependencies.
	wp_enqueue_script(
		'met-hello-child-scroll-top',
		MET_HELLO_CHILD_URI . 'assets/js/scroll-top.js',
		array(),
		MET_HELLO_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'met_hello_child_enqueue_scroll_top_assets' );

/**
 * Add the left-position body class when the button is on and set to the left.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function met_hello_child_scroll_top_body_class( $classes ) {
	if ( met_hello_child_scroll_top_enabled() && 'left' === met_hello_child_scroll_top_position() ) {
		$classes[] = 'met-hello-child-tt-left';
	}

	return $classes;
}
add_filter( 'body_class', 'met_hello_child_scroll_top_body_class' );

/**
 * Print the button markup in the footer. Fires on every page: `wp_footer` runs
 * on Elementor Canvas (canvas.php) and via get_footer() on Elementor Full
 * Width and every other template.
 */
function met_hello_child_render_scroll_top_button() {
	if ( ! met_hello_child_scroll_top_enabled() ) {
		return;
	}
	?>
	<button type="button" class="met-to-top" aria-label="<?php esc_attr_e( 'Scroll to top', 'met-hello-child' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
	</button>
	<?php
}
add_action( 'wp_footer', 'met_hello_child_render_scroll_top_button' );
