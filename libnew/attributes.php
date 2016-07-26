<?php


require_once('interface.php');


class PLX_Attributes extends PLX_Data_Internal {
	const BOOLEAN = 1;
	const NUMERIC = 2;
	const CURRENCY = 5;
	const DATE_TIME = 8;
	const COORDINATE = 9;
	const TEXT_ID = 11;
	const TEXT_VALUE = 12;
	const SHORT_TEXT = 15;
	const LONG_TEXT = 16;

	private static function this() {
		if(!isset(self::$attribute_interface))
			self::$attribute_interface = new self; // default implementation below

		return self::$attribute_interface;
	}

	public static function get($name) {
		return self::this()->_get($name);
	}

	public static function get_attributes() {
		return self::this()->_get_attributes();
	}

	public static function get_listing_types() {
		return self::this()->_get_listing_types();
	}

	public static function get_listing_attributes($listing_type = null) {
		return self::this()->_get_listing_attributes($listing_type);
	}

	public static function get_basic_attributes($listing_type = null) {
		return self::this()->_get_basic_attributes($listing_type);
	}

	public static function get_extended_attributes($listing_type = null) {
		return self::this()->_get_extended_attributes($listing_type);
	}

	public static function get_group_title($group, $listing_type = null) {
		return self::this()->_get_group_title($group, $listing_type);
	}

	public static function get_group_attributes($groups, $flatten = false) {
		return self::this()->_get_group_attributes($groups, $flatten);
	}

	public static function get_attribute_values($name, $static = true, $dynamic = true, $none = null) {
		return self::this()->_get_attribute_values($name, $static, $dynamic, $none);
	}

	public static function get_static_values($name, $none = null) {
		return self::this()->_get_static_values($name, $none);
	}

	public static function get_dynamic_values($name, $none = null) {
		return self::this()->_get_dynamic_values($name, $none);
	}


	// default implementation -- derive and use PLX_Data_Interface::set_attribute_interface to override
	protected $attributes;

	protected function _get($name) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	protected function _get_attributes() {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		return $this->attributes;
	}

	protected function _get_listing_types() {
		$this->_get_attribute_values('listing_type');
	}

	protected function _get_listing_attributes($listing_type = null) {
		if($listing_type)
			return array_merge($this->_get_basic_attributes($listing_type), $this->_get_extended_attributes($listing_type));
		else
			return $this->_get_attributes();
	}

	protected function _get_basic_attributes($listing_type = null) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		$basic_attributes = self::get_group_attributes(array('Listing', 'Location', 'Basic', 'Terms', 'Provider'));

		// the parameter is used to select attributes for listing creation
		if($listing_type) {
			$basic_attributes['Listing'] = array(
				'listing_type' => $this->attributes['listing_type'],
				'property_type' => $this->attributes['property_type'],
				'status' => $this->attributes['status']);

			if(in_array($listing_type, array('res_sale', 'comm_sale')))
				unset($basic_attributes['Terms']);
		}

		return $basic_attributes;
	}

	protected function _get_extended_attributes($listing_type = null) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		$groups = array('Listing', 'Location', 'Basic', 'Terms', 'Provider');
		$results = array();

		foreach($this->attributes as $attribute)
			if(!in_array($attribute['group'], $groups))
				if(!isset($results[$attribute['group']]))
					$results[$attribute['group']] = array($attribute);
				else
					$results[$attribute['group']][] = $attribute;

		return $results;
	}

	protected function _get_group_title($group, $listing_type = null) {
		if($group == 'Basic') return 'Basic Property Details';
		if($group == 'Terms') return 'Lease Terms';

		return $group;
	}

	protected function _get_group_attributes($groups, $flatten = false) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		$groups = (array) $groups;
		$results = $flatten ? array() : array_fill_keys($groups, array());

		foreach($this->attributes as $attribute)
			if(in_array($attribute['group'], $groups))
				if($flatten)
					$results[] = $attribute;
				else
					$results[$attribute['group']][] = $attribute;

		return $results;
	}

	protected function _get_attribute_values($name, $static = true, $dynamic = true, $none = null) {
		if($static) $static = $this->_get_static_values($name, $none);
		else $static = null;

		if($dynamic) $dynamic = $this->_get_dynamic_values($name, $none);
		else $dynamic = null;

		if($static === null && $dynamic === null)
			return null;

		return array_merge((array) $static, (array) $dynamic);
	}

	protected function _get_static_values($name, $none = null) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		if(!isset($this->attributes[$name]) || $this->attributes[$name]['type'] != self::TEXT_VALUE)
			return null;

		if(isset($this->attributes[$name]['values']))
			return $this->attributes[$name]['values'];

		return array();
	}

	protected function _get_dynamic_values($name, $none = null) {
		return null;
	}


	protected function _define_attributes() {
		$attributes = array(
			'id' =>               array(   'name' => 'id',               'type' => self::TEXT_ID,        'group' => 'Listing',                'display' => 'Listing ID'                  ),

			'listing_type' =>     array(   'name' => 'listing_type',     'type' => self::TEXT_VALUE,     'group' => 'Listing',                'display' => 'Listing Type'                ),
			'property_type' =>    array(   'name' => 'property_type',    'type' => self::TEXT_VALUE,     'group' => 'Listing',                'display' => 'Property Type'               ),
			'zoning_type' =>      array(   'name' => 'zoning_type',      'type' => self::TEXT_VALUE,     'group' => 'Listing',                'display' => 'Zoning Type'                 ),
			'purchase_type' =>    array(   'name' => 'purchase_type',    'type' => self::TEXT_VALUE,     'group' => 'Listing',                'display' => 'Purchase Type'               ),

			'created_at' =>       array(   'name' => 'created_at',       'type' => self::DATE_TIME,      'group' => 'Listing',                'display' => 'Created at'                  ),
			'updated_at' =>       array(   'name' => 'updated_at',       'type' => self::DATE_TIME,      'group' => 'Listing',                'display' => 'Updated at'                  ),
			'status' =>           array(   'name' => 'status',           'type' => self::TEXT_VALUE,     'group' => 'Listing',                'display' => 'Status'                      ),
			'list_date' =>        array(   'name' => 'list_date',        'type' => self::DATE_TIME,      'group' => 'Listing',                'display' => 'List Date'                   ),
			'days_on' =>          array(   'name' => 'days_on',          'type' => self::NUMERIC,        'group' => 'Listing',                'display' => 'Days on Market'              ),

			// Address
			'address' =>          array(   'name' => 'address',          'type' => self::SHORT_TEXT,     'group' => 'Location',               'display' => 'Address'                     ),
			'unit' =>             array(   'name' => 'unit',             'type' => self::SHORT_TEXT,     'group' => 'Location',               'display' => 'Unit'                        ),

			// Location
			'locality' =>         array(   'name' => 'locality',         'type' => self::TEXT_VALUE,     'group' => 'Location',               'display' => 'City'                        ),
			'region' =>           array(   'name' => 'region',           'type' => self::TEXT_VALUE,     'group' => 'Location',               'display' => 'State'                       ),
			'postal' =>           array(   'name' => 'postal',           'type' => self::TEXT_VALUE,     'group' => 'Location',               'display' => 'Zip'                         ),
			'country' =>          array(   'name' => 'country',          'type' => self::TEXT_VALUE,     'group' => 'Location',               'display' => 'Country'                     ),

			'neighborhood' =>     array(   'name' => 'neighborhood',     'type' => self::TEXT_VALUE,     'group' => 'Location',               'display' => 'Neighborhood'                ),
			'county' =>           array(   'name' => 'county',           'type' => self::TEXT_VALUE,     'group' => 'Location',               'display' => 'County'                      ),

			'latitude' =>         array(   'name' => 'latitude',         'type' => self::COORDINATE,     'group' => 'Location',               'display' => 'Latitude'                    ),
			'longitude' =>        array(   'name' => 'longitude',        'type' => self::COORDINATE,     'group' => 'Location',               'display' => 'Longitude'                   ),

			// Basic Info
			'price' =>            array(   'name' => 'price',            'type' => self::CURRENCY,       'group' => 'Basic',                  'display' => 'Price'                       ),
			'sqft' =>             array(   'name' => 'sqft',             'type' => self::NUMERIC,        'group' => 'Basic',                  'display' => 'Square Feet'                 ),
			'beds' =>             array(   'name' => 'beds',             'type' => self::NUMERIC,        'group' => 'Basic',                  'display' => 'Bedrooms'                    ),
			'baths' =>            array(   'name' => 'baths',            'type' => self::NUMERIC,        'group' => 'Basic',                  'display' => 'Bathrooms'                   ),
			'half_baths' =>       array(   'name' => 'half_baths',       'type' => self::NUMERIC,        'group' => 'Basic',                  'display' => 'Half Baths'                  ),
			'desc' =>             array(   'name' => 'desc',             'type' => self::LONG_TEXT,      'group' => 'Basic',                  'display' => 'Description'                 ),

			// Lease Info
			'lse_trms' =>         array(   'name' => 'lse_trms',         'type' => self::TEXT_VALUE,     'group' => 'Terms',                  'display' => 'Lease Interval'              ),
			'avail_on' =>         array(   'name' => 'avail_on',         'type' => self::DATE_TIME,      'group' => 'Terms',                  'display' => 'Date Available'              ),
			'deposit' =>          array(   'name' => 'deposit',          'type' => self::CURRENCY,       'group' => 'Terms',                  'display' => 'Deposit'                     )
		);

		// Attribute Values
		$attributes['listing_type']['fixed'] = true;
		$attributes['listing_type']['values'] = array(
			'res_sale' =>       'Residential Sale',
			'res_rental' =>     'Residential Rental',
			'comm_sale' =>      'Commercial Sale',
			'comm_rental' =>    'Commercial Rental'
		);

		$attributes['property_type']['fixed'] = false;

		$attributes['zoning_type']['fixed'] = true;
		$attributes['zoning_type']['values'] = array(
			'residential' =>    'Residential',
			'commercial' =>     'Commercial'
		);

		$attributes['purchase_type']['fixed'] = true;
		$attributes['purchase_type']['values'] = array(
			'sale' =>           'Sale',
			'rental' =>         'Rental'
		);

		$attributes['status']['fixed'] = false;
		$attributes['status']['values'] = array(
			'Active',
			'Pending',
			'Sold'
		);

		$attributes['locality']['fixed'] = false;
		$attributes['region']['fixed'] = false;
		$attributes['postal']['fixed'] = false;
		$attributes['country']['fixed'] = false;

		$attributes['neighborhood']['fixed'] = false;
		$attributes['county']['fixed'] = false;

		$attributes['lse_trms']['fixed'] = true;
		$attributes['lse_trms']['values'] = array(
			'per_mnt' =>        'Per Month',
			'per_ngt' =>        'Per Night',
			'per_wk' =>         'Per Week',
			'per_yr' =>         'Per Year'
		);

		return $attributes;
	}
}


class PLX_Parameters extends PLX_Data_Internal {
	private static function this() {
		if(!isset(self::$parameter_interface))
			self::$parameter_interface = new self; // default implementation below

		return self::$parameter_interface;
	}

	public static function get($name) {
		return self::this()->_get($name);
	}

	public static function get_parameters() {
		return self::this()->_get_parameters();
	}

	public static function get_basic_parameters() {
		return self::this()->_get_basic_parameters();
	}

	public static function get_extended_parameters() {
		return self::this()->_get_extended_parameters();
	}

	public static function get_group_title($group) {
		return self::this()->_get_group_title($group);
	}

	public static function get_group_parameters($groups, $flatten = false) {
		return self::this()->_get_group_parameters($groups, $flatten);
	}

	public static function get_parameter_values($name, $static = true, $dynamic = true, $none = null) {
		return self::this()->_get_parameter_values($name, $static, $dynamic, $none);
	}

	public static function get_static_values($name, $none = null) {
		return self::this()->_get_static_values($name, $none);
	}

	public static function get_dynamic_values($name, $none = null) {
		return self::this()->_get_dynamic_values($name, $none);
	}

	protected $parameters;
	protected $range_types;
	protected $value_types;
	protected $text_types;

	protected function _get($name) {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters();

		if(isset($this->parameters[$name])) return $this->parameters[$name];

		return null;
	}

	protected function _get_parameters() {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters();

		return $this->parameters;
	}

	protected function _get_basic_parameters() {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters();

		$basic_parameters = self::get_group_parameters(array('Listing', 'Location', 'Basic', 'Images', 'Sorting'));
		return $basic_parameters;
	}

	protected function _get_extended_parameters() {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters();

		$groups = array('Listing', 'Location', 'Basic', 'Images', 'Sorting');
		$results = array();

		foreach($this->parameters as $parameter)
			if(!in_array($parameter['group'], $groups))
				if(!isset($results[$parameter['group']]))
					$results[$parameter['group']] = array($parameter);
				else
					$results[$parameter['group']][] = $parameter;

		return $results;
	}

	protected function _get_group_title($group) {
		return PLX_Attributes::get_group_title($group);
	}

	protected function _get_group_parameters($groups, $flatten = false) {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters();

		$groups = (array) $groups;
		$results = $flatten ? array() : array_fill_keys($groups, array());

		foreach($this->parameters as $parameter)
			if(in_array($parameter['group'], $groups))
				if($flatten)
					$results[] = $parameter;
				else
					$results[$parameter['group']][] = $parameter;

		return $results;
	}

	protected function _get_parameter_values($name, $static = true, $dynamic = true, $none = null) {
		if($static) $static = $this->_get_static_values($name, $none);
			else $static = null;

		if($dynamic) $dynamic = $this->_get_dynamic_values($name, $none);
			else $dynamic = null;

		if($static === null && $dynamic === null)
			return null;

		return array_merge((array) $static, (array) $dynamic);
	}

	protected function _get_static_values($name, $none = null) {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters();

		if(!isset($this->parameters[$name]) || $this->parameters[$name]['type'] != PLX_Attributes::TEXT_VALUE)
			return null;

		if(isset($this->parameters[$name]['values']))
			return $this->parameters[$name]['values'];

		return array();
	}

	protected function _get_dynamic_values($name, $none = null) {
		return null;
	}

	protected function _define_parameters() {
		if(empty($this->value_types))
			$this->value_types = array(PLX_Attributes::BOOLEAN, PLX_Attributes::TEXT_ID, PLX_Attributes::TEXT_VALUE);

		if(empty($this->range_types))
			$this->range_types = array(PLX_Attributes::NUMERIC, PLX_Attributes::CURRENCY, PLX_Attributes::DATE_TIME, PLX_Attributes::COORDINATE);

		if(empty($this->text_types))
			$this->text_types = array(PLX_Attributes::SHORT_TEXT, PLX_Attributes::LONG_TEXT);

		$parameters = array(); $sort_values = array();
		$attributes = PLX_Attributes::get_attributes();
		foreach($attributes as $attribute) {
			$parameter = $attribute;
			$parameter['attribute'] = $attribute['name'];

			// special case
			if(in_array($attribute['name'], array('beds', 'baths', 'half_baths'))) {
				$parameters[$parameter['name']] = $parameter;
				if($attribute['type'] != PLX_Attributes::COORDINATE)
					$sort_values[$attribute['name']] = $attribute['display'];

				$parameter['name'] = 'min_' . $attribute['name'];
				$parameter['display'] = 'Min ' . $attribute['display'];
				$parameters[$parameter['name']] = $parameter;

				$parameter['name'] = 'max_' . $attribute['name'];
				$parameter['display'] = 'Max ' . $attribute['display'];
				$parameters[$parameter['name']] = $parameter;
			}

			else if(in_array($attribute['type'], $this->value_types)) {
				$parameters[$parameter['name']] = $parameter;
				if($attribute['type'] != PLX_Attributes::BOOLEAN)
					$sort_values[$attribute['name']] = $attribute['display'];
			}

			else if(in_array($attribute['type'], $this->text_types)) {
				$parameters[$parameter['name']] = $parameter;
			}

			else if(in_array($attribute['type'], $this->range_types)) {
				$sort_values[$attribute['name']] = $attribute['display'];

				$parameter['name'] = 'min_' . $attribute['name'];
				$parameter['display'] = 'Min ' . $attribute['display'];
				$parameters[$parameter['name']] = $parameter;

				$parameter['name'] = 'max_' . $attribute['name'];
				$parameter['display'] = 'Max ' . $attribute['display'];
				$parameters[$parameter['name']] = $parameter;
			}
		}

		$parameters = array_merge($parameters, array (
			'images' =>           array(   'name' => 'images',            'type' => PLX_Attributes::BOOLEAN,        'group' => 'Images',                 'display' => 'Images'                      ),
			'min_images' =>       array(   'name' => 'min_images',        'type' => PLX_Attributes::NUMERIC,        'group' => 'Images',                 'display' => 'Min Images'                  ),
			'max_images' =>       array(   'name' => 'max_images',        'type' => PLX_Attributes::NUMERIC,        'group' => 'Images',                 'display' => 'Max Images'                  ),

			'sort_by' =>          array(   'name' => 'sort_by',           'type' => PLX_Attributes::TEXT_VALUE,     'group' => 'Sorting',                'display' => 'Sort By'                     ),
			'sort_type' =>        array(   'name' => 'sort_type',         'type' => PLX_Attributes::TEXT_VALUE,     'group' => 'Sorting',                'display' => 'Direction'                   )
		));

		$parameters['sort_by']['fixed'] = true;
		$parameters['sort_by']['values'] = $sort_values;
		$parameters['sort_by']['values']['images'] = 'Images';

		$parameters['sort_type']['fixed'] = true;
		$parameters['sort_type']['values'] = array('asc' => 'Ascending', 'desc' => 'Descending');

		return $parameters;
	}
}


class PLX_Placester_Attributes extends PLX_Attributes {
	public function _get_group_title($group, $listing_type = null) {
		if($group == 'Basic') {
			if($listing_type == 'res_sale' || $listing_type == 'res_rental')
				return 'Basic Residential Details';
			if($listing_type == 'comm_sale' || $listing_type == 'comm_rental')
				return 'Basic Commercial Details';
			if($listing_type == 'vac_rental')
				return 'Basic Vacation Details';

			return 'Basic Property Details';
		}

		if($group == 'Listing') return 'Listing';
		if($group == 'Location') return 'Location';

		if($group == 'Terms') return 'Lease Terms';
		if($group == 'Notes') return 'Financial Notes';
		if($group == 'Building') return 'Building Details';
		if($group == 'Parking') return 'Parking Information';
		if($group == 'Pets') return 'Pets';
		if($group == 'Schools') return 'Schools';
		if($group == 'Lot') return 'Lot Details';
		if($group == 'Amenities') return 'Property Amenities';
		if($group == 'Neighborhood') return 'Neighborhood Features';

		return $group;
	}

	public function _get_basic_attributes($listing_type = null) {
		$basic_attributes = parent::_get_basic_attributes($listing_type);

		// generic attributes for display
		if(!$listing_type)
			return $basic_attributes;

		// the parameter is used to select attributes for private listing creation
		$listing_attributes = array(
			'Listing' => $basic_attributes['Listing'],
			'Location' => $basic_attributes['Location']
		);

		switch($listing_type) {
			case 'res_sale':
				$listing_attributes['Basic'] = $basic_attributes['Basic'];
				break;

			case 'res_rental':
				$listing_attributes['Basic'] = $basic_attributes['Basic'];
				$listing_attributes['Terms'] = $basic_attributes['Terms'];
				break;

			case 'comm_sale':
				$listing_attributes['Basic'] = array(
					'price' => $this->attributes['price'],
					'sqft' => $this->attributes['sqft'],
					'loc_desc' => $this->attributes['loc_desc'],
					'zone_desc' => $this->attributes['zone_desc'],
					'desc' => $this->attributes['desc']
				);
				break;

			case 'comm_rental':
				$listing_attributes['Basic'] = array(
					'price' => $this->attributes['price'],
					'sqft' => $this->attributes['sqft'],
					'loc_desc' => $this->attributes['loc_desc'],
					'zone_desc' => $this->attributes['zone_desc'],
					'desc' => $this->attributes['desc']
				);
				$listing_attributes['Terms'] = array(
					'lse_trms' => $this->attributes['lse_trms'],
					'lse_type' => $this->attributes['lse_type'],
					'comm_rate_unit' => $this->attributes['comm_rate_unit'],
					'avail_on' => $this->attributes['avail_on'],
					'sublse' => $this->attributes['sublse'],
					'bld_suit' => $this->attributes['bld_suit'],
				);
				break;

			case 'vac_rental':
				$listing_attributes['Basic'] = array(
					'price' => $this->attributes['price'],
					'sqft' => $this->attributes['sqft'],
					'beds' => $this->attributes['beds'],
					'baths' => $this->attributes['baths'],
					'half_baths' => $this->attributes['half_baths'],
					'accoms' => $this->attributes['accoms'],
					'avail_info' => $this->attributes['avail_info'],
					'desc' => $this->attributes['desc']
				);
				$listing_attributes['Terms'] = $basic_attributes['Terms'];
				break;

			default:
				$listing_attributes['Basic'] = $basic_attributes['Basic'];
				break;
		}

		return $listing_attributes;
	}

	protected function _get_extended_attributes($listing_type = null) {
		$extended_attributes = parent::_get_extended_attributes($listing_type);

		// generic attributes for display
		if(!$listing_type)
			return $extended_attributes;

		$listing_attributes = array();
		switch($listing_type) {
			case 'res_sale':
				$listing_attributes['Notes'] = $extended_attributes['Notes'];
				$listing_attributes['Building'] = $extended_attributes['Building'];
				$listing_attributes['Parking'] = $extended_attributes['Parking'];
				$listing_attributes['Pets'] = $extended_attributes['Pets'];
				$listing_attributes['Schools'] = $extended_attributes['Schools'];
				$listing_attributes['Lot'] = $extended_attributes['Lot'];
				$listing_attributes['Amenities'] = $extended_attributes['Amenities'];
				$listing_attributes['Neighborhood'] = $extended_attributes['Neighborhood'];
				break;

			case 'res_rental':
				$listing_attributes['Building'] = $extended_attributes['Building'];
				$listing_attributes['Parking'] = $extended_attributes['Parking'];
				$listing_attributes['Pets'] = $extended_attributes['Pets'];
				$listing_attributes['Amenities'] = $extended_attributes['Amenities'];
				$listing_attributes['Neighborhood'] = $extended_attributes['Neighborhood'];
				break;

			case 'comm_sale':
				$listing_attributes['Building'] = $extended_attributes['Building'];
				$listing_attributes['Lot'] = $extended_attributes['Lot'];
				break;

			case 'comm_rental':
				$listing_attributes['Building'] = $extended_attributes['Building'];
				$listing_attributes['Parking'] = $extended_attributes['Parking'];
				break;

			case 'vac_rental':
				$listing_attributes['Parking'] = $extended_attributes['Parking'];
				$listing_attributes['Pets'] = $extended_attributes['Pets'];
				$listing_attributes['Amenities'] = $extended_attributes['Amenities'];
				break;

			default:
				$listing_attributes = $extended_attributes;
		}

		return $listing_attributes;
	}

	protected function _define_attributes() {
		$attributes = parent::_define_attributes();

		$attributes = array_merge($attributes, array(
			// Sale Notes
			'lndr_own' =>         array(   'name' => 'lndr_own',         'type' => self::TEXT_VALUE,     'group' => 'Notes',                  'display' => 'Lender Owned'                ),
			'hoa_fee' =>          array(   'name' => 'hoa_fee',          'type' => self::CURRENCY,       'group' => 'Notes',                  'display' => 'HOA Fee'                     ),
			'hoa_mand' =>         array(   'name' => 'hoa_mand',         'type' => self::BOOLEAN,        'group' => 'Notes',                  'display' => 'HOA Mandatory'               ),

			// Building Info
			'cons_stts' =>        array(   'name' => 'cons_stts',        'type' => self::TEXT_VALUE,     'group' => 'Building',               'display' => 'Construction Status'         ),
			'prop_name' =>        array(   'name' => 'prop_name',        'type' => self::SHORT_TEXT,     'group' => 'Building',               'display' => 'Property Name'               ),
			'style' =>            array(   'name' => 'style',            'type' => self::TEXT_VALUE,     'group' => 'Building',               'display' => 'Style'                       ),
			'floors' =>           array(   'name' => 'floors',           'type' => self::NUMERIC,        'group' => 'Building',               'display' => 'Floors'                      ),
			'year_blt' =>         array(   'name' => 'year_blt',         'type' => self::NUMERIC,        'group' => 'Building',               'display' => 'Year Built'                  ),

			// Parking Info
			'park_type' =>        array(   'name' => 'park_type',        'type' => self::TEXT_VALUE,     'group' => 'Parking',                'display' => 'Parking Type'                ),
			'pk_spce' =>          array(   'name' => 'pk_spce',          'type' => self::NUMERIC,        'group' => 'Parking',                'display' => 'Parking Spaces'              ),
			'pk_lease' =>         array(   'name' => 'pk_lease',         'type' => self::BOOLEAN,        'group' => 'Parking',                'display' => 'Parking Included'            ),
			'valet' =>            array(   'name' => 'valet',            'type' => self::BOOLEAN,        'group' => 'Parking',                'display' => 'Valet'                       ),
			'guard' =>            array(   'name' => 'guard',            'type' => self::BOOLEAN,        'group' => 'Parking',                'display' => 'Guarded'                     ),
			'heat' =>             array(   'name' => 'heat',             'type' => self::BOOLEAN,        'group' => 'Parking',                'display' => 'Heated'                      ),
			'carwsh' =>           array(   'name' => 'carwsh',           'type' => self::BOOLEAN,        'group' => 'Parking',                'display' => 'Carwash'                     ),

			// Pets Info
			'cats' =>             array(   'name' => 'cats',             'type' => self::BOOLEAN,        'group' => 'Pets',                   'display' => 'Cats Allowed'                ),
			'dogs' =>             array(   'name' => 'dogs',             'type' => self::BOOLEAN,        'group' => 'Pets',                   'display' => 'Dogs Allowed'                ),
			'pets_cond' =>        array(   'name' => 'pets_cond',        'type' => self::BOOLEAN,        'group' => 'Pets',                   'display' => 'Pets Conditional'            ),

			// Schools
			'sch_dist' =>         array(   'name' => 'sch_dist',         'type' => self::TEXT_VALUE,     'group' => 'Schools',                'display' => 'School District'             ),
			'sch_elm' =>          array(   'name' => 'sch_elm',          'type' => self::TEXT_VALUE,     'group' => 'Schools',                'display' => 'Elementary School'           ),
			'sch_jnr' =>          array(   'name' => 'sch_jnr',          'type' => self::TEXT_VALUE,     'group' => 'Schools',                'display' => 'Middle School'               ),
			'sch_hgh' =>          array(   'name' => 'sch_hgh',          'type' => self::TEXT_VALUE,     'group' => 'Schools',                'display' => 'High School'                 ),

			// Lot Info
			'lt_sz' =>            array(   'name' => 'lt_sz',            'type' => self::NUMERIC,        'group' => 'Lot',                    'display' => 'Lot Size'                    ),
			'lt_sz_unit' =>       array(   'name' => 'lt_sz_unit',       'type' => self::TEXT_VALUE,     'group' => 'Lot',                    'display' => 'Lot Size Unit'               ),
			'corner' =>           array(   'name' => 'corner',           'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Corner'                      ),
			'wooded' =>           array(   'name' => 'wooded',           'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Wooded'                      ),
			'pvd_drv' =>          array(   'name' => 'pvd_drv',          'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Paved Drive'                 ),
			'und_st_tnk' =>       array(   'name' => 'und_st_tnk',       'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Underground Storage Tank'    ),
			'stream' =>           array(   'name' => 'stream',           'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Stream'                      ),
			'glf_frt' =>          array(   'name' => 'glf_frt',          'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Golf Course Frontage'        ),
			'add_lnd_ava' =>      array(   'name' => 'add_lnd_ava',      'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Additional Land Available'   ),
			'zr_lt_lne' =>        array(   'name' => 'zr_lt_lne',        'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Zero Lot Line'               ),
			'fld_pln' =>          array(   'name' => 'fld_pln',          'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Flood Plain'                 ),
			'shrd_drv' =>         array(   'name' => 'shrd_drv',         'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Shared Drive'                ),
			'cty_view' =>         array(   'name' => 'cty_view',         'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'City View'                   ),
			'clrd' =>             array(   'name' => 'clrd',             'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Cleared'                     ),
			'frmlnd' =>           array(   'name' => 'frmlnd',           'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Farmland'                    ),
			'fencd_encld' =>      array(   'name' => 'fencd_encld',      'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Fenced/Enclosed'             ),
			'fll_ndd' =>          array(   'name' => 'fll_ndd',          'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Fill Needed'                 ),
			'gntl_slpe' =>        array(   'name' => 'gntl_slpe',        'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Gentle Slope'                ),
			'level' =>            array(   'name' => 'level',            'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Level'                       ),
			'marsh' =>            array(   'name' => 'marsh',            'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Marsh'                       ),
			'sloping' =>          array(   'name' => 'sloping',          'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Sloping'                     ),
			'stp_slpe' =>         array(   'name' => 'stp_slpe',         'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Steep Slope'                 ),
			'scenic' =>           array(   'name' => 'scenic',           'type' => self::BOOLEAN,        'group' => 'Lot',                    'display' => 'Scenic Views'                ),

			// Amenities
			'grnt_tops' =>        array(   'name' => 'grnt_tops',        'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Granite Countertops'         ),
			'air_cond' =>         array(   'name' => 'air_cond',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Air Conditioning'            ),
			'cent_ac' =>          array(   'name' => 'cent_ac',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Central A/C'                 ),
			'frnshed' =>          array(   'name' => 'frnshed',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Furnished'                   ),
			'cent_ht' =>          array(   'name' => 'cent_ht',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Central Heat'                ),
			'frplce' =>           array(   'name' => 'frplce',           'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Fireplace'                   ),
			'hv_ceil' =>          array(   'name' => 'hv_ceil',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'High/Vaulted Ceiling'        ),
			'wlk_clst' =>         array(   'name' => 'wlk_clst',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Walk-In Closet'              ),
			'hdwdflr' =>          array(   'name' => 'hdwdflr',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Hardwood Floor'              ),
			'tle_flr' =>          array(   'name' => 'tle_flr',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Tile Floor'                  ),
			'fm_lv_rm' =>         array(   'name' => 'fm_lv_rm',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Family/Living Room'          ),
			'bns_rec_rm' =>       array(   'name' => 'bns_rec_rm',       'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Bonus/Rec Room'              ),
			'lft_lyout' =>        array(   'name' => 'lft_lyout',        'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Loft Layout'                 ),
			'off_den' =>          array(   'name' => 'off_den',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Office/Den'                  ),
			'dng_rm' =>           array(   'name' => 'dng_rm',           'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Dining Room'                 ),
			'brkfst_nk' =>        array(   'name' => 'brkfst_nk',        'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Breakfast Nook'              ),
			'dshwsher' =>         array(   'name' => 'dshwsher',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Dishwasher'                  ),
			'refrig' =>           array(   'name' => 'refrig',           'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Refrigerator'                ),
			'stve_ovn' =>         array(   'name' => 'stve_ovn',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Stove/Oven'                  ),
			'stnstl_app' =>       array(   'name' => 'stnstl_app',       'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Stainless Steel Appliances'  ),
			'attic' =>            array(   'name' => 'attic',            'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Attic'                       ),
			'basemnt' =>          array(   'name' => 'basemnt',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Basement'                    ),
			'washer' =>           array(   'name' => 'washer',           'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Washer'                      ),
			'dryer' =>            array(   'name' => 'dryer',            'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Dryer'                       ),
			'lndry_in' =>         array(   'name' => 'lndry_in',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Laundry Area - Inside'       ),
			'lndry_gar' =>        array(   'name' => 'lndry_gar',        'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Laundry Area - Garage'       ),
			'blc_deck_pt' =>      array(   'name' => 'blc_deck_pt',      'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Balcony/Deck/Patio'          ),
			'yard' =>             array(   'name' => 'yard',             'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Yard'                        ),
			'swm_pool' =>         array(   'name' => 'swm_pool',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Swimming Pool'               ),
			'jacuzzi' =>          array(   'name' => 'jacuzzi',          'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Jacuzzi/Whirlpool'           ),
			'sauna' =>            array(   'name' => 'sauna',            'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Sauna'                       ),
			'cble_rdy' =>         array(   'name' => 'cble_rdy',         'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'Cable-ready'                 ),
			'hghspd_net' =>       array(   'name' => 'hghspd_net',       'type' => self::BOOLEAN,        'group' => 'Amenities',              'display' => 'High-speed Internet'         ),

			// Neighborhood Features
			'ngb_trans' =>        array(   'name' => 'ngb_trans',        'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Public Transportation'       ),
			'ngb_shop' =>         array(   'name' => 'ngb_shop',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Shopping'                    ),
			'ngb_pool' =>         array(   'name' => 'ngb_pool',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Swimming Pool'               ),
			'ngb_court' =>        array(   'name' => 'ngb_court',        'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Tennis Court'                ),
			'ngb_park' =>         array(   'name' => 'ngb_park',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Park'                        ),
			'ngb_trails' =>       array(   'name' => 'ngb_trails',       'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Walk/Jog Trails'             ),
			'ngb_stbles' =>       array(   'name' => 'ngb_stbles',       'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Stables'                     ),
			'ngb_golf' =>         array(   'name' => 'ngb_golf',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Golf Courses'                ),
			'ngb_med' =>          array(   'name' => 'ngb_med',          'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Medical Facilities'          ),
			'ngb_bike' =>         array(   'name' => 'ngb_bike',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Bike Path'                   ),
			'ngb_cons' =>         array(   'name' => 'ngb_cons',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Conservation Area'           ),
			'ngb_hgwy' =>         array(   'name' => 'ngb_hgwy',         'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Highway Access'              ),
			'ngb_mar' =>          array(   'name' => 'ngb_mar',          'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Marina'                      ),
			'ngb_pvtsch' =>       array(   'name' => 'ngb_pvtsch',       'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Private School'              ),
			'ngb_pubsch' =>       array(   'name' => 'ngb_pubsch',       'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'Public School'               ),
			'ngb_uni' =>          array(   'name' => 'ngb_uni',          'type' => self::BOOLEAN,        'group' => 'Neighborhood',           'display' => 'University'                  ),

			// Commercial Info
			'loc_desc' =>         array(   'name' => 'loc_desc',         'type' => self::SHORT_TEXT,     'group' => 'Commercial',             'display' => 'Location Description'        ),
			'zone_desc' =>        array(   'name' => 'zone_desc',        'type' => self::SHORT_TEXT,     'group' => 'Commercial',             'display' => 'Zoning Description'          ),
			'lse_type' =>         array(   'name' => 'lse_type',         'type' => self::TEXT_VALUE,     'group' => 'Commercial',             'display' => 'Lease Type'                  ),
			'comm_rate_unit' =>   array(   'name' => 'comm_rate_unit',   'type' => self::TEXT_VALUE,     'group' => 'Commercial',             'display' => 'Rental Rate'                 ),
			'sublse' =>           array(   'name' => 'sublse',           'type' => self::BOOLEAN,        'group' => 'Commercial',             'display' => 'Sublease'                    ),
			'bld_suit' =>         array(   'name' => 'bld_suit',         'type' => self::BOOLEAN,        'group' => 'Commercial',             'display' => 'Build to Suit'               ),

			// Vacation Rental Info
			'accoms' =>           array(   'name' => 'accoms',           'type' => self::SHORT_TEXT,     'group' => 'Vacation',               'display' => 'Accomodates'                 ),
			'avail_info' =>       array(   'name' => 'avail_info',       'type' => self::SHORT_TEXT,     'group' => 'Vacation',               'display' => 'Availability'                ),
		));


		$attributes['lndr_own']['fixed'] = true;
		$attributes['lndr_own']['values'] = array(
			'' =>               'No',
			'true' =>           'Yes',
			'undis' =>          'Undisclosed'
		);

		$attributes['cons_stts']['fixed'] = true;
		$attributes['cons_stts']['values'] = array(
			'exstng' =>         'Existing',
			'new_cnst' =>       'New Construction', // in rc?
			'prpsd' =>          'Proposed', // in rc?
			'und_prop' =>       'Under Construction'
		);

		$attributes['style']['fixed'] = false;
		$attributes['style']['values'] = array(
			'bungal' =>         'Bungalow',
			'cape' =>           'Cape Cod',
			'colonial' =>       'Colonial',
			'contemp' =>        'Contemporary',
			'cott' =>           'Cottage',
			'farmh' =>          'Farmhouse',
			'fnt_bk_splt' =>    'Front to Back Split',
			'gamb_dutc'=>       'Gambrel / Dutch',
			'garrison' =>       'Garrison',
			'greek_rev' =>      'Greek Revival',
			'loft_splt' =>      'Lofted Split',
			'mult_lvl' =>       'Multi-level',
			'rai_ranch' =>      'Raised Ranch',
			'ranch' =>          'Ranch',
			'saltb' =>          'Saltbox',
			'split_ent' =>      'Split Entry',
			'tudor' =>          'Tudor',
			'victor' =>         'Victorian',
			'antiq' =>          'Antique'
		);

		$attributes['park_type']['fixed'] = false;
		$attributes['park_type']['values'] = array(
			'atch_gar' =>       'Attached Garage',
			'dtch_gar' =>       'Detached Garage',
			'cov' =>            'Covered',
			'strt' =>           'On-street',
			'off_str' =>        'Off-street',
			'tan' =>            'Tandem'
		);

		$attributes['lt_sz_unit']['fixed'] = true;
		$attributes['lt_sz_unit']['values'] = array(
		);

		$attributes['lse_type']['fixed'] = true;
		$attributes['lse_type']['values'] = array(
		);

		$attributes['comm_rate_unit']['fixed'] = true;
		$attributes['comm_rate_unit']['values'] = array(
		);

		$attributes['sch_dist']['fixed'] = false;
		$attributes['sch_elm']['fixed'] = false;
		$attributes['sch_jnr']['fixed'] = false;
		$attributes['sch_hgh']['fixed'] = false;

		return $attributes;
	}
}
