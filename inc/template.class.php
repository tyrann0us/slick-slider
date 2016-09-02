<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to utput all JS files and Slick Slider settings templates for being used by WordPress Media Uploader.
 *
 * @since 0.1
 */
class slickTemplate {

	/**
	 * Initiate registering of gallery settings template and required JS and CSS files.
	 *
	 * @since 0.1
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
	 * Print the settings template.
	 * 
	 * @since 0.1
	 */
	public static function printMediaTemplates() {

		$pagenow = slick::currentPage(); ?>

		<script type="text/html" id="tmpl-slick-slider-gallery-settings">
			<div class="clear"></div>
			<div class="slick-slider-settings">
				<hr>
				<h3><?php _e( 'Slick Slider', 'slick-slider' ); ?></h3>
				<div class="slick-slider-toggle-settings">
					<label class="setting">
						<span><?php _e( 'Use Slick Slider', 'slick-slider' ); ?></span>
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
	 * Get Slick Slider options and inline print them json encoded inside a script tag.
	 *
	 * @since 0.1
	 * 
	 * @return string json encoded options.
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
	 * Add JS file to extend the wp.media object and register the settings template.
	 * 
	 * @since 0.1
	 */
	public static function jsExtendGallery() {

		wp_enqueue_script(
			'slick-gallery-settings',
			slick::pluginUrl( 'js/slick-gallery-settings.js' ),
			array( 'media-views' ),
			slick::getPluginData( 'Version' ),
			true
		);

	}

	/**
	 * Add CSS file to style Slick Slider settings in the Media Uploader.
	 *
	 * @since 0.1
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
	 * Add JS file for basic toggling actions of labels.
	 *
	 * @since 0.1
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