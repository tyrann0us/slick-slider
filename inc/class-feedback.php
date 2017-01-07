<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to show Slick Slider warnings.
 *
 * @since 0.1
 */
class Slick_Slider_Feedback {

	/**
	 * Add warning if PHP and/or WordPress version is too low.
	 *
	 * @since 0.1
	 */
	public static function rules() {

		switch ( Slick_Slider_Main::current_page() ) {
			case 'plugins' :
			case 'options-media' :
				if ( ! Slick_Slider_Main::is_min_wp( SLICK_SLIDER_MIN_WP ) ) {
					self::add( 'critical', sprintf( '%s: %s %s.', __( 'Attention', 'slick-slider' ), __( 'Slick Slider requires at least WordPress', 'slick-slider' ), SLICK_SLIDER_MIN_WP ) );
				} elseif ( ! Slick_Slider_Main::is_min_php( SLICK_SLIDER_MIN_PHP ) ) {
					self::add( 'critical', sprintf( '%s: %s %s.', __( 'Attention', 'slick-slider' ), __( 'Slick Slider requires at least PHP', 'slick-slider' ), SLICK_SLIDER_MIN_PHP ) );
				}
				break;
			default :
				break;
		}

	}

	/**
	 * Add Slick Slider message to cache.
	 *
	 * @since 0.1
	 * 
	 * @param string $type Type of message (critical|notice).
	 * @param string $msg  Message to add.
	 */
	public static function add( $type, $msg ) {

		if ( empty( $type ) || empty( $msg ) || ! in_array( $type, array(
			'critical',
			'notice' 
		) ) ) {
			return false;
		}
		$data = (array) Slick_Slider_Cache::get( 'feedback' );
		$data[ $type ] = $msg;
		Slick_Slider_Cache::set( 'feedback', $data );

	}

	/**
	 * Get Slick Slider message from cache.
	 *
	 * @since 0.1
	 * 
	 * @param string $type                Type of message to get
	 * @return string|array|boolean $data False if no message is cached, all messages if $type is empty or messages of type $type.
	 */
	public static function get( $type = '' ) {

		$data = (array) Slick_Slider_Cache::get( 'feedback' );
		if ( empty( $data ) ) {
			return false;
		}
		if ( empty( $type ) ) {
			return $data;
		}
		if ( in_array( $type, array(
			'critical',
			'notice' 
		) ) && ! empty( $data[ $type ] ) ) {
			return $data[ $type ];
		}
		return false;

	}

	/**
	 * Initiate output of message(s) if in multisite installation.
	 *
	 * @since 0.1
	 */
	public static function network() {

		self::_display();

	}

	/**
	 * Initiate output of message(s) if not in multisite installation.
	 *
	 * @since 0.1
	 */
	public static function admin() {

		if ( ! is_multisite() ) {
			self::_display();
		}

	}

	/**
	 * Echo all cached Slick Slider messages with HTML markup.
	 *
	 * @since 0.1
	 * 
	 * @return string All messages.
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
				esc_attr( $matrix[ $type ] ),
				$msg
			);
		}

	}

}
