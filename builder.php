<?php

/**
Plugin Name: PDX Website Builder
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


define('BUILDER', __DIR__ . '/');

require_once(BUILDER . 'cpt/wp_site.php');
require_once(BUILDER . 'shortcodes/api_shortcodes.php');
require_once(BUILDER . 'shortcodes/www_shortcodes.php');

$pl_wp_site = new PL_WP_Site();
register_activation_hook(__FILE__, array('PL_WP_Site', 'wp_activate_plugin'));
register_deactivation_hook(__FILE__, array('PL_WP_Site', 'wp_deactivate_plugin'));


//---


require_once(BUILDER . 'transitional/compatibility-api.php');

if(defined(PL_TRANSITIONAL) && PL_TRANSITIONAL) :
	$pl_compatibility_plugin = new PL_Compatibility_Plugin();
endif;
