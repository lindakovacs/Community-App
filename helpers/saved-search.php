<?php

// JS in js/public/saved-search.js

PL_Saved_Search::init();

class PL_Saved_Search {

	public static $translations = array(
		// Listing API V3 fields
		'min_sqft' => 'Min Sqft',
		'min_beds' => 'Min Beds',
		'min_baths' => 'Min Baths',
		'max_price' => 'Max Price',
		'min_price' => 'Min Price',
		'prop_type' => 'Property Type',
		// Listing API V2.1 fields
		'location[locality]' => 'City',
        'location[postal]' => 'Zip Code',
        'location[neighborhood]' => 'Neighborhood',
        'metadata[min_sqft]' => 'Min Sqft',
        'purchase_types[]' => 'Purchase Type',
        'price_off' => 'Min Price',
        'metadata[min_beds]' => 'Min Beds',
        'metadata[min_baths]' => 'Min Baths',
        'metadata[min_price]' => 'Min Price'
	);

	public static $schedule_types = array(
		'daily' => 'Daily',
		'biweekly' => 'Twice Weekly',
		'weekly' => 'Once Weekly',
		'bimonthly' => 'Every Two Weeks', 
		'monthly' => 'Every Month'
	);

	public static function init () {
		// AJAX endpoints for attaching saved searches to users (currently, ONLY exposed for authenticated users...)
		add_action('wp_ajax_is_search_saved', array(__CLASS__, 'ajax_is_search_saved'));
		add_action('wp_ajax_get_saved_searches', array(__CLASS__, 'ajax_get_saved_searches'));
		add_action('wp_ajax_add_saved_search', array(__CLASS__,'ajax_add_saved_search'));
		add_action('wp_ajax_delete_saved_search', array(__CLASS__, 'ajax_delete_saved_search'));
		add_action('wp_ajax_update_search_notification', array(__CLASS__, 'ajax_update_search_notification'));

		// Expose certain UI elements as shortcodes...
		add_shortcode('saved_search_button', array(__CLASS__, 'get_saved_search_button'));
	}

	public static function get_saved_searches ($wp_user_id = null, $lead_id = null) {
		// Default return value is an empty array (i.e., no saved searches)
		$saved_searches = array();

		$saved_searches = array(
			'total' => 40,
			'searches' => array(
				array(
					'id' => '1',
					'name' => 'Boston Properties',
					'saved_fields' => '1 Beds, City Boston, $500k+',
					'link_to_search' => '/listings/something',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'notification_schedule' => 'Once per week'
				),
				array(
					'id' => '2',
					'name' => 'Cambridge Properties',
					'saved_fields' => '1 Beds, City Boston, $500k+',
					'link_to_search' => '/listings/something',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'notification_schedule' => 'Once per week'
				),
			)
		);

		// Setup details call args to only pull saved searches...
		// $args = array('id' => $lead_id, 'meta' => array('meta_key' => 'saved_search'));
		
		// Fetch saved searches
		// $result = PL_Lead_Helper::lead_details($wp_user_id, $args);

		// Prep searches...
		if (!empty($result) && is_array($result)) {
			foreach ($result as $hash => &$search) {
				// Construct full search URL based on current site's URL...
				if (!empty($search['url'])) {
					$search['url'] = site_url($search['url']);
				}
			}
			unset($search); // break the reference with the last element...

			$saved_searches = $result;
		}

		return $saved_searches;
	}

	private static function strip_empty_filters ($search_filters) {
		$filters = array();
		
		if (!empty($search_filters) && is_array($search_filters)) {
			foreach ($search_filters as $key => $filter) {
				if (trim($filter) != '') {
					$filters[$key] = trim($filter);
				}
			}
		}
		
		return $filters;
	}

	public static function is_search_saved ($search_filters) {
		$is_saved = false;

		// Remove empty filters...
		$filters = self::strip_empty_filters($search_filters);
		// error_log(var_export($filters, true));
		
		if (!empty($filters) && is_array($filters)) {
			// Setup details call args to check whether or not search is saved...
			$args = array('meta' => array('meta_key' => 'saved_search', 'meta_value' => $filters));

			// Call API to check for existence of saved search...
			$is_saved = PL_Lead_Helper::lead_details($args);
		}

		return $is_saved;
	}

	public static function construct_listing_url ($filters = array(), $global_filters = true) {
		// If flag is true, merge existing filters with any set global filters...
		if ($global_filters) { 
			$filters = PL_Global_Filters::merge_global_filters($filters); 
		}

		// Respect block address setting if it's already set, otherwise, defer to the plugin setting...
		if (empty($filters['address_mode'])) {
			$filters['address_mode'] = ( PL_Option_Helper::get_block_address() ? 'polygon' : 'exact' );
		}

		// Validate the list of filters and add the API Key as one...
		$filters = array_merge(array("api_key" => self::api_key()), PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get', 'args')));

		// Translate the listing filters into a query string to be used in an API call...
		$query_string = PL_HTTP::build_request($filters);

		// Fetch the URL of the listing API endpoint from  the config...
		$api_url = PL_Config::PL_API_LISTINGS('get', 'request', 'url');

		// Construct the full URL with both the API endpoint and the accompanying query string...
		$url = "{$api_url}?{$query_string}";

		return $url;
	}

	public static function add_saved_search ($search_filters, $search_name, $search_url_path) {
		// Default result...
		$success = false;
		$message = "";

		// Remove empty filters...
		$filters = self::strip_empty_filters($search_filters);

		if (!empty($filters) && is_array($filters) && !empty($user_id)) {			
			// Construct listing API call URL...
			$listing_api_url = self::construct_listing_url($filters);

			// Args for saving search...
			$saved_search = array(
				'filters' => $filters, 
				'name' => $search_name,
				'url' => $search_url_path,
				'notification' => false,
				'listing_api_url' => $listing_api_url
			);
			
			// Setup details call args to check whether or not search is saved...
			$args = array('meta' => array('meta_op' => 'create', 'meta_key' => 'saved_search', 'meta_value' => $saved_search));

			$response = PL_Lead_Helper::update_lead($args);
			
			$success = empty($response) ? false : true;
			$message = ($success === false) ? "Could not save search -- please try again" : "";
		}

		return array("success" => $success, "message" => $message);
	}

    public static function delete_saved_search ($search_id) {
		// Default result...
		$success = false;
		$message = "";

		if (!empty($search_id)) {
			// Setup details call args to check whether or not search is saved...
			$args = array('meta' => array('meta_op' => 'delete', 'meta_key' => 'saved_search', 'meta_id' => $search_id));

			// TODO: Actually delete...
			$response = PL_Lead_Helper::update_lead($args);
			
			$success = empty($response) ? false : true;
			$message = ($success === false) ? "Could not delete search -- please try again" : "";
		}
		else {
			$message = "No search ID was passed -- cannot delete...";
		}
			
		return array("success" => $success, "message" => $message);
	}

	public static function update_search_notification ($search_id, $schedule) {
		// Setup details call args to check whether or not search is saved...
		$args = array('notifications' => array('type' => 'listing', 'meta_id' => $search_id, 'schedule' => $schedule, 'notification_op' => 'update'));

		// TODO: Update the corresponding saved search...
		$response = PL_Lead_Helper::update_lead($search_id, $enable);
		
		$success = empty($response) ? false : true;
		$message = ($success === false) ? "Could not enable notification -- please try again" : "";

		return array("success" => $success, "message" => $message);
	}

	/*
	 * AJAX Endpoints for saved searches associated with site users + wrappers...
	 */

	public static function ajax_is_search_saved () {
		$search_filters = $_POST['search_filters'];
		
		$is_saved = self::is_search_saved($search_filters);
		$response = array("saved" => $is_saved);

		echo json_encode($response);
    	die();
	}

	public static function ajax_get_saved_searches () {
		$lead_id = $_POST['lead_id'];

		$saved_searches = self::get_saved_searches($lead_id);
		
		// build response for datatables.js
		$searches = array();
		foreach ($saved_searches['searches'] as $key => $search) {
			// $images = $listing['images'];
			$searches[$key][] = $search['created'];
			// $searches[$key][] = ((is_array($images) && isset($images[0])) ? '<img width=50 height=50 src="' . $images[0]['url'] . '" />' : 'empty');
			$searches[$key][] = '<a class="address" href="' . ADMIN_MENU_URL . $search['link_to_search'] . '">' . 
									$search['name'] . 
								'</a>
								<div class="row_actions">
									<a href="' . ADMIN_MENU_URL . '?page=placester_my_searches&id=' . $search['id'] . '">
										View
									</a>
									<span>|</span>
									<a class="red" id="pls_delete_search" href="#" ref="'.$search['id'].'">
										Delete
									</a>
								</div>';
		
			// <a href="' . ADMIN_MENU_URL . '?page=placester_my_searches&id=' . $search['id'] . '" >
			//   Edit
			// </a>
			
			$searches[$key][] = $search['saved_fields'];
			$searches[$key][] = $search['updated'];
			$searches[$key][] = $search['notification_schedule'];
		}

		// Required for datatables.js to function properly.
		$response = array();
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $searches;
		$response['iTotalRecords'] = $saved_searches['total'];
		$response['iTotalDisplayRecords'] = $saved_searches['total'];
		
		echo json_encode($response);
		die();
	}

	public static function ajax_add_saved_search () {
    	$search_url_path = $_POST['search_url_path'];
    	$search_name = $_POST['search_name'];
    	$search_filters = $_POST['search_filters'];
		
		// error_log(var_export($_POST['search_filters'], true));
		// error_log(var_export($_POST['search_url_path'], true));

    	// Add meta to user for saved searches...
    	if (!empty($search_filters) && is_array($search_filters)) {
    		$response = self::add_saved_search($search_filters, $search_name, $search_url_path);
    	}
    	else {
    		$response = array("success" => false, "message" => "No search filters to save -- select some and try again");
    	}
    	
    	echo json_encode($response);
    	die();
    }

    public static function ajax_delete_saved_search () {
    	// Get search id...
    	$search_id = empty($_POST['search_id']) ? null : $_POST['search_id'];

    	echo json_encode(self::delete_saved_search($search_id));
    	die();
    }

	public static function ajax_update_search_notification () {
		$search_id = $_POST['search_id'];
		$schedule_id = $_POST['schedule_id'];

		$response = self::update_search_notification($search_id, $schedule_id);

		echo json_encode($response);
		die();
	}

	/*
	 * UI + Views
	 */

	// Renders the saved search form overlay...
	public static function get_saved_search_registration_form () {
        ob_start();

        if (is_user_logged_in()) {
            include(trailingslashit(PL_FRONTEND_DIR) . 'saved-search-authenticated.php');
        }
		// else {
		// 	include(trailingslashit(PL_FRONTEND_DIR) . 'saved-search-unauthenticated.php');
		// }

        return ob_get_clean();
    }

    public static function get_saved_search_button () {
    	ob_start();
            include(trailingslashit(PL_FRONTEND_DIR) . 'saved-search-button.php');
        return ob_get_clean();	
    }

    public static function translate_key ($key) {
		$val = ( isset(self::$translations[$key]) ? self::$translations[$key] : $key );
		return $val;
	}
}