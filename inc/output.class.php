<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Class to output all files and HTML markup required for Slick Slider on frontend.
 */
class slickSliderOutput {

	/**
	 * Number of Slick Sliders on same WordPress page.
	 *
	 * @since 0.1
	 * 
	 * @var integer
	 */
	private static $slickInstance = 0;

	/**
	 * Initiate registering of assets and replacing default WordPress gallery HTML with Slick Slider markup.
	 *
	 * @since 0.1
	 * 
	 * @return If PHP version is too low.
	 */
	public static function initSlider() {

		if ( ! slickSliderMain::isMinPhp( SLICK_SLIDER_MIN_PHP ) ) {
			return;
		}

		add_action(
			'wp_enqueue_scripts',
			array(
				__CLASS__,
				'registerSlickAssets'
			)
		);

		add_filter(
			'post_gallery',
			array(
				__CLASS__,
				'slickMarkup'
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
	public static function registerSlickAssets() {

		$assetSuffix = slickSliderMain::getAssetSuffix();

		wp_register_script(
			'slick-slider-core',
			slickSliderMain::pluginUrl( "bower_components/slick-carousel/slick/slick{$assetSuffix}.js" ),
			array( 'jquery' ),
			'1.6.0',
			true
		);
		
		wp_register_style(
			'slick-slider-core',
			slickSliderMain::pluginUrl( 'bower_components/slick-carousel/slick/slick.css' ),
			array(),
			'1.6.0'
		);
		wp_register_style(
			'slick-slider-core-theme',
			slickSliderMain::pluginUrl( 'bower_components/slick-carousel/slick/slick-theme.css' ),
			array( 'slick-slider-core' ),
			'1.6.0'
		);

		if ( apply_filters( 'slick_slider_init_slider', true ) ) {
			wp_add_inline_script( 'slick-slider-core', file_get_contents( SLICK_SLIDER_DIR . '/js/slick-slider-init.js' ) );
		}

		if ( apply_filters( 'slick_slider_load_helper_css', true ) ) {
			wp_add_inline_style( 'slick-slider-core-theme', file_get_contents( SLICK_SLIDER_DIR . "/css/slick-slider-helper{$assetSuffix}.css" ) );
		}

		if ( class_exists( 'WP_Featherlight' ) ) {
			wp_register_script(
				'slick-slider-featherlight-helper',
				slickSliderMain::pluginUrl( "js/slick-slider-featherlight-helper{$assetSuffix}.js" ),
				array( 'wp-featherlight' ),
				slickSliderMain::getPluginData( 'Version' ),
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
	public static function slickMarkup( $output = '', $atts, $instance ) {
		
		if ( isset( $atts['slick_active'] ) && 'true' === $atts['slick_active'] ) {

			global $post;

			$atts = wp_parse_args( $atts, array(
				'order' => 'ASC',
				'orderby' => 'menu_order ID',
				'id' => $post ? $post->ID : 0,
				'size' => 'thumbnail',
				'include' => '',
				'exclude' => '',
				'link' => '',
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

			wp_enqueue_script( 'slick-slider-featherlight-helper' );
			wp_enqueue_script( 'slick-slider-core' );
			wp_enqueue_style( 'slick-slider-core-theme' );

			$options = slickSliderOptions::prepareOptionsForOutput( $atts );

			$output = [];
			$output[] = '<div class="slick-slider-wrapper">';
			$output[] = sprintf(
				'<div class="slick-slider slick-slider--size-%s" id="slick-slider-%s" %s>',
				sanitize_html_class( $atts['size'] ),
				++self::$slickInstance,
				! empty( $options )
					? sprintf(
						'data-slick=\'%s\'',
						json_encode( $options )
					)
					: ''
			);

			foreach ( $attachments as $id => $attachment ) {

				$slide = [];
				$slide[] = sprintf( '<div class="slide" data-attachment-id="%s">', $id );
				$slide[] = '<div class="slide__inner">';

				$image_src = wp_get_attachment_image_src( $id, $atts['size'] );
				$meta = wp_prepare_attachment_for_js( $id );
				$image_tag = isset( $options['lazyLoad'] ) && 'progressive' === $options['lazyLoad']
					? sprintf(
						'<img data-lazy="%s" width="%s" height="%s" alt="%s" />',
						$image_src[0],
						$image_src[1],
						$image_src[2],
						$meta['alt'] ? sanitize_title( $meta['alt'] ) : ''
					)
					: wp_get_attachment_image( $id, $atts['size'] );


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

				if ( apply_filters( 'slick_slider_show_caption', false ) ) {
					$caption_text = ! empty( $meta['caption'] )
						? $meta['caption']
						: ( ! empty( $meta['title'] )
							? $meta['title']
							: $meta['alt'] );

					if ( ! empty( $caption_text ) ) {
						$caption = [];
						$caption[] = '<div class="slide__caption">';
						$caption[] = apply_filters( 'slick_slider_caption_html', $caption_text, $id, $post->ID );
						$caption[] = '</div>';

						$slide[] = implode( "\n", $caption );
					}
				}


				$slide[] = '</div>';
				$slide[] = '</div>';
				$output[] = apply_filters( 'slick_slider_slide_html', implode( "\n", $slide ), $id, $post->ID );

			}

			$output[] = '</div>';
			$output[] = '</div>';

			$output = implode( "\n", $output );

			return apply_filters( 'slick_slider_html', $output, $post->ID );
		}

	}

}