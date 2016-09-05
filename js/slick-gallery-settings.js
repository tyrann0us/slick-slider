( function( $ ) {

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

	
} )( jQuery );