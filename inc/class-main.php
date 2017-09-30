<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Main Slick Slider class.
 *
 * @since 0.1
 */
class Slick_Slider_Main {

	/**
	 * Hook all actions and filters for backend and frontend.
	 *
	 * @since 0.1
	 */
	public static function init() {

		if ( is_admin() ) {

			add_action( 'admin_notices', array(
				 'Slick_Slider_Feedback',
				'rules' 
			) );
			add_action( 'network_admin_notices', array(
				 'Slick_Slider_Feedback',
				'network' 
			) );
			add_action( 'admin_notices', array(
				 'Slick_Slider_Feedback',
				'admin' 
			) );

			switch ( self::current_page() ) {
				case 'post' :
				case 'post-new' :
					add_action( 'admin_init', array(
						'Slick_Slider_Template',
						'init_template'
					) );
					break;
				case 'options-media' :
					add_action( 'admin_init', array(
						'Slick_Slider_Gui',
						'init_settings' 
					) );
					break;
				case 'options' :
					add_action( 'update_option',  array(
						 'Slick_Slider_Gui',
						'save_changes' 
					) );
					break;
				case 'plugins' :
					add_filter( 'plugin_action_links_' . SLICK_SLIDER_BASE, array(
						 __CLASS__,
						'add_settings_links' 
					) );
					add_filter( 'plugin_row_meta', array(
						 __CLASS__,
						'add_thanks_links' 
						),
						10,
						2
					);
					break;
				default:
					break;
			}

		} else {

			add_action( 'init', array(
				'Slick_Slider_Output',
				'init_slider'
			) );

		}

	}

	/**
	 * Initiate setting of Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function install() {

		Slick_Slider_Options::init();

	}

	/**
	 * Initiate removing of Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function uninstall() {

		Slick_Slider_Options::destroy();

	}

	/**
	 * Get URL relative to given path.
	 *
	 * @since 0.1
	 * 
	 * @param string $path URI to asset.
	 * @return string      Full URL to asset.
	 */
	public static function plugin_url( $path ) {

		return plugins_url( $path, SLICK_SLIDER_FILE );

	}

	/**
	 * Get current backend page.
	 *
	 * @since 0.1
	 * 
	 * @return string Slug of curerent page (e. g. 'options-media').
	 */
	public static function current_page() {

		return ( empty( $GLOBALS['pagenow'] ) ? 'index' : basename( $GLOBALS['pagenow'], '.php' ) );

	}

	/**
	 * Add link to Slick Slider settings on plugin page.
	 * 
	 * @since 0.1
	 * 
	 * @param array $data Current plugin action link.
	 * @return array      Merged array without link to plugin-editor and with link to Slick Slider settings.
	 */
	public static function add_settings_links( $data ) {

		$output = array_filter( $data, function( $value ) { return ! strpos( $value, 'plugin-editor.php' ); } );
		if ( current_user_can( 'manage_options' ) ) {
			$output = array_merge( $output, array(
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'options-media.php#slick-slider-settings' ),
					__( 'Settings', 'slick-slider' )
				) 
			) );
		}
		return $output;

	}

	/**
	 * Add link to PayPal donation page and w.org rating page.
	 *
	 * @since 0.1
	 * 
	 * @param array $data  Current plugin row links.
	 * @param string $page Plugin basename.
	 * @return array $data Merged array with links (see description).
	 */
	public static function add_thanks_links( $data, $page ) {

		if ( SLICK_SLIDER_BASE !== $page ) {
			return $data;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return array_merge( $data, array(
				sprintf(
					'<a href="%s" target="blank">%s</a>',
					'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4347QQ8J3L54',
					__( 'Donate', 'slick-slider' )
				),
				sprintf(
					'<a href="%s" target="blank">%s</a>',
					'https://wordpress.org/support/plugin/slick-slider/reviews/#new-post',
					__( 'Rate', 'slick-slider' )
				)
			) );
		}
		return $data;

	}

	/**
	 * Check WordPress version.
	 *
	 * @since 0.1
	 * 
	 * @param string $version WordPress version to check against.
	 * @return boolean        True if WordPress is at least of version $version, false otherwise.
	 */
	public static function is_min_wp( $version ) {

		return version_compare( $GLOBALS['wp_version'], $version . 'alpha', '>=' );

	}

	/**
	 * Check PHP version.
	 *
	 * @since 0.1
	 * 
	 * @param string $version PHP version to check against.
	 * @return boolean        True if PHP is at least of version $version, false otherwise.
	 */
	public static function is_min_php( $version ) {

		return version_compare( phpversion(), $version, '>=' );

	}

	/**
	 * Get plugin metadata value.
	 *
	 * @since 0.1
	 * 
	 * @param string $field Meta field value to get.
	 * @return string|array Single value if $field is set, array of all values otherwise.
	 */
	public static function get_plugin_data( $field = NULL ) {

		if ( ! $plugin_data = Slick_Slider_Cache::get( 'plugin_data' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( SLICK_SLIDER_FILE );
			Slick_Slider_Cache::set( 'plugin_data', $plugin_data );
		}
		if ( ! empty( $field ) && isset( $plugin_data[ $field ] ) ) {
			return $plugin_data[ $field ];
		}
		return $plugin_data;

	}

	/**
	 * Compute the difference of multi-dimensional arrays with additional index check.
	 *
	 * @since 0.1
	 * 
	 * @link http://php.net/manual/de/function.array-diff-assoc.php#111675
	 * @param array $array_1 The array to compare from.
	 * @param array $array_2 An array to compare against.
	 * @return array        Array containing all the values from $array_1 that are not present in $array_2.
	 */
	public static function array_diff_assoc_recursive( $array_1, $array_2 ) { 

		foreach( $array_1 as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( ! isset( $array_2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} elseif ( ! is_array( $array_2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} else  {
					$new_diff = self::array_diff_assoc_recursive( $value, $array_2[ $key ] );
					if ( $new_diff != false ) {
						$difference[ $key ] = $new_diff;
					}
				}
			} elseif ( ! isset( $array_2[ $key ] ) || $array_2[ $key ] != $value ) {
				$difference[ $key ] = $value;
			}
		}
		return ! isset( $difference ) ? 0 : $difference;

	}

	/**
	 * Helper function for getting the `.min` suffix for minified assets.
	 *
	 * @since  0.4
	 * 
	 * @return string `.min` if SCRIPT_DEBUG is set and true, empty string otherwise.
	 */
	public static function get_asset_suffix() {

		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	}

	/**
	 * Enable lazy loading of images by replacing image attributes.
	 *
	 * @since  0.5.0
	 *
	 * @see wp_get_attachment_image()
	 * @param array        $attr       Attributes for the image markup.
	 * @param WP_Post      $attachment Image attachment post.
	 * @param string|array $size       Requested size. Image size or array of width and height values (in that order). Default 'thumbnail'.
	 * @return array $attr 
	 */
	public static function switch_attachment_attr( $attr, $attachment, $size ) {

		$attributes = array(
			'sizes'  => 'data-sizes',
			'src'    => 'data-lazy',
			'srcset' => 'data-srcset',
		);

		foreach ( $attributes as $key => $attribute ) {
			if ( isset( $attr[ $key ] ) ) {
				$attr[ $attribute ] = $attr[ $key ];
				unset( $attr[ $key] );
			}
		}

		return $attr;

	}

}
