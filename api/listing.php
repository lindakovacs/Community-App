<?php 

class PL_Listing {
	
	/* A wrapper for PL_Option_Helper::api_key() for class functions that need to be aware of demo data */
	private static function api_key () {
		$api_key = PL_Option_Helper::api_key();

		$admin_listing_ajax = ( defined('DOING_AJAX') && isset($_POST['action']) && ($_POST['action'] == 'datatable_ajax') );
		if ( PL_Option_Helper::get_demo_data_flag() && defined('DEMO_API_KEY') && !(is_admin() && $admin_listing_ajax) ) {
			$api_key = DEMO_API_KEY;
		}

		return $api_key;
	}

	public static function get ($args = array()) {
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get', 'args')));

		$response = PL_HTTP::send_request(PL_Config::PL_API_LISTINGS('get', 'request', 'url'), $request, PL_Config::PL_API_LISTINGS('get', 'request', 'type'));
		if (isset($response) && isset($response['listings']) && is_array($response['listings'])) {
			foreach ($response['listings'] as $key => $listing) {
				$response['listings'][$key] = PL_Validate::attributes($listing, PL_Config::PL_API_LISTINGS('get','returns'));
			}
		} 
		else {
			$response = PL_Validate::attributes($response, array('listings' => array(), 'total' => 0));
		}
		
		return $response;
	}

	public static function create ($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('create', 'args')));
		$response = PL_HTTP::send_request(PL_Config::PL_API_LISTINGS('create', 'request', 'url'), $request, PL_Config::PL_API_LISTINGS('create', 'request', 'type'));
		
		return $response;
	}

	public static function update ($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('create', 'args')));
		$update_url = trailingslashit( PL_Config::PL_API_LISTINGS('update', 'request', 'url') ) . $args['id'];
		$response = PL_HTTP::send_request($update_url, $request, PL_Config::PL_API_LISTINGS('update', 'request', 'type'));
		
		return $response;	
	}

	public static function delete ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('delete');
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, $config['args']));
		$delete_url = trailingslashit($config['request']['url']) . $request['id'];
		$response = PL_HTTP::send_request($delete_url, $request, $config['request']['type']);
		$response = PL_Validate::attributes($response, $config['returns']);
		
		return $response;	
	}

	public static function temp_image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$config = PL_Config::PL_API_LISTINGS('temp_image');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));
		$response = PL_HTTP::send_request_multipart($config['request']['url'], $request, $file_name, $file_mime_type, $file_tmpname);
		
		return $response;	
	}

	public static function locations ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('get.locations');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));

		return PL_Validate::attributes(PL_HTTP::send_request($config['request']['url'], $request), $config['returns']);
	}
	
	public static function aggregates ($args = array()) {
		$config = PL_Config::PL_API_LISTINGS('get.aggregate');
		$request = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, $config['args']));

		return PL_Validate::attributes(PL_HTTP::send_request($config['request']['url'], $request), $config['returns']);
	}

	public static function convert_default_country () {
		$country_array = PL_Helper_User::get_default_country();
		$country = (isset($country_array['default_country']) ? $country_array['default_country'] : 'US');
		return $country;
	}

	public static function supported_countries () {
		return array(
			"AD" => "Andorra (AD)",
			"AE" => "United Arab Emirates (AE)",
			"AF" => "Afghanistan (AF)",
			"AG" => "Antigua &amp; Barbuda (AG)",
			"AI" => "Anguilla (AI)",
			"AL" => "Albania (AL)",
			"AM" => "Armenia (AM)",
			"AO" => "Angola (AO)",
			"AQ" => "Antarctica (AQ)",
			"AR" => "Argentina (AR)",
			"AS" => "Samoa (American) (AS)",
			"AT" => "Austria (AT)",
			"AU" => "Australia (AU)",
			"AW" => "Aruba (AW)",
			"AX" => "Aaland Islands (AX)",
			"AZ" => "Azerbaijan (AZ)",
			"BA" => "Bosnia &amp; Herzegovina (BA)",
			"BB" => "Barbados (BB)",
			"BD" => "Bangladesh (BD)",
			"BE" => "Belgium (BE)",
			"BF" => "Burkina Faso (BF)",
			"BG" => "Bulgaria (BG)",
			"BH" => "Bahrain (BH)",
			"BI" => "Burundi (BI)",
			"BJ" => "Benin (BJ)",
			"BL" => "St Barthelemy (BL)",
			"BM" => "Bermuda (BM)",
			"BN" => "Brunei (BN)",
			"BO" => "Bolivia (BO)",
			"BQ" => "Bonaire Sint Eustatius &amp; Saba (BQ)",
			"BR" => "Brazil (BR)",
			"BS" => "Bahamas (BS)",
			"BT" => "Bhutan (BT)",
			"BV" => "Bouvet Island (BV)",
			"BW" => "Botswana (BW)",
			"BY" => "Belarus (BY)",
			"BZ" => "Belize (BZ)",
			"CA" => "Canada (CA)",
			"CC" => "Cocos (Keeling) Islands (CC)",
			"CD" => "Congo (Dem. Rep.) (CD)",
			"CF" => "Central African Rep. (CF)",
			"CG" => "Congo (Rep.) (CG)",
			"CH" => "Switzerland (CH)",
			"CI" => "Cote d'Ivoire (CI)",
			"CK" => "Cook Islands (CK)",
			"CL" => "Chile (CL)",
			"CM" => "Cameroon (CM)",
			"CN" => "China (CN)",
			"CO" => "Colombia (CO)",
			"CR" => "Costa Rica (CR)",
			"CU" => "Cuba (CU)",
			"CV" => "Cape Verde (CV)",
			"CW" => "Curacao (CW)",
			"CX" => "Christmas Island (CX)",
			"CY" => "Cyprus (CY)",
			"CZ" => "Czech Republic (CZ)",
			"DE" => "Germany (DE)",
			"DJ" => "Djibouti (DJ)",
			"DK" => "Denmark (DK)",
			"DM" => "Dominica (DM)",
			"DO" => "Dominican Republic (DO)",
			"DZ" => "Algeria (DZ)",
			"EC" => "Ecuador (EC)",
			"EE" => "Estonia (EE)",
			"EG" => "Egypt (EG)",
			"EH" => "Western Sahara (EH)",
			"ER" => "Eritrea (ER)",
			"ES" => "Spain (ES)",
			"ET" => "Ethiopia (ET)",
			"FI" => "Finland (FI)",
			"FJ" => "Fiji (FJ)",
			"FK" => "Falkland Islands (FK)",
			"FM" => "Micronesia (FM)",
			"FO" => "Faroe Islands (FO)",
			"FR" => "France (FR)",
			"GA" => "Gabon (GA)",
			"GB" => "Britain (UK) (GB)",
			"GD" => "Grenada (GD)",
			"GE" => "Georgia (GE)",
			"GF" => "French Guiana (GF)",
			"GG" => "Guernsey (GG)",
			"GH" => "Ghana (GH)",
			"GI" => "Gibraltar (GI)",
			"GL" => "Greenland (GL)",
			"GM" => "Gambia (GM)",
			"GN" => "Guinea (GN)",
			"GP" => "Guadeloupe (GP)",
			"GQ" => "Equatorial Guinea (GQ)",
			"GR" => "Greece (GR)",
			"GS" => "South Georgia &amp; the South Sandwich Islands (GS)",
			"GT" => "Guatemala (GT)",
			"GU" => "Guam (GU)",
			"GW" => "Guinea-Bissau (GW)",
			"GY" => "Guyana (GY)",
			"HK" => "Hong Kong (HK)",
			"HM" => "Heard Island &amp; McDonald Islands (HM)",
			"HN" => "Honduras (HN)",
			"HR" => "Croatia (HR)",
			"HT" => "Haiti (HT)",
			"HU" => "Hungary (HU)",
			"ID" => "Indonesia (ID)",
			"IE" => "Ireland (IE)",
			"IL" => "Israel (IL)",
			"IM" => "Isle of Man (IM)",
			"IN" => "India (IN)",
			"IO" => "British Indian Ocean Territory (IO)",
			"IQ" => "Iraq (IQ)",
			"IR" => "Iran (IR)",
			"IS" => "Iceland (IS)",
			"IT" => "Italy (IT)",
			"JE" => "Jersey (JE)",
			"JM" => "Jamaica (JM)",
			"JO" => "Jordan (JO)",
			"JP" => "Japan (JP)",
			"KE" => "Kenya (KE)",
			"KG" => "Kyrgyzstan (KG)",
			"KH" => "Cambodia (KH)",
			"KI" => "Kiribati (KI)",
			"KM" => "Comoros (KM)",
			"KN" => "St Kitts &amp; Nevis (KN)",
			"KP" => "Korea (North) (KP)",
			"KR" => "Korea (South) (KR)",
			"KW" => "Kuwait (KW)",
			"KY" => "Cayman Islands (KY)",
			"KZ" => "Kazakhstan (KZ)",
			"LA" => "Laos (LA)",
			"LB" => "Lebanon (LB)",
			"LC" => "St Lucia (LC)",
			"LI" => "Liechtenstein (LI)",
			"LK" => "Sri Lanka (LK)",
			"LR" => "Liberia (LR)",
			"LS" => "Lesotho (LS)",
			"LT" => "Lithuania (LT)",
			"LU" => "Luxembourg (LU)",
			"LV" => "Latvia (LV)",
			"LY" => "Libya (LY)",
			"MA" => "Morocco (MA)",
			"MC" => "Monaco (MC)",
			"MD" => "Moldova (MD)",
			"ME" => "Montenegro (ME)",
			"MF" => "St Martin (French part) (MF)",
			"MG" => "Madagascar (MG)",
			"MH" => "Marshall Islands (MH)",
			"MK" => "Macedonia (MK)",
			"ML" => "Mali (ML)",
			"MM" => "Myanmar (Burma) (MM)",
			"MN" => "Mongolia (MN)",
			"MO" => "Macau (MO)",
			"MP" => "Northern Mariana Islands (MP)",
			"MQ" => "Martinique (MQ)",
			"MR" => "Mauritania (MR)",
			"MS" => "Montserrat (MS)",
			"MT" => "Malta (MT)",
			"MU" => "Mauritius (MU)",
			"MV" => "Maldives (MV)",
			"MW" => "Malawi (MW)",
			"MX" => "Mexico (MX)",
			"MY" => "Malaysia (MY)",
			"MZ" => "Mozambique (MZ)",
			"NA" => "Namibia (NA)",
			"NC" => "New Caledonia (NC)",
			"NE" => "Niger (NE)",
			"NF" => "Norfolk Island (NF)",
			"NG" => "Nigeria (NG)",
			"NI" => "Nicaragua (NI)",
			"NL" => "Netherlands (NL)",
			"NO" => "Norway (NO)",
			"NP" => "Nepal (NP)",
			"NR" => "Nauru (NR)",
			"NU" => "Niue (NU)",
			"NZ" => "New Zealand (NZ)",
			"OM" => "Oman (OM)",
			"PA" => "Panama (PA)",
			"PE" => "Peru (PE)",
			"PF" => "French Polynesia (PF)",
			"PG" => "Papua New Guinea (PG)",
			"PH" => "Philippines (PH)",
			"PK" => "Pakistan (PK)",
			"PL" => "Poland (PL)",
			"PM" => "St Pierre &amp; Miquelon (PM)",
			"PN" => "Pitcairn (PN)",
			"PR" => "Puerto Rico (PR)",
			"PS" => "Palestine (PS)",
			"PT" => "Portugal (PT)",
			"PW" => "Palau (PW)",
			"PY" => "Paraguay (PY)",
			"QA" => "Qatar (QA)",
			"RE" => "Reunion (RE)",
			"RO" => "Romania (RO)",
			"RS" => "Serbia (RS)",
			"RU" => "Russia (RU)",
			"RW" => "Rwanda (RW)",
			"SA" => "Saudi Arabia (SA)",
			"SB" => "Solomon Islands (SB)",
			"SC" => "Seychelles (SC)",
			"SD" => "Sudan (SD)",
			"SE" => "Sweden (SE)",
			"SG" => "Singapore (SG)",
			"SH" => "St Helena (SH)",
			"SI" => "Slovenia (SI)",
			"SJ" => "Svalbard &amp; Jan Mayen (SJ)",
			"SK" => "Slovakia (SK)",
			"SL" => "Sierra Leone (SL)",
			"SM" => "San Marino (SM)",
			"SN" => "Senegal (SN)",
			"SO" => "Somalia (SO)",
			"SR" => "Suriname (SR)",
			"SS" => "South Sudan (SS)",
			"ST" => "Sao Tome &amp; Principe (ST)",
			"SV" => "El Salvador (SV)",
			"SX" => "Sint Maarten (SX)",
			"SY" => "Syria (SY)",
			"SZ" => "Swaziland (SZ)",
			"TC" => "Turks &amp; Caicos Is (TC)",
			"TD" => "Chad (TD)",
			"TF" => "French Southern &amp; Antarctic Lands (TF)",
			"TG" => "Togo (TG)",
			"TH" => "Thailand (TH)",
			"TJ" => "Tajikistan (TJ)",
			"TK" => "Tokelau (TK)",
			"TL" => "East Timor (TL)",
			"TM" => "Turkmenistan (TM)",
			"TN" => "Tunisia (TN)",
			"TO" => "Tonga (TO)",
			"TR" => "Turkey (TR)",
			"TT" => "Trinidad &amp; Tobago (TT)",
			"TV" => "Tuvalu (TV)",
			"TW" => "Taiwan (TW)",
			"TZ" => "Tanzania (TZ)",
			"UA" => "Ukraine (UA)",
			"UG" => "Uganda (UG)",
			"UM" => "US minor outlying islands (UM)",
			"US" => "United States (US)",
			"UY" => "Uruguay (UY)",
			"UZ" => "Uzbekistan (UZ)",
			"VA" => "Vatican City (VA)",
			"VC" => "St Vincent (VC)",
			"VE" => "Venezuela (VE)",
			"VG" => "Virgin Islands (UK) (VG)",
			"VI" => "Virgin Islands (US) (VI)",
			"VN" => "Vietnam (VN)",
			"VU" => "Vanuatu (VU)",
			"WF" => "Wallis &amp; Futuna (WF)",
			"WS" => "Samoa (western) (WS)",
			"YE" => "Yemen (YE)",
			"YT" => "Mayotte (YT)",
			"ZA" => "South Africa (ZA)",
			"ZM" => "Zambia (ZM)",
			"ZW" => "Zimbabwe (ZW)"
		);
	}
}


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
							'class' => 'PL_Listing',
							'method' => 'supported_countries',
							'default' => array('PL_Listing','convert_default_country')
						)
					),
					'coords_latitude' => array('type' => 'hidden','group' => 'location', 'label' => 'Latitude'),
					'coords_longitude' => array('type' => 'hidden','group' => 'location', 'label' => 'Longitude')
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
