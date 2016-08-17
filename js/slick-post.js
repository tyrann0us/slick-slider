var slickSlider = {

	settings: {
		$slickSliderSettings: false,
		$invalidGallerySettings: false,
		slickActive: false,
	},

	initSliderSettings: function() {

		$( document ).on( 'click', '.dashicons-edit, .media-button-gallery', function() {

			s.slickActive = $( '[data-setting="slick_active"]' ).is( ':checked' );
			s.$invalidGallerySettings = $( '[name="columns"]' ).parent();
			s.$invalidGallerySettings.css( 'display', ! s.slickActive ? 'block' : 'none' );
	
			s.$slickSliderSettings = $( '.slick-slider-settings-inner' );
			s.$slickSliderSettings.css( 'display', s.slickActive ? 'block' : 'none' );

		} );

	},

	toggleSliderSettings: function() {

		$( document ).on( 'click', '.media-modal .slick-slider-toggle-settings input', function() {

			s.$slickSliderSettings.add( s.$invalidGallerySettings ).toggle();

		} );

	},

	init: function() {

		s = this.settings;
		$ = jQuery;
		this.initSliderSettings();
		this.toggleSliderSettings();

	}
}

jQuery( document ).ready( function() {

	'use strict';

	slickSlider.init();

} )