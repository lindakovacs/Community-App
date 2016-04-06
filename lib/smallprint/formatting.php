<?php

class PLS_Format
{

	public static function phone($phone, $options = '')
	{
		$new_phone = '';

		/** Define the default argument array. */
		$defaults = array(
			'format' => 'hyphens',
			'html_args' => array()
		);

		/** Merge the arguments with the defaults. */
		$options = wp_parse_args($options, $defaults);

		extract($options, EXTR_SKIP);

		if (self::validate_number($phone)) {
			$phone_parts = self::process_phone_parts($phone);

			switch ($format) {
				case 'hyphens':
					$new_phone = self::format_phone($phone_parts, '-');
					break;

				case 'spaces':
					$new_phone = self::format_phone($phone_parts, ' ');
					break;

				case 'as_html':
					$new_phone = self::format_phone_html($phone_parts, $html_args);
					break;

				default:
					# code...
					break;
			}

		}

		return $new_phone;

	}

	public static function listing_price($listing, $args)
	{

		if (is_array($listing)) {

			$price = self::number($listing['price'], $args);

			if (isset($listing['purchase_types']) && ($listing['purchase_types'][0] == 'rental')) {
				$price .= "/month";
			}

			return $price;

		} elseif (is_object($listing)) {

			$price = self::number($listing->price, $args);

			if (isset($listing->purchase_types) && ($listing->purchase_types[0] == 'rental')) {
				$price .= "/month";
			}

			return $price;
		}


		return "";

	}

	// formats the given number based on the supplied options. 
	public static function number($number, $options = '')
	{

		// Handle specific values AND types (this makes sure to allow '0' as a string and 0 as an integer)
		if ($number === '' || $number === false || $number === null) {
			return '';
		}

		$formatted_number = false;

		/** Define the default argument array. */
		$defaults = array(
			'add_commas' => true,
			'add_currency_sign' => true,
			'abbreviate' => true
		);

		/** Merge the arguments with the defaults. */
		$options = wp_parse_args($options, $defaults);

		//given a number, properly insert commas. 
		if ($options['add_commas']) {
			$formatted_number = number_format($number);
		}

		if ($options['abbreviate']) {
			$formatted_number = self::abbreviate_number($formatted_number);
		}

		//insert $ sign.
		if ($options['add_currency_sign']) {
			$formatted_number = pls_get_currency_symbol() . $formatted_number;
		}

		return $formatted_number;

	}

	public static function abbreviate_number($number)
	{
		$abbreviated_number = false;


		if (!strpos((string)$number, ',')) {
			$formatted_number = number_format($number);
		}
		// Force length intellegently
		$number_blocks = explode(',', $number);

		$block_length = count($number_blocks);

		switch ($block_length) {
			case 1:
				$abbreviated_number = $number;
				break;

			case 2:
				$b = $number[2];
				if ($b == 0) {
					$abbreviated_number = $number_blocks[0] . 'K';
				} else {
					$abbreviated_number = $number_blocks[0] . '.' . $b . 'K';
				}
				break;

			case 3:
				$b = $number[2];
				if ($b == 0) {
					$abbreviated_number = $number_blocks[0] . 'M';
				} else {
					$abbreviated_number = $number_blocks[0] . '.' . $b . 'M';
				}
				break;

			default:
				$abbreviated_number = $number;
				break;
		}

		return $abbreviated_number;
	}

	private static function validate_number(&$phone)
	{

		//placester api dumps a + in there. 		
		if (substr($phone, 0, 1) == '+') {
			$phone = substr($phone, 1);
		}


		if (is_numeric($phone)) {

			// test if there's a 1 prepended.
			if (strlen($phone) === 11 && $phone[0] == 1) {
				//pop 1 off. 
				$phone = substr($phone, 1);
				return true;

			} elseif (strlen($phone) === 10) {
				// it's a 10 digit number... I guess that works.
				return true;
			}
		}

		// is invalid if no return by here. 
		return false;
	}

	private static function process_phone_parts($phone)
	{

		$phone_parts = array();
		//area code in states
		$phone_parts[] = substr($phone, 0, 3);
		//next 3
		$phone_parts[] = substr($phone, 3, 3);
		// next 4
		$phone_parts[] = substr($phone, 6, 4);

		return $phone_parts;
	}

	private static function format_phone($phone_parts, $delimiter = '')
	{

		$new_phone = '';

		foreach ($phone_parts as $key => $part) {
			if ($key == 0) {
				$new_phone .= $part;
			} else {
				$new_phone .= $delimiter . $part;
			}
		}

		return $new_phone;
	}

	private static function format_phone_html($phone_parts, $args)
	{
		$new_phone = '';

		/** Define the default argument array. */
		$defaults = array(
			'wrapper' => '',
			'area_wrapper' => '',
			'three_wrapper' => '',
			'four_wrapper' => '',
			'delimiter' => ' '
		);

		/** Merge the arguments with the defaults. */
		$options = wp_parse_args($args, $defaults);

		extract($options, EXTR_SKIP);

		if ($area_wrapper != '') {
			$new_phone .= pls_h($area_wrapper, $phone_parts[0]);
		} else {
			$new_phone .= $phone_parts[0];
		}

		if ($three_wrapper != '') {
			$new_phone .= pls_h($three_wrapper, $phone_parts[1]);
		} else {
			$new_phone .= $delimiter . $phone_parts[1];
		}

		if ($four_wrapper != '') {
			$new_phone .= pls_h($four_wrapper, $phone_parts[2]);
		} else {
			$new_phone .= $delimiter . $phone_parts[2];
		}

		if ($wrapper != '') {
			$new_phone = pls_h($wrapper, $new_phone);
		}

		return $new_phone;
	}

	public static function translate_region($region)
	{

		// apply correct case to region to match library
		$region = ucwords($region);

		$region_library = array('AL' => "Alabama", 'AK' => "Alaska", 'AZ' => "Arizona", 'AR' => "Arkansas", 'CA' => "California", 'CO' => "Colorado", 'CT' => "Connecticut", 'DE' => "Delaware", 'FL' => "Florida", 'GA' => "Georgia", 'HI' => "Hawaii", 'ID' => "Idaho", 'IL' => "Illinois", 'IN' => "Indiana", 'IA' => "Iowa", 'KS' => "Kansas", 'KY' => "Kentucky", 'LA' => "Louisiana", 'ME' => "Maine", 'MD' => "Maryland", 'MA' => "Massachusetts", 'MI' => "Michigan", 'MN' => "Minnesota", 'MS' => "Mississippi", 'MO' => "Missouri", 'MT' => "Montana", 'NE' => "Nebraska", 'NV' => "Nevada", 'NH' => "New Hampshire", 'NJ' => "New Jersey", 'NM' => "New Mexico", 'NY' => "New York", 'NC' => "North Carolina", 'ND' => "North Dakota", 'OH' => "Ohio", 'OK' => "Oklahoma", 'OR' => "Oregon", 'PA' => "Pennsylvania", 'RI' => "Rhode Island", 'SC' => "South Carolina", 'SD' => "South Dakota", 'TN' => "Tennessee", 'TX' => "Texas", 'UT' => "Utah", 'VT' => "Vermont", 'VA' => "Virginia", 'WA' => "Washington", 'DC' => "Washington D.C.", 'WV' => "West Virginia", 'WI' => "Wisconsin", 'WY' => "Wyoming");

		// If correct 2 letter abbreviation is set, leave it
		if (isset($region_library[$region]) && !empty($region_library[$region])) {
			return $region;
		}

		// Find state name
		foreach ($region_library as $abbrev => $name) {
			if ($region == $name) {
				return $abbrev;
			}
		}

		// if the region doesn't match ones of our states, just return it
		return $region;
	}

	public static function translate_property_type($listing)
	{

		if (isset($listing['cur_data']['prop_type']) && ($listing['cur_data']['prop_type'] != null) && !empty($listing['cur_data']['prop_type'])) {

			if ($listing['cur_data']['prop_type'] == "fam_home") {
				$property_type = 'Single Family Home';
			} elseif ($listing['cur_data']['prop_type'] == "multi_fam") {
				$property_type = 'Multi Family Home';
			} else {

				if (is_array($listing['cur_data']['prop_type'])) {
					$property_type = ucwords(implode($listing['cur_data']['prop_type'], ''));
				} else {
					$property_type = ucfirst($listing['cur_data']['prop_type']);
				}

			}
			return $property_type;
		}
	}

	public static function amenities_but($listing_data, $amenities_to_remove)
	{
		$amenities = array();
		$amenities['ngb'] = array();
		$amenities['list'] = array();

		// Prevent fake data handling when there is no valid listing
		if (is_null($listing_data)) {
			return $amenities;
		}

		if (is_array($listing_data['cur_data'])) {
			foreach ($listing_data['cur_data'] as $amenity => $value) {
				if (empty($value)) {
					continue;
				}
				if (in_array($amenity, $amenities_to_remove)) {
					continue;
				}
				if (is_int(strrpos((string)$amenity, 'ngb'))) {
					if ($value) {
						$amenities['ngb'][$amenity] = ' ' . $value;
					}
				} else {
					if ($value) {
						$amenities['list'][$amenity] = ' ' . $value;
					}
				}
			}
		}

		if (is_array($listing_data['uncur_data'])) {
			$amenities['uncur'] = array();
			foreach ($listing_data['uncur_data'] as $uncur_amenity => $uncur_value) {
				if (empty($uncur_value)) {
					continue;
				}
				if (in_array($uncur_amenity, $amenities_to_remove)) {
					continue;
				}
				$amenities['uncur'][$uncur_amenity] = $uncur_value;
			}
		}

		return $amenities;
	}

	public static function translate_lease_terms($listing, $term = 'price_unit')
	{
		$lease_terms = array(
			'per_yr' => 'per year',
			'per_wk' => 'per week',
			'per_mnt' => 'per month',
			'per_ngt' => 'per night'
		);

		$translate_this = $listing['cur_data'][$term];

		$translated = '';
		if ($translate_this != null) {
			foreach ($lease_terms as $key => $value) {
				if ($key == $translate_this) {
					$translated = $value;
					return $translated;
				}
			}
			return $translated;
		}
	}

	public static function translate_amenities($amenities)
	{

		$local_dictionary = array(
			'half_baths' => 'Half Baths',
			'price' => 'Price',
			'min_price' => 'Min Price',
			'max_price' => 'Max Price',
			'price_range' => 'Price Range',
			'sqft' => 'Square Feet',
			'baths' => 'Baths',
			'avail_on' => 'Available On',
			'cons_stts' => 'Construction Status',
			'beds' => 'Beds',
			'url' => 'Link',
			'desc' => 'Description',
			'lt_sz' => 'Lot Size',
			'ngb_shop' => 'Local Shopping',
			'ngb_hgwy' => 'Local Highway Access',
			'grnt_tops' => 'Granite Counter Tops',
			'ngb_med' => 'Local Medical Facilities',
			'ngb_trails' => 'Local Walk/Jog Trails',
			'cent_ht' => 'Central Heat',
			'pk_spce' => 'Parking Spaces Included',
			'park_type' => "Parking Type",
			'air_cond' => 'Air Conditioning',
			'air_conditioning' => 'Air Conditioning',
			'price_unit' => 'Unit Price',
			'lt_sz_unit' => 'Unit Lot Size',
			'lse_trms' => 'Lease Terms',
			'lse_type' => "Lease Type",
			'ngb_trans' => 'Local Public Transportation',
			'off_den' => 'Office / Den',
			'frnshed' => 'Furnished',
			'refrig' => 'Refrigerator',
			'deposit' => 'Deposit',
			'ngb_pubsch' => 'Local Public Schools',
			'beds_avail' => 'Beds Available',
			'hoa_fee' => 'Home Owners Assocation Fee',
			'floors' => 'Floors',
			'bns_rec_rm' => 'Basement Rec Room',
			'fm_lv_rm' => 'Family Rec Room',
			'yard' => 'Has Yard',
			'hdwdflr' => 'Hardwood Floors',
			'sauna' => 'Sauna',
			'year_blt' => 'Year Built',
			"oid" => "Office ID",
			"aid" => "Agent ID",
			"mls_id" => "MLS ID",
			"oname" => "Office Name",
			"aname" => "Agent Name",
			"aemail" => "Agent Email",
			"assofee" => "Association Fee",
			"assocation_fee" => "Association Fee",
			"association_fee_per" => "Association Fee Per",
			"garagetype" => "Garage Type",
			"foundlength" => "Foundation Length",
			"foundation_length" => "Foundation Length",
			"foundwidth" => "Foundation Width",
			"foundation_width" => "Foundation Width",
			"assessment" => "Assessment",
			"assessed_value" => "Assessed Value",
			"lender_owned" => "Lender Owned",
			"retaxes" => "Real Estate Taxes",
			"tax_amount" => "Tax Amount",
			"real_estate_tax" => "Real Estate Taxes",
			"lotfrontage" => "Waterfront",
			"lot_frontage" => "Waterfront",
			"taxyear" => "Tax Year",
			"tax_year" => "Tax Year",
			"heat1" => "Heat",
			"room" => "Rooms",
			"rooms" => "Rooms",
			"floor" => "Floors",
			"flooring" => "Flooring",
			"belowgroundlsqf" => "Below Ground Sqft",
			"below_ground_sqft" => "Below Ground Sqft",
			"above_ground_sqft" => "Above Ground Sqft",
			"pricesqft_abv_gr" => "Price/sqft Above Ground",
			"approx_acres" => "Approximate Acres",
			"approx_lot_sqft" => "Approximate Lot Sqft",
			"fire_district_tax" => "Fire District Tax",
			"garage_description" => "Garage Description",
			"garage_spaces" => "Garage Spaces",
			"insulation" => "Insulation",
			"tank_type" => "Tank Type",
			"tank_size" => "Tank Size",
			"number_of_rooms" => "Number of Rooms",
			"energy_features" => "Energy Features",
			"exterior_constr" => "Exterior Construction",
			"occupancyclose" => "Occupancy Close",
			"zone_desc" => "Zone Description",
			"assessment_amount" => "Assessment Amount",
			"assessor_plat" => "Assessor Plat",
			"assessor_lot" => "Assessor Lot",
			"occupancy" => "Occupancy",
			"pets" => "Pets",
			"historic" => "Historic",
			"blc_deck_pt" => "Balcony/Deck/Patio",
			"dryer" => "Dryer",
			"washer" => "Washer",
			"status" => "Status",
			"appliances" => "Appliances",
			"appliances_included" => "Appliances",
			"oid" => "Office ID",
			"aid" => "Agent ID",
			"mls_id" => "MLS ID",
			"oname" => "Office Name",
			"aname" => "Agent Name",
			"aemail" => "Agent Email",
			"assofee" => "Association Fee",
			"garagetype" => "Garage Type",
			"historic" => "Historic",
			"age_years" => "Age in Years",
			"foundation" => "Foundation",
			"foundlength" => "Foundation Length",
			"foundwidth" => "Foundation Width",
			"assessment" => "Assessment",
			"retaxes" => "Real Estate Taxes",
			"lotfrontage" => "Waterfront",
			"taxyear" => "Tax Year",
			"heat1" => "Heat",
			"total_rooms" => "Total Rooms",
			"total_fireplaces" => "Total Fireplaces",
			"total_closets" => "Total Closets",
			"fencing" => "Fencing",
			"construction" => "Construction",
			"floor" => "Floors",
			"occupancy" => "Occupancy",
			"belowgroundlsqf" => "Below Ground Sqft",
			"insulation" => "Insulation",
			"approx_head_cost" => "Approximate Head Cost",
			"approx_lot_sqft" => "4791",
			"cooling" => "Cooling",
			"basement" => "Basement",
			"basemnt" => "Basement",
			"building_levels" => "Building Levels",
			"levels" => "Levels",
			"utility_room" => "Utility Room",
			"room_areas" => "Room Areas",
			"water_heater" => "Water Heater",
			"other_rooms" => "Other Rooms",
			"fence" => "Fence",
			"fireplace_details" => "Fireplace Details",
			"lot_number" => "Lot Number",
			"water_heaters" => "Water Heaters",
			"kitchen_equipment" => "Kitchen Equipment",
			"living_areas" => "Living Areas",
			"electric" => "Electric",
			"first_floor_rooms" => "First Floor Rooms",
			"second_floor_rooms" => "Second Floor Rooms",
			"third_floor_rooms" => "Third Floor Rooms",
			"flooring" => "Flooring",
			"fireplace" => "Fireplace",
			"heating_fuel" => "Heating Fuel",
			"garage_description" => "Garage Description",
			"heat_system" => "Heat System",
			"hot_water" => "Hot Water",
			"living_room_length" => "Living Room Length",
			"list_of_rooms" => "List of Rooms",
			"living_room_width" => "Living Room Width",
			"lot_description" => "Lot Description",
			"lower_level_rooms" => "Lower Level Rooms",
			"master_bedroom_l" => "Master Bedroom Length",
			"master_bedroom_w" => "Master Bedroom Width",
			"number_of_fireplaces" => "Number of Fireplaces",
			"number_of_rooms" => "Number of Rooms",
			"near" => "Near",
			"plumbing" => "Plumbing",
			"pricesqft_abv_gr" => "Price per Sqft Above Ground",
			"tank_size" => "Tank Size",
			"sewer" => "Sewer",
			"tank_type" => "Tank Type",
			"type" => "Type",
			"wall_description" => "Wall Description",
			"water_supply" => "Water Supply",
			"approx_heat_cost" => "Approximate Heat Cost",
			"association_fee" => "Association Fee",
			"exterior" => "Exterior",
			"exterior_features" => "Exterior Features",
			"interior" => "Interior",
			"Interior_features" => "Interior Features",
			"water_amenities" => "Water Amenities",
			"acres" => "Acres",
			"master_bath" => "Master Bath",
			"park_type" => "Parking Type",
			"property_type" => "Property Type",
			"built_to_suit" => "Built to Suit",
			"mobile_homes_allowed" => "Mobile Homes Allowed",
			"basemnt" => "Basement",
			"move_in" => "Move In",
			"bld_name" => "Building Name",
			"tl_earn" => "Total Earned",
			"style" => "Style",
			"company" => "Company",
			"landing_select" => "Advertising Style",
			"bld_suit" => "Build to Suit",
			"cent_ac" => "Central A/C",
			"frplce" => "Fireplace",
			"hv_ceil" => "High/Vaulted Ceiling",
			"wlk_clst" => "Walk-In Closet",
			"tle_flr" => "Tile Floor",
			"lft_lyout" => "Loft Layout",
			"off_den" => "Office/Den",
			"dng_rm" => "Dining Room",
			"brkfst_nk" => "Breakfast Nook",
			"dshwsher" => "Dishwasher",
			"refrig" => "Refrigerator",
			"stve_ovn" => "Stove/Oven",
			"stnstl_app" => "Stainless Steel Appliances",
			"attic" => "Attic",
			"washer" => "Washer",
			"dryer" => "Dryer",
			"lndry_in" => "Laundry Area - Inside",
			"lndry_gar" => "Laundry Area - Garage",
			"blc_deck_pt" => "Balcony/Deck/Patio",
			"yard" => "Yard",
			"swm_pool" => "Swimming Pool",
			"jacuzzi" => "Jacuzzi/Whirlpool",
			"sauna" => "Sauna",
			"cble_rdy" => "Cable-ready",
			"hghspd_net" => "High-speed Internet",
			"lt_sz" => "Lot Size",
			"lt_sz_unit" => "Lot Unit Type",
			"accoms" => "Accommodates",
			"avail_info" => "Availability",
			"hoa_mand" => "HOA Mandatory",
			"hoa_fee" => "HOA Fee",
			"hoa_fee_desc" => "HOA Fee Description",
			"lndr_own" => "Lender Owned",
			"ngb_trans" => "Public Transportation",
			"ngb_shop" => "Shopping",
			"ngb_pool" => "Swimming Pool",
			"ngb_court" => "Tennis Court",
			"ngb_park" => "Park",
			"ngb_trails" => "Walk/Jog Trails",
			"ngb_stbles" => "Stables",
			"ngb_golf" => "Golf Courses",
			"ngb_med" => "Medical Facilities",
			"ngb_bike" => "Bike Path",
			"ngb_cons" => "Conservation Area",
			"ngb_hgwy" => "Highway Access",
			"ngb_mar" => "Marina",
			"ngb_pvtsch" => "Private School",
			"ngb_pubsch" => "Public School",
			"school_district" => "School District",
			"sch_dist" => "School District",
			"sch_elm" => "Elementary School",
			"sch_hgh" => "High School",
			"sch_jnr" => "Junior High School",
			"zone" => "Zone",
			"road_surface" => "Road Surface",
			"utilities" => "Utilities",
			"restrictions" => "Restrictions",
			"ngb_uni" => "University",
			"cats" => "Cats",
			"dogs" => "Dogs",
			"pk_lease" => "Parking Lease",
			"lease_type" => "Lease Type",
			"master_bath" => "Master Bath",
			"area" => "Area",
			"prop_type" => "Property Type",
			"prop_name" => "Property Name",
			"bld_sz" => "Building Size",
			"max_cont" => "Maximum Contiguous",
			"min_div" => "Minimum Divisible",
			"lst_dte" => "List Date",
			"dom" => "Days on Market",
			"construction_type" => "Construction Type",
			"design" => "Construction Type",
			"floor_desc" => "Construction Type",
			"heating_desc" => "Construction Type",
			"parking_description" => "Parking Description",
			"sewer_desc" => "Sewer Description",
			"water_desc" => "Water Description",
			"cooling_desc" => "Cooling Description",
			"front_exposure" => "Front Exposure",
			"roof_desc" => "Roof Description",
			"view" => "View",
			"video_tour" => "Video Tour",
			"virtual_tour" => "Virtual Tour",
			"virtual_tour_link" => "Virtual Tour Link"
		);

		$local_values_dictionary = array(
			"per_ngt" => "per night",
			"per_mnt" => "per month",
			"per_yr" => "per year",
			"per_wk" => "per week",
			"exstng" => "existing",
			"und_prop" => "under construction / proposed",
			"fl_srv" => "full service",
			"ind_grs" => "industrial gross",
			"mod_grs" => "modified gross",
			"mod_net" => "industrial net",
			"na" => "n/a",
			"Atch_gar" => "attached garage",
			"amt_mnt" => "amount/month",
			"amt_yr" => "amount/year",
			"sqft_mnt" => "sqft/month",
			"sqft_yr" => "sqft/year",
		);

		$style_values = array("bungal" => "Bungalow",
			"cape" => "Cape Cod",
			"colonial" => "Colonial",
			"contemp" => "Contemporary",
			"cott" => "Cottage",
			"farmh" => "Farmhouse",
			"fnt_bk_splt" => "Front to Back Split",
			"gamb_dutc" => "Gambrel/Dutch",
			"garrison" => "Garrison",
			"greek_rev" => "Greek Revival",
			"loft_splt" => "Lofted Split",
			"mult_lvl" => "Multi-level",
			"rai_ranch" => "Raised Ranch",
			"ranch" => "Ranch",
			"saltb" => "Saltbox",
			"split_ent" => "Split Entry",
			"tudor" => "Tudor",
			"victor" => "Victorian",
			"antiq" => "Antique");

		$api_dictionary = PL_Custom_Attributes::get_translations();
		$local_dictionary = array_merge($local_dictionary, $api_dictionary);

		global $pls_custom_amenity_dictionary;

		if (isset($pls_custom_amenity_dictionary) && !empty($pls_custom_amenity_dictionary)) {
			$local_dictionary = wp_parse_args($pls_custom_amenity_dictionary, $local_dictionary);
		}

		foreach ($amenities as $key => $value) {
			if (isset($local_values_dictionary[trim($value)])) {
				$value = $local_values_dictionary[trim($value)];
			} // translate booleans to Yes/No
			elseif ($value == '1') {
				$value = 'Yes';
			} elseif ($value == '0') {
				$value = 'No';
			} // break up comma separated spaceless lists
			else {
				$value = preg_replace('/,([^[:space:]])/', ', \\1', $value);
			}

			if (isset($local_dictionary[$key])) {
				if ($key == "lst_dte" || $key == "avail_on") {
					if ($date = strstr($value, "T00:00:00Z", true)) {
						$value = $date;
					}
				} else if ($key == 'style') {
					if (isset($style_values[$value])) {
						$value = $style_values[$value];
					}
				} else if ($key == 'virtual_tour_link' || $key == 'video_tour' || $key == 'virtual_tour') {
					$value = make_clickable($value);
					$value = preg_replace('|<a ([^>]+)>([^<]+)</a>|', '<a $1 target="_blank">View Tour</a>', $value);
				} else
					$value = ucwords($value);

				unset($amenities[$key]);
				$amenities[$local_dictionary[$key]] = ucwords($value);
			}
		}

		return $amenities;
	}

	public static function shorten_excerpt($post, $length = 50, $ellipsis = true)
	{

		if (!empty($post->post_excerpt)) {
			// 1st priority: excerpt
			// remove shortcodes, strip tags, and trim whitespace.
			$excerpt_no_shortcodes = strip_shortcodes($post->post_excerpt);
			$excerpt = trim(strip_tags($excerpt_no_shortcodes));
		} elseif (!empty($post->post_content)) {
			// 2nd priority: content
			// remove shortcodes, strip tags, and trim whitespace.
			$content = strip_shortcodes($post->post_content);
			$excerpt = trim(strip_tags($content));
		} else {
			return '';
		}

		if (strlen($excerpt) <= $length) {
			return $excerpt;
		}

		$shortened_excerpt = preg_replace('/\s+?(\S+)?$/', '', substr($excerpt, 0, $length));
		if ($ellipsis == true) {
			$shortened_excerpt .= '...';
		}

		return $shortened_excerpt;
	}

	public static function shorten_text($content, $length = 50, $ellipsis = true)
	{
		if (strlen($content) > $length) {
			$content = strip_tags($content);
			$shortened_content = preg_replace('/\s+?(\S+)?$/', '', substr($content, 0, $length));
			$shortened_content = $shortened_content . '...';
			return $shortened_content;
		} else {
			return $content;
		}
	}

	public static function get_lat_lng_of_address($address)
	{
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address;
		$url = str_replace(',', '', $url);
		$url = str_replace(' ', '+', $url);
		$result = wp_remote_get($url);

		$body = json_decode($result['body']);
		if (isset($body->results) && isset($body->results[0]) && isset($body->results[0]->geometry)) {
			return array(
				'lat' => $body->results[0]->geometry->location->lat,
				'lng' => $body->results[0]->geometry->location->lng);
		}

		return null;
	}

//end of class
}
