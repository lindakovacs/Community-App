<?php


require_once('http.php');
require_once('search_request.php');
require_once('search_result.php');


class PL_API_Connection extends PL_Attribute_Map {
	protected $http_connection;
	protected $custom_attributes;

	public function __construct($key, $http_class = null) {
		parent::__construct();

		$this->http_connection = new PL_HTTP_Connection($key, $http_class);
		$this->custom_attributes = $this->read_custom_attributes();
	}

	public function get_api_key() {
		return $this->$http_connection->API_KEY;
	}

	public function get_custom_attributes() {
		return $this->custom_attributes;
	}

	public function enable_attribute($name) {
		if(is_array($name)) {
			$result = true;
			foreach($name as $item)
				$result = $result && $this->enable_attribute($item);
		}

		else if($result = $this->custom_attributes[$name])
			$this->attributes[$name] = $result;

		else if($result = self::$standard_attributes[$name])
			$this->attributes[$name] = $result;

		if($result) $this->filter_options = null; // cached value is dirty
		return $result;
	}

	public function disable_attribute($name) {
		if(is_array($name)) {
			$result = true;
			foreach($name as $item)
				$result = $result && $this->disable_attribute($item);
		}

		else
			$result = $this->remove_attribute($name);

		if($result) $this->filter_options = null; // cached value is dirty
		return $result;
	}

	public function get_listing($id) {
		if($data = $this->http_connection->GET_LISTING($id))
			return new PL_Listing($data, $this);

		return null;
	}

	public function new_private_listing($id = null) {
		if(!$id)
			return new PL_Private_Listing(null, $this);

		if($id instanceof PL_Listing)
			$data = json_decode($id->json_string());
		else
			$data = $this->http_connection->GET_LISTING($id);

		if(!$data)
			return null;

		unset($data->id); unset($data->rets);	// shouldn't really access listing internals here
		return new PL_Private_Listing($data, $this);
	}

	public function get_private_listing($id) {
		if($id instanceof PL_Listing)
			$data = json_decode($id->json_string());
		else
			$data = $this->http_connection->GET_LISTING($id);

		if($data && !$data->rets)	// shouldn't really access listing internals here
			return new PL_Private_Listing($data, $this);

		return null;
	}

	public function create_private_listing(PL_Private_Listing $listing) {
		// debug only
		if($this->http_connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		if($data = $this->http_connection->CREATE_LISTING($listing->post_string())) {
		}
	}

	public function update_private_listing(PL_Private_Listing $listing) {
		// debug only
		if($this->http_connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		if($data = $this->http_connection->UPDATE_LISTING($listing->pdx_id, $listing->post_string())) {
		}
	}

	public function delete_private_listing($id) {
		// debug only
		if($this->http_connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		return $this->http_connection->DELETE_LISTING($id);
	}

	public function new_search_request($args = null) {
		return new PL_Search_Request($this, $args);
	}

	public function search_listings(PL_Search_Request $item = null) {
		$query = $item ? $item->query_string() : '';
		if($data = $this->http_connection->SEARCH_LISTINGS($query))
			return new PL_Search_Result($data, $this);
		return null;
	}

	protected function read_custom_attributes() {
		$attributes = array();
		if($result = $this->http_connection->ATTRIBUTES()) {
			foreach($result as $item) {

				$field = 'uncur_data.' . $item->key;
				switch($item->attr_type) {
					case 0: $type = PL_NUMERIC; break;
					case 1: $type = PL_NUMERIC; break;
					case 2: $type = PL_TEXT_VALUE; break;
					case 3: $type = PL_SHORT_TEXT; break;
					case 4: $type = PL_DATE_TIME; break;
					case 5: $type = PL_SHORT_TEXT; break;
					case 6: $type = PL_BOOLEAN; break;
					case 7: $type = PL_CURRENCY; break;
					case 8: $type = PL_SHORT_TEXT; break;
					case 9: $type = PL_SHORT_TEXT; break;
					default: $type = PL_SHORT_TEXT; break;
				}

				$attributes[$item->key] = new PL_Attribute($item->key, $type, $field, $item->cat, $item->name);
			}
		}
		return $attributes;
	}

	public function read_attribute_values($attribute, PL_Search_Filter $filter = null) {
		$keys = array();

		// accepts an attribute name...
		if(is_scalar($attribute) && ($object = $this->get_attribute($attribute)) && $object->aggregate_name)
			$keys[$object->name] = $object->aggregate_name;

		// ... or an array of names
		else if(is_array($attribute))
			foreach($attribute as $element)
				if(is_scalar($element) && ($object = $this->get_attribute($element)) && $object->aggregate_name)
					$keys[$object->name] = $object->aggregate_name;

		if(empty($keys))
			return null;

		$request = 'keys[]=' . implode('&keys[]=', $keys);
		if($filter) $filter = $filter->query_string();
		$response = $this->http_connection->SEARCH_AGGREGATE($request, $filter);

		foreach($keys as $name => $aggregate_name) {
			natcasesort($response->{$aggregate_name});

			$keys[$name] = array_values($response->{$aggregate_name});
			while(trim($keys[$name][0]) === '')
				array_shift($keys[$name]);
		}

		return is_array($attribute) ? $keys : $keys[$attribute];
	}
}
