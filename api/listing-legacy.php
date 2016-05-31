<?php 


require_once('listing-interface.php');


class PLX_Legacy_Listing implements PLX_Listing_Interface {

	/* A wrapper for PL_Option_Helper::api_key() for class functions that need to be aware of demo data */
	private static function api_key () {
		$api_key = PL_Option_Helper::api_key();

		$admin_listing_ajax = ( defined('DOING_AJAX') && isset($_POST['action']) && ($_POST['action'] == 'datatable_ajax') );
		if ( PL_Option_Helper::get_demo_data_flag() && defined('DEMO_API_KEY') && !(is_admin() && $admin_listing_ajax) ) {
			$api_key = DEMO_API_KEY;
		}

		return $api_key;
	}

	private static function map_request($request) {
		$attributes = PLX_Attributes::get_attributes();
		$mapped = array();

		foreach($request as $name => $value)
			if(isset($attributes[$name]))
				if($name == 'listing_type')
					$mapped['compound_type'] = $value;
				else if($name == 'property_type')
					$mapped['metadata']['prop_type'] = $value;
				else if($name == 'status')
					$mapped['metadata']['status'] = $value;
				else if($name == 'latitude')
					$mapped['location']['coord_latitude'] = $value;
				else if($name == 'longitude')
					$mapped['location']['coord_longitude'] = $value;
				else {
					$attribute = $attributes[$name];
					if($attribute['group'] == 'Location')
						$mapped['location'][$name] = $value;
					else if(!in_array($attribute['group'], array('Listing', 'Attribution')))
						$mapped['metadata'][$name] = $value;
				}

		$mapped['images'] = $request['images'];
		return $mapped;
	}

	private static function map_response($response) {
		$mapped = array();
		$mapped['id'] = $response['id'];

		$mapped['listing_type'] = $response['compound_type'];
		$mapped['property_type'] = $response['cur_data']['prop_type'];
		$mapped['zoning_type'] = $response['zoning_types'][0];
		$mapped['purchase_type'] = $response['purchase_types'][0];

		$mapped['created_at'] = $response['created_at'];
		$mapped['updated_at'] = $response['updated_at'];
		$mapped['status'] = $response['cur_data']['status'];
		$mapped['list_date'] = $response['cur_data']['lst_dte'];
		$mapped['days_on'] = $response['cur_data']['dom'];

		$mapped['latitude'] = $response['location']['coords'][0];
		$mapped['longitude'] = $response['location']['coords'][1];

		unset($response['cur_data']['prop_type']);
		unset($response['cur_data']['status']);
		unset($response['cur_data']['lst_dte']);
		unset($response['cur_data']['dom']);
		unset($response['location']['coords']);

		$mapped = array_merge($mapped,
			(array) $response['location'],
			(array) $response['cur_data'],
			(array) $response['uncur_data'],
			(array) $response['rets']);

		$mapped['images'] = $response['images'];
		return $mapped;
	}

	private static function map_search_terms($args) { return $args; }

	public static function get ($args = array()) {
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get', 'args')));

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2.1/listings', $request, PL_Config::PL_API_LISTINGS('get', 'request', 'type'));
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

	public static function locations ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('get.locations');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));

		return PL_Validate::attributes(PL_HTTP::send_request($config['request']['url'], $request), $config['returns']);
	}

	public static function aggregates ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('get.aggregate');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));

		return PL_Validate::attributes(PL_HTTP::send_request($config['request']['url'], $request), $config['returns']);
	}

	public static function create ($args = array()) {
		global $PL_API_SERVER;
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), self::map_request($args));
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings', $request, 'POST');

		return $response;
	}

	public static function read ($args = array()) {
		global $PL_API_SERVER;
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), self::map_request($args));
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'GET');

		return self::map_response($response);
	}

	public static function update ($args = array()) {
		global $PL_API_SERVER;
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), self::map_request($args));
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'PUT');

		return $response;
	}

	public static function delete ($args = array()) {
		global $PL_API_SERVER;
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), self::map_request($args));
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'DELETE');

		// $config = PL_Config::PL_API_LISTINGS('delete');
		// $response = PL_Validate::attributes($response, $config['returns']);

		return $response;
	}

	public static function temp_image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$config = PL_Config::PL_API_LISTINGS('temp_image');
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, $config['args']));
		$response = PL_HTTP::send_request_multipart($config['request']['url'], $request, $file_name, $file_mime_type, $file_tmpname);

		return $response;
	}
}
