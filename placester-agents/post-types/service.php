<?php
// Add Custom Post Type - Services
add_action('init', 'service_register');

function service_register() {
	$labels = array(
			'name' => _x('Services', 'post type general name'),
			'singular_name' => _x('Services', 'post type singular name'),
			'add_new' => _x('Add New', 'Service'),
			'add_new_item' => __('Add New Service'),
			'edit_item' => __('Edit Service'),
			'new_item' => __('New Service'),
			'view_item' => __('View Service'),
			'search_items' => __('Search Services'),
			'not_found' =>  __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
	);

	$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'has_archive' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','thumbnail')
	);

	register_post_type( 'service' , $args );
}

