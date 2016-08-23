<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Outputs all files and HTML markup required for Slick Slider options on the media settings page.
 */
class slickGui {

	/**
	 * If set to true, skip saving Slick Slider options to database.
	 * This is necessary because update_option() action get called on saving every single option
	 * and thus prevents repeated requests to database.
	 * @var boolean $skip_saving wether to skip saving
	 */
	private static $skipSaving = false;

	/**
	 * Enqueues assets,
	 * prints Slick Slider options's HTML markup using add_settings_field()
	 * and adds a help tab to the Contextual Help menu.
	 */
	public static function initSettings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		add_action(
			'admin_enqueue_scripts',
			array(
				 __CLASS__,
				'addCss' 
			)
		);
		add_action(
			'admin_print_styles',
			array(
				 __CLASS__,
				'addJs' 
			)
		);

		add_action(
			'load-options-media.php',
			array(
				__CLASS__,
				'addHelpTab'
			)
		);

		add_settings_section(
			'slick',
			__( 'Slick Slider settings', 'slick-slider' ),
			array(
				__CLASS__,
				'settingSectionCallback'
			),
			'media'
		);

		$pagenow = slick::currentPage();
		slickOptions::renderSettingsMarkup( $pagenow );

	}

	/**
	 * Adds CSS file with some basic styling using wp_enqueue_style().
	 */
	public static function addCss() {

		wp_enqueue_style(
			'slick-options-media',
			slick::pluginUrl( 'css/slick-options-media.css' ),
			array(),
			slick::getPluginData( 'Version' )
		);

	}

	/**
	 * Adds JS file using wp_enqueue_script().
	 */
	public static function addJs() {

		wp_enqueue_script(
			'slick-options-media',
			slick::pluginUrl( 'js/slick-options-media.js' ),
			array( 'jquery' ),
			slick::getPluginData( 'Version' )
		);

	}

	/**
	 * Adds help tab.
	 */
	public static function addHelpTab() {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id' => 'slick-slider-help',
			'title' => esc_html__( 'Slick Slider', 'slick-slider' ),
			'content' => sprintf(
				'<p>%s</p>',
				sprintf(
					__( 'For more information about the slider visit %s.', 'slick-slider' ),
					'<a href="https://kenwheeler.github.io/slick/" target="_blank">https://kenwheeler.github.io/slick/</a>'
				)
			)
		) );
	}

	/**
	 * Prints intro text and reset button.
	 */
	public static function settingSectionCallback() {

		wp_nonce_field( '_slick__settings_nonce', '_slick_nonce' );
		echo '<a id="slick-settings"></a>';
		echo '<input type="hidden" name="_slick_action" value="update" />';
		echo sprintf( '<p>%s</p>', __( 'Change default Slick Slider settings.', 'slick-slider' ) );
		submit_button( __( 'Reset Slick Slider settings', 'slick-slider' ), 'delete', '_slick_reset' );

	}

	/**
	 * Initiates saving Slick Slider options to database.
	 */
	public static function saveChanges() {

		if ( self::$skipSaving ) {
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

		self::$skipSaving = true;
		$_POST = array_map( 'stripslashes_deep', $_POST );
		if ( isset( $_POST['_slick_reset'] ) ) {
			slickOptions::reset();
			return;
		}
		slickOptions::update( $_POST, true );

	}
}