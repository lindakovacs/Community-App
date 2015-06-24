<?php


require_once('attribute.php');


class PL_Attribute_Filter {
	protected $attribute;

	protected $match;
	protected $value;
	protected $min_value;
	protected $max_value;

	protected $empty;
	protected $error;
	protected $closed;

	public function construct(PL_Attribute $attribute, $value = null, $match = null) {
		$this->attribute = $attribute;
		if(!$attribute || !$attribute->query_name)
			$this->error = true;

		if($value) $this->set_value($value, $match);
	}

	public function set_value($value, $match = null) {
		if($this->closed)
			return false;

		if(!is_null($this->value))
			$this->error = true;

		$this->value = $value;
		if(!is_null($match))
			return $this->set_match($match);
		return !$this->error;
	}
	public function set_match($match) {
		if($this->closed)
			return false;

		if(!is_null($this->match))
			$this->error = true;

		$this->match = $match;
		return !$this->error;
	}
	public function set_min($min_value) {
		if($this->closed)
			return false;

		if(!is_null($this->min_value))
			$this->error = true;

		$this->min_value = $min_value;
		return !$this->error;
	}
	public function set_max($max_value) {
		if($this->closed)
			return false;

		if(!is_null($this->max_value))
			$this->error = true;

		$this->max_value = $max_value;
		return !$this->error;
	}

	public function close($force = false) {
		if($this->closed) {
			if($force) $this->error = false;
			return true;
		}
		else {
			$this->empty = false;
			$this->error = false;
		}

		// error checking on match type
		$match = $this->match;
		$regular_match = is_null($match) || in_array($match, array('eq', 'in', 'all'));
		$inverse_match = !$regular_match && in_array($match, array('ne', 'nin'));
		$partial_match = !$regular_match && in_array($match, array('like', 'and_like', 'or_like'));
		if(!$regular_match && !$inverse_match && !$partial_match) {
			$match = null;
			$this->error = true;
		}

		// error checking on value array
		$value_array = array();
		if(is_scalar($this->value)) {
			$value_array[] = $this->value;
		}
		else if(is_array($this->value)) {
			foreach($this->value as $element)
				if(is_scalar($this->value))
					$value_array[] = $element;
				else
					$this->error = true;
		}
		else if(!is_null($this->value))
			$this->error = true;

		// remove any duplicate values
		$value_array = array_unique($value_array);

		// error checking on min/max
		if(is_scalar($this->min_value)) {
			$min_value = $this->min_value;
			$check_min = count($value_array) > 0 && !$partial_match;
		}
		else {
			$min_value = null;
			$check_min = false;
			if(!is_null($this->min_value))
				$this->error = true;
		}
		if(is_scalar($this->max_value)) {
			$max_value = $this->max_value;
			$check_max = count($value_array) > 0 && !$partial_match;
		}
		else {
			$max_value = null;
			$check_max = false;
			if(!is_null($this->max_value))
				$this->error = true;
		}

		// check for empty range
		if(!is_null($min_value) && !is_null($max_value) && $min_value > $max_value) {
			$this->empty = true;
			$value_array = array();
		}

		// strip values outside of the min/max range
		else if(!$partial_match) {
			if(count($value_array) > 0 && (!is_null($min_value) || !is_null($max_value))) {
				$lowest = null;
				$highest = null;

				if(!is_null($min_value))
					foreach($value_array as &$value)
						if($value < $min_value) {
							$lowest = is_null($lowest) ? $value : max($lowest, $value);
							if($match == 'all') {
								$value_array = array();
								break;
							}
							unset($value);
						}

				if(count($value_array) > 0 && !is_null($max_value))
					foreach($value_array as &$value)
						if($value > $max_value) {
							$highest = is_null($highest) ? $value : min($highest, $value);
							if($match == 'all') {
								$value_array = array();
								break;
							}
							unset($value);
						}

				if($regular_match) {
					if(count($value_array) == 0) {
						$this->empty = true;
						$max_value = is_null($lowest) ? $max_value : $lowest; // create an empty search
						$min_value = is_null($highest) ? $min_value : $highest; // with min and max
					}
					else {
						// have value(s), min/max are irrelevant
						$max_value = null;
						$min_value = null;
					}
				}

				else if($inverse_match) {
					if(count($value_array) == 0) {
						$match = null;
					}
					else if(!is_null($min_value) && !is_null($max_value) && $min_value == $max_value) {
						// $value equal $min_value equal $max_value
						$this->empty = true;
					}
				}
			}

			// reduce the case where min = max (when a valid non-empty search)
			if(!$this->empty && !is_null($min_value) && !is_null($max_value) && $min_value == $max_value) {
				$match = 'eq';
				$min_value = null;
				$max_value = null;
				$value_array = array($min_value);
			}
		}

		// clear or promote match parameter, unless there a standalone match (see combine below)
		if(count($value_array) > 0 || !is_null($min_value) || !is_null($max_value)) {
			if(count($value_array) == 0)
				$match = null;
			else if(count($value_array) > 1) {
				if($match == 'like')
					$match = 'or_like';
				else if($match = 'eq')
					$match = 'in';
			}
		}

		// if there are no errors on a non-empty search, or the caller uses force...
		if((!$this->error && !$this->empty) || $force) {
			$this->match = $match;
			$this->min_value = $min_value;
			$this->max_value = $max_value;
			$this->value = $value_array;
			$this->closed = true;
			return !$this->error;
		}

		// otherwise we can't close
		return false;
	}

	public static function combine(PL_Attribute_Filter $left, PL_Attribute_Filter $right) {

		// can combine a filter with an error, but only if it's already been closed
		$l_error = ($left->closed ? false : !$left->close(true));
		$r_error = ($right->closed ? false : !$right->close(true));
		if($l_error || $r_error || $left->attribute != $right->attribute)
			return false;

		// if one of the filters is already marked empty we can bail out
		if($left->empty) return clone $left;
		if($right->empty) return clone $right;

		// a standalone match parameter on the left sets the match for a standalone value filter on the right
		if($left->match && count($left->value) == 0 && is_null($right->min_value) && is_null($right->max_value)) {
			$result = clone $right;
			if(is_null($right->match) && is_null($right->min_value) && is_null($right->max_value))
				$result->match = $left->match;
			return $result;
		}

		// otherwise, we need to combine (and the operation is sometimes not commutative)
		$result = clone $left;
		$result->closed = false;

		// min/max combine straightforwardly
		if($right->min_value)
			$result->min_value = is_null($result->min_value) ? $right->min_value : max($result->min_value, $right->min_value);
		if($right->max_value)
			$result->max_value = is_null($result->max_value) ? $right->max_value : min($result->max_value, $right->max_value);

		// trivial combinations
		if(count($right->value) == 0) {
			$result->close(true);
			return $result;
		}
		if(count($result->value) == 0) {
			$result->match = $right->match;
			$result->value = $right->value;
			$result->close(true);
			return $result;
		}

		// other supported combinations
		$require = $all = $include = $exclude = $like = $or_like = null;
		foreach(array($result, $right) as $filter) {
			$match = $filter->match;
			if(is_null($match) || in_array('eq', 'in'))
				$match = count($filter->value) > 1 ? 'in' : 'eq';

			switch($match) {
				case 'all':
					$all = true;
				case 'eq':
					if(!$require)
						$require = $filter->value;
					else
						$require = $require + $filter->value; // array union
					break;
				case 'in':
					if(!$include)
						$include = $filter->value;
					else
						$include = array_intersect($include, $filter->value);
					break;
				case 'ne':
				case 'nin':
					if(!$exclude)
						$exclude = $filter->value;
					else
						$exclude = $exclude + $filter->value;
					break;
				case 'like':
				case 'and_like':
					if(!$like)
						$like = $filter->value;
					else
						$like = $like + $filter->value;
					break;
				case 'or_like':
					if(!$or_like)
						$or_like = $filter->value;
					break;
			}
		}

		if($require) {
			$is_array_attribute = false; // add type check for array data values
			if(count($require) > 1 && !($all || $is_array_attribute)) {
				$result->empty = true; // cannot require more than one value without 'all'
			}
			else if($include) {
				if(count(array_intersect($require, $include)) == 0) {
					if($all || $is_array_attribute)
						$result->error = true;
					else
						$result->empty = true;
				}
			}
			else if($exclude) {
				if(count(array_intersect($require, $exclude)) > 0)
					$result->empty = true;
			}
			else if($like || $or_like) {
				$result->error = true;
			}

			if(!$result->empty) {
				$result->match = $all || count($require) > 1 ? 'all' :
					($result->match == 'in' || $right->match == 'in' ? 'in' :
					($result->match == 'eq' || $right->match == 'eq' ? 'eq' : null));
				$result->value = $require;
			}
		}

		else if($include) {
			if($exclude)
				$include = array_diff($include, $exclude);
			else if($like || $or_like)
				$result->error = true;

			if(count($include) == 0)
				$result->empty = true;
			else {
				$result->match = $result->match == 'in' || $right->match == 'in' ? 'in' : null;
				$result->value = $include;
			}
		}

		else if($exclude) {
			if($like || $or_like)
				$result->error = true;

			$result->match = 'ne';
			$result->value = $exclude;
		}

		else if($like) {
			if($or_like)
				$result->error = true;

			$result->match = 'like';
			$result->value = $like;
		}

		else if($or_like) {
			$result->error = true;	// can't combine with anything

			$result->match = 'or_like';
			$result->value = $or_like;
		}

		else {
			assert(false, 'Unknown error in matching operation logic');
		}

		if($result->empty) {
			$result->min_value = max($require, $include, $exclude);
			$result->max_value = min($require, $include, $exclude);
			if($result->min_value == $result->max_value) {
				$result->match = 'ne';
				$result->value = array($result->min_value);
			}
			else {
				$result->match = null;
				$result->value = null;
			}
		}

		$error = $result->error;
		$result->close(true);
		$result->error = $error;
	}
}


class PL_Search_Filter {
	protected $attributes;
	protected $filter;
	protected $empty;
	protected $error;

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
			else
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

	public static function combine(PL_Search_Filter $a, PL_Search_Filter $b) {
		if($a->attributes == $b->attributes) {
			$result = new PL_Search_Filter($a->attributes);
			return $result;
		}

		$result = new PL_Search_Filter(null);
		$result->error = $result->empty = true;
		return $result;
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
