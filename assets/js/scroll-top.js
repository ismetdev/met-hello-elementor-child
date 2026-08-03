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

	// Re-parent to a direct child of <body>. A `transform` on any ancestor
	// (common in mobile-menu slide animations) makes `position:fixed`
	// position against that ancestor instead of the screen. This guarantees
	// the button always positions against the real viewport.
	if ( button.parentElement !== document.body ) {
		document.body.appendChild( button );
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
