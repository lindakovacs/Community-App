<?php 

class PL_Listing_Helper {
	public static function results ($args = array(), $global_filters = true) {
		// Handle edge-case $args formatting and value...
		if (!is_array($args)) { $args = wp_parse_args($args); } 
		elseif (empty($args)) { $args = $_GET; }

		// If a list of specific property IDs was passed in, handle acccordingly...
		if (!empty($args['property_ids'])) { 
			$args['listing_ids'] = $args['property_ids']; 
		}

		// check if we are using a custom drawn neighborhood
		if (!empty($args['location']['neighborhood'])){
			$polygons = PL_Option_Helper::get_polygons();
			foreach ($polygons as $polygon) {
				if ($polygon['tax']=='neighborhood' && $polygon['name']==$args['location']['neighborhood']) {
					$args['polygon'] = $polygon['vertices'];
					unset($args['location']);
					break;
				}
			}
		}

		// fetch single inactive listing if specifically requested
		if (!empty($args['listing_ids']) && count($args['listing_ids']) == 1 && !isset($args['include_disabled'])) {
			$args['include_disabled'] = 1;
		}

		// Respect the ability for this function to return results that do NOT respect global filters..
		if ($global_filters && empty($args['listing_ids'])) {
			$args = PL_Global_Filters::merge_global_filters($args); 
		}

		if (!empty($args['purchase_types']) && !is_array($args['purchase_types'])) {
			$args['purchase_types'] = array($args['purchase_types']);
		}
		if (!empty($args['zoning_types']) && !is_array($args['zoning_types'])) {
			$args['zoning_types'] = array($args['zoning_types']);
		}

		// Avoid the data server's address_mode functionality
		$address_mode = $args['address_mode'] ?: (PL_Option_Helper::get_block_address() ? 'polygon' : 'exact');
		$args['address_mode'] = 'exact'; // this overrides the server-side account setting

		// Call the API with the given args...
		$args = self::map_listings_request($args);
		$listings = PLX_Search::listings($args);
		$listings = self::map_listings_response($listings);

		// Make sure it contains listings, then process accordingly...
		if (!empty($listings['listings'])) {
			foreach ($listings['listings'] as $key => $listing) {

				// if the user wants "block" addresses, remove the street number (trying to catch known variations)
				if($address_mode == 'polygon') {
					$listing['location']['address'] = self::obscure_address($listing['location']['address']);
				}
				else if($listing['location']['unit']) {
					$listing['location']['address'] = self::append_address_unit($listing['location']['address'], $listing['location']['unit']);
				}

				$listing['cur_data']['url'] = PL_Pages::get_url($listing['id'], $listing);
				$listing['location']['full_address'] = $listing['location']['address'] . ' ' . $listing['location']['locality'] . ' ' . $listing['location']['region'];
				$listings['listings'][$key] = $listing;
			}
		}

		// Make sure result is structured accordingly if empty/false/invalid...
		if (!is_array($listings) || !is_array($listings['listings'])) {
			$listings = array('listings' => array(), 'total' => 0); 
		}

		return $listings;
	}

	// convert from blueprint format to internal format
	protected static function map_listings_request($request) {
		$mapped = array('_blueprint' => $request);

		foreach($request as $name => $value) {
			if($name == 'listing_ids')
				$mapped['id'] = $value;

			else if($name == 'compound_type')
				$mapped['listing_type'] = $value;
			else if($name == 'zoning_types')
				$mapped['zoning_type'] = $value;
			else if($name == 'purchase_types')
				$mapped['purchase_type'] = $value;

			else if($name == 'property_type')
				$mapped['metadata']['prop_type'] = $value;
			else if($name == 'property_type_match')
				$mapped['metadata']['prop_type_match'] = $value;

			else if($name == 'metadata')
				foreach($value as $meta_name => $meta_value) {
					if($meta_name == 'prop_type')
						$mapped['property_type'] = $meta_value;
					else if($meta_name == 'prop_type_match')
						$mapped['property_type_match'] = $meta_value;

					else if($meta_name == 'status' || $meta_name == 'status_match')
						$mapped['metadata'][$meta_name] = $meta_value;

					else if($meta_name == 'lst_dte')
						$mapped['list_date'] = $meta_value;
					else if($meta_name == 'min_lst_dte')
						$mapped['min_list_date'] = $meta_value;
					else if($meta_name == 'max_lst_dte')
						$mapped['max_list_date'] = $meta_value;

					else if($meta_name == 'dom')
						$mapped['days_on'] = $meta_value;
					else if($meta_name == 'min_dom')
						$mapped['min_days_on'] = $meta_value;
					else if($meta_name == 'max_dom')
						$mapped['max_days_on'] = $meta_value;
					else
						$mapped[$meta_name] = $meta_value;
				}

			else if($name == 'sort_by') {
				if($value == 'compound_type')
					$mapped['sort_by'] = 'listing_type';
				else if($value == 'cur_data.prop_type')
					$mapped['sort_by'] = 'property_type';
				else if($value == 'cur_data.status')
					$mapped['sort_by'] = 'status';
				else if($value == 'cur_data.lst_dte')
					$mapped['sort_by'] = 'list_date';
				else if($value == 'cur_data.dom')
					$mapped['sort_by'] = 'days_on';
				else if($value == 'total_images')
					$mapped['sort_by'] = 'images';

				else
					$mapped['sort_by'] = array_pop(explode('.', $value));
			}

			else if(in_array($name, array('location', 'box', 'rets'))) {
				foreach($value as $group_name => $group_value) {
					$mapped[$group_name] = $group_value;
				}
			}

			else
				$mapped[$name] = $value;
		}

		return $mapped;
	}

	// convert from intrnal format to blueprint format
	protected static function map_listings_response($response) {
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

	protected static function map_listing_response($response) {
		// if we're using a legacy data source, we already have the blueprint-compatible structure
		if(isset($response['_legacy']))
			return $response['_legacy'];

		$mapped = array('location' => '', 'cur_data' => array(), 'uncur_data' => array());

		foreach($response as $name => $value) {
			if($name == 'listing_type')
				$mapped['compound_type'] = $value;
			else if($name == 'zoning_type')
				$mapped['zoning_types'] = (array) $value;
			else if($name == 'purchase_type')
				$mapped['purchase_types'] = (array) $value;

			else if($name == 'property_type')
				$mapped['cur_data']['prop_type'] = $mapped['property_type'] = $value;
			else if($name == 'status')
				$mapped['cur_data']['status'] = $value;
			else if($name == 'list_date')
				$mapped['cur_data']['lst_dte'] = $value;
			else if($name == 'days_on')
				$mapped['cur_data']['dom'] = $value;

			else if($name == 'url')
				$mapped['cur_data']['url'] = $value;

			else if($name == 'latitude')
				$mapped['location']['coords'][0] = $value;
			else if($name == 'longitude')
				$mapped['location']['coords'][1] = $value;

			else if($name == 'images')
				$mapped['images'] = $value;

			else if($group = self::get_attribute_group($name))
				$mapped[$group][$name] = $value;
			else
				$mapped[$name] = $value;
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
			else
				return null;
		}

		return 'uncur_data';
	}

	protected static function obscure_address($address) {
		return preg_replace('{^([0-9]|# ?)([0-9A-Za-z]| ?([\#\&\-\/\,\.]|and) ?)* ([A-DF-MO-RT-Za-df-mo-rt-z] )?}', '', $address);
	}

	protected static function append_address_unit($address, $unit) {
		if(strpos($address, $unit . ' ') !== 0)
			$address .= (strpos($unit, ' ') === false && substr($unit, 0, 1) != '#' ? ' #' : ' ') . $unit;

		return $address;
	}

	public static function details ($args) {
		if (empty($args['property_ids'])) {
			return array('listings' => array(), 'total' => 0);
		}

		// Global filters should be ignored if a specific set of property IDs are requested...
		return self::results($args, false);
	}

	public static function single_listing ($property_id = null) {
		// Sanity check...
		if (empty($property_id)) { return null; }

		// Response is always bundled...
		$listings = self::details(array('property_ids' => array($property_id)));

		// If the listings key isn't empty, return it's first value (there should only be a single listing...)
		$listing = empty($listings['listings']) ? null : $listings['listings'][0];

		return $listing;
	}

	/*
	 * Used primarily to fetch listing details for the property inferred from the URL structure (i.e., for the property details template). 
	 * Returns null if listing no longer exists, and property post is deleted.
	 *
	 * @returns 	array|null 
	 */
	public static function get_listing_in_loop () {
		global $post;

		$listing_data = null;

		if ($post->post_type === PL_Pages::$property_post_type) {
			$listing_data = PL_Pages::get_listing_details();
		}

		return $listing_data;
	}

	public static function custom_attributes ($args = array()) {
		$custom_attributes = PL_Custom_Attributes::get(array('attr_class' => '2'));
		return $custom_attributes;
	}

	// helper sets keys to values
	public static function types_for_options ($return_only = false, $allow_globals = true, $type_key = 'property_type') {
		$options = array();

		// Use merge (with no arguments) to get the existing filters properly formatted for API calls...
		$global_filters = PL_Global_Filters::merge_global_filters();

		// If global filters related to location are set, incorporate those and use aggregates API...
		if ( $allow_globals && !empty($global_filters) && !empty($global_filters[$type_key]) ) {
			$response[$type_key] = (array)$global_filters[$type_key];
		}
		else {
			if($type_key == 'compound_type')
				$type_key = 'listing_type';
			else if($type_key == 'purchase_types')
				$type_key = 'purchase_type';
			else if($type_key == 'zoning_types')
				$type_key = 'zoning_type';

			$response = PLX_Search::aggregates(array('keys' => array($type_key)));
		}

		if(!$response) {
			return array();
		}
		// might be able to do this faster with array_fill_keys() -pk
		foreach ($response[$type_key] as $key => $value) {
			$options[$value] = $value;
		}
		ksort($options);
		$options = array_merge(array('false' => 'Any'), $options);
		return $options;
	}

	private static $memo_locations = array();
	public static function locations_for_options ($return_only = false, $allow_globals = true) {
		$options = array();
		$response = array();
		
		$global_flag = ($allow_globals == true) ? 'global_on' : 'global_off';

		// Check if response is memoized for this request...
		$memoized = empty(self::$memo_locations[$global_flag]) ? false : true;

		if ($memoized) {
			$response = self::$memo_locations[$global_flag];
		}
		else {
			// Use merge (with no arguments) to get the existing filters properly formatted for API calls...
			$global_filters = $allow_globals ? PL_Global_Filters::merge_global_filters() : array();
			
			// If global filters related to location are set, incorporate those and use aggregates API...	
			if (!empty($global_filters) && !empty($global_filters['location'])) {
				$args = $global_filters['location'];
				$args['keys'] = array('locality', 'region', 'postal', 'neighborhood', 'county');
				$response = PLX_Search::aggregates($args);
			}
			else {
				$response = PLX_Search::locations();
			}
			
			// add custom drawn neighborhoods to the lists
			$polygons = PL_Option_Helper::get_polygons();
			foreach ($polygons as $polygon) {
				switch($polygon['tax']) {
					case 'neighborhood':
						$response['neighborhood'][] = $polygon['name'];
				}
			}

			// Memoize...
			self::$memo_locations[$global_flag] = $response;
		}	
		
		if (!$return_only) {
			$options = $response;
		}
		// Handle special case of 'return_only' being set to true...
		else if ($return_only && isset($response[$return_only])) {
			foreach ($response[$return_only] as $key => $value) {
				$options[$value] = $value;
			}

			ksort($options);
			$options = array('false' => 'Any') + $options;	
		}
		
		return $options;
	}

	public static function counts_for_locations ($args, $allow_globals = true) {
		extract(wp_parse_args($args, array('locations'=>array(), 'type'=>'neighborhood')));
		$result = array();
		foreach($locations as $location) {
			$result[$location] = 0;
			if (!empty($location['type'])) {
				$api_response = self::results(array('location'=>array($type=>$location),'limit'=>1), $allow_globals);
				if ($api_response) {
					$result[$location] = $api_response['total'];
				}
			}
		}
		return $result;
	}

	public static function basic_aggregates ($keys) {
		// Need to specify an array that contains at least one key..
		if (!is_array($keys) || empty($keys)) { return array(); }

		$args = array('keys' => $keys);
		$response = PLX_Search::aggregates($args);

		return $response;
	}

	public static function polygon_locations ($return_only = false) {
		$response = array();
		$polygons = PL_Option_Helper::get_polygons();

		foreach ($polygons as $polygon) {
			if (!$return_only || $polygon['tax'] == $return_only) {
				$response[] = $polygon['name'];
			}
		}
		
		return $response;
	}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
	public static function many_details ($args) {
		return self::details($args);
	}
}
