<?php 

class PL_Menus {

	public static function create( $manifest_menus ) {

		// Check manifest menus against current menus
		foreach ($manifest_menus as $manifest_menu) {
			// create menus
			self::create_new_menu($manifest_menu['name']);
			// add pages
			self::add_pages_to_menu($manifest_menu);
		}

		self::assign_menu_to_theme_location( $manifest_menus );
	}



	public static function create_new_menu ( $menu_name ) {
		// check if $menu exists
		$menu_check = wp_get_nav_menu_object( $menu_name );
		// if menu doesn't already exist, make it
		if ( !empty($menu_check) ) {
			return false;
		}

		// Create the menu
		wp_update_nav_menu_object( 0, array( 'menu-name' => $menu_name) );
	}


	public static function add_pages_to_menu ( $manifest_menu ) {

		// get the menu object
		$the_menu = wp_get_nav_menu_object($manifest_menu['name']);
		$menu_id = (int) $the_menu->term_id;
		$the_menu_pages = wp_get_nav_menu_items($menu_id);
		$existing_menu_pages = array();

		if (is_array($the_menu_pages)) {

			foreach ($the_menu_pages as $page) {
				$existing_menu_pages[] = $page->post_title;
			}

			// add manifest pages to menu obejct
			foreach ($manifest_menu['pages'] as $page) {
				// don't allow duplicates
				if (in_array($page, $existing_menu_pages)) {
					continue;
				}

				$the_page = get_page_by_title($page);

				$args =  array(
						'menu-item-object-id' => $the_page->ID,
						// 'menu-item-parent-id' => 0,
						// 'menu-item-position'  => 2,
						// 'menu-item-object'    => 'page',
						// 'menu-item-type'      => 'post_type',
						'menu-item-title'     => $the_page->post_title,
						'menu-item-classes'   => $the_page->post_title,
						'menu-item-url'       => $the_page->guid,	// should this be a call to get_permalink?
						'menu-item-status'    => 'publish'
				);
				wp_update_nav_menu_item( $menu_id, 0, $args );
			}

		}

	}


	public static function assign_menu_to_theme_location ( $manifest_menus ) {

		// Get Menu Locations
		$locations = array();
		// Get Current Menus
		$all_current_menus = wp_get_nav_menus();

		// Try to respect each manifest menu
		foreach ($manifest_menus as $manifest_menu) {
			// this manifest menu's real ID if it already exists
			$menu = get_term_by( 'name', $manifest_menu['name'], 'nav_menu' );
			// add menu id to location array (this assumes that the menu location exists)
			$locations[$manifest_menu['location']] = $menu->term_id;
		}

		set_theme_mod( 'nav_menu_locations', $locations );

	}

}