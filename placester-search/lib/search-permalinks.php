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

	public static function get_listing_attributes() {
		static $hashed_attributes;
		if(empty($hashed_attributes)) {
			$attributes = PL_Shortcode_CPT::get_listing_attributes(true);

			$hashed_attributes = array();
			foreach ($attributes as $attribute) {
				$name = $attribute['attribute'];
				$group = $attribute['group'];
				switch($group) {
					case "cur_data":
					case "uncur_data":
						$group = "metadata";
						break;
				}
				$hashed_attributes[$group ? $group . '[' . $name . ']' : $name] = $attribute['label'];
				$hashed_attributes[$group ? $group . '[min_' . $name . ']' : 'min_' . $name] = 'Min ' . $attribute['label'];
				$hashed_attributes[$group ? $group . '[max_' . $name . ']' : 'max_' . $name] = 'Max ' . $attribute['label'];
			}
		}

		return $hashed_attributes;
	}

	public static function display_saved_search_filters ($search_id) {
		$filters = self::get_saved_search_filters($search_id);
		$attributes = self::get_listing_attributes();

		ob_start();
		if (is_array($filters)) {
			echo "<ul>";
			// display top level criteria first -- it looks nicer this way
			foreach ($filters as $key => $value) {
				if (!is_array($value)) {
					switch($key) {
						case "sort_by":
						case "sort_type":
						case "limit":
						case "offset":
							continue;
						default:
							if ($value && substr($key, -6) != "_match") {
								if ($label = $attributes[$key]) {
									echo "<li>{$label}&nbsp;=&nbsp;{$value}</li>";
								} else {
									echo "<li>{$key}&nbsp;=&nbsp;{$value}</li>";
								}}}}}

			// now display the nested criteria
			foreach ($filters as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $k => $v) {
						if ($v && substr($k, -6) != "_match") {
							if ($label = $attributes["{$key}[{$k}]"]) {
								echo "<li>{$label}&nbsp;=&nbsp;$v</li>";
							} else {
								echo "<li>{$key}[{$k}]&nbsp;=&nbsp;$v</li>";
							}}}}}

			echo "</ul>\n";
		}
		return ob_get_clean();
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

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
class PL_Saved_Search extends PL_Permalink_Search {}
