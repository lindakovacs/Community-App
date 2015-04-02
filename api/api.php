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
require_once('src/search.php');
//require_once('src/sort_filter.php');
//require_once('src/page_filter.php');

class PDX_API {
	public static function get_connection($api_key) {
		return new PDX_API_Connection($api_key, "WP_Http");
	}

	public static function create_listing($connection, $listing) {
		// debug only
		if($connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		return $connection->CREATE_LISTING($listing->post_string());
	}

	public static function get_listing($connection, $id) {
		if($result = $connection->GET_LISTING($id))
			return new PDX_Listing($result);

		return null;
	}

	public static function update_listing($connection, $listing) {
		// debug only
		if($connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		return $connection->UPDATE_LISTING($listing->pdx_id, $listing->post_string());
	}

	public static function delete_listing($connection, $id) {
		// debug only
		if($connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		return $connection->DELETE_LISTING($id);
	}

	public static function search_listings($connection, $search = null, $sort = null, $offset = null, $count = 12) {
		$query = $search ? ($search = $search->query_string()) ? $search . '&' : '' : '';
		$query .= $sort ? ($sort = $sort->query_string()) ? $sort . '&' : '' : 'sort_by=created_at&sort_type=desc&';
		$query .= $offset ? 'offset=' . $offset . '&' : '';
		$query .= $count ? 'count=' . $count : '';

		return $connection->SEARCH_LISTINGS($query);
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
		return "[connection api_key=" . $api_key . "]";
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
		$global_listing = new PDX_Display_Listing($global_results->listings[$global_current = $index]);
	}
	else if(!is_null($global_current) && $next) {
		$global_listing = new PDX_Display_Listing($global_results->listings[++$global_current]);
	}
	else
		$global_listing = null;

	return $global_listing ? ("[listing id=" . $global_listing->pdx_id . "]") : null;
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

	if($global_results = PDX_API::search_listings($global_conn, $search_filter)) {
		if($global_results->listings) {
			$global_current = -1;
		}
		else {
			$global_listing = $global_current = null;
		}
		return "[search total=" . $global_results->total . " count=" . $global_results->count . "]";
	}

	return null;
}


function test_shortcode($args) {
	extract(shortcode_atts(array('api_key' => null, 'test_id' => 0), $args));
	global $global_listing;

	if($api_key) {
		$test_conn = PDX_API::get_connection($api_key);

		switch($test_id) {
			case 1:
				$test_listing = new PDX_Private_Listing($global_listing);
				$test_listing->pdx_id = null;
				$test_result = PDX_API::create_listing($test_conn, $test_listing);
				$test_result = $test_result ? 'create id=' . $test_result : null;
				break;
			case 2:
				$test_listing = new PDX_Private_Listing();
				$test_listing->pdx_id = $global_listing->pdx_id;
				$test_listing->latitude = -50.0;
				$test_listing->longitude = -50.0;
				$test_listing->price = $global_listing->price + 500;
				$test_listing->beds = $global_listing->beds + 1;
				$test_result = PDX_API::update_listing($test_conn, $test_listing);
				$test_result = $test_result ? 'update id=' . $test_result : null;
				break;
			case 3:
				$test_listing = new PDX_Private_Listing($global_listing);
				$test_listing = $test_listing->pdx_id;
				$test_result = PDX_API::delete_listing($test_conn, $test_listing);
				$test_result = $test_result ? 'delete id=' . $test_listing : null;
				break;
		}

		if($test_result)
			return "[" . $test_result . "]";
	}

	return null;
}