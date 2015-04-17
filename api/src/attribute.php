<?php


define(PL_BOOLEAN, 1);
define(PL_NUMERIC, 2);
define(PL_CURRENCY, 3);
define(PL_TEXT_VALUE, 4);
define(PL_SHORT_TEXT, 5);
define(PL_LONG_TEXT, 6);
define(PL_DATE_TIME, 7);


class PL_Attribute {
	public $name;
	public $type;
	public $field_name;
	public $display_group;
	public $display_name;

	public $access_name;

	public $array_name;
	public $query_name;
	public $sort_name;
	public $aggregate_name;

	public function __construct($name, $type, $field_name, $display_group, $display_name, $api_names = array()) {
		$this->name = $name;
		$this->type = $type;
		$this->field_name = $field_name;
		$this->display_group = $display_group;
		$this->display_name = $display_name;

		// PHP accessor on local data object
		$this->access_name = $api_names['access_name'];

		// different syntax in various api contexts
		$this->array_name = $api_names['array_name'];
		$this->query_name = $api_names['query_name'];
		$this->sort_name = $api_names['sort_name'];
		$this->aggregate_name = $api_names['aggregate_name'];

		// if a custom access_name is specified we don't calculate default api names
		if($this->access_name)
			return;

		// otherwise fill in api names not specified according to the rules below
		if($this->array_name)
			$this->access_name = implode('->', explode('.', $this->array_name)) . '[0]';
		else
			$this->access_name = implode('->', explode('.', $this->field_name));

		$api_group = array_shift(explode('.', $this->field_name));
		if($api_group == $this->field_name) $api_group = null;

		// by default rets attributes are not searchable, sortable, or aggregable
		if($api_group == 'rets')
			return;

		// if a custom query_name is specified we don't calculate further defaults
		if($this->query_name)
			return;

		if($this->array_name) {
			$this->query_name = self::construct_query_name($this->array_name) . '[]';
			if(!$this->aggregate_name) $this->aggregate_name = $this->array_name;
		}
		else {
			$this->query_name = self::construct_query_name($this->field_name);
			if(!$this->sort_name) $this->sort_name = $this->field_name;
			if(!$this->aggregate_name) $this->aggregate_name = $this->field_name;
		}

		return;
	}

	static protected function construct_query_name($field_name) {
		$parts = explode('.', $field_name);
		if(in_array($parts[0], array('cur_data', 'uncur_data'))) $parts[0] = 'metadata';
		return array_shift($parts) . (count($parts) ? '[' . implode('][', $parts) . ']' : '');
	}

	protected function initialize($name, $type, $field_name, $display_group, $display_name, $api_names) {
		$this->name = $name;
		$this->type = $type;
		$this->field_name = $field_name;
		$this->display_group = $display_group;
		$this->display_name = $display_name;

		// PHP accessor on local data object
		$this->access_name = $api_names['access_name'];

		// different syntax in various api contexts
		$this->array_name = $api_names['array_name'];
		$this->query_name = $api_names['query_name'];
		$this->sort_name = $api_names['sort_name'];
		$this->aggregate_name = $api_names['aggregate_name'];
	}
}


class PL_Attributes {
	protected $attributes;

	public function get_attribute($name) {
		return $this->attributes[$name];
	}

	public function get_attributes() {
		return $this->attributes;
	}

	public function get_filter_attributes() {
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
}
