<?php 

class PL_Listing {
	
	/* A wrapper for PL_Option_Helper::api_key() for class functions that need to be aware of demo data */
	private static function api_key () {
		// The default value to use--the user's own key...
		$api_key = PL_Option_Helper::api_key();
	
		// Check to see whether or not the request being processed is a listing AJAX call from the front-end
		// (i.e., if it is NOT the single action we know is used by the "listings" admin page...)
		$admin_listing_ajax = ( defined('DOING_AJAX') && isset($_POST['action']) && ($_POST['action'] == 'datatable_ajax') );
		
		// If the user chose to use demo data, make requests using API key that corresponds to the demo listing account
		// NOTE: Check for any calls from admin pages, as they should NEVER use demo data...
		if ( PL_Option_Helper::get_demo_data_flag() && defined('DEMO_API_KEY') && !(is_admin() && $admin_listing_ajax) ) {
			$api_key = DEMO_API_KEY;
		}

		return $api_key;
	}

	public static function get ($args = array()) {
		// merge incoming args with preset options, basically api key at this point.
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get', 'args')));
//		if ( defined('HOSTED_PLUGIN_KEY') ) {
//			$request['hosted_key'] = HOSTED_PLUGIN_KEY;
//		}

		// sent request, use details from config.
		$response = PL_HTTP::send_request(PL_Config::PL_API_LISTINGS('get', 'request', 'url'), $request, PL_Config::PL_API_LISTINGS('get', 'request', 'type'));
		// validate response. 
		if (isset($response) && isset($response['listings']) && is_array($response['listings'])) {
			foreach ($response['listings'] as $key => $listing) {
				$response['listings'][$key] = PL_Validate::attributes($listing, PL_Config::PL_API_LISTINGS('get','returns'));
			}
		} 
		else {
			$response = PL_Validate::attributes($response, array('listings' => array(), 'total' => 0));
		}
		
		return $response;
	}

	public static function create ($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('create', 'args')));
		$response = PL_HTTP::send_request(PL_Config::PL_API_LISTINGS('create', 'request', 'url'), $request, PL_Config::PL_API_LISTINGS('create', 'request', 'type'));
		
		return $response;
	}

	public static function update ($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('create', 'args')));
		$update_url = trailingslashit( PL_Config::PL_API_LISTINGS('update', 'request', 'url') ) . $args['id'];
		$response = PL_HTTP::send_request($update_url, $request, PL_Config::PL_API_LISTINGS('update', 'request', 'type'));
		
		return $response;	
	}

	public static function delete ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('delete');
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, $config['args']));
		$delete_url = trailingslashit($config['request']['url']) . $request['id'];
		$response = PL_HTTP::send_request($delete_url, $request, $config['request']['type']);
		$response = PL_Validate::attributes($response, $config['returns']);
		
		return $response;	
	}

	public static function temp_image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$config = PL_Config::PL_API_LISTINGS('temp_image');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));
		$response = PL_HTTP::send_request_multipart($config['request']['url'], $request, $file_name, $file_mime_type, $file_tmpname);
		
		return $response;	
	}

	public static function locations ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('get.locations');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));
//		if (defined('HOSTED_PLUGIN_KEY')) {
//			$request['hosted_key'] = HOSTED_PLUGIN_KEY;
//		}

		return PL_Validate::attributes(PL_HTTP::send_request($config['request']['url'], $request), $config['returns']);
	}
	
	public static function aggregates ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('get.aggregate');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));
//		if (defined('HOSTED_PLUGIN_KEY')) {
//			$request['hosted_key'] = HOSTED_PLUGIN_KEY;
//		}

		return PL_Validate::attributes(PL_HTTP::send_request($config['request']['url'], $request), $config['returns']);
	}

// end of class
}