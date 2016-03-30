<?php
defined( 'ABSPATH' ) || exit;

/**
 * Main class for all frontend methods.
 */
class slick {

	/**
	 * Initiates Slick Slider.
	 */
	public static function init() {

		add_action( 'init', array(
			'slickOutput',
			'initSlider'
		) );

	}

	/**
	 * Get URL to asset using plugins_url().
	 * @param  string $path URI to asset
	 * @return string        full URL to asset			
	 */
	public static function pluginUrl( $path ) {

		return plugins_url( $path, SLICK_FILE );

	}

	/**
	 * Check PHP version.
	 * @param  string  $version PHP version to check against.
	 * @return boolean          true if PHP is at least of version $version, false otherwise
	 */
	public static function isMinPhp( $version ) {

		return version_compare( phpversion(), $version, '>=' );

	}

	/**
	 * Wrapper for WordPress function of the same name. Gets plugin metadata value.
	 * @param  string $field meta field value to get
	 * @return string|array        single value if $field is set, array of all values otherwise
	 */
	public static function get_plugin_data( $field = null )	{

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( SLICK_FILE );
		if ( ! empty( $field ) && isset( $plugin_data[$field] ) ) {
			return $plugin_data[$field];
		}
		return $plugin_data;

	}

/**
 * Computes the difference of multi-dimensional arrays with additional index check.
 * Props: http://php.net/manual/de/function.array-diff-assoc.php#111675
 * @param  array $array1 the array to compare from
 * @param  array $array2 an array to compare against
 * @return array         array containing all the values from $array1 that are not present in $array2.
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