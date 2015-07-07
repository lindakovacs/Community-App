<?php

class PDX_Search_CPT {
	protected $connection;
	protected $property;

	public function __construct() {
		add_action('init', array($this, 'register_post_type'));
	}

	public function register_post_type() {

		// custom post type to hold a customized shortcode
		$args = array(
			'name' => 'search_page',
			'labels' => array(
				'name' => __('Search Pages', 'placester'),
				'singular_name' => __( 'Search Page', 'placester' ),
				'add_new_item' => __('Add New Search Page', 'placester'),
				'edit_item' => __('Edit Search Page', 'placester'),
				'new_item' => __('New Search Page', 'placester'),
				'all_items' => __('All Search Pages', 'placester'),
				'view_item' => __('View Search Page', 'placester'),
				'search_items' => __('Search Search Pages', 'placester'),
				'not_found' =>  __('No search pages found', 'placester'),
				'not_found_in_trash' => __('No search pages found in Trash', 'placester')),
			'public' => true,
			'has_archive' => true
		);

		register_post_type('search_page', $args );
	}
}