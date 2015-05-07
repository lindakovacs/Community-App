<?php

class PDX_Connection_CPT {
	protected $connection;
	protected $attributes;

	static public function init() {
		static $initialized;

		if(!$initialized)
			add_action('init', array(__CLASS__, 'register_cpt'));

		$initialized = true;
	}

	static public function register_cpt() {
		static $registered;

		$args = array(
			'name' => 'pdx_connection_cpt',
			'labels' => array(
				'name' => __('PDX Connections', 'pdx_builder'),
				'singular_name' => __( 'PDX Connection', 'pdx_builder'),
				'add_new_item' => __('Add New PDX Connection', 'pdx_builder'),
				'edit_item' => __('Edit PDX Connection', 'pdx_builder'),
				'new_item' => __('New PDX Connection', 'pdx_builder'),
				'all_items' => __('All PDX Connections', 'pdx_builder'),
				'view_item' => __('View PDX Connection', 'pdx_builder'),
				'search_items' => __('Search PDX Connections', 'pdx_builder'),
				'not_found' =>  __('No connections found', 'pdx_builder'),
				'not_found_in_trash' => __('No connections found in Trash', 'pdx_builder')),
			'public' => true,
			'has_archive' => true
		);

		if(!$registered)
			register_post_type('pdx_connection_cpt', $args );

		$registered = true;
	}

	public function __construct() {
	}
}