<?php
defined( 'ABSPATH' ) || exit;

/**
 * Main class for all frontend methods.
 *
 * @since 0.1
 */
class slickSlider {

	/**
	 * Initiate adding of Slick Slider’s assets and markup.
	 *
	 * @since 0.1
	 */
	public static function init() {

		add_action( 'init', array(
			'slickSliderOutput',
			'initSlider'
		) );

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
	public static function getPluginData( $field = NULL )	{

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( SLICK_SLIDER_FILE );
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