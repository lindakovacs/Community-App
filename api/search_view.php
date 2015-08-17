<?php


require_once('attribute_map.php');


class PL_Search_View {
	protected $attributes;
	protected $attribute;
	protected $direction;
	protected $offset;
	protected $limit;

	public function __construct(PL_Attribute_Map $attributes) {
		$this->attributes = $attributes ?: new PL_Standard_Attributes();
		$this->limit = 12;
		$this->offset = 0;
	}

	public function get_view_options() {
		return array('sort_by', 'sort_type', 'offset', 'limit');
	}
	public function get_view_options_array($fill_value = null) {
		return array_fill_keys($this->get_view_options(), $fill_value);
	}

	public function get_view_option_values($option) {
		switch($option) {
			case 'sort_by':
				return array_keys($this->attributes->get_sort_attributes());
				break;
			case 'sort_type':
				return array('asc', 'desc');
				break;
		}
		return null;
	}

	public function get($name) {
		switch($name) {
			case 'sort_by':
				if($this->attribute) return $this->attribute->name;
				break;
			case 'sort_type':
				return $this->direction;
				break;
			case 'offset':
				return $this->offset;
				break;
			case 'limit':
				return $this->limit;
				break;
		}

		return null;
	}

	public function set($name, $value) {
		switch($name) {
			case 'sort_by':
				if(($attribute = $this->attributes->get_attribute($value)) && $attribute->sort_name)
					$this->attribute = $attribute;
				break;
			case 'sort_type':
				if($value == 'asc' || $value == 'desc')
					$this->direction = $value;
				break;
			case 'offset':
				if(is_scalar($value))
					$this->offset = 0 + $value;
				break;
			case 'limit':
				if(is_scalar($value))
					$this->limit = 0 + $value;
				break;
		}
	}

	protected function get_default_sort_type(PL_Attribute $attribute) {
		switch($attribute->type) {
			case PL_BOOLEAN:
			case PL_NUMERIC:
			case PL_CURRENCY:
			case PL_DATE_TIME:
				$direction = 'desc';
				break;
			case PL_TEXT_VALUE:
			case PL_SHORT_TEXT:
			case PL_LONG_TEXT:
			default:
				$direction = 'asc';
				break;
		}
		return $direction;
	}

	public function query_string() {
		if($this->attribute) {
			if(!$this->direction)
				$this->direction = $this->get_default_sort_type($this->attribute);
			$sort = 'sort_by=' . $this->attribute->sort_name . '&sort_type=' . $this->direction;
		}
		else
			$sort = 'sort_by=created_at&sort_type=desc';

		return $sort . '&' . 'offset=' . max($this->offset, 0) . '&limit=' . max($this->limit, 1);
	}
}
