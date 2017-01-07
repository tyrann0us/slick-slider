<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to handle Slick Slider internal caching system of
 * options and metadata.
 *
 * @since 0.1
 */
class Slick_Slider_Cache {

	/**
	 * Cached values stored in array.
	 * 
	 * @since 0.1
	 * 
	 * @var array
	 */
	private static $_cache;


	/**
	 * Get cached value ($key).
	 *
	 * @since 0.1
	 * 
	 * @param string $key   Key to get.
	 * @return mixed $cache Value of $key if not empty, null otherwise.
	 */
	public static function get( $key ) {

		if ( empty( $key ) ) {
			return;
		}
		$cache = (array) self::$_cache;
		if ( empty( $cache[ $key ] ) ) {
			return null;
		}
		return $cache[ $key ];

	}

	/**
	 * Add a key value pair to the cache.
	 *
	 * @since 0.1
	 * 
	 * @param string $key   Key to be set.
	 * @param string $value Corresponding value to set.
	 * @return null         If $key is empty.
	 */
	public static function set( $key, $value ) {

		if ( empty( $key ) ) {
			return;
		}
		$cache = (array) self::$_cache;
		$cache[ $key ] = $value;
		self::$_cache = $cache;

	}
}
