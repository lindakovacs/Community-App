<?php

class PL_Search_Field {
	protected $group;
	protected $attribute;
	protected $value;

	public function __construct($group, $attribute) {
		$this->group = $group;
		$this->attribute = $attribute;
	}

	public function set($value)
	{
		if(is_null($value) || is_scalar($value))
			$this->value = $value;
	}

	public function clear()
	{
		$this->value = null;
	}

	protected function api_name() {
		$api_name = $this->attribute;
		if($this->group)
			$api_name = $this->group . '[' . $api_name . ']';

		return $api_name;
	}

	public function api_query() {
		if($this->value) {
			return $this->api_name() . '=' . $this->value;
		}

		return '';
	}

	public static function combine($left, $right) {
		assert($left->group == $right->group && $left->attribute == $right->attribute);
		return $right;
	}
}

class PL_Text_Field extends PL_Search_Field {
	protected function api_name($op = null) {
		$api_name = $this->attribute;

		if($op == 'match')
			$api_name = $api_name . '_' . $op;

		if($this->group)
			$api_name = $this->group . '[' . $api_name . ']';

		return $api_name;
	}

	public function api_query() {
		if(is_scalar($this->value)) {
			return $this->api_name() . '=' . $this->value . '&' . $this->api_name('match') . '=' . 'like';
		}

		return '';
	}
}

class PL_Numeric_Field extends PL_Search_Field {
	protected $min;
	protected $max;

	public function set($value)
	{
		if(is_null($value) || is_scalar($value)) {
			$this->value = $value;
			$this->min = null;
			$this->max = null;
		}
	}

	public function clear()
	{
		$this->value = null;
		$this->min = null;
		$this->max = null;
	}

	public function set_min($min)
	{
		if(is_null($min) || is_scalar($min)) {
			$this->value = null;
			$this->min = $min;
		}
	}

	public function set_max($max)
	{
		if(is_null($max) || is_scalar($max)) {
			$this->value = null;
			$this->max = $max;
		}
	}

	public function set_range($min, $max)
	{
		$this->set_min($min);
		$this->set_max($max);
	}

	protected function api_name($op = null) {
		$api_name = $this->attribute;

		if($op == 'min' || $op == 'max')
			$api_name = $op . '_' . $api_name;

		if($this->group)
			$api_name = $this->group . '[' . $api_name . ']';

		return $api_name;
	}

	public function api_query() {
		$min = '';
		if($this->min)
			$min = $this->api_name('min') . '=' . $this->min;

		$max = '';
		if($this->max)
			$max = $this->api_name('max') . '=' . $this->max;

		if($min && $max)
			return $min . '&' . $max;
		else if($min)
			return $min;
		else if($max)
			return $max;

		if($this->value)
			return $this->api_name() . '=' . $this->value;

		return '';
	}

	public static function combine($left, $right) {
		assert($left->group == $right->group && $left->attribute == $right->attribute);

		if($left->value && $right->value) {
			if($left->value != $right->value)
				$left->value = "__DOES_NOT_EXIST__";
			return $left;
		}

		if($left->value) {
			if(($right->min ? $left->value < $right->min : false) ||
				($right->max ? $left->value > $right->max : false))
				$left->value = "__DOES_NOT_EXIST__";
			return $left;
		}

		if($right->value) {
			if(($left->min ? $right->value < $left->min : false) ||
				($left->max ? $right->value > $left->max : false))
				$right->value = "__DOES_NOT_EXIST__";
			return $right;
		}

		if($right->min) {
			if(!$left->min || $right->min > $left->min) $left->min = $right->min;
		}

		if($right->max) {
			if(!$left->max || $right->max < $left->max) $left->max = $right->max;
		}

		if($left->min && $left->max && $left->min > $left->max)
		{
			$left->value = "__DOES_NOT_EXIST__";
			$left->min = $left->max = null;
		}
		return $left;
	}
}

class PL_Enumerated_Field extends PL_Search_Field {
	protected $exclude;

	public function set($value, $exclude = false)
	{
		if(is_null($value) || is_scalar($value) || is_array($value)) {
			$this->value = $value;
			$this->exclude = $exclude;
		}
	}

	public function clear()
	{
		$this->value = null;
		$this->exclude = null;
	}

	protected function api_name($op = null) {
		$api_name = $this->attribute;

		if($op == 'match')
			$api_name = $api_name . '_' . $op;

		if($this->group)
			$api_name = $this->group . '[' . $api_name . ']';

		return $api_name;
	}

	public function api_query() {
		if(is_scalar($this->value)) {
			$query = $this->api_name() . '=' . $this->value;
			if($this->exclude)
				$query .= '&' . $this->api_name('match') . '=' . 'ne';

			return $query;
		}

		if(is_array($this->value)) {
			if(count($this->value) == 0)
				return '';

			if(count($this->value) == 1) {
				$query = $this->api_name() . '=' . $this->value[0];
				if($this->exclude)
					$query .= '&' . $this->api_name('match') . '=' . 'ne';

				return $query;
			}

			$query = '';
			foreach ($this->value as $value) {
				$query .= $this->api_name() . '[]' . '=' . $value . '&';
			}

			if($this->exclude)
				$query .= $this->api_name('match') . '=' . 'nin';
			else
				$query .= $this->api_name('match') . '=' . 'in';

			return $query;
		}

		return '';
	}

	public static function combine($left, $right) {
		assert($left->group == $right->group && $left->attribute == $right->attribute);

		if(empty($left->value))
			return $right;
		else if(empty($right->value))
			return $left;

		// set union
		if($left->exclude && $right->exclude) {
			$left->value = array_keys(array_flip(array_merge($left->value, $right->value)));
		}

		// set intersection
		else if(!$left->exclude && !$right->exclude) {
			$left->value = array_intersect($left->value, $right->value);
		}

		// set subtraction
		else {
			if($left->exclude) {
				$left->exclude = false;
				$left->value = array_diff($right->value, $left->value);
			}
			else {
				$left->value = array_diff($left->value, $right->value);
			}
		}

		if(!$left->exclude && empty($left->value))
			$left->value = "__DOES_NOT_EXIST__";
		return $left;
	}
}

class PL_Array_Field extends PL_Enumerated_Field {
	public function api_query() {

		$query = '';
		if(is_scalar($this->value)) {
			$query .= $this->api_name() . '[]' . '=' . $this->value . '&';
		}

		else if(is_array($this->value)) {
			foreach ($this->value as $value)
				$query .= $this->api_name() . '[]' . '=' . $value . '&';
		}

		if($query) {
			if($this->exclude)
				$query .= $this->api_name('match') . '=' . 'nin';
			else
				$query .= $this->api_name('match') . '=' . 'in';
		}

		return $query;
	}
}
