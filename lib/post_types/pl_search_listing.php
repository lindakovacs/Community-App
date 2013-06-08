<?php
/**
 * Post type/Shortcode to generate a list of listings
 *
 */
class PL_Search_Listing_CPT extends PL_Post_Base {

	protected static $post_type = 'pl_search_listings';
	
	protected static $shortcode = 'search_listings';
	
	protected static $filters = array(
			'listing_types'		=> array( 'type' => 'select', 'label' => 'Listing Type', 'default' => 'false' ),
			'zoning_types'		=> array( 'type' => 'select', 'label' => 'Width', 'default' => null ),
			'purchase_types'	=> array( 'type' => 'select', 'label' => 'Width', 'default' => null ),
			'location'			=> array( 'type' => 'subgrp', 'label' => 'Width', 'subgrp' => '', 'default' => array() ),
			'metadata'			=> array( 'type' => 'subgrp', 'label' => 'Width', 'subgrp' => '', 'default' => array() ),
	);




	public function register_post_type() {
		$args = array(
				'labels' => array(
						'name' => __( 'Search Listings', 'pls' ),
						'singular_name' => __( 'search_listing', 'pls' ),
						'add_new_item' => __('Add New Search Listing', 'pls'),
						'edit_item' => __('Edit Search Listing', 'pls'),
						'new_item' => __('New Search Listing', 'pls'),
						'all_items' => __('All Search Listings', 'pls'),
						'view_item' => __('View Search Listings', 'pls'),
						'search_items' => __('Search Search Listings', 'pls'),
						'not_found' => __('No search listings found', 'pls'),
						'not_found_in_trash' => __('No search listings found in Trash', 'pls')),
				'menu_icon' => trailingslashit(PL_IMG_URL) . 'featured.png',
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => false,
				'query_var' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array('title', 'editor'),
				'taxonomies' => array('category', 'post_tag')
		);

		register_post_type('pl_search_listing', $args );
	}
}

new PL_Search_Listing_CPT();