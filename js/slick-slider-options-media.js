var slickSlider = {

	/**
	 * Prepares the settings markup to be made collapsible using jQuery UI Accordion and activate it.
	 *
	 * @since  0.1
	 * @return void
	 */
	collapseSettings: function() {

		var settingsWrap = '<div class="slick-slider-collapse"><div class="collapse-inner"></div></div>';
		$( '.slick-slider-option' ).parents( '.form-table' ).wrap( settingsWrap );

		var $collapse = $( '.slick-slider-collapse' );
		$( '.collapse-header' ).prependTo( $collapse ).removeClass( 'hidden' );
		$( '#_slick_reset' ).parent().appendTo( $( '.collapse-inner' ) );

		$( '.collapse-header' ).click( function() {
			var button_text = $( this ).text();
			$( this ).text( $( this ).data( 'collapse-header-text' ) );
			$( this ).data( 'collapse-header-text', button_text );
		} );

		$collapse.accordion( {
			active: false,
			collapsible: true,
		} );

	},

	/**
	 * Lets the user confirm he wants to reset all settings.
	 *
	 * @since  0.1
	 * @return {bool} False if user aborts.
	 */
	confirmReset: function() {
		
		$( '#_slick_reset' ).click( function() {
			if ( ! confirm( $( this ).val() + '?' ) ) {
				return false;
			}
		} );

	},

	/**
	 * Calls all functions.
	 *
	 * @since  0.1
	 * @return void
	 */
	init: function() {
		$ = jQuery;
		this.collapseSettings();
		this.confirmReset();
	}

}

jQuery( document ).ready( function() {

	'use strict';

	slickSlider.init();

} )
