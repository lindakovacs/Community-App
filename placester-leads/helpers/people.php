<?php 

PL_People_Helper::init();
class PL_People_Helper {

	public static function get_person ($id) { // BP
		$lead_object = PL_People::details(array('id' => $id));
		if(is_array($lead_object) && $lead_object['id'])
			$lead_object = self::resolve_output($lead_object);
		return $lead_object;
	}

	public static function add_person ($lead_object) { // BP
		$lead_object = self::resolve_input($lead_object);
		self::external_crm_event($lead_object);
		return PL_People::create($lead_object);
	}

	public static function update_person ($lead_object) { // BP
		$lead_object = self::resolve_input($lead_object);
		self::external_crm_event($lead_object);
		return PL_People::update($lead_object);
	}

	// synchronize fields across differently formatted inputs
	public static function resolve_input($lead_object) {
		if(is_array($lead_object['metadata'])) {
			if(isset($lead_object['metadata']['name']) && !isset($lead_object['name'])) $lead_object['name'] = $lead_object['metadata']['name'];
			if(isset($lead_object['metadata']['company']) && !isset($lead_object['company'])) $lead_object['company'] = $lead_object['metadata']['company'];
			if(isset($lead_object['metadata']['email']) && !isset($lead_object['email'])) $lead_object['email'] = $lead_object['metadata']['email'];
			if(isset($lead_object['metadata']['phone']) && !isset($lead_object['phone'])) $lead_object['phone'] = $lead_object['metadata']['phone'];
		}
		else
			$lead_object['metadata'] = array();

		if(!isset($lead_object['metadata']['name']) && $lead_object['name']) $lead_object['metadata']['name'] = $lead_object['name'];
		if(!isset($lead_object['metadata']['company']) && $lead_object['company']) $lead_object['metadata']['company'] = $lead_object['company'];
		if(!isset($lead_object['metadata']['email']) && $lead_object['email']) $lead_object['metadata']['email'] = $lead_object['email'];
		if(!isset($lead_object['metadata']['phone']) && $lead_object['phone']) $lead_object['metadata']['phone'] = $lead_object['phone'];

		return $lead_object;
	}

	// merge fields for output
	public static function resolve_output($lead_object) {
		unset($lead_object['relation']);
		if(isset($lead_object['cur_data'])) {
			if(isset($lead_object['cur_data']['name'])) $lead_object['name'] = $lead_object['cur_data']['name'];
			if(isset($lead_object['cur_data']['company'])) $lead_object['company'] = $lead_object['cur_data']['company'];
			if(isset($lead_object['cur_data']['email'])) $lead_object['email'] = $lead_object['cur_data']['email'];
			if(isset($lead_object['cur_data']['phone'])) $lead_object['phone'] = $lead_object['cur_data']['phone'];
		}

		return $lead_object;
	}

	// hook for third party CRMs
	public static function external_crm_event ($args) {
		if (!empty(PL_Options::get('pl_active_CRM'))) {
			include_once(PL_LEADS_DIR . 'lib/CRM/controller.php');
			PL_CRM_Controller::callCRMLib('createContact', $args);
		}
	}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
	public static function init () {
		add_action('wp_ajax_add_person', array(__CLASS__, 'add_person_ajax'));
		add_action('wp_ajax_update_person', array(__CLASS__, 'update_person_ajax'));
	}
	public static function add_person_ajax () {
		echo json_encode(self::add_person($_POST));
		die();
	}
	public static function update_person_ajax () {
		echo json_encode(PL_Membership::update_site_user($_POST)['crm_response']);
		die();
	}

	public static function person_details () {
		return PL_Membership::get_site_user();
	}

	public static function get_favorite_ids() {
		return PL_Favorite_Listings::get_favorite_properties();
	}
	public static function placester_favorite_link_toggle($args) {
		return PL_Favorite_Listings::placester_favorite_link_toggle($args);
	}
}
