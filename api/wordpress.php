<?php 

class PL_WordPress {

	public static function set ($args = array()) {
		$site_id = get_option('pls_site_id');
		if (!$site_id) {
			update_option('pls_site_id', sha1(site_url()));
		}
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_WORDPRESS('set', 'args')));
		$request_url = trailingslashit(PL_Config::PL_API_WORDPRESS('set', 'request', 'url')) . $site_id;
		$response = PL_HTTP::send_request($request_url, $request, PL_Config::PL_API_WORDPRESS('set', 'request', 'type'));
		$response = PL_Validate::attributes($response, PL_Config::PL_API_WORDPRESS('set','returns'));
		if (is_array($response)) {
			return $response;
		}
		return false;
	}

	public static function delete () {
		$site_id = get_option('pls_site_id');
		if (!$site_id) {
			update_option('pls_site_id', sha1(site_url()));
		}
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_WORDPRESS('delete', 'args')));
		$request_url = trailingslashit(PL_Config::PL_API_WORDPRESS('delete', 'request', 'url')) . $site_id;
		$response = PL_HTTP::send_request($request_url, $request, PL_Config::PL_API_WORDPRESS('delete', 'request', 'type'));
		$response = PL_Validate::attributes($response, PL_Config::PL_API_WORDPRESS('delete','returns'));
		if (is_array($response) && isset($response['id']) && $response['id'] == $site_id) {
			return true;
		}
		return false;
	}

}


// v2 ruby core API
global $PL_API_SERVER;
global $PL_API_WORDPRESS;
$PL_API_WORDPRESS = array(
	'set' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/wordpress/filters/",
			'type' => 'POST'
		),
		'args' => array(
			'url' => ''
		),
		'returns' => array()
	),
	'delete' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/wordpress/filters/",
			'type' => 'delete'
		),
		'args' => array(
			'url' => ''
		),
		'returns' => array()
	)
);