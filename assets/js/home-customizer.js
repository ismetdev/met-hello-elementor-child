/**
 * Live preview for the Homepage Customizer section: the four stat pairs update
 * in place without a full refresh. Runs only inside the Customizer preview
 * frame (enqueued by met_hello_child_enqueue_home_customizer_preview()).
 */
( function ( api ) {
	'use strict';

	function bindStat( slot ) {
		api( 'met_hello_child_home_stat' + slot + '_number', function ( value ) {
			value.bind( function ( to ) {
				var el = document.querySelectorAll( '.met-stats .met-stat' )[ slot - 1 ];
				if ( el ) {
					var n = el.querySelector( '.met-stat__n' );
					if ( n ) {
						n.textContent = to;
					}
				}
			} );
		} );

		api( 'met_hello_child_home_stat' + slot + '_label', function ( value ) {
			value.bind( function ( to ) {
				var el = document.querySelectorAll( '.met-stats .met-stat' )[ slot - 1 ];
				if ( el ) {
					var t = el.querySelector( '.met-stat__t' );
					if ( t ) {
						t.textContent = to;
					}
				}
			} );
		} );
	}

	[ 1, 2, 3, 4 ].forEach( bindStat );
} )( wp.customize );
