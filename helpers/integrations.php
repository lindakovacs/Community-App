<?php

PL_Integration_Helper::init();
class PL_Integration_Helper {

	public static function init() {
		add_action('wp_ajax_create_integration', array(__CLASS__, 'create' ) );
		add_action('wp_ajax_new_integration_view', array(__CLASS__, 'new_integration_view') );
		add_action('wp_ajax_idx_prompt_view', array(__CLASS__, 'idx_prompt_view') );
		add_action('wp_ajax_idx_prompt_completed', array(__CLASS__, 'idx_prompt_completed_ajax') );
	}

	public static function create () {
		// TODO: Handle Phone Number if it exists!!!
		if (isset($_POST['phone'])) {
			// Send update to user options with new phone...
			$usr_response = PL_Helper_User::update_user(array('phone' => $_POST['phone']));
			//pls_dump($usr_response);

			unset($_POST['phone']);
		}

		$response = array('result' => false, 'message' => 'There was an error. Please try again.');
		$api_response = PL_Integration::create(wp_kses_data($_POST));
		// pls_dump($api_response, $api_response['id']);
		if (isset($api_response['id'])) {
			$response = array('result' => true, 'message' => 'You\'ve successfully submitted your integration request. This page will update momentarily');
		} elseif (isset($api_response['validations'])) {
			$response = $api_response;
		} elseif (isset($api_response['code']) && $api_response['code'] == '102') {
			$response = array('result' => false, 'message' => 'You are already integrated with an MLS. To enable multiple integrations call sales at (800) 728-8391');
		}
		echo json_encode($response);
		die();
	}

	public static function new_integration_view () {
		$wizard = true;
		include PLACESTER_PLUGIN_DIR . 'admin/views/partials/integration-form.php';
		die();
	}

	public static function idx_prompt_view () {
		global $wpdb, $i_am_a_placester_theme;

		$page = $pagelink = null;
		PL_Option_Helper::set_demo_data_flag(true);

		ob_start(); include PLACESTER_PLUGIN_DIR . 'admin/views/partials/idx-prompt.php'; $html = ob_get_clean();

		if (!$i_am_a_placester_theme) {
			$querystr = "
				SELECT $wpdb->posts.*
				FROM $wpdb->posts, $wpdb->postmeta
				WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
				AND $wpdb->postmeta.meta_key = 'pl_sample'
				AND $wpdb->postmeta.meta_value = 'property-search'
				AND $wpdb->posts.post_status = 'publish'
				AND $wpdb->posts.post_type = 'page'
				AND $wpdb->posts.post_content LIKE '%[search_form]%[search_listings]%'
				ORDER BY $wpdb->posts.post_date DESC
			";
			$pages = $wpdb->get_results($querystr, OBJECT);
			if ($pages) {
				$page = $pages[0];
			}
			else {
				$page_args = array(
					'post_name' => 'property-search',
					'post_title' => 'Real Estate Search',
					'post_content' => "[search_form]\n[search_listings]\n",
					'post_type' => 'page',
					'post_status' => 'publish',
				);
				$ID = wp_insert_post($page_args);
				update_post_meta($ID, 'pl_sample', 'property-search');
				$page = get_post($ID);
			}
		}

		$placestertheme = $i_am_a_placester_theme;
		ob_start(); include PLACESTER_PLUGIN_DIR . 'admin/views/partials/sample-page.php'; $html .= ob_get_clean();

		if ($page)
			$pagelink = get_permalink($page->ID);

		header( "Content-Type: application/json" );
		echo json_encode(array('html' => $html, 'data' => array('search_page' => $pagelink, 'listing_page' => admin_url('admin.php?page=placester_property_add'))));
		die();
	}

	/*
	 * If either through the plugin sign-up dialog flow, or the customizer
	 */
	public static function idx_prompt_completed ($mark_completed = false) {
		$key = 'idx_prompt_completed';

		// If $mark_complete is true, try to set the option and store the outcome -- otherwise,
		// check if it currently exists and retrieve its value...
		$exists = ( $mark_completed ? PL_Options::set($key, true) : $exists = PL_Options::get($key) );

		return $exists;
	}

	public static function idx_prompt_completed_ajax () {
		self::idx_prompt_completed(isset($_POST['mark_completed']));
		die();
	}

	public static function integration_pending () {
		$integration = PL_Integration::get();
		return !empty($integration[0]['id']);
	}

	public static function get () {
		$response = array();
		$integration = PL_Integration::get();
		$whoami = PL_Helper_User::whoami();
		$listings = PL_Listing::get(array('limit' => 1));
		$locations = PL_Listing::locations();
		return array('integration_status' => array('integration' => $integration, 'whoami' => $whoami, 'listings' => $listings, 'locations' => $locations));
	}

	public static function mls_list () {
		$mls_list = PL_Integration::mls_list();
		return $mls_list;
	}

//end of class
}