<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Outputs all JS files and Slick Slider settings templates for being used by WordPress Media Uploader.
 */
class slickTemplate {

	/**
	 * [init_template description]
	 * @return [type] [description]
	 */
	public static function initTemplate() {

		add_action( 'print_media_templates', array(
			__CLASS__,
			'printMediaTemplates'
		) );
		add_action( 'admin_print_scripts', array(
			__CLASS__,
			'printSliderDefaults' 
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'jsExtendGallery' 
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'addCss' 
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'addJs' 
		) );

	}

	/**
	 * Inline prints the settings template.
	 */
	public static function printMediaTemplates() {

		$pagenow = slick::currentPage(); ?>

		<script type="text/html" id="tmpl-slick-slider-gallery-setting">
			<div class="clear"></div>
			<div class="slick-slider-settings">
				<hr>
				<h3><?php _e( 'Slick Slider', 'slick-wp' ); ?></h3>
				<div class="slick-slider-toggle-settings">
					<label class="setting">
						<span><?php _e( 'Use Slick Slider', 'slick-wp' ); ?></span>
						<input type="checkbox" data-setting="slick_active">
					</label>
				</div>
				<div class="slick-slider-settings-inner">
					<?php slickOptions::renderSettingsMarkup( $pagenow ); ?>
				</div>
			</div>
		</script>

	<?php }

	/**
	 * Gets Slick Slider options and inline prints them json encoded inside a script tag.
	 * @return string json encoded options
	 */
	public static function printSliderDefaults() {

		$options_json = json_encode( slickOptions::get() );

		$output = array();
		$output[] = '<script type="text/javascript">';
		$output[] = sprintf( 'var slider_defaults = %s;', $options_json );
		$output[] = '</script>';

		echo implode( "\n", $output );

	}

	/**
	 * Enqueues JS file to extend the wp.media object and register the settings template.
	 */
	public static function jsExtendGallery() {

		wp_enqueue_script(
			'slick-slider-gallery-settings',
			slick::pluginUrl( 'js/slick-post-gallery-defaults.js' ),
			array( 'jquery' ),
			slick::getPluginData( 'Version' ),
			true
		);

	}

	/**
	 * Enqueues CSS file for basic styling of Slick Slider settings section inside WordPress Media Uploader.
	 */
	public static function addCss() {

		wp_enqueue_style(
			'slick-post-gallery',
			slick::pluginUrl( 'css/slick-post.css' ),
			false,
			slick::getPluginData( 'Version' )
		);

	}

	/**
	 * Enqueues JS file for basic toggling actions of labels.
	 */
	public static function addJs() {

		wp_enqueue_script(
			'slick-post-gallery',
			slick::pluginUrl( 'js/slick-post.js' ),
			array( 'jquery' ),
			slick::getPluginData( 'Version' )
		);

	}

}