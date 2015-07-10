<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER . 'api/connection.php');


add_shortcode('connection', 'connection_shortcode');
add_shortcode('filter', 'filter_shortcode');
add_shortcode('search', 'search_shortcode');

add_shortcode('listing', 'listing_shortcode');
add_shortcode('image', 'image_shortcode');
add_shortcode('data', 'data_shortcode');

add_shortcode('foreach:listing', 'foreach_listing_shortcode');
add_shortcode('foreach:image', 'foreach_image_shortcode');

add_shortcode('test', 'search_test_shortcode');


function connection_shortcode($args) {
	extract(shortcode_atts(array('api_key' => null), $args));
	global $global_connection;

	if($api_key) {
		$global_connection = new PL_API_Connection($api_key);
		$global_connection->enable_attribute(array_keys($global_connection->get_standard_attributes()));
		$global_connection->enable_attribute(array_keys($global_connection->get_custom_attributes()));
		return "[connection api_key=" . $api_key . "]";
	}

	return null;
}


function listing_shortcode($args) {
	extract(shortcode_atts(array('id' => null, 'index' => null, 'next' => null), $args));
	global $global_connection;
	global $global_results;
	global $global_listing;

	if($id && $global_connection) {
		if($result = $global_connection->get_private_listing($id))
			$global_listing = new PL_Listing($result, $global_connection);
		else
			$global_listing = null;
	}
	else if(!$global_results) {
		$global_listing = null;
		return null;
	}
	else if(is_null($global_listing) || $next || !is_null($index))
		$global_listing = $global_results->get_listing($index);

	if($global_listing) {
		if(!$content)
			return "[listing id=" . $global_listing->pdx_id . "]";

		return do_shortcode($content);
	}

	return null;
}

function foreach_listing_shortcode($args, $content) {
	extract(shortcode_atts(array('index' => null, 'count' => null), $args));
	global $global_results;
	global $global_listing;

	if(!$global_results) {
		$global_listing = null;
		return null;
	}

	$value = '';

	$global_listing = $global_results->get_listing($index);
	if(is_null($count)) $count = $global_results->count();
	if(!is_numeric($count)) $count = 0;

	while($content && $global_listing && $count-- > 0) {
		$value .= do_shortcode($content);
		$global_listing = $global_results->get_listing();
	}

	return $value;
}

function image_shortcode($args, $content) {
	extract(shortcode_atts(array('index' => null, 'next' => null), $args));
	global $global_listing;
	global $global_image;
	global $global_image_in_context;

	if(!$global_listing) {
		$global_image = null;
		return null;
	}

	if(is_null($global_image) || $next || !is_null($index))
		$global_image = $global_listing->images->get_image($index);

	if($global_image) {
		if(!$content)
			return $global_image->url;

		$image_in_context = $global_image_in_context;
		$global_image_in_context = true;
		$value = do_shortcode($content);
		$global_image_in_context = $image_in_context;
		return $value;
	}

	return null;
}

function foreach_image_shortcode($args, $content) {
	extract(shortcode_atts(array('index' => null, 'count' => null), $args));
	global $global_listing;
	global $global_image;
	global $global_image_in_context;

	if(!$global_listing) {
		$global_image = null;
		return null;
	}

	$value = '';
	$image_in_context = $global_image_in_context;
	$global_image_in_context = true;

	$global_image = $global_listing->images->get_image($index);
	if(is_null($count)) $count = $global_listing->images->count();
	if(!is_numeric($count)) $count = 0;

	while($content && $global_image && $count-- > 0) {
		$value .= do_shortcode($content);
		$global_image = $global_listing->images->get_image();
	}

	$global_image_in_context = $image_in_context;
	return $value;
}

function data_shortcode($args) {
	extract(shortcode_atts(array('attribute' => null), $args));
	global $global_listing;
	global $global_image;
	global $global_image_in_context;

	if($global_image && $global_image_in_context && in_array($attribute, array('id', 'url', 'caption')))
		$value = $global_image->{$attribute};

	else if($global_listing && $attribute)
		$value = $global_listing->{$attribute};

	else
		$value = null;

	return $value;
}

function new_shortcode_search_filter(PL_API_Connection $connection, $args) {
	$filter = $connection->new_search_filter();
	if(is_array($args)) {
		$filter_options = array_fill_keys($filter->get_filter_options(), true);
		foreach($args as $field => $value)
			if($filter_options[$field]) {
				if(is_string($value) && $filter->allow_array($field))
					$value = explode('||', $value);
				$filter->set($field, $value);
			}
	}
	return $filter;
}

function search_shortcode($args) {
	global $global_connection;
	global $global_filter;
	global $global_results;

	$search_filter = new_shortcode_search_filter($global_connection, $args);
	$search_view = $global_connection->new_search_view($args);

	if($global_filter)
		$search_filter = PL_Search_Filter::combine($global_filter, $search_filter);

	if($global_results = $global_connection->search_listings($search_filter, $search_view))
		return "[search total=" . $global_results->total() . " count=" . $global_results->count() . "]";

	return null;
}

function filter_shortcode($args) {
	global $global_connection;
	global $global_filter;

	$global_filter = new_shortcode_search_filter($global_connection, $args);
	if($filter_results = $global_connection->search_listings($global_filter))
		return "[filter total=" . $filter_results->total() . "]";

	return null;
}


function test_shortcode($args) {
	extract(shortcode_atts(array('api_key' => null, 'test_id' => 0), $args));
	global $global_listing;

	if($api_key) {
		$test_connection = new PL_API_Connection($api_key);
		$test_connection->enable_attribute(array_keys($test_connection->get_standard_attributes()));
		$test_connection->enable_attribute(array_keys($test_connection->get_custom_attributes()));

		switch($test_id) {
			case 1:
				$test_listing = $test_connection->new_private_listing($global_listing);
				$test_result = $test_connection->create_listing($test_listing);
				$test_result = $test_result ? 'create id=' . $test_result : null;
				break;
			case 2:
				$test_listing = $test_connection->get_private_listing($global_listing);
				$test_listing->latitude = -50.0;
				$test_listing->longitude = -50.0;
				$test_listing->price = $global_listing->price + 500;
				$test_listing->beds = $global_listing->beds + 1;
				$test_result = $test_connection->update_listing($test_listing);
				$test_result = $test_result ? 'update id=' . $test_result : null;
				break;
			case 3:
				$test_listing = $global_listing->pdx_id;
				$test_result = $test_connection->delete_listing($test_listing);
				$test_result = $test_result ? 'delete id=' . $test_listing : null;
				break;
		}

		if($test_result)
			return "[" . $test_result . "]";
	}

	return null;
}


function search_test_shortcode($args) {
	extract(shortcode_atts(array('field' => ''), $args));
	global $global_connection;
	global $global_filter;

	$values = $global_connection->read_attribute_values($field, $global_filter);
	$result = '';

	if(count($values) > 1) {
		$args = array($field => $values[0]);
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$args = array($field . '_match' => 'ne', $field => $values[0]);
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$result .= '<br>';
		$args = array($field . '_match' => 'like', $field => substr($values[0], 0, 3));
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$args = array($field . '_match' => 'and_like', $field => substr($values[0], 0, 3) . '||' . substr($values[0], -3));
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$args = array($field . '_match' => 'or_like', $field => substr($values[0], 0, 3) . '||' . substr($values[0], -3));
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$result .= '<br>';
		$args = array('min_' . $field => substr($values[0], 0, 3));
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$args = array('max_' . $field => substr($values[0], 0, 3));
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';
	}

	if(count($values) > 2) {
		$result .= '<br>';

		$args = array($field . '_match' => 'in', $field => $values[0] . '||' . $values[1]);
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';

		$args = array($field . '_match' => 'nin', $field => $values[0] . '||' . $values[1]);
		$result .= search_shortcode($args) . ' ';
		foreach($args as $name => $value)
			$result .= ' (' . $name . '=' . $value . ') ';
		$result .= '<br>';
	}

	return $result;
}
