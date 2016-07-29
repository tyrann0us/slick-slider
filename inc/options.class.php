<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Handles getting and setting of all Slick Slider options.
 */
class slickOptions {

	/**
	 * Gets Slick Slider options from database.
	 * The function uses the default options array whose values are replaced with user options.
	 * @param  string $field field to get
	 * @return string|array        array of all options if $field not set, single field otherwise or empty string
	 */
	public static function get( $field = '' ) {

		$options = slickCache::get( 'options' );
		if ( empty( $options ) ) {
			$options = self::defaults();
			$options_user = get_option( 'slick' );
			if ( empty( $options_user ) ) {
				self::init();
				$options_user = get_option( 'slick' );
			}
			foreach ( $options as $option => $array_values ) {
				$options[$option]['value'] = $options_user[$option];
			}
			slickCache::set( 'options', $options );
		}
		if ( empty( $field ) ) {
			return $options;
		}
		return ( empty( $options[$field] ) ? '' : $options[$field] );

	}

	/**
	 * Updates Slick Slider settings to database.
	 * @param  array  $fields  fields to update
	 * @param  boolean $prepare wether to prepare options before saving (preparing means putting all options into multi-dimensional array)
	 * @return boolean           false if $fields not set
	 */
	public static function update( $fields, $prepare = false ) {

		if ( empty( $fields ) ) {
			return false;
		}
		if ( $prepare ) {
			$options_default = self::defaults();
			$options = array();
			foreach ( $options_default as $option => $array_values ) {
				if ( isset( $fields[$option] ) ) {
					switch ( $array_values['type'] ) {
						case 'boolean' :
							$options[$option] = true;
							break;
						case 'integer' :
							$options[$option] = floatval( $fields[$option] );
							break;
						case 'string' :
						case 'select' :
						case 'function' :
							$options[$option] = $fields[$option];
							break;
					}
				} else {
					switch ( $array_values['type'] ) {
						case 'boolean' :
							$options[$option] = false;
							break;
					}
				}
			}	
		} else {
			$options = array_merge( (array) get_option( 'slick' ), $fields );
		}
		update_option( 'slick', $options );
		slickCache::set( 'options', $options );

	}

	/**
	 * Adds Slick Slider options to databse (when plugin gets installed).
	 */
	public static function init() {

		add_option( 'slick', self::defaultOptions() );

	}

	/**
	 * Resets Slick Slider options using self::defaultOptions().
	 */
	public static function reset() {

		self::update( self::defaultOptions() );

	}

	/**
	 * Deletes Slick Slider options from databse (when plugin gets deleted).
	 * @return [type] [description]
	 */
	public static function destroy() {

		delete_option( 'slick' );

	}

	/**
	 * This method merges default options, user options and current gallery parameters into correct formatted array.
	 * It only outputs options who differ from default Slick Slider options.
	 * @param  array $atts shortcode parameters
	 * @return array       Slick Slider options
	 */
	public static function prepareOptionsForOutput( $atts ) {

		$options_user = self::get();
		$options_default = self::defaults();
		
		$options_merged = Slick::arrayDiffAssocRecursive( $options_user, $options_default );

		if ( is_array( $options_merged ) ) {
			foreach ( $options_merged as $option => $value ) {
				$options_merged[$option] = $value['value'];
			}
		}
		
		$options_slider_raw = array_filter( $atts, function( $value, $key ) {
		    return strpos( $key, 'sl_') === 0;
		}, true );

		$options_slider = [];
		$keys = array_keys( $options_user );
		foreach ( $options_slider_raw as $option => $value ) {
			if ( 'true' == $value || 'false' == $value ) {
				$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} else if ( is_numeric( $value ) ) {
				$value = floatval( $value );
			}
			$key = array_search( $option, array_column( $options_user, 'setting' ) );
			$options_slider[$keys[$key]] = $value;
		}

		return is_array( $options_merged ) ? array_merge( $options_merged, $options_slider ) : $options_slider;

	}

	/**
	 * Renders Sick Slider options HTML markup for different locations.
	 * @param  string $location where the markup should be inserted (markup differs based on location)
	 * @return if $location is empty
	 */
	public static function renderSettingsMarkup( $location = '' ) {

		if ( empty( $location ) ) {
			return;
		}
		$options = self::get();
		switch ( $location ) {
			case 'options-media' :
				foreach ( $options as $option => $array_values ) {
					add_settings_field(
						$option,
						sprintf( '<label for="%s">%s</label><small>%s</small>', $option, $array_values['name'], $array_values['desc'] ),
						function() use ( $option, $array_values ) {
							switch ( $array_values['type'] ) {
								case 'boolean' :
									printf(
										'<input type="checkbox" id="%s" name="%s" %s />',
										$option,
										$option,
										$array_values['value'] ? 'checked' : ''
									);
									break;
								case 'integer' :
									printf(
										'<input type="number" id="%s" name="%s" value="%s" %s />',
										$option,
										$option,
										esc_attr( $array_values['value'] ),
										'edgeFriction' == $option ? 'step="0.01"' : ''
									);
									break;
								case 'string' :
									printf(
										'<input type="text" id="%s" name="%s" value="%s" />',
										$option,
										$option,
										esc_attr( $array_values['value'] )
									);
									break;
								case 'select' :
									$select_options = [];
									foreach ( $array_values['values'] as $value ) {
										$select_options[] = sprintf( '<option %s>%s</option>', $value == $array_values['value'] ? 'selected' : '', $value );
									}
									printf(
										'<select id="%s" name="%s">%s</select>',
										$option,
										$option,
										implode( "\n", $select_options )
									);
									break;
								case 'function' :
									printf(
										'<textarea id="%s" name="%s">%s</textarea>',
										$option,
										$option,
										$array_values['value']
									);
									break;
								case 'object' :
									printf(
										'<button class="slick-slider-add-breakpoint">%s</button>',
										__( 'Add breakpoint', 'slick-slider' ) );
									break;
								default:
									break;
							}
						},
						'media',
						'slick'
					);
				};
				break;
			
			case 'post' :
			case 'post-new' :
				foreach ( $options as $option => $array_values ) {
					if ( false === $array_values['showOnSingleGallery'] ) continue; ?>
					<label class="setting">
						<span><?php echo $option; ?></span><small data-hint="<?php echo $array_values['desc']; ?>">[?]</small>
						<?php switch ( $array_values['type'] ) {
							case 'boolean' :
								printf(
									'<input type="checkbox" data-setting="%s" />',
									$array_values['setting']
								);
								break;
							case 'integer' :
								printf(
									'<input type="text" data-setting="%s" />',
									$array_values['setting']
								);
								break;
							case 'string' :
								printf(
									'<input type="text" data-setting="%s" />',
									$array_values['setting']
								);
								break;
							case 'select' :
								printf(
									'<select data-setting="%s">',
									$array_values['setting']
								);
								foreach ( $array_values['values'] as $value ) :
									printf(
										'<option value="%s">%s</option>',
										$value,
										$value
									);
								endforeach;
								echo '</select>';
								break;
							case 'function' :
								printf(
									'<textarea data-setting="%s"></textarea>',
									$array_values['setting']
								);
								break;
							case 'object' :
								printf(
									'<button class="slick-slider-add-breakpoint">%s</button>',
									__( 'Add breakpoint', 'slick-slider' )
								);
								break;
							default :
								break;
						} ?>
					</label>
				<?php };
				break;
		}

	}  

	/**
	 * Gets Slick Slider's default options and returns them.
	 * @return array       Slick Slider options encoded using esc_attr()
	 */
	public static function defaultOptions() {

		return array_map( function( $option ) { return esc_attr( $option['value'] ); }, self::defaults() );

	}

	/**
	 * Array of Slick Slider options.
	 * @return array       Slick Slider options
	 */
	public static function defaults() {

		return array(
			'accessibility' => array(
				'name' => __( 'accessibility', 'slick-slider' ),
				'desc' => 'Enables tabbing and arrow key navigation.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_accessibility',
				'type' => 'boolean',
				'value' => true,
			),
			'adaptiveHeight' => array(
				'name' => __( 'adaptiveHeight', 'slick-slider' ),
				'desc' => 'Enables adaptive height for single slide horizontal carousels.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_adaptiveheight',
				'type' => 'boolean',
				'value' => false,
			),
			'autoplay' => array(
				'name' => __( 'autoplay', 'slick-slider' ),
				'desc' => 'Enables Autoplay.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_autoplay',
				'type' => 'boolean',
				'value' => false,
			),
			'autoplaySpeed' => array(
				'name' => __( 'autoplaySpeed', 'slick-slider' ),
				'desc' => 'Autoplay Speed in milliseconds.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_autoplayspeed',
				'type' => 'integer',
				'value' => 3000,
			),
			'arrows' => array(
				'name' => __( 'arrows', 'slick-slider' ),
				'desc' => 'Prev/Next Arrows.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_arrows',
				'type' => 'boolean',
				'value' => true,
			),
			'asNavFor' => array(
				'name' => __( 'asNavFor', 'slick-slider' ),
				'desc' => 'Set the slider to be the navigation of other slider (Class or ID Name).',
				'showOnSingleGallery' => true,
				'setting' => 'sl_asnavfor',
				'type' => 'string',
				'value' => '',
			),
			'appendArrows' => array(
				'name' => __( 'appendArrows', 'slick-slider' ),
				'desc' => 'Change where the navigation arrows are attached (Selector, htmlString, Array, Element, jQuery object).',
				'showOnSingleGallery' => false,
				'setting' => 'sl_appendarrows',
				'type' => 'string',
				'value' => '',
			),
			'prevArrow' => array(
				'name' => __( 'prevArrow', 'slick-slider' ),
				'desc' => 'Allows you to select a node or customize the HTML for the "Previous" arrow.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_prevarrow',
				'type' => 'string',
				'value' => '<button type=\'button\' class=\'slick-prev\'>Previous</button>',
			),
			'nextArrow' => array(
				'name' => __( 'nextArrow', 'slick-slider' ),
				'desc' => 'Allows you to select a node or customize the HTML for the "Next" arrow.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_nextarrow',
				'type' => 'string',
				'value' => '<button type=\'button\' class=\'slick-next\'>Next</button>',
			),
			'centerMode' => array(
				'name' => __( 'centerMode', 'slick-slider' ),
				'desc' => 'Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_centermode',
				'type' => 'boolean',
				'value' => false,
			),
			'centerPadding' => array(
				'name' => __( 'centerPadding', 'slick-slider' ),
				'desc' => 'Side padding when in center mode (px or %).',
				'showOnSingleGallery' => true,
				'setting' => 'sl_centerpadding',
				'type' => 'string',
				'value' => '50px',
			),
			'cssEase' => array(
				'name' => __( 'cssEase', 'slick-slider' ),
				'desc' => 'CSS3 Animation Easing.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_cssease',
				'type' => 'select',
				'value' => 'ease',
				'values' => array(
					'ease',
				),
			),
			'customPaging' => array(
				'name' => __( 'customPaging', 'slick-slider' ),
				'desc' => 'Custom paging templates. See source for use example.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_custompaging',
				'type' => 'function',
				'value' => '',
			),
			'dots' => array(
				'name' => __( 'dots', 'slick-slider' ),
				'desc' => 'Show dot indicators.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_dots',
				'type' => 'boolean',
				'value' => false,
			),
			'draggable' => array(
				'name' => __( 'draggable', 'slick-slider' ),
				'desc' => 'Enable mouse dragging.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_draggable',
				'type' => 'boolean',
				'value' => true,
			),
			'fade' => array(
				'name' => __( 'fade', 'slick-slider' ),
				'desc' => 'Enable fade.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_fade',
				'type' => 'boolean',
				'value' => false,
			),
			'focusOnSelect' => array(
				'name' => __( 'focusOnSelect', 'slick-slider' ),
				'desc' => 'Enable focus on selected element (click).',
				'showOnSingleGallery' => true,
				'setting' => 'sl_focusonselect',
				'type' => 'boolean',
				'value' => false,
			),
			'easing' => array(
				'name' => __( 'easing', 'slick-slider' ),
				'desc' => 'Add easing for jQuery animate. Use with easing libraries or default easing methods.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_easing',
				'type' => 'select',
				'value' => 'linear',
				'values' => array(
					'linear',
				),
			),
			'edgeFriction' => array(
				'name' => __( 'edgeFriction', 'slick-slider' ),
				'desc' => 'Resistance when swiping edges of non-infinite carousels.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_edgefriction',
				'type' => 'integer',
				'value' => 0.15,
			),
			'infinite' => array(
				'name' => __( 'infinite', 'slick-slider' ),
				'desc' => 'Infinite loop sliding.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_infinite',
				'type' => 'boolean',
				'value' => true,
			),
			'initialSlide' => array(
				'name' => __( 'initialSlide', 'slick-slider' ),
				'desc' => 'Slide to start on.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_initialslide',
				'type' => 'integer',
				'value' => 0,
			),
			'lazyLoad' => array(
				'name' => __( 'lazyLoad', 'slick-slider' ),
				'desc' => 'Set lazy loading technique. Accepts \'ondemand\' or \'progressive\'.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_lazyload',
				'type' => 'select',
				'value' => 'ondemand',
				'values' => array(
					'ondemand',
					'progressive',
				),
			),
			//'mobileFirst' => array(
			//	'name' => __( 'mobileFirst', 'slick-slider' ),
			//	'desc' => 'Responsive settings use mobile first calculation.',
			//	'showOnSingleGallery' => false,
			//	'setting' => 'sl_mobilefirst',
			//	'type' => 'boolean',
			//	'value' => false,
			//),
			'pauseOnHover' => array(
				'name' => __( 'pauseOnHover', 'slick-slider' ),
				'desc' => 'Pause Autoplay On Hover.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_pauseonhover',
				'type' => 'boolean',
				'value' => true,
			),
			'pauseOnDotsHover' => array(
				'name' => __( 'pauseOnDotsHover', 'slick-slider' ),
				'desc' => 'Pause Autoplay when a dot is hovered.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_pauseondotshover',
				'type' => 'boolean',
				'value' => false,
			),
			'respondTo' => array(
				'name' => __( 'respondTo', 'slick-slider' ),
				'desc' => 'Width that responsive object responds to. Can be \'window\', \'slider\' or \'min\' (the smaller of the two).',
				'showOnSingleGallery' => false,
				'setting' => 'sl_respondto',
				'type' => 'select',
				'value' => 'window',
				'values' => array(
					'window',
					'slider',
					'min',
				),
			),
			//'responsive' => array(
			//	'name' => __( 'responsive', 'slick-slider' ),
			//	'showOnSingleGallery' => false,
			//	'desc' => 'Object containing breakpoints and settings objects (see demo). Enables settings sets at given screen width. Set settings to "unslick" instead of an object to disable slick at a given breakpoint.',
			//	'setting' => 'sl_responsive',
			//	'type' => 'object',
			//	'value' => '',
			//),
			'rows' => array(
				'name' => __( 'rows', 'slick-slider' ),
				'desc' => 'Setting this to more than 1 initializes grid mode. Use slidesPerRow to set how many slides should be in each row.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_rows',
				'type' => 'integer',
				'value' => 1,
			),
			'slide' => array(
				'name' => __( 'slide', 'slick-slider' ),
				'desc' => 'Element query to use as slide.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_slide',
				'type' => 'string',
				'value' => '',
			),
			'slidesPerRow' => array(
				'name' => __( 'slidesPerRow', 'slick-slider' ),
				'desc' => 'With grid mode intialized via the rows option, this sets how many slides are in each grid row.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_slidesperrow',
				'type' => 'integer',
				'value' => 1,
			),
			'slidesToShow' => array(
				'name' => __( 'slidesToShow', 'slick-slider' ),
				'desc' => '# of slides to show.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_slidestoshow',
				'type' => 'integer',
				'value' => 1,
			),
			'slidesToScroll' => array(
				'name' => __( 'slidesToScroll', 'slick-slider' ),
				'desc' => '# of slides to scroll.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_slidestoscroll',
				'type' => 'integer',
				'value' => 1,
			),
			'speed' => array(
				'name' => __( 'speed', 'slick-slider' ),
				'desc' => 'Slide/Fade animation speed.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_speed',
				'type' => 'integer',
				'value' => 300,
			),
			'swipe' => array(
				'name' => __( 'swipe', 'slick-slider' ),
				'desc' => 'Enable swiping.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_swipe',
				'type' => 'boolean',
				'value' => true,
			),
			'swipeToSlide' => array(
				'name' => __( 'swipeToSlide', 'slick-slider' ),
				'desc' => 'Allow users to drag or swipe directly to a slide irrespective of slidesToScroll.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_swipetoslide',
				'type' => 'boolean',
				'value' => false,
			),
			'touchMove' => array(
				'name' => __( 'touchMove', 'slick-slider' ),
				'desc' => 'Enable slide motion with touch.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_touchmove',
				'type' => 'boolean',
				'value' => true,
			),
			'touchThreshold' => array(
				'name' => __( 'touchThreshold', 'slick-slider' ),
				'desc' => 'To advance slides, the user must swipe a length of (1/touchThreshold) * the width of the slider.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_touchthreshold',
				'type' => 'integer',
				'value' => 5,
			),
			'useCSS' => array(
				'name' => __( 'useCSS', 'slick-slider' ),
				'desc' => 'Enable/Disable CSS Transitions.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_usecss',
				'type' => 'boolean',
				'value' => true,
			),
			'useTransform' => array(
				'name' => __( 'useTransform', 'slick-slider' ),
				'desc' => 'Enable/Disable CSS Transforms.',
				'showOnSingleGallery' => false,
				'setting' => 'sl_usetransform',
				'type' => 'boolean',
				'value' => false,
			),
			'variableWidth' => array(
				'name' => __( 'variableWidth', 'slick-slider' ),
				'desc' => 'Variable width slides.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_variablewidth',
				'type' => 'boolean',
				'value' => false,
			),
			'vertical' => array(
				'name' => __( 'vertical', 'slick-slider' ),
				'desc' => 'Vertical slide mode.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_vertical',
				'type' => 'boolean',
				'value' => false,
			),
			'verticalSwiping' => array(
				'name' => __( 'verticalSwiping', 'slick-slider' ),
				'desc' => 'Vertical swipe mode.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_verticalswiping',
				'type' => 'boolean',
				'value' => false,
			),
			'rtl' => array(
				'name' => __( 'rtl', 'slick-slider' ),
				'desc' => 'Change the slider\'s direction to become right-to-left.',
				'showOnSingleGallery' => true,
				'setting' => 'sl_rtl',
				'type' => 'boolean',
				'value' => false,
			),
		);

	}
}