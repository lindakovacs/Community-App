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

	public static function get_group_title($group, $listing_type = null) {
		return self::this()->_get_group_title($group, $listing_type);
	}

	public static function get_group_attributes($groups, $flatten = false) {
		return self::this()->_get_group_attributes($groups, $flatten);
	}

	public static function get_basic_attributes($listing_type = null) {
		return self::this()->_get_basic_attributes($listing_type);
	}

	public static function get_extended_attributes($listing_type = null) {
		return self::this()->_get_extended_attributes($listing_type);
	}

	public static function get_listing_attributes($listing_type = null) {
		return self::this()->_get_listing_attributes($listing_type);
	}

	public static function get_static_values($name, $none = null) {
		return self::this()->_get_static_values($name, $none);
	}

	public static function get_dynamic_values($name, $none = null) {
		return self::this()->_get_dynamic_values($name, $none);
	}

	public static function get_attribute_values($name, $static = true, $dynamic = true, $none = null) {
		return self::this()->_get_attribute_values($name, $static, $dynamic, $none);
	}


	// default implementation -- derive and use PLX_Data_Interface::set_attribute_interface to override
	protected $attributes;

	protected function _get($name) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	public function _get_attributes() {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		return $this->attributes;
	}

	public function _get_group_title($group, $listing_type = null) {
		if($group == 'Basic') return 'Basic Property Details';
		if($group == 'Terms') return 'Lease Terms';

		return $group;
	}

	public function _get_group_attributes($groups, $flatten = false) {
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

	public function _get_basic_attributes($listing_type = null) {
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

	protected function _get_listing_attributes($listing_type = null) {
		if($listing_type)
			return array_merge($this->_get_basic_attributes($listing_type), $this->_get_extended_attributes($listing_type));
		else
			return $this->_get_attributes();
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

	protected function _get_attribute_values($name, $static = true, $dynamic = true, $none = null) {
		return $this->get_static_values($name);
	}


	protected function _define_attributes() {
		$attributes = array(
			'id' =>               array(   'name' => 'pdx_id',           'type' => self::TEXT_ID,        'group' => 'Listing',                'display' => 'Listing ID'                  ),

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


class PLX_Search_Terms extends PLX_Attributes {
	static protected $terms;

	public static function get($name) {
		if(!isset(self::$terms))
			self::$terms = self::_define_terms(); // additional search terms, non-attribute based

		if(isset(self::$terms[$name])) return self::$terms[$name];

		$term = $name; $prefix = substr($name, 0, 4);
		if(in_array($prefix, array('min_', 'max_')))
			$name = substr($name, 4);
		else
			$prefix = null;

		if(!$attribute = parent::get($name))
			if($attribute = parent::get($term))
				$prefix = null;

		if($attribute) {
			$caps = self::get_actual_caps($attribute);
			if(!$caps['search'] || ($prefix && !$caps['minmax']))
				$attribute = null;

			else {
				if($prefix) {
					$attribute['name'] = $term;
					$attribute['minmax'] = true;
					$attribute['display'] = str_replace('_', ' ', ucfirst($prefix)) . $attribute['display'];
				}
				$attribute['actual_caps'] = $caps;
				$attribute['public_caps'] = self::get_public_caps($attribute);
			}
		}

		return self::$terms[$term] = $attribute;
	}

	protected static function get_public_caps($attribute) {
		return null;
	}

	protected static function get_actual_caps($attribute) {
		if(!isset(self::$caps))
			self::$caps = self::_define_caps(); // search feature limitations of the data api
	}

	private static function _define_terms() {
		return array (
			'sort_by' =>          array(   'name' => 'sort_by',           'type' => self::TEXT_VALUE,     'group' => 'Sorting',                'display' => 'Sort By'                     ),
			'sort_type' =>        array(   'name' => 'sort_type',         'type' => self::TEXT_VALUE,     'group' => 'Sorting',                'display' => 'Direction'                   ),

			'total_images' =>     array(   'name' => 'agency_only',       'type' => self::BOOLEAN,        'group' => 'Images',                 'display' => 'Total Images'                ),

			'agency_only' =>      array(   'name' => 'agency_only',       'type' => self::BOOLEAN,        'group' => 'Listing',                'display' => 'Agency Listings'             ),
			'non_import' =>       array(   'name' => 'non_import',        'type' => self::BOOLEAN,        'group' => 'Listing',                'display' => 'Private Listings'            ),
			'include_disabled' => array(   'name' => 'include_disabled',  'type' => self::BOOLEAN,        'group' => 'Listing',                'display' => 'Disabled Listings'           )
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


class Bazinga extends PLX_Attributes {
	protected function _get($name) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	public function _get_attributes() {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		return $this->attributes;
	}

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

	public function _get_group_attributes($groups, $flatten = false) {
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

	public function _get_basic_attributes($listing_type = null) {
		if(!isset($this->attributes))
			$this->attributes = self::_define_attributes();

		$basic_attributes = self::get_group_attributes(array('Listing', 'Location', 'Basic', 'Terms'));

		// generic attributes for display
		if(!$listing_type)
			return $basic_attributes;

		// the parameter is used to select attributes for private listing creation
		$listing_attributes = array(
			'Listing' => array(
				'listing_type' => $this->attributes['listing_type'],
				'property_type' => $this->attributes['property_type'],
				'status' => $this->attributes['status']),

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
				$basic_attributes['Listing'] = $listing_attributes['Listing'];
				$listing_attributes = $basic_attributes;
				break;
		}

		return $listing_attributes;
	}

	protected function _get_extended_attributes($listing_type = null) {
		if(!isset($this->attributes))
			$this->attributes = $this->_define_attributes();

		$extended_attributes = $this->_get_group_attributes(array('Notes', 'Building', 'Parking',
			'Pets', 'Schools', 'Lot', 'Amenities', 'Neighborhood', 'Commercial', 'Vacation'));

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

	protected function _get_listing_attributes($listing_type = null) {
		return array_merge($this->_get_basic_attributes($listing_type), $this->_get_extended_attributes($listing_type));
	}


	protected function _get_static_values($name, $none = null) {
		if(!isset($this->values))
			$this->values = $this->_define_values();

		return isset($this->values[$name]) ? $this->values[$name]['options'] : null;
	}

	protected function _get_dynamic_values($name, $none = null) {
		if(!isset($this->values))
			$this->values = $this->_define_values();

		return isset($this->values[$name]) ? null : null;
	}

	protected function _get_attribute_values($name, $static = true, $dynamic = true, $none = null) {
		if(!isset($this->values))
			$this->values = $this->_define_values();

		return isset($this->values[$name]) ? $this->values[$name]['options'] : null;
	}

	protected function _define_attributes() {
		return array (
			'pdx_id' =>           array(   'name' => 'pdx_id',           'type' => self::TEXT_ID,        'group' => 'Listing',                'display' => 'PDX ID'                      ),
			'mls_id' =>           array(   'name' => 'mls_id',           'type' => self::TEXT_ID,        'group' => 'Listing',                'display' => 'MLS ID'                      ),

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
			'deposit' =>          array(   'name' => 'deposit',          'type' => self::CURRENCY,       'group' => 'Terms',                  'display' => 'Deposit'                     ),

			// Attribution
			'aid' =>              array(   'name' => 'aid',              'type' => self::TEXT_ID,        'group' => 'Attribution',            'display' => 'Agent ID'                    ),
			'aname' =>            array(   'name' => 'aname',            'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Agent Name'                  ),
			'aphone' =>           array(   'name' => 'aphone',           'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Agent Phone'                 ),
			'alicense' =>         array(   'name' => 'alicense',         'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Agent License'               ),

			'oid' =>              array(   'name' => 'oid',              'type' => self::TEXT_ID,        'group' => 'Attribution',            'display' => 'Office ID'                   ),
			'oname' =>            array(   'name' => 'oname',            'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Office Name'                 ),
			'ophone' =>           array(   'name' => 'ophone',           'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Office Phone'                ),

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

			// Co-attribution
			'acoid' =>            array(   'name' => 'acoid',            'type' => self::TEXT_ID,        'group' => 'Attribution',            'display' => 'Co-agent ID'                 ),
			'aconame' =>          array(   'name' => 'aconame',          'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Co-agent Name'               ),
			'acophone' =>         array(   'name' => 'acophone',         'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Co-agent Phone'              ),
			'acolicense' =>       array(   'name' => 'acolicense',       'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Co-agent License'            ),

			'ocoid' =>            array(   'name' => 'ocoid',            'type' => self::TEXT_ID,        'group' => 'Attribution',            'display' => 'Co-office ID'                ),
			'oconame' =>          array(   'name' => 'oconame',          'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Co-office Name'              ),
			'ocophone' =>         array(   'name' => 'ocophone',         'type' => self::SHORT_TEXT,     'group' => 'Attribution',            'display' => 'Co-office Phone'             ),
		);
	}

	protected function _define_values() {
		return array (
			'listing_type' =>     array(   'name' => 'listing_type',     'fixed' => true,     'options' => array(
				'res_sale' =>       'Residential Sale',
				'res_rental' =>     'Residential Rental',
				'comm_sale' =>      'Commercial Sale',
				'comm_rental' =>    'Commercial Rental',
				'vac_rental' =>     'Vacation Rental'
			)),

			'zoning_type' =>      array(   'name' => 'zoning_type',      'fixed' => true,     'options' => array(
				'residential' =>    'Residential',
				'commercial' =>     'Commercial'
			)),

			'purchase_type' =>    array(   'name' => 'purchase_type',    'fixed' => true,     'options' => array(
				'sale' =>           'Sale',
				'rental' =>         'Residential'
			)),

			'status' =>           array(   'name' => 'status',           'fixed' => false,    'options' => array(
				'Active',
				'Pending',
				'Sold'
			)),

			'lse_trms' =>         array(   'name' => 'lse_trms',         'fixed' => true,     'options' => array(
				'per_mnt' =>        'Per Month',
				'per_ngt' =>        'Per Night',
				'per_wk' =>         'Per Week',
				'per_yr' =>         'Per Year'
			)),

			'lndr_own' =>         array(   'name' => 'lndr_own',         'fixed' => true,     'options' => array(
				'' =>               'No',
				'true' =>           'Yes',
				'undis' =>          'Undisclosed'
			)),

			'cons_stts' =>        array(   'name' => 'cons_stts',        'fixed' => true,     'options' => array(
				'exstng' =>         'Existing',
				'new_cnst' =>       'New Construction', // in rc?
				'prpsd' =>          'Proposed', // in rc?
				'und_prop' =>       'Under Construction'
			)),

			'style' =>            array(   'name' => 'style',            'fixed' => false,    'options' => array(
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
			)),

			'park_type' =>        array(   'name' => 'park_type',        'fixed' => false,    'options' => array(
				'atch_gar' =>       'Attached Garage',
				'dtch_gar' =>       'Detached Garage',
				'cov' =>            'Covered',
				'strt' =>           'On-street',
				'off_str' =>        'Off-street',
				'tan' =>            'Tandem'
			)),

			'lt_sz_unit' =>       array(   'name' => 'lt_sz_unit',       'fixed' => true,     'options' => array(
			)),

			'lse_type' =>         array(   'name' => 'lse_type',         'fixed' => true,     'options' => array(
			)),
			'comm_rate_unit' =>   array(   'name' => 'comm_rate_unit',   'fixed' => true,     'options' => array(
			)),

			'property_type' =>    array(   'name' => 'property_type',    'fixed' => false   ),

			'locality' =>         array(   'name' => 'locality',         'fixed' => false   ),
			'region' =>           array(   'name' => 'region',           'fixed' => false   ),
			'postal' =>           array(   'name' => 'postal',           'fixed' => false   ),
			'neighborhood' =>     array(   'name' => 'neighborhood',     'fixed' => false   ),
			'county' =>           array(   'name' => 'county',           'fixed' => false   ),

			'sch_dist' =>         array(   'name' => 'sch_dist',         'fixed' => false   ),
			'sch_elm' =>          array(   'name' => 'sch_elm',          'fixed' => false   ),
			'sch_jnr' =>          array(   'name' => 'sch_jnr',          'fixed' => false   ),
			'sch_hgh' =>          array(   'name' => 'sch_hgh',          'fixed' => false   ),

			'country' =>          array(   'name' => 'country',          'fixed' => true,     'options' => array(
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
			)),
		);
	}
}