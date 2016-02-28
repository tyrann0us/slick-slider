 jQuery( document ).ready( function( $ ) {
 	$( '#_slick_reset' ).click( function() {
		if ( ! confirm( $( this ).val() + '?' ) ) {
			return false;
		}
	} )
 } )