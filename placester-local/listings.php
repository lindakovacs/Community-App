<?php


require_once(PLACESTER_PLUGIN_DIR . 'libnew/listings.php');


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

	public function _image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		$file_array = array('name' => $file_name, 'type' => $file_mime_type, 'tmp_name' => $file_tmpname);
		$result = wp_handle_upload($file_array, array('test_form' => false, 'test_size' => false));

		if(isset($result['error']))
			return array('message' => $result['error']);

		return array('url' => $result['url'], 'filename' => $result['file']);
	}
}
