<?php


require_once('attribute_filter.php');


class PL_Search_Filter {
	protected $attributes;
	protected $filters;

	protected $empty;
	protected $error;
	protected $closed;

	public function __construct(PL_Attributes $attributes) {
		$this->attributes = $attributes ?: new PL_Standard_Attributes();
		$this->filters = array();

		$this->empty = false;
		$this->error = false;
		$this->closed = false;
	}

	public function get_filter_options() {
		$array = array();
		foreach($this->attributes->get_filter_attributes() as $attribute)
			switch($attribute->type) {
				case PL_NUMERIC:
				case PL_CURRENCY:
				case PL_DATETIME:
					array_push($array, 'min_' . $attribute->name);
					array_push($array, 'max_' . $attribute->name);
					/* if($attribute->type != PL_NUMERIC) */ break;
				default:
					array_push($array, $attribute->name);
					array_push($array, $attribute->name . '_match');
					break;
			}
		return $array;
	}

	public function get_empty() { return $this->empty; }
	public function get_error() { return $this->error; }
	public function get_closed() { return $this->closed; }

	public function allow_min($name) {
		return ($attribute = $this->attributes->get_attribute($name)) && $attribute->min_name;
	}
	public function allow_max($name) {
		return ($attribute = $this->attributes->get_attribute($name)) && $attribute->max_name;
	}
	public function allow_array($name) {
		return ($attribute = $this->attributes->get_attribute($name)) && $attribute->array_name;
	}
	public function allow_match($name, $match = null) {
		return ($attribute = $this->attributes->get_attribute($name)) && $attribute->match_name;
	}

	public function set($name, $value, $match = null) {
		if($this->empty || is_null($name) || is_null($value)) return !$this->error;

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

				$filter = $this->filters[$name] ?: ($this->filters[$name] = new PL_Attribute_Filter($attribute));
				switch($variation) {
					case 'match':
						$result = $this->allow_match($name, $value) ? $filter->set_match($value) : false;
						break;

					case 'min':
						$result = $this->allow_min($name) ? $filter->set_min($value) : false;
						break;

					case 'max':
						$result = $this->allow_max($name) ? $filter->set_max($value) : false;
						break;

					default:
						$result = !is_array($value) || $this->allow_array($name) &&
							is_null($match) || $this->allow_match($name, $match) ?
							$filter->set_value($value, $match) : false;
						break;
				}

				$this->error = $this->error || !$result;
				return $result;
			}
		}

		// if we fall through to here, the caller did something wrong and broke the query
		return !($this->error = true);
	}

	public function close($force = false) {
		if($this->closed) {
			if($force) $this->error = false;
			return true;
		}

		$this->empty = false;
		$this->error = false;
		$result = true; // a separate flag to report an empty filter that's still open

		foreach($this->filters as $filter) {
			$result = $filter->close($force) && $result;
			$this->error = $this->error || $filter->get_error();
			$this->empty = $this->empty || $filter->get_empty();
		}

		if($result || $force)
			$this->closed = true;

		return $result;
	}

	public static function combine(PL_Search_Filter $a, PL_Search_Filter $b) {
		if(!$a->closed) $a->close(true);
		if(!$b->closed) $b->close(true);

		if($a->attributes == $b->attributes) {
			$result = new PL_Search_Filter($a->attributes);

			foreach($a->filters as $name => $filter) {
				if($b->filters[$name])
					$result->filters[$name] = PL_Attribute_Filter::combine($filter, $b->filters[$name]);
				else
					$result->filters[$name] = $filter;
			}
			foreach($b->filters as $name => $filter) {
				if(!$a->filters[$name])
					$result->filters[$name] = $filter;
			}

			$result->close(true);
			return $result;
		}

		$result = new PL_Search_Filter(null);
		$result->error = $result->empty = true;
		return $result;
	}

	public function query_string() {
		if(!$this->closed) $this->close(true);
		if($this->empty) return "listing_ids[]=0";

		$query = '';
		foreach($this->filters as $filter)
			if($filter_query = $filter->query_string()) {
				if($query) $query .= '&';
				$query .= $filter_query;
			}

		return $query;
	}
}
