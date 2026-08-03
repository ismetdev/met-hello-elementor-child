/**
 * Live-preview bindings for the "Scroll to Top" Customizer section. Enqueued
 * only on customize_preview_init (inc/scroll-top.php); never loads for a
 * front-end visitor.
 *
 * The on/off setting uses the `refresh` transport (it adds or removes the
 * button from the page entirely), so only colour and position are bound here.
 *
 * @package MetHelloElementorChild
 */
( function ( api ) {
	'use strict';

	api( 'met_hello_child_scroll_top_colour', function ( value ) {
		value.bind( function ( newValue ) {
			document.documentElement.style.setProperty( '--met-tt-accent', newValue );
		} );
	} );

	api( 'met_hello_child_scroll_top_position', function ( value ) {
		value.bind( function ( newValue ) {
			document.body.classList.toggle( 'met-hello-child-tt-left', 'left' === newValue );
		} );
	} );
} )( wp.customize );
