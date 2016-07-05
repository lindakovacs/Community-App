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


	global $placester_gallery_themes;
	$placester_gallery_themes = array(
		"columbus" => array(
			"display_name" => "Columbus",
			"download_link" => "http://plcstr.com/14Ka7ic",
			"current_version" => '2.9',
			"screenshot" => 'http://demo.placester.net/wp-content/themes/columbus/screenshot.png',
			"info_link" => "https://placester.com/wordpress-themes/columbus"
		),
		"manchester" => array(
			"display_name" => "Manchester",
			"download_link" => "http://plcstr.com/1237uEg",
			"current_version" => '2.9',
			"screenshot" => 'http://demo.placester.net/wp-content/themes/manchester/screenshot.png',
			"info_link" => "https://placester.com/wordpress-themes/manchester"
		),
		"tampa" => array(
			"display_name" => "Tampa",
			"download_link" => "http://plcstr.com/16Jco9m",
			"current_version" => '2.9',
			"screenshot" => 'http://demo.placester.net/wp-content/themes/tampa/screenshot.png',
			"info_link" => "https://placester.com/wordpress-themes/tampa"
		),
		"ventura" => array(
			"display_name" => "Ventura",
			"download_link" => "http://plcstr.com/17LXH4m",
			"current_version" => '2.9',
			"screenshot" => 'http://demo.placester.net/wp-content/themes/ventura/screenshot.png',
			"info_link" => "https://placester.com/wordpress-themes/ventura"
		)
	);


	// trap the WP theme version check to add Placester info
	add_filter('pre_set_site_transient_update_themes', 'placester_gallery_update_themes', 10, 2);
	function placester_gallery_update_themes($value, $transient)
	{
		global $placester_gallery_themes;
		if(is_array($value->checked)) foreach($value->checked as $theme => $installed_version) {
			if(in_array($theme, array_keys($placester_gallery_themes))) {

				// verify we've got a Placester theme -- don't just rely on the name
				if(($info = wp_get_theme($theme)) && preg_match('/.*[Pp]lacester.*/', $info->author)) {

					$meta = $placester_gallery_themes[$theme];
					if ($installed_version < $meta['current_version']) {
						if (!isset($value->response[$theme]) || $value->response[$theme]['new_version'] < $meta['current_version']) {
							$value->response[$theme] = array(
								'theme' => $theme,
								'new_version' => $meta['current_version'],
								'url' => $meta['info_link'],
								'package' => $meta['download_link']
							);
						}
					}
				}
			}
		}

		return $value;
	}
}
