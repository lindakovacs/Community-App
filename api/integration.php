<?php

class PL_Integration {

	public static function get($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_INTEGRATION('get', 'args')));
		$response = PL_HTTP::send_request(trailingslashit(PL_Config::PL_API_INTEGRATION('get', 'request', 'url')), $request, PL_Config::PL_API_INTEGRATION('get', 'request', 'type'), false);

		return $response;
	}

	public static function create($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_INTEGRATION('create', 'args') ) );
		$response = PL_HTTP::send_request(PL_Config::PL_API_INTEGRATION('create', 'request', 'url'), $request, PL_Config::PL_API_INTEGRATION('create', 'request', 'type'));

		return $response;
	}

	public static function mls_list($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_INTEGRATION('mls_list', 'args')));
		$response = PL_HTTP::send_request(PL_Config::PL_API_INTEGRATION('mls_list', 'request', 'url'), $request, PL_Config::PL_API_INTEGRATION('mls_list', 'request', 'type'));

		ksort($response);
		return $response;
	}
}


// v2 ruby core API
global $PL_API_SERVER;
global $PL_API_INTEGRATION;

$PL_API_INTEGRATION = array(
	'get' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/integration/requests",
			'type' => 'GET'
		),
		'args' => array(),
		'returns' => array(
			'id' => false,
			'mls_name' => false,
			'url' => false,
			'updated_at' => false,
			'created_at' => false,
			'completed_at' => false,
			'status' => false
		)
	),
	'create' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/integration/requests",
			'type' => 'POST'
		),
		'args' => array(
			'mls_id' => array('type' => 'text', 'group' => 'basic', 'label' => 'MLS Name'),
			'office_name' => array('type' => 'text', 'group' => 'basic', 'label' => 'Office Name'),
			'feed_agent_id' => array('type' => 'text', 'group' => 'basic', 'label' => 'Agent ID')
		),
		'returns' => array(
			'id' => false
		)
	),
	'mls_list' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/integration/requests/mls",
			'type' => 'GET'
		),
		'args' => array(),
		'returns' => array()
	)
);
