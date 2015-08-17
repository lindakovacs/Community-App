<?php


require_once('attribute.php');


class PL_Attribute_Map {
	static protected $standard_attributes;
	protected $attributes;
	protected $filter_options; // expanded query attributes including min_, max_, _match

	public function __construct() {
		if(!self::$standard_attributes)
			self::$standard_attributes = self::read_standard_attributes();

		$this->attributes = array();
	}

	public function add_attribute($attribute) {
		if($attribute instanceof PL_Attribute && $attribute->name) {
			$this->filter_options = null; // cached list is now invalid
			return $this->attributes[$attribute->name] = $attribute;
		}

		return null;
	}

	public function remove_attribute($name) {
		if($attribute = $this->attributes[$name]) {
			unset($this->attributes[$name]);
			$this->filter_options = null; // cached list is now invalid
		}
		return $attribute;
	}

	public function get_attribute($name) {
		return $this->attributes[$name];
	}

	public function get_attributes() {
		return $this->attributes;
	}

	public function get_standard_attributes() {
		return self::$standard_attributes;
	}

	public function get_query_attributes() {
		$array = array();
		foreach($this->attributes as $attribute) {
			if($attribute->query_name) {
				$array[$attribute->name] = $attribute;
			}
		}
		return $array;
	}

	public function get_sort_attributes() {
		$array = array();
		foreach($this->attributes as $attribute) {
			if($attribute->sort_name) {
				$array[$attribute->name] = $attribute;
			}
		}
		return $array;
	}

	public function get_filter_attributes() {
		if(!is_null($this->filter_options))
			return $this->filter_options;

		$options = array();
		foreach($this->attributes as $attribute) {
			if($attribute->query_name)
				switch($attribute->type) {
					case PL_NUMERIC:
					case PL_CURRENCY:
					case PL_DATETIME:
						$options['min_' . $attribute->name] = $attribute;
						$options['max_' . $attribute->name] = $attribute;
						/* if($attribute->type != PL_NUMERIC) */ break;
					default:
						$options[$attribute->name] = $attribute;
						$options[$attribute->name . '_match'] = $attribute;
						break;
				}
		}

		return $this->filter_options = $options;
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
}


class PL_Standard_Attributes extends PL_Attribute_Map {
	public function __construct() {
		parent::__construct();
		$this->attributes = self::$standard_attributes;
	}
}
