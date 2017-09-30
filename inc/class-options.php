<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to handle getting and setting of all Slick Slider options.
 *
 * @since 0.1
 */
class Slick_Slider_Options {

	/**
	 * Get Slick Slider options from database.
	 * The function uses the default options array whose values are replaced with user options.
	 *
	 * @since 0.1
	 * 
	 * @param string $field Field to get.
	 * @return string|array Array of all options if $field not set, single field otherwise or empty string.
	 */
	public static function get( $field = '' ) {

		$options = Slick_Slider_Cache::get( 'options' );
		if ( empty( $options ) ) {
			$options = self::defaults();
			$options_db = get_option( 'slick-slider' );
			if ( empty( $options_db ) ) {
				self::init();
				$options_db = get_option( 'slick-slider' );
			}
			foreach ( $options as $option => $array_values ) {
				if ( isset( $options_db[ $option ] ) ) {
					$options[ $option ]['value'] = $options_db[ $option ];
				}
			}
			Slick_Slider_Cache::set( 'options', $options );
		}
		array_walk_recursive( $options, function( &$value, $key ) {
			'value' === $key && is_string( $value ) && $value = htmlspecialchars_decode( $value );
		} );

		if ( empty( $field ) ) {
			return $options;
		}
		return ( empty( $options[ $field ] ) ? '' : $options[ $field ] );

	}

	/**
	 * Update Slick Slider settings to database.
	 *
	 * @since 0.1
	 * 
	 * @param array $fields    Fields to update.
	 * @param boolean $prepare Whether to prepare options before saving.
	 *                         Preparing means putting all options into multi-dimensional array.
	 * @return boolean         False if $fields not set.
	 */
	public static function update( $fields, $prepare = false ) {

		if ( empty( $fields ) ) {
			return false;
		}
		if ( $prepare ) {
			$options_default = self::defaults();
			$options = [];
			foreach ( $options_default as $option => $array_values ) {
				if ( isset( $fields[ $option ] ) ) {
					switch ( $array_values['type'] ) {
						case 'boolean' :
							$options[ $option ] = true;
							break;
						case 'integer' :
							$options[ $option ] = floatval( $fields[ $option ] );
							break;
						case 'string' :
						case 'select' :
						case 'function' :
							$options[ $option ] = $fields[ $option ];
							break;
					}
				} else {
					switch ( $array_values['type'] ) {
						case 'boolean' :
							$options[ $option ] = false;
							break;
					}
				}
			}
		} else {
			$options = array_merge( (array) get_option( 'slick-slider' ), $fields );
		}
		update_option( 'slick-slider', $options );
		Slick_Slider_Cache::set( 'options', $options );

	}

	/**
	 * Add Slick Slider options to databse (when plugin gets activated).
	 *
	 * @since 0.1
	 */
	public static function init() {

		add_option( 'slick-slider', self::default_options() );

	}

	/**
	 * Reset Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function reset() {

		self::update( self::default_options() );

	}

	/**
	 * Delete Slick Slider options from databse (when plugin gets deleted).
	 *
	 * @since 0.1
	 */
	public static function destroy() {

		delete_option( 'slick-slider' );

	}

	/**
	 * Merge default options, user options and current gallery parameters into correctly formatted array.
	 * It only outputs options which differ from default Slick Slider options.
	 *
	 * @since 0.1
	 * 
	 * @param array $atts Shortcode parameters.
	 * @return array      Slick Slider options.
	 */
	public static function prepare_options_for_output( $atts ) {

		$options_db = self::get();
		$options_default = self::defaults();

		$options_merged = Slick_Slider_Main::array_diff_assoc_recursive( $options_db, $options_default );

		if ( is_array( $options_merged ) ) {
			foreach ( $options_merged as $option => $value ) {
				$options_merged[ $option ] = $value['value'];
			}
		}
		
		$options_slider_raw = array_filter( $atts, function( $value, $key ) {
			return 0 === strpos( $key, 'sl_');
		}, true );

		$options_slider = [];
		$keys = array_keys( $options_db );
		foreach ( $options_slider_raw as $option => $value ) {
			if ( 'true' === $value || 'false' === $value ) {
				$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( is_numeric( $value ) ) {
				$value = floatval( $value );
			}
			$key = array_search( $option, array_column( $options_db, 'setting' ) );
			$options_slider[ $keys[ $key ] ] = $value;
		}

		return is_array( $options_merged ) ? array_merge( $options_merged, $options_slider ) : $options_slider;

	}

	/**
	 * Render Sick Slider option’s HTML markup for different locations.
	 *
	 * @since 0.1
	 * 
	 * @param string $location Where the markup should be inserted (markup differs based on location).
	 * @return                 If $location is empty.
	 */
	public static function render_settings_markup( $location = '' ) {

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
										'edgeFriction' === $option ? 'step="0.01"' : ''
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
										$select_options[] = sprintf(
											'<option %s>%s</option>',
											$value === $array_values['value'] ? 'selected' : '', $value
										);
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
						'slick-slider',
						array(
							'class' => 'slick-slider-option',
						)
					);
				};
				break;
			
			case 'post' :
			case 'post-new' :
				foreach ( $options as $option => $array_values ) {
					if ( false === $array_values['showOnSingleGallery'] ) continue; ?>
					<label class="setting">
						<span><?php echo $array_values['name']; ?></span><small data-hint="<?php echo $array_values['desc']; ?>">[?]</small>
						<?php switch ( $array_values['type'] ) {
							case 'boolean' :
								printf(
									'<input type="checkbox" data-setting="%s" %s />',
									$array_values['setting'],
									sprintf(
										'<# if ( slider_defaults.%s.value ) { #> checked="checked" <# } #>',
										$option
									)
								);
								break;
							case 'integer' :
							case 'string' :
								printf(
									'<input type="%s" data-setting="%s" value="%s" />',
									'text',
									$array_values['setting'],
									sprintf(
										'{{ slider_defaults.%s.value }}',
										$option
									)
								);
								break;
							case 'select' :
								printf(
									'<select data-setting="%s">',
									$array_values['setting']
								);
								foreach ( $array_values['values'] as $value ) :
									printf(
										'<option value="%s" %s>%s</option>',
										$value,
										sprintf(
											'<# if ( "%s" === slider_defaults.%s.value ) { #> selected="selected" <# } #>',
											$value,
											$option
										),
										$value
									);
								endforeach;
								echo '</select>';
								break;
							case 'function' :
								printf(
									'<textarea data-setting="%s">%s</textarea>',
									$array_values['setting'],
									sprintf(
										'{{ slider_defaults.%s.value }}',
										$option
									)
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
	 * Get Slick Slider's default options and return them.
	 *
	 * @since 0.1
	 * 
	 * @return array Slick Slider options encoded.
	 */
	public static function default_options() {

		return array_map( function( $option ) { return esc_attr( $option['value'] ); }, self::defaults() );

	}

	/**
	 * Array of Slick Slider options.
	 *
	 * @since 0.1
	 * 
	 * @return array Slick Slider options.
	 */
	public static function defaults() {

		return array(
			'showOnGalleryModal' => array(
				'name' => __( 'Show options on gallery modal', 'slick-slider' ),
				'desc' => __( 'Show Slick Slider options on single gallery modal.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'showOnGalleryModal',
				'type' => 'boolean',
				'value' => true,
			),
			'showCaption' => array(
				'name' => __( 'Show caption', 'slick-slider' ),
				'desc' => __( 'Show caption below slide image.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_show_caption',
				'type' => 'boolean',
				'value' => false,
			),
			'accessibility' => array(
				'name' => __( 'accessibility', 'slick-slider' ),
				'desc' => __( 'Enables tabbing and arrow key navigation.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_accessibility',
				'type' => 'boolean',
				'value' => true,
			),
			'adaptiveHeight' => array(
				'name' => __( 'adaptiveHeight', 'slick-slider' ),
				'desc' => __( 'Enables adaptive height for single slide horizontal carousels.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_adaptiveheight',
				'type' => 'boolean',
				'value' => false,
			),
			'autoplay' => array(
				'name' => __( 'autoplay', 'slick-slider' ),
				'desc' => __( 'Enables Autoplay.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_autoplay',
				'type' => 'boolean',
				'value' => false,
			),
			'autoplaySpeed' => array(
				'name' => __( 'autoplaySpeed', 'slick-slider' ),
				'desc' => __( 'Autoplay Speed in milliseconds.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_autoplayspeed',
				'type' => 'integer',
				'value' => 3000,
			),
			'arrows' => array(
				'name' => __( 'arrows', 'slick-slider' ),
				'desc' => __( 'Prev/Next Arrows.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_arrows',
				'type' => 'boolean',
				'value' => true,
			),
			'asNavFor' => array(
				'name' => __( 'asNavFor', 'slick-slider' ),
				'desc' => __( 'Set the slider to be the navigation of other slider (Class or ID Name).', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_asnavfor',
				'type' => 'string',
				'value' => '',
			),
			'appendArrows' => array(
				'name' => __( 'appendArrows', 'slick-slider' ),
				'desc' => __( 'Change where the navigation arrows are attached (Selector, htmlString, Array, Element, jQuery object).', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_appendarrows',
				'type' => 'string',
				'value' => '',
			),
			'appendDots' => array(
				'name' => __( 'appendDots', 'slick-slider' ),
				'desc' => __( 'Change where the navigation dots are attached (Selector, htmlString, Array, Element, jQuery object)', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_appenddots',
				'type' => 'string',
				'value' => '',
			),
			'prevArrow' => array(
				'name' => __( 'prevArrow', 'slick-slider' ),
				'desc' => __( 'Allows you to select a node or customize the HTML for the "Previous" arrow.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_prevarrow',
				'type' => 'string',
				'value' => '<button type="button" class="slick-prev">Previous</button>',
			),
			'nextArrow' => array(
				'name' => __( 'nextArrow', 'slick-slider' ),
				'desc' => __( 'Allows you to select a node or customize the HTML for the "Next" arrow.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_nextarrow',
				'type' => 'string',
				'value' => '<button type="button" class="slick-next">Next</button>',
			),
			'centerMode' => array(
				'name' => __( 'centerMode', 'slick-slider' ),
				'desc' => __( 'Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_centermode',
				'type' => 'boolean',
				'value' => false,
			),
			'centerPadding' => array(
				'name' => __( 'centerPadding', 'slick-slider' ),
				'desc' => __( 'Side padding when in center mode (px or %).', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_centerpadding',
				'type' => 'string',
				'value' => '50px',
			),
			'cssEase' => array(
				'name' => __( 'cssEase', 'slick-slider' ),
				'desc' => __( 'CSS3 Animation Easing.', 'slick-slider' ),
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
				'desc' => __( 'Custom paging templates. See source for use example.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_custompaging',
				'type' => 'function',
				'value' => '',
			),
			'dots' => array(
				'name' => __( 'dots', 'slick-slider' ),
				'desc' => __( 'Show dot indicators.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_dots',
				'type' => 'boolean',
				'value' => false,
			),
			'dotsClass' => array(
				'name' => __( 'dotsClass', 'slick-slider' ),
				'desc' => __( 'Class for slide indicator dots container.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_dotsclass',
				'type' => 'string',
				'value' => 'slick-dots',
			),
			'draggable' => array(
				'name' => __( 'draggable', 'slick-slider' ),
				'desc' => __( 'Enable mouse dragging.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_draggable',
				'type' => 'boolean',
				'value' => true,
			),
			'fade' => array(
				'name' => __( 'fade', 'slick-slider' ),
				'desc' => __( 'Enable fade.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_fade',
				'type' => 'boolean',
				'value' => false,
			),
			'focusOnChange' => array(
				'name' => __( 'focusOnChange', 'slick-slider' ),
				'desc' => __( 'Puts focus on slide after change.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_focusonchange',
				'type' => 'boolean',
				'value' => false,
			),
			'focusOnSelect' => array(
				'name' => __( 'focusOnSelect', 'slick-slider' ),
				'desc' => __( 'Enable focus on selected element (click).', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_focusonselect',
				'type' => 'boolean',
				'value' => false,
			),
			'easing' => array(
				'name' => __( 'easing', 'slick-slider' ),
				'desc' => __( 'Add easing for jQuery animate. Use with easing libraries or default easing methods.', 'slick-slider' ),
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
				'desc' => __( 'Resistance when swiping edges of non-infinite carousels.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_edgefriction',
				'type' => 'integer',
				'value' => 0.15,
			),
			'infinite' => array(
				'name' => __( 'infinite', 'slick-slider' ),
				'desc' => __( 'Infinite loop sliding.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_infinite',
				'type' => 'boolean',
				'value' => true,
			),
			'initialSlide' => array(
				'name' => __( 'initialSlide', 'slick-slider' ),
				'desc' => __( 'Slide to start on.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_initialslide',
				'type' => 'integer',
				'value' => 0,
			),
			'lazyLoad' => array(
				'name' => __( 'lazyLoad', 'slick-slider' ),
				'desc' => __( 'Set lazy loading technique. Accepts \'ondemand\' or \'progressive\'.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_lazyload',
				'type' => 'select',
				'value' => 'ondemand',
				'values' => array(
					'anticipated',
					'ondemand',
					'progressive',
				),
			),
			//'mobileFirst' => array(
			//	'name' => __( 'mobileFirst', 'slick-slider' ),
			//	'desc' => __( 'Responsive settings use mobile first calculation.', 'slick-slider' ),
			//	'showOnSingleGallery' => false,
			//	'setting' => 'sl_mobilefirst',
			//	'type' => 'boolean',
			//	'value' => false,
			//),
			'pauseOnFocus' => array(
				'name' => __( 'pauseOnFocus', 'slick-slider' ),
				'desc' => __( 'Pause Autoplay On Focus.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_pauseonfocus',
				'type' => 'boolean',
				'value' => true,
			),
			'pauseOnHover' => array(
				'name' => __( 'pauseOnHover', 'slick-slider' ),
				'desc' => __( 'Pause Autoplay On Hover.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_pauseonhover',
				'type' => 'boolean',
				'value' => true,
			),
			'pauseOnDotsHover' => array(
				'name' => __( 'pauseOnDotsHover', 'slick-slider' ),
				'desc' => __( 'Pause Autoplay when a dot is hovered.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_pauseondotshover',
				'type' => 'boolean',
				'value' => false,
			),
			'respondTo' => array(
				'name' => __( 'respondTo', 'slick-slider' ),
				'desc' => __( 'Width that responsive object responds to. Can be \'window\', \'slider\' or \'min\' (the smaller of the two).', 'slick-slider' ),
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
			//	'desc' => __( 'Object containing breakpoints and settings objects (see demo). Enables settings sets at given screen width. Set settings to "unslick" instead of an object to disable slick at a given breakpoint.', 'slick-slider' ),
			//	'setting' => 'sl_responsive',
			//	'type' => 'object',
			//	'value' => array (
			//		(object) ( array(
			//			'breakpoint' => 600,
			//			'settings' => (object) ( array(
			//				'slidesToShow' => 4,
			//			) ),
			//		) ),
			//	),
			//),
			'rows' => array(
				'name' => __( 'rows', 'slick-slider' ),
				'desc' => __( 'Setting this to more than 1 initializes grid mode. Use slidesPerRow to set how many slides should be in each row.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_rows',
				'type' => 'integer',
				'value' => 1,
			),
			'slide' => array(
				'name' => __( 'slide', 'slick-slider' ),
				'desc' => __( 'Element query to use as slide.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_slide',
				'type' => 'string',
				'value' => '',
			),
			'slidesPerRow' => array(
				'name' => __( 'slidesPerRow', 'slick-slider' ),
				'desc' => __( 'With grid mode intialized via the rows option, this sets how many slides are in each grid row.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_slidesperrow',
				'type' => 'integer',
				'value' => 1,
			),
			'slidesToShow' => array(
				'name' => __( 'slidesToShow', 'slick-slider' ),
				'desc' => __( '# of slides to show.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_slidestoshow',
				'type' => 'integer',
				'value' => 1,
			),
			'slidesToScroll' => array(
				'name' => __( 'slidesToScroll', 'slick-slider' ),
				'desc' => __( '# of slides to scroll.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_slidestoscroll',
				'type' => 'integer',
				'value' => 1,
			),
			'speed' => array(
				'name' => __( 'speed', 'slick-slider' ),
				'desc' => __( 'Slide/Fade animation speed.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_speed',
				'type' => 'integer',
				'value' => 300,
			),
			'swipe' => array(
				'name' => __( 'swipe', 'slick-slider' ),
				'desc' => __( 'Enable swiping.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_swipe',
				'type' => 'boolean',
				'value' => true,
			),
			'swipeToSlide' => array(
				'name' => __( 'swipeToSlide', 'slick-slider' ),
				'desc' => __( 'Allow users to drag or swipe directly to a slide irrespective of slidesToScroll.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_swipetoslide',
				'type' => 'boolean',
				'value' => false,
			),
			'touchMove' => array(
				'name' => __( 'touchMove', 'slick-slider' ),
				'desc' => __( 'Enable slide motion with touch.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_touchmove',
				'type' => 'boolean',
				'value' => true,
			),
			'touchThreshold' => array(
				'name' => __( 'touchThreshold', 'slick-slider' ),
				'desc' => __( 'To advance slides, the user must swipe a length of (1/touchThreshold) * the width of the slider.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_touchthreshold',
				'type' => 'integer',
				'value' => 5,
			),
			'useCSS' => array(
				'name' => __( 'useCSS', 'slick-slider' ),
				'desc' => __( 'Enable/Disable CSS Transitions.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_usecss',
				'type' => 'boolean',
				'value' => true,
			),
			'useTransform' => array(
				'name' => __( 'useTransform', 'slick-slider' ),
				'desc' => __( 'Enable/Disable CSS Transforms.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_usetransform',
				'type' => 'boolean',
				'value' => false,
			),
			'variableWidth' => array(
				'name' => __( 'variableWidth', 'slick-slider' ),
				'desc' => __( 'Variable width slides.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_variablewidth',
				'type' => 'boolean',
				'value' => false,
			),
			'vertical' => array(
				'name' => __( 'vertical', 'slick-slider' ),
				'desc' => __( 'Vertical slide mode.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_vertical',
				'type' => 'boolean',
				'value' => false,
			),
			'verticalSwiping' => array(
				'name' => __( 'verticalSwiping', 'slick-slider' ),
				'desc' => __( 'Vertical swipe mode.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_verticalswiping',
				'type' => 'boolean',
				'value' => false,
			),
			'rtl' => array(
				'name' => __( 'rtl', 'slick-slider' ),
				'desc' => __( 'Change the slider\'s direction to become right-to-left.', 'slick-slider' ),
				'showOnSingleGallery' => true,
				'setting' => 'sl_rtl',
				'type' => 'boolean',
				'value' => false,
			),
			'waitForAnimate' => array(
				'name' => __( 'waitForAnimate', 'slick-slider' ),
				'desc' => __( 'Ignores requests to advance the slide while animating.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_waitforanimate',
				'type' => 'boolean',
				'value' => true,
			),
			'zIndex' => array(
				'name' => __( 'zIndex', 'slick-slider' ),
				'desc' => __( 'Set the zIndex values for slides, useful for IE9 and lower.', 'slick-slider' ),
				'showOnSingleGallery' => false,
				'setting' => 'sl_zindex',
				'type' => 'integer',
				'value' => 1000,
			),
		);

	}
}
