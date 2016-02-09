<?php
/**
Plugin Name: Placester Google Maps Integration
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/


add_action( 'after_setup_theme', 'placester_add_maps_if_no_blueprint', 20 );
function placester_add_maps_if_no_blueprint () {
	if (!class_exists('Placester_Blueprint')) {
		include_once('lib/maps-util.php');
		include_once('lib/lifestyle.php');
		include_once('lib/lifestyle_polygon.php');
		include_once('lib/listings.php');
		include_once('lib/polygon.php');
		include_once('lib/neighborhood.php');

		include_once('lib/office-widget.php');
	}
}
