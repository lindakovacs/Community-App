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
	static protected $standard_attributes;	// attributes defined by Placester
	protected $attributes;

	public function __construct() {
		if(!self::$standard_attributes)
			self::$standard_attributes = self::read_standard_attributes();

		$this->attributes = array();
	}

	public function add_attribute($attribute) {
		$result = null;

		if(is_array($attribute)) {
			foreach($attribute as $element)
				$result = $this->add_attribute($element) ?: $result;
		}
		else if(is_string($attribute)) {
			$result = $this->add_attribute_by_name($attribute);
		}
		else if($attribute instanceof PL_Attribute) {
			if($attribute->name)
				$result = $this->attributes[$attribute->name] = $attribute;
		}

		return $result;
	}

	public function add_attribute_by_name($name) {
		if($result = self::$standard_attributes[$name])
			$this->attributes[$name] = $result;

		return $result;
	}

	public function add_attribute_by_field($field) {
	}

	public function remove_attribute($name) {
		$attribute = $this->attributes[$name];
		unset($this->attributes[$name]);
		return $attribute;
	}

	public function get_attribute($name) {
		return $this->attributes[$name];
	}

	public function get_attributes() {
		return $this->attributes;
	}

	public function get_standard_attributes() {
		return self::$standard_attributes;
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

	static protected function read_standard_attributes() {
		global $PL_STANDARD_ATTRIBUTE_LIST;

		$attributes = array();
		$continuation = false;
		foreach(array_map('trim', explode("\n", $PL_STANDARD_ATTRIBUTE_LIST)) as $line) {
			if(empty($line) || substr($line, 0, 2) == '//')
				continue;

			$line = array_map('trim', explode(',', $line));
			if(!$continuation) {
				if(count($line) == 5) {
					$attributes[] = new PL_Attribute($line[0], $line[1], $line[2], $line[3], $line[4]);
					continue;
				}

				if(count($line) == 6 && empty($line[5])) {
					$continuation = true;
					$basic = $line;
					$extended = array();
					continue;
				}

				assert(false, "Error parsing attribute {$line[0]}");
			}

			$param = array_map('trim', explode('=>', $line[0]));
			if(count($line) == 1)
				$continuation = false;
			elseif(count($line) == 2 && empty($line[1]))
				$continuation = true;
			else
				assert(false, "Error parsing parameter {$param[0]} on attribute {$basic[0]}");

			$extended[$param[0]] = $param[1];
			if(!$continuation)
				$attributes[] = new PL_Attribute($basic[0], $basic[1], $basic[2], $basic[3], $basic[4], $extended);
		}

		// turn the array into an associative array with names as the index values
		return array_combine(array_map(function ($attribute) { return $attribute->name; }, $attributes), $attributes);
	}
}


$PL_BOOLEAN = PL_BOOLEAN;
$PL_NUMERIC = PL_NUMERIC;
$PL_CURRENCY = PL_CURRENCY;
$PL_TEXT_VALUE = PL_TEXT_VALUE;
$PL_SHORT_TEXT = PL_SHORT_TEXT;
$PL_LONG_TEXT = PL_LONG_TEXT;
$PL_DATE_TIME = PL_DATE_TIME;


$PL_STANDARD_ATTRIBUTE_LIST = <<<PL_STANDARD_ATTRIBUTE_LIST
	pdx_id,             $PL_TEXT_VALUE,      id,                         Listing,             Placester ID,
		query_name      =>   listing_ids[]
	mls_id,             $PL_TEXT_VALUE,      rets.mls_id,                Listing,             MLS ID,
		query_name      =>   rets[mls_id]

	listing_type,       $PL_TEXT_VALUE,      compound_type,              Listing,             Listing Type
	property_type,      $PL_TEXT_VALUE,      property_type,              Listing,             Property Type

	zoning_type,        $PL_TEXT_VALUE,      zoning_type,                Listing,             Zoning Type,
		array_name      =>   zoning_types
	purchase_type,      $PL_TEXT_VALUE,      purchase_type,              Listing,             Purchase Type,
		array_name      =>   purchase_types

	created_at,         $PL_DATE_TIME,       created_at,                 Listing,             Created at
	updated_at,         $PL_DATE_TIME,       updated_at,                 Listing,             Updated at
	status,             $PL_TEXT_VALUE,      cur_data.status,            Listing,             Status
	dom,                $PL_NUMERIC,         cur_data.dom,               Listing,             Days on Market
	lst_dte,            $PL_DATE_TIME,       cur_data.lst_dte,           Listing,             List Date

	// Address
	address,            $PL_SHORT_TEXT,      location.address,           Location,            Address
	unit,               $PL_SHORT_TEXT,      location.unit,              Location,            Unit

	// Location
	locality,           $PL_TEXT_VALUE,      location.locality,          Location,            City
	region,             $PL_TEXT_VALUE,      location.region,            Location,            State
	postal,             $PL_TEXT_VALUE,      location.postal,            Location,            Zip
	neighborhood,       $PL_TEXT_VALUE,      location.neighborhood,      Location,            Neighborhood
	county,             $PL_TEXT_VALUE,      location.county,            Location,            County
	country,            $PL_TEXT_VALUE,      location.country,           Location,            Country

	latitude,           $PL_NUMERIC,         location.latitude,          Location,            Latitude,
		access_name     =>   location->coords[0]
	longitude,          $PL_NUMERIC,         location.longitude,         Location,            Longitude,
		access_name     =>   location->coords[1]

	// Basic Info
	price,              $PL_CURRENCY,        cur_data.price,             Basic,               Price
	sqft,               $PL_NUMERIC,         cur_data.sqft,              Basic,               Square Feet
	beds,               $PL_NUMERIC,         cur_data.beds,              Basic,               Bedrooms
	beds_avail,         $PL_NUMERIC,         cur_data.beds_avail,        Basic,               Beds Available
	baths,              $PL_NUMERIC,         cur_data.baths,             Basic,               Bathrooms
	half_baths,         $PL_NUMERIC,         cur_data.half_baths,        Basic,               Half Baths
	desc,               $PL_LONG_TEXT,       cur_data.desc,              Basic,               Description
	short_desc,         $PL_SHORT_TEXT,      cur_data.short_desc,        Basic,               Short Description

	// Price Info
	price_type,         $PL_TEXT_VALUE,      cur_data.price_type,        Price,               Price Type
	min_price,          $PL_CURRENCY,        cur_data.min_price,         Price,               Minimum Price
	max_price,          $PL_CURRENCY,        cur_data.max_price,         Price,               Maximum Price
	price_range,        $PL_SHORT_TEXT,      cur_data.price_range,       Price,               Price Range

	// Lease Info
	avail_on,           $PL_DATE_TIME,       cur_data.avail_on,          Lease,               Date Available
	move_in,            $PL_DATE_TIME,       cur_data.move_in,           Lease,               Move-in Date
	deposit,            $PL_CURRENCY,        cur_data.deposit,           Lease,               Deposit
	price_unit,         $PL_TEXT_VALUE,      cur_data.price_unit,        Lease,               Price Interval
	lse_trms,           $PL_TEXT_VALUE,      cur_data.lse_trms,          Lease,               Lease Interval

	// Sale Notes
	hoa_fee,            $PL_CURRENCY,        cur_data.hoa_fee,           Notes,               HOA Fee
	hoa_mand,           $PL_BOOLEAN,         cur_data.hoa_mand,          Notes,               HOA Mandatory
	lndr_own,           $PL_TEXT_VALUE,      cur_data.lndr_own,          Notes,               Lender Owned

	// Building Info
	prop_name,          $PL_SHORT_TEXT,      cur_data.prop_name,         Building,            Property Name
	style,              $PL_TEXT_VALUE,      cur_data.style,             Building,            Style
	floors,             $PL_NUMERIC,         cur_data.floors,            Building,            Floors
	year_blt,           $PL_NUMERIC,         cur_data.year_blt,          Building,            Year Built
	bld_sz,             $PL_SHORT_TEXT,      cur_data.bld_sz,            Building,            Building Size
	cons_stts,          $PL_TEXT_VALUE,      cur_data.cons_stts,         Building,            Construction Status
	bld_suit,           $PL_BOOLEAN,         cur_data.bld_suit,          Building,            Build to Suit

	// Parking Info
	park_type,          $PL_TEXT_VALUE,      cur_data.park_type,         Parking,             Parking Type
	pk_spce,            $PL_NUMERIC,         cur_data.pk_spce,           Parking,             Parking Spaces
	pk_lease,           $PL_BOOLEAN,         cur_data.pk_lease,          Parking,             Parking Included
	valet,              $PL_BOOLEAN,         cur_data.valet,             Parking,             Valet
	guard,              $PL_BOOLEAN,         cur_data.guard,             Parking,             Guarded
	heat,               $PL_BOOLEAN,         cur_data.heat,              Parking,             Heated
	carwsh,             $PL_BOOLEAN,         cur_data.carwsh,            Parking,             Carwash

	// Pets Info
	cats,               $PL_BOOLEAN,         cur_data.cats,              Pets,                Cats Allowed
	dogs,               $PL_BOOLEAN,         cur_data.dogs,              Pets,                Dogs Allowed
	pets_cond,          $PL_BOOLEAN,         cur_data.pets_cond,         Pets,                Pets Conditional

	// Schools
	sch_dist,           $PL_TEXT_VALUE,      cur_data.sch_dist,          Schools,             School District
	sch_elm,            $PL_TEXT_VALUE,      cur_data.sch_elm,           Schools,             Elementary School
	sch_jnr,            $PL_TEXT_VALUE,      cur_data.sch_jnr,           Schools,             Middle School
	sch_hgh,            $PL_TEXT_VALUE,      cur_data.sch_hgh,           Schools,             High School

	// Lot Info
	lt_sz,              $PL_NUMERIC,         cur_data.lt_sz,             Lot,                 Lot Size
	lt_sz_unit,         $PL_TEXT_VALUE,      cur_data.lt_sz_unit,        Lot,                 Lot Size Unit
	corner,             $PL_BOOLEAN,         cur_data.corner,            Lot,                 Corner
	wooded,             $PL_BOOLEAN,         cur_data.wooded,            Lot,                 Wooded
	pvd_drv,            $PL_BOOLEAN,         cur_data.pvd_drv,           Lot,                 Paved Drive
	und_st_tnk,         $PL_BOOLEAN,         cur_data.und_st_tnk,        Lot,                 Underground Storage Tank
	stream,             $PL_BOOLEAN,         cur_data.stream,            Lot,                 Stream
	glf_frt,            $PL_BOOLEAN,         cur_data.glf_frt,           Lot,                 Golf Course Frontage
	add_lnd_ava,        $PL_BOOLEAN,         cur_data.add_lnd_ava,       Lot,                 Additional Land Available
	zr_lt_lne,          $PL_BOOLEAN,         cur_data.zr_lt_lne,         Lot,                 Zero Lot Line
	fld_pln,            $PL_BOOLEAN,         cur_data.fld_pln,           Lot,                 Flood Plain
	shrd_drv,           $PL_BOOLEAN,         cur_data.shrd_drv,          Lot,                 Shared Drive
	cty_view,           $PL_BOOLEAN,         cur_data.cty_view,          Lot,                 City View
	clrd,               $PL_BOOLEAN,         cur_data.clrd,              Lot,                 Cleared
	frmlnd,             $PL_BOOLEAN,         cur_data.frmlnd,            Lot,                 Farmland
	fencd_encld,        $PL_BOOLEAN,         cur_data.fencd_encld,       Lot,                 Fenced/Enclosed
	fll_ndd,            $PL_BOOLEAN,         cur_data.fll_ndd,           Lot,                 Fill Needed
	gntl_slpe,          $PL_BOOLEAN,         cur_data.gntl_slpe,         Lot,                 Gentle Slope
	level,              $PL_BOOLEAN,         cur_data.level,             Lot,                 Level
	marsh,              $PL_BOOLEAN,         cur_data.marsh,             Lot,                 Marsh
	sloping,            $PL_BOOLEAN,         cur_data.sloping,           Lot,                 Sloping
	stp_slpe,           $PL_BOOLEAN,         cur_data.stp_slpe,          Lot,                 Steep Slope
	scenic,             $PL_BOOLEAN,         cur_data.scenic,            Lot,                 Scenic Views

	// Amenities
	grnt_tops,          $PL_BOOLEAN,         cur_data.grnt_tops,         Amenities,           Granite Countertops
	air_cond,           $PL_BOOLEAN,         cur_data.air_cond,          Amenities,           Air Conditioning
	cent_ac,            $PL_BOOLEAN,         cur_data.cent_ac,           Amenities,           Central A/C
	frnshed,            $PL_BOOLEAN,         cur_data.frnshed,           Amenities,           Furnished
	cent_ht,            $PL_BOOLEAN,         cur_data.cent_ht,           Amenities,           Central Heat
	frplce,             $PL_BOOLEAN,         cur_data.frplce,            Amenities,           Fireplace
	hv_ceil,            $PL_BOOLEAN,         cur_data.hv_ceil,           Amenities,           High/Vaulted Ceiling
	wlk_clst,           $PL_BOOLEAN,         cur_data.wlk_clst,          Amenities,           Walk-In Closet
	hdwdflr,            $PL_BOOLEAN,         cur_data.hdwdflr,           Amenities,           Hardwood Floor
	tle_flr,            $PL_BOOLEAN,         cur_data.tle_flr,           Amenities,           Tile Floor
	fm_lv_rm,           $PL_BOOLEAN,         cur_data.fm_lv_rm,          Amenities,           Family/Living Room
	bns_rec_rm,         $PL_BOOLEAN,         cur_data.bns_rec_rm,        Amenities,           Bonus/Rec Room
	lft_lyout,          $PL_BOOLEAN,         cur_data.lft_lyout,         Amenities,           Loft Layout
	off_den,            $PL_BOOLEAN,         cur_data.off_den,           Amenities,           Office/Den
	dng_rm,             $PL_BOOLEAN,         cur_data.dng_rm,            Amenities,           Dining Room
	brkfst_nk,          $PL_BOOLEAN,         cur_data.brkfst_nk,         Amenities,           Breakfast Nook
	dshwsher,           $PL_BOOLEAN,         cur_data.dshwsher,          Amenities,           Dishwasher
	refrig,             $PL_BOOLEAN,         cur_data.refrig,            Amenities,           Refrigerator
	stve_ovn,           $PL_BOOLEAN,         cur_data.stve_ovn,          Amenities,           Stove/Oven
	stnstl_app,         $PL_BOOLEAN,         cur_data.stnstl_app,        Amenities,           Stainless Steel Appliances
	attic,              $PL_BOOLEAN,         cur_data.attic,             Amenities,           Attic
	basemnt,            $PL_BOOLEAN,         cur_data.basemnt,           Amenities,           Basement
	washer,             $PL_BOOLEAN,         cur_data.washer,            Amenities,           Washer
	dryer,              $PL_BOOLEAN,         cur_data.dryer,             Amenities,           Dryer
	lndry_in,           $PL_BOOLEAN,         cur_data.lndry_in,          Amenities,           Laundry Area - Inside
	lndry_gar,          $PL_BOOLEAN,         cur_data.lndry_gar,         Amenities,           Laundry Area - Garage
	blc_deck_pt,        $PL_BOOLEAN,         cur_data.blc_deck_pt,       Amenities,           Balcony/Deck/Patio
	yard,               $PL_BOOLEAN,         cur_data.yard,              Amenities,           Yard
	swm_pool,           $PL_BOOLEAN,         cur_data.swm_pool,          Amenities,           Swimming Pool
	jacuzzi,            $PL_BOOLEAN,         cur_data.jacuzzi,           Amenities,           Jacuzzi/Whirlpool
	sauna,              $PL_BOOLEAN,         cur_data.sauna,             Amenities,           Sauna
	cble_rdy,           $PL_BOOLEAN,         cur_data.cble_rdy,          Amenities,           Cable-ready
	hghspd_net,         $PL_BOOLEAN,         cur_data.hghspd_net,        Amenities,           High-speed Internet

	// Local Points of Interest
	ngb_trans,          $PL_BOOLEAN,         cur_data.ngb_trans,         Local,               Public Transportation
	ngb_shop,           $PL_BOOLEAN,         cur_data.ngb_shop,          Local,               Shopping
	ngb_pool,           $PL_BOOLEAN,         cur_data.ngb_pool,          Local,               Swimming Pool
	ngb_court,          $PL_BOOLEAN,         cur_data.ngb_court,         Local,               Tennis Court
	ngb_park,           $PL_BOOLEAN,         cur_data.ngb_park,          Local,               Park
	ngb_trails,         $PL_BOOLEAN,         cur_data.ngb_trails,        Local,               Walk/Jog Trails
	ngb_stbles,         $PL_BOOLEAN,         cur_data.ngb_stbles,        Local,               Stables
	ngb_golf,           $PL_BOOLEAN,         cur_data.ngb_golf,          Local,               Golf Courses
	ngb_med,            $PL_BOOLEAN,         cur_data.ngb_med,           Local,               Medical Facilities
	ngb_bike,           $PL_BOOLEAN,         cur_data.ngb_bike,          Local,               Bike Path
	ngb_cons,           $PL_BOOLEAN,         cur_data.ngb_cons,          Local,               Conservation Area
	ngb_hgwy,           $PL_BOOLEAN,         cur_data.ngb_hgwy,          Local,               Highway Access
	ngb_mar,            $PL_BOOLEAN,         cur_data.ngb_mar,           Local,               Marina
	ngb_pvtsch,         $PL_BOOLEAN,         cur_data.ngb_pvtsch,        Local,               Private School
	ngb_pubsch,         $PL_BOOLEAN,         cur_data.ngb_pubsch,        Local,               Public School
	ngb_uni,            $PL_BOOLEAN,         cur_data.ngb_uni,           Local,               University

	// Commercial Lease Info
	loc_desc,           $PL_LONG_TEXT,       cur_data.loc_desc,          Commercial,          Location Description
	zone_desc,          $PL_LONG_TEXT,       cur_data.zone_desc,         Commercial,          Zoning Description
	spc_ava,            $PL_NUMERIC,         cur_data.spc_ava,           Commercial,          Space Available (Sqft)
	min_div,            $PL_NUMERIC,         cur_data.min_div,           Commercial,          Minimum Divisible
	max_cont,           $PL_NUMERIC,         cur_data.max_cont,          Commercial,          Maximum Contiguous
	lse_type,           $PL_TEXT_VALUE,      cur_data.lse_type,          Commercial,          Lease Type
	comm_rate_unit,     $PL_TEXT_VALUE,      cur_data.comm_rate_unit,    Commercial,          Rental Rate
	sublse,             $PL_BOOLEAN,         cur_data.sublse,            Commercial,          Sublease
	bld_st,             $PL_BOOLEAN,         cur_data.bld_st,            Commercial,          Build to Suit

	// Vacation Rental Info
	accoms,             $PL_LONG_TEXT,       cur_data.accoms,            Vacation,            Accomodates
	avail_info,         $PL_LONG_TEXT,       cur_data.avail_info,        Vacation,            Availability

	// Attribution
	aid,                $PL_TEXT_VALUE,      rets.aid,                   Attribution,         Agent ID,
		query_name      =>   rets[aid]
	aname,              $PL_SHORT_TEXT,      rets.aname,                 Attribution,         Agent Name
	aphone,             $PL_SHORT_TEXT,      rets.aphone,                Attribution,         Agent Phone
	alicense,           $PL_SHORT_TEXT,      rets.alicense,              Attribution,         Agent License
	oid,                $PL_TEXT_VALUE,      rets.oid,                   Attribution,         Office ID,
		query_name      =>   rets[oid]
	oname,              $PL_SHORT_TEXT,      rets.oname,                 Attribution,         Office Name
	ophone,             $PL_SHORT_TEXT,      rets.ophone,                Attribution,         Office Phone

	// Co-attribution
	acoid,              $PL_TEXT_VALUE,      rets.acoid,                 Co-attribution,      Co-agent ID,
		query_name      =>   rets[acoid]
	aconame,            $PL_SHORT_TEXT,      rets.aconame,               Co-attribution,      Co-agent Name
	acophone,           $PL_SHORT_TEXT,      rets.acophone,              Co-attribution,      Co-agent Phone
	acolicense,         $PL_SHORT_TEXT,      rets.acolicense,            Co-attribution,      Co-agent License
	ocoid,              $PL_TEXT_VALUE,      rets.ocoid,                 Co-attribution,      Co-office ID,
		query_name      =>   rets[ocoid]
	oconame,            $PL_SHORT_TEXT,      rets.oconame,               Co-attribution,      Co-office Name
	ocophone,           $PL_SHORT_TEXT,      rets.ocophone,              Co-attribution,      Co-office Phone
PL_STANDARD_ATTRIBUTE_LIST;
