<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Outputs all JS files and Slick Slider settings templates for being used by WordPress Media Uploader.
 */
class Slick_Template {

	/**
	 * [init_template description]
	 * @return [type] [description]
	 */
	public static function init_template() {

		add_action( 'print_media_templates', array(
			__CLASS__,
			'print_media_templates'
		) );
		add_action( 'admin_print_scripts', array(
			__CLASS__,
			'print_slider_defaults' 
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'js_extend_gallery' 
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'add_css' 
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'add_js' 
		) );

	}

	/**
	 * Inline prints the settings template.
	 */
	public static function print_media_templates() {

		$pagenow = Slick::current_page(); ?>

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
					<?php Slick_Options::render_settings_markup( $pagenow ); ?>
				</div>
			</div>
		</script>

	<?php }

	/**
	 * Gets Slick Slider options and inline prints them json encoded inside a script tag.
	 * @return string json encoded options
	 */
	public static function print_slider_defaults() {

		$options_json = json_encode( Slick_Options::get() );

		$output = array();
		$output[] = '<script type="text/javascript">';
		$output[] = sprintf( 'var slider_defaults = %s;', $options_json );
		$output[] = '</script>';

		echo implode( "\n", $output );

	}

	/**
	 * Enqueues JS file to extend the wp.media object and register the settings template.
	 */
	public static function js_extend_gallery() {

		wp_enqueue_script(
			'slick-slider-gallery-settings',
			Slick::plugin_url( 'js/slick-post-gallery-defaults.js' ),
			array( 'jquery' ),
			Slick::get_plugin_data( 'Version' ),
			true
		);

	}

	/**
	 * Enqueues CSS file for basic styling of Slick Slider settings section inside WordPress Media Uploader.
	 */
	public static function add_css() {

		wp_enqueue_style(
			'slick-post-gallery',
			Slick::plugin_url( 'css/slick-post-gallery.css' ),
			false,
			Slick::get_plugin_data( 'Version' )
		);

	}

	/**
	 * Enqueues JS file for basic toggling actions of labels.
	 */
	public static function add_js() {

		wp_enqueue_script(
			'slick-post-gallery',
			Slick::plugin_url( 'js/slick-post-gallery.js' ),
			array( 'jquery' ),
			Slick::get_plugin_data( 'Version' )
		);

	}

}