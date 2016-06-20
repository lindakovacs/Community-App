<?php


require_once(PLACESTER_PLUGIN_DIR . 'libnew/interface.php');
require_once(PLACESTER_PLUGIN_DIR . 'libnew/attributes.php');
require_once('listings.php');


class PLX_Local_Interface extends PLX_Data_Interface {
	public static function init() {
		self::$attribute_interface = new PLX_Placester_Attributes();
		self::$parameter_interface = new PLX_Parameters();
		self::$listing_interface = new PLX_Local_Listings();
		self::$search_interface = new PLX_Local_Search();
	}

	protected static function get_query_variable($array, $name, $default = null) {
		return isset($array[$name]) ? $array[$name] : $default;
	}

	public static function get_listing_type_map($compound_type) {
		static $compound_type_map;
		if(!isset($compound_type_map))
			$compound_type_map = array(
				'res_sale'    => array( 'res_sale',    'residential', 'sale'  ),
				'res_rental'  => array( 'res_rental',  'residential', 'rental'),
				'comm_sale'   => array( 'comm_sale',   'commercial',  'sale'  ),
				'comm_rental' => array( 'comm_rental', 'commercial',  'rental'),
				'vac_rental'  => array( 'vac_rental',   null,         'rental')
			);

		if(isset($compound_type_map[$compound_type]))
			return $compound_type_map[$compound_type];

		return array($compound_type, null, null);
	}

	public static function get_compare_from_match($match) {
		static $match_compare_map;
		if(!isset($match_compare_map))
			$match_compare_map = array(
				'eq' => '=', 'ne' => '!=', 'gt' => '>', 'ge' => '>=', 'lt' => '<', 'le' => '<=',
				'in' => 'in', 'nin' => 'not in', 'like' => 'rlike');

		if(isset($match_compare_map[$match]))
			return $match_compare_map[$match];

		return $match;
	}

	public static function get_attribute_type($plx_type) {
		switch($plx_type) {
			case PLX_Attributes::BOOLEAN:

			case PLX_Attributes::NUMERIC:
			case PLX_Attributes::CURRENCY:
			case PLX_Attributes::COORDINATE:
				return 'numeric';

			case PLX_Attributes::DATE_TIME:

			case PLX_Attributes::TEXT_ID:
			case PLX_Attributes::TEXT_VALUE:
			case PLX_Attributes::SHORT_TEXT:
			case PLX_Attributes::LONG_TEXT:
			default:
				return 'char';
		}
	}

	public static function get_listing_from_post($post) {
		$listing = unserialize($post->post_content);
		$listing['id'] = $post->ID;

		$listing['created_at'] = $post->post_date_gmt;
		$listing['updated_at'] = $post->post_modified_gmt;

		$listing['images'] = (array) $listing['images'];
		$order = 0; foreach($listing['images'] as &$image) {
			$image['url'] = wp_get_attachment_url($image['id']);
			$image['order'] = ++$order;
		}

		return $listing;
	}

	public static function get_key_from_attribute($name) {
		if($attribute = PLX_Attributes::get($name)) {
			if(in_array('id', 'created_at', 'updated_at'))
				return null;

			return 'plx.' . $name;
		}

		return null;
	}

	public static function build_meta_filter($name, $value, $match = null, $type = null) {
		if($match == 'exists')
			$filter = array('key' => $name, 'value' => 'bug #23268', 'compare' => ($value ? 'exists' : 'not exists'));

		else if($match == 'or_like')
			$filter = array('key' => $name, 'value' => '(' . implode(')|(', (array) $value) . ')', 'compare' => 'rlike');

		else if($match == 'and_like') {
			$filter = array();
			foreach((array) $value as $re)
				$filter[] = array('key' => $name, 'value' => $re, 'compare' => 'rlike');
		}

		else {
			$filter = array('key' => $name, 'value' => $value);
			if($type)
				$filter['type'] = $type;
			if($match)
				$filter['compare'] = self::get_compare_from_match($match);
		}

		return $filter;
	}

	public static function parse_search_args($args) {
		$post_filter = array();
		$meta_filter = array();

		foreach($args as $name => $value) { // sanitize!
			if($value === "") continue;

			if($name == 'id')
				$post_filter[$name] = 'ID in (' . implode(', ', (array) $value) . ')'; // sanitize!

			else if($name == 'min_created_at')
				$post_filter[$name] = 'post_date_gmt >= ' . $value;
			else if($name == 'max_created_at')
				$post_filter[$name] = 'post_date_gmt <= ' . $value;
			else if($name == 'min_updated_at')
				$post_filter[$name] = 'modified_date_gmt >= ' . $value;
			else if($name == 'max_updated_at')
				$post_filter[$name] = 'modified_date_gmt <= ' . $value;

			else if($name == 'min_list_date')
				$post_filter[$name] = 'post_date_gmt >= ' . $value;
			else if($name == 'max_list_date')
				$post_filter[$name] = 'post_date_gmt <= ' . $value;

			else if($name == 'images')
				$meta_filter[$name] = self::build_meta_filter('plx.images', $value, 'exists');
			else if($name == 'min_images')
				$meta_filter[$name] = self::build_meta_filter('plx.images', $value, 'ge', 'unsigned');
			else if($name == 'max_images')
				$meta_filter[$name] = self::build_meta_filter('plx.images', $value, 'le', 'unsigned');

			else if($name == 'sort_by')
				;
			else if($name == 'sort_type')
				;

			else if($parameter = PLX_Parameters::get($name)) {
				if($parameter && strpos($name, 'min_') === 0) {
					$key = 'plx.' . $parameter['attribute']; $type = self::get_attribute_type($parameter['type']);
					$meta_filter[$name] = self::build_meta_filter($key, $value, 'ge', $type);
				}
				else if($parameter && strpos($name, 'max_') === 0) {
					$key = 'plx.' . $parameter['attribute']; $type = self::get_attribute_type($parameter['type']);
					$meta_filter[$name] = self::build_meta_filter($key, $value, 'le', $type);
				}
				else if($name == $parameter['attribute']) {
					$match = self::get_query_variable($args, $name . '_match');
					$key = 'plx.' . $name; $type = self::get_attribute_type($parameter['type']);
					$meta_filter[$name] = self::build_meta_filter($key, $value, $match, $type);
				}
			}

			// this data interface supports additional attribute searches
			else if($attribute = PLX_Attributes::get($name)) {
				if($attribute['type'] != PLX_Attributes::COORDINATE) {
					$match = self::get_query_variable($args, $name . '_match');
					$key = 'plx.' . $name; $type = self::get_attribute_type($attribute['type']);
					$meta_filter[$name] = self::build_meta_filter($key, $value, $match, $type);
				}
			}
		}

		return array('post_filter' => $post_filter, 'meta_filter' => $meta_filter);
	}

	public static function store_post_fields($post, $new_fields, $old_fields = array()) {
		foreach($old_fields as $name => $value) {
			if(!isset($new_fields[$name]))
				delete_post_meta($post, $name);
		}

		foreach($new_fields as $name => $value) {
			if(!isset($old_fields[$name]) || $value != $old_fields[$name])
				update_post_meta($post, $name, $value);
		}
	}

	public static function build_post_attachment($post, $filename) {
		$filebase = basename($filename);
		$filetype = wp_check_filetype($filebase, null);

		$attachment = array(
			'post_title' => preg_replace( '/\.[^.]+$/', '', $filebase),
			'post_status' => 'inherit', 'post_content' => '',
			'post_mime_type' => $filetype['type']
		);

		return $attachment_id = wp_insert_attachment($attachment, $filename, $post);
	}

	public static function store_post_images($post, $new_images, $old_images = array()) {
		// if $post is set, we need to attach images to a newly created post
		if($post) foreach($new_images as $image)
			wp_update_post(array('ID' => $image['id'], 'post_parent' => $post));

		// if we have old images, check for attachments to delete
		if($old_images) {
			$old_ids = array(); foreach($old_images as $image) $old_ids[] = $image['id'];
			$new_ids = array(); foreach($new_images as $image) $new_ids[] = $image['id'];
			foreach(array_diff($old_ids, $new_ids) as $id)
				wp_delete_attachment($id, true);
		}
	}
}


PLX_Local_Interface::init();