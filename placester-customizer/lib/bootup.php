<?php 

PL_Bootup::init();
class PL_Bootup {

    private static $items_that_can_be_created = array(
        'pages' => array(),
        'menus' => array(),
        'posts' => array(),
        'agents' => array(),
        'testimonials' => array(),
        'services' => array(),
        'videos' => array(),
        'settings' => array()
    );

    public static function init () {
        add_action('wp_ajax_add_dummy_data', array( __CLASS__, 'add_dummy_data_ajax') );
    }

    public static function add_dummy_data () {
        // Retrieve default and theme manifests
        $manifest = wp_parse_args( self::parse_manifest_to_array(), self::$items_that_can_be_created );
        extract($manifest);

        // Start creating dummy data here...
        if ( !empty($pages) )  {
            self::create_pages( $pages );
        }
        if ( !empty($menus) ) {
            self::create_menus( $menus );
        }
        if ( !empty($settings['taxonomies']) ) {
            self::create_taxonomies( $settings['taxonomies'] );
        }
        if ( !empty($posts) ) {
            self::create_posts( $posts, 'post', $settings );
        }

        // Add CPTs here
        $all_cpts = array(
            'agent' => $agents,
            'testimonial' => $testimonials,
            'service' => $services,
            'video' => $videos
        );

        // create CPT posts
        foreach ($all_cpts as $post_type => $custom_posts) {
            if (post_type_exists($post_type)) {
                self::create_posts( $custom_posts, $post_type, $settings );
            }
            // delete Hello World post
            wp_delete_post( 1, true );
        }
    }

    public static function add_dummy_data_ajax () {
        self::add_dummy_data();

        echo json_encode(true);
        die();
    }

    private static function create_pages ( $pages ) {
        PL_Pages::create_once( $pages, $force_template = false );
    }

    private static function create_menus ( $menus ) {
        PL_Menus::create( $menus );
    }

    private static function create_taxonomies ( $taxonomies ) {
        PL_Taxonomy_Helper::create( $taxonomies );
    }

    private static function create_posts ( $posts, $post_type, $settings ) {
        PL_Posts::create( $posts, $post_type, $settings );
    }

	private static function parse_manifest_to_array () {
		return json_decode( file_get_contents( self::get_current_theme_manifest_location() ), true );
	}

	private static function get_current_theme_manifest_location () {	
		$template = trailingslashit( get_template_directory() );
		if (file_exists( $template . 'manifest.json' )) {
			return $template . 'manifest.json';
		}
		return trailingslashit( PLACESTER_PLUGIN_URL ) . 'config/default-manifest.json';
	}

}