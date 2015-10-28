<?php


require_once('membership/membership.php');


class PL_Compatibility_Plugin {
	static $singleton;

	protected $api_key;
	protected $connection;

	protected $global_filter;
	protected $current_listing;
	protected $custom_attributes;


	public function __construct() {
		if(!self::$singleton) {
			self::$singleton = $this;

			// only if we need the compatibility api
			PL_Membership::init();
			wp_enqueue_script('membership');
		}
	}

	public function get_api_key() {
		if(!isset($this->api_key))
			$this->api_key = get_option('placester_api_key', '');

		return $this->api_key;
	}

	public function get_connection() {
		if(!isset($this->connection)) {
			$this->connection = PL_WP_API_Connection::get_connection();
//			$this->connection = $this->get_api_key() ? new PL_API_Connection($this->get_api_key()) : null;
//			$this->connection->enable_attribute(array_keys($this->connection->get_standard_attributes()));
//			$this->connection->enable_attribute(array_keys($this->connection->get_custom_attributes()));
		}
		return $this->connection;
	}

	public function get_global_filter() {
		if(!isset($this->global_filter))
			$this->global_filter = $this->convert_request_params(get_option('pls_global_search_filters', ''));

		return $this->global_filter;
	}

	public function get_search_listings($query_string, $use_global_filter) {
		$search_request = $this->convert_request_params($query_string);
		if($use_global_filter)
			$search_request = PL_Search_Request::combine($this->get_global_filter(), $search_request);

		$search_result = $this->get_connection()->search_listings($search_request);
		return $this->convert_search_result($search_result);
	}

	public function get_current_listing() {
		if(!isset($this->current_listing) && is_singular('property')) {
			global $pl_wp_site;
			global $wp_query;

			$listings = $pl_wp_site->get_query_results($wp_query);
			$this->current_listing = $this->convert_listing($listings[0]);
		}
		return $this->current_listing;
	}

	public function get_custom_attributes() {
		if(!isset($this->custom_attributes)) {
			$this->custom_attributes = array();
			foreach($this->get_connection()->get_custom_attributes() as $attribute)
				$this->custom_attributes[$attribute->name] = $attribute->display_name;
		}
		return $this->custom_attributes;
	}

	public function get_attribute_values($attribute, $use_global_filter, $as_menu = false) {
		$filter = $use_global_filter ? $this->get_global_filter() : null;
		$result = $this->get_connection()->read_attribute_values($attribute, $filter);

		if(!$result)
			return array();

		if(is_scalar($attribute) && $as_menu)
			$result = array_merge(array('' => 'Any'), array_combine($result, $result));

		return $result;
	}


	protected function convert_listing(PL_Listing $listing) {
		$json = $listing ? $listing->json_string() : null;
		$data = json_decode($json, true); // associative array format

		$data['cur_data']['url'] = home_url('property/' . $listing->pdx_id);
		$data['location']['full_address'] = $data['location']['address'] . ' ' . $data['location']['locality'] . ' ' . $data['location']['region'];

		if($unit = $data['location']['unit'])
			if(strpos($data['location']['address'], $unit . ' ') !== 0) {
				$prefix = (strpos($unit, ' ') === false && substr($unit, 0, 1) != '#') ? ' #' : ' ';
				$data['location']['address'] = $data['location']['address'] . $prefix . $unit;
			}

		return $data;
	}

	protected function convert_search_result(PL_Search_Result $result) {
		$result_array = array();
		$result_array['total'] = $result->total();
		$result_array['offset'] = $result->offset();
		$result_array['count'] = $result->count();
		$result_array['limit'] = $result->limit();

		$result_array['listings'] = array();
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
