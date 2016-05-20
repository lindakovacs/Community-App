<?php 


require_once('listing-interface.php');
require_once('listing-legacy.php');
require_once('listing-wordpress.php');


class PL_Listing extends PL_WordPress_Listing {};


// v2/2.1 ruby core API
global $PL_API_SERVER;
global $PL_API_LISTINGS;

// check for specialized feed configuration
$k = get_option('placester_api_key', null);
$f = dirname(__FILE__) . '/listings-' . $k . '.php';
if(is_file($f)) include $f;

if(!$PL_API_LISTINGS) {
	$PL_API_LISTINGS = array(
		'get' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2.1/listings",
				'type' => 'GET'
			),
			'args' => array(
				'listing_ids'  => array(),
				'zoning_types' => array(
					'attr_type' => 'text',
					'label' => 'Zoning Type',
					'type' => 'select',
					'group' => 'listing types',
					'options' => array(
						'false' => 'Any',
						'residential' => 'Residential',
						'commercial' => 'Commercial'
					)
				),
				'purchase_types' => array(
					'attr_type' => 'text',
					'label' => 'Purchase Type',
					'type' => 'select',
					'group' => 'listing types',
					'bound' => array(
						'class' => 'PL_Listing_Helper',
						'method' => 'types_for_options',
						'params' => array(false, false, 'purchase_types')
					)
				),
				'property_type'  => array(
					'attr_type' => 'text',
					'multi' => '1',
					'label' => 'Property Type',
					'type' => 'select',
					'group' => 'listing types',
					'bound' => array(
						'class' => 'PL_Listing_Helper',
						'method' => 'types_for_options',
						'params' => array(false, false, 'property_type')
					)
				),

				'building_id' => array(),

				'location' => array(
					'address'  => array(),
					'unit' => array(),

					'region'  => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'State (Region)',
						'type' => 'select',
						'group' => 'location',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'locations_for_options',
							'params' => array('region', false, false)
						)
					),
					'locality'  => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'City (Locality)',
						'type' => 'select',
						'group' => 'location',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'locations_for_options',
							'params' => array('locality', false, false)
						)
					),
					'postal' => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'Zip (Postal)',
						'type' => 'select',
						'group' => 'location',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'locations_for_options',
							'params' => array('postal', false, false)
						),
					),
					'neighborhood'  => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'Neighborhood',
						'type' => 'select',
						'group' => 'location',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'locations_for_options',
							'params' => array('neighborhood', false, false)
						)
					),
					'county'  => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'County',
						'type' => 'select',
						'group' => 'location',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'locations_for_options',
							'params' => array('county', false, false)
						)
					),
				),

				'metadata' => array(
					'status' => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'Status',
						'type' => 'select',
						'group' => 'listing types',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'types_for_options',
							'params' => array(false, false, 'cur_data.status')
						)
					),

					'price' => array(
						'attr_type' => 'int',
						'label' => 'Price',
						'type' => 'text',
						'group' => 'basic'
					),
					'beds' => array(
						'attr_type' => 'int',
						'label' => 'Beds',
						'type' => 'text',
						'group' => 'basic'
					),
					'baths' => array(
						'attr_type' => 'int',
						'label' => 'Baths',
						'type' => 'text',
						'group' => 'basic'
					),
					'half_baths' => array(
						'attr_type' => 'int',
						'label' => 'Half Baths',
						'type' => 'text',
						'group' => 'basic'
					),
					'sqft' => array(
						'attr_type' => 'int',
						'label' => 'Sqft',
						'type' => 'text',
						'group' => 'basic',
					),

					'desc' => array(
						'attr_type' => 'text',
						'label' => 'Description',
						'type' => 'text',
						'group' => 'advanced',
					),
					'lst_dte' => array(
						'attr_type' => 'date',
						'label' => 'List Date',
						'label_max' => 'Latest List Date',
						'label_min' => 'Earliest List Date',
						'type' => 'date',
						'group' => 'advanced',
					),
					'dom' => array(
						'attr_type' => 'int',
						'label' => 'Days on Market',
						'type' => 'text',
						'group' => 'advanced',
					),

					'lt_sz' => array(
						'attr_type' => 'int',
						'label' => 'Lot Size',
						'type' => 'text',
						'group' => 'basic',
					),

					'sch_elm' => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'Elementary School',
						'type' => 'select',
						'group' => 'schools',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'types_for_options',
							'params' => array(false, false, 'cur_data.sch_elm')
						)
					),
					'sch_jnr' => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'Middle School',
						'type' => 'select',
						'group' => 'schools',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'types_for_options',
							'params' => array(false, false, 'cur_data.sch_jnr')
						)
					),
					'sch_hgh' => array(
						'attr_type' => 'text',
						'multi' => '1',
						'label' => 'High School',
						'type' => 'select',
						'group' => 'schools',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'types_for_options',
							'params' => array(false, false, 'cur_data.sch_hgh')
						)
					),
				),

				'custom' => array(
					'type' => 'bundle',
					'group' => '',
					'id' => 'custom',
					'bound' => array(
						'class' => 'PL_Listing_Helper',
						'method' => 'custom_attributes',
					)
				),

				'rets' => array(
					'mls_id' => array(
						'attr_type' => 'text',
						'label' => 'MLS Listing ID',
						'type' => 'text',
						'group' => 'advanced',
					),
					'aid' => array(
						'attr_type' => 'text',
						'label' => 'MLS Agent ID',
						'type' => 'text',
						'group' => 'advanced',
					),
					'oid' => array(
						'attr_type' => 'text',
						'label' => 'MLS Office ID',
						'type' => 'text',
						'group' => 'advanced',
					),
				),

				'agency_only' => array('type' => 'checkbox', 'group' => 'listing types', 'label' => 'Office Listings'),
				'non_import' => array('type' => 'checkbox',  'group' => 'listing types', 'label' => 'Private Listings'),
				'include_disabled' => array('type' => 'checkbox', 'group' => 'listing types','label' => 'Inactive Listings'),

				'total_images' => array(),
				'address_mode' => array(),

				'box' => array(
					'min_latitude' => array(),
					'min_longitude' => array(),
					'max_latitude' => array(),
					'max_longitude' => array()
				),

				'limit' => array(),
				'offset' => array(),
				'sort_by' => array(),
				'sort_type' => array()
			),
			'returns' => array(
				'id' => false,
				'compound_type' => false,
				'property_type' => array(),
				'zoning_types' => array(),
				'purchase_types' => array(),
				'listing_types' => false,
				'building_id' => false,
				'cur_data' => array(
					'half_baths' => false,
					'price' => false,
					'sqft' => false,
					'baths' => false,
					'avail_on' => false,
					'lst_dte' => false,
					'beds' => false,
					'url' => false,
					'desc' => false,
					'lt_sz' => false,
					'ngb_shop' => false,
					'ngb_hgwy' => false,
					'grnt_tops' => false,
					'ngb_med' => false,
					'ngb_trails' => false,
					'cent_ht' => false,
					'pk_spce' => false,
					'air_cond' => false,
					'price_unit' => false,
					'lt_sz_unit' => false,
					'lse_trms' => false,
					'ngb_trans' => false,
					'off_den' => false,
					'frnshed' => false,
					'refrig' => false,
					'deposit' => false,
					'ngb_pubsch' => false
				),
				'uncur_data' => false,
				'location' => array(
					'address' => false,
					'locality' => false,
					'region' => false,
					'postal' => false,
					'neighborhood' => false,
					'county' => false,
					'country' => false,
					'coords' => array(
						'latitude' => false,
						'longitude' => false
					)
				),
				'contact' => array(
					'email' => false,
					'phone' => false
				),
				'images' => false,
				'tracker_url' => false,
				'rets' => array(
					'aname' => false,
					'oname' => false,
					'mls_id' => false
				)
			)
		),
		'create' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2/listings",
				'type' => 'POST'
			),
			'args' => array(
				'compound_type' => array(
					'attr_type' => 'text',
					'multi' => '1',
					'label' => 'Listing Type',
					'type' => 'select',
					'group' => 'Basic Details',
					'options' => array(
						'false' => 'Not Set',
						'res_sale' => 'Residential Sale',
						'res_rental' => 'Residential Rental',
						'vac_rental' => 'Vacation Rental',
						'park_rental' => 'Parking',
						'comm_rental' => 'Commercial Rental',
						'comm_sale' => 'Commercial Sale',
						'sublet' => 'Sublet'
					)
				),
				'property_type' => array(
					'type' => 'text',
					'label' => 'Property Type',
					'group' => 'Basic Details'
				),
				'location' => array(
					'address' => array('type' => 'text','group' => 'location', 'label' => 'Address'),
					'locality'  => array('type' => 'text','group' => 'location', 'label' => 'City'),
					'region'  => array('type' => 'text','group' => 'location', 'label' => 'State'),
					'postal' => array('type' => 'text','group' => 'location', 'label' => 'Zip Code'),
					'unit'  => array('type' => 'text','group' => 'location', 'label' => 'Unit'),
					'neighborhood'  => array('type' => 'text','group' => 'location', 'label' => 'Neighborhood'),
					'county' => array('type' => 'text','group' => 'location', 'label' => 'County'),
					'country'  => array(
						'type' => 'select',
						'group' => 'location',
						'label' => 'Country',
						'bound' => array(
							'class' => 'PL_Listing_Helper',
							'method' => 'supported_countries',
							'default' => array('PL_Listing_Helper','convert_default_country')
						)
					)
				),
				// // binds to keys / values of all attributes (cur + uncur)
				'metadata' => array(
					//comm_rental
					'prop_name' => array('type' => 'text','group' => 'basic details', 'label' => 'Property Name'),
					'cons_stts' => array('type' => 'select','options' => array('exstng' => 'Existing', 'und_prop' => 'Under Construction / Proposed'), 'group' => 'basic details', 'label' => 'Construction Status'),
					'bld_suit' => array('type' => 'checkbox','group' => 'basic details', 'label' => 'Built to Suit'),
					'min_div' => array('type' => 'text','group' => 'building details', 'label' => 'Minimum Divisible'),
					'max_cont' => array('type' => 'text','group' => 'building details', 'label' => 'Maximum Contiguous'),
					'bld_sz' => array('type' => 'text','group' => 'building details', 'label' => 'Total Building Size'),
					'bld_sz' => array('type' => 'text','group' => 'building details', 'label' => 'Total Building Size'),
					//res_rental
					'beds' => array('type' => 'text','group' => 'basic details', 'label' => 'Bedrooms'),
					'baths' => array('type' => 'text', 'group' => 'basic details', 'label' => 'Bathrooms'),
					'half_baths' => array('type' => 'text', 'group' => 'basic details', 'label' => 'Half Bathrooms'),
					'price' => array('type' => 'text', 'group' => 'lease details', 'label' => 'Price'),
					'sqft' => array('type' => 'text', 'group' => 'basic details', 'label' => 'Square Feet'),
					'avail_on' => array('type' => 'date', 'group' => 'basic details', 'label' => 'Available On'),
					'desc' => array('type' => 'textarea', 'group' => 'description', 'label' => 'Description'),
					//rentals
					'lse_trms' => array('type' => 'select', 'options' => array('false' => 'Not Set', 'per_mnt' => 'Per Month','per_ngt' => 'Per Night', 'per_wk' => 'Per Week', 'per_yr' => 'Per Year'), 'group' => 'Transaction Details','label' => 'Lease Terms'),
					'lse_type' => array('type' => 'select', 'options' => array('false' => 'Not Set', 'ind_grs' => 'Full Service','ind_grs' => 'Industrial Gross', 'mod_grs' => 'Modified Gross', 'mod_net' => 'Modified Net', 'na' => 'N/A', 'other' => 'Other' ), 'group' => 'Transaction Details','label' => 'Lease Type'),
					'sublse' => array('type' => 'checkbox', 'group' => 'Transaction Details','label' => 'Sublease'),
					'rate_unit' => array('type' => 'select', 'options' => array('false' => 'Not Set', 'amt_mnt' => 'Amount/Month','amt_yr' => 'Amount/Year', 'sf_mnt' => 'Sqft/Month', 'sf_yr' => 'Sqft/Year'), 'group' => 'Transaction Details','label' => 'Rental Rate'),
					//General
					'lt_sz' => array('type' => 'text', 'group' => 'Lot Details', 'label' => 'Lot Size'),
					'lt_sz_unit' => array('type' => 'select','options' => array('false' => 'Not Set', 'acres' => 'Acres', 'sqft' => 'Square Feet'), 'group' => 'Lot Details', 'label' => 'Lot Unit Type'),
					'year_blt' => array('type' => 'text', 'group' => 'Lot Details', 'label' => 'Year Built'),
					'pk_spce' => array('type' => 'text', 'group' => 'basic details', 'label' => 'Parking Spaces'),
					'park_type' => array('type' => 'select','options' => array('false' => 'Not Set','atch_gar' => 'Attached Garage', 'cov' => 'Covered', 'dtch_gar' => 'Detached Garage', 'off_str' => 'Off-street', 'strt' => 'On-street', 'tan' => 'Tandem'), 'group' => 'basic details', 'label' => 'Parking Type'),
					'pk_lease' => array('type' => 'checkbox', 'group' => 'lease details', 'label' => 'Parking Included'),
					'deposit' => array('type' => 'text', 'group' => 'Transation Details', 'label' => 'Deposit'),
					'floors' => array('type' => 'text', 'group' => 'basic Details', 'label' => 'Floors'),
					'hoa_mand' => array('type' => 'checkbox', 'group' => 'financial details', 'label' => 'HOA Mandatory'),
					'hoa_fee' => array('type' => 'text', 'group' => 'financial details', 'label' => 'HOA Fee'),
					'lndr_own' => array('type' => 'select','options' => array('false' => 'Not Set','true' => 'Yes', 'false' => 'No', 'undis' => 'Undisclosed'), 'group' => 'financial details', 'label' => 'Floors'),
					'style' => array('type' => 'select','options' => array('false' => 'Not Set','bungal' => 'Bungalow', 'cape' => 'Cape Cod', 'colonial' => 'Colonial' ,'contemp' => 'Contemporary', 'cott' => 'Cottage', 'farmh' => 'Farmhouse','fnt_bk_splt' => 'Front to Back Split', 'gamb_dutc'=>'Gambrel/Dutch','garrison' => 'Garrison', 'greek_rev' => 'Greek Revival', 'loft_splt' => 'Lofted Split','mult_lvl' => 'Multi-level','rai_ranch' => 'Raised Ranch','ranch' => 'Ranch','saltb' => 'Saltbox', 'split_ent' => 'Split Entry', 'tudor' => 'Tudor', 'victor' => 'Victorian', 'antiq' => 'Antique'), 'group' => 'structure details', 'label' => 'Style'),
					//Pet Details
					'cats' => array('type' => 'checkbox', 'group' => 'Pets', 'label' => 'Cats'),
					'dogs' => array('type' => 'checkbox', 'group' => 'Pets', 'label' => 'Dogs'),
					'cond' => array('type' => 'checkbox', 'group' => 'Pets', 'label' => 'Conditional'),
					//Vacation
					'accoms' => array('type' => 'textarea', 'group' => 'basic details', 'label' => 'Accomodates Description'),
					'avail_info' => array('type' => 'textarea', 'group' => 'availability details', 'label' => 'Availability Description'),
					//Parking Amenities
					'valet' => array('type' => 'checkbox', 'group' => 'Amenities', 'label' => 'Valet'),
					'guard' => array('type' => 'checkbox', 'group' => 'Amenities', 'label' => 'Guarded'),
					'heat' => array('type' => 'checkbox', 'group' => 'Amenities', 'label' => 'Heated'),
					'carwsh' => array('type' => 'checkbox', 'group' => 'Amenities', 'label' => 'Carwash'),
					//Neighborhood Amenities
					'ngb_trans' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Public Transportation'),
					'ngb_shop' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Shopping'),
					'ngb_swim' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Swimming Pool'),
					'ngb_court' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Tennis Court'),
					'ngb_park' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Park'),
					'ngb_trails' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Walk/Jog Trails'),
					'ngb_stbles' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Stables'),
					'ngb_golf' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Golf Courses'),
					'ngb_med' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Medical Facilities'),
					'ngb_bike' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Bike Path'),
					'ngb_cons' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Conservation Area'),
					'ngb_hgwy' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Highway Access'),
					'ngb_mar' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Marina'),
					'ngb_pvtsch' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Private School'),
					'ngb_pubsch' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'Public School'),
					'ngb_uni' => array('type' => 'checkbox', 'group' => 'Neighborhood Amenities', 'label' => 'University'),
					//Listing Amenities
					'grnt_tops' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Granite Countertops'),
					'air_cond' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Air Conditioning'),
					'cent_ac' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Central AC'),
					'frnshed' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Furnished'),
					'cent_ht' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Central Heat'),
					'frplce' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Fireplace'),
					'hv_ceil' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'High/Vaulted Ceiling'),
					'wlk_clst' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Walk-in Closet'),
					'hdwdflr' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Hardwood Floor'),
					'tle_flr' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Tile Floor'),
					'fm_lv_rm' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Family/Living Room'),
					'bns_rec_rm' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Bonus/Rec Room'),
					'lft_lyout' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Loft Layout'),
					'off_den' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Office/Den'),
					'dng_rm' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Dining Room'),
					'brkfst_nk' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Breakfast Nook'),
					'dshwsher' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Dishwasher'),
					'refrig' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Refrigerator'),
					'stve_ovn' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Stove/Oven'),
					'stnstl_app' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Stainless Steel Appliances'),
					'attic' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Attic'),
					'basemnt' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Basement'),
					'washer' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Washer'),
					'dryer' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Dryer'),
					'lndry_in' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Laundry Area - Inside'),
					'lndry_gar' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Laundry Area - Garage'),
					'blc_deck_pt' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Balcony/Deck/Patio'),
					'yard' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Yard'),
					'swm_pool' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Swimming Pool'),
					'jacuzzi' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Jacuzzi'),
					'sauna' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Sauna'),
					'cble_rdy' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'Cable-ready'),
					'hghspd_net' => array('type' => 'checkbox', 'group' => 'Listing Amenities', 'label' => 'High-speed Internet'),
				),
				'uncur_data' => array(
					'type' => 'bundle',
					'group' => '',
					'bound' => array(
						'class' => 'PL_Listing_Helper',
						'method' => 'custom_attributes',
					)
				),
				'custom_data' => array(
					'type' => 'custom_data',
					'group' => 'Custom Amenities'
				),
				'images' => array(
					'type' => 'image',
					'group' => 'Upload Images',
					'label' => 'Select Files'
				)
			),
			'returns' => array(
			)
		),
		'temp_image' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2/listings/media/temp/image",
				'type' => 'POST'
			),
			'args' => array(
				'file'
			),
			'returns' => array()
		),
		'update' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2/listings",
				'type' => 'PUT'
			),
			'args' => array(),
			'returns' => array()
		),
		'delete' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2/listings",
				'type' => 'DELETE'
			),
			'args' => array(
				'id' => array()
			),
			'returns' => array()
		),
		'get.locations' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2/listings/locations/",
				'type' => 'GET'
			),
			'args' => array(
				'include_disabled' => array(
					'type' => 'checkbox'
				)
			),
			'returns' => array(
				'postal' => array(),
				'region'  => array(),
				'locality' => array(),
				'neighborhood' => array(),
				'county' => array(),
				'neighborhood_polygons' => array()
			)
		),
		'get.aggregate' => array(
			'request' => array(
				'url' => "$PL_API_SERVER/v2.1/listings/aggregate/",
				'type' => 'GET'
			),
			'args' => array(
				'keys' => array()
			),
			'returns' => array()
		)
	);
}
