=== Slick Slider ===
Contributors: Tyrannous
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4347QQ8J3L54
Tags: gallery, slider, image slider, slideshow, carousel, slick, jQuery slider, lightbox
Requires at least: 4.6
Tested up to: 4.8
Requires PHP: 5.6
Stable tag: 0.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Turn your native WordPress galleries into beautiful fully responsive sliders. Adjust the slider to your needs on a per gallery base.

== Description ==

Slick Slider turns your native WordPress galleries into beautiful fully responsive sliders. Choose from a wide range of options to adjust all sliders to your needs with a simple click.
Slick Slider allows you to change default options valid for all sliders or to adjust them on a per gallery base.

Available options (amongst others):

* Turn **autoplay** on or off
* Change default **speed** of animation and autoplay
* Turn **fade** effect on or off
* Turn **arrows** and **dots** on or off
* Use **center mode** to see partial prev/next slides
* Make slider **infinite**
* **Pause** slider on hover
* Adjust **slides to scroll** and **slides to show**
* Enable **lazy loading** for better performance
* Stack images up in **rows**
* Turn **vertical sliders** and **RTL support** on or off
* **Link** your images using native gallery settings
* Many more


> Note: No support for slick’s responsive options feature (different options at different breakpoints) at the moment.

> Note: Slick Slider requires at least PHP 5.6! That means it won’t work on websites which are powered by PHP older than version 5.6!
> If you don’t know your website’s PHP version ask your host and request an update if necessary. [Click here](https://wordpress.org/support/topic/attention-slick-slider-requires-at-least-php-5-6/) for more information.


Slick Slider uses the awesome [slick slider](https://kenwheeler.github.io/slick/) written by Ken Wheeler.

== Screenshots ==

1. Media settings. All available options are listed here.
2. Gallery Media Modal. Only the most important options are visible.
3. Frontend with slider.

== Frequently Asked Questions ==

= I don’t want all galleries to become sliders! Is this possible? =

Absolutely! On each gallery Media Modal there’s a checkbox called “Use Slick Slider”. If you leave this box unchecked your gallery will be a simple … gallery.

= Is it possible to place the slider somewhere else, say in a widget? =

Since Slick Slider uses the default gallery shortcode, you can simply paste it wherever you want it to appear.
Note that you may need to [enable support for shortcodes in widgets](http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/). Additionally if you want to edit the slider options you’ll have to change it using the shortcode attributes directly.

= Is it possible to specify custom links for the gallery images? =

Use the plugin [WP Gallery Custom Links](https://wordpress.org/plugins/wp-gallery-custom-links/).

> Note: Only support for custom URLs and link targets (including the filter `wpgcl_filter_raw_gallery_link_url`). No support for additional custom fields or shortcode attributes.

= Is it possible to open linked images in a lightbox? =

Use the plugin [WP Featherlight](https://wordpress.org/plugins/wp-featherlight/).

> Note: The gallery’s setting “Link To” has to be set to “Media File”.

= Is it possible to add captions? =

Use the option “Show caption”.
> Note: The captions aren’t styled. You need to apply some CSS on them (use the class `.slide__caption`).

= Is it possible to use slick’s JS and CSS independently? =

Paste the following lines in your functions.php:

`add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'slick-slider-core' );
	wp_enqueue_style( 'slick-slider-core-theme' );
}, 11 );`

= Is it possible to prevent slick’s JS and CSS to get loaded? = 

Paste the following lines in your functions.php:

`add_action( 'wp_enqueue_scripts', function() {
	wp_deregister_script( 'slick-slider-core' );
	wp_deregister_style( 'slick-slider-core' );
}, 11 );`

> Note: This will also remove the initiation script and helper CSS from the page (see below).

= Is it possible to prevent the slider from getting automatically initiated? =

Use `add_filter( 'slick_slider_init_slider', '__return_false' );`.

= Is it possible to prevent the plugin from adding additional helper CSS on my page? =

These three line of CSS are sometimes required if the page has a white background. Otherwise the slider arrows won’t be visible.
You can turn it off by using `add_filter( 'slick_slider_load_helper_css', '__return_false' );`.

= Is it possible to change the gallery attributes using PHP? =

Use the WordPress core filter [`shortcode_atts_gallery`](http://codex.wordpress.org/Function_Reference/shortcode_atts_gallery).

= Is it possible to adjust the caption’s markup? =

Use the filter `slick_slider_caption_html`. Parameters: caption HTML, attachment ID, post ID, slider instance.

= Is it possible to adjust the markup for each slide? =

Use the filter `slick_slider_slide_html`. Parameters: slide HTML, attachment ID, post ID, slider instance.

= Is it possible to adjust the markup for the entire slider? =

Use the filter `slick_slider_html`. Parameters: slider HTML, post ID, slider instance.

= Is it possible to enqueue scripts and styles unminified? =

Use the constant [`SCRIPT_DEBUG`](https://codex.wordpress.org/Debugging_in_WordPress#SCRIPT_DEBUG).

= I want to buy you a beer! =

Thats great, thanks! First of all, you should say thank you to [Ken Wheeler](http://kenwheeler.github.io/) who developed the actual slick slider.
If you want, [you can buy me a beer too](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4347QQ8J3L54).

== Changelog ==

= 0.5.2 (08/07/2023) =
* Fix: PHP 8.0 compatibility

= 0.5.1 (10/04/2017) =
* Fix: Bug (introduced in 0.5) that caused non Slick galleries following a Slick gallery to not show images

= 0.5 (09/30/2017) =
* Feature: Updated Slick to v1.8.0
* Feature: Added new options “appendDots”, “dotsClass”, "focusOnChange", “pauseOnFocus”, “waitForAnimate” and “zIndex”
* Feature: Added new option “Show caption”, deprecating the `slick_slider_show_caption` filter
* Feature: Always lazy load images to improve page load time
* Fix: Bug that hid the Slick Slider settings in the Gallery Media Modal under certain circumstances
* Misc: Added new actions `slick_slider_before_slider`, `slick_slider_after_slider`, `slick_slider_before_slide` and `slick_slider_after_slide`
* Misc: Extended filters `slick_slider_caption_html`, `slick_slider_slide_html` and `slick_slider_html`
* Misc: Changed default value of "focusOnSelect" to `false`
* Misc: Minor PHP, JavaScript and CSS improvements

= 0.4.2 (01/08/2017) =
* Fix: Bug (introduced in 0.4) that prevented numeric option values in gallery modal to update

= 0.4.1 (01/07/2017) =
* Fix: Bug (introduced in 0.4) that appeared when activating the plugin or resetting the options
* Misc: Several code formatting changes according to the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)

= 0.4 (01/03/2017) =
* Feature: Added support for custom links (see [FAQ section](https://wordpress.org/plugins/slick-slider/#faq))
* Feature: Added lightbox support (see [FAQ section](https://wordpress.org/plugins/slick-slider/#faq))
* Feature: Added support for [`SCRIPT_DEBUG` constant](https://codex.wordpress.org/Debugging_in_WordPress#SCRIPT_DEBUG)

= 0.3 (10/31/2016) =
* Feature: Extended FAQ section in readme.txt
* Fix: Bug that prevented new options to be added (finally)
* Fix: Minor tweaks in readme.txt

= 0.2 (10/23/2016) =
* Feature: Added option to toggle Slick Slider options on gallery modal
* Fix: Minor tweaks in readme.txt
* Fix: Minor bug causing some default values to be rendered in `data-slick` attribute
* Fix: Minor bug that prevented new options to be added
* Fix: Minor bug that caused wrapper div to be included in caption filter
* Fix: Normalized gallery modal CSS

= 0.1 (10/07/2016) =
* Initial release
