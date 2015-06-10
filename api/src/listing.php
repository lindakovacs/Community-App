<?php


require_once('attribute.php');


class PL_Listing {
	static protected $default_attributes;

	protected $attributes;
	protected $listing;

	public function __construct($data = null, PL_Attributes $attributes = null) {
		// clone from existing PL_Listing object
		if($data instanceof PL_Listing) {
			$this->construct_from_PL_Listing($data);
			return;
		}

		// use standard attributes if attributes not provided
		if(!$attributes) {
			if(!self::$default_attributes) {
				self::$default_attributes = new PL_Standard_Attributes();
			}
			$attributes = self::$default_attributes;
		}
		$this->attributes = $attributes;

		// json may or may not be already decoded
		if($data instanceof stdClass)
			$this->listing = $data;
		else if(is_string($data))
			$this->listing = json_decode($data);
		else if(empty($data))
			$this->listing = new stdClass;
	}

	protected function construct_from_PL_Listing(PL_Listing $other) {
		$this->attributes = $other->attributes;
		$this->listing = json_decode($other->json_string());
	}

	public function __get($name) {
		if(($attribute = $this->attributes->get_attribute($name)) && $attribute->access_name)
			return $this->get_value($this->listing, $attribute->access_name);

		return null;
	}

	protected function get_value(stdClass $object, $attribute) {
		return eval('return $object->' . $attribute . ';');
	}

	protected function set_value(stdClass $object, $attribute, $value) {
		$levels = explode('->', $attribute);
		while($attribute = array_shift($levels)) {

			// directly assign a property (or auto-create an array)
			if(count($levels) == 0)
				return eval('return $object->' . $attribute . ' = $value;');

			// walk down through the structure, create a new object if necessary)
			$object = $this->get_value($object, $attribute) ?: $this->set_value($object, $attribute, new stdClass());
		}
	}

	public function json_string() {
		return json_encode($this->listing);
	}
}


class PL_Private_Listing extends PL_Listing {
	public function __set($name, $value) {
		if(($attribute = $this->attributes->get_attribute($name)) && $attribute->access_name)
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

		if(!empty($this->listing->location))
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

		if(!empty($this->listing->cur_data))
			foreach($this->listing->cur_data as $name => $value) {
				$name = 'metadata[' . $name . ']';
				if(is_scalar($value)) {
					$post .= '&' . $name . '=' . urlencode($value);
				}
			}

		if(!empty($this->listing->uncur_data))
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
