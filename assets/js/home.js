/**
 * Homepage behaviour: reveal-on-scroll, the hero carousel, and the company
 * filter. Plain IIFE, no jQuery. Progressive: with this script absent, every
 * section shows, the hero shows its first slide, and all company cards show with
 * no dead filter controls.
 */
( function () {
	'use strict';

	var home = document.querySelector( '.met-home' );
	if ( ! home ) {
		return;
	}

	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Signal to CSS that JS is on, so the reveal start-state and the absolute
	// hero stacking apply only when we can actually drive them.
	home.classList.add( 'met-home--js' );

	/* ----- Reveal on scroll ----- */
	var reveals = home.querySelectorAll( '.met-reveal' );
	if ( 'IntersectionObserver' in window && ! reduce ) {
		var revealObserver = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-in' );
					revealObserver.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.12 } );
		reveals.forEach( function ( el ) {
			revealObserver.observe( el );
		} );
	} else {
		reveals.forEach( function ( el ) {
			el.classList.add( 'is-in' );
		} );
	}

	/* ----- Hero carousel ----- */
	( function () {
		var hero = home.querySelector( '.met-hero-home' );
		if ( ! hero ) {
			return;
		}

		var slides = hero.querySelectorAll( '.met-hero-home__slide' );
		var dots = hero.querySelectorAll( '.met-hero-home__dot' );
		var total = slides.length;
		if ( total < 2 ) {
			return;
		}

		var current = 0;
		var timer = null;
		var INTERVAL = 6000;

		function show( next ) {
			next = ( next + total ) % total;
			slides[ current ].classList.remove( 'is-active' );
			slides[ current ].setAttribute( 'aria-hidden', 'true' );
			if ( dots[ current ] ) {
				dots[ current ].classList.remove( 'is-active' );
				dots[ current ].setAttribute( 'aria-selected', 'false' );
			}
			current = next;
			slides[ current ].classList.add( 'is-active' );
			slides[ current ].removeAttribute( 'aria-hidden' );
			if ( dots[ current ] ) {
				dots[ current ].classList.add( 'is-active' );
				dots[ current ].setAttribute( 'aria-selected', 'true' );
			}
		}

		function start() {
			if ( reduce || null !== timer ) {
				return;
			}
			timer = window.setInterval( function () {
				show( current + 1 );
			}, INTERVAL );
		}

		function stop() {
			if ( null !== timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		function restart() {
			stop();
			start();
		}

		dots.forEach( function ( dot, index ) {
			dot.addEventListener( 'click', function () {
				show( index );
				restart();
			} );
		} );

		hero.querySelectorAll( '.met-hero-home__arrow-btn' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				show( current + ( 'prev' === btn.getAttribute( 'data-dir' ) ? -1 : 1 ) );
				restart();
			} );
		} );

		// Pause when off-screen, and when the tab is hidden.
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						start();
					} else {
						stop();
					}
				} );
			}, { threshold: 0.3 } ).observe( hero );
		} else {
			start();
		}

		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				stop();
			} else {
				start();
			}
		} );
	} )();

	/* ----- Company filter ----- */
	( function () {
		var filters = home.querySelector( '.met-companies__filters' );
		var grid = home.querySelector( '.met-companies__grid' );
		if ( ! filters || ! grid ) {
			return;
		}

		// Only expose the controls once JS can act on them.
		filters.hidden = false;

		var buttons = filters.querySelectorAll( 'button' );
		var cards = grid.querySelectorAll( '.met-co-card' );

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				buttons.forEach( function ( other ) {
					other.setAttribute( 'aria-pressed', other === button ? 'true' : 'false' );
				} );
				var filter = button.getAttribute( 'data-filter' );
				cards.forEach( function ( card ) {
					var match = 'all' === filter || card.getAttribute( 'data-cat' ) === filter;
					card.classList.toggle( 'is-hidden', ! match );
				} );
			} );
		} );
	} )();
} )();
