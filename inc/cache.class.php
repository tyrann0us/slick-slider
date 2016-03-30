<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Caches Slick Slider options and metadata for faster performance.
 */
class slickCache {

	/**
	 * Contains cached values in an array.
	 * @var array
	 */
	private static $_cache;


	/**
	 * Gets cached value ($key).
	 * @param  string $key key to get
	 * @return mixed $cache returns value of $key if not empty, null otherwise
	 */
	public static function get( $key ) {

		if ( empty( $key ) ) {
			return;
		}
		$cache = ( array ) self::$_cache;
		if ( empty( $cache[$key] ) ) {
			return null;
		}
		return $cache[$key];

	}

	/**
	 * Adds a key value pair to the cache.
	 * @param string $key   key to be set
	 * @param string $value corresponding value to set
	 * @return null null if $key is empty
	 */
	public static function set( $key, $value ) {

		if ( empty( $key ) ) {
			return;
		}
		$cache        = ( array ) self::$_cache;
		$cache[$key]  = $value;
		self::$_cache = $cache;
		
	}
}