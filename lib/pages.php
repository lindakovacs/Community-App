<?php

PL_Pages::init();
/**
 * @todo expose list of taxonomies in helpers/taxonomy.php and get list from there instead
 */
class PL_Pages {

	public static $property_post_type = 'property';
	private static $listing_details = null;

	public static function init () {
		add_action('init', array(__CLASS__, 'setup_rewrite'));

		add_filter('query_vars', array(__CLASS__, 'setup_url_vars'));
		add_filter('pre_get_posts', array(__CLASS__, 'detect_virtual_page'));
		add_filter('the_posts', array(__CLASS__, 'the_posts'));
		add_filter('post_type_link', array(__CLASS__, 'get_property_permalink'), 10, 3);
				
		add_action('wp_footer', array(__CLASS__,'force_rewrite_update'));
		add_action('admin_footer', array(__CLASS__,'force_rewrite_update'));
		add_action('404_template', array(__CLASS__, 'dump_permalinks'));
	}

	/**
	 * Load rules
	 */
	public static function setup_rewrite(){
		// do not make public or Yoast will create sitemaps - we are making our own elsewhere
		register_post_type(self::$property_post_type, array('labels'=>array('name'=>__('Properties'), 'singular_name'=>__('property')), 'public'=>false, 'has_archive'=>true, 'rewrite'=>true, 'query_var'=>true, 'taxonomies'=>array(), 'exclude_from_search'=>true, 'publicly_queryable'=>false));

		add_rewrite_rule('property/([^/]*)/([^/]*)/([^/]*)/([^/]*)/([^/]*)/([^/]+)/?$', 'index.php?property=$matches[6]', 'top');
		add_rewrite_rule('property/([^/]+)/?$', 'index.php?property=$matches[1]', 'top');
	}

	/**
	 * Setup wp_query values to detect parameters
	 */
	public static function setup_url_vars($vars)	{
		array_push($vars, 'property');

		return $vars;
	}

	/**
	 * Fetch listing details if this is a details page
	 */
	public static function detect_virtual_page($query) {
		if (!empty($query->query_vars['property'])) {
			$args = array('listing_ids' => array($query->query_vars['property']));

			// Respect address_mode type -- exact, or block adresses (i.e., 'polygon')
			$args['address_mode'] = ( PL_Option_Helper::get_block_address() ? 'polygon' : 'exact' );

			$response = PL_Listing_Helper::results($args, false);
			if (!empty($response['listings'][0])) {
				$query->set('post_type', self::$property_post_type);
				self::$listing_details = $response['listings'][0];
			}
		}
	}

	/**
	 * When we get here should have some object to display, so create something if necessary
	 */
	public function the_posts($posts) {
		global $wp, $wp_query;

		if (!empty($wp_query->query_vars['property'])) {
			// If details page and have a listing, make a dummy post
			if (self::$listing_details) {
				// Creating a property page by creating a fake post instance
				$post = new stdClass;
				// fill properties of $post with everything a page in the database would have
				$post->ID = -1;						// use an illegal value for page ID
				$post->post_author = 1;				// post author id
				$post->post_date = null;			// date of post
				$post->post_date_gmt = null;
				$post->post_content = '';
				$post->post_title = self::$listing_details['location']['address'];
				$post->post_excerpt = '';
				$post->post_status = 'publish';
				$post->comment_status = 'closed';	// mark as closed for comments, since page doesn't exist
				$post->ping_status = 'closed';		// mark as closed for pings, since page doesn't exist
				$post->post_password = '';			// no password
				$post->post_name = self::$listing_details['id'];
				$post->to_ping = '';
				$post->pinged = '';
				$post->modified = $post->post_date;
				$post->modified_gmt = $post->post_date_gmt;
				$post->post_content_filtered = '';
				$post->post_parent = 0;
				$post->guid = null;
				$post->menu_order = 0;
				$post->post_style = '';
				$post->post_type = 'property';
				$post->post_mime_type = '';
				$post->comment_count = 0;

				// set filter results
				$posts = array($post);

				// reset wp_query properties to simulate a found page
				$wp_query->is_page = true;
				$wp_query->is_singular = true;
				$wp_query->is_single = true;
				$wp_query->is_home = false;
				$wp_query->is_archive = false;
				$wp_query->is_category = false;
				unset($wp_query->query['error']);
				$wp_query->query_vars['error'] = '';
				$wp_query->is_404 = false;
			}
		}

		return $posts;
	}

	public static function get_listing_details() {
		return self::$listing_details;
	}

	public static function get_taxonomy_object() {
		return self::$taxonomy_object;
	}

	/**
	 * Provide template for property details page URLs
	 */
	public static function get_link_template () {
		$permalink_struct = get_option('permalink_structure');
		if (empty($permalink_struct)) {
			// non pretty format
			$link = '?property=%id%';
		}
		else {
			$link = "/property/%region%/%locality%/%postal%/%neighborhood%/%address%/%id%/";
		}
		return home_url($link);
	}

	/**
	 * Create a pretty link for property details page
	 */
	public static function get_url ($placester_id, $listing = array()) {
		$default = array(
				'region' => 'region', 'locality' => 'locality', 'postal' => 'postal',
				'neighborhood' => 'neighborhood', 'address' => 'address', 'id' => ''
		);

		$listing = wp_parse_args($listing, $default);
		$listing['id'] = $placester_id;

		$tmpl_replace = $tmpl_keys = array();
		$url = self::get_link_template();

		foreach($default as $key => $val) {
			$tmpl_replace[] = empty($listing[$key]) ? '-' : preg_replace('/[^a-z0-9\-]+/', '-', strtolower($listing[$key]));
			$tmpl_keys[] = '%'.$key.'%';
		}
		$url = str_replace($tmpl_keys, $tmpl_replace, $url);

		return $url;
	}

	/**
	 * Build a permalink for a property page
	 * Handles when we have a dummy property post object - normally only when viewing a property details page
	 */
	public static function get_property_permalink ($permalink, $post, $leavename) {
		if (!empty($permalink) && is_object($post) && $post->post_type == 'property' && !empty($post->post_name) && !in_array($post->post_status, array('draft', 'pending', 'auto-draft'))) {
			if (!empty(self::$listing_details) && self::$listing_details['id']==$post->post_name) {
				// viewing virtual details page
				return self::get_url($post->post_name, self::$listing_details);
			}
		}
		return $permalink;
	}

	/**
	 * Plugin version change - run updates, flush rewrites, etc
	 */
	public static function force_rewrite_update () {
		if (defined('PL_PLUGIN_VERSION')) {
			$current_version = get_option('pl_plugin_version');
			if ($current_version != PL_PLUGIN_VERSION) {
				// Run the updater script before updating the version number...
				include_once(trailingslashit(PLACESTER_PLUGIN_DIR) . 'updater.php');

				// Update version in DB
				update_option('pl_plugin_version', PL_PLUGIN_VERSION);

				global $wp_rewrite;
				$wp_rewrite->flush_rules();

				// PL_Cache::invalidate();
			}
		}
	}

	/**
	 * Flush rewrites - maybe after we get a 404
	 */
	public static function dump_permalinks () {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
function placester_get_property_url($id) {
	return PL_Pages::get_url($id);
}

class PL_Page_Helper {
	public static function get_url($id) {
		return PL_Pages::get_url($id);
	}
}
