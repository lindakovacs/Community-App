<?php


require_once('attribute.php');

define(EPSILON, 0);


class PL_Attribute_Filter {
	public $match;
	public $value;
	public $min_value;
	public $max_value;
}


class PL_Search_Filter {
	protected $attributes;
	protected $filter;
	protected $empty; // indicates an illogical (empty set) search
	protected $error; // indicates an illegal (syntax error) search

	public function __construct(PL_Attributes $attributes) {
		$this->attributes = $attributes;
		$this->filter = array();
	}

	public function get_filter_options() {
		$array = array();
		foreach($this->attributes->get_filter_attributes() as $attribute) {
			array_push($array, $attribute->name);
			if(in_array($attribute->type, array(PL_NUMERIC, PL_CURRENCY, PL_DATETIME))) {
				array_push($array, 'min_' . $attribute->name);
				array_push($array, 'max_' . $attribute->name);
			}
			array_push($array, $attribute->name . '_match');
		}
		return $array;
	}

	public function set($name, $value, $match = null) {
		if($this->empty || is_null($value)) return !$this->empty;

		// handle the attribute search syntax of the Placester Data API
		if(strpos($name, 'min_') === 0) {
			$variation = 'min';
			$name = substr($name, 4);
		}
		else if(strpos($name, 'max_') === 0) {
			$variation = 'max';
			$name = substr($name, 4);
		}
		else if(strpos($name, '_match') === strlen($name) - 6) {
			$variation = 'match';
			$name = substr($name, 0, strlen($name) - 6);
		}
		else {
			$variation = null;
		}

		// you can't specify a match parameter if you're using min_, max_, etc.
		if(is_null($match) || is_null($variation)) {

			// the attribute must exist and be searchable
			if(($attribute = $this->attributes->get_attribute($name)) && $attribute->query_name) {

				// a filter on this attribute exists, combine appropriately
				if($exists = $this->filter[$name]) {

					// use of direct _match attributes is only allowed before any values are specified
					if($variation != 'match') {

						// add a min or max value to the existing query with a logical AND
						if($variation == 'min' && is_scalar($value) && $this->allow_min($name))
							return $this->update_min($name, $value);

						else if($variation == 'max' && is_scalar($value) && $this->allow_max($name))
							return $this->update_max($name, $value);

						// add search value(s) to the existing query with a logical AND
						else if(is_scalar($value) && $this->allow_scalar($name, $match))
							return $this->update_scalar($name, $value, $match);

						else if(is_array($value) && $this->allow_array($name, $match))
							return $this->update_array($name, $value, $match);
					}
				}

				// create a new attribute filter (or change the match type of an empty one--a special case)
				else {
					if($variation == 'match' && is_scalar($value) && $this->allow_match($name, $value))
						return $this->set_match($name, $value);

					if($variation == 'min' && is_scalar($value) && $this->allow_min($name))
						return $this->set_min($name, $value);

					if($variation == 'max' && is_scalar($value) && $this->allow_max($name))
						return $this->set_max($name, $value);

					if(is_scalar($value) && $this->allow_scalar($name, $match))
						return $this->set_scalar($name, $value, $match);

					if(is_array($value) && $this->allow_array($name, $match))
						return $this->set_array($name, $value, $match);
				}
			}
		}

		// if we fall through to here, the caller did something wrong and broke the query
		return !($this->error = $this->empty = true);
	}

	protected function allow_match($name, $match) {
		return true;
	}

	protected function allow_min($name) {
		return true;
	}

	protected function allow_max($name) {
		return true;
	}

	protected function allow_scalar($name, $match) {
		return true;
	}

	protected function allow_array($name, $match) {
		return true;
	}

	protected function clean_array_value($value) {
		$value = array_values(array_unique(array_filter($value, "is_scalar")));
		return $value;
	}

	protected function clean_array_match($match) {
		if($match == null || $match == 'eq')
			$match = 'in';
		else if($match == 'ne')
			$match = 'nin';
		return $match;
	}

	protected function set_match($name, $value) {
		$this->filter[$name] = new PL_Attribute_Filter();
		$this->filter[$name]->match = $value;
		return true;
	}

	protected function set_min($name, $value) {
		$this->filter[$name] = new PL_Attribute_Filter();
		$this->filter[$name]->min_value = $value;
		return true;
	}

	protected function set_max($name, $value) {
		$this->filter[$name] = new PL_Attribute_Filter();
		$this->filter[$name]->max_value = $value;
		return true;
	}

	protected function set_scalar($name, $value, $match) {
		$this->filter[$name] = new PL_Attribute_Filter();
		$this->filter[$name]->value = $value;
		$this->filter[$name]->match = $match;
		return true;
	}

	protected function set_array($name, $value, $match) {
		$value = $this->clean_array_value($value);
		$match = $this->clean_array_match($match);

		$this->filter[$name] = new PL_Attribute_Filter();
		$this->filter[$name]->value = $value;
		$this->filter[$name]->match = $match;
		return true;
	}

	protected function update_min($name, $value) {
		if(is_scalar($this->filter[$name]->value)) {
			switch($this->filter[$name]->match) {
				case null:
				case 'eq':
					if($value <= $this->filter[$name]->value)
						return true;
					else
						return !($this->empty = true);
					break;

				case 'gt':
				case 'gte':
					if($value > $this->filter[$name]->value) {
						$this->filter[$name]->min_value = $value;
						unset($this->filter[$name]->value);
						unset($this->filter[$name]->match);
					}
					return true;
					break;

				case 'lt':
					if($value < $this->filter[$name]->value) {
						$this->filter[$name]->min_value = $value;
						$this->filter[$name]->max_value = $this->filter[$name]->value - EPSILON;
						unset($this->filter[$name]->value);
						unset($this->filter[$name]->match);
						return true;
					}
					else
						return !($this->empty = true);
					break;

				case 'lte':
					if($value <= $this->filter[$name]->value) {
						$this->filter[$name]->min_value = $value;
						$this->filter[$name]->max_value = $this->filter[$name]->value;
						unset($this->filter[$name]->value);
						unset($this->filter[$name]->match);
						return true;
					}
					else
						return !($this->empty = true);
					break;
			}
		}

		else if($this->filter[$name]->value == null && $this->filter[$name]->match == null) {
			if(isset($this->filter[$name]->max_value) && $value > $this->filter[$name]->max_value)
				return !($this->empty = true);

			if(!isset($this->filter[$name]->min_value) || $value > $this->filter[$name]->min_value)
				$this->filter[$name]->min_value = $value;

			return true;
		}

		return !($this->error = $this->empty = true);
	}

	protected function update_max($name, $value) {
		if(is_scalar($this->filter[$name]->value)) {
			switch($this->filter[$name]->match) {
				case null:
				case 'eq':
					if($value > $this->filter[$name]->value)
						return true;
					else
						return !($this->empty = true);
					break;

				case 'lt':
				case 'lte':
					if($value < $this->filter[$name]->value) {
						$this->filter[$name]->max_value = $value;
						unset($this->filter[$name]->value);
						unset($this->filter[$name]->match);
					}
					return true;
					break;

				case 'gt':
					if($value > $this->filter[$name]->value) {
						$this->filter[$name]->max_value = $value;
						$this->filter[$name]->min_value = $this->filter[$name]->value + EPSILON;
						unset($this->filter[$name]->value);
						unset($this->filter[$name]->match);
						return true;
					}
					else
						return !($this->empty = true);
					break;

				case 'gte':
					if($value >= $this->filter[$name]->value) {
						$this->filter[$name]->max_value = $value;
						$this->filter[$name]->min_value = $this->filter[$name]->value;
						unset($this->filter[$name]->value);
						unset($this->filter[$name]->match);
						return true;
					}
					else
						return !($this->empty = true);
					break;
			}
		}

		else if($this->filter[$name]->value == null && $this->filter[$name]->match == null) {
			if(isset($this->filter[$name]->min_value) && $value < $this->filter[$name]->min_value)
				return !($this->empty = true);

			if(!isset($this->filter[$name]->max_value) || $value < $this->filter[$name]->max_value)
				$this->filter[$name]->max_value = $value;

			return true;
		}
		return !($this->error = $this->empty = true);
	}

	protected function update_scalar($name, $value, $match) {
		switch($match) {
			case null:
			case 'eq':
				if(is_scalar($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case null:
						case 'eq':
							if($value != $this->filter[$name]->value)
								break;
						case 'ne':
							if($value == $this->filter[$name]->value)
								break;
						case 'lt':
							if($value >= $this->filter[$name]->value)
								break;
						case 'lte':
							if($value > $this->filter[$name]->value)
								break;
						case 'gt':
							if($value <= $this->filter[$name]->value)
								break;
						case 'gte':
							if($value < $this->filter[$name]->value)
								break;

							$this->filter[$name]->value = $value;
							$this->filter[$name]->match = $match;
							return true;
							break;

						default:
							return !($this->error = $this->empty = true);
							break;
					}
					return !($this->empty = true);
				}
				else if(is_array($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case 'in':
							if(!in_array($value, $this->filter[$name]->value))
								break;
						case 'nin':
							if(in_array($value, $this->filter[$name]->value))
								break;

							$this->filter[$name]->value = $value;
							$this->filter[$name]->match = $match;
							return true;
							break;

						default:
							return !($this->error = $this->empty = true);
							break;
					}
					return !($this->empty = true);
				}
				break;

			case 'ne':
				if(is_scalar($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case null:
						case 'eq':
							if($value != $this->filter[$name]->value)
								return true;
							else
								return !($this->empty = true);
							break;

						case 'ne':
							if($value == $this->filter[$name]->value)
								return true;
							else if($this->allow_array($name, 'nin')) {
								$array = array($this->filter[$name]->value, $value);
								$this->filter[$name]->value = $array;
								$this->filter[$name]->match = 'nin';
								return true;
							}
							break;
					}
					return !($this->error = $this->empty = true);
				}
				else if(is_array($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case 'in':
							if(($index = array_search($value, $this->filter[$name]->value)) === false)
								return true;
							else if(count($this->filter[$name]->value > 1)) {
								unset($this->filter[$name]->value[$index]);
								$this->filter[$name]->value = array_values($this->filter[$name]->value);
								if(count($this->filter[$name]->value == 1)) {
									$this->filter[$name]->value = $this->filter[$name]->value[0];
									$this->filter[$name]->match = null;
								}
								return true;
							}
							break;

						case 'nin':
							if(!in_array($value, $this->filter[$name]->value))
								array_push($this->filter[$name]->value, $value);
							return true;
							break;

						default:
							return !($this->error = $this->empty = true);
							break;
					}
					return !($this->empty = true);
				}
				break;

			case 'lt':
				return $this->update_max($name, $value - EPSILON);
				break;
			case 'lte':
				return $this->update_max($name, $value);
				break;
			case 'gt':
				return $this->update_min($name, $value + EPSILON);
				break;
			case 'gte':
				return $this->update_min($name, $value);
				break;
		}

		return !($this->error = $this->empty = true);
	}

	protected function update_array($name, $value, $match) {
		$value = $this->clean_array_value($value);
		$match = $this->clean_array_match($match);

		switch($match) {
			case null:
			case 'eq':
			case 'in':
				if(is_scalar($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case null:
						case 'eq':
							if(in_array($this->filter[$name]->value, $value))
								return true;
							else
								return !($this->empty = true);
							break;

						case 'ne':
							if(($index = array_search($this->filter[$name]->value, $value)) !== false) {
								unset($value[$index]);
								$value = array_values($value);
								if(count($value == 1)) {
									$this->filter[$name]->value = $value[0];
									$this->filter[$name]->match = null;
									return true;
								}
							}
							$this->filter[$name]->value = $value;
							$this->filter[$name]->match = 'in';
							return true;
							break;
					}
					return !($this->error = $this->empty = true);
				}
				else if(is_array($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case 'in':
							$value = array_values(array_intersect($this->filter[$name]->value, $value));
							break;
						case 'nin':
							$value = array_values(array_diff($value, $this->filter[$name]->value));
							break;

						default:
							return !($this->error = $this->empty = true);
							break;
					}

					if(($n = count($this->filter[$name]->value)) == 1) {
						$this->filter[$name]->value = $value[0];
						$this->filter[$name]->match = null;
						return true;
					}
					else if($n > 1) {
						$this->filter[$name]->value = $value;
						$this->filter[$name]->match = 'in';
						return true;
					}

					return !($this->empty = true);
				}
				break;

			case 'ne':
			case 'nin':
				if(is_scalar($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case null:
						case 'eq':
							if(!in_array($this->filter[$name]->value, $value))
								return true;
							else
								return !($this->empty = true);
							break;

						case 'ne':
							if(!in_array($this->filter[$name]->value, $value))
								$value = array_merge(array($this->filter[$name]->value), $value);
							$this->filter[$name]->value = $value;
							$this->filter[$name]->match = 'nin';
							return true;
					}
					return !($this->error = $this->empty = true);
				}
				else if(is_array($this->filter[$name]->value)) {
					switch($this->filter[$name]->match) {
						case 'in':
							$value = array_values(array_diff($this->filter[$name]->value, $value));
							if(($n = count($this->filter[$name]->value)) == 1) {
								$this->filter[$name]->value = $value[0];
								$this->filter[$name]->match = null;
								return true;
							}
							else if($n > 1) {
								$this->filter[$name]->value = $value;
								$this->filter[$name]->match = 'in';
								return true;
							}
							break;

						case 'nin':
							$value = array_values(array_unique(array_merge($this->filter[$name]->value, $value)));
							$this->filter[$name]->value = $value;
							break;

						default:
							return !($this->error = $this->empty = true);
							break;
					}

					return !($this->empty = true);
				}
		}

		return !($this->error = $this->empty = true);
	}

	public function query_string() {
		if($this->empty) return "listing_ids[]=0";

		$query = '';
		foreach($this->filter as $name => $filter) {
			if(($attribute = $this->attributes->get_attribute($name)) && $attribute->query_name) {

				if(isset($filter->min_value))
					$query .= '&' . str_replace($name, 'min_' . $name, $attribute->query_name) . '=' . urlencode($filter->min_value);

				if(isset($filter->max_value))
					$query .= '&' . str_replace($name, 'max_' . $name, $attribute->query_name) . '=' . urlencode($filter->max_value);

				if(isset($filter->match))
					$query .= '&' . str_replace($name, $name . '_match', $attribute->query_name) . '=' . urlencode($filter->match);

				if(is_scalar($filter->value))
					$query .= '&' . $attribute->query_name . '=' . urlencode($filter->value);

				else if(is_array($filter->value)) {
					if(count($filter->value) == 1)
						$query .= '&' . $attribute->query_name . '=' . urlencode($filter->value[0]);

					else if(count($filter->value) > 1) {
						$array_name = $attribute->query_name;
						if(strpos($array_name, '[]') == strlen($array_name) - 2)
							$array_name .= '[]';
						foreach($filter->value as $value) {
							$query .= '&' . $array_name . '=' . urlencode($value);
						}
					}
				}
			}
		}

		// remove initial ampersand
		return substr($query, 1);
	}
}
