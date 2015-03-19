<?php

require_once('search_field.php');

// this is the basic class for filtering property listings on the Placester API
class PL_Search_Filter {
	protected $listing_type;
	protected $purchase_type;
	protected $zoning_type;
	protected $property_type;

	protected $price;
	protected $status;

	protected $area;
	protected $beds;
	protected $baths;
	protected $half_baths;

	protected $address;
	protected $neighborhood;
	protected $locality;
	protected $county;
	protected $region;
	protected $postal;
	protected $country;

	protected $mls_id;
	protected $office_id;
	protected $agent_id;

	public function __construct() {
		$this->listing_type = new PL_Enumerated_Field('', 'compound_type');
		$this->purchase_type = new PL_Array_Field('', 'purchase_types');
		$this->zoning_type = new PL_Array_Field('', 'zoning_types');
		$this->property_type = new PL_Enumerated_Field('', 'property_type');

		$this->price = new PL_Numeric_Field('metadata', 'price');
		$this->status = new PL_Enumerated_Field('metadata', 'status');

		$this->sqft = new PL_Numeric_Field('metadata', 'sqft');
		$this->beds = new PL_Numeric_Field('metadata', 'beds');
		$this->baths = new PL_Numeric_Field('metadata', 'baths');
		$this->half_baths = new PL_Numeric_Field('metadata', 'half_baths');

		$this->address = new PL_Text_Field('location', 'address');
		$this->neighborhood = new PL_Enumerated_Field('metadata', 'neighborhood');
		$this->locality = new PL_Enumerated_Field('metadata', 'locality');
		$this->county = new PL_Enumerated_Field('metadata', 'county');
		$this->region = new PL_Enumerated_Field('metadata', 'region');
		$this->postal = new PL_Enumerated_Field('metadata', 'postal');
		$this->country = new PL_Enumerated_Field('metadata', 'country');

		$this->mls_id = new PL_Enumerated_Field('rets', 'mls_id');
		$this->office_id = new PL_Enumerated_Field('rets', 'office_id');
		$this->agent_id = new PL_Enumerated_Field('rets', 'agent_id');
	}

	protected static function query_concatenate(&$query, $criterion) {
		if($criterion)
		{
			if($query) $query .= "&";
			$query .= $criterion;
		}
	}

	public function api_query() {
		$query = '';

		self::query_concatenate($query, $this->listing_type->api_query());
		self::query_concatenate($query, $this->purchase_type->api_query());
		self::query_concatenate($query, $this->zoning_type->api_query());
		self::query_concatenate($query, $this->property_type->api_query());

		self::query_concatenate($query, $this->price->api_query());
		self::query_concatenate($query, $this->status->api_query());

		self::query_concatenate($query, $this->sqft->api_query());
		self::query_concatenate($query, $this->beds->api_query());
		self::query_concatenate($query, $this->baths->api_query());
		self::query_concatenate($query, $this->half_baths->api_query());

		self::query_concatenate($query, $this->address->api_query());
		self::query_concatenate($query, $this->neighborhood->api_query());
		self::query_concatenate($query, $this->locality->api_query());
		self::query_concatenate($query, $this->county->api_query());
		self::query_concatenate($query, $this->region->api_query());
		self::query_concatenate($query, $this->postal->api_query());
		self::query_concatenate($query, $this->country->api_query());

		self::query_concatenate($query, $this->mls_id->api_query());
		self::query_concatenate($query, $this->office_id->api_query());
		self::query_concatenate($query, $this->agent_id->api_query());

		return $query;
	}

	public function set_listing_type($type, $exclude = false) {
		$this->listing_type->set($type, $exclude);
	}
	public function set_purchase_type($type, $exclude = false) {
		$this->purchase_type->set($type, $exclude);
	}
	public function set_zoning_type($type, $exclude = false) {
		$this->zoning_type->set($type, $exclude);
	}
	public function set_property_type($type, $exclude = false) {
		$this->property_type->set($type, $exclude);
	}

	public function set_price($price) {
		$this->price->set($price);
	}
	public function set_min_price($price) {
		$this->price->set_min($price);
	}
	public function set_max_price($price) {
		$this->price->set_max($price);
	}

	public function set_status($status, $exclude = false) {
		$this->status->set($status, $exclude);
	}

	public function set_sqft($sqft) {
		$this->sqft->set($sqft);
	}
	public function set_min_sqft($sqft) {
		$this->sqft->set_min($sqft);
	}
	public function set_max_sqft($sqft) {
		$this->sqft->set_max($sqft);
	}

	public function set_beds($beds) {
		$this->beds->set($beds);
	}
	public function set_min_beds($beds) {
		$this->beds->set_min($beds);
	}
	public function set_max_beds($beds) {
		$this->beds->set_max($beds);
	}

	public function set_baths($baths) {
		$this->baths->set((integer) $baths);
		if((integer) $baths && preg_match("[0-9]*[+]", $baths)) {
			$this->half_baths->set_range(1, null);
		}
		else {
			$this->half_baths->clear();
		}
	}

	public function set_full_baths($baths) {
		$this->baths->set($baths);
	}
	public function set_half_baths($half_baths) {
		$this->half_baths->set($half_baths);
	}
	public function set_min_baths($baths) {
		$this->baths->set_min($baths);
	}
	public function set_max_baths($baths) {
		$this->baths->set_max($baths);
	}

	public function set_address($address) {
		$this->address->set($address);
	}
	public function set_neighborhood($neighborhood, $exclude = false) {
		$this->$neighborhood->set($neighborhood, $exclude);
	}
	public function set_locality($locality, $exclude = false) {
		$this->locality->set($locality, $exclude);
	}
	public function set_county($county, $exclude = false) {
		$this->county->set($county, $exclude);
	}
	public function set_region($region, $exclude = false) {
		$this->region->set($region, $exclude);
	}
	public function set_postal($postal, $exclude = false) {
		$this->postal->set($postal, $exclude);
	}
	public function set_country($country, $exclude = false) {
		$this->country->set($country, $exclude);
	}

	public function set_mls_id($id, $exclude) {
		$this->mls_id->set($id, $exclude);
	}
	public function set_office_id($id, $exclude) {
		$this->office_id->set($id, $exclude);
	}
	public function set_agent_id($id, $exclude) {
		$this->agent_id->set($id, $exclude);
	}

	public static function combine($left, $right) {
		$left->listing_type = PL_Enumerated_Field::combine($left->listing_type, $right->listing_type);
		$left->purchase_type = PL_Array_Field::combine($left->purchase_type, $right->purchase_type);
		$left->zoning_type = PL_Array_Field::combine($left->zoning_type, $right->zoning_type);
		$left->property_type = PL_Enumerated_Field::combine($left->property_type, $right->property_type);

		$left->price = PL_Numeric_Field::combine($left->price, $right->price);
		$left->status = PL_Enumerated_Field::combine($left->status, $right->status);

		$left->sqft = PL_Numeric_Field::combine($left->sqft, $right->sqft);
		$left->beds = PL_Numeric_Field::combine($left->beds, $right->beds);
		$left->baths = PL_Numeric_Field::combine($left->baths, $right->baths);
		$left->half_baths = PL_Numeric_Field::combine($left->half_baths, $right->half_baths);

		$left->address = PL_Text_Field::combine($left->address, $right->address);
		$left->neighborhood = PL_Enumerated_Field::combine($left->neighborhood, $right->neighborhood);
		$left->locality = PL_Enumerated_Field::combine($left->locality, $right->locality);
		$left->county = PL_Enumerated_Field::combine($left->county, $right->county);
		$left->region = PL_Enumerated_Field::combine($left->region, $right->region);
		$left->postal = PL_Enumerated_Field::combine($left->postal, $right->postal);
		$left->country = PL_Enumerated_Field::combine($left->country, $right->country);

		$left->mls_id = PL_Enumerated_Field::combine($left->mls_id, $right->mls_id);
		$left->office_id = PL_Enumerated_Field::combine($left->office_id, $right->office_id);
		$left->agent_id = PL_Enumerated_Field::combine($left->agent_id, $right->agent_id);

		return $left;
	}

	public function clear() {}
}
