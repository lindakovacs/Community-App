<?php


require_once('interface.php');
require_once('attributes.php');


class PLX_Local_Interface extends PLX_Data_Interface {
	public static function init() {
		self::$attribute_interface = new PLX_Placester_Attributes();
		self::$parameter_interface = new PLX_Parameters();
		self::$listing_interface = new PLX_Local_Listings();
		self::$search_interface = new PLX_Local_Search();
		self::$image_interface = new PLX_Local_Images();
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
				return 'decimal';

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
			if($value === '')
				continue;

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
				if($parameter['type'] == PLX_Attributes::BOOLEAN) {
					if(!$value || strtolower($value) == 'false')
						continue;

					$value = 'true';
				}

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


class PLX_Local_Search extends PLX_Search {
	public function _listings($args = array()) {
		global $wpdb;

		$post_clause = array(
			'query' => "select $wpdb->posts.* from $wpdb->posts",
			'count' => "select count(*) from $wpdb->posts",
			'where' => " where post_type = 'property'");

		$meta_clause = array('join' => '', 'where' => '');
		$sort_clause = array('join' => '', 'order' => '');

		$filters = PLX_Local_Interface::parse_search_args($args);

		if($filters['post_filter'])
			$post_clause['where'] .= " and (" . implode(') and (', $filters['post_filter']) . ")";

		if($filters['meta_filter']) {
			$meta_query = new WP_Meta_Query($filters['meta_filter']);
			$meta_clause = $meta_query->get_sql('post', $wpdb->posts, 'ID');
		}

		if(isset($args['sort_by'])) {
			switch($sort_by = $args['sort_by']) {
				case 'id':
					$sort_clause['join'] = "";
					$sort_clause['order'] = " order by ID";
					break;

				case 'created_at':
					$sort_clause['join'] = "";
					$sort_clause['order'] = " order by post_date";
					break;

				case 'updated_at':
					$sort_clause['join'] = "";
					$sort_clause['order'] = " order by post_modified";
					break;

				default:
					if($attribute = PLX_Attributes::get($sort_by)) {
						$meta_key = 'plx.' . $sort_by;
						$meta_type = PLX_Local_Interface::get_attribute_type($attribute['type']);

						$sort_clause['join'] = " left join $wpdb->postmeta as metasort"
							. " on (ID = metasort.post_id and metasort.meta_key = '$meta_key')";
						$sort_clause['order'] = " order by cast(metasort.meta_value as $meta_type)";
					}
					break;
			}

			if($sort_clause['order'] && isset($args['sort_type'])) {
				if(in_array($sort_type = strtolower($args['sort_type']), array('asc', 'desc')))
					$sort_clause['order'] .= " $sort_type";
			}
		}

		$limit = isset($args['limit']) ? $args['limit'] : 150; // sanitize!
		$offset = isset($args['offset']) ? $args['offset'] : 0; // sanitize!
		$sort_clause['limit'] = " limit $limit offset $offset";

		$posts = $wpdb->get_results($post_clause['query']
			. $meta_clause['join'] . $sort_clause['join']
			. $post_clause['where'] . $meta_clause['where']
			. $sort_clause['order'] . $sort_clause['limit']);

		$listings = array();
		foreach($posts as $post)
			$listings[] = PLX_Local_Interface::get_listing_from_post($post);

		if(($count = count($posts)) && $count < $limit)
			$total = $offset + $count;
		else
			$total = $wpdb->get_var($post_clause['count'] . $meta_clause['join']
				. $post_clause['where'] . $meta_clause['where']);

		return array(
			'total' => $total, 'offset' => $offset, 'limit' => $limit, 'count' => $count, 'listings' => $listings);
	}

	public function _locations($args = array()) {
		return self::aggregates(array('keys' => array(
			'region', 'locality', 'postal', 'neighborhood', 'county')));
	}

	public function _aggregates($args = array()) {
		global $wpdb;

		if(!isset($args['keys']))
			return array();

		$post_clause = array(
			'query' => "select distinct aggregate.meta_value from $wpdb->posts",
			'where' => " where post_type = 'property'");

		$meta_clause = array('join' => '', 'where' => '');

		$filters = PLX_Local_Interface::parse_search_args($args);

		if($filters['post_filter'])
			$post_clause['where'] .= " and (" . implode(') and (', $filters['post_filter']) . ")";

		if($filters['meta_filter']) {
			$meta_query = new WP_Meta_Query($filters['meta_filter']);
			$meta_clause = $meta_query->get_sql('post', $wpdb->posts, 'ID');
		}

		$aggr_result = array();
		foreach((array) $args['keys'] as $name)
			if(($attribute = PLX_Attributes::get($name)) && $attribute['type'] == PLX_Attributes::TEXT_VALUE)
			{
				$meta_key = 'plx.' . $name;
				$aggr_clause = array('join' => " inner join $wpdb->postmeta as aggregate"
					. " on (ID = aggregate.post_id and aggregate.meta_key = '$meta_key')");

				$aggr_values = $wpdb->get_col($post_clause['query']
					. $meta_clause['join'] . $aggr_clause['join']
					. $post_clause['where'] . $meta_clause['where']);

				$aggr_result[$name] = $aggr_values;
			}

		return $aggr_result;
	}
}


class PLX_Local_Listings extends PLX_Listings {
	public function _create($args = array()) {
		$listing = array();
		$meta = array();

		foreach($args as $name => $value) {
			if(in_array($name, array('id', 'created_at', 'updated_at', 'zoning_type', 'purchase_type')))
				;
			else if($name == 'listing_type') {
				$map = PLX_Local_Interface::get_listing_type_map($value);
				$meta['plx.listing_type'] = $listing['listing_type'] = $map[0];
				$meta['plx.zoning_type'] = $listing['zoning_type'] = $map[1];
				$meta['plx.purchase_type'] = $listing['purchase_type'] = $map[2];
			}
			else if($attribute = PLX_Attributes::get($name)) {
				if($value === '')
					continue;

				if($attribute['type'] == PLX_Attributes::BOOLEAN) {
					if(!$value || strtolower($value) == 'false')
						continue;

					$value = 'true';
				}

				$meta['plx.' . $name] = $listing[$name] = $value;
			}
		}

		$listing['images'] = array();
		$images = isset($args['images']) ? $args['images'] : array();

		foreach($images as $image)
			if(isset($image['filename'])) {
				if($attachment_id = PLX_Local_Interface::build_post_attachment(0, $image['filename']))
					$listing['images'][] = array('id' => $attachment_id);
			}

		if($images = count($listing['images']))
			$meta['plx.images'] = $images;

		$post = wp_insert_post(array(
			'post_type' => 'property',
			'post_content' => serialize($listing)));

		if($post) {
			PLX_Local_Interface::store_post_fields($post, $meta);
			PLX_Local_Interface::store_post_images($post, $listing['images'], array());
			return array('id' => $post);
		}

		return null;
	}

	public function _read($args = array()) {
		if(!isset($args['id']) || !($post = get_post($args['id'])))
			return null;

		return PLX_Local_Interface::get_listing_from_post($post);
	}

	public function _update($args = array()) {
		if(!isset($args['id']) || !($listing = self::read($args)))
			return null;

		$new_listing = array();
		$new_meta = $old_meta = array();
		$new_imgs = $old_imgs = array();

		foreach($listing as $name => $value) {
			if(in_array($name, array('id', 'created_at', 'updated_at', 'listing_type', 'zoning_type', 'purchase_type')))
				$new_listing[$name] = $value;
			else if($name == 'images') {
				$old_imgs = $value;
				$old_meta['plx.' . $name] = count((array) $value);
			}
			else if($attribute = PLX_Attributes::get($name)) {
				$old_meta['plx.' . $name] = $value;
			}
		}

		foreach($args as $name => $value) {
			if(in_array($name, array('id', 'created_at', 'updated_at', 'listing_type', 'zoning_type', 'purchase_type')))
				;
			else if($name == 'images') {
				foreach((array) $value as $image)
					if(isset($image['image_id']))
						$new_imgs[] = array('id' => $image['image_id']);
					else if(isset($image['filename'])) {
						if($attachment_id = PLX_Local_Interface::build_post_attachment($args['id'], $image['filename']))
							$new_imgs[] = array('id' => $attachment_id);
					}
			}
			else if($attribute = PLX_Attributes::get($name)) {
				if($value === '')
					continue;

				if($attribute['type'] == PLX_Attributes::BOOLEAN) {
					if(!$value || strtolower($value) == 'false')
						continue;

					$value = 'true';
				}

				$new_meta['plx.' . $name] = $new_listing[$name] = $value;
			}
		}

		$new_listing['images'] = $new_imgs;
		if(count($new_listing['images']))
			$new_meta['images'] = count($new_listing['images']);


		$post = wp_update_post(array(
			'ID' => $new_listing['id'],
			'post_content' => serialize($new_listing)));

		if($post) {
			PLX_Local_Interface::store_post_fields($post, $new_meta, $old_meta);
			PLX_Local_Interface::store_post_images(0, $new_imgs, $old_imgs);
			return array('id' => $post);
		}

		return null;
	}

	public function _delete ($args = array()) {
		if(isset($args['id']) && $post = wp_delete_post($args['id']))
			return $post->ID;

		return null;
	}
}


class PLX_Local_Images extends PLX_Images {
	protected function _upload ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$file_array = array('name' => $file_name, 'type' => $file_mime_type, 'tmp_name' => $file_tmpname);
		$result = wp_handle_upload($file_array, array('test_form' => false, 'test_size' => false));

		if(isset($result['error']))
			return array('message' => $result['error']);

		return array('url' => $result['url'], 'filename' => $result['file']);
	}

	protected function _resize($image_args) {
		return $image_args['old_image'];
	}
}
