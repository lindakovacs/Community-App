<?php


require_once('attribute.php');


class PL_Search_View {
	protected $attributes;
	protected $attribute;
	protected $direction;
	protected $offset;
	protected $limit;

	public function __construct(PL_Attributes $attributes) {
		$this->attributes = $attributes ?: new PL_Standard_Attributes();
		$this->limit = 12;
		$this->offset = 0;
	}

	public function get_view_options() {
		return array('sort_by', 'sort_type', 'offset', 'limit');
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
		else if($name == 'offset') {
			if(is_scalar($value))
				$this->offset = 0 + $value;
		}
		else if($name == 'limit') {
			if(is_scalar($value))
				$this->limit = 0 + $value;
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
			$sort = 'sort_by=' . $this->attribute->sort_name . '&sort_type=' . $this->direction;
		}
		else
			$sort = 'sort_by=created_at&sort_type=desc';

		return $sort . '&' . 'offset=' . max($this->offset, 0) . '&limit=' . max($this->limit, 1);
	}
}
