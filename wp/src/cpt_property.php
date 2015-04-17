<?php

class PL_CPT_Template {
	protected $connection;
	protected $property;

	public function __construct() {
		$this->connection = new PL_API_Connection('Kt1ZS3DJzKY1fdimQLHjo2tXiP9jl1ZH9COSLvWY2iMa');
		$this->property = new PL_Property_Listing();

		add_action('init', array($this, 'register_post_type'));
		add_filter('pre_get_posts', array($this, 'detect_virtual_page'));
		add_filter('the_posts', array($this, 'the_posts'));
		add_filter('the_post', array($this, 'the_post'));
	}

	public function register_post_type() {

		// custom post type to hold a customized shortcode
		$args = array(
			'name' => 'property',
			'labels' => array(
				'name' => __('Properties', 'placester'),
				'singular_name' => __( 'Property', 'placester' ),
				'add_new_item' => __('Add New Property', 'placester'),
				'edit_item' => __('Edit Property', 'placester'),
				'new_item' => __('New Property', 'placester'),
				'all_items' => __('All Properties', 'placester'),
				'view_item' => __('View Property', 'placester'),
				'search_items' => __('Search Properties', 'placester'),
				'not_found' =>  __('No properties found', 'placester'),
				'not_found_in_trash' => __('No properties found in Trash', 'placester')),
			'public' => true,
			'has_archive' => true
		);

		register_post_type('property', $args );
	}

	/**
	 * Setup wp_query values to detect parameters
	 */
//		add_filter('query_vars', array($this, 'setup_url_vars'));
	public function setup_url_vars($vars)
	{
		array_push($vars, 'property');

		return $vars;
	}

	/**
	 * Fetch listing details if this is a details page
	 */
	public function detect_virtual_page($query)
	{
		if ($query->is_main_query() && $query->query_vars['property']) {
			$this->property->listing_read($this->connection, $query->query_vars['property']);
			if($this->property->get_id()) {
				$query->set('property', 432);
			}
		}
	}

	/**
	 * Create a pretty link for property details page
	 */
//		add_filter('post_type_link', array($this, 'get_permalink'));
	public function get_url($listing = null)
	{
		$default = array(
			'region' => 'region',
			'locality' => 'locality',
			'postal' => 'postal',
			'neighborhood' => 'neighborhood',
			'address' => 'address',
			'id' => ''
		);
		//$listing = wp_parse_args($listing, array('location' => $default));
		//$listing = $listing['location'];
		//$listing['id'] = $placester_id;
		// not using get_permalink because it's a virtual page
		$url = home_url("/property/%region%/%locality%/%postal%/%neighborhood%/%address%/%id%/");

		$tmpl_replace = $tmpl_keys = array();
		foreach ($default as $key => $val) {
			$tmpl_replace[] = empty($listing[$key]) ? '-' : preg_replace('/[^a-z0-9\-]+/', '-', strtolower($listing[$key]));
			$tmpl_keys[] = '%' . $key . '%';
		}
		$url = str_replace($tmpl_keys, $tmpl_replace, $url);

		return $url;
	}

	/**
	 * Build a permalink for a property page
	 * Handles when we have a dummy property post object - normally only when viewing a property details page
	 */
	public function get_property_permalink($permalink, $post, $leavename)
	{
		if (!empty($permalink) && is_object($post) && $post->post_type == 'property' && !empty($post->post_name) && !in_array($post->post_status, array('draft', 'pending', 'auto-draft'))) {
			if($this->property->get_id() == $post->post_name) {
				return $this->get_url();
			}
		}
		return $permalink;
	}

	/**
	 * When we get here should have some object to display, so create something if necessary
	 */
	public function the_posts($posts) {
		global $wp_query;

		if ($wp_query->query_vars['property'] && $this->property->get_id())
			foreach ($posts as &$post) {
					$post->post_title = $this->property->get_address();
				}

		return $posts;
	}

	/**
	 * When we get here should have some object to display, so create something if necessary
	 */
	public function the_post(&$post) {
		global $wp_query;

		if (is_singular('property') && $this->property->get_id())
			if($post->post_type == 'property') {
				$post->post_name = $this->property->get_id();
				$post->post_title = $this->property->get_address();
				$post->post_content = $this->property;

				$post->post_date = null;
				$post->post_date_gmt = null;
				$post->modified = null;
				$post->modified_gmt = null;
			}

		return $post;
	}
}