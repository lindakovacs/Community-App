<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */

require_once('src/php_curl.php');
require_once('src/connection.php');
require_once('src/listing.php');
require_once('src/attribute.php');
require_once('src/filter.php');
//require_once('src/sort_filter.php');
//require_once('src/page_filter.php');

class PDX_API {
	public static function get_connection($api_key) {
		return new PDX_API_Connection($api_key, "PHP_Curl");
	}

	public static function create_listing($connection, $listing) {
		global $pdx_error_msg;

		// debug only
		if($connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka') return null;

		if($result = $connection->CREATE_LISTING($listing->post_string())) {
			if($result = json_decode($result)) {
				if($result->id)
					return $result->id;
				else
					$pdx_error_msg = $result->message;
			}
		}

		return null;
	}

	public static function update_listing($connection, $listing) {
		// debug only
		if($connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka') return null;

		if($result = $connection->UPDATE_LISTING($listing->pdx_id, $listing->post_string())) {
			return $result;
		}

		return null;
	}

	public static function get_listing($connection, $id) {
		if($result = $connection->GET_LISTING($id)) {
			return new PDX_Listing($result);
		}

		return null;
	}

	public static function get_listings($connection, $search = null, $sort = null, $offset = null, $count = 12) {
		$query = $search ? $search->query_string() : '';
		if($query) $query .= '&'; $query .= 'sort_by=created_at&sort_type=desc';

		if($result = $connection->GET_LISTINGS($query)) {
			return json_decode($result);
		}

		return null;
	}
}

add_shortcode('connection', 'connection_shortcode');
add_shortcode('listing', 'listing_shortcode');
add_shortcode('data', 'data_shortcode');
add_shortcode('search', 'search_shortcode');
add_shortcode('test', 'test_shortcode');

function connection_shortcode($args) {
	extract(shortcode_atts(array('api_key' => null), $args));
	global $global_conn;

	if($api_key) {
		$global_conn = PDX_API::get_connection($api_key);
		return "[" . $api_key . "]";
	}

	return null;
}

function listing_shortcode($args) {
	extract(shortcode_atts(array('id' => null, 'index' => null, 'next' => true), $args));
	global $global_conn;
	global $global_results;
	global $global_current;
	global $global_listing;

	if($id) {
		$global_listing = PDX_API::get_listing($global_conn, $id);
	}
	else if(!is_null($global_current) && !is_null($index)) {
		$global_listing = new PDX_Listing($global_results->listings[$global_current = $index]);
	}
	else if(!is_null($global_current) && $next) {
		$global_listing = new PDX_Listing($global_results->listings[++$global_current]);
	}
	else
		$global_listing = null;

	return $global_listing ? ("[" . $global_listing->pdx_id . "]") : null;
}

function data_shortcode($args) {
	extract(shortcode_atts(array('attribute' => null), $args));
	global $global_listing;

	if($global_listing && $attribute) {
		$value = $global_listing->{$attribute};
		return $value;
	}

	return null;
}

function search_shortcode($args) {
	global $global_conn;
	global $global_results;
	global $global_current;
	global $global_listing;

	$search_filter = new PDX_Search_Filter();
	$search_fields = shortcode_atts(array_fill_keys($search_filter->get_search_fields(), null), $args);

	foreach($search_fields as $field => $value) {
		if(!is_null($value)) {
			$search_filter->set($field, $value);
		}
	}

	if($global_results = PDX_API::get_listings($global_conn, $search_filter)) {
		if($global_results->listings) {
			$global_current = -1;
		}
		else {
			$global_listing = $global_current = null;
		}
		return "[" . $global_results->total . " listings found, " . $global_results->count . " listings returned]";
	}

	return null;
}


function test_shortcode($args) {
	extract(shortcode_atts(array('api_key' => null), $args));
	global $global_listing;
	global $pdx_error_msg;

	if($api_key) {
		$test_conn = PDX_API::get_connection($api_key);
		$test_listing = new PDX_Private_Listing($global_listing); $test_listing->pdx_id = null; $test_listing->price = "FUCK";
		$test_id = PDX_API::create_listing($test_conn, $test_listing);
		return "[" . ($test_id ? $test_id : $pdx_error_msg) . "]";
	}

	return null;
}