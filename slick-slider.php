<?php
/*
Plugin Name: Slick Slider
Plugin URI:  https://wordpress.org/plugins/slick-slider/
Description: Turn your native WordPress galleries into beautiful sliders. Powered by the awesome “slick” slider.
Version:     0.3
Author:      Philipp Bammes
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: slick-slider
Domain Path: /languages

Slick Slider is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Slick Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Slick Slider. If not, see <http://www.gnu.org/licenses/>.
*/


/* Quit */
defined( 'ABSPATH' ) OR exit;

define( 'SLICK_SLIDER_DIR', dirname( __FILE__ ) );
define( 'SLICK_SLIDER_FILE', __FILE__ );
define( 'SLICK_SLIDER_BASE', plugin_basename( __FILE__ ) );
define( 'SLICK_SLIDER_MIN_PHP', 5.6 );


spl_autoload_register(
	'slickSliderAutoload'
);

add_action(
	'plugins_loaded',
	array(
		'slickSliderMain',
		'init'
	)
);


register_activation_hook(
	__FILE__,
	array(
		'slickSliderMain',
		'install'
	)
);

register_uninstall_hook(
	__FILE__,
	array(
		'slickSliderMain',
		'uninstall'
	)
);


function slickSliderAutoload( $class ) {

	$available = array(
		'slickSliderMain' => 'main',
		'slickSliderCache' => 'cache',
		'slickSliderFeedback' => 'feedback',
		'slickSliderOptions' => 'options',
		'slickSliderOutput' => 'output',
		'slickSliderGui' => 'gui',
		'slickSliderTemplate' => 'template',
	);

	if ( isset( $available[$class] ) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				SLICK_SLIDER_DIR,
				$available[$class]
			)
		);
	}

}