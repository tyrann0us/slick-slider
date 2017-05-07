<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to utput all JS files and Slick Slider settings templates for being used by WordPress Media Uploader.
 *
 * @since 0.1
 */
class Slick_Slider_Template {

	/**
	 * Initiate registering of gallery settings template and required JS and CSS files.
	 *
	 * @since 0.1
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
			'add_css'
		) );
		add_action( 'admin_enqueue_scripts',  array(
			 __CLASS__,
			'add_js'
		) );

	}

	/**
	 * Print the settings template.
	 * 
	 * @since 0.1
	 */
	public static function print_media_templates() {

		$pagenow = Slick_Slider_Main::current_page();
		$show_on_gallery_modal = Slick_Slider_Options::get( 'showOnGalleryModal' )['value'];
		?>

		<script type="text/html" id="tmpl-slick-slider-settings">
			<div class="clear"></div>
			<div class="slick-slider-settings">
				<hr>
				<h3><?php _e( 'Slick Slider', 'slick-slider' ); ?></h3>
				<div class="slick-slider-toggle-settings">
					<label class="setting">
						<span><?php _e( 'Use Slick Slider', 'slick-slider' ); ?></span>
						<input
							type="checkbox"
							data-setting="slick_active"
							<# if ( slickSlider.settings.slickActive ) { #> checked="checked" <# } #>
						>
					</label>
				</div>
				<?php if ( $show_on_gallery_modal ) { ?>
					<div class="slick-slider-settings-inner">
						<?php Slick_Slider_Options::render_settings_markup( $pagenow ); ?>
					</div>
				<?php } ?>
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
	public static function print_slider_defaults() {

		$options_json = json_encode( Slick_Slider_Options::get() );

		$output = [];
		$output[] = '<script type="text/javascript">';
		$output[] = sprintf( 'var slider_defaults = %s;', $options_json );
		$output[] = '</script>';

		echo implode( "\n", $output );

	}

	/**
	 * Add CSS file to style Slick Slider settings in the Media Uploader.
	 *
	 * @since 0.1
	 */
	public static function add_css() {

		$assetSuffix = Slick_Slider_Main::get_asset_suffix();

		wp_enqueue_style(
			'slick-slider-post-gallery',
			Slick_Slider_Main::plugin_url( "css/slick-slider-post{$assetSuffix}.css" ),
			false,
			Slick_Slider_Main::get_plugin_data( 'Version' )
		);

	}

	/**
	 * Add JS file to extend the wp.media object and register the settings template.
	 * Additionally it provides basic toggling actions of settings.
	 *
	 * @since 0.1
	 */
	public static function add_js() {

		$assetSuffix = Slick_Slider_Main::get_asset_suffix();

		wp_enqueue_script(
			'slick-slider-post-gallery',
			Slick_Slider_Main::plugin_url( "js/slick-slider-post{$assetSuffix}.js" ),
			array( 'jquery' ),
			Slick_Slider_Main::get_plugin_data( 'Version' ),
			true
		);

	}

}
