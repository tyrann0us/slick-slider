<?php
/*
Plugin Name: Slick Slider
Plugin URI:  https://wordpress.org/plugins/slick-slider/
Description: Turn your native WordPress galleries into beautiful sliders using the awesome “slick” slider.
Version:     0.1
Author:      Philipp Bammes
Text Domain: slick-slider
License:     GPL2

Slick Slider is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Slick Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Slick Slider. If not, see {URI to Plugin License}.
*/


/* Quit */
defined( 'ABSPATH' ) OR exit;

define( 'SLICK_DIR', dirname( __FILE__ ) );
define( 'SLICK_FILE', __FILE__ );
define( 'SLICK_BASE', plugin_basename( __FILE__ ) );
define( 'SLICK_MIN_PHP', 5.6 );


require_once sprintf(
	'%s/inc/_%s.class.php',
	SLICK_DIR,
	( is_admin() ? 'be' : 'fe' )
);

spl_autoload_register(
	'slickAutoload'
);

add_action(
	'plugins_loaded',
	array(
		'Slick',
		'init'
	)
);


register_activation_hook(
	__FILE__,
	array(
		'Slick',
		'install'
	)
);

register_uninstall_hook(
	__FILE__,
	array(
		'Slick',
		'uninstall'
	)
);


function slickAutoload( $class ) {

	$available = array(
		'slickCache' => 'cache',
		'slickFeedback' => 'feedback',
		'slickOptions' => 'options',
		'slickOutput' => 'output',
		'slickGui' => 'gui',
		'slickTemplate' => 'template',
	);

	if ( isset( $available[$class] ) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				SLICK_DIR,
				$available[$class]
			)
		);
	}

}