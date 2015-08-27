<?php


class PL_Compatibility_Plugin {
	static $singleton;

	protected $api_key;
	protected $connection;

	protected $global_filter;


	public function __construct() {
		if(!self::$singleton)
			self::$singleton = $this;
	}

	public function get_api_key() {
		if(!isset($this->api_key))
			$this->api_key = get_option('placester_api_key', '');

		return $this->api_key;
	}

	public function get_connection() {
		if(!isset($this->connection)) {
			$this->connection = $this->get_api_key() ? new PL_API_Connection($this->get_api_key()) : null;
			$this->connection->enable_attribute(array_keys($this->connection->get_standard_attributes()));
			$this->connection->enable_attribute(array_keys($this->connection->get_custom_attributes()));
		}
		return $this->connection;
	}

	public function get_global_filter() {
		if(!isset($this->global_filter))
			$this->global_filter = $this->convert_request_params(get_option('pls_global_search_filters', ''));

		return $this->global_filter;
	}

	public function get_post_listing() {
		if(is_singular('property')) {
			global $pl_wp_site;
			global $wp_query;

			$listings = $pl_wp_site->get_query_results($wp_query);
			return $this->convert_listing($listings[0]);
		}
		return null;
	}

	public function get_search_listings($query_string, $use_global_filter) {
		$search_request = $this->convert_request_params($query_string);
		if($use_global_filter)
			$search_request = PL_Search_Request::combine($this->get_global_filter(), $search_request);

		$search_result = $this->get_connection()->search_listings($search_request);
		return $this->convert_search_result($search_result);
	}


	protected function convert_listing(PL_Listing $listing) {
		$json = $listing ? $listing->json_string() : null;
		$data = json_decode($json, true); // associative array format

		$data['cur_data']['url'] = home_url('property/' . $listing->pdx_id);

		return $data;
	}

	protected function convert_search_result(PL_Search_Result $result) {
		$result_array = array();
		$result_array['total'] = $result->total();
		$result_array['offset'] = $result->offset();
		$result_array['count'] = $result->count();

		foreach($result as $listing)
			$result_array['listings'][] = $this->convert_listing($listing);

		return $result_array;
	}


	protected function convert_request_params($old_array) {
		$new_array = array();

		foreach ($old_array as $key => $value) {
			switch($key) {
				case 'listing_ids':
				case 'property_ids':
					$new_array['pdx_id'] = $value;
					break;

				case 'listing_types':
					$new_array['compound_type'] = $value;
					break;
				case 'purchase_types':
					$new_array['purchase_type'] = $value;
					break;
				case 'zoning_types':
					$new_array['zoning_type'] = $value;
					break;

				case 'metadata':
				case 'location':
				case 'rets':
					foreach($value as $sub_key => $sub_value)
						$new_array[$sub_key] = $sub_value;
					break;

				case 'sort_by':
					if(($dot = strpos($value, '.')) !== false)
						$value = substr($value, $dot + 1);
					$new_array[$key] = $value;
					break;

				default:
					$new_array[$key] = $value;
					break;
			}
		}

		$request = new PL_Search_Request($this->get_connection(), $new_array);
		return $request;
	}
}
