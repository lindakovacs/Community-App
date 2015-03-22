<?php

require_once('connection.php');
require_once('attribute.php');

class PDX_Listing {
	static protected $attributes;
	protected $listing;

	public function __construct($json = null) {
		if(!self::$attributes)
			self::$attributes = new PDX_Attributes();

		// deep copy of existing PDX_Listing object
		if($json instanceof PDX_Listing)
			$this->listing = json_decode($json->json_string());

		// attempt to decode string as json
		else if(is_string($json))
			$this->listing = json_decode($json);

		// take possession of provided stdClass object
		else if($json instanceof stdClass)
			$this->listing = $json;

		// create a new empty listing
		else if(empty($json))
			$this->listing = new stdClass;
	}

	public function __clone() {
		$this->listing = json_decode($this->json_string());
	}

	public function __get($name) {
		if(($attribute = self::$attributes->get_attribute($name)) && $attribute->access_name)
			return $this->get_value($this->listing, $attribute->access_name);

		return null;
	}

	protected function get_value($object, $attribute) {
		return eval('return $object->' . $attribute . ';');
	}

	protected function set_value($object, $attribute, $value) {
		return eval('return $object->' . $attribute . ' = $value;');
	}

	public function json_string() {
		return json_encode($this->listing);
	}
}

class PDX_Private_Listing extends PDX_Listing {
	public function __set($name, $value) {
		if(($attribute = self::$attributes->get_attribute($name)) && $attribute->access_name)
			return $this->set_value($this->listing, $attribute->access_name, $value);

		return null;
	}

	public function post_string() {
		$post = '';
		if(is_null($this->pdx_id)) {
			if(!is_null($listing_type = $this->listing_type))
				$post .= '&compound_type=' . urlencode($listing_type);
		}

		if(!is_null($property_type = $this->property_type))
			$post .= '&metadata[prop_type]=' . urlencode($property_type);

		foreach($this->listing->location as $name => $value) {
			$name = 'location[' . $name . ']';
			if(is_array($value)) {
				$name .= '[]';
				foreach($value as $x)
					$post .= '&' . $name . '=' . urlencode($x);
			}
			else if(is_scalar($value)) {
				$post .= '&' . $name . '=' . urlencode($value);
			}
		}

		foreach($this->listing->cur_data as $name => $value) {
			$name = 'metadata[' . $name . ']';
			if(is_scalar($value)) {
				$post .= '&' . $name . '=' . urlencode($value);
			}
		}

		foreach($this->listing->uncur_data as $name => $value) {
			$name = 'metadata[' . $name . ']';
			if(is_scalar($value)) {
				$post .= '&' . $name . '=' . urlencode($value);
			}
		}

		// remove initial ampersand
		return substr($post, 1);
	}
}

class PDX_Display_Listing extends PDX_Listing {
	protected $display;

	public function __construct($json = null) {
		parent::__construct($json);
		$this->display = new stdClass();
	}

	public function __get($name) {
		if(($attribute = self::$attributes->get_attribute($name)) && $attribute->access_name) {
			$value = $this->get_value($this->display, $attribute->access_name);

			if(is_null($value)) {
				$value = $this->get_value($this->listing, $attribute->access_name);
				$value = $this->format_value($attribute, $value);
				$value = $this->set_value($this->display, $attribute->access_name, $value);
			}
			return $value;
		}
		return null;
	}

	protected function format_value($attribute, $value) {
		return $value;
	}
}