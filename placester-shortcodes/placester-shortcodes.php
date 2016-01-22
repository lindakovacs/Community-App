<?php
/**
Plugin Name: Placester Shortcode v1
Description: Property Search Shortcodes v1 for Placester Real Estate Website Builder
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/

require_once('lib/shortcode-cpt.php');
require_once('lib/component_entities.php');
require_once('lib/shortcodes.php');
require_once('lib/listing-customizer.php');

// for selecting featured listings
require_once('lib/shortcode-listing-chooser.php');


// the shortcode admin pages may need to send a browser redirect after content is generated
add_action('admin_title', 'placester_shortcodes_defer');
function placester_shortcodes_defer($title) {
	if(strpos($_REQUEST['page'], 'placester_shortcode') === 0) ob_start();
	return $title;
}


PL_Admin_Page::register_script('codemirror', PL_SHORTCODES_JS_URL . 'codemirror/codemirror.js', null, true);
PL_Admin_Page::register_script('codemirror-foldcode', PL_SHORTCODES_JS_URL . 'codemirror/addon/fold/foldcode.js', array('codemirror'), null, true);
PL_Admin_Page::register_script('codemirror-foldgutter', PL_SHORTCODES_JS_URL . 'codemirror/addon/fold/foldgutter.js', array('codemirror-foldcode'), null, true);
PL_Admin_Page::register_script('codemirror-brace-fold', PL_SHORTCODES_JS_URL . 'codemirror/addon/fold/brace-fold.js', array('codemirror-foldgutter'), null, true);
PL_Admin_Page::register_script('codemirror-xml-fold', PL_SHORTCODES_JS_URL . 'codemirror/addon/fold/xml-fold.js', array('codemirror-foldgutter'), null, true);
PL_Admin_Page::register_script('codemirror-xml', PL_SHORTCODES_JS_URL . 'codemirror/mode/xml/xml.js', array('codemirror-xml-fold'), null, true);
PL_Admin_Page::register_script('codemirror-css', PL_SHORTCODES_JS_URL . 'codemirror/mode/css/css.js', array('codemirror-brace-fold'), null, true);
PL_Admin_Page::register_style('codemirror', PL_SHORTCODES_JS_URL . 'codemirror/codemirror.css');


$pl_admin_page = new PL_Admin_Page('placester', 2100, 'placester_shortcodes', 'Shortcodes', 'Custom Shortcodes', PL_SHORTCODES_DIR . 'admin/views/shortcodes.php');

$pl_admin_page = new PL_Admin_Page('placester_shortcodes', 2105, 'placester_shortcode_edit', 'Create Custom Shortcode', 'Create Custom Shortcode', PL_SHORTCODES_DIR . 'admin/views/shortcode-edit.php');
$pl_admin_page->require_script('listing-chooser', PL_SHORTCODES_JS_URL . 'shortcode-listing-chooser.js', array('jquery', 'jquery-ui-dialog', 'jquery-datatables'), null, true);
$pl_admin_page->require_style('listing-chooser', PL_SHORTCODES_CSS_URL . 'shortcode-listing-chooser.css', array());
$pl_admin_page->require_script('shortcode-edit', PL_SHORTCODES_JS_URL . 'all.js', array('jquery-ui-datepicker', 'listing-chooser'), null, true);
$pl_admin_page->require_style('shortcode-edit', PL_SHORTCODES_CSS_URL . 'all.css', array('listing-chooser'));

$pl_admin_page = new PL_Admin_Page('placester_shortcodes', 2120, 'placester_shortcode_templates', 'Shortcode Templates', 'Shortcode Templates', PL_SHORTCODES_DIR . 'admin/views/templates.php');

$pl_admin_page = new PL_Admin_Page('placester_shortcodes', 2125, 'placester_shortcode_template_edit', 'Create Shortcode Template', 'Create Shortcode Template', PL_SHORTCODES_DIR . 'admin/views/template-edit.php');
$pl_admin_page->require_script('shortcode-edit', PL_SHORTCODES_JS_URL . 'all.js', array('jquery-ui-dialog', 'codemirror-xml', 'codemirror-css'), null, true);
$pl_admin_page->require_style('shortcode-edit', PL_SHORTCODES_CSS_URL . 'all.css', array('codemirror'));

$pl_admin_page = new PL_Admin_Page('placester_shortcodes', 2150, 'placester_shortcode_details', 'Listing Templates', 'Listing Templates', PL_SHORTCODES_DIR . 'admin/views/details.php');

$pl_admin_page = new PL_Admin_Page('placester_shortcodes', 2155, 'placester_shortcode_detail_edit', 'Create Listing Template', 'Create Listing Template', PL_SHORTCODES_DIR . 'admin/views/detail-edit.php');
$pl_admin_page->require_script('listing-customizer', PL_SHORTCODES_JS_URL . 'listing-customizer.js', array('jquery-ui-dialog', 'codemirror-xml', 'codemirror-css'), null, true);
$pl_admin_page->require_style('shortcode-edit', PL_SHORTCODES_CSS_URL . 'all.css', array('codemirror'));
