<?php

define(PDX_BOOLEAN, 1);
define(PDX_NUMERIC, 2);
define(PDX_CURRENCY, 3);
define(PDX_TEXT_VALUE, 4);
define(PDX_SHORT_TEXT, 5);
define(PDX_LONG_TEXT, 6);
define(PDX_DATE_TIME, 7);

class PDX_Attribute {
	public $name;
	public $type;
	public $field_name;
	public $display_group;
	public $display_name;

	public $sort_name;
	public $query_name;
	public $aggregate_name;

	public $access_name;

	// constructors with different default capabilities for convenience
	static public function create($name, $type, $field_name, $display_group, $display_name, $args = array()) {

		if(!isset($args['access_name'])) $args['access_name'] = self::get_default_access_name($field_name);
		if(!isset($args['sort_name'])) $args['sort_name'] = self::get_default_sort_name($field_name);
		if(!isset($args['query_name'])) $args['query_name'] = self::get_default_query_name($field_name);
		if(!isset($args['aggregate_name'])) $args['aggregate_name'] = self::get_default_aggregate_name($field_name);

		return new PDX_Attribute($name, $type, $field_name, $display_group, $display_name, $args);
	}

	// this field will be for data access only, unless otherwise specified
	static public function create_accessible($name, $type, $field_name, $display_group, $display_name, $args = array()) {

		if(!isset($args['access_name'])) $args['access_name'] = self::get_default_access_name($field_name);
		if(!isset($args['sort_name'])) $args['sort_name'] = false;
		if(!isset($args['query_name'])) $args['query_name'] = false;
		if(!isset($args['aggregate_name'])) $args['aggregate_name'] = false;

		return new PDX_Attribute($name, $type, $field_name, $display_group, $display_name, $args);
	}

	// this field will be for data access and search queries
	static public function create_searchable($name, $type, $field_name, $display_group, $display_name, $args = array()) {

		if(!isset($args['access_name'])) $args['access_name'] = self::get_default_access_name($field_name);
		if(!isset($args['sort_name'])) $args['sort_name'] = false;
		if(!isset($args['query_name'])) $args['query_name'] = self::get_default_query_name($field_name);
		if(!isset($args['aggregate_name'])) $args['aggregate_name'] = false;

		return new PDX_Attribute($name, $type, $field_name, $display_group, $display_name, $args);
	}

	static protected function get_default_sort_name($field_name) { return $field_name; }
	static protected function get_default_access_name($field_name) { return implode('->', explode('.', $field_name)); }
	static protected function get_default_aggregate_name($field_name) { return $field_name; }

	static protected function get_default_query_name($field_name) {
		$parts = explode('.', $field_name); $first = array_shift($parts);
		return (in_array($first, array('cur_data', 'uncur_data')) ? 'metadata' : $first) .
			implode('', array_map(function ($part) { return '[' . $part . ']'; }, $parts));
	}

	protected function __construct($name, $type, $field_name, $display_group, $display_name, $args = array()) {
		$this->name = $name;
		$this->type = $type;
		$this->field_name = $field_name;
		$this->display_group = $display_group;
		$this->display_name = $display_name;

		// different syntax in various api contexts
		$this->sort_name = $args['sort_name'];
		$this->query_name = $args['query_name'];
		$this->aggregate_name = $args['aggregate_name'];

		// PHP accessor on local data object
		$this->access_name = $args['access_name'];
	}
}

class PDX_Attributes {
	static protected $standard_attributes;	// attributes defined by Placester

	protected $custom_attributes;	// attributes defined for a specific feed
	protected $populated_attributes;	// attributes actually in use on a feed
	protected $abandoned_attributes;	// attributes formerly in use on a feed
	protected $new_custom_attributes;	// attributes defined since the last configuration
	protected $newly_populated_attributes;	// attributes coming into use since the last configuration
	protected $newly_abandoned_attributes;	// attributes going out of use since the last configuration

	protected $configured_attributes;	// attributes chosen for use on a site

	public function __construct() {
		if(!self::$standard_attributes)
			self::$standard_attributes = self::get_standard_attributes();
		$this->configured_attributes = self::$standard_attributes;
	}

	public function get_attribute($name) {
		return $this->configured_attributes[$name];
	}

	public function get_attributes() {
		return $this->configured_attributes;
	}

	static protected function get_standard_attributes() {
		$attributes = array(

			// Listing Info
			PDX_Attribute::create_searchable('pdx_id', PDX_SHORT_TEXT, 'id', 'Listing', 'Placester ID', array(
				'query_name' => 'listing_ids[]')),

			PDX_Attribute::create_searchable('mls_id', PDX_SHORT_TEXT, 'rets.mls_id', 'Listing', 'MLS ID'),
			PDX_Attribute::create('listing_type', PDX_TEXT_VALUE, 'compound_type', 'Listing', 'Listing Type'),
			PDX_Attribute::create('property_type', PDX_TEXT_VALUE, 'property_type', 'Listing', 'Property Type'),

			PDX_Attribute::create_searchable('zoning_type', PDX_TEXT_VALUE, 'zoning_type', 'Listing', 'Zoning Type', array(
				'query_name' => 'zoning_types[]',
				'aggregate_name' => 'zoning_types',
				'access_name' => 'zoning_types[0]')),
			PDX_Attribute::create_searchable('purchase_type', PDX_TEXT_VALUE, 'purchase_type', 'Listing', 'Purchase Type', array(
				'query_name' => 'purchase_types[]',
				'aggregate_name' => 'purchase_types',
				'access_name' => 'purchase_types[0]')),

			PDX_Attribute::create('created_at', PDX_DATE_TIME, 'created_at', 'Listing', 'Created at'),
			PDX_Attribute::create('updated_at', PDX_DATE_TIME, 'updated_at', 'Listing', 'Updated at'),
			PDX_Attribute::create('status', PDX_TEXT_VALUE, 'cur_data.status', 'Listing', 'Status'),
			PDX_Attribute::create('dom', PDX_NUMERIC, 'cur_data.dom', 'Listing', 'Days on Market'),
			PDX_Attribute::create('lst_dte', PDX_DATE_TIME, 'cur_data.lst_dte', 'Listing', 'List Date'),

			// Address
			PDX_Attribute::create('address', PDX_SHORT_TEXT, 'location.address', 'Location', 'Address'),
			PDX_Attribute::create('unit', PDX_SHORT_TEXT, 'location.unit', 'Location', 'Unit'),

			// Location
			PDX_Attribute::create('locality', PDX_TEXT_VALUE, 'location.locality', 'Location', 'City'),
			PDX_Attribute::create('region', PDX_TEXT_VALUE, 'location.region', 'Location', 'State'),
			PDX_Attribute::create('postal', PDX_TEXT_VALUE, 'location.postal', 'Location', 'Zip'),
			PDX_Attribute::create('neighborhood', PDX_TEXT_VALUE, 'location.neighborhood', 'Location', 'Neighborhood'),
			PDX_Attribute::create('county', PDX_TEXT_VALUE, 'location.county', 'Location', 'County'),
			PDX_Attribute::create('country', PDX_TEXT_VALUE, 'location.country', 'Location', 'Country', array(
				'aggregate_name' => false)),

			PDX_Attribute::create_accessible('latitude', PDX_NUMERIC, 'location.latitude', 'Location', 'Latitude', array(
				'access_name' => 'location->coords[0]')),
			PDX_Attribute::create_accessible('longitude', PDX_NUMERIC, 'location.longitude', 'Location', 'Longitude', array(
				'access_name' => 'location->coords[1]')),

			// Basic Info
			PDX_Attribute::create('price', PDX_CURRENCY, 'cur_data.price', 'Basic', 'Price'),
			PDX_Attribute::create('sqft', PDX_NUMERIC, 'cur_data.sqft', 'Basic', 'Square Feet'),
			PDX_Attribute::create('beds', PDX_NUMERIC, 'cur_data.beds', 'Basic', 'Bedrooms'),
			PDX_Attribute::create('beds_avail', PDX_NUMERIC, 'cur_data.beds_avail', 'Basic', 'Beds Available'),
			PDX_Attribute::create('baths', PDX_NUMERIC, 'cur_data.baths', 'Basic', 'Bathrooms'),
			PDX_Attribute::create('half_baths', PDX_NUMERIC, 'cur_data.half_baths', 'Basic', 'Half Baths'),
			PDX_Attribute::create('desc', PDX_LONG_TEXT, 'cur_data.desc', 'Basic', 'Description'),
			PDX_Attribute::create('short_desc', PDX_SHORT_TEXT, 'cur_data.short_desc', 'Basic', 'Short Description'),

			// Price Info
			PDX_Attribute::create('price_type', PDX_TEXT_VALUE, 'cur_data.price_type', 'Price', 'Price Type'),
			PDX_Attribute::create('min_price', PDX_CURRENCY, 'cur_data.min_price', 'Price', 'Minimum Price'),
			PDX_Attribute::create('max_price', PDX_CURRENCY, 'cur_data.max_price', 'Price', 'Maximum Price'),
			PDX_Attribute::create('price_range', PDX_SHORT_TEXT, 'cur_data.price_range', 'Price', 'Price Range'),

			// Lease Info
			PDX_Attribute::create('avail_on', PDX_DATE_TIME, 'cur_data.avail_on', 'Lease', 'Date Available'),
			PDX_Attribute::create('move_in', PDX_DATE_TIME, 'cur_data.move_in', 'Lease', 'Move-in Date'),
			PDX_Attribute::create('deposit', PDX_CURRENCY, 'cur_data.deposit', 'Lease', 'Deposit'),
			PDX_Attribute::create('price_unit', PDX_TEXT_VALUE, 'cur_data.price_unit', 'Lease', 'Price Interval'),
			PDX_Attribute::create('lse_trms', PDX_TEXT_VALUE, 'cur_data.lse_trms', 'Lease', 'Lease Interval'),

			// Sale Notes
			PDX_Attribute::create('hoa_fee', PDX_CURRENCY, 'cur_data.hoa_fee', 'Notes', 'HOA Fee'),
			PDX_Attribute::create('hoa_mand', PDX_BOOLEAN, 'cur_data.hoa_mand', 'Notes', 'HOA Mandatory'),
			PDX_Attribute::create('lndr_own', PDX_TEXT_VALUE, 'cur_data.lndr_own', 'Notes', 'Lender Owned'),

			// Building Info
			PDX_Attribute::create('prop_name', PDX_SHORT_TEXT, 'cur_data.prop_name', 'Building', 'Property Name'),
			PDX_Attribute::create('style', PDX_TEXT_VALUE, 'cur_data.style', 'Building', 'Style'),
			PDX_Attribute::create('floors', PDX_NUMERIC, 'cur_data.floors', 'Building', 'Floors'),
			PDX_Attribute::create('year_blt', PDX_NUMERIC, 'cur_data.year_blt', 'Building', 'Year Built'),
			PDX_Attribute::create('bld_sz', PDX_SHORT_TEXT, 'cur_data.bld_sz', 'Building', 'Building Size'),
			PDX_Attribute::create('cons_stts', PDX_TEXT_VALUE, 'cur_data.cons_stts', 'Building', 'Construction Status'),
			PDX_Attribute::create('bld_suit', PDX_BOOLEAN, 'cur_data.bld_suit', 'Building', 'Build to Suit'),

			// Parking Info
			PDX_Attribute::create('park_type', PDX_TEXT_VALUE, 'cur_data.park_type', 'Parking', 'Parking Type'),
			PDX_Attribute::create('pk_spce', PDX_NUMERIC, 'cur_data.pk_spce', 'Parking', 'Parking Spaces'),
			PDX_Attribute::create('pk_lease', PDX_BOOLEAN, 'cur_data.pk_lease', 'Parking', 'Parking Included'),
			PDX_Attribute::create('valet', PDX_BOOLEAN, 'cur_data.valet', 'Parking', 'Valet'),
			PDX_Attribute::create('guard', PDX_BOOLEAN, 'cur_data.guard', 'Parking', 'Guarded'),
			PDX_Attribute::create('heat', PDX_BOOLEAN, 'cur_data.heat', 'Parking', 'Heated'),
			PDX_Attribute::create('carwsh', PDX_BOOLEAN, 'cur_data.carwsh', 'Parking', 'Carwash'),

			// Pets Info
			PDX_Attribute::create('cats', PDX_BOOLEAN, 'cur_data.cats', 'Pets', 'Cats Allowed'),
			PDX_Attribute::create('dogs', PDX_BOOLEAN, 'cur_data.dogs', 'Pets', 'Dogs Allowed'),
			PDX_Attribute::create('pets_cond', PDX_BOOLEAN, 'cur_data.pets_cond', 'Lot', 'Pets Conditional'),

			// Schools
			PDX_Attribute::create('sch_dist', PDX_TEXT_VALUE, 'cur_data.sch_dist', 'Schools', 'School District'),
			PDX_Attribute::create('sch_elm', PDX_TEXT_VALUE, 'cur_data.sch_elm', 'Schools', 'Elementary School'),
			PDX_Attribute::create('sch_jnr', PDX_TEXT_VALUE, 'cur_data.sch_jnr', 'Schools', 'Middle School'),
			PDX_Attribute::create('sch_hgh', PDX_TEXT_VALUE, 'cur_data.sch_hgh', 'Schools', 'High School'),

			// Lot Info
			PDX_Attribute::create('lt_sz', PDX_NUMERIC, 'cur_data.lt_sz', 'Lot', 'Lot Size'),
			PDX_Attribute::create('lt_sz_unit', PDX_TEXT_VALUE, 'cur_data.lt_sz_unit', 'Lot', 'Lot Size Unit'),
			PDX_Attribute::create('corner', PDX_BOOLEAN, 'cur_data.corner', 'Lot', 'Corner'),
			PDX_Attribute::create('wooded', PDX_BOOLEAN, 'cur_data.wooded', 'Lot', 'Wooded'),
			PDX_Attribute::create('pvd_drv', PDX_BOOLEAN, 'cur_data.pvd_drv', 'Lot', 'Paved Drive'),
			PDX_Attribute::create('und_st_tnk', PDX_BOOLEAN, 'cur_data.und_st_tnk', 'Lot', 'Underground Storage Tank'),
			PDX_Attribute::create('stream', PDX_BOOLEAN, 'cur_data.stream', 'Lot', 'Stream'),
			PDX_Attribute::create('glf_frt', PDX_BOOLEAN, 'cur_data.glf_frt', 'Lot', 'Golf Course Frontage'),
			PDX_Attribute::create('add_lnd_ava', PDX_BOOLEAN, 'cur_data.add_lnd_ava', 'Lot', 'Additional Land Available'),
			PDX_Attribute::create('zr_lt_lne', PDX_BOOLEAN, 'cur_data.zr_lt_lne', 'Lot', 'Zero Lot Line'),
			PDX_Attribute::create('fld_pln', PDX_BOOLEAN, 'cur_data.fld_pln', 'Lot', 'Flood Plain'),
			PDX_Attribute::create('shrd_drv', PDX_BOOLEAN, 'cur_data.shrd_drv', 'Lot', 'Shared Drive'),
			PDX_Attribute::create('cty_view', PDX_BOOLEAN, 'cur_data.cty_view', 'Lot', 'City View'),
			PDX_Attribute::create('clrd', PDX_BOOLEAN, 'cur_data.clrd', 'Lot', 'Cleared'),
			PDX_Attribute::create('frmlnd', PDX_BOOLEAN, 'cur_data.frmlnd', 'Lot', 'Farmland'),
			PDX_Attribute::create('fencd_encld', PDX_BOOLEAN, 'cur_data.fencd_encld', 'Lot', 'Fenced/Enclosed'),
			PDX_Attribute::create('fll_ndd', PDX_BOOLEAN, 'cur_data.fll_ndd', 'Lot', 'Fill Needed'),
			PDX_Attribute::create('gntl_slpe', PDX_BOOLEAN, 'cur_data.gntl_slpe', 'Lot', 'Gentle Slope'),
			PDX_Attribute::create('level', PDX_BOOLEAN, 'cur_data.level', 'Lot', 'Level'),
			PDX_Attribute::create('marsh', PDX_BOOLEAN, 'cur_data.marsh', 'Lot', 'Marsh'),
			PDX_Attribute::create('sloping', PDX_BOOLEAN, 'cur_data.sloping', 'Lot', 'Sloping'),
			PDX_Attribute::create('stp_slpe', PDX_BOOLEAN, 'cur_data.stp_slpe', 'Lot', 'Steep Slope'),
			PDX_Attribute::create('scenic', PDX_BOOLEAN, 'cur_data.scenic', 'Lot', 'Scenic Views'),

			// Amenities
			PDX_Attribute::create('grnt_tops', PDX_BOOLEAN, 'cur_data.grnt_tops', 'Amenities', 'Granite Countertops'),
			PDX_Attribute::create('air_cond', PDX_BOOLEAN, 'cur_data.air_cond', 'Amenities', 'Air Conditioning'),
			PDX_Attribute::create('cent_ac', PDX_BOOLEAN, 'cur_data.cent_ac', 'Amenities', 'Central A/C'),
			PDX_Attribute::create('frnshed', PDX_BOOLEAN, 'cur_data.frnshed', 'Amenities', 'Furnished'),
			PDX_Attribute::create('cent_ht', PDX_BOOLEAN, 'cur_data.cent_ht', 'Amenities', 'Central Heat'),
			PDX_Attribute::create('frplce', PDX_BOOLEAN, 'cur_data.frplce', 'Amenities', 'Fireplace'),
			PDX_Attribute::create('hv_ceil', PDX_BOOLEAN, 'cur_data.hv_ceil', 'Amenities', 'High/Vaulted Ceiling'),
			PDX_Attribute::create('wlk_clst', PDX_BOOLEAN, 'cur_data.wlk_clst', 'Amenities', 'Walk-In Closet'),
			PDX_Attribute::create('hdwdflr', PDX_BOOLEAN, 'cur_data.hdwdflr', 'Amenities', 'Hardwood Floor'),
			PDX_Attribute::create('tle_flr', PDX_BOOLEAN, 'cur_data.tle_flr', 'Amenities', 'Tile Floor'),
			PDX_Attribute::create('fm_lv_rm', PDX_BOOLEAN, 'cur_data.fm_lv_rm', 'Amenities', 'Family/Living Room'),
			PDX_Attribute::create('bns_rec_rm', PDX_BOOLEAN, 'cur_data.bns_rec_rm', 'Amenities', 'Bonus/Rec Room'),
			PDX_Attribute::create('lft_lyout', PDX_BOOLEAN, 'cur_data.lft_lyout', 'Amenities', 'Loft Layout'),
			PDX_Attribute::create('off_den', PDX_BOOLEAN, 'cur_data.off_den', 'Amenities', 'Office/Den'),
			PDX_Attribute::create('dng_rm', PDX_BOOLEAN, 'cur_data.dng_rm', 'Amenities', 'Dining Room'),
			PDX_Attribute::create('brkfst_nk', PDX_BOOLEAN, 'cur_data.brkfst_nk', 'Amenities', 'Breakfast Nook'),
			PDX_Attribute::create('dshwsher', PDX_BOOLEAN, 'cur_data.dshwsher', 'Amenities', 'Dishwasher'),
			PDX_Attribute::create('refrig', PDX_BOOLEAN, 'cur_data.refrig', 'Amenities', 'Refrigerator'),
			PDX_Attribute::create('stve_ovn', PDX_BOOLEAN, 'cur_data.stve_ovn', 'Amenities', 'Stove/Oven'),
			PDX_Attribute::create('stnstl_app', PDX_BOOLEAN, 'cur_data.stnstl_app', 'Amenities', 'Stainless Steel Appliances'),
			PDX_Attribute::create('attic', PDX_BOOLEAN, 'cur_data.attic', 'Amenities', 'Attic'),
			PDX_Attribute::create('basemnt', PDX_BOOLEAN, 'cur_data.basemnt', 'Amenities', 'Basement'),
			PDX_Attribute::create('washer', PDX_BOOLEAN, 'cur_data.washer', 'Amenities', 'Washer'),
			PDX_Attribute::create('dryer', PDX_BOOLEAN, 'cur_data.dryer', 'Amenities', 'Dryer'),
			PDX_Attribute::create('lndry_in', PDX_BOOLEAN, 'cur_data.lndry_in', 'Amenities', 'Laundry Area - Inside'),
			PDX_Attribute::create('lndry_gar', PDX_BOOLEAN, 'cur_data.lndry_gar', 'Amenities', 'Laundry Area - Garage'),
			PDX_Attribute::create('blc_deck_pt', PDX_BOOLEAN, 'cur_data.blc_deck_pt', 'Amenities', 'Balcony/Deck/Patio'),
			PDX_Attribute::create('yard', PDX_BOOLEAN, 'cur_data.yard', 'Amenities', 'Yard'),
			PDX_Attribute::create('swm_pool', PDX_BOOLEAN, 'cur_data.swm_pool', 'Amenities', 'Swimming Pool'),
			PDX_Attribute::create('jacuzzi', PDX_BOOLEAN, 'cur_data.jacuzzi', 'Amenities', 'Jacuzzi/Whirlpool'),
			PDX_Attribute::create('sauna', PDX_BOOLEAN, 'cur_data.sauna', 'Amenities', 'Sauna'),
			PDX_Attribute::create('cble_rdy', PDX_BOOLEAN, 'cur_data.cble_rdy', 'Amenities', 'Cable-ready'),
			PDX_Attribute::create('hghspd_net', PDX_BOOLEAN, 'cur_data.hghspd_net', 'Amenities', 'High-speed Internet'),

			// Local Points of Interest
			PDX_Attribute::create('ngb_trans', PDX_BOOLEAN, 'cur_data.ngb_trans', 'Local', 'Public Transportation'),
			PDX_Attribute::create('ngb_shop', PDX_BOOLEAN, 'cur_data.ngb_shop', 'Local', 'Shopping'),
			PDX_Attribute::create('ngb_pool', PDX_BOOLEAN, 'cur_data.ngb_pool', 'Local', 'Swimming Pool'),
			PDX_Attribute::create('ngb_court', PDX_BOOLEAN, 'cur_data.ngb_court', 'Local', 'Tennis Court'),
			PDX_Attribute::create('ngb_park', PDX_BOOLEAN, 'cur_data.ngb_park', 'Local', 'Park'),
			PDX_Attribute::create('ngb_trails', PDX_BOOLEAN, 'cur_data.ngb_trails', 'Local', 'Walk/Jog Trails'),
			PDX_Attribute::create('ngb_stbles', PDX_BOOLEAN, 'cur_data.ngb_stbles', 'Local', 'Stables'),
			PDX_Attribute::create('ngb_golf', PDX_BOOLEAN, 'cur_data.ngb_golf', 'Local', 'Golf Courses'),
			PDX_Attribute::create('ngb_med', PDX_BOOLEAN, 'cur_data.ngb_med', 'Local', 'Medical Facilities'),
			PDX_Attribute::create('ngb_bike', PDX_BOOLEAN, 'cur_data.ngb_bike', 'Local', 'Bike Path'),
			PDX_Attribute::create('ngb_cons', PDX_BOOLEAN, 'cur_data.ngb_cons', 'Local', 'Conservation Area'),
			PDX_Attribute::create('ngb_hgwy', PDX_BOOLEAN, 'cur_data.ngb_hgwy', 'Local', 'Highway Access'),
			PDX_Attribute::create('ngb_mar', PDX_BOOLEAN, 'cur_data.ngb_mar', 'Local', 'Marina'),
			PDX_Attribute::create('ngb_pvtsch', PDX_BOOLEAN, 'cur_data.ngb_pvtsch', 'Local', 'Private School'),
			PDX_Attribute::create('ngb_pubsch', PDX_BOOLEAN, 'cur_data.ngb_pubsch', 'Local', 'Public School'),
			PDX_Attribute::create('ngb_uni', PDX_BOOLEAN, 'cur_data.ngb_uni', 'Local', 'University'),

			// Commercial Lease Info
			PDX_Attribute::create('loc_desc', PDX_LONG_TEXT, 'cur_data.loc_desc', 'Commercial', 'Location Description'),
			PDX_Attribute::create('zone_desc', PDX_LONG_TEXT, 'cur_data.zone_desc', 'Commercial', 'Zoning Description'),
			PDX_Attribute::create('spc_ava', PDX_NUMERIC, 'cur_data.spc_ava', 'Commercial', 'Space Available (Sqft)'),
			PDX_Attribute::create('min_div', PDX_NUMERIC, 'cur_data.min_div', 'Commercial', 'Minimum Divisible'),
			PDX_Attribute::create('max_cont', PDX_NUMERIC, 'cur_data.max_cont', 'Commercial', 'Maximum Contiguous'),
			PDX_Attribute::create('lse_type', PDX_TEXT_VALUE, 'cur_data.lse_type', 'Commercial', 'Lease Type'),
			PDX_Attribute::create('comm_rate_unit', PDX_TEXT_VALUE, 'cur_data.comm_rate_unit', 'Commercial', 'Rental Rate'),
			PDX_Attribute::create('sublse', PDX_BOOLEAN, 'cur_data.sublse', 'Commercial', 'Sublease'),
			PDX_Attribute::create('bld_st', PDX_BOOLEAN, 'cur_data.bld_st', 'Commercial', 'Build to Suit'),

			// Vacation Rental Info
			PDX_Attribute::create('accoms', PDX_LONG_TEXT, 'cur_data.accoms', 'Vacation', 'Accomodates'),
			PDX_Attribute::create('avail_info', PDX_LONG_TEXT, 'cur_data.avail_info', 'Vacation', 'Availability'),

			// Attribution
			PDX_Attribute::create_searchable('aid', PDX_TEXT_VALUE, 'rets.aid', 'Attribution', 'Agent ID'),
			PDX_Attribute::create_accessible('aname', PDX_SHORT_TEXT, 'rets.aname', 'Attribution', 'Agent Name'),
			PDX_Attribute::create_accessible('aphone', PDX_SHORT_TEXT, 'rets.aphone', 'Attribution', 'Agent Phone'),
			PDX_Attribute::create_accessible('alicense', PDX_SHORT_TEXT, 'rets.alicense', 'Attribution', 'Agent License'),
			PDX_Attribute::create_searchable('oid', PDX_TEXT_VALUE, 'rets.oid', 'Attribution', 'Office ID'),
			PDX_Attribute::create_accessible('oname', PDX_SHORT_TEXT, 'rets.oname', 'Attribution', 'Office Name'),
			PDX_Attribute::create_accessible('ophone', PDX_SHORT_TEXT, 'rets.ophone', 'Attribution', 'Office Phone'),

			// Co-attribution
			PDX_Attribute::create_searchable('acoid', PDX_TEXT_VALUE, 'rets.acoid', 'Co-attribution', 'Co-agent ID'),
			PDX_Attribute::create_accessible('aconame', PDX_SHORT_TEXT, 'rets.aconame', 'Co-attribution', 'Co-agent Name'),
			PDX_Attribute::create_accessible('acophone', PDX_SHORT_TEXT, 'rets.acophone', 'Co-attribution', 'Co-agent Phone'),
			PDX_Attribute::create_accessible('acolicense', PDX_SHORT_TEXT, 'rets.acolicense', 'Co-attribution', 'Co-agent License'),
			PDX_Attribute::create_searchable('ocoid', PDX_TEXT_VALUE, 'rets.ocoid', 'Co-attribution', 'Co-office ID'),
			PDX_Attribute::create_accessible('oconame', PDX_SHORT_TEXT, 'rets.oconame', 'Co-attribution', 'Co-office Name'),
			PDX_Attribute::create_accessible('ocophone', PDX_SHORT_TEXT, 'rets.ocophone', 'Co-attribution', 'Co-office Phone'));

		// turn the array into an associative array with names as the index values
		return array_combine(array_map(function ($attribute) { return $attribute->name; }, $attributes), $attributes);
	}
}

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