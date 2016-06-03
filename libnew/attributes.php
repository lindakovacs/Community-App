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
			$this->attributes = self::_define_attributes();

		$basic_attributes = self::get_group_attributes(array('Listing', 'Location', 'Basic', 'Terms'));

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
		return array();
	}

	protected function _get_group_title($group, $listing_type = null) {
		if($group == 'Basic') return 'Basic Property Details';
		if($group == 'Terms') return 'Lease Terms';

		return $group;
	}

	protected function _get_group_attributes($groups, $flatten = false) {
		if(!isset($this->attributes))
			$this->attributes = self::_define_attributes();

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
		return $this->get_static_values($name);
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


	protected function _get($name) {
		if(!isset($this->parameters))
			$this->parameters = $this->_define_parameters(); // additional search terms, non-attribute based

		if(isset($this->parameters[$name])) return $this->parameters[$name];

		if(in_array($prefix = substr($name, 0, 4), array('min_', 'max_'))) {
			if($parameter = PLX_Attributes::get($attribute = substr($name, 4)))
				if(in_array($parameter['type'], array())) {
					$parameter['name'] = $name;
					$parameter['attribute'] = $attribute;
					$parameter['display'] = ucfirst(str_replace('_', ' ', $prefix));
					return $parameter;
				}

			return null;
		}

		if($parameter = PLX_Attributes::get($name)) {
			if(in_array($name, array('beds', 'baths', 'half_baths'))) {
				$parameter['attribute'] = $name;
				return $parameter;
			}
			if(in_array($parameter['type'], array())) {
				$parameter['attribute'] = $name;
				return $parameter;
			}
		}

		return null;
	}

	protected function _get_parameters() {
		return self::this()->_get_parameters();
	}

	protected function _get_basic_parameters() {
		return self::this()->_get_basic_parameters();
	}

	protected function _get_extended_parameters() {
		return self::this()->_get_extended_parameters();
	}

	protected function _get_group_title($group) {
		return self::this()->_get_group_title($group);
	}

	protected function _get_group_parameters($groups, $flatten = false) {
		return self::this()->_get_group_parameters($groups, $flatten);
	}

	protected function _get_parameter_values($name, $static = true, $dynamic = true, $none = null) {
	}

	protected function _get_static_values($name, $none = null) {
		return self::this()->_get_static_values($name, $none);
	}

	protected function _get_dynamic_values($name, $none = null) {
		return self::this()->_get_dynamic_values($name, $none);
	}

	protected function _define_parameters() {
		return array (
			'sort_by' =>          array(   'name' => 'sort_by',           'type' => PLX_Attributes::TEXT_VALUE,     'group' => 'Sorting',                'display' => 'Sort By'                     ),
			'sort_type' =>        array(   'name' => 'sort_type',         'type' => PLX_Attributes::TEXT_VALUE,     'group' => 'Sorting',                'display' => 'Direction'                   ),

			'images' =>           array(   'name' => 'images',            'type' => PLX_Attributes::BOOLEAN,        'group' => 'Images',                 'display' => 'Images'                      ),

			'agency_only' =>      array(   'name' => 'agency_only',       'type' => PLX_Attributes::BOOLEAN,        'group' => 'Listing',                'display' => 'Agency Listings'             ),
			'non_import' =>       array(   'name' => 'non_import',        'type' => PLX_Attributes::BOOLEAN,        'group' => 'Listing',                'display' => 'Private Listings'            ),
			'include_disabled' => array(   'name' => 'include_disabled',  'type' => PLX_Attributes::BOOLEAN,        'group' => 'Listing',                'display' => 'Disabled Listings'           )
		);
	}
}


class PLX_Provider_Attributes extends PLX_Attributes {
	public static function init() {}

	public static function check_attribute() {
		$providers = PL_Listing::aggregates(array('keys' => array('provider_id')));
		$providers = $providers['provider_id'];

		$response = PL_Listing::get(array('non_import' => 1, 'limit' => 1));
		if($response['total']) $non_import = 1; else $non_import = 0;

		// $attributes = self::get_extended_attributes();
		$attributes = self::get_group_attributes(array('Building', 'Parking'));

		echo '<table>' . "\n";

		foreach($attributes as $name => $group)
			foreach($group as $attribute) {

				echo '<tr><td>' . $attribute['display'] . '</td>';

				foreach($providers as $provider) {
					$response = PL_Listing::get(array(
						'provider_id' => $provider, 'limit' => 1,
						'metadata[' . $attribute['name'] . '_match]' => 'exists',
						'metadata[' . $attribute['name'] . ']' => 1));

					echo '<td>';

					if(!isset($response['total']))
						echo '?';
					else if($response['total'] > 0 && !isset($response['listings'][0]['cur_data'][$attribute['name']]))
						echo 'x';
					else
						echo $response['total'];

					echo '</td>';
				}

				if($non_import) {
					$response = PL_Listing::get(array(
						'non_import' => 1, 'limit' => 1,
						'metadata[' . $attribute['name'] . '_match]' => 'exists',
						'metadata[' . $attribute['name'] . ']' => 1));

					echo '<td>';

					if(!isset($response['total']))
						echo '?';
					else if($response['total'] > 0 && !isset($response['listings'][0]['cur_data'][$attribute['name']]))
						echo 'x';
					else
						echo $response['total'];

					echo '</td>';
				}

				echo '</tr>' . "\n";
			}

		echo '</table>' . "\n";
	}

	public static function check_attributes() {
		$providers = PL_Listing::aggregates(array('keys' => array('provider_id')));
		$providers = $providers['provider_id'];

		$response = PL_Listing::get(array('non_import' => 1, 'limit' => 1));
		if($response['total']) $non_import = 1; else $non_import = 0;

		// $attributes = self::get_extended_attributes();
		$attributes = self::get_group_attributes(array('Building', 'Parking'));

		echo '<table>' . "\n";

		foreach($attributes as $name => $group)
			foreach($group as $attribute) {

				echo '<tr><td>' . $attribute['display'] . '</td>';

				foreach($providers as $provider) {
					$response = PL_Listing::get(array(
						'provider_id' => $provider, 'limit' => 1,
						'metadata[' . $attribute['name'] . '_match]' => 'exists',
						'metadata[' . $attribute['name'] . ']' => 1));

					echo '<td>';

					if(!isset($response['total']))
						echo '?';
					else if($response['total'] > 0 && !isset($response['listings'][0]['cur_data'][$attribute['name']]))
						echo 'x';
					else
						echo $response['total'];

					echo '</td>';
				}

				if($non_import) {
					$response = PL_Listing::get(array(
						'non_import' => 1, 'limit' => 1,
						'metadata[' . $attribute['name'] . '_match]' => 'exists',
						'metadata[' . $attribute['name'] . ']' => 1));

					echo '<td>';

					if(!isset($response['total']))
						echo '?';
					else if($response['total'] > 0 && !isset($response['listings'][0]['cur_data'][$attribute['name']]))
						echo 'x';
					else
						echo $response['total'];

					echo '</td>';
				}

				echo '</tr>' . "\n";
			}

		echo '</table>' . "\n";
	}

	public static function custom_attributes() {

	}
}
