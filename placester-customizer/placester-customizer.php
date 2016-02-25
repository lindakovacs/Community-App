<?php
/**
Plugin Name: Placester Site Customizer
Description: Onboarding, Setup, and Configuration Wizardry for Placester Real Estate Website Builder
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/


if(!defined('HOSTED_PLUGIN_KEY')) {
	$pl_admin_page = new PL_Admin_Page('placester', 9500, 'placester_gallery', 'Theme Gallery', 'Theme Gallery', PLACESTER_PLUGIN_DIR . 'placester-customizer/admin/theme-gallery.php');
	$pl_admin_page->require_style('placester-gallery', PLACESTER_PLUGIN_URL . 'placester-customizer/admin/theme-gallery.css');
}
