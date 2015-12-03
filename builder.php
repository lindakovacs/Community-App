<?php

/**
Plugin Name: PDX Website Builder
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


define('BUILDER_DIR', __DIR__ . '/');
define('BUILDER_FILE', __FILE__);


require_once(BUILDER_DIR . 'wp/wp_plugin.php');
$the_pl_plugin = new PL_WP_Plugin();


//---


if(defined(PL_TRANSITIONAL) && PL_TRANSITIONAL) :
	require_once(BUILDER_DIR . 'wp/transitional/compatibility-api.php');
	$the_pl_compatibility_plugin = new PL_Compatibility_Plugin();
endif;
