<?php

if(!get_option('pls_enable_community_pages', false))
	return;

PL_Community_Pages::init();
class PL_Community_Pages {
	private static $community_post_type = 'community';
	private static $neighborhood_community_meta_key = 'community_page';

	public static function init() {
		add_action( 'init', array(__CLASS__, 'create_community_page_cpt') );
		add_action( 'init', array( __CLASS__, 'create_neighborhood_picker' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_neighborhood_meta_box') );
		add_action( 'save_post', array( __CLASS__, 'save_community_page' ) );
		
		add_action( 'edited_neighborhood', array( __CLASS__, 'save_neighborhood' ), 10, 2 );
		add_action( 'created_neighborhood', array( __CLASS__, 'save_neighborhood' ), 10, 2 );
	}

	public static function add_neighborhood_meta_box() {
		add_meta_box(
			'neighborhood_picker',
			'Neighborhood Picker', 
			array( __CLASS__, 'neighborhood_picker_box' ),
			self::$community_post_type
		);
	}

	public static function save_community_page( $post_id ) {
		// no autosaves
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		
		// community nonce there?
		if( isset( $_POST['community_nonce'] ) && ! wp_verify_nonce($_POST['community_nonce'], 'save_community_page' ) ) {
			return;
		}

		// no regular users too
		if( ! current_user_can( 'manage_options' ) ) {
			return; 
		}
		if( isset( $_POST['page_header_title'] ) ) {
			update_post_meta( $post_id, 'page_header_title', $_POST['page_header_title'] );
		}

		// Get old meta and clear neighborhoods
		$old_neighborhoods = get_post_meta( $post_id, 'community_neighborhoods', true );
		if( empty( $old_neighborhoods ) || ! is_array( $old_neighborhoods ) ) {
			$old_neighborhoods = array();
		}

		if( ! empty( $_POST['neighborhoods'] ) ) {
			$neighborhoods = $_POST['neighborhoods'];
			update_post_meta( $post_id, 'community_neighborhoods', $neighborhoods );

			foreach( $neighborhoods as $neighborhood_id ) {
				update_tax_meta( $neighborhood_id, self::$neighborhood_community_meta_key, $post_id );
				if( isset( $old_neighborhoods[$neighborhood_id] ) )
					unset( $old_neighborhoods[$neighborhood_id] );
			}
		}

		// Remove old references
		foreach( $old_neighborhoods as $old_neighborhood ) {
			$old_neighborhood_option = get_option( 'tax_meta_' . $old_neighborhood );
			if (isset($old_neighborhood_option[self::$neighborhood_community_meta_key])){
				unset($old_neighborhood_option[self::$neighborhood_community_meta_key]);
			}
			update_option( 'tax_meta_' . $old_neighborhood, $old_neighborhood_option );
		}
	}

	public static function save_neighborhood( $term_id ) {
		if( empty( $_POST['community_page'] ) || $_POST['community_page'] === 'false' ) {
			return; 
		}

		// verify that a community is selected
		$community_page_id = (int) $_POST['community_page'];
		if( empty( $community_page_id ) )
			return;

		// get old page, if any
		$old_community_page_id = Tax_Meta_Class::get_tax_meta($term_id, 'community_page' );
		if( ! empty( $old_community_page_id ) ) {
			// get neighborhoods from the page, if any
			$neighborhood_ids = get_post_meta( $old_community_page_id, 'community_neighborhoods', true );
			if( ! empty( $neighborhood_ids ) && is_array( $neighborhood_ids ) ) {
				unset( $neighborhood_ids[$term_id] );
			}
		}

		// update 'em
		$neighborhood_ids = get_post_meta( $community_page_id, 'community_neighborhoods', true );
		if( ! empty( $neighborhood_ids ) && is_array( $neighborhood_ids ) ) {
			if( ! in_array( $term_id, array_values( $neighborhood_ids ) ) ) {
				$neighborhood_ids[] = $term_id;
			}
		} else {
			$neighborhood_ids = array();
			$neighborhood_ids[] = $term_id;
		}

		update_post_meta( $community_page_id, 'community_neighborhoods', $neighborhood_ids );
	}

	public static function neighborhood_picker_box( $post ) {
		$page_header_title = get_post_meta( $post->ID, 'page_header_title', true );

		if( empty( $page_header_title ) ) $page_header_title = '';
		echo "<p>Page Header Title: <input type='text' name='page_header_title' value='$page_header_title' style='width: 150px'/></p>";

		$page_neighborhoods = get_post_meta( $post->ID, 'community_neighborhoods', true );
		if( empty( $page_neighborhoods ) ) {
			$page_neighborhoods = array();
		} 

		$neighborhoods = get_terms( 'neighborhood', array( 'hide_empty' => false ) );
		foreach( $neighborhoods as $neighborhood ) {
 			printf("<input type='checkbox' name='neighborhoods[]' id=n-".$neighborhood->term_id." value='%d' %s />", 
		 			$neighborhood->term_id, 
		 			checked(in_array($neighborhood->term_id, array_values( $page_neighborhoods ) ), true, false) );
			echo "<label for=n-".$neighborhood->term_id.">".$neighborhood->name."</label>";
			echo '<br />';
		}

		wp_nonce_field('save_community_page', 'community_nonce');
	}

	public static function create_neighborhood_picker( ) {
		$config = array('id' => 'community_meta_box', 
			'title' => 'Community Meta',
			'pages' => array('neighborhood'),
			'context' => 'normal',
			'priority' => 'low',
			'fields' => array(),
			'local_images' => false,
			'use_with_theme' => false );

		$my_meta = new Tax_Meta_Class($config);
		$my_meta->addPosts( self::$neighborhood_community_meta_key, 
			array( 'post_type' => self::$community_post_type, 'std' => 'Select Page' ),
			array( 'name' => 'Community' ) );
		$my_meta->Finish();
	}
	
	public static function create_community_page_cpt() {
		$args = array(
			'labels' => array(
				'name' => __( 'Communities', 'pls' ),
				'singular_name' => __( self::$community_post_type, 'pls' ),
				'add_new_item' => __('Add New Community', 'pls'),
				'edit_item' => __('Edit Community', 'pls'),
				'new_item' => __('New Community', 'pls'),
				'all_items' => __('All Communities', 'pls'),
				'view_item' => __('View Communities', 'pls'),
				'search_items' => __('Search Communities', 'pls'),
				'not_found' =>  __('No Community found', 'pls'),
				'not_found_in_trash' => __('No Community found in Trash', 'pls')),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title', 'editor', 'thumbnail'),
			'taxonomies' => array('neighborhood')
		);

		register_post_type(self::$community_post_type, $args );
	}
}
