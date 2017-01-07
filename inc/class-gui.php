<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to output all files and HTML markup required for
 * Slick Slider options on the media settings page.
 *
 * @since 0.1
 */
class Slick_Slider_Gui {

	/**
	 * If set to true, skip saving Slick Slider options to database.
	 * This is necessary because {@see update_option()} action gets called on saving every
	 * single option and thus prevents repeated requests to database.
	 *
	 * @since 0.1
	 * 
	 * @var boolean $skip_saving Whether to skip saving.
	 */
	private static $skip_saving = false;

	/**
	 * Enqueue assets,
	 * print Slick Slider options's HTML markup
	 * and add help tab to the Contextual Help menu.
	 *
	 * @since 0.1
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

		add_action(
			'load-options-media.php',
			array(
				__CLASS__,
				'add_help_tab'
			)
		);

		add_settings_section(
			'slick-slider',
			__( 'Slick Slider settings', 'slick-slider' ),
			array(
				__CLASS__,
				'setting_section_callback'
			),
			'media'
		);

		$pagenow = Slick_Slider_Main::current_page();
		Slick_Slider_Options::render_settings_markup( $pagenow );

	}

	/**
	 * Add CSS file to style Slick Slider settings in Media settings.
	 *
	 * @since 0.1
	 */
	public static function add_css() {

		$assetSuffix = Slick_Slider_Main::get_asset_suffix();

		wp_enqueue_style(
			'slick-slider-options-media',
			Slick_Slider_Main::plugin_url( "css/slick-slider-options-media{$assetSuffix}.css" ),
			array(),
			Slick_Slider_Main::get_plugin_data( 'Version' )
		);

	}

	/**
	 * Add JS file.
	 *
	 * @since 0.1
	 */
	public static function add_js() {

		$assetSuffix = Slick_Slider_Main::get_asset_suffix();

		wp_enqueue_script(
			'slick-slider-options-media',
			Slick_Slider_Main::plugin_url( "js/slick-slider-options-media{$assetSuffix}.js" ),
			array( 'jquery-ui-accordion' ),
			Slick_Slider_Main::get_plugin_data( 'Version' )
		);

	}

	/**
	 * Add help tab.
	 *
	 * @since 0.1
	 */
	public static function add_help_tab() {
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
	 * Print intro text and reset button.
	 *
	 * @since 0.1
	 */
	public static function setting_section_callback() {

		wp_nonce_field( '_slick__settings_nonce', '_slick_nonce' );
		echo '<a name="slick-slider-settings" id="slick-slider-settings"></a>';
		echo '<input type="hidden" name="_slick_action" value="update" />';
		submit_button( __( 'Reset Slick Slider settings', 'slick-slider' ), 'delete', '_slick_reset' );
		echo sprintf(
			'<span class="button collapse-header hidden" data-collapse-header-text="%s">%s</span>',
			__( 'Collapse settings', 'slick-slider' ),
			__( 'Expand settings', 'slick-slider' )
		);

	}

	/**
	 * Initiate saving Slick Slider options to database.
	 *
	 * @since 0.1
	 */
	public static function save_changes() {

		if ( self::$skip_saving ) {
			return;
		}
		if ( empty( $_POST ) || empty( $_POST['_slick_action'] ) ) {
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
			Slick_Slider_Options::reset();
			return;
		}
		Slick_Slider_Options::update( $_POST, true );

	}
}
