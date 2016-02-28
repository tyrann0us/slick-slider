<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Outputs all files and HTML markup required for main Slick Slider on frontend.
 */
class Slick_Output {

	/**
	 * Number of Slick Sliders on same WordPress page.
	 * @var integer
	 */
	private static $slick_instance = 0;

	/**
	 * Initiate registering of assets and replacing default WordPress gallery HTML with Slick Slider markup using add_filter().
	 * @return if PHP version is too old
	 */
	public static function init_slider() {

		if ( ! Slick::is_min_php( SLICK_MIN_PHP ) ) {
			return;
		}

		add_action(
			'wp_enqueue_scripts',
			array(
				__CLASS__,
				'register_slick_assets'
			)
		);

		add_filter(
			'post_gallery',
			array(
				__CLASS__,
				'slick_markup'
			),
			10,
			3
		);

	}

	/**
	 * Registers assets (JS and CSS files).
	 */
	public static function register_slick_assets() {

		wp_register_script(
			'slick',
			Slick::plugin_url( 'bower_components/slick-carousel/slick/slick.min.js' ),
			array( 'jquery' ),
			'1.5.9',
			true
		);
		wp_register_style(
			'slick',
			Slick::plugin_url( 'bower_components/slick-carousel/slick/slick.css' ),
			array(),
			'1.5.9'
		);
		wp_register_style(
			'slick-theme',
			Slick::plugin_url( 'bower_components/slick-carousel/slick/slick-theme.css' ),
			array( 'slick' ),
			'1.5.9'
		);

	}


	/**
	 * Reads JS from file and returns it.
	 * Initiates Slick Slider.
	 * Prevent output with
	 * add_filter( 'slick_slider_init', '__return_false' );
	 * @return string           minified initiation script
	 */
	public static function slick_init() {

		if ( ! wp_script_is( 'slick', 'done' ) ) {
			return;
		}
		if ( false === apply_filters( 'slick_slider_init', '' ) ) {
			return;
		}

		$output = [];
		$output[] = '<script type="text/javascript">';
		$output[] = file_get_contents( SLICK_DIR . '/js/slick-init.js' );
		$output[] = '</script>';

		echo str_replace( array( "\r", "\n", "\t" ), '', implode( "\n", $output ) );

	}

	/**
	 * Reads CSS from file and returns it.
	 * The CSS moves Slick Slider arrows inside the gallery because it is invisible on white backgrounds.
	 * Prevent output with
	 * add_filter( 'slick_slider_helper_css', '__return_false' );
	 * @return string           minified CSS script
	 */
	public static function slick_helper_css() {

		if ( false === apply_filters( 'slick_slider_helper_css', '' ) ) {
			return;
		}

		$output = [];
		$output[] = '<style type="text/css">';
		$output[] = file_get_contents( SLICK_DIR . '/css/slick-frontend.css' );
		$output[] = '</style>';

		echo str_replace( array( "\r", "\n", "\t" ), '', implode( "\n", $output ) );

	}

	/**
	 * Main method to enqueue required assets, build HTML markup for Slick Slider and return it.
	 * @param  string $output   the current output
	 * @param  array $atts     the attributes from the gallery shortcode
	 * @param  integer $instance unique numeric ID of this gallery shortcode instance
	 * @return string           complete Slick Slider markup which can be modified by multiple filters
	 */
	public static function slick_markup( $output = '', $atts, $instance ) {
		
		if ( isset( $atts['slick_active'] ) && 'true' === $atts['slick_active'] ) {

			global $post;
			wp_enqueue_script( 'slick' );
			wp_enqueue_style( 'slick-theme' );

			add_action(
				'wp_footer',
				array(
					__CLASS__,
					'slick_init',
				),
				100
			);

			add_action(
				'wp_footer',
				array(
					__CLASS__,
					'slick_helper_css',
				)
			);


			$atts = wp_parse_args( $atts, array(
				'order' => 'ASC',
				'orderby' => 'menu_order ID',
				'id' => $post ? $post->ID : 0,
				'size' => 'thumbnail',
				'include' => '',
				'exclude' => '',
				'link' => '',
			) );
			$atts = apply_filters( 'shortcode_atts_gallery', $atts, array(), 'gallery' );

			$id = intval( $atts['id'] );

			if ( ! empty( $atts['include'] ) ) {
				$_attachments = get_posts(
					array(
						'include' => $atts['include'],
						'post_status' => 'inherit',
						'post_type' => 'attachment',
						'post_mime_type' => 'image',
						'order' => $atts['order'],
						'orderby' => $atts['orderby']
					)
				);

				$attachments = array();
				foreach ( $_attachments as $key => $val ) {
					$attachments[$val->ID] = $_attachments[$key];
				}
			} elseif ( ! empty( $atts['exclude'] ) ) {
				$attachments = get_children(
					array(
						'post_parent' => $id,
						'exclude' => $atts['exclude'],
						'post_status' => 'inherit',
						'post_type' => 'attachment',
						'post_mime_type' => 'image',
						'order' => $atts['order'],
						'orderby' => $atts['orderby']
					)
				);
			} else {
				$attachments = get_children(
					array(
						'post_parent' => $id,
						'post_status' => 'inherit',
						'post_type' => 'attachment',
						'post_mime_type' => 'image',
						'order' => $atts['order'],
						'orderby' => $atts['orderby']
					)
				);
			}

			if ( empty( $attachments ) ) {
				return '';
			}

			$options = Slick_Options::prepare_options_for_output( $atts );

			$output = [];
			$output[] = '<div class="slick-slider-wrapper">';
			$output[] = sprintf(
				'<div class="slick-slider slick-slider-size-%s" id="slick-slider-%s" data-slick=\'%s\'>',
				sanitize_html_class( $atts['size'] ),
				++self::$slick_instance,
				json_encode( $options )
			);
				
			foreach ( $attachments as $id => $attachment ) {

				$slide = [];
				$slide[] = sprintf( '<div class="slide" data-attachment-id="%s">', $id );

				$image_src = wp_get_attachment_image_src( $id, $atts['size'] );
				$meta = wp_prepare_attachment_for_js( $id );
				$image_tag = isset( $options['lazyLoad'] ) && 'progressive' === $options['lazyLoad']
					? sprintf(
						'<img data-lazy="%s" width="%s" height="%s" alt="%s" />',
						$image_src[0],
						$image_src[1],
						$image_src[2],
						$meta['alt']
							? sanitize_title( $meta['alt'] )
							: sanitize_title( $meta['title'] )
					)
					: wp_get_attachment_image( $id, $atts['size'] );

				if ( 'none' !== $atts['link'] ) {
					$slide[] = wp_get_attachment_link(
						$id,
						$atts['size'],
						'file' === $atts['link'] ? false : true,
						false,
						$image_tag
					);
				} else {
					$slide[] = $image_tag;
				}

				$slide[] = '</div>';
				$output[] = apply_filters( 'slick_slider_slide', implode( "\n", $slide ), $id, $post->ID );

			}

			$output[] = '</div>';
			$output[] = '</div>';

			$output = implode( "\n", $output );

			return apply_filters( 'slick_slider', $output );
		}

	}

}