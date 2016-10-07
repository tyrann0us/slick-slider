<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Main Slick Slider class.
 *
 * @since 0.1
 */
class slickSliderMain {

	/**
	 * Hook all actions and filters for backend and frontend.
	 *
	 * @since 0.1
	 */
	public static function init() {

		if ( is_admin() ) {

			add_action( 'admin_notices', array(
				 'slickSliderFeedback',
				'rules' 
			) );
			add_action( 'network_admin_notices', array(
				 'slickSliderFeedback',
				'network' 
			) );
			add_action( 'admin_notices', array(
				 'slickSliderFeedback',
				'admin' 
			) );

			switch ( self::currentPage() ) {
				case 'post' :
				case 'post-new' :
					add_action( 'admin_init', array(
						'slickSliderTemplate',
						'initTemplate'
					) );
					break;
				case 'options-media' :
					add_action( 'admin_init', array(
						'slickSliderGui',
						'initSettings' 
					) );
					break;
				case 'options' :
					add_action( 'update_option',  array(
						 'slickSliderGui',
						'saveChanges' 
					) );
					break;
				case 'plugins' :
					add_filter( 'plugin_action_links_' . SLICK_SLIDER_BASE, array(
						 __CLASS__,
						'addSettingsLinks' 
					) );
					add_filter( 'plugin_row_meta', array(
						 __CLASS__,
						'addThanksLink' 
					), 10, 2 );
					break;
				default:
					break;
			}

		} else {

			add_action( 'init', array(
				'slickSliderOutput',
				'initSlider'
			) );

		}

	}

	/**
	 * Initiate setting of Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function install() {

		slickSliderOptions::init();

	}

	/**
	 * Initiate removing of Slick Slider options.
	 *
	 * @since 0.1
	 */
	public static function uninstall() {

		slickSliderOptions::destroy();

	}

	/**
	 * Get URL relative to given path.
	 *
	 * @since 0.1
	 * 
	 * @param string $path URI to asset.
	 * @return string      Full URL to asset.
	 */
	public static function pluginUrl( $path ) {

		return plugins_url( $path, SLICK_SLIDER_FILE );

	}

	/**
	 * Get current backend page.
	 *
	 * @since 0.1
	 * 
	 * @return string Slug of curerent page (e. g. 'options-media').
	 */
	public static function currentPage() {

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
	public static function addSettingsLinks( $data ) {

		$output = array_filter( $data, function( $value ) { return ! strpos( $value, 'plugin-editor.php' ); } );
		if ( current_user_can( 'manage_options' ) ) {
			$output = array_merge( $output, array(
				 sprintf( '<a href="%s">%s</a>', admin_url( 'options-media.php#slick-slider-settings' ), __( 'Settings', 'slick-slider' ) ) 
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
	public static function addThanksLink( $data, $page ) {

		if ( SLICK_SLIDER_BASE != $page ) {
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
	public static function isMinWp( $version ) {

		return version_compare( $GLOBALS[ 'wp_version' ], $version . 'alpha', '>=' );

	}

	/**
	 * Check PHP version.
	 *
	 * @since 0.1
	 * 
	 * @param string $version PHP version to check against.
	 * @return boolean        True if PHP is at least of version $version, false otherwise.
	 */
	public static function isMinPhp( $version ) {

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
	public static function getPluginData( $field = NULL ) {

		if ( ! $plugin_data = slickSliderCache::get( 'plugin_data' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( SLICK_SLIDER_FILE );
			slickSliderCache::set( 'plugin_data', $plugin_data );
		}
		if ( ! empty( $field ) && isset( $plugin_data[$field] ) ) {
			return $plugin_data[$field];
		}
		return $plugin_data;

	}

	/**
	 * Compute the difference of multi-dimensional arrays with additional index check.
	 *
	 * @since 0.1
	 * 
	 * @link http://php.net/manual/de/function.array-diff-assoc.php#111675
	 * @param array $array1 The array to compare from.
	 * @param array $array2 An array to compare against.
	 * @return array        Array containing all the values from $array1 that are not present in $array2.
	 */
	public static function arrayDiffAssocRecursive( $array1, $array2 ) { 

		foreach( $array1 as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( ! isset( $array2[$key] ) ) {
					$difference[$key] = $value;
				} elseif ( ! is_array( $array2[$key] ) ) {
					$difference[$key] = $value;
				} else  {
					$new_diff = self::arrayDiffAssocRecursive( $value, $array2[$key] );
					if ( $new_diff != FALSE ) {
						$difference[$key] = $new_diff;
					}
				}
			} elseif ( ! isset( $array2[$key] ) || $array2[$key] != $value ) {
				$difference[$key] = $value;
			}
		}
		return ! isset( $difference ) ? 0 : $difference;

	}

}