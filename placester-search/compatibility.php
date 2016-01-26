<?php
/**
 * This class acts as a buffer between the theme and the plugin. All calls to 
 * functions from the plugin must be done using this class. If new functions 
 * that need to be available to theme developers are added to the plugin, they 
 * must be added to this class.
 *
 * @package PlacesterBlueprint
 * @subpackage Functions
 * @since 0.0.1
 */
class PLS_Plugin_API {

    static public $default_company_details = array(
        'description' => '',
        'logo_url' => '',
        'name' => '',
        'phone' => '',
        'email' => '',
        'location' => array(
            'address' => '',
            'unit' => '',
            'locality' => '',
            'region' => '',
            'postal' => '',
            'neighborhood' => '',
            'country' => '',
            'latitude' => '',
            'longitude' => ''
        )
    );

    /**
     * Verify if calling a plugin function throws any exceptions. If it throws 
     * a timeout exception, set the theme global error flag.
     * 
     * @static
     * @access private
     * @return mixed The result of the execution of the function if the plugin 
     * didn't throw any exceptions, false otherwise.
     * @since 0.0.1
     */
    private static function try_call_func () {
        // Don't proceed if there's an issue with the plugin...
        if (pls_has_plugin_error()) {
            return false;
        }
            
        $parameters = func_get_args();

        // Assume first function arg is a PHP callback array...
        $function_name = array_shift($parameters);

        // Assume the second function arg is a value that will be returned if the call fails...
        $if_fail = array_shift($parameters);

        try {
            // Call the function with its parameters...
            $return = call_user_func_array($function_name, $parameters);
        } 
        catch (Exception $e) {
            // Assumes an exception with a private message is a timeout
            if ( !isset($e->message) ) {
                pls_has_plugin_error('timeout');
            }
            
            // If invalid, set to value specified by 'if_fail'
            $return = $if_fail;
        }
        
        return $return;
    }

    /*
     * Taxonomies
     */

    public static function get_term ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_term"), false, $params );
    }

    public static function get_permalink_templates ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_permalink_templates"), false, $params );
    }

    public static function get_polygon_listings ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_listings_polygon_name"), array("listings" => array()), $params );
    }

    public static function get_polygon_links ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_polygon_links"), array(), $params );
    }

    public static function get_taxonomies_by_type ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_polygons_by_type"), array(), $params );
    }

    public static function get_taxonomies_by_slug ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_polygons_by_slug"), array(), $params );
    }

    public static function get_polygon_detail ($params = array()) {
        return self::try_call_func( array("PL_Taxonomy_Helper","get_polygon_detail"), array(), $params );
    }

    /*
     * Membership + People Funcs
     */

    public static function placester_lead_control_panel ($args) {
        return self::try_call_func( array("PL_Membership", "placester_lead_control_panel"), false, $args );
    }

    public static function get_client_area_url () {
        return self::try_call_func( array("PL_Membership", "get_client_area_url"), false, false );
    }

    public static function get_person_details () {
        return self::try_call_func( array("PL_People_Helper", "person_details"), array(), array() );
    }

    public static function update_person ($person_details) {
        return self::try_call_func( array("PL_People_Helper", "update_person"), false, $person_details );
    }

    public static function create_person ($person_details) {
        return self::try_call_func( array("PL_People_Helper", "add_person"), false, $person_details );
    }

    public static function merge_bcc_forwarding_addresses_for_sending ($headers) {
        return self::try_call_func( array("PL_Lead_Capture_Helper", "merge_bcc_forwarding_addresses_for_sending"), false, $headers);
    }

    /*
     * Favorite Listing and My Search Funcs
     */

    public static function get_favorite_properties () {
        return self::try_call_func( array("PL_Favorite_Listings", "get_favorite_ids" /* renamed to "get_favorite_properties" in next release */), false, array() );
    }

    public static function placester_favorite_link_toggle ($args) {
        return self::try_call_func( array("PL_Favorite_Listings", "placester_favorite_link_toggle"), false, $args );
    }

    public static function get_favorite_search ($hash_id) {
        return self::try_call_func( array("PL_User_Saved_Search", "get_favorite_search"), false, $hash_id );
    }

    public static function get_favorite_searches () {
        return self::try_call_func( array("PL_User_Saved_Search", "get_favorite_searches"), false, array() );
    }

    public static function placester_search_link_toggle ($args) {
        return self::try_call_func( array("PL_User_Saved_Search", "placester_search_link_toggle"), false, $args );
    }

    public static function placester_favorite_search_list ($args) {
        return self::try_call_func( array("PL_User_Saved_Search", "placester_favorite_search_list"), false, $args );
    }

    /*
     * Permalink Search Funcs
     */
    
    public static function save_search ($search_id, $search_filters) {
        return self::try_call_func( array("PL_Permalink_Search", "save_search"), false, $search_id, $search_filters );
    }

    public static function get_saved_search_filters ($search_id) {
        return self::try_call_func( array("PL_Permalink_Search", "get_saved_search_filters"), false, $search_id );
    }

    /*
     * Miscellaneous
     */

    public static function get_api_key () {
        $api_key = null;

        if (function_exists('placester_get_api_key')) {
            $api_key = placester_get_api_key();
            
            if (!$api_key) { 
                $api_key = false; 
            }
        }

        return $api_key;
    }

    public static function get_schools ($params = array()) {
        return self::try_call_func( array("PL_Education_Helper","get_schools"), array(), $params );
    }

    public static function get_walkscore ($params = array()) {
        return self::try_call_func( array("PL_Walkscore","get_score"), array(), $params );
    }

    public static function get_translations () {
        return self::try_call_func( array("PL_Custom_Attribute_Helper", "get_translations"), array() );
    }

    public static function get_user_details ($args = array(), $api_key = null) {
        return self::try_call_func( array("PL_Helper_User", "whoami"), null,  $args, $api_key);
    }

    /**
     * Return public-safe info for company
     * @return array Public company info. Any fields not populated by user will be an empty string.
     */
    public static function get_company_details () {
        $details = self::get_user_details();
        $company_info = self::$default_company_details;

        if (is_array($details)) {
            $r = array_replace_recursive($company_info, $details);
            $company_info = array_intersect_key($r, $company_info);
        }

        return $company_info;
    }

    public static function get_default_location () {
        return self::try_call_func( array("PL_Option_Helper", "get_default_location"), array('lat' => 42.3596681, 'lng' => -71.0599325));
    }

    public static function mls_message ($context) {
        return self::try_call_func( array("PL_Compliance", "mls_message"), false, $context );
    }

    public static function log_snippet_js ($event, $attributes) {
        return self::try_call_func( array("PL_Analytics", "log_snippet_js"), false, $event, $attributes );
    }

    /*
     * Listings
     */

    public static function get_listing_in_loop () {
        return self::try_call_func( array("PL_Listing_Helper", "get_listing_in_loop") );
    }

    public static function get_listing_aggregates ($keys) {
        return self::try_call_func( array("PL_Listing_Helper", "basic_aggregates"), array(), $keys );
    }
    
    public static function get_property_url ($id = false, $listing = array()) {
        // Make sure $id is set...
        if (!$id) { return false; }

        // Test the function for any exceptions
        return self::try_call_func( array("PL_Pages", "get_url"), false, $id, $listing );
    }

    /**
     * Returns a list of type values (property types, by default) valid for current site for use in search dropdowns
     * 
     * @static
     * @return array the options available for the given type key
     */
    public static function get_type_list ($return_only = false, $allow_globals = true, $type_key = 'property_type') {
        return self::try_call_func( array("PL_Listing_Helper","types_for_options"), array(), $return_only, $allow_globals, $type_key );
    }

	/**
     * Gets an object containing the list of cities, zip codes and states of 
     * the available properties.
     *
     * The object looks like this: 
     * <code>
     * object(stdClass)#59 (3) {
     *   ["city"]=> array(8) { [0]=> string(7) "City 1", ... }
     *   ["zip"]=> array(8) { [0]=> string(7) "Zip Code 1", ... }
     *   ["state"]=> array(6) { [0]=> string(2) "State Code 1", ... }
     * }
     * </code>
     * 
     * @return mixed The object containing the data if the plugin is active and 
     * has a API key, FALSE otherwise.
     * @since 0.0.1
     */
    public static function get_location_list ($return_only = false) {
        return self::try_call_func( array("PL_Listing_Helper","locations_for_options"), array(), $return_only );
    }

    public static function get_location_list_polygons ($return_only = false) {
        return self::try_call_func( array("PL_Listing_Helper","polygon_locations"), array(), $return_only );
    }

    public static function get_locations_counts ($params = array()) {
        return self::try_call_func( array("PL_Listing_Helper","counts_for_locations"), array(), $params );
    }

    /**
     * Prints a standalone list of properties.
     *
     * @return list of property details
     * @since 0.0.1
     */
    public static $listing_data_requested; // flag used to trigger compliance disclaimer

    public static function get_listings ($args, $global_filters = true, $caching_on = false) {
        self::$listing_data_requested = true;
        return self::try_call_func(array("PL_Listing_Helper", "results"), array(), $args, $global_filters);
    }

    public static function get_listing_details ($args) {
        self::$listing_data_requested = true;
        return self::try_call_func(array("PL_Listing_Helper", "details"), array(), $args);
    }

    public static function get_type_values ($type) {
        // Define the supported types
        $supported_types = array( 
            'property' => array(
                'apartment' => 'Apartment',
                'penthouse' => 'Penthouse',
                'townhouse' => 'Townhouse',
                'brownstone' => 'Brownstone',
                'Single Family Home' => 'Single Family Home',
                'Multi Family Home' => 'Multi Family Home',
                'flat' => 'Flat',
                'loft' => 'Loft',
                'cottage' => 'Cottage',
                'mansion' => 'Mansion',
                'ranch' => 'Ranch',
                'duplex' => 'Duplex',
                'condo' => 'Condominium'
            ), 
            'listing' => array(
                // 'storage' => 'Storage',
                'housing' => 'Housing',
                'parking' => 'Parking',
                'sublet' => 'Sublet',
                'vacation' => 'Vacation',
                'land' => 'Land',
                // 'other' => 'Other',
            ), 
            'zoning' => array(
                'residential' => 'Residential',
                'commercial' => 'Commercial',
            ), 
            'purchase' => array(
                'rental' => 'Rent',
                'sale' => 'Buy',
            )
        );

        // If not a valid type, return empty handed
        if ( empty($type) || !array_key_exists($type, $supported_types) ) { return; }

        return $supported_types[$type];
    }

    public static function resize_image($image_args) {
    	return self::try_call_func(array("PL_Dragonfly", "resize"), $image_args['old_image'], $image_args);
    }
}
