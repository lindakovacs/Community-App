<?php 

PLS_Listing_Helper::init();
class PLS_Listing_Helper {

	public static function init() {
		add_action('wp_ajax_pls_listings_for_options', array(__CLASS__,'listings_for_options'));
		add_action('wp_ajax_pls_get_search_count', array(__CLASS__,'get_search_count'));
		add_action('wp_ajax_nopriv_pls_get_search_count', array(__CLASS__,'get_search_count'));

		// Set default property URL (can't call functions when declaring class variables)
		self::$default_listing['url'] = PL_Pages::get_url(self::$default_listing['listings'][0]['id'], self::$default_listing['listings'][0]);
	}

	public static function listings_for_options() {
		$api_response = PL_Listing_Helper::results($_POST);
		$formatted_listings = '';

		if ($api_response['listings']) {
			foreach ($api_response['listings'] as $listing) {
				if ( !empty($listing['location']['unit']) ) {
					$formatted_listings .= '<option value="' . $listing['id'] . '" >' . $listing['location']['address'] . ', #' . $listing['location']['unit'] . ', ' . $listing['location']['locality'] . ', ' . $listing['location']['region'] . '</option>';
				} 
				else {
					$formatted_listings .= '<option value="' . $listing['id'] . '" >' . $listing['location']['address'] . ', ' . $listing['location']['locality'] . ', ' . $listing['location']['region'] . '</option>';
				}
			}
		} 
		else {
			$formatted_listings .= "No Results. Broaden your search.";
		}

		echo json_encode($formatted_listings);
		die();
	}

	public static function get_featured ($featured_option_id, $args = array()) {
		$api_response = array('listings' => array());
		$option_ids = pls_get_option($featured_option_id); 

		if (!empty($option_ids)) {
			$property_ids = array_keys($option_ids);

			if (!empty($property_ids)) {
				$args['property_ids'] = $property_ids;
			}

			$api_response = PL_Listing_Helper::details($args);
		}

		return $api_response;
	}

	// Pass in property IDs array
	public static function get_featured_from_post ($post_id, $post_meta_key) {
		$api_response = array('listings' => array());
		
		// Data comes in different forms...
		$property_data = get_post_meta($post_id, $post_meta_key);
		$property_ids = empty($property_data) ? array() : @json_decode($property_data[0], true);
		
		if (empty($property_ids) && is_array($property_data) && isset($property_data[0]['featured-listings-type'])) {
			$listings_array = $property_data[0]['featured-listings-type'];
			if (is_array($listings_array)) {
				$property_ids = array_keys($listings_array);
			}
			// $property_ids = implode(',', $property_ids );
		} 
		elseif (is_array($property_ids)) {
			$property_ids = array_keys($property_ids);
		} 
		
		if (!empty($property_ids)) {
			$api_response = PL_Listing_Helper::details(array('property_ids' => $property_ids));
		} 
		
		return $api_response;
	}

	public static function get_compliance ($args) {
		$message = PL_Compliance::mls_message($args);
		if ($message && !empty($message) && isset($args['context'])) {
			$_POST['compliance_message'] = $message;

			$template = PLACESTER_PLUGIN_DIR . 'placester-compliance/templates/' . $args['context'] . '-compliance.php';
			if(file_exists($template))
				include($template);
		}
		return false;
	}

	public static function get_search_count() {
	    $response = PL_Listing_Helper::results($_POST);
	    echo json_encode(array('count' => $response['total']));
	    die();
	}

	public static $default_listing = array(
	    'total' => '1',
	    'listings' => array(
	        array(
	            'id' => '000000000000000000000000',
	            'property_type' => 'fam_home',
	            'zoning_types' => array('residential'),
	            'purchase_types' => array('sale'),
	            'listing_types' => array('fam_home'),
	            'building_id' => '1',
	            'cur_data' => array(
	                'half_baths' => '1',
	                'price' => '350000',
	                'sqft' => '2000',
	                'baths' => '2',
	                'avail_on' => '10/16/2015',
	                'beds' => '3',
	                'desc' => 'This is a sample listing. It isn\'t real or available for sale but it\'s a great representation of what you could have on your new real estate website. If you are the owner of this website you need to finish setting it up. Please login and enter an api key.',
	                'lt_sz' => '2',
	                'ngb_shop' => true,
	                'ngb_hgwy' => false,
	                'grnt_tops' => true,
	                'ngb_med' => true,
	                'ngb_trails' => true,
	                'cent_ht' => true,
	                'pk_spce' => '3',
	                'air_cond' => true,
	                'price_unit' => false,
	                'lt_sz_unit' => 'acres',
	                'lse_trms' => false,
	                'ngb_trans' => false,
	                'off_den' => false,
	                'frnshed' => false,
	                'refrig' => false,
	                'deposit' => false,
	                'ngb_pubsch' => false
	            ),
	            'uncur_data' => array(),
	            'location' => array(
	                'full_address' => '123 Fake Street, Boston MA 02142',
	                'address' => '123 Fake Street',
	                'locality' => 'Boston',
	                'region' => 'MA',
	                'postal' => '02142',
	                'neighborhood' => 'Back Bay',
	                'country' => 'US',
	                'coords' => array(
	                    '42.3596681',
	                    '-71.0599325'
	                )
	            ),
	            'contact' => array(
	                'email' => 'test@example.com',
	                'phone' => '+1231231234'
	            ),
	            'images' => false,
	            'tracker_url' => false,
	            'rets' => array(
	                'aname' => 'John Smith',
	                'oname' => 'Smith Realty Group'
	            )
	        )
	    )
	);
} 
// end of class
?>