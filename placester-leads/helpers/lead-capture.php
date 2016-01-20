<?php

PL_Lead_Capture_Helper::init();
class PL_Lead_Capture_Helper {

	private static $forward_address_options_key = 'pls_lead_forward_addresses';

	public static function init () {
		// Register ajax endpoints
		add_action('wp_ajax_set_forwarding_addresses', array(__CLASS__, 'update_lead_forwarding_addresses'));
	}

	public static function update_lead_forwarding_addresses () {
		// Transform comma-separated string of e-mail addresses into an array, then store it in the options table...
		$email_addresses = explode(',', $_POST['email_addresses']);
		PL_Options::set(self::$forward_address_options_key, $email_addresses);
		
		echo json_encode(array("success" => true));
		die();
	}

	public static function get_lead_forwarding_addresses () {
		return PL_Options::get(self::$forward_address_options_key, array());
	}

	// Used as a quick way of always including the right email addresses in outgoing mail based on the users forwarding settings.
	//
	// NOTE: See http://codex.wordpress.org/Function_Reference/wp_mail for more details
	public static function merge_bcc_forwarding_addresses_for_sending ($headers = array()) {
		$email_addresses = self::get_lead_forwarding_addresses();
		
		foreach ($email_addresses as $email) {
			$headers[] = 'Bcc: ' . $email;
		}

		return $headers;
	}


}