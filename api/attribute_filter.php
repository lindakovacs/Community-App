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

	public function __construct(PL_Attribute $attribute) {
		$this->attribute = $attribute;
		if(!$attribute || !$attribute->query_name)
			$this->error = true;
	}

	public function get_empty() { return $this->empty; }
	public function get_error() { return $this->error; }
	public function get_closed() { return $this->closed; }

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
		else if(!$this->attribute) {
			return false;
		}

		$this->empty = false;
		$this->error = false;

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
				if(is_scalar($element))
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
				$match = null;
				$value_array = array($min_value);
				$min_value = null;
				$max_value = null;
			}
		}

		// clear or promote match parameter, unless there a standalone match (see combine below)
		if(count($value_array) > 0 || !is_null($min_value) || !is_null($max_value)) {
			if(count($value_array) == 0)
				$match = null;
			else if(count($value_array) > 1 || $this->attribute->query_name == $this->attribute->array_name) {
				if($match == 'eq')
					$match = 'in';
				else if($match == 'ne')
					$match = 'nin';
				else if($match == 'like')
					$match = 'or_like';
			}
		}

		// if there are no errors on a non-empty search, or the caller uses force, close the filter
		if((!$this->error && !$this->empty) || $force) {
			$this->match = $match;
			$this->min_value = $min_value;
			$this->max_value = $max_value;
			$this->value = $value_array;
			$this->closed = true;
		}

		return !$this->error;
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
			if(is_null($right->match) && is_null($right->min_value) && is_null($right->max_value)) {
				$result->closed = false;
				$result->match = $left->match;
				$result->close(true);
			}
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
		$is_array_attribute = $result->attribute->query_name == $result->attribute->array_name;

		foreach(array($result, $right) as $filter) {
			$match = $filter->match;
			if(is_null($match) || in_array($match, array('eq', 'in')))
				$match = count($filter->value) > 1 ? 'in' : 'eq';

			switch($match) {
				case 'all':
					$all = true;
				case 'eq':
					if(!$require)
						$require = $filter->value;
					else
						$require = array_merge($require, array_diff($filter->value, $require)); // array union
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
						$exclude = array_merge($exclude, array_diff($filter->value, $exclude));
					break;
				case 'like':
				case 'and_like':
					if(!$like)
						$like = $filter->value;
					else
						$like = array_merge($like, array_diff($filter->value, $like));
					break;
				case 'or_like':
					if(!$or_like)
						$or_like = $filter->value;
					break;
			}
		}

		if($require) {
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

			$result->match = count($like) > 1 || $is_array_attribute ? 'nin' : 'ne';
			$result->value = $exclude;
		}

		else if($like) {
			if($or_like)
				$result->error = true;

			$result->match = count($like) > 1 ? 'and_like' : 'like';
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
		return $result;
	}

	public function query_string() {
		if(!$this->closed) $this->close(true);
		$query = '';

		if(isset($this->min_value))
			$query .= '&' . $this->attribute->min_name . '=' . urlencode($this->min_value);

		if(isset($this->max_value))
			$query .= '&' . $this->attribute->max_name . '=' . urlencode($this->max_value);

		if($count = count($this->value)) {
			$match = $this->match;
			if($count > 1) {
				$query_name = $this->attribute->array_name;
				if(!$match) $match = 'in';
			}
			else {
				$query_name = $this->attribute->query_name;
				if($this->attribute->array_name != $this->attribute->query_name)
					switch($match) {
						case 'all':
						case 'in':
							$match = 'eq';
							break;
						case 'nin':
							$match = 'ne';
							break;
						case 'and_like':
						case 'or_like':
							$match = 'like';
							break;
					}
			}

			if($match)
				$query .= '&' . $this->attribute->match_name . '=' . urlencode($match);

			foreach($this->value as $value)
				$query .= '&' . $query_name . '=' . urlencode($value);
		}

		// remove initial ampersand
		return substr($query, 1);
	}
}
