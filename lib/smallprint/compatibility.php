<?php


function placester_get_api_key () {
	return false;
}


// Deprecated
function placester_get_property_url($id) {
	return PL_Pages::get_url($id);
}


// Deprecated
function placester_post_slug() {
	return true; // ??
}


class PL_Helper_User {
	public static function whoami($args = array(), $api_key = null) {
		return null;
	}
}


class PL_Pages {
	public static function get_url($id = false, $listing = array()) {
		return false;
	}
}


// Deprecated
class PL_Page_Helper {
	public static function get_url($id = false, $listing = array()) {
		return PL_Pages::get_url($id, $listing);
	}
}


class PL_Listing_Helper {
	public static function get_listing_in_loop() {
		return null;
	}

	public static function basic_aggregates($keys) {
		return array();
	}

	public static function types_for_options($return_only = false, $allow_globals = true, $type_key = 'property_type') {
		return array();
	}


	public static function locations_for_options($return_only = false) {
		return array();
	}

	public static function polygon_locations($return_only = false) {
		return array();
	}

	public static function counts_for_locations($params = array()) {
		return array();
	}


	public static function results($args, $global_filters = true) {
		return array();
	}

	public static function details($args) {
		return array();
	}

	// Deprecated
	public static function many_details ($args) {
		return self::details($args);
	}
}


class PL_Permalink_Search {
	public static function save_search($search_id, $search_filters) {
		return false;
	}

	public static function get_saved_search_filters($search_id) {
		return false;
	}
}


// Deprecated
class PL_Saved_Search {
	public static function save_search($search_id, $search_filters) {
		return PL_Permalink_Search::save_search($search_id, $search_filters);
	}

	public static function get_saved_search_filters($search_id) {
		return PL_Permalink_Search::get_saved_search_filters($search_id);
	}
}


class PL_Custom_Attributes {
	public static function get_translations() {
		return array();
	}
}


// Deprecated
class PL_Custom_Attribute_Helper {
	public static function get_translations () {
		return PL_Custom_Attributes::get_translations();
	}
}


class PL_Compliance {
	public static function mls_message($context) {
		return false;
	}
}


class PL_Taxonomy_Helper {
	public static function get_term($params = array()) {
		return false;
	}

	public static function get_permalink_templates($params = array()) {
		return false;
	}

	public static function get_listings_polygon_name($params = array()) {
		return array("listings" => array());
	}

	public static function get_polygon_links($params = array()) {
		return array();
	}

	public static function get_polygons_by_type($params = array()) {
		return array();
	}

	public static function get_polygons_by_slug($params = array()) {
		return array();
	}

	public static function get_polygon_detail($params = array()) {
		return array();
	}
}


class PL_People_Helper {
	public static function person_details() {
		return array();
	}

	public static function update_person($person_details) {
		return false;
	}

	public static function add_person($person_details) {
		return false;
	}

	// Deprecated
	public static function update_person_details($person_details) {
		return self::update_person($person_details);
	}

	// Deprecated
	public static function get_favorite_ids() {
		return PL_Favorite_Listings::get_favorite_properties();
	}

	// Deprecated
	public static function placester_favorite_link_toggle($args) {
		return PL_Favorite_Listings::placester_favorite_link_toggle($args);
	}
}


class PL_Lead_Capture_Helper {
	public static function merge_bcc_forwarding_addresses_for_sending($headers) {
		return false;
	}
}


class PL_Membership {
	public static function placester_lead_control_panel($args) {
		return false;
	}

	public static function get_client_area_url() {
		return false;
	}

	// Deprecated
	static function get_favorite_ids() {
		return PL_Favorite_Listings::get_favorite_properties();
	}

	// Deprecated
	static function placester_favorite_link_toggle($args) {
		return PL_Favorite_Listings::placester_favorite_link_toggle($args);
	}
}


class PL_Favorite_Listings {
	public static function get_favorite_properties() {
		return false;
	}

	public static function placester_favorite_link_toggle($args) {
		return false;
	}

	// Deprecated
	public static function get_favorite_ids() {
		return self::get_favorite_properties();
	}
}


class PL_User_Saved_Search {
	public static function get_favorite_search($hash_id) {
		return false;
	}

	public static function get_favorite_searches() {
		return false;
	}

	public static function placester_search_link_toggle($args) {
		return false;
	}

	public static function placester_favorite_search_list($args) {
		return false;
	}
}


class PL_Option_Helper {
	public static function get_default_location() {
		return array('lat' => 42.3596681, 'lng' => -71.0599325);
	}
}


class PL_Education_Helper {
	public static function get_schools($params = array()) {
		return array();
	}
}


class PL_Walkscore {
	public static function get_score($params = array()) {
		return array();
	}
}


// Deprecated
class PL_Analytics {
	public static function log_snippet_js($event, $attributes) {
		return null;
	}
}


class PL_Dragonfly {
	public static function resize($image_args) {
		return $image_args['old_image'];
	}
}
