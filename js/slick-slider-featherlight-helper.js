var slickSliderFeatherlightHelper = {

	/**
	 * Provides jQuery selectors/objects to be used later on.
	 *
	 * @since  0.4
	 * 
	 * @return void
	 */
	settings: {

		body: jQuery( document.body ),
		slickSliderSelector: '.slick-slider',
		slickSliderLinkSelector: '.slide a',
		slickSliderCaptionSelector: '.slide__caption',
		featherlightInstanceSelector: '.featherlight',
		featherlightContentSelector: '.featherlight-content',
		featherlightNavigationArrowsSelector: '.featherlight-content span[class^="featherlight-"]',
		targetSlider: null,

	},

	/**
	 * Finds and creates Featherlight galleries for Slick sliders.
	 *
	 * @since  0.4
	 * 
	 * @return void
	 */
	findSliders: function() {

		var $slider = s.body.find( s.slickSliderSelector );
		if ( $slider.length ) {
			$.each( $slider, this.buildGalleries );
		}

	},

	/**
	 * Callback function to initialize Featherlight galleries.
	 * Sets and deletes target slider object and adds captions, if available.
	 *
	 * @since  0.4
	 * 
	 * @return void
	 */
	buildGalleries: function( index, element ) {

		var $sliderObj   = $( element ),
			$sliderItems = $sliderObj.find( s.slickSliderLinkSelector ).filter( slickSliderFeatherlightHelper.testImages );

		$sliderItems.featherlightGallery( {
			afterContent: function() {
				var object    = this.$instance,
					target    = this.$currentTarget,
					parent    = target.parent(),
					caption   = parent.find( s.slickSliderCaptionSelector );

				s.targetSlider = target.parents( s.slickSliderSelector );

				object.find( '.caption' ).remove();
				if ( caption.length ) {
					$( '<div class="caption">' ).appendTo( object.find( s.featherlightContentSelector ) );
					var captionElement = document.getElementsByClassName( 'caption' )[0];
					captionElement.innerHTML = caption.html();
				}
			},
			afterClose: function() {
				s.targetSlider = null;
			},
		} );

	},

	/**
	 * Checks href targets to see if a given anchor is linking to an image.
	 *
	 * @since  0.4
	 * 
	 * @return mixed
	 */
	testImages: function( index, element ) {

		return /(.png|.jpg|.jpeg|.gif|.tiff|.bmp)$/.test(
			$( element ).attr( 'href' ).toLowerCase().split( '?' )[0].split( '#' )[0]
		);

	},

	/**
	 * Listens for previous/next arrow click, touch swipe and keyboard navigation.
	 * 	 
	 * @since  0.4
	 * 
	 * @return void
	 */
	featherlightNavigationCallback: function() {

		var self = this,
			featherlightNamespace = $.featherlight.prototype.namespace,
			featherlightEvents = 'previous.' + featherlightNamespace + ' next.' + featherlightNamespace;

		$( document ).on( featherlightEvents, s.featherlightNavigationArrowsSelector, function( e ) {
			self.slickSliderGoTo( e );
		} );

		$( document ).on( 'previous next', s.featherlightInstanceSelector, function( e ) {
			self.slickSliderGoTo( e );
		} );

	},

	/**
	 * Moves the slider to previous/next slide.
	 * 
	 * @since  0.4
	 * 
	 * @return void 
	 */
	slickSliderGoTo: function( e ) {

		if ( ! s.targetSlider ) {
			return;
		}

		var slickSliderCurrentSlide = s.targetSlider.slick( 'slickCurrentSlide' ),
			slickSliderNextSlide = 'next' === e.type ? slickSliderCurrentSlide + 1 : slickSliderCurrentSlide - 1;
		s.targetSlider.slick( 'slickGoTo', slickSliderNextSlide );

	},

	/**
	 * Call all functions.
	 * 	 
	 * @since  0.4
	 * 
	 * @return void
	 */
	init: function() {
		$ = jQuery;
		s = this.settings;
		this.findSliders();
		this.featherlightNavigationCallback();
		
	}

}

jQuery( document ).ready( function() {

	'use strict';

	slickSliderFeatherlightHelper.init();

} )
