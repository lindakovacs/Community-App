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
		$listings = PL_Listing::get($args);

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

	public static function obscure_address($address) {
		return preg_replace('{^([0-9]|# ?)([0-9A-Za-z]| ?([\#\&\-\/\,\.]|and) ?)* ([A-DF-MO-RT-Za-df-mo-rt-z] )?}', '', $address);
	}

	public static function append_address_unit($address, $unit) {
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
			$response = PL_Listing::aggregates(array('keys' => array($type_key)));
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
				// TODO: Move these to a global var or constant...
				$args = array();
				$args['location'] = $global_filters['location'];
				$args['keys'] = array('location.locality', 'location.region', 'location.postal', 'location.neighborhood', 'location.county');
				$response = PL_Listing::aggregates($args);
			
				// Remove "location." from key names to conform to data standard expected by caller(s)...
				$alt = array();
				foreach ( $response as $key => $value ) {
					$new_key = str_replace('location.', '', $key);
					$alt[$new_key] = $value;
				}
				$response = $alt;
			}
			else {
				$response = PL_Listing::locations();
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

	/* 
	 * Aggregates listing data to produce all unique values that exist for the given set of keys passed
	 * in as array.  Classified as "basic" because no filters are incorporated (might add this later...)
	 *
	 * Keys must be passed in a slightly different format than elsewhere, for example, to aggregate on
	 * city and state (i.e., find all unique cities and states present in all available listings), you'd
	 * pass the following value for $keys:
	 *     array('location.region', 'location.locality') // Notice the 'dot' notation in contrast to brackets...
	 *
	 * Returns an array containing keys for all those passed in (i.e. $keys) that themselves map to arrays 
	 * filled with the coresponding unique values that exist.
	 */
	public static function basic_aggregates ($keys) {
		// Need to specify an array that contains at least one key..
		if (!is_array($keys) || empty($keys)) { return array(); }

		$args = array('keys' => $keys);
		$response = PL_Listing::aggregates($args);

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

  /*
    I think the pricing choices returned here are confusing.
    Typically I would expect ranges to be in 1,000; 10,000; 100,000 increments.
    This might be friendlier if we:
    a. find the max-priced listing
    b. set the range max to that max rounded up to the nearest $10,000
    c. set the range min to the minimum rounded down to the nearest $100 (rentals will be affected, so not $1000)
    d. the range array should be returned with 20 items (that's manageble) in some decent increment determined by the total price range.
    e. also consider calculating two groups of prices -- find the min and max of lower range, min and max of higher range, and build array accordingly.
    HOWEVER: That will all come later, as I'm just trying to solve the initial problem of the filter not working. -pek
  */
	public static function pricing_min_options ($type = 'min') {

		$api_response = PL_Listing::get();
		$prices = array();
		foreach ($api_response['listings'] as $key => $listing) {
			$prices[] = $listing['cur_data']['price'];
		}
		
		sort($prices);
		
		if (is_array($prices) && !empty($prices)) {
		  // difference between highest- and lowest-priced listing, divided into 20 levels
			$range = round( ( end( $prices ) - $prices[0] ) / 20 );
			
			if ($type == 'max') {
				$range = range($prices[0], end($prices), $range);
				// add the highest price as the last element
				$range[] = end( $prices );
				// should flip max price to show the highest value first
				$range = array_reverse( $range );		
			} else {
				$range = range($prices[0], end($prices), $range);
			}
		} else {
		  $range = array('');		  
		}
	    // we need to return the array with keys == values for proper form creation
	    // (keys will be the option values, values will be the option's human-readable)
	    if( ! empty( $range ) && $range[0] !== '' ) {
	    	$range = array_combine( $range, $range );
	    	// let's format the human-readable; do not use money_format() because its dependencies are not guaranteed
	    	array_walk( $range, create_function( '&$value,$key', '$value = "$" . number_format($value,2);'));
	    }
		return $range;
	}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
	public static function many_details ($args) {
		return self::details($args);
	}
}
