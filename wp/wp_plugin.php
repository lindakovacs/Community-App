<?php


require_once('wp_connection.php');
require_once('wp_shortcodes.php');
require_once('wp_page.php');


class PL_WP_Plugin {
	static protected $singleton;
	protected $deactivate;

	protected $connection;
	protected $shortcode_system;
	protected $shortcode_context;

	protected $property_templates;

	// for managing virtual property pages
	protected $current_template;
	protected $current_results;
	protected $current_listing;

	// for managing pdx search pages
	protected $current_post; // the WP_Post object of our singular pdx_page (when we have one)
	protected $current_page; // the PL_WP_Page object used to render the pdx_page



	public function __construct() {
		if(!self::$singleton) {
			self::$singleton = $this;
			$this->register_wp_site();
		}
	}

	protected function register_wp_site() {
		register_activation_hook(BUILDER_FILE, array($this, 'wp_activate_plugin'));
		register_deactivation_hook(BUILDER_FILE, array($this, 'wp_deactivate_plugin'));

		add_action('init', array($this, 'wp_init'));
		add_filter('post_rewrite_rules', array($this, 'wp_post_rewrite_rules'), 10, 1);
		add_filter('page_rewrite_rules', array($this, 'wp_page_rewrite_rules'), 10, 1);
		add_filter('property_rewrite_rules', array($this, 'wp_property_rewrite_rules'), 10, 1);
		add_filter('pdx_page_rewrite_rules', array($this, 'wp_pdx_page_rewrite_rules'), 10, 1);

		if(!is_admin()) {
			add_action('wp_loaded', array($this, 'wp_loaded'));

			add_filter('the_posts', array($this, 'wp_the_posts'), 10, 2);
			add_filter('the_post', array($this, 'wp_the_post'), 10, 2);
			add_filter('the_content', array($this, 'wp_the_content'), 10, 1);

			add_filter('post_type_link', array($this, 'wp_post_type_link'), 10, 2);
			add_filter('get_previous_post_where', array($this, 'wp_get_adjacent_post_where'), 10, 2);
			add_filter('get_next_post_where', array($this, 'wp_get_adjacent_post_where'), 10, 2);
			add_filter('pre_get_shortlink', array($this, 'wp_pre_get_shortlink'), 10, 2);

		}
	}

	public function wp_init() {
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

		register_post_type('pdx_template', array(
			'name' => 'pdx_template',
			'labels' => array(
				'name' => __('PDX Templates', 'pdx_builder'),
				'singular_name' => __( 'PDX Template', 'pdx_builder')),
			'public' => false,
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

		global $wp;
		$wp->add_query_var('pdx_search_id');
		$wp->add_query_var('pdx_search_pg');
	}

	public function wp_activate_plugin() {
		self::wp_init();
		flush_rewrite_rules();
	}

	public function wp_deactivate_plugin() {
		$this->deactivate = true;
		flush_rewrite_rules();
	}

	public function wp_post_rewrite_rules($rules) {
		return $rules;
	}

	public function wp_page_rewrite_rules($rules) {
		if($this->deactivate)
			return $rules;

		$page_rules = array();
		foreach($rules as $regex => $rewrite)
			if(strpos($rewrite, 'pagename='))
				$page_rules[$regex] = $rewrite;

		$pdx_rules = array();
		foreach($page_rules as $regex => $rewrite) {
			$regex = str_replace('(.?.+?)', 'property-search(/[0-9a-z]{8})?', $regex);
			$rewrite = str_replace('pagename=', 'pdx_page=property-search&pdx_search_id=', $rewrite);
			$rewrite = str_replace('&page=', '&pdx_search_pg=', $rewrite);
			$pdx_rules[$regex] = $rewrite;
		}

		return $pdx_rules + $rules; // weird php array union operator does what we want
	}

	public function wp_property_rewrite_rules($rules) {
		if($this->deactivate)
			return array();

		$pdx_rules = array();
		$pdx_rules['property(/[^/]+){0,5}/([0-9a-f]{24})'] = 'index.php?property=$matches[2]';

		return $pdx_rules + $rules; // weird php array union operator does what we want
	}

	public function wp_pdx_page_rewrite_rules($rules) {
		if($this->deactivate)
			return array();

		return $rules;
	}

	public function wp_loaded() {
		// setup the global shortcode interpreter
		if(!$this->shortcode_system) {
			$this->shortcode_system = new PL_WP_Shortcode_System();
			$this->shortcode_system->set_connection($this->get_connection());
		}
	}

	public function wp_the_posts($posts, $query) {
		// if the query may refer to a remote property, try to retrieve it
		if($query->get('post_type') == 'property' && $query->get('property') && !$posts) {
			if($this->get_property_templates()) {
				$results = $this->set_current_results($query);

				$query->found_posts = $results->total();
				foreach($results as $listing) {
					$template = $this->set_current_template($listing);

					$post = clone $template;
					$post->post_type = 'property';
					$post->post_author = 'pdx_builder';
					$post->post_name = $listing->pdx_id;
					$post->post_title = $listing->address . ', ' . $listing->locality;
					$post->post_date = $listing->created_at;
					$post->post_date_gmt = $listing->created_at;
					$post->post_modified = $listing->updated_at;
					$post->post_modified_gmt = $listing->updated_at;
					$post->guid = home_url('/property/' . $this->current_listing->pdx_id);

					$posts[] = $post;
				}

				// for a singular page, we may need the current listing in the header, before the post is set up
				if($results->count() == 1)
					$this->set_current_listing(0);
			}
		}

		else if($query->get('post_type') == 'pdx_page' && $query->get('pdx_page') && $posts) {
			$this->current_post = $posts[0];
			$this->current_page = new PL_WP_Page(new PL_Page_Context($this->get_connection()), $posts[0]);
			$this->shortcode_context = new PL_Shortcode_Context(new PL_Shortcode_Page($this->current_page));
		}

		return $posts;
	}

	public function wp_the_post($post, $query) {
		if($post->post_author == 'pdx_builder') {
			$listing = $this->set_current_listing($query->current_post);

			global $authordata; // sad, but there doesn't seem to be another way
			$authordata = new stdClass();
			$authordata->user_nicename = $authordata->display_name = $listing->aname . ' of ' . $listing->oname;
		}
		return $post;
	}

	public function wp_the_content($content) {
		if($this->current_page)
			return $this->current_page->get_content();

		return $content;
	}

	public function wp_post_type_link($permalink, $post) {
		if($post->post_type == 'property')
			$permalink = home_url('/property/' . $post->post_name . '/');
		else if($post->post_type == 'pdx_template' && is_singular('property'))
			$permalink = home_url('/property/' . $this->current_listing->pdx_id . '/');

		else if($post->post_type == 'pdx_page')
			if($this->current_post && $post->ID == $this->current_post->ID)
				$permalink = $this->current_page->get_current_page_url();
			else
				$permalink = get_page_link($post);

		return $permalink;
	}

	public function wp_get_adjacent_post_where($where) {
		$post = get_post();
		if($post->post_author == 'pdx_builder')
			$where = "WHERE true = false";

		return $where;
	}

	public function wp_pre_get_shortlink($shortlink, $id) {
		$post = get_post($id);
		if($post->post_type == 'property' && $post->post_author == 'pdx_builder')
			$shortlink = home_url('/?property=' . $post->post_name);
		else if($post->post_type == 'pdx_template' && is_singular('property'))
			$shortlink = home_url('/?property=' . $this->current_listing->pdx_id);

		return $shortlink;
	}

	public static function get_current_page() {
		if(self::$singleton && self::$singleton->current_page)
			return self::$singleton->current_page;

		return null;
	}

	public static function get_current_listing() {
		if(self::$singleton && self::$singleton->current_listing)
			return self::$singleton->current_listing;

		return null;
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
			$this->shortcode_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($this->current_listing));
		else
			$this->shortcode_context = $this->current_listing = null;

		return $this->current_listing;
	}

	protected function set_current_template($listing) {
		$templates = $this->get_property_templates();
		return $this->current_template = $templates[0];
	}
}
