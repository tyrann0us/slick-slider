<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to output all files and HTML markup required for Slick Slider on frontend.
 */
class Slick_Slider_Output {

	/**
	 * Number of Slick Sliders on same WordPress page.
	 *
	 * @since 0.1
	 * 
	 * @var integer
	 */
	private static $slick_instance = 0;

	/**
	 * Initiate registering of assets and replacing default WordPress gallery HTML with Slick Slider markup.
	 *
	 * @since 0.1
	 * 
	 * @return If PHP version is too low.
	 */
	public static function init_slider() {

		if ( ! Slick_Slider_Main::is_min_php( SLICK_SLIDER_MIN_PHP ) ) {
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
	 * Register assets (JS and CSS files).
	 * Also initiate Slick Slider and add helper CSS
	 * which moves Slick Slider arrows inside the gallery because it is invisible on white backgrounds.
	 * To prevent output use
	 * add_filter( 'slick_slider_init', '__return_false' ); and
	 * add_filter( 'slick_slider_helper_css', '__return_false' ); respectively.
	 *
	 * @since 0.1
	 */
	public static function register_slick_assets() {

		$asset_suffix = Slick_Slider_Main::get_asset_suffix();

		wp_register_script(
			'slick-slider-core',
			Slick_Slider_Main::plugin_url( "bower_components/slick-carousel/slick/slick{$asset_suffix}.js" ),
			array( 'jquery' ),
			'1.8.0',
			true
		);
		
		wp_register_style(
			'slick-slider-core',
			Slick_Slider_Main::plugin_url( 'bower_components/slick-carousel/slick/slick.css' ),
			array(),
			'1.8.0'
		);
		wp_register_style(
			'slick-slider-core-theme',
			Slick_Slider_Main::plugin_url( 'bower_components/slick-carousel/slick/slick-theme.css' ),
			array( 'slick-slider-core' ),
			'1.8.0'
		);

		if ( apply_filters( 'slick_slider_init_slider', true ) ) {
			wp_add_inline_script( 'slick-slider-core', file_get_contents( SLICK_SLIDER_DIR . '/js/slick-slider-init.js' ) );
		}

		if ( apply_filters( 'slick_slider_load_helper_css', true ) ) {
			wp_add_inline_style( 'slick-slider-core-theme', file_get_contents( SLICK_SLIDER_DIR . "/css/slick-slider-helper{$asset_suffix}.css" ) );
		}

		if ( class_exists( 'WP_Featherlight' ) ) {
			wp_register_script(
				'slick-slider-featherlight-helper',
				Slick_Slider_Main::plugin_url( "js/slick-slider-featherlight-helper{$asset_suffix}.js" ),
				array( 'wp-featherlight' ),
				Slick_Slider_Main::get_plugin_data( 'Version' ),
				true
			);
		}

	}

	/**
	 * Enqueue required assets, build HTML markup for Slick Slider and return it.
	 *
	 * @since 0.1
	 * 
	 * @param string $output    HTML markup.
	 * @param array $atts       Attributes from the gallery shortcode.
	 * @param integer $instance Unique numeric ID of this gallery shortcode instance.
	 * @return string           Complete Slick Slider markup which can be modified by multiple filters.
	 */
	public static function slick_markup( $output = '', $atts = null, $instance = null ) {
		
		if ( isset( $atts['slick_active'] ) && 'true' === $atts['slick_active'] ) {

			global $post;
			self::$slick_instance++;

			$atts = wp_parse_args( $atts, array(
				'order' => 'ASC',
				'orderby' => 'menu_order ID',
				'id' => $post ? $post->ID : 0,
				'size' => 'thumbnail',
				'include' => '',
				'exclude' => '',
				'link' => '',
				'slick_instance' => self::$slick_instance,
			) );
			$atts = apply_filters( 'shortcode_atts_gallery', $atts, [], 'gallery' );

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

				$attachments = [];
				foreach ( $_attachments as $key => $val ) {
					$attachments[ $val->ID ] = $_attachments[ $key ];
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

			wp_enqueue_script( 'slick-slider-featherlight-helper' );
			wp_enqueue_script( 'slick-slider-core' );
			wp_enqueue_style( 'slick-slider-core-theme' );

			$options = Slick_Slider_Options::prepare_options_for_output( $atts );

			if ( apply_filters( 'slick_slider_show_caption', false ) ) {

				/* translators: Replacement string ("Use %3$s instead."), see https://developer.wordpress.org/reference/functions/_deprecated_function/ */
				_deprecated_function( 'add_filter( \'slick_slider_show_caption\' )', 0.5, __( 'the new option “Show caption”', 'slick-slider' ) );

				$options['showCaption'] = true;
			}

			add_filter(
				'wp_get_attachment_image_attributes',
				array(
					'Slick_Slider_Main',
					'switch_attachment_attr'
				),
				10,
				3
			);

			do_action( 'slick_slider_before_slider', $atts, $post->ID, self::$slick_instance );

			$output = [];
			$output[] = '<div class="slick-slider-wrapper">';
			$output[] = sprintf(
				'<div class="slick-slider slick-slider--size-%s" id="slick-slider-%s" %s>',
				sanitize_html_class( $atts['size'] ),
				$atts['slick_instance'],
				! empty( $options )
					? sprintf(
						"data-slick='%s'",
						json_encode( $options )
					)
					: ''
			);

			foreach ( $attachments as $id => $attachment ) {

				do_action( 'slick_slider_before_slide', $id, $post->ID, self::$slick_instance );

				$slide = [];
				$slide[] = sprintf( '<div class="slide" data-attachment-id="%s">', $id );
				$slide[] = '<div class="slide__inner">';

				$image_tag = wp_get_attachment_image( $id, $atts['size'] );

				if ( class_exists( 'WPGalleryCustomLinks' ) && $link = get_post_meta( $id, '_gallery_link_url', true ) ) {
					$slide[] = sprintf(
						'<a href="%s" target="%s">%s</a>',
						esc_html( apply_filters( 'wpgcl_filter_raw_gallery_link_url', $link, $id, $post->ID ) ),
						esc_html( get_post_meta( $id, '_gallery_link_target', true ) ),
						$image_tag
					);
				} elseif ( 'none' !== $atts['link'] ) {
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

				if ( isset( $options['showCaption'] ) && $options['showCaption'] ) {
					$meta = wp_prepare_attachment_for_js( $id );
					$caption_text = ! empty( $meta['caption'] )
						? $meta['caption']
						: ( ! empty( $meta['title'] )
							? $meta['title']
							: $meta['alt'] );

					if ( ! empty( $caption_text ) ) {
						$caption = [];
						$caption[] = '<div class="slide__caption">';
						$caption[] = apply_filters( 'slick_slider_caption_html', $caption_text, $id, $post->ID, self::$slick_instance );
						$caption[] = '</div>';

						$slide[] = implode( "\n", $caption );
					}
				}


				$slide[] = '</div>';
				$slide[] = '</div>';
				$output[] = apply_filters( 'slick_slider_slide_html', implode( "\n", $slide ), $id, $post->ID, self::$slick_instance );

				do_action( 'slick_slider_after_slide', $id, $post->ID, self::$slick_instance );

			}

			$output[] = '</div>';
			$output[] = '</div>';

			$output = implode( "\n", $output );

			do_action( 'slick_slider_after_slider', $atts, $post->ID, self::$slick_instance );

			remove_filter(
				'wp_get_attachment_image_attributes',
				array(
					'Slick_Slider_Main',
					'switch_attachment_attr'
				),
				10
			);

			return apply_filters( 'slick_slider_html', $output, $post->ID, self::$slick_instance );
		}

	}

}
