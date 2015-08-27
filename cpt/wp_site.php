<?php

class PL_WP_Site {
	static protected $singleton;
	protected $connection;
	protected $templates;

	protected $query_results;
	protected $current_listing;


	public function __construct() {
		if(!self::$singleton) {
			self::$singleton = $this;
			$this->register_wp_site();
		}
	}

	public function __sleep() {
		$this->query_results = null;
		$this->current_listing = null;
	}

	public function __wakeup() {
		if(!self::$singleton) {
			self::$singleton = $this;
			$this->register_wp_site();
		}
	}

	protected static function register_wp_site() {
		add_action('init', array(__CLASS__, 'wp_init'));
		add_filter('the_posts', array(__CLASS__, 'wp_the_posts'), 10, 2);
		add_filter('the_post', array(__CLASS__, 'wp_the_post'), 10, 2);
		add_filter('post_type_link', array(__CLASS__, 'wp_post_type_link'), 10, 4);
	}

	public static function wp_init() {
		register_post_type('pl_property', array(
			'name' => 'pl_property',
			'labels' => array(
				'name' => __('Properties', 'pdx_builder'),
				'singular_name' => __( 'Property', 'pdx_builder')),
			'public' => true,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => false
		));
		register_post_type('pl_property_template', array(
			'name' => 'pl_property_template',
			'labels' => array(
				'name' => __('Property Templates', 'pdx_builder'),
				'singular_name' => __( 'Property Template', 'pdx_builder')),
			'public' => false,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => false
		));

		add_rewrite_rule('^property(/[^/]+){0,5}/([0-9a-f]{24})','index.php?pl_property=$matches[2]', 'top');
	}

	public static function wp_activate_plugin() {
		self::wp_init();
		flush_rewrite_rules();
	}

	public static function wp_deactivate_plugin() {
	}

	public static function wp_the_posts($posts, $query) {
		if($query && $query->get('post_type') == 'pl_property' && !is_admin()) {
			$templates = self::$singleton->get_property_templates();
			if($templates[0]) {
				$results = self::$singleton->get_query_results($query);

				$query->posts_found = $results->total;

				foreach($results as $listing) {
					$posts[] = $post = $templates[0];
					$post->post_type = 'property';
					$post->post_name = $listing->pdx_id;
					$post->post_title = $listing->address;
					$post->post_author = 'pl_author';
					$post->post_date = $listing->created_at;
				}
			}
		}
		return $posts;
	}

	public static function wp_the_post($post, $query) {
		if($query && $query->get('post_type') == 'pl_property') {
			if($post && ($post->post_type == 'pl_property_template' || $post->post_type == 'property')) {
				$results = self::$singleton->get_query_results($query);
				self::$singleton->current_listing = $listing = $results[$query->current_post];

				global $global_listing;
				$global_listing = $listing;

				global $authordata; // sad, but there doesn't seem to be another way
				$authordata = new stdClass();
				$authordata->user_nicename = $authordata->display_name = $listing->aname . ' of ' . $listing->oname;

				$post->post_type = 'property';
			}
		}
		return $post;
	}

	public static function wp_post_type_link($permalink, $post, $leavename, $sample) {
		global $wp_query;
		if($wp_query && $wp_query->get('post_type') == 'pl_property') {
			if($post && ($post->post_type == 'pl_property_template' || $post->post_type == 'property')) {
				return home_url('?pl_property=' . $post->post_name);
			}
		}
		return $permalink;
	}

	protected function get_connection($api_key = 'Ea2FELLoFQLvRggANfFzS6eyoV3nC31kfYFpRvW3vo36XoVEcgQCLDCL9oTSng7cT9TExjJvRUACKUtLrKYrUQaa') {
		if(!$this->connection) {
			$this->connection = new PL_API_Connection($api_key);
			$this->connection->enable_attribute(array_keys($this->connection->get_standard_attributes()));
			$this->connection->enable_attribute(array_keys($this->connection->get_custom_attributes()));
		}
		return $this->connection;
	}

	protected function get_property_templates() {
		if(!$this->templates) {
			$template_search = array('post_type' => 'pl_property_template', 'post_status' => 'publish');
			$this->templates = get_posts($template_search);
		}
		return $this->templates;
	}

	public function get_query_results($wp_query) {
		if(!$this->query_results) {
			$connection = $this->get_connection();
			$request = $connection->new_search_request();

			$request->set('pdx_id', $wp_query->get('pl_property'));
			$request->set('limit', 1);

			$this->query_results = $this->connection->search_listings($request);
		}
		return $this->query_results;
	}
}
