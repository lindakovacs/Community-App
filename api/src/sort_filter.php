<?php

// this is the basic class for filtering property listings on the Placester API
class PL_Sort_Filter {
	protected $group;
	protected $attribute;
	protected $direction;

	protected function api_name() {
		if(!$this->attribute)
			return null;

		$api_name = $this->attribute;
		if($this->group)
			$api_name = $this->group . '.' . $api_name;

		return $api_name;
	}

	public function api_query() {
		if(!$this->attribute)
			return '';

		return 'sort_by=' . $this->api_name() . '&sort_type=' . $this->direction;
	}

	public function by_created_at($direction = 'desc') {
		$this->group = '';
		$this->attribute = 'created_at';
		$this->direction = $direction;
	}

	public function by_property_type($direction = 'asc') {
		$this->group = 'cur_data';
		$this->attribute = 'prop_type';
		$this->direction = $direction;
	}

	public function by_price($direction = 'desc') {
		$this->group = 'cur_data';
		$this->attribute = 'price';
		$this->direction = $direction;
	}
	public function by_sqft($direction = 'desc') {
		$this->group = 'cur_data';
		$this->attribute = 'sqft';
		$this->direction = $direction;
	}
	public function by_beds($direction = 'desc') {
		$this->group = 'cur_data';
		$this->attribute = 'beds';
		$this->direction = $direction;
	}
	public function by_baths($direction = 'desc') {
		$this->group = 'cur_data';
		$this->attribute = 'baths';
		$this->direction = $direction;
	}

	public function by_address($direction = 'asc') {
		$this->group = 'location';
		$this->attribute = 'address';
		$this->direction = $direction;
	}
	public function by_neighborhood($direction = 'asc') {
		$this->group = 'location';
		$this->attribute = 'neighborhood';
		$this->direction = $direction;
	}
	public function by_locality($direction = 'asc') {
		$this->group = 'location';
		$this->attribute = 'locality';
		$this->direction = $direction;
	}
	public function by_county($direction = 'asc') {
		$this->group = 'location';
		$this->attribute = 'county';
		$this->direction = $direction;
	}
	public function by_region($direction = 'asc') {
		$this->group = 'location';
		$this->attribute = 'region';
		$this->direction = $direction;
	}
	public function by_postal($direction = 'asc') {
		$this->group = 'location';
		$this->attribute = 'postal';
		$this->direction = $direction;
	}

	public function clear() {}
}
