<?php

class PDX_Object_CPT {
	protected $post_id;
	protected $post_object;
	protected $post_meta;

	public $api_key;


	static public function define() {
		static $initialized;

		if(!$initialized)
			add_action('init', array(__CLASS__, 'register_cpts'));

		$initialized = true;
	}

	static public function wp_register_cpts() {
		static $registered;

		if(!$registered) {
			register_post_type('pdx_connection_cpt', array(
				'name' => 'pdx_connection_cpt',
				'labels' => array(
					'name' => __('PDX Connections', 'pdx_builder'),
					'singular_name' => __( 'PDX Connection', 'pdx_builder')),
				'public' => false,
				'show_ui' => true,
				'supports' => array('title', 'editor', 'custom-fields')
			));
		}

		$registered = true;
	}

	static public function query_cpt() {
		global $wpdb;

		$post_ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM $wpdb->posts WHERE post_type = 'pdx_connection_cpt' AND post_status = 'Publish'"));
		return true;
	}

	public function __construct($id_or_key = null) {
		self::init();

		if(is_int($id_or_key)) {
			if($connection = $this->restore($id_or_key)) {
				$this->post_id = $id_or_key;
				$this->connection = $connection;
			}
			else if($connection = $this->rebuild($id_or_key)) {
				$this->post_id = $id_or_key;
				$this->connection = $connection;
			}
		}
		else if(is_string($id_or_key)) {
			if($connection = $this->initialize($id_or_key)) {
				$this->api_key = $id_or_key;
				$this->connection = $connection;
				$this->save_post();
				$this->save_meta();
			}
		}
	}

	protected function restore($id) {
		$this->post_id = $id;
		if($post = $this->load_post())
			return unserialize($post->post_content))

		return false;
	}

	protected function rebuild($id) {
		$this->post_id = $id;
		if(!$post = $this->load_post())
			return false;

		if(!$this->load_meta())
			return false;


	}

	protected function initialize($api_key) {
		$connection = new PL_API_Connection($api_key);
		$connection->enable_attribute(array_keys($connection->get_standard_attributes()));
		$connection->enable_attribute(array_keys($connection->get_custom_attributes()));
		return $connection;
	}

	protected function load_post() {
		return new stdClass();
	}
	protected function save_post() {
	}
	protected function load_meta() {
	}
	protected function save_meta() {
	}
}