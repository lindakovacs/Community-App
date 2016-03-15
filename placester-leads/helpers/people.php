<?php 

PL_People_Helper::init();

class PL_People_Helper {

	const USER_META_KEY = 'placester_api_id';

	public static function init () {
		add_action('wp_ajax_add_person', array(__CLASS__, 'add_person_ajax'));
		add_action('wp_ajax_update_person', array(__CLASS__, 'update_person_ajax'));
	}

	public static function add_person ($args = array()) {
		// Try to push lead to CRM (if one is linked/active)...
		self::add_person_to_CRM($_POST);

		return PL_People::create($args);
	}	

	public static function add_person_ajax () {
		$api_response = self::add_person($_POST);
		echo json_encode($api_response);
		die();
	}

	public static function add_person_to_CRM ($args = array()) {
		// Check to see if site is actively linked to a CRM...
		$activeCRMKey = 'pl_active_CRM';
		$crm_id = PL_Options::get($activeCRMKey);
		
		if (!empty($crm_id)) {
			// Load CRM libs...
			$path_to_CRM = trailingslashit(PL_LEADS_DIR) . 'lib/CRM/controller.php';
			include_once($path_to_CRM);

			// Call necessary lib to add the contact to the active/registered CRM...
			if (class_exists('PL_CRM_Controller')) {
				PL_CRM_Controller::callCRMLib('createContact', $args);
			}
		}
	}

	public static function update_person ($person_details) {
		$placester_person = self::person_details();
		return PL_People::update(array_merge(array('id' => $placester_person['id']), $person_details));
	}

	public static function update_person_ajax () {
        $person_details = $_POST;
        $result = self::update_person($person_details);

        echo json_encode($result);
        die();
    }

	// Fetch a site user's details based on his/her unique Placester ID (managed by Rails, stored in WP's usermeta table)
	public static function person_details () {
		$details = array();
		$wp_user = wp_get_current_user();

		if (!empty($wp_user->ID)) {
			$placester_id = get_user_meta($wp_user->ID, self::USER_META_KEY);
		
			if (is_array($placester_id)) { 
				$placester_id = implode($placester_id, ''); 
			}
			
			if (!empty($placester_id)) {
				$details = PL_People::details(array('id' => $placester_id));
			}
		}
		
		// Just in case...
		if (!is_array($details)) {
			$details = array();
		}

		return $details;
	}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
	public static function update_person_details($args) {
		return self::update_person($args);
	}
	public static function get_favorite_ids() {
		return PL_Favorite_Listings::get_favorite_properties();
	}
	public static function placester_favorite_link_toggle($args) {
		return PL_Favorite_Listings::placester_favorite_link_toggle($args);
	}
}
