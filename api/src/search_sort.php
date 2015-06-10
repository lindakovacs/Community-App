<?php


require_once('attribute.php');


class PL_Search_Sort {
	protected $attributes;
	protected $attribute;
	protected $direction;

	public function __construct(PL_Attributes $attributes) {
		$this->attributes = $attributes;
	}

	public function get_sort_options() {
		return array('sort_by', 'sort_type');
	}

	public function set($name, $value) {
		if($name == 'sort_by') {
			if(($attribute = $this->attributes->get_attribute($value)) && $attribute->sort_name)
				$this->attribute = $attribute;
		}
		else if($name == 'sort_type') {
			if($value == 'asc' || $value == 'desc')
				$this->direction = $value;
		}
		else if(($attribute = $this->attributes->get_attribute($name)) && $attribute->sort_name) {
			$this->attribute = $attribute;
			if($value == 'asc' || $value == 'desc')
				$this->direction = $value;
		}
	}

	protected function get_default_sort_type(PL_Attribute $attribute) {
		switch($attribute->type) {
			case PL_BOOLEAN:
			case PL_NUMERIC:
			case PL_CURRENCY:
			case PL_DATETIME:
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
			return 'sort_by=' . $this->attribute->sort_name . '&sort_type=' . $this->direction;
		}

		return '';
	}
}
