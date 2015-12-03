<?php


require_once('compatibility-plugin.php');


add_action('after_setup_theme', 'placester_blueprint_active', 8);
function placester_blueprint_active() {
	global $placester_blueprint;
	global $pl_compatibility_plugin;

	if(!isset($placester_blueprint))
		$placester_blueprint = false;

	else {
		$placester_blueprint['has_plugin_error'] = false;
		add_theme_support('pls-widget-contact');

		// if we have a blueprint theme, we need the old API
		if(!isset($pl_compatibility_plugin)) $pl_compatibility_plugin = new PL_Compatibility_Plugin();
	}
}


function placester_get_api_key() {
	global $pl_compatibility_plugin;
	return $pl_compatibility_plugin
		? $pl_compatibility_plugin->get_api_key()
		: false;
}

function placester_get_property_url($id) { return false; }
function placester_post_slug() { return true; }


class PL_Listing_Helper {
	public static function get_listing_in_loop () {
		global $pl_compatibility_plugin;
		return $pl_compatibility_plugin
			? $pl_compatibility_plugin->get_current_listing()
			: false;
	}

	public static function basic_aggregates($keys) {
		global $pl_compatibility_plugin;
		return $pl_compatibility_plugin
			? $pl_compatibility_plugin->get_attribute_values($keys, false, false)
			: array();
	}

	public static function types_for_options($return_only = false, $use_global_filter = true, $type_key = 'property_type') {
		global $pl_compatibility_plugin;
		return $pl_compatibility_plugin
			? $pl_compatibility_plugin->get_attribute_values($return_only ?: $type_key, $use_global_filter, true)
			: array();
	}

	public static function locations_for_options($return_only = false, $use_global_filter = true) {
		$keys = $return_only ?: array('region', 'locality', 'postal', 'county', 'neighborhood');

		global $pl_compatibility_plugin;
		return $pl_compatibility_plugin
			? $pl_compatibility_plugin->get_attribute_values($keys, $use_global_filter, !!$return_only)
			: array();
	}

	public static function polygon_locations($return_only = false) { return array(); }
	public static function counts_for_locations($params = array()) { return array(); }

	public static function results($args, $global_filters = true) {
		global $pl_compatibility_plugin;
		return $pl_compatibility_plugin
			? $pl_compatibility_plugin->get_search_listings($args, $global_filters)
			: array('total' => 0, 'listings' => array());
	}

	public static function details($args) {
		return $args['property_ids']
			? self::results($args, false)
			: array('total' => 0, 'listings' => array());
	}

	public static function many_details($args) {
		return $args['property_ids']
			? self::results($args, false)
			: array('total' => 0, 'listings' => array());
	}
}

class PL_Permalink_Search {
	public static function save_search($search_id, $search_filters) { return false; }
	public static function get_saved_search_filters($search_id) { return false; }
}


class PL_Pages {
	public static function get_url($id = false, $listing = array()) { return placester_get_property_url($id); }
}

class PL_Page_Helper {
	public static function get_url($id) { return placester_get_property_url($id); } // OLDER
}


class PL_Dragonfly {
	public static function resize($image, $args = array()) { return false; }
}


class PL_People_Helper {
	public static function person_details($always = array()) { return array(); }
	public static function update_person($person_details) { return false; }
	public static function add_person($person_details) { return false; }
}

//class PL_Membership {
//	public static function placester_lead_control_panel($args) { return false; }
//	public static function get_client_area_url($always = false) { return false; }
//	public static function get_favorite_ids($always = array()) { return PL_Favorite_Listings::get_favorite_properties(); }
//	public static function placester_favorite_link_toggle($args) { return false; }
//}
//
//class PL_Favorite_Listings {
//	public static function get_favorite_properties() { return ; }
//	public static function get_favorite_ids($always = array()) { return PL_Membership::get_favorite_ids(); }
//	public static function placester_favorite_link_toggle($args) { return PL_Membership::placester_favorite_link_toggle($args); }
//}

class PL_Lead_Capture_Helper {
	public static function merge_bcc_forwarding_addresses_for_sending($headers) { return false; }
}


class PL_Helper_User {
	public static function whoami($args = array(), $api_key = null) { return null; }
}

class PL_Custom_Attribute_Helper {
	public static function get_translations() {
		global $pl_compatibility_plugin;
		return $pl_compatibility_plugin ? $pl_compatibility_plugin->get_custom_attributes() : array();
	}
}

class PL_Compliance {
	public static function mls_message($context) { return false; }
}

class PL_Analytics {
	public static function log_snippet_js($event, $attributes) { return false; }
}


class PL_Option_Helper {
	public static function get_default_location() { return array('lat' => 42.3596681, 'lng' => -71.0599325); }
}

class PL_Taxonomy_Helper {
	public static function get_term($params = array()) { return false; }
	public static function get_permalink_templates($params = array()) { return false; }
	public static function get_listings_polygon_name($params = array()) { return array("listings" => array()); }
	public static function get_polygon_links($params = array()) { return array(); }
	public static function get_polygons_by_type($params = array()) { return array(); }
	public static function get_polygons_by_slug($params = array()) { return array(); }
	public static function get_polygon_detail($params = array()) { return array(); }
}

class PL_Education_Helper {
	public static function get_schools($params = array()) { return array(); }
}

class PL_Walkscore {
	public static function get_score($params = array()) { return array(); }
}

