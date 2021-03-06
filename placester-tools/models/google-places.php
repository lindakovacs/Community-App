<?php

class PL_Google_Places {

	public static function get ($args = array()) {
		$places_api_key = PL_Option_Helper::get_google_places_key();
		if ($places_api_key) {
			$request = PL_Validate::request(array_merge(array('key' => $places_api_key),$args), PL_Config::PL_TP_GOOGLE_PLACES('get', 'args'));
			$response = PL_HTTP::send_request(PL_Config::PL_TP_GOOGLE_PLACES('get', 'request', 'url'), $request, PL_Config::PL_TP_GOOGLE_PLACES('get', 'request', 'type'), true, false, true, false);
			if (is_array($response) && isset($response['status']) && $response['status'] == 'OK' ) {
				return $response['results'];
			}
		}
		return array();
	}
}