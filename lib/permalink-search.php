<?php

PL_Permalink_Search::init();
class PL_Permalink_Search {

	public static $search_prefix = 'pl_ss_';

	public static function init () {
		// Basic AJAX endpoints
		add_action('wp_ajax_get_saved_search_filters', array(__CLASS__, 'ajax_get_saved_search_filters'));
		add_action('wp_ajax_nopriv_get_saved_search_filters', array(__CLASS__, 'ajax_get_saved_search_filters'));
	}

	public static function generate_key ($search_id) {
		$hash = sha1($search_id);
		$key = self::$search_prefix . $hash;

		return $key;
	}

	public static function save_search ($search_id, $search_filters) {
		$key = self::generate_key($search_id);

		// Ensure these option-entries are NOT autoloaded on every request...
		return PL_Options::set($key, $search_filters, false);
	}

	public static function get_saved_search_filters ($search_id) {
		$key = self::generate_key($search_id);
		$result = PL_Options::get($key, false);

		return $result;
	}

	public static function ajax_get_saved_search_filters () {
		$result = array();
		$search_id = $_POST['search_id'];

		// Retrieve search filters associated with the given saved search ID...
		$filters = self::get_saved_search_filters($search_id);

		if (is_array($filters)) {
			foreach ($filters as $key => $value) {
				if (is_array($value)) {
					// This is how multidimensional arrays are stored in the name attribute in JS
					foreach ($value as $k => $v) {
						$result["{$key}[{$k}]"] = $v;
					}
				}
				else {
					// Otherwise, just store it regularly
					$result[$key] = $value;
				}
			}
		}

		echo json_encode($result);
		die();
	}

	// Clear all saved searches stored in the DB
	public static function clear () {
		$saved_searches = $wpdb->get_results('SELECT option_name FROM ' . $wpdb->prefix . 'options ' ."WHERE option_name LIKE 'pl_ss_%'");
		foreach ($saved_searches as $option) {
			PL_Options::delete($option->option_name);
		}
	}
}
