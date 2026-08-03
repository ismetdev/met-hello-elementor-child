/**
 * Scroll to Top: show after ~1 screen of scroll, scroll to top on click.
 *
 * @package MetHelloElementorChild
 */
( function () {
	'use strict';

	var button = document.querySelector( '.met-to-top' );
	if ( ! button ) {
		return;
	}

	var threshold    = window.innerHeight;
	var ticking      = false;
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function updateVisibility() {
		button.classList.toggle( 'is-visible', window.pageYOffset > threshold );
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( updateVisibility );
				ticking = true;
			}
		},
		{ passive: true }
	);

	updateVisibility(); // Covers a page that loads already scrolled.

	button.addEventListener( 'click', function () {
		window.scrollTo( { top: 0, behavior: reduceMotion ? 'auto' : 'smooth' } );
	} );
} )();
