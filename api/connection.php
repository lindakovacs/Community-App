<?php


require_once('http.php');
require_once('attribute.php');
require_once('listing.php');
require_once('search_filter.php');
require_once('search_view.php');
require_once('search_result.php');


class PL_API_Connection extends PL_Attributes {
	protected $http_connection;
	protected $custom_attributes;

	public function __construct($key, $http_class = null) {
		parent::__construct();

		$this->http_connection = new PL_HTTP_Connection($key, $http_class);
		$this->custom_attributes = $this->read_custom_attributes();
	}

	public function get_standard_attributes() {
		return self::$standard_attributes;
	}

	public function get_custom_attributes() {
		return $this->custom_attributes;
	}

	public function enable_attribute($name) {
		if(is_array($name)) {
			foreach($name as $item)
				$this->enable_attribute($item);
			return true;
		}

		if($result = $this->custom_attributes[$name])
			return $this->attributes[$name] = $result;

		if($result = self::$standard_attributes[$name])
			return $this->attributes[$name] = $result;

		return null;
	}

	public function disable_attribute($name) {
		if(is_array($name)) {
			foreach($name as $item)
				$this->disable_attribute($item);
			return true;
		}

		return $this->remove_attribute($name);
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

	public function create_listing(PL_Private_Listing $listing) {
		// debug only
		if($this->http_connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		if($data = $this->http_connection->CREATE_LISTING($listing->post_string())) {
		}
	}

	public function update_listing(PL_Private_Listing $listing) {
		// debug only
		if($this->http_connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		if($data = $this->http_connection->UPDATE_LISTING($listing->pdx_id, $listing->post_string())) {
		}
	}

	public function delete_listing($id) {
		// debug only
		if($this->http_connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		return $this->http_connection->DELETE_LISTING($id);
	}

	public function new_search_filter($args = null) {
		$filter = new PL_Search_Filter($this);
		if(is_array($args)) {
			$filter_options = array_fill_keys($filter->get_filter_options(), true);
			foreach($args as $field => $value)
				if($filter_options[$field]) {
					if(is_array($value) && !($filter->allow_array($field)))
						continue;
					$filter->set($field, $value);
				}
		}
		return $filter;
	}

	public function new_search_view($args = null) {
		$view = new PL_Search_View($this);
		if(is_array($args)) {
			$view_options = array_fill_keys($view->get_view_options(), true);
			foreach($args as $field => $value) {
				if($view_options[$field])
					$view->set($field, $value);
			}
		}
		return $view;
	}

	public function search_listings(PL_Search_Filter $filter = null, PL_Search_View $view = null) {
		$query = $filter ? $filter->query_string() : '';
		if($view && ($view_query = $view->query_string())) {
			if($query) $query .= '&';
			$query .= $view_query;
		}

		if($data = $this->http_connection->SEARCH_LISTINGS($query))
			return new PL_Search_Result($data, $this);
		return null;
	}

	public function read_attribute_values($attribute, PL_Search_Filter $filter = null) {
		if(is_scalar($attribute))
			if(($attribute = $this->get_attribute($attribute)) && $attribute->aggregate_name) {
				$request = 'keys[]=' . $attribute->aggregate_name;
				if($filter) $filter = $filter->query_string();
				if($data = $this->http_connection->SEARCH_AGGREGATE($request, $filter))
					return $data->{$attribute->aggregate_name};
			}
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
}
