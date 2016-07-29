<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Show Slick Slider warnings using (network)_admin_notices().
 */
class slickFeedback {

	/**
	 * Adds warning if PHP and/or WordPress version is too low.
	 */
	public static function rules() {

		switch ( slick::currentPage() ) {
			case 'plugins' :
			case 'options-media' :
				if ( ! slick::isMinWp( '4.4' ) ) {
					self::add( 'critical', sprintf( '%s: %s %s.', __( 'Attention', 'slick-slider' ), __( 'Slick Slider requires at least WordPress', 'slick-slider' ), '4.4' ) );
				} else if ( ! slick::isMinPhp( SLICK_MIN_PHP ) ) {
					self::add( 'critical', sprintf( '%s: %s %s.', __( 'Attention', 'slick-slider' ), __( 'Slick Slider requires at least PHP', 'slick-slider' ), SLICK_MIN_PHP ) );
				}
				break;
			default :
				break;
		}
		
	}

	/**
	 * Adds Slick Slider message to cache.
	 * @param string $type type of message (critical|notice)
	 * @param string $msg  message to add
	 */
	public static function add( $type, $msg ) {

		if ( empty( $type ) OR empty( $msg ) OR ! in_array( $type, array(
			'critical',
			'notice' 
		) ) ) {
			return false;
		}
		$data        = ( array ) slickCache::get( 'feedback' );
		$data[$type] = $msg;
		Slick_Cache::set( 'feedback', $data );

	}

	/**
	 * Gets Slick Slider message from cache.
	 * @param  string $type type of message to get
	 * @return string|array|boolean $data false if no message is cached, all messages if $type is empty or messages of type $type
	 */
	public static function get( $type = '' ) {

		$data = ( array ) slickCache::get( 'feedback' );
		if ( empty( $data ) ) {
			return false;
		}
		if ( empty( $type ) ) {
			return $data;
		}
		if ( in_array( $type, array(
			'critical',
			'notice' 
		) ) && ! empty( $data[$type] ) ) {
			return $data[$type];
		}
		return false;

	}

	/**
	 * Initiate output of message(s) if in multisite installtion.
	 */
	public static function network() {

		self::_display();

	}

	/**
	 * Initiate output of message(s) if not in multisite installtion.
	 */
	public static function admin() {

		if ( ! is_multisite() ) {
			self::_display();
		}

	}

	/**
	 * Echos all cached Slick Slider messages including correct markup.
	 * @return string all messages
	 */
	private static function _display() {

		if ( ! $errors = self::get() ) {
			return false;
		}
		$matrix = array(
			'critical' => 'error',
			'notice' => 'success' 
		);
		foreach ( $errors as $type => $msg ) {
			echo sprintf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $matrix[$type] ),
				$msg
			);
		}

	}

}