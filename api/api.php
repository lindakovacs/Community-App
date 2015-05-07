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
require_once('src/connection_attributes.php');
require_once('src/listing_display.php');
require_once('src/search_filter.php');
require_once('src/search_sort.php');
require_once('src/search_page.php');


class PL_API {
	public static function create_listing($connection, $listing) {
		// debug only
		if($connection->API_KEY != 'wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka')
			return null;

		return $connection->CREATE_LISTING($listing->post_string());
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

	public static function search_listings($connection, $filter = null, $sort = null, $page = null) {
		if($filter) $filter = $filter->query_string();
		if($sort) $sort = $sort->query_string();
		if($page) $page = $page->query_string();

		$query = ($filter ? $filter . '&' : '')
			. ($sort ? $sort : 'sort_by=created_at&sort_type=desc')
			. ($page ? '&' . $page : '');

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
	global $global_connection;
	global $global_attributes;

	if($api_key) {
		$global_connection = new PL_API_Connection($api_key, "WP_Http");
		$global_attributes = new PL_Connection_Attributes($global_connection);
		$global_attributes->add_attribute($global_attributes->get_standard_attributes());
		$global_attributes->add_attribute($global_attributes->get_custom_attributes());
		return "[connection api_key=" . $api_key . "]";
	}

	return null;
}


function listing_shortcode($args) {
	extract(shortcode_atts(array('id' => null, 'index' => null, 'next' => true), $args));
	global $global_connection;
	global $global_attributes;
	global $global_results;
	global $global_current;
	global $global_listing;

	if($id) {
		if($result = $global_connection->GET_LISTING($id))
			$global_listing = new PL_Listing_Display($result, $global_attributes);
		else
			$global_listing = null;
	}
	else if(!is_null($global_current) && !is_null($index)) {
		$global_listing = new PL_Listing_Display($global_results->listings[$global_current = $index], $global_attributes);
	}
	else if(!is_null($global_current) && $next) {
		$global_listing = new PL_Listing_Display($global_results->listings[++$global_current], $global_attributes);
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
	global $global_connection;
	global $global_attributes;
	global $global_results;
	global $global_current;
	global $global_listing;

	$search_filter = new PL_Search_Filter($global_attributes);
	$search_sort = new PL_Search_Sort($global_attributes);
	$search_page = new PL_Search_Page();

	$filter_options = array_fill_keys($search_filter->get_filter_options(), null);
	$sort_options = array_fill_keys($search_sort->get_sort_options(), null);
	$page_options = array_fill_keys($search_page->get_page_options(), null);
	$combined_options = array_merge($filter_options, $sort_options, $page_options);

	$search_fields = shortcode_atts($combined_options, $args);
	foreach($search_fields as $field => $value) {
		if(!is_null($value)) {
			if(array_key_exists($field, $sort_options))
				$search_sort->set($field, $value);
			else if(array_key_exists($field, $page_options))
				$search_page->set($field, $value);
			else
				$search_filter->set($field, $value);
		}
	}

	if($global_results = PL_API::search_listings($global_connection, $search_filter, $search_sort, $search_page)) {
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
	global $global_attributes;

	if($api_key) {
		$test_connection = PL_API::get_connection($api_key);
		$test_attributes = new PL_Connection_Attributes($test_connection);

		switch($test_id) {
			case 1:
				$test_listing = new PL_Private_Listing($global_listing);
				$test_listing->pdx_id = null;
				$test_result = PL_API::create_listing($test_connection, $test_listing);
				$test_result = $test_result ? 'create id=' . $test_result : null;
				break;
			case 2:
				$test_listing = new PL_Private_Listing(null, $test_attributes);
				$test_listing->pdx_id = $global_listing->pdx_id;
				$test_listing->latitude = -50.0;
				$test_listing->longitude = -50.0;
				$test_listing->price = $global_listing->price + 500;
				$test_listing->beds = $global_listing->beds + 1;
				$test_result = PL_API::update_listing($test_connection, $test_listing);
				$test_result = $test_result ? 'update id=' . $test_result : null;
				break;
			case 3:
				$test_listing = new PL_Private_Listing($global_listing);
				$test_listing = $test_listing->pdx_id;
				$test_result = PL_API::delete_listing($test_connection, $test_listing);
				$test_result = $test_result ? 'delete id=' . $test_listing : null;
				break;
		}

		if($test_result)
			return "[" . $test_result . "]";
	}

	return null;
}