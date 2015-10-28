<?php


require_once('wp_connection.php');
require_once('wp_shortcodes.php');


class PL_WP_Site {
	static protected $singleton;
	protected $connection;
	protected $property_templates;

	protected $current_template;
	protected $current_results;
	protected $current_listing;
	protected $site_shortcode_context;
	protected $post_shortcode_context;


	public function __construct() {
		if(!self::$singleton) {
			self::$singleton = $this;
			$this->register_wp_site();

			$this->shortcode_system = new PL_WP_Shortcode_System();
		}
	}

	protected static function register_wp_site() {
		add_action('init', array(__CLASS__, 'wp_init'));
		if(!is_admin()) {
			add_filter('the_posts', array(__CLASS__, 'wp_the_posts'), 10, 2);
			add_filter('the_post', array(__CLASS__, 'wp_the_post'), 10, 2);
			add_filter('post_type_link', array(__CLASS__, 'wp_post_type_link'), 10, 2);
			add_filter('get_previous_post_where', array(__CLASS__, 'wp_get_adjacent_post_where'), 10, 2);
			add_filter('get_next_post_where', array(__CLASS__, 'wp_get_adjacent_post_where'), 10, 2);
			add_filter('pre_get_shortlink', array(__CLASS__, 'wp_pre_get_shortlink'), 10, 2);
		}
	}

	public static function wp_init() {
		register_post_type('pdx_connection', array(
			'name' => 'pdx_connection',
			'labels' => array(
				'name' => __('PDX Connections', 'pdx_builder'),
				'singular_name' => __( 'PDX Connection', 'pdx_builder')),
			'public' => false,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => false
		));
		register_post_type('pdx_attribute', array(
			'name' => 'pdx_attribute',
			'labels' => array(
				'name' => __('PDX Attributes', 'pdx_builder'),
				'singular_name' => __( 'IDX Attribute', 'pdx_builder')),
			'public' => false,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => false
		));
		register_post_type('pdx_page', array(
			'name' => 'pdx_page',
			'labels' => array(
				'name' => __('PDX Pages', 'pdx_builder'),
				'singular_name' => __( 'PDX Page', 'pdx_builder')),
			'public' => true,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => false
		));
		register_post_type('pdx_template', array(
			'name' => 'pdx_template',
			'labels' => array(
				'name' => __('PDX Templates', 'pdx_builder'),
				'singular_name' => __( 'PDX Template', 'pdx_builder')),
			'public' => true,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => false
		));
		register_post_type('property', array(
			'name' => 'property',
			'labels' => array(
				'name' => __('Properties', 'pdx_builder'),
				'singular_name' => __( 'Property', 'pdx_builder')),
			'public' => true,
			'show_ui' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'rewrite' => true
		));

		add_rewrite_rule('^property(/[^/]+){0,5}/([0-9a-f]{24})','index.php?property=$matches[2]', 'top');
	}

	public static function wp_activate_plugin() {
		self::wp_init();
		flush_rewrite_rules();
	}

	public static function wp_deactivate_plugin() {
	}

	public static function wp_the_posts($posts, $query) {
		// setup the global shortcode interpreter
		if(!self::$singleton->site_shortcode_context)
			self::$singleton->site_shortcode_context =
				new PL_Shortcode_Context(new PL_Shortcode_Connection(self::$singleton->get_connection()));

		if($query->get('post_type') == 'property' && $query->get('property') && !$posts) {
			if(self::$singleton->get_property_templates()) {
				$results = self::$singleton->set_current_results($query);

				$query->found_posts = $results->total();
				foreach($results as $listing) {
					$template = self::$singleton->set_current_template($listing);

					$post = clone $template;
					$post->post_type = 'property';
					$post->post_author = 'pdx_builder';
					$post->post_name = $listing->pdx_id;
					$post->post_title = $listing->address . ', ' . $listing->locality;
					$post->post_date = $listing->created_at;
					$post->post_date_gmt = $listing->created_at;
					$post->post_modified = $listing->updated_at;
					$post->post_modified_gmt = $listing->updated_at;
					$post->guid = home_url('/property/' . self::$singleton->current_listing->pdx_id);

					$posts[] = $post;
				}

				// for a singular page, we may need the current listing in the header, before the post is set up
				if($results->count() == 1)
					self::$singleton->set_current_listing(0);
			}
		}

		return $posts;
	}

	public static function wp_the_post($post, $query) {
		if($post->post_author == 'pdx_builder') {
			$listing = self::$singleton->set_current_listing($query->current_post);

			global $authordata; // sad, but there doesn't seem to be another way
			$authordata = new stdClass();
			$authordata->user_nicename = $authordata->display_name = $listing->aname . ' of ' . $listing->oname;
		}
		return $post;
	}

	public static function wp_post_type_link($permalink, $post) {
		if($post->post_author == 'pdx_builder')
			$permalink = home_url('/property/' . $post->post_name . '/');
		else if($post->post_type == 'pdx_template' && is_singular('property'))
			$permalink = home_url('/property/' . self::$singleton->current_listing->pdx_id . '/');

		return $permalink;
	}

	public static function wp_pre_get_shortlink($shortlink, $id) {
		$post = get_post($id);
		if($post->post_author == 'pdx_builder')
			$shortlink = home_url('/?property=' . $post->post_name);
		else if($post->post_type == 'pdx_template' && is_singular('property'))
			$shortlink = home_url('/?property=' . self::$singleton->current_listing->pdx_id);

		return $shortlink;
	}

	public static function wp_get_adjacent_post_where($where) {
		$post = get_post();
		if($post->post_author == 'pdx_builder')
			$where = "WHERE true = false";

		return $where;
	}

	protected function get_connection() {
		if(!$this->connection)
			$this->connection = PL_WP_API_Connection::get_connection();
		return $this->connection;
	}

	protected function get_property_templates() {
		if(!$this->property_templates) {
			$template_search = array('post_type' => 'pdx_template', 'post_status' => 'publish');
			$this->property_templates = get_posts($template_search);
		}
		return $this->property_templates;
	}

	protected function set_current_results($wp_query) {
		$connection = $this->get_connection();
		$request = $connection->new_search_request();

		if($pdx_id = $wp_query->get('property')) {
			$request->set('pdx_id', $pdx_id);
		}

		return $this->current_results = $connection->search_listings($request);
	}

	protected function set_current_listing($index) {
		if($this->current_results && ($this->current_listing = $this->current_results[$index]))
			$this->post_shortcode_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($this->current_listing));
		else
			$this->post_shortcode_context = $this->current_listing = null;

		return $this->current_listing;
	}

	protected function set_current_template($listing) {
		$templates = $this->get_property_templates();
		return $this->current_template = $templates[0];
	}
}
