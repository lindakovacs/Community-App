<?php
/**
Plugin Name: Placester Site Customizer
Description: Onboarding, Setup, and Configuration Wizardry for Placester Real Estate Website Builder
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/

define( 'PL_THEME_SKIN_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'config/customizer/theme-skins/' );

include_once('lib/bootup.php'); // has some taxonomy stuff
//include_once('placester-customizer/lib/customizer.php');
//include_once('placester-customizer/helpers/customizer.php');

include_once('config/customizer/onboard_settings.php');
include_once('config/customizer/theme_choices.php');
include_once('models/themes.php');
include_once('lib/posts.php');
include_once('lib/menus.php');


//if ( !defined('HOSTED_PLUGIN_KEY') ) {
//	add_submenu_page( 'placester', '', 'Theme Gallery', 'edit_pages', 'placester_theme_gallery', array('PL_Router','theme_gallery') );
//}

/*
public static function customize_scripts() {
	self::enqueue_script('placester-global', trailingslashit(PL_ADMIN_JS_URL) . 'global.js', array('jquery'));
	self::enqueue_style('placester-global', trailingslashit(PL_CSS_URL) . 'global.css', array('jquery-ui'));

	self::enqueue_script('customizer', trailingslashit(PL_CUSTOMIZER_JS_URL) . 'customizer.js', array('jquery'));
	self::enqueue_style('customizer', trailingslashit(PL_CSS_URL) . 'customizer.css');

	if(PL_Customizer_Helper::is_onboarding()) {
		self::enqueue_script('onboard', trailingslashit(PL_CUSTOMIZER_JS_URL) . 'onboard.js', array('jquery'));
		self::enqueue_style('onboard', trailingslashit(PL_CSS_URL) . 'onboard.css');
	}

	else if($_GET['theme_changed'] == 'true') {
		PL_Router::load_builder_partial('theme-switch.php');
		PL_Router::load_builder_partial('dummy-data-confirmation.php');
		self::enqueue_script('theme-switch', trailingslashit(PL_CUSTOMIZER_JS_URL) . 'theme-switch.js', array('jquery-ui-core', 'jquery-ui-dialog'));
	}
}

public static function theme_gallery () {
	if (isset($_GET['theme_url'])) {
		self::router('install-theme.php', array(), PL_CUSTOMIZER_DIR . 'admin/views/');
	} else {
		self::router('theme-gallery.php', array(), PL_CUSTOMIZER_DIR . 'admin/views/');
	}
}
*/