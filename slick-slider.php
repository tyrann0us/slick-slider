<?php
/*
Plugin Name: Slick Slider
Plugin URI:  https://wordpress.org/plugins/slick-slider/
Description: Turn your native WordPress galleries into beautiful sliders. Powered by the awesome “slick” slider.
Version:     0.5.2
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
define( 'SLICK_SLIDER_MIN_WP', 4.6 );
define( 'SLICK_SLIDER_MIN_PHP', 5.6 );


spl_autoload_register(
	'Slick_Slider_Autoload'
);

add_action(
	'plugins_loaded',
	array(
		'Slick_Slider_Main',
		'init'
	)
);


register_activation_hook(
	__FILE__,
	array(
		'Slick_Slider_Main',
		'install'
	)
);

register_uninstall_hook(
	__FILE__,
	array(
		'Slick_Slider_Main',
		'uninstall'
	)
);


function Slick_Slider_Autoload( $class ) {

	$available = array(
		'Slick_Slider_Main' => 'main',
		'Slick_Slider_Cache' => 'cache',
		'Slick_Slider_Feedback' => 'feedback',
		'Slick_Slider_Options' => 'options',
		'Slick_Slider_Output' => 'output',
		'Slick_Slider_Gui' => 'gui',
		'Slick_Slider_Template' => 'template',
	);

	if ( isset( $available[ $class ] ) ) {
		require_once(
			sprintf(
				'%s/inc/class-%s.php',
				SLICK_SLIDER_DIR,
				$available[ $class ]
			)
		);
	}

}
