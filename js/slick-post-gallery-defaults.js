( function( $ ) {

	var media = wp.media;
	$.extend( wp.media.gallery.defaults, {
		slick_active: false,
	} );
	$.each( slider_defaults, function( key, value ) {
		switch ( value.type ) {
			case 'string' :
			case 'function' :

				//var mapObj = {
				//	'&lt;': '<',
				//	'&gt;': '>',
				//	'&#039;': '\'',
				//};
				//var re = new RegExp( Object.keys( mapObj ).join( '|' ), 'gi' );
				//value.value = value.value.replace( re, function( matched ) {
				//  return mapObj[matched];
				//});

				$.extend( wp.media.gallery.defaults, {
					[value['setting']]: value.value,
				} );
				break;
			case 'boolean' :
				$.extend( wp.media.gallery.defaults, {
					[value['setting']]: '1' == value.value ? true : false,
				} );
				break;
			case 'integer' :
				$.extend( wp.media.gallery.defaults, {
					[value['setting']]: value.value,
				} );
				break;
			case 'object' :
				$.extend( wp.media.gallery.defaults, {
					[value['setting']]: value.value[0],
				} );
				break;
			default :
				break;
		}		
	} );
	media.view.Settings.Gallery = media.view.Settings.Gallery.extend( {
		template: function(view) {
		  return media.template( 'gallery-settings' )( view )
			   + media.template( 'slick-slider-gallery-setting' )( view );
		}
	} );
	
} )( jQuery );