=== Slick Slider ===
Contributors: tyrann0us
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4347QQ8J3L54
Tags: gallery, slider, image slider, slideshow, carousel, slick slider
Requires at least: 4.6
Tested up to: 4.6
Stable tag: 0.1
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
> If you don’t know your website’s PHP version ask your host and request an update if necessary.


Slick Slider is based upon the awesome [slick slider](https://kenwheeler.github.io/slick/) written by Ken Wheeler.


== Installation ==

1. Upload the extracted plugin folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the “Plugins” screen in WordPress
3. Use the “Settings” -> “Media” screen to configure the default slider options or leave it as it is


== Frequently Asked Questions ==

= I don’t want all galleries to become sliders! Is this possible? =

Absolutely! On each gallery edit modal there’s a checkbox called “Use Slick Slider”. If you leave this box unchecked your gallery will be a simple … gallery.

= I want the slider to appear somewhere else, say in a widget =

Since Slick Slider uses the default gallery shortcode, you can simply paste it wherever you want it to appear.
Note that you may need to [enable support for shortcodes in widgets](http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/). Additionally if you want to edit the slider options you’ll have to change it using the shortcode attributes directly.

= Are there captions available? =

Yes, but hidden by default. To activate them use `add_filter( 'slick_slider_show_caption', '__return_true' );`.
> Note: The captions are unstyled. You need to apply some CSS on them (use the class `.slide__caption`).

= Is it possible to only use slick’s JS and CSS?

Of course. Paste the following lines in your template or functions.php:

`add_action( 'wp_enqueue_scripts', 'slick_slider_enqueue_assets' );
function slick_slider_enqueue_assets() {
	wp_enqueue_script( 'slick-slider-core' );
	wp_enqueue_style( 'slick-slider-core-theme' );
}`

= I don’t want the Plugin to initiate the slider =

Use `add_filter( 'slick_slider_init_slider', '__return_false' );`

= I don’t want the Plugin to print additional CSS on my page =

These three line of CSS are sometimes required if the page has a white background. Otherwise the slider arrows won’t be visible.
You can turn it off by using `add_filter( 'slick_slider_load_helper_css', '__return_false' );`

= I want to change the gallery attributes using PHP =

Use the WordPress core filter `[shortcode_atts_gallery](http://codex.wordpress.org/Function_Reference/shortcode_atts_gallery)`.

= Is it possible to adjust the caption’s markup? =

Use the filter `slick_slider_caption_html`. First argument is the caption’s HTML, second is the attachment ID, third is the post ID.

= Is it possible to adjust the markup for each slide? =

Use the filter `slick_slider_slide_html`. First argument is the slide’s HTML markup, second is the attachment ID, third is the post ID.

= Is it possible to adjust the markup for the entire slider? =

Use the filter `slick_slider_html`. First argument is the slider’s HTML markup, second is the post ID.

= I want to buy you a beer! =

Thats great, thanks! First of all, you should say thank you to [Ken Wheeler](http://kenwheeler.github.io/) who developed the actual slick slider.
If you want, you can buy me a beer too. You’ll find the donation link on your plugin page once you have Slick Slider installed and activated.


== Screenshots ==

1. Media settings screen. All available options are listed here.
2. Single gallery screen. Only the most important options are visible.
3. Frontend with slider.

== Changelog ==

= 0.1 =
* Initial release