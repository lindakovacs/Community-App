<?php


require_once(PLACESTER_PLUGIN_DIR . 'libnew/listings.php');


class PLX_Legacy_Listings extends PLX_Listings {
	protected function _create($args = array()) {
		$request = PLX_Legacy_Interface::map_listing_request($args);
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings', $request, 'POST');

		return $response;
	}

	protected function _read($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'GET');

		return PLX_Legacy_Interface::map_listing_response($response);
	}

	protected function _update($args = array()) {
		$request = PLX_Legacy_Interface::map_listing_request($args);
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'PUT');

		return $response;
	}

	protected function _delete($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'DELETE');

		return $response;
	}

	protected function _image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request_multipart($PL_API_SERVER . '/v2/listings/media/temp/image', $request, $file_name, $file_mime_type, $file_tmpname);

		return $response;
	}
}


class PLX_Legacy_Search extends PLX_Search {
	protected function _listings ($args = array()) {
		$request = PLX_Legacy_Interface::map_search_request($args);
		$request = array_merge(array("api_key" => self::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2.1/listings', $request, 'GET');

		return PLX_Legacy_Interface::map_search_response($response);
	}

	protected function _locations ($args = array()) {
		$request = array_merge(array("api_key" => self::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/locations', $request, 'GET');

		return $response;
	}

	protected function _aggregates ($args = array()) {
		$request = PLX_Legacy_Interface::map_search_request($args);
		$request = array_merge(array("api_key" => self::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/aggregate', $request, 'GET');

		return PLX_Legacy_Interface::map_aggregate_response($response);
	}


	private static function api_key () {
		$api_key = PL_Option_Helper::api_key();

		$admin_listing_ajax = ( defined('DOING_AJAX') && isset($_POST['action']) && ($_POST['action'] == 'datatable_ajax') );
		if ( PL_Option_Helper::get_demo_data_flag() && defined('DEMO_API_KEY') && !(is_admin() && $admin_listing_ajax) ) {
			$api_key = DEMO_API_KEY;
		}

		return $api_key;
	}
}
