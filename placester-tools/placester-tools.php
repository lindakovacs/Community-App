<?php
/**
Plugin Name: Placester Agent Tools and Third Party Add-Ons
Description: Agent Marketing Tools and Third Party Site Add-Ons for Real Estate Website Builder
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/


include_once('lib/dragonfly-resize.php');

if ((!is_admin() && file_exists(WP_PLUGIN_DIR.'/wordpress-seo/inc/class-sitemaps.php') && strpos($_SERVER["REQUEST_URI"],'sitemap')!==false)
	|| is_admin()) {
	include_once('lib/sitemaps.php'); // refers to taxonomies
}

//include_once('placester-tools/lib/social_networks.php');

include_once('config/third-party/google-places.php');
include_once('models/google-places.php');
include_once('helpers/google-places.php');

include_once('models/walkscore.php');

include_once('models/education-com.php');
include_once('helpers/education-com.php');
