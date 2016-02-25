<?php

class PL_User {

	// Memoize the whoami response for a single request's execution to greatly cut down on the
	// number of calls to Memcache (or whatever caching platform is used).
	private static $whoami_memo = null;

	public static function whoami($args = array(), $custom_api_key = null) {
		$api_key = $custom_api_key;

		// If no custom API key was passed in, use the one the plugin is currently activated with...
		if ( empty($api_key) ) {
			// Check for the memoized response since no custom API key was passed...
			if ( !empty(self::$whoami_memo) ) {
				// error_log("Using memoized whoami!!!");
				return self::$whoami_memo;
			}
			// Otherwise, proceed with the whoami call using the active API key...
			$api_key = PL_Option_Helper::api_key();
		}
		// error_log("API Key = " . $api_key);

		$request = array_merge(array("api_key" => $api_key), PL_Validate::request($args, PL_Config::PL_API_USERS('whoami', 'args')));
		$response = PL_HTTP::send_request(trailingslashit(PL_Config::PL_API_USERS('whoami', 'request', 'url')), $request, PL_Config::PL_API_USERS('whoami', 'request', 'type'), true);
	    if ( $response ) {
			$response = PL_Validate::attributes($response, PL_Config::PL_API_USERS('whoami', 'returns'));
		}

		// Memoize response if NOT using custom api key...
		if ( empty($custom_api_key) ) {
			// error_log("Memoizing whoami...");
			self::$whoami_memo = $response;
		}

		return $response;
	}

	public static function create($args = array()) {
		$request = PL_Validate::request($args, PL_Config::PL_API_USERS('setup', 'args') );
		$request['source'] = 'wordpress';
		$response = PL_HTTP::send_request(PL_Config::PL_API_USERS('setup', 'request', 'url'), $request, PL_Config::PL_API_USERS('setup', 'request', 'type'));
		return $response;
	}

	public static function update($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_USERS('update', 'args')));
		// pls_dump($args, $request);
		$response = PL_HTTP::send_request(PL_Config::PL_API_USERS('update', 'request', 'url'), $request, PL_Config::PL_API_USERS('update', 'request', 'type'), false);
		$response = PL_Validate::attributes($response, PL_Config::PL_API_USERS('update', 'returns'));
		return $response;
	}

	public static function subscriptions($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_USERS('subscriptions', 'args')));
		$response = PL_HTTP::send_request(trailingslashit(PL_Config::PL_API_USERS('subscriptions', 'request', 'url')), $request, PL_Config::PL_API_USERS('subscriptions', 'request', 'type'), false);
		$response = PL_Validate::attributes($response, PL_Config::PL_API_USERS('subscriptions', 'returns'));
		return $response;
	}

	public static function start_subscription_trial($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_USERS('start_subscriptions', 'args')));
		$response = PL_HTTP::send_request(trailingslashit(PL_Config::PL_API_USERS('subscriptions', 'request', 'url')), $request, PL_Config::PL_API_USERS('start_subscriptions', 'request', 'type'), false);
		$response = PL_Validate::attributes($response, PL_Config::PL_API_USERS('start_subscriptions', 'returns'));
		return $response;
	}

}


// v2 ruby core API
global $PL_API_SERVER;
global $PL_API_USERS;

$PL_API_USERS = array(
	'whoami' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/organizations/whoami",
			'type' => 'GET'
		),
		'args' => array(),
		'returns' => array(
			'id' => false,
			'name' => false,
			'phone' => false,
			'website' => false,
			'is_verified' => false,
			'api_key_id' => false,
			'location' => array(
				'address' => false,
				'locality' => false,
				'region' => false,
				'postal' => false,
				'neighborhood' => false,
				'country' => false,
				'latitude' => false,
				'longitude' => false
			),
			'provider' => array(
				'id' => false,
				'name' => false,
				'website' => false,
				'first_logo' => false,
				'second_logo' => false,
				'disclaimer' => false,
				'disclaimer_on' => array(),
				'last_import' => false
			),
			'providers' => false,
			'user' => array(
				'id' => false,
				'first_name' => false,
				'last_name' => false,
				'email' => false,
				'phone' => false,
				'website' => false,
			),
			'disabled_publishers' => array()
		)
	),
	'setup' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/users/setup",
			'type' => 'POST'
		),
		'args' => array(
			'email' => array(
				'label' => 'Confirm Email Address',
				'type' => 'text',
				'group' => 'required'
			),
			'first_name'  => array('label' => 'First Name','type' => 'text'),
			'last_name'  => array('label' => 'Last Name','type' => 'text'),
			'phone'  => array('label' => 'Phone Number','type' => 'text'),
			'website' => array(),
			'about' => array(),
			'slogan' => array(),
			'has_group' => array(),
			'source' => 'wordpress',
			'password'  => array('label' => 'Password','type' => 'password'),
			'password_confirmation'  => array('label' => 'Confirm Password','type' => 'confirm_password'),
			'location' => array(
				'address' => array('label' => 'Street','type' => 'text'),
				'postal' => array('label' => 'Zip','type' => 'text'),
				'region'  => array('label' => 'State','type' => 'text'),
				'locality'  => array('label' => 'City','type' => 'text'),
				'country'  => array('label' => 'Country','type' => 'text')
			)
		),
		'returns' => array(
		)
	),
	'update' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/users",
			'type' => 'PUT'
		),
		'args' => array(
			'email' => array(
				'label' => 'Email Address',
				'type' => 'text',
				'group' => 'required'
			),
			'first_name'  => array('label' => 'First Name','type' => 'text'),
			'last_name'  => array('label' => 'Last Name','type' => 'text'),
			'phone'  => array('label' => 'Phone Number','type' => 'text'),
			'website' => array(),
			'about' => array(),
			'slogan' => array(),
			'has_group' => array(),
			'source' => 'wordpress',
			'password'  => array('label' => 'Password','type' => 'password'),
			'password_confirmation'  => array('label' => 'Confirm Password','type' => 'confirm_password'),
			'location' => array(
				'address' => array('label' => 'Street','type' => 'text'),
				'postal' => array('label' => 'Zip','type' => 'text'),
				'region'  => array('label' => 'State','type' => 'text'),
				'locality'  => array('label' => 'City','type' => 'text'),
				'country'  => array('label' => 'Country','type' => 'text')
			)
		),
		'returns' => array(
			'id' => false
		)
	),
	'subscriptions' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/subscriptions",
			'type' => 'GET'
		),
		'args' => array(),
		'returns' => array(
			'next_charge_at' => false,
			'price' => false,
			'plan' => false,
			'eligible_for_trial' => false
		)
	),
	'start_subscriptions' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/subscriptions",
			'type' => 'POST'
		),
		'args' => array(
			'source' => false
		),
		'returns' => array()
	)
);