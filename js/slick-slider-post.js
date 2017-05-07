var slickSlider = {

	/**
	 * Init variables.
	 *
	 * @since  0.1
	 * @return void
	 */
	settings: {
		slickActive: false,
	},

	/**
	 * Extend WordPress gallery default settings with Slick Slider settings.
	 *
	 * @since  0.1
	 * @return {string} All media gallery templates.
	 */
	extendGalleryDefaults: function() {

		var defaults = wp.media.gallery.defaults;
		$.extend( defaults, {
			slick_active: s.slickActive,
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

		if ( ! wp.media.gallery.templates ) wp.media.gallery.templates = ['gallery-settings'];
		wp.media.gallery.templates.push( 'slick-slider-settings' );

		wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend( {
			template: function ( view ) {
				var output = '';
				for ( var i in wp.media.gallery.templates ) {
					output += wp.media.template( wp.media.gallery.templates[i] )( view );
				}
				return output;
			}
		});

	},

	/**
	 * Init listeners when media modal has been opened.
	 * 
	 * @since  0.1
	 * @return void
	 */
	initSliderSettings: function() {

		wp.media.view.Modal.prototype.on( 'open', function() {

			slickSlider.toggleSliderListener();
			slickSlider.toggleSliderSettings();

			/**
			 * Fire when the `Add to gallery` button has been clicked.
			 * Use `setTimeout` to get new media modal sidebar.
			 */
			wp.media.frame.on( 'toolbar:render:gallery-edit', function() {
				setTimeout( function() {
					slickSlider.toggleSliderSettings();
					slickSlider.toggleSliderListener();
				}, 0 );
			});
		} );

	},


	/**
	 * Fire when the `Use Slick Slider` checkbox has been changed in the media modal.
	 *
	 * @since  0.5
	 * @return void
	 */
	toggleSliderListener: function() {

		slickSlider.getModalSidebar().find( '[data-setting="slick_active"]' ).on( 'change', function() {
			slickSlider.toggleSliderSettings();
		} );

	},

	/**
	 * Toggle settings element and unneeded `Columns` gallery setting depending on whether Slick Slider is active.
	 *
	 * @since  0.1
	 * @return void
	 */
	toggleSliderSettings: function() {

		$media_sidebar = slickSlider.getModalSidebar();

		s.slickActive = $media_sidebar.find( '[data-setting="slick_active"]' ).is( ':checked' );
		$media_sidebar.find( '.slick-slider-settings-inner' ).toggle( s.slickActive );
		$media_sidebar.find( '[name="columns"]' ).prop( 'disabled', s.slickActive );

	},

	/**
	 * Get the current media modal sidebar element and return it.
	 *
	 * @return jQuery object sidebar of current wp.media.frame.
	 * @since  0.5
	 */
	getModalSidebar: function() {

		return wp.media.frame.content.get( 'view' ).sidebar.$el;

	},

	/**
	 * Call all functions.
	 *
	 * @since  0.1
	 * @return void
	 */
	init: function() {

		s = this.settings;
		$ = jQuery;
		this.extendGalleryDefaults();
		this.initSliderSettings();

	}
}

jQuery( document ).ready( function() {

	'use strict';

	slickSlider.init();

} )
