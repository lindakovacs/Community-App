<?php
/**
Plugin Name: Placester Community Pages
Description: Community Pages and Polygon Tools for Placester Real Estate Website Builder
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/

// this is either deprecated or developmental -- let's see if anyone squawks when it goes away
// include_once('lib/community-pages.php');

include_once('helpers/taxonomy.php');

include_once('lib/tax-meta-class/tax-meta-class.php');
include_once('lib/convex-hull.php');


// the placester_areas page needs to send a browser redirect after content is generated
add_action('admin_title', 'placester_areas_defer');
function placester_areas_defer($title) {
	if($_REQUEST['page'] == 'placester_areas') ob_start();
	return $title;
}


PL_Admin_Page::register_script('google-maps', 'http://maps.googleapis.com/maps/api/js');
PL_Admin_Page::register_script('text-overlay', PL_AREAS_JS_URL . 'text-overlay.js', array('google-maps'));

$pl_admin_page = new PL_Admin_Page('placester', 1100, 'placester_areas', 'Locations', 'Area Pages', PL_AREAS_DIR . 'admin/views/property-taxonomies.php');

$pl_admin_page = new PL_Admin_Page('placester_areas', 1150, 'placester_polygons', 'Custom Areas', 'Custom Areas', PL_AREAS_DIR . 'admin/views/polygons.php');
$pl_admin_page->require_script('colorpick', PL_AREAS_JS_URL . 'colorpicker/colorpicker.js');
$pl_admin_page->require_style('colorpick', PL_AREAS_JS_URL . 'colorpicker/colorpicker.css');
$pl_admin_page->require_script('polygons', PL_AREAS_JS_URL . 'polygon.js', array('jquery-datatables', 'google-maps', 'text-overlay', 'colorpick'));
$pl_admin_page->require_style('polygons', PL_AREAS_CSS_URL . 'polygon.css', array('settings-all', 'colorpick'));
