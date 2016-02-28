<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Outputs all files and HTML markup required for Slick Slider options on the media settings page.
 */
class Slick_GUI {

	/**
	 * If set to true, skip saving Slick Slider options to database.
	 * This is necessary because update_option() action get called on saving every single option
	 * and thus prevents repeated requests to database.
	 * @var boolean $skip_saving wether to skip saving
	 */
	private static $skip_saving = false;

	/**
	 * Enqueues assets and prints Slick Slider options's HTML markup using add_settings_field().
	 */
	public static function init_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		add_action(
			'admin_enqueue_scripts',
			array(
				 __CLASS__,
				'add_css' 
			)
		);
		add_action(
			'admin_print_styles',
			array(
				 __CLASS__,
				'add_js' 
			)
		);

		add_settings_section(
			'slick',
			__( 'Slick Slider settings', 'slick-wp' ),
			array(
				__CLASS__,
				'setting_section_callback'
			),
			'media'
		);

		$pagenow = Slick::current_page();
		Slick_Options::render_settings_markup( $pagenow );

	}

	/**
	 * Adds CSS file with some basic styling using wp_enqueue_style().
	 */
	public static function add_css() {

		wp_enqueue_style(
			'slick-options-media',
			Slick::plugin_url( 'css/slick-options-media.css' ),
			array(),
			Slick::get_plugin_data( 'Version' )
		);

	}

	/**
	 * Adds JS file using wp_enqueue_script().
	 */
	public static function add_js() {

		wp_enqueue_script(
			'slick-options-media',
			Slick::plugin_url( 'js/slick-options-media.js' ),
			array( 'jquery' ),
			Slick::get_plugin_data( 'Version' )
		);

	}

	/**
	 * Prints intro text and reset button
	 */
	public static function setting_section_callback() {

		wp_nonce_field( '_slick__settings_nonce', '_slick_nonce' );
		echo '<a id="slick-settings"></a>';
		echo '<input type="hidden" name="_slick_action" value="update" />';
		echo sprintf( '<p>%s</p>', __( 'Change default Slick Slider settings.', 'slick-wp' ) );
		echo sprintf(
			'<p>%s</p>',
			sprintf(
				__( 'For more information about the slider visit %s.', 'slick-wp' ),
				'<a href="https://kenwheeler.github.io/slick/" target="blank">https://kenwheeler.github.io/slick/</a>'
			)
		);
		submit_button( __( 'Reset Slick Slider settings', 'slick-wp' ), 'delete', '_slick_reset' );

	}

	/**
	 * Initiates saving Slick Slider options to database.
	 */
	public static function save_changes() {

		if ( self::$skip_saving ) {
			return;
		}
		if ( empty( $_POST ) OR empty( $_POST['_slick_action'] ) ) {
			return;
		}
		if ( ! isset( $_POST['_slick_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['_slick_nonce'], '_slick__settings_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::$skip_saving = true;
		$_POST = array_map( 'stripslashes_deep', $_POST );
		if ( isset( $_POST['_slick_reset'] ) ) {
			Slick_Options::reset();
			return;
		}
		Slick_Options::update( $_POST, true );

	}
}