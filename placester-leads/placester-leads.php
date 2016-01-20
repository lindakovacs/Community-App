<?php
/**
Plugin Name: Placester Lead Management
Description: Contact, Lead Capture, and User Accounts for Placester Real Estate Website Builder
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/

if (defined('DOING_AJAX') && isset($_POST['action']) && $_POST['action'] == 'crm_ajax_controller') {
	include_once('lib/CRM/controller.php');
	return;
}

include_once('config/api/people.php');
include_once('models/people.php');
include_once('helpers/people.php');

include_once('lib/membership.php');
include_once('helpers/membership.php');
include_once('helpers/member-listings.php');
include_once('helpers/member-search.php');

include_once('helpers/lead-capture.php');


$pl_admin_page = new PL_Admin_Page('placester', 1200, 'placester_leads', 'Leads', 'Email Addresses', PL_LEADS_DIR . 'admin/views/lead-capture.php');
$pl_admin_page->require_script('lead-capture', PL_LEADS_JS_URL . 'general.js', array('jquery-ui-core', 'jquery-ui-dialog'));
$pl_admin_page->require_style('lead-capture', PL_LEADS_CSS_URL . 'general.css', array('settings-all'));

$pl_admin_page = new PL_Admin_Page('placester_leads', 1220, 'placester_crm', 'CRM Integration', 'CRM Integration', PL_LEADS_DIR . 'admin/views/crm.php');
$pl_admin_page->require_script('placester-crm', PL_LEADS_JS_URL . 'crm.js', array('jquery-datatables'));
$pl_admin_page->require_style('placester-crm', PL_LEADS_CSS_URL . 'crm.css', array('settings-all'));

$pl_admin_page = new PL_Admin_Page('placester_leads', 1240, 'placester_client', 'Client Message', 'Client Message', PL_LEADS_DIR . 'admin/views/client.php');
$pl_admin_page->require_script('placester-client', PL_LEADS_JS_URL . 'client.js');
$pl_admin_page->require_style('placester-client', PL_LEADS_CSS_URL . 'client.css', array('settings-all'));


add_action('wp_enqueue_scripts', 'placester_membership_enqueue');
function placester_membership_enqueue() {
	wp_enqueue_script('placester-membership', PL_LEADS_URL . 'membership.js', array('jquery'), filemtime(PL_LEADS_DIR . 'membership.js'), true);
}
