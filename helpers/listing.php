<?php 

PL_Listing_Helper::init();

class PL_Listing_Helper {

	public static function init () {
		add_action('wp', array(__CLASS__, 'check_listing_exists'));
		add_action('wp_ajax_datatable_ajax', array(__CLASS__, 'datatable_ajax'));
		add_action('wp_ajax_add_listing', array(__CLASS__, 'add_listing_ajax'));
		add_action('wp_ajax_update_listing', array(__CLASS__, 'update_listing_ajax'));
		add_action('wp_ajax_add_temp_image', array(__CLASS__, 'add_temp_image'));
		add_action('wp_ajax_delete_listing', array(__CLASS__, 'delete_listing_ajax'));
	}

	/*
	 * If this is a single property listing, and the listing no longer exists, redirect
	 */
	public static function check_listing_exists() {
		if (is_singular(PL_Pages::$property_post_type) && is_null(self::get_listing_in_loop())) {
			$home = home_url();
			wp_redirect($home);
			exit;
		}
	}
	
	public static function results ($args = array(), $global_filters = true) {
		// Handle edge-case $args formatting and value...
		if (!is_array($args)) { $args = wp_parse_args($args); } 
		elseif (empty($args)) { $args = $_GET; }

		/* REMOVE */
		// if ($global_filters) {
		// 	error_log("\n[[[BEFORE:]]]\n" . var_export($args, true));
		// 	error_log("\n[[[FILTERS:]]]\n" . var_export(PL_Global_Filters::get_global_filters(), true));
		// }

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
		// purchase_types is a special case in the API
		if (!empty($args['purchase_types']) && !is_array($args['purchase_types'])) {
			$args['purchase_types'] = array($args['purchase_types']);
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

	public static function datatable_ajax () {
		$response = array();

		// Start the args array -- exact addresses should always be shown in this view...
		$args = array('address_mode' => 'exact');

		// Sorting
		// Controls the order of columns returned to the datatable
		$columns = array(
			'total_images',
			'location.address',
			'location.locality',
			'location.region',
			'location.postal',
			'zoning_types',
			'purchase_types',
			'property_type',
			'cur_data.beds',
			'cur_data.baths',
			'cur_data.price',
			'cur_data.sqft',
			'cur_data.avail_on'
		);

		$args['sort_by'] = $columns[$_POST['iSortCol_0']];
		$args['sort_type'] = $_POST['sSortDir_0'];
		
		// text searching on address
		$args['location']['address'] = @$_POST['sSearch'];
		$args['location']['address_match'] = 'like';

		// Pagination
		$args['limit'] = $_POST['iDisplayLength'];
		$args['offset'] = $_POST['iDisplayStart'];		

		// We need to check for and parse compound_type...
		if (!empty($_POST['compound_type'])) {
			// First copy to args...
			$args['compound_type'] = $_POST['compound_type'];

			// Infer other fields based on this field's value...
			switch ($_POST['compound_type']) {
				case "res_sale":
				  	$args['zoning_types'][] = 'residential';
				  	$args['purchase_types'][] = 'sale';
				  	break;
				case "res_rental":
				  	$args['zoning_types'][] = 'residential';
				  	$args['purchase_types'][] = 'rental';
				  	break;
				case "comm_sale":
				  	$args['zoning_types'][] = 'commercial';
				  	$args['purchase_types'][] = 'sale';
				  	break;
				case "comm_rental":
				  	$args['zoning_types'][] = 'commercial';
				  	$args['purchase_types'][] = 'rental';
				  	break;
				case "vac_rental":
				case "park_rental":
				case "sublet":
				default:
				  	$args['zoning_types'] = false;
				  	$args['purchase_types'] = false;
			}
		}

		// Transfer over control flags...
		$flags = array('agency_only', 'non_import', 'include_disabled');
		foreach ($flags as $key) {
			if (!empty($_POST[$key])) {
				$args[$key] = $_POST[$key];
			}
		}

		// Transfer over pertinent groups of args...
		$arg_groups = array('zoning_types', 'purchase_types', 'property_type', 'location', 'rets', 'metadata', 'custom');
		foreach ($arg_groups as $key) {
			if (!empty($_POST[$key])) {
				if ($key == 'custom') {
					// get list of text fields
					$attrs = self::custom_attributes();
					$text_fields = array();
					$textarea_fields = array();
					foreach($attrs as $attr) {
						if ($attr['attr_type'] == 2 || $attr['attr_type'] == 3) {
							$text_fields[] = $attr['key'];
						}
					}
					// custom text fields do a non exact search and they need to be queried as 'metadata'
					foreach($_POST[$key] as $subkey => $val) {
						if (!empty($val)) {
							if (in_array($subkey, $text_fields)) {
								$args['metadata'][$subkey] = $val;
								$args['metadata'][$subkey.'_match'] = 'like';
							}
							else {
								$args['metadata'][$subkey] = $val;
							}
						}
					}
				}
				else {
					$args[$key] = $_POST[$key];
				}
			}
		}

		// Get listings from model -- no global filters applied...
		$api_response = PL_Listing::get($args);
		
		// build response for datatables.js
		$listings = array();
		foreach ($api_response['listings'] as $key => $listing) {
			$images = $listing['images'];
			$listings[$key][] = ((is_array($images) && isset($images[0])) ? '<img width=50 height=50 src="' . $images[0]['url'] . '" />' : 'empty');

			$edit_link = admin_url('admin.php?page=placester_property_edit&id=' . $listing['id']);
			$address = $listing["location"]["address"] . ' ' . $listing["location"]["locality"] . ' ' . $listing["location"]["region"];
			$listings[$key][] = '<a class="address" href="' . $edit_link . '">' . $address . '</a>' .
				'<div class="row_actions">' .
					'<a href="' . $edit_link . '">Edit</a><span>|</span>' .
					'<a href=' . PL_Pages::get_url($listing['id'], $listing) . '>View</a><span>|</span>' .
					'<a class="red" id="pls_delete_listing" href="#" ref="'.$listing['id'].'">Delete</a>' .
				'</div>';

			$listings[$key][] = $listing["location"]["postal"];
			$listings[$key][] = implode($listing["zoning_types"], ', ') . ' ' . implode($listing["purchase_types"], ', ');
			$listings[$key][] = $listing["property_type"];
			$listings[$key][] = $listing["cur_data"]["beds"];
			$listings[$key][] = $listing["cur_data"]["baths"];
			$listings[$key][] = $listing["cur_data"]["price"];
			$listings[$key][] = $listing["cur_data"]["sqft"];
			$listings[$key][] = $listing["cur_data"]["avail_on"] ? date_format(date_create($listing["cur_data"]["avail_on"]), "jS F, Y g:i A.") : 'n/a';
		}

		// Required for datatables.js to function properly.
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $listings;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		echo json_encode($response);

		// WordPress echos out a 0 randomly, 'die' prevents it...
		die();
	}
	
	private static function prepare_post_array () {
		$_POST = stripslashes_deep($_POST);

		foreach ($_POST as $key => $value) {
			if (is_int(strpos($key, 'property_type'))) {
				unset( $_POST[$key] );
				if( $value !== 'false' && ! empty( $value ) ) {
					$_POST['metadata']['prop_type'] = $value;
				}
			}
		}
	}

	public static function add_listing_ajax () {
		self::prepare_post_array();
		
		$api_response = PL_Listing::create($_POST);
		echo json_encode($api_response);
		if (isset($api_response['id'])) {
			PL_Listing::get( array('listing_ids' => array($api_response['id'])) );
			// If on, turn off demo data...
			PL_Option_Helper::set_demo_data_flag(false);
		}
		die();
	}	

	public static function update_listing_ajax () {
		self::prepare_post_array();
		
		$api_response = PL_Listing::update($_POST);
		echo json_encode($api_response);
		if (isset($api_response['id'])) {
			PL_Listing::get( array('listing_ids' => array($api_response['id'])) );
		}
		die();
	}

	public static function delete_listing_ajax () {
		$api_response = PL_Listing::delete($_POST);
		//api returns empty, with successful header. Return actual message so js doesn't explode trying to check empty.
		if (empty($api_response)) { 
			echo json_encode(array('response' => true, 'message' => 'Listing successfully deleted. This page will reload momentarily.'));
		} elseif ( isset($api_response['code']) && $api_response['code'] == 1800 ) {
			echo json_encode(array('response' => false, 'message' => 'Cannot find listing. Try <a href="'.admin_url().'?page=placester_settings">emptying your cache</a>.'));
		}
		die();
	}

	public static function add_temp_image () {
		$api_response = array();
		$response = array();
		if (isset($_FILES['files'])) {
			foreach ($_FILES as $key => $image) {
				if (isset($image['name']) && is_array($image['name']) && (count($image['name']) == 1))  {
					$image['name'] = implode($image['name']);
				}
				if (isset($image['type']) && is_array($image['type']) && (count($image['type']) == 1))  {
					$image['type'] = implode($image['type']);
				}
				if (isset($image['tmp_name']) && is_array($image['tmp_name']) && (count($image['tmp_name']) == 1))  {
					$image['tmp_name'] = implode($image['tmp_name']);
				}
				if (isset($image['size']) && is_array($image['size']) && (count($image['size']) == 1))  {
					$image['size'] = implode($image['size']);
				}
				if (!in_array($image['type'], array('image/jpeg','image/jpg','image/png','image/gif'))) {
					$api_response['message'] = "Unsupported file type - the image file must be a jpeg, jpg, png or gif file.".$image['type'];
				}
				else {
					$api_response = PL_Listing::temp_image($_POST, $image['name'], $image['type'], $image['tmp_name']);
				}
				$api_response = wp_parse_args( $api_response, array('filename'=>'','url'=>'','message'=>'') ); 
				// If no image URL is returned, the call failed -- so pass along the error message...
				if (empty($api_response['url'])) {
					$response[$key]['message'] = $api_response['message'];
				}
				else {
					$response[$key]['url'] = $api_response['url'];
				}
				$response[$key]['name'] = $api_response['filename'];
				$response[$key]['orig_name'] = $image['name'];
			}
		}		
		header('Vary: Accept');
		header('Content-type: text/html');
		echo json_encode($response);
		die();
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

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
	public static function many_details ($args) {
		return self::details($args);
	}
}
