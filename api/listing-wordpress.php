<?php 


require_once('listing-interface.php');


class PL_WordPress_Listing_Util extends PL_SQL_Listing_Util {
	protected static function get_listing_from_post($post) {
		$listing = unserialize($post->post_content);
		$listing['id'] = $post->ID;
		$listing['metadata']['url'] = $post->guid;

		$listing['created_at'] = $post->post_date_gmt;
		$listing['updated_at'] = $post->post_modified_gmt;

		$listing['property_type'] = $listing['metadata']['prop_type'];
		$listing['listing_types'] = (array) $listing['listing_types'];
		$listing['zoning_types'] = (array) $listing['zoning_types'];
		$listing['purchase_types'] = (array) $listing['purchase_types'];

		$listing['cur_data'] = &$listing['metadata'];
		$listing['uncur_data'] = array();

		return $listing;
	}

	protected static function get_key_from_attribute($name, $include_plurals = true) {
		if($name == 'compound_type')
			return $name;

		else if($include_plurals && in_array($name, array('listing_types', 'zoning_types', 'purchase_types')))
			return $name;

		else if($name == 'property_type')
			return 'metadata.prop_type';

		else if(strpos($name, 'cur_data.') === 0)
			return 'metadata.' . substr($name, 9);
		else if(strpos($name, 'uncur_data.') === 0)
			return 'metadata.' . substr($name, 11);

		else if(strpos($name, 'metadata.') === 0)
			return $name;
		else if(strpos($name, 'location.') === 0)
			return $name;

		else
			return null;
	}

	protected static function build_meta_filter($name, $value, $match = null) {
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
			if($match)
				$filter['compare'] = self::get_compare_from_match($match);
		}

		return $filter;
	}

	protected static function parse_search_args($args) {
		$post_filter = array();
		$meta_filter = array();

		foreach($args as $name => $value)
			switch($name) {
				case 'listing_ids':
					$post_filter[$name] = 'ID in (' . implode(', ', (array) $value) . ')'; // sanitize!
					break;

				case 'listings_types':
				case 'zoning_types':
				case 'purchase_types':
					$match = self::get_query_variable($args, $name . '_match', 'in');
					$meta_filter[$name] = self::build_meta_filter($name, $value, $match);
					break;

				case 'property_type':
					$match = self::get_query_variable($args, $name . '_match');
					$meta_filter[$name] = self::build_meta_filter('metadata.prop_type', $value, $match);
					break;

				case 'location':
					foreach((array) $value as $key => $val) if(strpos($key, '_match') !== strlen($key) - 6) {
						$match = self::get_query_variable($value, $key . '_match');
						$meta_filter['location.' . $key] = self::build_meta_filter('location.' . $key, $val, $match);
					}
					break;

				case 'metadata':
					foreach((array) $value as $key => $val) if(strpos($key, '_match') !== strlen($key) - 6) {
						if(strpos($key, 'min_') === 0) {
							$key = substr($key, 4);
							$meta_filter['metadata.min_' . $key] = self::build_meta_filter('metadata.' . $key, $val, 'ge');
						} else if(strpos($key, 'max_') === 0) {
							$key = substr($key, 4);
							$meta_filter['metadata.max_' . $key] = self::build_meta_filter('metadata.' . $key, $val, 'le');
						} else {
							$match = self::get_query_variable($value, $key . '_match');
							$meta_filter['metadata.' . $key] = self::build_meta_filter('metadata.' . $key, $val, $match);
						}
					}
					break;
			}

		return array('post_filter' => $post_filter, 'meta_filter' => $meta_filter);
	}

	protected static function store_post_fields($post, $new_fields, $old_fields = array()) {
	}

	protected static function store_post_images($post, $new_images, $old_images = array()) {
	}
}


class PL_WordPress_Listing extends PL_WordPress_Listing_Util implements PL_Listing_Interface {
	public static function get ($args = array()) {
		global $wpdb;

		$post_clause = array(
			'query' => "select $wpdb->posts.* from $wpdb->posts",
			'count' => "select count(*) from $wpdb->posts",
			'where' => " where post_type = 'property'");

		$meta_clause = array('join' => '', 'where' => '');
		$sort_clause = array('join' => '', 'order' => '');

		$args = PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get', 'args'));
		$filters = self::parse_search_args($args);

		if($filters['post_filter'])
			$post_clause['where'] .= " and (" . implode(') and (', $filters['post_filter']) . ")";
		if($filters['meta_filter']) {
			$meta_query = new WP_Meta_Query($filters['meta_filter']);
			$meta_clause = $meta_query->get_sql('post', $wpdb->posts, 'ID');
		}

		if($sort_by = self::get_query_variable($args, 'sort_by')) {
			switch($sort_by) {
				case 'created_at':
					$sort_clause['join'] = "";
					$sort_clause['order'] = " order by post_date";
					break;

				case 'updated_at':
					$sort_clause['join'] = "";
					$sort_clause['order'] = " order by post_modified";
					break;

				case 'total_images':
					break;

				default:
					if($meta_key = self::get_key_from_attribute($sort_by, false)) { // sanitize!
						$sort_clause['join'] = " left join $wpdb->postmeta as metasort"
							. " on (ID = metasort.post_id and metasort.meta_key = '$meta_key')";
						$sort_clause['order'] = " order by metasort.meta_value";
					}
					break;
			}

			if($sort_clause['order'] && $sort_type = self::get_query_variable($args, 'sort_type')) {
				if(in_array(strtolower($sort_type), array('asc', 'desc')))
					$sort_clause['order'] .= " $sort_type";
			}
		}

		$limit = self::get_query_variable($args, 'limit', 150); // sanitize!
		$offset = self::get_query_variable($args, 'offset', 0); // sanitize!
		$sort_clause['limit'] = " limit $limit offset $offset";

		$posts = $wpdb->get_results($post_clause['query']
			. $meta_clause['join'] . $sort_clause['join']
			. $post_clause['where'] . $meta_clause['where']
			. $sort_clause['order'] . $sort_clause['limit']);

		$listings = array(); foreach($posts as $post)
			$listings[] = self::get_listing_from_post($post);

		if(($count = count($posts)) && $count < $limit)
			$total = $offset + $count;
		else
			$total = $wpdb->get_var($post_clause['count'] . $meta_clause['join']
				. $post_clause['where'] . $meta_clause['where']);

		return array(
			'total' => $total, 'offset' => $offset, 'limit' => $limit, 'count' => $count, 'listings' => $listings);
	}

	public static function locations ($args = array()) {
		$aggr_result = self::aggregates(array('keys' => array(
			'location.region', 'location.locality', 'location.postal', 'location.neighborhood', 'location.county')));

		foreach((array) $aggr_result as $name => $values) {
			$aggr_result[substr($name, 9)] = $values; unset($aggr_result[$name]);
		}

		return $aggr_result;
	}

	public static function aggregates ($args = array()) {
		global $wpdb;

		$post_clause = array(
			'query' => "select distinct aggregate.meta_value from $wpdb->posts",
			'where' => " where post_type = 'property'");

		$meta_clause = array('join' => '', 'where' => '');

		$args = PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get.aggregate', 'args'));
		$filters = self::parse_search_args($args);

		if($filters['post_filter'])
			$post_clause['where'] .= " and (" . implode(') and (', $filters['post_filter']) . ")";
		if($filters['meta_filter']) {
			$meta_query = new WP_Meta_Query($filters['meta_filter']);
			$meta_clause = $meta_query->get_sql('post', $wpdb->posts, 'ID');
		}

		if(!($keys = self::get_query_variable($args, 'keys')))
			return array();

		$aggr_result = array();
		foreach((array) $keys as $name)
			if($meta_key = self::get_key_from_attribute($name, true)) { // sanitize!

				$aggr_clause = array('join' => " inner join $wpdb->postmeta as aggregate"
				. " on (ID = aggregate.post_id and aggregate.meta_key = '$meta_key')");

				$aggr_values = $wpdb->get_col($post_clause['query']
					. $meta_clause['join'] . $aggr_clause['join']
					. $post_clause['where'] . $meta_clause['where']);

				$aggr_result[$name] = $aggr_values;
			}

		return $aggr_result;
	}

	public static function create ($args = array()) {
		$listing = array(
			'compound_type' => isset($args['compound_type']) ? $args['compound_type'] : null,
			'location' => isset($args['location']) ? $args['location'] : array(),
			'metadata' => isset($args['metadata']) ? $args['metadata'] : array());

		if($listing['compound_type'] && $map = self::get_listing_type_map($listing['compound_type'])) {
			$listing['listing_types'] = $map[0];
			$listing['zoning_types'] = $map[1];
			$listing['purchase_types'] = $map[2];
		}

		$meta = $listing; unset($meta['location']); unset($meta['metadata']);
		foreach($listing['location'] as $name => $value)
			$meta['location.' . $name] = $value;
		foreach($listing['metadata'] as $name => $value)
			$meta['metadata.' . $name] = $value;

		$post = wp_insert_post(array(
			'post_type' => 'property',
			'post_content' => serialize($listing)));

		if($post) {
			foreach($meta as $name => $value)
				add_post_meta($post, $name, $value);

			return array('id' => $post);
		}

		return null;
	}

	public static function read ($args = array()) {
		if(!isset($args['id']) || !($post = get_post($args['id'])))
			return null;

		return self::get_listing_from_post($post);
	}

	public static function update ($args = array()) {
		if(!isset($args['id']) || !($listing = self::read($args)))
			return null;

		$meta = array();
		if(isset($args['location'])) {
			$listing['location'] = $args['location'];
			foreach($args['location'] as $name => $value)
				$meta['location.' . $name] = $value;
		}
		if(isset($args['metadata'])) {
			$listing['metadata'] = $args['metadata'];
			foreach($args['metadata'] as $name => $value)
				$meta['metadata.' . $name] = $value;
		}

		$post = wp_update_post(array(
			'ID' => $listing['id'],
			'post_content' => serialize($listing)));

		if($post) {
			global $wpdb;
			$wpdb->query(
				"delete from $wpdb->postmeta where post_id = '${listing['id']}'"
				. " and (meta_key like 'location.%' or meta_key like 'metadata.%')");

			foreach($meta as $name => $value)
				add_post_meta($post, $name, $value);

			return array('id' => $post);
		}

		return null;
	}

	public static function delete ($args = array()) {
		if(isset($args['id']) && $post = wp_delete_post($args['id']))
			return $post->ID;

		return null;
	}

	public static function temp_image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$file_array = array('name' => $file_name, 'type' => $file_mime_type, 'tmp_name' => $file_tmpname);
		$result = wp_handle_upload($file_array, array('test_form' => false, 'test_size' => false));

		if(isset($result['error']))
			return array('message' => $result['error']);

		return array('url' => $result['url'], 'filename' => $result['file']);
	}
}
