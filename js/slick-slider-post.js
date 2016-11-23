var slickSlider = {

	/**
	 * Inits variables.
	 *
	 * @since  0.1
	 * @return void
	 */
	settings: {
		$slickSliderSettings: false,
		$invalidGallerySettings: false,
		slickActive: false,
	},

	/**
	 * Extends WordPress gallery default settings with Slick Slider settings.
	 *
	 * @since  0.1
	 * @return {string} All media gallery templates.
	 */
	extendGalleryDefaults: function() {

		var media = wp.media, defaults = media.gallery.defaults;
		$.extend( defaults, {
			slick_active: false,
		} );
		$.each( slider_defaults, function( key, value ) {
			switch ( value.type ) {
				case 'boolean' :
					$.extend( defaults, {
						[value['setting']]: '1' == value.value ? true : false,
					} );
					break;
				case 'integer' :
					$.extend( defaults, {
						[value['setting']]: value.value,
					} );
					break;
				case 'string' :
				case 'function' :
					$.extend( defaults, {
						[value['setting']]: value.value,
					} );
					break;
				case 'select' :
					$.extend( defaults, {
						[value['setting']]: value.value,
					} );
					break;
				default :
					break;
			}
		} );

		if ( ! media.gallery.templates ) media.gallery.templates = ['gallery-settings'];
		media.gallery.templates.push( 'slick-slider-gallery-settings' );

		media.view.Settings.Gallery = media.view.Settings.Gallery.extend( {
			template: function ( view ) {
				var output = '';
				for ( var i in media.gallery.templates ) {
					output += media.template( media.gallery.templates[i] )( view );
				}
				return output;
			}
		});

	},

	/**
	 * Inits Slick settings if Slick Slider is active.
	 * 
	 * @since  0.1
	 * @return void
	 */
	initSliderSettings: function() {

		$( document ).on( 'click', '.dashicons-edit, .media-button-gallery', function() {

			s.slickActive = $( '[data-setting="slick_active"]' ).is( ':checked' );
			s.$invalidGallerySettings = $( '[name="columns"]' ).parent();
			s.$invalidGallerySettings.css( 'display', ! s.slickActive ? 'block' : 'none' );
	
			s.$slickSliderSettings = $( '.slick-slider-settings-inner' );
			s.$slickSliderSettings.css( 'display', s.slickActive ? 'block' : 'none' );

		} );

	},

	/**
	 * Toggles settings element when Slick Slider gets activated or deactivated.
	 *
	 * @since  0.1
	 * @return void
	 */
	toggleSliderSettings: function() {

		$( document ).on( 'click', '.media-modal .slick-slider-toggle-settings input', function() {

			s.$slickSliderSettings.add( s.$invalidGallerySettings ).toggle();

		} );

	},

	/**
	 * Calls all functions.
	 *
	 * @since  0.1
	 * @return void
	 */
	init: function() {

		s = this.settings;
		$ = jQuery;
		this.extendGalleryDefaults();
		this.initSliderSettings();
		this.toggleSliderSettings();

	}
}

jQuery( document ).ready( function() {

	'use strict';

	slickSlider.init();

} )