<?php
/**
Plugin Name: Placester Property Search System
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/


add_action( 'after_setup_theme', 'placester_add_slideshow_components', 20 );
function placester_add_slideshow_components () {
	if (!class_exists('Placester_Blueprint')) {
		include_once('slideshow.php');
	}
}
