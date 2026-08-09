/**
 * Header and drawer behaviour: collapse the utility bar on scroll, and drive the
 * mobile drawer (open/close, focus trap, Esc, body scroll lock, aria state).
 * Desktop dropdown/mega opening stays pure CSS (:hover / :focus-within) so it
 * works with JS off; this only adds the mobile layer and the scroll state.
 *
 * Plain IIFE, no jQuery.
 */
( function () {
	'use strict';

	var header = document.getElementById( 'met-header' );

	/* ----- Sticky header: collapse utility bar after scrolling ----- */
	if ( header ) {
		var ticking = false;
		var applyScrollState = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 60 );
			ticking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( applyScrollState );
				ticking = true;
			}
		}, { passive: true } );
		applyScrollState();
	}

	/* ----- Mobile drawer ----- */
	var drawer = document.getElementById( 'met-drawer' );
	var scrim = document.getElementById( 'met-scrim' );
	var openBtn = document.getElementById( 'met-menu-btn' );
	var closeBtn = document.getElementById( 'met-drawer-close' );

	if ( ! drawer || ! scrim || ! openBtn ) {
		return;
	}

	var lastFocused = null;

	function focusable() {
		return drawer.querySelectorAll( 'a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])' );
	}

	function openDrawer() {
		lastFocused = document.activeElement;
		drawer.classList.add( 'is-open' );
		scrim.classList.add( 'is-open' );
		scrim.hidden = false;
		drawer.setAttribute( 'aria-hidden', 'false' );
		openBtn.setAttribute( 'aria-expanded', 'true' );
		document.body.style.overflow = 'hidden';
		var first = focusable()[ 0 ];
		if ( first ) {
			first.focus();
		}
		document.addEventListener( 'keydown', onKeydown );
	}

	function closeDrawer() {
		drawer.classList.remove( 'is-open' );
		scrim.classList.remove( 'is-open' );
		drawer.setAttribute( 'aria-hidden', 'true' );
		openBtn.setAttribute( 'aria-expanded', 'false' );
		document.body.style.overflow = '';
		document.removeEventListener( 'keydown', onKeydown );
		// Keep the scrim out of the a11y tree once closed.
		window.setTimeout( function () {
			if ( ! scrim.classList.contains( 'is-open' ) ) {
				scrim.hidden = true;
			}
		}, 300 );
		if ( lastFocused && lastFocused.focus ) {
			lastFocused.focus();
		}
	}

	function onKeydown( event ) {
		if ( 'Escape' === event.key ) {
			closeDrawer();
			return;
		}
		if ( 'Tab' !== event.key ) {
			return;
		}
		// Trap focus inside the open drawer.
		var nodes = focusable();
		if ( ! nodes.length ) {
			return;
		}
		var first = nodes[ 0 ];
		var last = nodes[ nodes.length - 1 ];
		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	openBtn.addEventListener( 'click', openDrawer );
	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeDrawer );
	}
	scrim.addEventListener( 'click', closeDrawer );
	drawer.querySelectorAll( 'a' ).forEach( function ( link ) {
		link.addEventListener( 'click', closeDrawer );
	} );
} )();
