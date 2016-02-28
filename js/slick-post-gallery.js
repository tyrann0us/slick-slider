( function( $ ) {
	var $slickSliderSettings;
	var $invalidGallerySettings;
	var slickActive = false;

	$( document ).on( 'click', '.dashicons-edit, .insert-media', function() {
		initSliderSettings();
	} );
	$( document ).on( 'click', '.media-modal .slick-slider-toggle-settings input', function() {
		toggleSliderSettings();
	} );
	$( document ).on( 'click', '.media-modal .slick-slider-add-breakpoint', manage_breakpoints );


	function initSliderSettings() {
		slickActive = $( '[data-setting="slick_active"]' ).is( ':checked' );
		$invalidGallerySettings = $( '[name="columns"]' ).parent();
		$invalidGallerySettings.css( 'display', ! slickActive ? 'block' : 'none' );

		$slickSliderSettings = $( '.media-modal .slick-slider-settings-inner' );
		$slickSliderSettings.css( 'display', slickActive ? 'block' : 'none' );
	}
	function toggleSliderSettings() {
		$slickSliderSettings.add( $invalidGallerySettings ).toggle();
	};
	function manage_breakpoints() {
		// prepend wrapper for better event handling
		if ( ! $( this ).parents( '.slick-slider-breakpoint-wrapper' ).length ) {
			$( this ).parents( '.setting' ).wrap( '<div class="slick-slider-breakpoint-wrapper"></div>' );
		};
		var breakpoint_template = wp.template( 'slick-slider-breakpoint-settings' );
		$( '.slick-slider-breakpoint-wrapper' ).prepend( breakpoint_template );

		$( document ).on( 'click', '.breakpoint-accordion-toggle', function() {
			$( '.breakpoint-accordion-content' ).toggleClass( 'active' );
		} );
	};
} )( jQuery );