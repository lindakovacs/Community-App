<?php


define('PL_BOOLEAN', 1);
define('PL_NUMERIC', 2);
define('PL_CURRENCY', 3);
define('PL_TEXT_VALUE', 4);
define('PL_SHORT_TEXT', 5);
define('PL_LONG_TEXT', 6);
define('PL_DATE_TIME', 7);


class PL_Attribute {
	public $name;
	public $type;

	public $field_name;
	public $display_group;
	public $display_name;

	public $access_name;

	public $query_name;
	public $array_name;

	public $sort_name;
	public $aggregate_name;

	public $min_name;
	public $max_name;
	public $match_name;

	public function __construct($name, $type, $field_name, $display_group, $display_name, $api_names = array()) {
		$this->name = $name;
		$this->type = $type;
		$this->field_name = $field_name;
		$this->display_group = $display_group;
		$this->display_name = $display_name;

		// PHP accessor on local data object
		$this->access_name = $api_names['access_name'];

		// different syntax in various api contexts
		$this->query_name = $api_names['query_name'];
		$this->sort_name = $api_names['sort_name'];
		$this->aggregate_name = $api_names['aggregate_name'];
		$this->array_name = $api_names['array_name'];
		$this->min_name = $api_names['min_name'];
		$this->max_name = $api_names['max_name'];
		$this->match_name = $api_names['match_name'];

		// if a custom access_name is specified we don't calculate default api names
		if($this->access_name)
			return;

		// otherwise parse the field name and calculate them
		$field_parts = explode('.', $this->field_name);
		if(count($field_parts) == 1) {
			$group = null;
			$field = $field_parts[0];
		}
		else {
			$group = $field_parts[0];
			$field = $field_parts[1];
		}

		// if the field is an array...
		if($array = (substr($field, -2) == '[]'))
			$field = substr($field, 0, strlen($field) - 2);

		$this->access_name = self::construct_access_name($group, $field, $array);

		// if a custom query_name is specified we don't calculate further defaults
		if(!$this->query_name && $group != 'rets') { // rets fields are not searchable by default
			$this->query_name = self::construct_query_name($group, $field, $array);
			$this->sort_name = self::construct_sort_name($group, $field, $array);
			$this->aggregate_name = self::construct_aggregate_name($group, $field, $array);
		}

		// variations on query_name for api calls
		if($this->query_name) {
			$this->array_name = self::construct_array_name($this->query_name, $group, $field, $array);
			$this->min_name = self::construct_min_name($this->query_name, $group, $field, $array);
			$this->max_name = self::construct_max_name($this->query_name, $group, $field, $array);
			$this->match_name = self::construct_match_name($this->query_name, $group, $field, $array);
		}

		return;
	}

	static protected function construct_access_name($group, $field, $array) {
		return ($group ? $group . '->' : '') . $field . ($array ? '[0]' : '');
	}

	static protected function construct_query_name($group, $field, $array) {
		if(in_array($group, array('cur_data', 'uncur_data'))) $group = 'metadata';
		return ($group ? $group . '[' . $field . ']' : $field) . ($array ? '[]' : '');
	}

	static protected function construct_sort_name($group, $field, $array) {
		return ($group ? $group . '.' : '') . $field;
	}

	static protected function construct_aggregate_name($group, $field, $array) {
		return ($group ? $group . '.' : '') . $field;
	}

	static protected function construct_array_name($query_name, $group, $field, $array) {
		return $query_name . ($array ? '' : '[]');
	}

	static protected function construct_min_name($query_name, $group, $field, $array) {
		return str_replace($field, 'min_' . $field, $array ? substr($query_name, 0, strlen($query_name) - 2) : $query_name);
	}

	static protected function construct_max_name($query_name, $group, $field, $array) {
		return str_replace($field, 'max_' . $field, $array ? substr($query_name, 0, strlen($query_name) - 2) : $query_name);
	}

	static protected function construct_match_name($query_name, $group, $field, $array) {
		return str_replace($field, $field . '_match', $array ? substr($query_name, 0, strlen($query_name) - 2) : $query_name);
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
		$this->query_name = $api_names['query_name'];
		$this->sort_name = $api_names['sort_name'];
		$this->aggregate_name = $api_names['aggregate_name'];
		$this->array_name = $api_names['array_name'];
		$this->min_name = $api_names['min_name'];
		$this->max_name = $api_names['max_name'];
		$this->match_name = $api_names['match_name'];
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
	property_type,      $PL_TEXT_VALUE,      cur_data.prop_type,         Listing,             Property Type
	zoning_type,        $PL_TEXT_VALUE,      zoning_types[],             Listing,             Zoning Type
	purchase_type,      $PL_TEXT_VALUE,      purchase_types[],           Listing,             Purchase Type

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
