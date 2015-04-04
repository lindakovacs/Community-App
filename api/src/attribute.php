<?php


class PDX_Attribute {
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

	static public function create($name, $type, $field_name, $display_group, $display_name, $args = array()) {
		$attribute = new PDX_Attribute($name, $type, $field_name, $display_group, $display_name, $args);

		// if a custom access_name is specified we don't calculate default api names
		if($attribute->access_name)
			return $attribute;

		if($attribute->array_name)
			$attribute->access_name = implode('->', explode('.', $attribute->array_name)) . '[0]';
		else
			$attribute->access_name = implode('->', explode('.', $attribute->field_name));

		$api_group = array_shift(explode('.', $attribute->field_name));
		if($api_group == $attribute->field_name) $api_group = null;

		// by default rets attributes are not searchable, sortable, or aggregable
		if($api_group == 'rets')
			return $attribute;

		// if a custom query_name is specified we don't calculate further defaults
		if($attribute->query_name)
			return $attribute;

		if($attribute->array_name) {
			$attribute->query_name = self::construct_query_name($attribute->array_name) . '[]';
			if(!$attribute->aggregate_name) $attribute->aggregate_name = $attribute->array_name;
		}
		else {
			$attribute->query_name = self::construct_query_name($attribute->field_name);
			if(!$attribute->sort_name) $attribute->sort_name = $attribute->field_name;
			if(!$attribute->aggregate_name) $attribute->aggregate_name = $attribute->field_name;
		}

		return $attribute;
	}

	static protected function construct_query_name($field_name) {
		$parts = explode('.', $field_name);
		if(in_array($parts[0], array('cur_data', 'uncur_data'))) $parts[0] = 'metadata';
		return array_shift($parts) . (count($parts) ? '[' . implode('][', $parts) . ']' : '');
	}

	protected function __construct($name, $type, $field_name, $display_group, $display_name, $api_names = array()) {
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


class PDX_Attributes {
	protected $attributes;

	public function get_attribute($name) {
		return $this->attributes[$name];
	}

	public function get_attributes() {
		return $this->attributes;
	}
}


class PDX_Standard_Attributes extends PDX_Attributes {
	static protected $standard_attributes;	// attributes defined by Placester

	public function __construct() {
		if(!self::$standard_attributes)
			self::$standard_attributes = self::construct_standard_attributes();

		$this->attributes = self::$standard_attributes;
	}

	static protected function construct_standard_attributes() {
		global $PDX_STANDARD_ATTRIBUTE_LIST;

		$attributes = array();
		$continuation = false;
		foreach(array_map('trim', explode("\n", $PDX_STANDARD_ATTRIBUTE_LIST)) as $line) {
			if(empty($line) || substr($line, 0, 2) == '//')
				continue;

			$line = array_map('trim', explode(',', $line));
			if(!$continuation) {
				if(count($line) == 5) {
					$attributes[] = PDX_Attribute::create($line[0], $line[1], $line[2], $line[3], $line[4]);
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
				$attributes[] = PDX_Attribute::create($basic[0], $basic[1], $basic[2], $basic[3], $basic[4], $extended);
		}

		// turn the array into an associative array with names as the index values
		return array_combine(array_map(function ($attribute) { return $attribute->name; }, $attributes), $attributes);
	}
}


class PDX_Connection_Attributes extends PDX_Standard_Attributes {
	protected $connection;

	public function __construct(PDX_API_Connection $connection) {
		parent::__construct();

		$this->connection = $connection;
		$this->attributes = $this->remove_unpopulated_attributes($this->attributes);
		$this->attributes = $this->add_custom_attributes($this->attributes);
	}

	protected function remove_unpopulated_attributes($attributes) {
		foreach($attributes as $attribute) {
			if(array_shift(explode('.', $attribute->field_name)) == 'cur_data') {
				switch($attribute->type) {
					
				}
			}
		}

		return $attributes;
	}

	protected function add_custom_attributes($attributes) {
		return $attributes;
	}
}


$PDX_BOOLEAN = 1;
$PDX_NUMERIC = 2;
$PDX_CURRENCY = 3;
$PDX_TEXT_VALUE = 4;
$PDX_SHORT_TEXT = 5;
$PDX_LONG_TEXT = 6;
$PDX_DATE_TIME = 7;


$PDX_STANDARD_ATTRIBUTE_LIST = <<<PDX_STANDARD_ATTRIBUTE_LIST
	pdx_id,             $PDX_TEXT_VALUE,      id,                         Listing,             Placester ID,
		query_name      =>   listing_ids[]
	mls_id,             $PDX_TEXT_VALUE,      rets.mls_id,                Listing,             MLS ID,
		query_name      =>   rets[mls_id]

	listing_type,       $PDX_TEXT_VALUE,      compound_type,              Listing,             Listing Type
	property_type,      $PDX_TEXT_VALUE,      property_type,              Listing,             Property Type

	zoning_type,        $PDX_TEXT_VALUE,      zoning_type,                Listing,             Zoning Type,
		array_name      =>   zoning_types
	purchase_type,      $PDX_TEXT_VALUE,      purchase_type,              Listing,             Purchase Type,
		array_name      =>   purchase_types

	created_at,         $PDX_DATE_TIME,       created_at,                 Listing,             Created at
	updated_at,         $PDX_DATE_TIME,       updated_at,                 Listing,             Updated at
	status,             $PDX_TEXT_VALUE,      cur_data.status,            Listing,             Status
	dom,                $PDX_NUMERIC,         cur_data.dom,               Listing,             Days on Market
	lst_dte,            $PDX_DATE_TIME,       cur_data.lst_dte,           Listing,             List Date

	// Address
	address,            $PDX_SHORT_TEXT,      location.address,           Location,            Address
	unit,               $PDX_SHORT_TEXT,      location.unit,              Location,            Unit

	// Location
	locality,           $PDX_TEXT_VALUE,      location.locality,          Location,            City
	region,             $PDX_TEXT_VALUE,      location.region,            Location,            State
	postal,             $PDX_TEXT_VALUE,      location.postal,            Location,            Zip
	neighborhood,       $PDX_TEXT_VALUE,      location.neighborhood,      Location,            Neighborhood
	county,             $PDX_TEXT_VALUE,      location.county,            Location,            County
	country,            $PDX_TEXT_VALUE,      location.country,           Location,            Country

	latitude,           $PDX_NUMERIC,         location.latitude,          Location,            Latitude,
		access_name     =>   location->coords[0]
	longitude,          $PDX_NUMERIC,         location.longitude,         Location,            Longitude,
		access_name     =>   location->coords[1]

	// Basic Info
	price,              $PDX_CURRENCY,        cur_data.price,             Basic,               Price
	sqft,               $PDX_NUMERIC,         cur_data.sqft,              Basic,               Square Feet
	beds,               $PDX_NUMERIC,         cur_data.beds,              Basic,               Bedrooms
	beds_avail,         $PDX_NUMERIC,         cur_data.beds_avail,        Basic,               Beds Available
	baths,              $PDX_NUMERIC,         cur_data.baths,             Basic,               Bathrooms
	half_baths,         $PDX_NUMERIC,         cur_data.half_baths,        Basic,               Half Baths
	desc,               $PDX_LONG_TEXT,       cur_data.desc,              Basic,               Description
	short_desc,         $PDX_SHORT_TEXT,      cur_data.short_desc,        Basic,               Short Description

	// Price Info
	price_type,         $PDX_TEXT_VALUE,      cur_data.price_type,        Price,               Price Type
	min_price,          $PDX_CURRENCY,        cur_data.min_price,         Price,               Minimum Price
	max_price,          $PDX_CURRENCY,        cur_data.max_price,         Price,               Maximum Price
	price_range,        $PDX_SHORT_TEXT,      cur_data.price_range,       Price,               Price Range

	// Lease Info
	avail_on,           $PDX_DATE_TIME,       cur_data.avail_on,          Lease,               Date Available
	move_in,            $PDX_DATE_TIME,       cur_data.move_in,           Lease,               Move-in Date
	deposit,            $PDX_CURRENCY,        cur_data.deposit,           Lease,               Deposit
	price_unit,         $PDX_TEXT_VALUE,      cur_data.price_unit,        Lease,               Price Interval
	lse_trms,           $PDX_TEXT_VALUE,      cur_data.lse_trms,          Lease,               Lease Interval

	// Sale Notes
	hoa_fee,            $PDX_CURRENCY,        cur_data.hoa_fee,           Notes,               HOA Fee
	hoa_mand,           $PDX_BOOLEAN,         cur_data.hoa_mand,          Notes,               HOA Mandatory
	lndr_own,           $PDX_TEXT_VALUE,      cur_data.lndr_own,          Notes,               Lender Owned

	// Building Info
	prop_name,          $PDX_SHORT_TEXT,      cur_data.prop_name,         Building,            Property Name
	style,              $PDX_TEXT_VALUE,      cur_data.style,             Building,            Style
	floors,             $PDX_NUMERIC,         cur_data.floors,            Building,            Floors
	year_blt,           $PDX_NUMERIC,         cur_data.year_blt,          Building,            Year Built
	bld_sz,             $PDX_SHORT_TEXT,      cur_data.bld_sz,            Building,            Building Size
	cons_stts,          $PDX_TEXT_VALUE,      cur_data.cons_stts,         Building,            Construction Status
	bld_suit,           $PDX_BOOLEAN,         cur_data.bld_suit,          Building,            Build to Suit

	// Parking Info
	park_type,          $PDX_TEXT_VALUE,      cur_data.park_type,         Parking,             Parking Type
	pk_spce,            $PDX_NUMERIC,         cur_data.pk_spce,           Parking,             Parking Spaces
	pk_lease,           $PDX_BOOLEAN,         cur_data.pk_lease,          Parking,             Parking Included
	valet,              $PDX_BOOLEAN,         cur_data.valet,             Parking,             Valet
	guard,              $PDX_BOOLEAN,         cur_data.guard,             Parking,             Guarded
	heat,               $PDX_BOOLEAN,         cur_data.heat,              Parking,             Heated
	carwsh,             $PDX_BOOLEAN,         cur_data.carwsh,            Parking,             Carwash

	// Pets Info
	cats,               $PDX_BOOLEAN,         cur_data.cats,              Pets,                Cats Allowed
	dogs,               $PDX_BOOLEAN,         cur_data.dogs,              Pets,                Dogs Allowed
	pets_cond,          $PDX_BOOLEAN,         cur_data.pets_cond,         Pets,                Pets Conditional

	// Schools
	sch_dist,           $PDX_TEXT_VALUE,      cur_data.sch_dist,          Schools,             School District
	sch_elm,            $PDX_TEXT_VALUE,      cur_data.sch_elm,           Schools,             Elementary School
	sch_jnr,            $PDX_TEXT_VALUE,      cur_data.sch_jnr,           Schools,             Middle School
	sch_hgh,            $PDX_TEXT_VALUE,      cur_data.sch_hgh,           Schools,             High School

	// Lot Info
	lt_sz,              $PDX_NUMERIC,         cur_data.lt_sz,             Lot,                 Lot Size
	lt_sz_unit,         $PDX_TEXT_VALUE,      cur_data.lt_sz_unit,        Lot,                 Lot Size Unit
	corner,             $PDX_BOOLEAN,         cur_data.corner,            Lot,                 Corner
	wooded,             $PDX_BOOLEAN,         cur_data.wooded,            Lot,                 Wooded
	pvd_drv,            $PDX_BOOLEAN,         cur_data.pvd_drv,           Lot,                 Paved Drive
	und_st_tnk,         $PDX_BOOLEAN,         cur_data.und_st_tnk,        Lot,                 Underground Storage Tank
	stream,             $PDX_BOOLEAN,         cur_data.stream,            Lot,                 Stream
	glf_frt,            $PDX_BOOLEAN,         cur_data.glf_frt,           Lot,                 Golf Course Frontage
	add_lnd_ava,        $PDX_BOOLEAN,         cur_data.add_lnd_ava,       Lot,                 Additional Land Available
	zr_lt_lne,          $PDX_BOOLEAN,         cur_data.zr_lt_lne,         Lot,                 Zero Lot Line
	fld_pln,            $PDX_BOOLEAN,         cur_data.fld_pln,           Lot,                 Flood Plain
	shrd_drv,           $PDX_BOOLEAN,         cur_data.shrd_drv,          Lot,                 Shared Drive
	cty_view,           $PDX_BOOLEAN,         cur_data.cty_view,          Lot,                 City View
	clrd,               $PDX_BOOLEAN,         cur_data.clrd,              Lot,                 Cleared
	frmlnd,             $PDX_BOOLEAN,         cur_data.frmlnd,            Lot,                 Farmland
	fencd_encld,        $PDX_BOOLEAN,         cur_data.fencd_encld,       Lot,                 Fenced/Enclosed
	fll_ndd,            $PDX_BOOLEAN,         cur_data.fll_ndd,           Lot,                 Fill Needed
	gntl_slpe,          $PDX_BOOLEAN,         cur_data.gntl_slpe,         Lot,                 Gentle Slope
	level,              $PDX_BOOLEAN,         cur_data.level,             Lot,                 Level
	marsh,              $PDX_BOOLEAN,         cur_data.marsh,             Lot,                 Marsh
	sloping,            $PDX_BOOLEAN,         cur_data.sloping,           Lot,                 Sloping
	stp_slpe,           $PDX_BOOLEAN,         cur_data.stp_slpe,          Lot,                 Steep Slope
	scenic,             $PDX_BOOLEAN,         cur_data.scenic,            Lot,                 Scenic Views

	// Amenities
	grnt_tops,          $PDX_BOOLEAN,         cur_data.grnt_tops,         Amenities,           Granite Countertops
	air_cond,           $PDX_BOOLEAN,         cur_data.air_cond,          Amenities,           Air Conditioning
	cent_ac,            $PDX_BOOLEAN,         cur_data.cent_ac,           Amenities,           Central A/C
	frnshed,            $PDX_BOOLEAN,         cur_data.frnshed,           Amenities,           Furnished
	cent_ht,            $PDX_BOOLEAN,         cur_data.cent_ht,           Amenities,           Central Heat
	frplce,             $PDX_BOOLEAN,         cur_data.frplce,            Amenities,           Fireplace
	hv_ceil,            $PDX_BOOLEAN,         cur_data.hv_ceil,           Amenities,           High/Vaulted Ceiling
	wlk_clst,           $PDX_BOOLEAN,         cur_data.wlk_clst,          Amenities,           Walk-In Closet
	hdwdflr,            $PDX_BOOLEAN,         cur_data.hdwdflr,           Amenities,           Hardwood Floor
	tle_flr,            $PDX_BOOLEAN,         cur_data.tle_flr,           Amenities,           Tile Floor
	fm_lv_rm,           $PDX_BOOLEAN,         cur_data.fm_lv_rm,          Amenities,           Family/Living Room
	bns_rec_rm,         $PDX_BOOLEAN,         cur_data.bns_rec_rm,        Amenities,           Bonus/Rec Room
	lft_lyout,          $PDX_BOOLEAN,         cur_data.lft_lyout,         Amenities,           Loft Layout
	off_den,            $PDX_BOOLEAN,         cur_data.off_den,           Amenities,           Office/Den
	dng_rm,             $PDX_BOOLEAN,         cur_data.dng_rm,            Amenities,           Dining Room
	brkfst_nk,          $PDX_BOOLEAN,         cur_data.brkfst_nk,         Amenities,           Breakfast Nook
	dshwsher,           $PDX_BOOLEAN,         cur_data.dshwsher,          Amenities,           Dishwasher
	refrig,             $PDX_BOOLEAN,         cur_data.refrig,            Amenities,           Refrigerator
	stve_ovn,           $PDX_BOOLEAN,         cur_data.stve_ovn,          Amenities,           Stove/Oven
	stnstl_app,         $PDX_BOOLEAN,         cur_data.stnstl_app,        Amenities,           Stainless Steel Appliances
	attic,              $PDX_BOOLEAN,         cur_data.attic,             Amenities,           Attic
	basemnt,            $PDX_BOOLEAN,         cur_data.basemnt,           Amenities,           Basement
	washer,             $PDX_BOOLEAN,         cur_data.washer,            Amenities,           Washer
	dryer,              $PDX_BOOLEAN,         cur_data.dryer,             Amenities,           Dryer
	lndry_in,           $PDX_BOOLEAN,         cur_data.lndry_in,          Amenities,           Laundry Area - Inside
	lndry_gar,          $PDX_BOOLEAN,         cur_data.lndry_gar,         Amenities,           Laundry Area - Garage
	blc_deck_pt,        $PDX_BOOLEAN,         cur_data.blc_deck_pt,       Amenities,           Balcony/Deck/Patio
	yard,               $PDX_BOOLEAN,         cur_data.yard,              Amenities,           Yard
	swm_pool,           $PDX_BOOLEAN,         cur_data.swm_pool,          Amenities,           Swimming Pool
	jacuzzi,            $PDX_BOOLEAN,         cur_data.jacuzzi,           Amenities,           Jacuzzi/Whirlpool
	sauna,              $PDX_BOOLEAN,         cur_data.sauna,             Amenities,           Sauna
	cble_rdy,           $PDX_BOOLEAN,         cur_data.cble_rdy,          Amenities,           Cable-ready
	hghspd_net,         $PDX_BOOLEAN,         cur_data.hghspd_net,        Amenities,           High-speed Internet

	// Local Points of Interest
	ngb_trans,          $PDX_BOOLEAN,         cur_data.ngb_trans,         Local,               Public Transportation
	ngb_shop,           $PDX_BOOLEAN,         cur_data.ngb_shop,          Local,               Shopping
	ngb_pool,           $PDX_BOOLEAN,         cur_data.ngb_pool,          Local,               Swimming Pool
	ngb_court,          $PDX_BOOLEAN,         cur_data.ngb_court,         Local,               Tennis Court
	ngb_park,           $PDX_BOOLEAN,         cur_data.ngb_park,          Local,               Park
	ngb_trails,         $PDX_BOOLEAN,         cur_data.ngb_trails,        Local,               Walk/Jog Trails
	ngb_stbles,         $PDX_BOOLEAN,         cur_data.ngb_stbles,        Local,               Stables
	ngb_golf,           $PDX_BOOLEAN,         cur_data.ngb_golf,          Local,               Golf Courses
	ngb_med,            $PDX_BOOLEAN,         cur_data.ngb_med,           Local,               Medical Facilities
	ngb_bike,           $PDX_BOOLEAN,         cur_data.ngb_bike,          Local,               Bike Path
	ngb_cons,           $PDX_BOOLEAN,         cur_data.ngb_cons,          Local,               Conservation Area
	ngb_hgwy,           $PDX_BOOLEAN,         cur_data.ngb_hgwy,          Local,               Highway Access
	ngb_mar,            $PDX_BOOLEAN,         cur_data.ngb_mar,           Local,               Marina
	ngb_pvtsch,         $PDX_BOOLEAN,         cur_data.ngb_pvtsch,        Local,               Private School
	ngb_pubsch,         $PDX_BOOLEAN,         cur_data.ngb_pubsch,        Local,               Public School
	ngb_uni,            $PDX_BOOLEAN,         cur_data.ngb_uni,           Local,               University

	// Commercial Lease Info
	loc_desc,           $PDX_LONG_TEXT,       cur_data.loc_desc,          Commercial,          Location Description
	zone_desc,          $PDX_LONG_TEXT,       cur_data.zone_desc,         Commercial,          Zoning Description
	spc_ava,            $PDX_NUMERIC,         cur_data.spc_ava,           Commercial,          Space Available (Sqft)
	min_div,            $PDX_NUMERIC,         cur_data.min_div,           Commercial,          Minimum Divisible
	max_cont,           $PDX_NUMERIC,         cur_data.max_cont,          Commercial,          Maximum Contiguous
	lse_type,           $PDX_TEXT_VALUE,      cur_data.lse_type,          Commercial,          Lease Type
	comm_rate_unit,     $PDX_TEXT_VALUE,      cur_data.comm_rate_unit,    Commercial,          Rental Rate
	sublse,             $PDX_BOOLEAN,         cur_data.sublse,            Commercial,          Sublease
	bld_st,             $PDX_BOOLEAN,         cur_data.bld_st,            Commercial,          Build to Suit

	// Vacation Rental Info
	accoms,             $PDX_LONG_TEXT,       cur_data.accoms,            Vacation,            Accomodates
	avail_info,         $PDX_LONG_TEXT,       cur_data.avail_info,        Vacation,            Availability

	// Attribution
	aid,                $PDX_TEXT_VALUE,      rets.aid,                   Attribution,         Agent ID,
		query_name      =>   rets[aid]
	aname,              $PDX_SHORT_TEXT,      rets.aname,                 Attribution,         Agent Name
	aphone,             $PDX_SHORT_TEXT,      rets.aphone,                Attribution,         Agent Phone
	alicense,           $PDX_SHORT_TEXT,      rets.alicense,              Attribution,         Agent License
	oid,                $PDX_TEXT_VALUE,      rets.oid,                   Attribution,         Office ID,
		query_name      =>   rets[oid]
	oname,              $PDX_SHORT_TEXT,      rets.oname,                 Attribution,         Office Name
	ophone,             $PDX_SHORT_TEXT,      rets.ophone,                Attribution,         Office Phone

	// Co-attribution
	acoid,              $PDX_TEXT_VALUE,      rets.acoid,                 Co-attribution,      Co-agent ID,
		query_name      =>   rets[acoid]
	aconame,            $PDX_SHORT_TEXT,      rets.aconame,               Co-attribution,      Co-agent Name
	acophone,           $PDX_SHORT_TEXT,      rets.acophone,              Co-attribution,      Co-agent Phone
	acolicense,         $PDX_SHORT_TEXT,      rets.acolicense,            Co-attribution,      Co-agent License
	ocoid,              $PDX_TEXT_VALUE,      rets.ocoid,                 Co-attribution,      Co-office ID,
		query_name      =>   rets[ocoid]
	oconame,            $PDX_SHORT_TEXT,      rets.oconame,               Co-attribution,      Co-office Name
	ocophone,           $PDX_SHORT_TEXT,      rets.ocophone,              Co-attribution,      Co-office Phone
PDX_STANDARD_ATTRIBUTE_LIST;


$PDX_Value_Table = array(
	'listing_type' => array(
		'res_sale' => 'Residential Sale',
		'comm_sale' => 'Commercial Sale',
		'res_rental' => 'Residential Rental',
		'comm_rental' => 'Commercial Rental',
		'sublet' => 'Sublet',
		'park_rental' => 'Parking',
		'vac_rental' => 'Vacation Rental'),

	'property_type' => array(
		'duplex' => 'Duplex',
		'penthouse' => 'Penthouse',
		'apartment' => 'Apartment',
		'condo' => 'Condominium',
		'coop' => 'Cooperative',
		'fam_home' => 'Single Family Home',
		'manuf' => 'Manufactured Home',
		'multi_fam' => 'Multi-Family Home',
		'tic' => 'Tenancy in Common',
		'townhouse' => 'Townhouse',
		'vacant' => 'Vacant',
		'ret_anchor' => 'Retail - Anchor',
		'ret_comm' => 'Retail - Community Center',
		'ret_free_stnd' => 'Retail - Free Standing Building',
		'ret_nghbr' => 'Retail - Neighborhood Center',
		'ret_other' => 'Retail - Other',
		'ret_pad' => 'Retail - Pad Site',
		'ret_reg' => 'Retail - Regional Center / Mall',
		'ret_resta' => 'Retail - Restaurant',
		'ret_special' => 'Retail - Speciality Center',
		'ret_strip' => 'Retail - Strip Mall',
		'ret_strt_ret' => 'Retail - Street Retail',
		'ret_sup_reg' => 'Retail - Super Regional Center',
		'ret_theme' => 'Retail - Theme / Festival Center',
		'ret_veh_rel' => 'Retail - Vehicle Related',
		'lan_comm' => 'Land - Commercial / Other',
		'lan_indust' => 'Land - Industrial',
		'lan_office' => 'Land - Office',
		'lan_resid' => 'Land - Residential',
		'lan_ret' => 'Land - Retail',
		'lan_ret_pad' => 'Land - Retail Pad Site',
		'off_med' => 'Office - Medical',
		'off_inst_gov' => 'Office - Institutional / Govermental',
		'off_rd' => 'Office - Research and Development',
		'off_gen' => 'Office - General',
		'off_loft' => 'Office - Loft',
		'ret_outlet' => 'Retail - Outlet',
		'ind_dist_warh' => 'Industrial - Distribution Warehouse',
		'ind_flex' => 'Industrial - Flex Space',
		'ind_manuf' => 'Industrial - Manufacturing',
		'ind_off_shw' => 'Industrial - Office Showroom',
		'ind_ref_str' => 'Industrial - Refigerated / Cold Storage',
		'ind_term_trans' => 'Industrial - Truck Teminal / Hub / Transit',
		'ind_warh' => 'Industrial - Warehouse'),

	'style' => array(
		'colonial' => 'Colonial',
		'garrison' => 'Garrison',
		'cape' => 'Cape Cod',
		'contemp' => 'Contemporary',
		'ranch' => 'Ranch',
		'rai_ranch' => 'Raised Ranch',
		'split_ent' => 'Split Entry',
		'victor' => 'Victorian',
		'tudor' => 'Tudor',
		'gamb_dutc' => 'Gambrel/Dutch',
		'antiq' => 'Antique',
		'farmh' => 'Farmhouse',
		'saltb' => 'Saltbox',
		'cott' => 'Cottage',
		'bungal' => 'Bungalow',
		'mult_lvl' => 'Multi-level',
		'fnt_bk_splt' => 'Front to Back Split',
		'loft_splt' => 'Lofted Split',
		'greek_rev' => 'Greek Revival'));
