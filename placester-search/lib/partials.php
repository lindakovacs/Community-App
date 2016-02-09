<?php
/**
 * This class contains methods that return plugin data wrapped in HTML.
 * 
 * Each method implements filters that allow the theme developer to modify the returned data contextually.
 *
 * @package PlacesterBlueprint
 * @since 0.0.1
 */
PLS_Partials::init();
class PLS_Partials {
    
    // Links in all the hooks and includes
    public static function init () {
        // All the includes
        include_once('partials/custom-property-details.php');
        include_once('partials/get-listings-ajax.php');
        include_once('partials/get-listings-search-form.php');
        include_once('partials/get-listings.php');

        // This hook allows for listing/property detail pages to be rendered correctly across themes
        add_filter('the_content', array( __CLASS__ ,'custom_property_details_html_filter'), 11);
    }

    // Wrapper for calling get listings directly
    public static function get_listings( $args = array() ) {
        return PLS_Partial_Get_Listings::init($args);
    }
    
    // Wrapper for listings search for content
    public static function get_listings_search_form ($args) {
        return PLS_Partials_Listing_Search_Form::init($args);
    }
    
    // Wrapper for listings list ajax content
    public static function get_listings_list_ajax ($args = '') {
        return PLS_Partials_Get_Listings_Ajax::load($args);       
    }
    
    // Wrapper for property details page content
    public static function custom_property_details_html_filter ($content) {
        return PLS_Partials_Property_Details::init($content);
    } 
}
