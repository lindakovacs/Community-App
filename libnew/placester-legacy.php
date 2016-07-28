<?php


require_once('interface.php');
require_once('attributes.php');


class PLX_Legacy_Interface extends PLX_Data_Interface {
	public static function init() {
		self::$attribute_interface = new PLX_Legacy_Attributes();
		self::$parameter_interface = new PLX_Legacy_Parameters();
		self::$listing_interface = new PLX_Legacy_Listings();
		self::$search_interface = new PLX_Legacy_Search();
		self::$image_interface = new PLX_Legacy_Images();
	}

	public static function map_listing_request($request) {
		$mapped = array();

		foreach($request as $name => $value) {
			if($name == 'listing_type')
				$mapped['compound_type'] = $value;
			else if($name == 'property_type')
				$mapped['metadata']['prop_type'] = $value;
			else if($name == 'status')
				$mapped['metadata']['status'] = $value;

			else if($name == 'latitude')
				$mapped['location']['coord_latitude'] = $value;
			else if($name == 'longitude')
				$mapped['location']['coord_longitude'] = $value;

			else if($group = self::get_attribute_group($name)) {
				if($group == 'location')
					$mapped['location'][$name] = $value;
				else if(in_array($group, array('cur_data', 'uncur_data')))
					$mapped['metadata'][$name] = $value;
			}
		}

		$mapped['images'] = $request['images'];
		return $mapped;
	}

	public static function map_listing_response($response) {
		$mapped = array('_legacy' => $response);
		$mapped['id'] = $response['id'];

		$mapped['listing_type'] = $response['compound_type'];
		$mapped['property_type'] = $response['cur_data']['prop_type'];
		$mapped['zoning_type'] = $response['zoning_types'][0];
		$mapped['purchase_type'] = $response['purchase_types'][0];

		$mapped['created_at'] = $response['created_at'];
		$mapped['updated_at'] = $response['updated_at'];
		$mapped['status'] = $response['cur_data']['status'];
		$mapped['list_date'] = $response['cur_data']['lst_dte'];
		$mapped['days_on'] = $response['cur_data']['dom'];

		$mapped['import_id'] = $response['import_id'];
		$mapped['provider_id'] = $response['provider_id'];

		$mapped['latitude'] = $response['location']['coords'][0];
		$mapped['longitude'] = $response['location']['coords'][1];

		unset($response['cur_data']['prop_type']);
		unset($response['cur_data']['status']);
		unset($response['cur_data']['lst_dte']);
		unset($response['cur_data']['dom']);
		unset($response['location']['coords']);

		$mapped = array_merge($mapped,
			(array) $response['rets'],
			(array) $response['location'],
			(array) $response['cur_data'],
			(array) $response['uncur_data']);

		$mapped['images'] = $response['images'];
		return $mapped;
	}

	public static function map_search_request($request) {
		if(isset($request['_blueprint']))
			return $request['_blueprint'];

		$mapped = array('address_mode' => 'exact');
		foreach($request as $name => $value) {
			if($value === '') continue;

			if($name == 'id')
				$mapped['listing_ids'] = (array) $value;

			else if($name == 'listing_type')
				$mapped['compound_type'] = $value;
			else if($name == 'zoning_type')
				$mapped['zoning_types'] = (array) $value;
			else if($name == 'purchase_type')
				$mapped['purchase_types'] = (array) $value;

			else if($name == 'property_type')
				$mapped['metadata']['prop_type'] = $value;
			else if($name == 'property_type_match')
				$mapped['metadata']['prop_type_match'] = $value;

			else if($name == 'status' || $name == 'status_match')
				$mapped['metadata'][$name] = $value;

			else if($name == 'list_date')
				$mapped['metadata']['lst_dte'] = $value;
			else if($name == 'min_list_date')
				$mapped['metadata']['min_lst_dte'] = $value;
			else if($name == 'max_list_date')
				$mapped['metadata']['max_lst_dte'] = $value;

			else if($name == 'days_on')
				$mapped['metadata']['dom'] = $value;
			else if($name == 'min_days_on')
				$mapped['metadata']['min_dom'] = $value;
			else if($name == 'max_days_on')
				$mapped['metadata']['max_dom'] = $value;

			else if($name == 'min_latitude' || $name == 'max_latitude')
				$mapped['box'][$name] = $value;
			else if($name == 'min_longitude' || $name == 'max_longitude')
				$mapped['box'][$name] = $value;

			else if($name == 'sort_by') {
				if($value == 'listing_type')
					$mapped['sort_by'] = 'compound_type';
				else if($value == 'property_type')
					$mapped['sort_by'] = 'cur_data.prop_type';
				else if($value == 'status')
					$mapped['sort_by'] = 'cur_data.status';
				else if($value == 'list_date')
					$mapped['sort_by'] = 'cur_data.lst_dte';
				else if($value == 'days_on')
					$mapped['sort_by'] = 'cur_data.dom';
				else if($value == 'images')
					$mapped['sort_by'] = 'total_images';

				else if($group = self::get_attribute_group($value))
					$mapped['sort_by'] = $group . '.' . $value;
				else
					$mapped['sort_by'] = $value;
			}

			else if($group = self::get_parameter_group($name)) {
				if(in_array($group, array('cur_data', 'uncur_data')))
					$mapped['metadata'][$name] = $value;
				else
					$mapped[$group][$name] = $value;
			}
			else
				$mapped[$name] = $value;
		}

		return $mapped;
	}

	public static function map_search_response($response) {
		if($response['listings']) {
			foreach($response['listings'] as $id => &$listing)
				$listing = self::map_listing_response($listing);
		}
		else {
			$response['total'] = $response['count'] = 0;
			$response['listings'] = array();
		}

		return $response;
	}

	public static function map_aggregate_request($request) {
		$keys = $request['keys'];
		$request = self::map_search_request($request);

		$mapped = array();
		foreach($keys as $key) {
			if($key == 'listing_type')
				$mapped[] = 'compound_type';
			else if($key == 'property_type')
				$mapped[] = 'cur_data.prop_type';
			else if($key == 'zoning_type')
				$mapped[] = 'zoning_types';
			else if($key == 'purchase_type')
				$mapped[] = 'purchase_types';
			else if($key == 'status')
				$mapped[] = 'cur_data.status';

			else if($group = self::get_attribute_group($key))
				$mapped[] = $group . '.' . $key;
		}

		$request['keys'] = $mapped;
		return $request;
	}

	public static function map_aggregate_response($response) {
		$mapped = array();

		foreach($response as $key => $values) {
			if($key == 'compound_type')
				$mapped['listing_type'] = $values;
			else if($key == 'cur_data.prop_type')
				$mapped['property_type'] = $values;
			else if($key == 'zoning_types')
				$mapped['zoning_type'] = $values;
			else if($key == 'purchase_types')
				$mapped['purchase_type'] = $values;
			else if($key == 'cur_data.status')
				$mapped['status'] = $values;

			else if(substr($key, 0, 9) == 'location.')
				$mapped[substr($key, 9)] = $values;

			else if(substr($key, 0, 9) == 'cur_data.')
				$mapped[substr($key, 9)] = $values;

			else if(substr($key, 0, 11) == 'uncur_data.')
				$mapped[substr($key, 11)] = $values;
		}

		return $mapped;
	}

	public static function get_attribute_group($attribute) {
		if($attribute = PLX_Attributes::get($attribute)) {
			if($attribute['group'] == 'Provider')
				return 'rets';
			else if($attribute['group'] == 'Location')
				return 'location';
			else if($attribute['group'] != 'Listing')
				return 'cur_data';
		}

		return null;
	}

	public static function get_parameter_group($parameter) {
		if(substr($parameter, strlen($parameter) - 6) == '_match')
			$parameter = substr($parameter, 0, strlen($parameter) - 6);
		else if(substr($parameter, 0, 4) == 'min_')
			$parameter = substr($parameter, 4);
		else if(substr($parameter, 0, 4) == 'max_')
			$parameter = substr($parameter, 4);

		return self::get_attribute_group($parameter);
	}
}


class PLX_Legacy_Attributes extends PLX_Placester_Attributes {
	protected $provider_attributes;

	protected function _define_attributes() {
		$attributes = parent::_define_attributes();

		$attributes = array_merge($attributes, $this->provider_attributes = array(
			// Provider
			'mls_id' =>           array(   'name' => 'mls_id',           'type' => self::TEXT_ID,        'group' => 'Provider',               'display' => 'MLS ID'                      ),

			'aid' =>              array(   'name' => 'aid',              'type' => self::TEXT_ID,        'group' => 'Provider',               'display' => 'Agent ID'                    ),
			'aname' =>            array(   'name' => 'aname',            'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Agent Name'                  ),
			'aphone' =>           array(   'name' => 'aphone',           'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Agent Phone'                 ),
			'alicense' =>         array(   'name' => 'alicense',         'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Agent License'               ),

			'oid' =>              array(   'name' => 'oid',              'type' => self::TEXT_ID,        'group' => 'Provider',               'display' => 'Office ID'                   ),
			'oname' =>            array(   'name' => 'oname',            'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Office Name'                 ),
			'ophone' =>           array(   'name' => 'ophone',           'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Office Phone'                ),

			// Co-attribution
			'acoid' =>            array(   'name' => 'acoid',            'type' => self::TEXT_ID,        'group' => 'Provider',               'display' => 'Co-agent ID'                 ),
			'aconame' =>          array(   'name' => 'aconame',          'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Co-agent Name'               ),
			'acophone' =>         array(   'name' => 'acophone',         'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Co-agent Phone'              ),
			'acolicense' =>       array(   'name' => 'acolicense',       'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Co-agent License'            ),

			'ocoid' =>            array(   'name' => 'ocoid',            'type' => self::TEXT_ID,        'group' => 'Provider',               'display' => 'Co-office ID'                ),
			'oconame' =>          array(   'name' => 'oconame',          'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Co-office Name'              ),
			'ocophone' =>         array(   'name' => 'ocophone',         'type' => self::SHORT_TEXT,     'group' => 'Provider',               'display' => 'Co-office Phone'             ),
		));

		$attributes['country']['fixed'] = true;
		$attributes['country']['values'] = array(
			"AD" =>             "Andorra (AD)",
			"AE" =>             "United Arab Emirates (AE)",
			"AF" =>             "Afghanistan (AF)",
			"AG" =>             "Antigua and Barbuda (AG)",
			"AI" =>             "Anguilla (AI)",
			"AL" =>             "Albania (AL)",
			"AM" =>             "Armenia (AM)",
			"AO" =>             "Angola (AO)",
			"AQ" =>             "Antarctica (AQ)",
			"AR" =>             "Argentina (AR)",
			"AS" =>             "Samoa (American) (AS)",
			"AT" =>             "Austria (AT)",
			"AU" =>             "Australia (AU)",
			"AW" =>             "Aruba (AW)",
			"AX" =>             "Aaland Islands (AX)",
			"AZ" =>             "Azerbaijan (AZ)",
			"BA" =>             "Bosnia and Herzegovina (BA)",
			"BB" =>             "Barbados (BB)",
			"BD" =>             "Bangladesh (BD)",
			"BE" =>             "Belgium (BE)",
			"BF" =>             "Burkina Faso (BF)",
			"BG" =>             "Bulgaria (BG)",
			"BH" =>             "Bahrain (BH)",
			"BI" =>             "Burundi (BI)",
			"BJ" =>             "Benin (BJ)",
			"BL" =>             "St Barthelemy (BL)",
			"BM" =>             "Bermuda (BM)",
			"BN" =>             "Brunei (BN)",
			"BO" =>             "Bolivia (BO)",
			"BQ" =>             "Bonaire Sint Eustatius and Saba (BQ)",
			"BR" =>             "Brazil (BR)",
			"BS" =>             "Bahamas (BS)",
			"BT" =>             "Bhutan (BT)",
			"BV" =>             "Bouvet Island (BV)",
			"BW" =>             "Botswana (BW)",
			"BY" =>             "Belarus (BY)",
			"BZ" =>             "Belize (BZ)",
			"CA" =>             "Canada (CA)",
			"CC" =>             "Cocos (Keeling) Islands (CC)",
			"CD" =>             "Congo (Dem. Rep.) (CD)",
			"CF" =>             "Central African Rep. (CF)",
			"CG" =>             "Congo (Rep.) (CG)",
			"CH" =>             "Switzerland (CH)",
			"CI" =>             "Cote d'Ivoire (CI)",
			"CK" =>             "Cook Islands (CK)",
			"CL" =>             "Chile (CL)",
			"CM" =>             "Cameroon (CM)",
			"CN" =>             "China (CN)",
			"CO" =>             "Colombia (CO)",
			"CR" =>             "Costa Rica (CR)",
			"CU" =>             "Cuba (CU)",
			"CV" =>             "Cape Verde (CV)",
			"CW" =>             "Curacao (CW)",
			"CX" =>             "Christmas Island (CX)",
			"CY" =>             "Cyprus (CY)",
			"CZ" =>             "Czech Republic (CZ)",
			"DE" =>             "Germany (DE)",
			"DJ" =>             "Djibouti (DJ)",
			"DK" =>             "Denmark (DK)",
			"DM" =>             "Dominica (DM)",
			"DO" =>             "Dominican Republic (DO)",
			"DZ" =>             "Algeria (DZ)",
			"EC" =>             "Ecuador (EC)",
			"EE" =>             "Estonia (EE)",
			"EG" =>             "Egypt (EG)",
			"EH" =>             "Western Sahara (EH)",
			"ER" =>             "Eritrea (ER)",
			"ES" =>             "Spain (ES)",
			"ET" =>             "Ethiopia (ET)",
			"FI" =>             "Finland (FI)",
			"FJ" =>             "Fiji (FJ)",
			"FK" =>             "Falkland Islands (FK)",
			"FM" =>             "Micronesia (FM)",
			"FO" =>             "Faroe Islands (FO)",
			"FR" =>             "France (FR)",
			"GA" =>             "Gabon (GA)",
			"GB" =>             "Britain (UK) (GB)",
			"GD" =>             "Grenada (GD)",
			"GE" =>             "Georgia (GE)",
			"GF" =>             "French Guiana (GF)",
			"GG" =>             "Guernsey (GG)",
			"GH" =>             "Ghana (GH)",
			"GI" =>             "Gibraltar (GI)",
			"GL" =>             "Greenland (GL)",
			"GM" =>             "Gambia (GM)",
			"GN" =>             "Guinea (GN)",
			"GP" =>             "Guadeloupe (GP)",
			"GQ" =>             "Equatorial Guinea (GQ)",
			"GR" =>             "Greece (GR)",
			"GS" =>             "South Georgia and the South Sandwich Islands (GS)",
			"GT" =>             "Guatemala (GT)",
			"GU" =>             "Guam (GU)",
			"GW" =>             "Guinea-Bissau (GW)",
			"GY" =>             "Guyana (GY)",
			"HK" =>             "Hong Kong (HK)",
			"HM" =>             "Heard Island and McDonald Islands (HM)",
			"HN" =>             "Honduras (HN)",
			"HR" =>             "Croatia (HR)",
			"HT" =>             "Haiti (HT)",
			"HU" =>             "Hungary (HU)",
			"ID" =>             "Indonesia (ID)",
			"IE" =>             "Ireland (IE)",
			"IL" =>             "Israel (IL)",
			"IM" =>             "Isle of Man (IM)",
			"IN" =>             "India (IN)",
			"IO" =>             "British Indian Ocean Territory (IO)",
			"IQ" =>             "Iraq (IQ)",
			"IR" =>             "Iran (IR)",
			"IS" =>             "Iceland (IS)",
			"IT" =>             "Italy (IT)",
			"JE" =>             "Jersey (JE)",
			"JM" =>             "Jamaica (JM)",
			"JO" =>             "Jordan (JO)",
			"JP" =>             "Japan (JP)",
			"KE" =>             "Kenya (KE)",
			"KG" =>             "Kyrgyzstan (KG)",
			"KH" =>             "Cambodia (KH)",
			"KI" =>             "Kiribati (KI)",
			"KM" =>             "Comoros (KM)",
			"KN" =>             "St Kitts and Nevis (KN)",
			"KP" =>             "Korea (North) (KP)",
			"KR" =>             "Korea (South) (KR)",
			"KW" =>             "Kuwait (KW)",
			"KY" =>             "Cayman Islands (KY)",
			"KZ" =>             "Kazakhstan (KZ)",
			"LA" =>             "Laos (LA)",
			"LB" =>             "Lebanon (LB)",
			"LC" =>             "St Lucia (LC)",
			"LI" =>             "Liechtenstein (LI)",
			"LK" =>             "Sri Lanka (LK)",
			"LR" =>             "Liberia (LR)",
			"LS" =>             "Lesotho (LS)",
			"LT" =>             "Lithuania (LT)",
			"LU" =>             "Luxembourg (LU)",
			"LV" =>             "Latvia (LV)",
			"LY" =>             "Libya (LY)",
			"MA" =>             "Morocco (MA)",
			"MC" =>             "Monaco (MC)",
			"MD" =>             "Moldova (MD)",
			"ME" =>             "Montenegro (ME)",
			"MF" =>             "St Martin (French part) (MF)",
			"MG" =>             "Madagascar (MG)",
			"MH" =>             "Marshall Islands (MH)",
			"MK" =>             "Macedonia (MK)",
			"ML" =>             "Mali (ML)",
			"MM" =>             "Myanmar (Burma) (MM)",
			"MN" =>             "Mongolia (MN)",
			"MO" =>             "Macau (MO)",
			"MP" =>             "Northern Mariana Islands (MP)",
			"MQ" =>             "Martinique (MQ)",
			"MR" =>             "Mauritania (MR)",
			"MS" =>             "Montserrat (MS)",
			"MT" =>             "Malta (MT)",
			"MU" =>             "Mauritius (MU)",
			"MV" =>             "Maldives (MV)",
			"MW" =>             "Malawi (MW)",
			"MX" =>             "Mexico (MX)",
			"MY" =>             "Malaysia (MY)",
			"MZ" =>             "Mozambique (MZ)",
			"NA" =>             "Namibia (NA)",
			"NC" =>             "New Caledonia (NC)",
			"NE" =>             "Niger (NE)",
			"NF" =>             "Norfolk Island (NF)",
			"NG" =>             "Nigeria (NG)",
			"NI" =>             "Nicaragua (NI)",
			"NL" =>             "Netherlands (NL)",
			"NO" =>             "Norway (NO)",
			"NP" =>             "Nepal (NP)",
			"NR" =>             "Nauru (NR)",
			"NU" =>             "Niue (NU)",
			"NZ" =>             "New Zealand (NZ)",
			"OM" =>             "Oman (OM)",
			"PA" =>             "Panama (PA)",
			"PE" =>             "Peru (PE)",
			"PF" =>             "French Polynesia (PF)",
			"PG" =>             "Papua New Guinea (PG)",
			"PH" =>             "Philippines (PH)",
			"PK" =>             "Pakistan (PK)",
			"PL" =>             "Poland (PL)",
			"PM" =>             "St Pierre and Miquelon (PM)",
			"PN" =>             "Pitcairn (PN)",
			"PR" =>             "Puerto Rico (PR)",
			"PS" =>             "Palestine (PS)",
			"PT" =>             "Portugal (PT)",
			"PW" =>             "Palau (PW)",
			"PY" =>             "Paraguay (PY)",
			"QA" =>             "Qatar (QA)",
			"RE" =>             "Reunion (RE)",
			"RO" =>             "Romania (RO)",
			"RS" =>             "Serbia (RS)",
			"RU" =>             "Russia (RU)",
			"RW" =>             "Rwanda (RW)",
			"SA" =>             "Saudi Arabia (SA)",
			"SB" =>             "Solomon Islands (SB)",
			"SC" =>             "Seychelles (SC)",
			"SD" =>             "Sudan (SD)",
			"SE" =>             "Sweden (SE)",
			"SG" =>             "Singapore (SG)",
			"SH" =>             "St Helena (SH)",
			"SI" =>             "Slovenia (SI)",
			"SJ" =>             "Svalbard and Jan Mayen (SJ)",
			"SK" =>             "Slovakia (SK)",
			"SL" =>             "Sierra Leone (SL)",
			"SM" =>             "San Marino (SM)",
			"SN" =>             "Senegal (SN)",
			"SO" =>             "Somalia (SO)",
			"SR" =>             "Suriname (SR)",
			"SS" =>             "South Sudan (SS)",
			"ST" =>             "Sao Tome and Principe (ST)",
			"SV" =>             "El Salvador (SV)",
			"SX" =>             "Sint Maarten (SX)",
			"SY" =>             "Syria (SY)",
			"SZ" =>             "Swaziland (SZ)",
			"TC" =>             "Turks and Caicos Is (TC)",
			"TD" =>             "Chad (TD)",
			"TF" =>             "French Southern and Antarctic Lands (TF)",
			"TG" =>             "Togo (TG)",
			"TH" =>             "Thailand (TH)",
			"TJ" =>             "Tajikistan (TJ)",
			"TK" =>             "Tokelau (TK)",
			"TL" =>             "East Timor (TL)",
			"TM" =>             "Turkmenistan (TM)",
			"TN" =>             "Tunisia (TN)",
			"TO" =>             "Tonga (TO)",
			"TR" =>             "Turkey (TR)",
			"TT" =>             "Trinidad and Tobago (TT)",
			"TV" =>             "Tuvalu (TV)",
			"TW" =>             "Taiwan (TW)",
			"TZ" =>             "Tanzania (TZ)",
			"UA" =>             "Ukraine (UA)",
			"UG" =>             "Uganda (UG)",
			"UM" =>             "US minor outlying islands (UM)",
			"US" =>             "United States (US)",
			"UY" =>             "Uruguay (UY)",
			"UZ" =>             "Uzbekistan (UZ)",
			"VA" =>             "Vatican City (VA)",
			"VC" =>             "St Vincent (VC)",
			"VE" =>             "Venezuela (VE)",
			"VG" =>             "Virgin Islands (UK) (VG)",
			"VI" =>             "Virgin Islands (US) (VI)",
			"VN" =>             "Vietnam (VN)",
			"VU" =>             "Vanuatu (VU)",
			"WF" =>             "Wallis and Futuna (WF)",
			"WS" =>             "Samoa (western) (WS)",
			"YE" =>             "Yemen (YE)",
			"YT" =>             "Mayotte (YT)",
			"ZA" =>             "South Africa (ZA)",
			"ZM" =>             "Zambia (ZM)",
			"ZW" =>             "Zimbabwe (ZW)"
		);

		return $attributes;
	}
}


class PLX_Legacy_Parameters extends PLX_Parameters {
	protected function _define_parameters() {
		$parameters = parent::_define_parameters();

		unset($parameters['aname']);
		unset($parameters['aphone']);
		unset($parameters['alicense']);
		unset($parameters['oname']);
		unset($parameters['ophone']);
		unset($parameters['aconame']);
		unset($parameters['acophone']);
		unset($parameters['acolicense']);
		unset($parameters['oconame']);
		unset($parameters['ocophone']);

		$parameters = array_merge(array(
			'include_disabled' => array(   'name' => 'include_disabled',  'type' => PLX_Attributes::BOOLEAN,        'group' => 'Listing',                'display' => 'Disabled Listings'           ),
			'non_import' =>       array(   'name' => 'non_import',        'type' => PLX_Attributes::BOOLEAN,        'group' => 'Listing',                'display' => 'Private Listings'            ),
			'agency_only' =>      array(   'name' => 'agency_only',       'type' => PLX_Attributes::BOOLEAN,        'group' => 'Listing',                'display' => 'Agency Listings'             )
		), $parameters);

		$parameters['sort_by']['values'] = array_diff_key($parameters['sort_by']['values'], array_fill_keys(
			array('id', 'listing_type', 'zoning_type', 'purchase_type', 'mls_id', 'aid', 'aname', 'aphone', 'alicense',
				'oid', 'oname', 'ophone', 'acoid', 'aconame', 'acophone', 'acolicense', 'ocoid', 'oconame', 'ocophone'), null));

		return $parameters;
	}
}


class PLX_Legacy_Listings extends PLX_Listings {
	protected function _create($args = array()) {
		$request = PLX_Legacy_Interface::map_listing_request($args);
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings', $request, 'POST');

		return $response;
	}

	protected function _read($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'GET');

		return PLX_Legacy_Interface::map_listing_response($response);
	}

	protected function _update($args = array()) {
		$request = PLX_Legacy_Interface::map_listing_request($args);
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'PUT');

		return $response;
	}

	protected function _delete($args = array()) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/' . $args['id'], $request, 'DELETE');

		return $response;
	}
}


class PLX_Legacy_Search extends PLX_Search {
	protected function _listings ($args = array()) {
		$request = PLX_Legacy_Interface::map_search_request($args);
		$request = array_merge(array("api_key" => self::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2.1/listings', $request, 'GET');

		return PLX_Legacy_Interface::map_search_response($response);
	}

	protected function _locations ($args = array()) {
		$request = array_merge(array("api_key" => self::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2/listings/locations', $request, 'GET');

		return $response;
	}

	protected function _aggregates ($args = array()) {
		$request = PLX_Legacy_Interface::map_aggregate_request($args);
		$request = array_merge(array("api_key" => self::api_key()), $request);

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request($PL_API_SERVER . '/v2.1/listings/aggregate', $request, 'GET');

		return PLX_Legacy_Interface::map_aggregate_response($response);
	}

	private static function api_key () {
		$api_key = PL_Option_Helper::api_key();

		$admin_listing_ajax = ( defined('DOING_AJAX') && isset($_POST['action']) && ($_POST['action'] == 'datatable_ajax') );
		if ( PL_Option_Helper::get_demo_data_flag() && defined('DEMO_API_KEY') && !(is_admin() && $admin_listing_ajax) ) {
			$api_key = DEMO_API_KEY;
		}

		return $api_key;
	}
}


class PLX_Legacy_Images extends PLX_Images {
	const PLACESTER_DF_HOST = 'd2frgvzmtkrf4d.cloudfront.net';

	protected function _upload ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$request = array_merge(array("api_key" => PL_Option_Helper::api_key()), $args); //?

		global $PL_API_SERVER;
		$response = PL_HTTP::send_request_multipart($PL_API_SERVER . '/v2/listings/media/temp/image', $request, $file_name, $file_mime_type, $file_tmpname);

		return $response;
	}

	protected function _resize($args) {
		if (!defined('PLACESTER_DF_SECRET'))
			return $args['old_image'];

		extract(wp_parse_args(parse_url($args['old_image']), array('query' => '') ));

		$pathinfo = pathinfo($path);
		$path = ltrim($path, '/');
		$ext = $pathinfo['extension'];

		$size = $args['resize']['w'] . 'x' . $args['resize']['h'] . ($args['nocrop'] ? '!' : '#');
		$action = 'thumb';

		$request_tabs_newlines = "f\t" . $path . "\np" . "\t". $action . "\t". $size . "\ne" . "\t" . $ext;
		$request_clean = 'f' . $path . 'p' . $action . $size . 'e' . $ext;

		$job = base64_encode($request_tabs_newlines);
		$secret = substr(sha1($request_clean . PLACESTER_DF_SECRET), 0, 16);

		$new_image = $scheme . '://' . self::PLACESTER_DF_HOST . '/' . $secret . '/' . rtrim($job, '=') . '.' . $ext . '?' . $query;
		return $new_image ? $new_image : $args['old_image'];
	}
}
