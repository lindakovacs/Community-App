<?php


require_once('http.php');
require_once('attribute.php');


class PL_API_Connection extends PL_Attributes {
	static protected $standard_attributes;

	protected $http_connection;
	protected $custom_attributes;


	public function __construct($key, $http_class = null) {
		if(!self::$standard_attributes)
			self::$standard_attributes = self::read_standard_attributes();

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

		return $this->http_connection->DELETE_LISTING($listing->post_string());
	}

	static protected function read_standard_attributes() {
		global $PL_STANDARD_ATTRIBUTE_LIST;

		$attributes = array();
		$continuation = false;
		foreach(array_map('trim', explode("\n", $PL_STANDARD_ATTRIBUTE_LIST)) as $line) {
			if(empty($line) || substr($line, 0, 2) == '//')
				continue;

			$line = array_map('trim', explode(',', $line));
			if(!$continuation) {
				if(count($line) == 5) {
					$attributes[] = new PL_Attribute($line[0], $line[1], $line[2], $line[3], $line[4]);
					continue;
				}

				if(count($line) == 6 && empty($line[5])) {
					$continuation = true;
					$basic = $line;
					$extended = array();
					continue;
				}

				assert(false, "Error parsing attribute {$line[0]}");
			}

			$param = array_map('trim', explode('=>', $line[0]));
			if(count($line) == 1)
				$continuation = false;
			elseif(count($line) == 2 && empty($line[1]))
				$continuation = true;
			else
				assert(false, "Error parsing parameter {$param[0]} on attribute {$basic[0]}");

			$extended[$param[0]] = $param[1];
			if(!$continuation)
				$attributes[] = new PL_Attribute($basic[0], $basic[1], $basic[2], $basic[3], $basic[4], $extended);
		}

		// turn the array into an associative array with names as the index values
		return array_combine(array_map(function ($attribute) { return $attribute->name; }, $attributes), $attributes);
	}

	protected function read_custom_attributes() {
		$attributes = array();
		if($result = $this->connection->ATTRIBUTES()) {
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
