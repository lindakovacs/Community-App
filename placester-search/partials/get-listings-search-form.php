<?php

class PLS_Partials_Listing_Search_Form {

	/**
     * Returns a form that can be used to search for listings.
     * 
     * The defaults are as follows:
     *     'ajax' - Default is false. Wether the resulting form should use ajax 
     *          or not. If ajax is set to true, then for the form to work, the 
     *          results container should be defined on the page. 
     *          {@link PLS_Partials::get_listings_list_ajax()} should be used.
     *     'context' - An execution context for the function. Used when the 
     *          filters are created.
     *     'context_var' - Any variable that needs to be passed to the filters 
     *          when function is executed.
     * Defines the following hooks.
     *      pls_listings_search_form_bedrooms_array[_context] - Filters the 
     *          array with the data used to generate the select.
     *      pls_listings_search_form_bathrooms_array[_context]
     *      pls_listings_search_form_available_on_array[_context]
     *      pls_listings_search_form_cities_array[_context]
     *      pls_listings_search_form_min_price_array[_context]
     *      pls_listings_search_form_max_price_array[_context]
     *      
     *      pls_listings_search_form_bedrooms_attributes[_context] - Filters 
     *          the attribute array for the select. If extra attributes need to 
     *          be added to the select element, they should be provided in 
     *          a array( $attribute_key => $attribute_value ) form.
     *      pls_listings_search_form_bathrooms_attributes[_context]
     *      pls_listings_search_form_available_on_attributes[_context]
     *      pls_listings_search_form_cities_attributes[_context]
     *      pls_listings_search_form_min_price_attributes[_context]
     *      pls_listings_search_form_max_price_attributes[_context]
     *      
     *      pls_listings_search_form_bedrooms_html[_context] - Filters the html 
     *          for this option. Can be used to add extra containers.
     *      pls_listings_search_form_bathrooms_html[_context]
     *      pls_listings_search_form_available_on_html[_context]
     *      pls_listings_search_form_cities_html[_context]
     *      pls_listings_search_form_min_price_html[_context]
     *      pls_listings_search_form_max_price_html[_context]
     *      
     *      pls_listings_search_form_submit[_context] - Filters the form submit 
     *          button.
     *
     *      pls_listings_search_form_inner[_context] - Filters the form inner html.
     *      pls_listings_search_form_outer[_context] - Filters the form html.
     *
     * @static
     * @param array $args Optional. Overrides defaults.
     * @return string The html for the listings search form.
     * @since 0.0.1
     */
  public static function init ($args = '') {
    // Define the default argument array
    $defaults = array(
        'ajax' => false,
        'class' => 'pls_search_form_listings',
        'context' => '',
        'form_data' => (object) array(
        	'hidden_field' => '',
        	'action' => ''
        ),
        'theme_option_id' => '',
        'context_var' => null,
        'bedrooms' => 1,
        'min_beds' => 1,
        'max_beds' => 1,
        'bathrooms' => 1,
        'min_baths' => 1,
        'max_baths' => 1,
        'price' => 1,
        'half_baths' => 1,
        'property_type' => 1,
        'listing_types'=> 1,
        'zoning_types' => 1,
        'purchase_types' => 1,
        'available_on' => 1,
        'cities' => 1,
        'multi_cities' => 0,
        'states' => 1,
        'multi_states' => 0,
        'zips' => 1,
        'neighborhood' => 1,
        'multi_neighborhoods' => 0,
        'county' => 1,
        'min_price' => 1,
        'max_price' => 1,
        'min_price_rental' => 1,
        'max_price_rental' => 1,
        'min_price_sales' => 1,
        'max_price_sales' => 1,
        'neighborhood_polygons' => 0,
        'neighborhood_polygons_type' => false,
        'min_sqft' => 1,
        'max_sqft' => 1,
        'include_submit' => true,
        'pls_empty_value' => array()
    );

    $args = wp_parse_args($args, $defaults);

    $cache_id = $args;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $cache_id['$_POST'] = $_POST;
    }

    $cache = new PLS_Cache('Search Form');
    if ($result = $cache->get($cache_id)) {
      return $result;
    }

    $form_options = array();

    // Set Form Defaults for params onload, set in Theme Options
    $form_options['location']['locality'] = pls_get_option('form_default_options_locality');
    $form_options['location']['region'] = pls_get_option('form_default_options_region');
    $form_options['location']['postal'] = pls_get_option('form_default_options_postal');
    $form_options['location']['neighborhood'] = pls_get_option('form_default_options_neighborhood');
    $form_options['location']['county'] = pls_get_option('form_default_options_county');
    $form_options['property_type'] = pls_get_option('form_default_options_property_type');
    
    $_POST = wp_parse_args($_POST, $form_options);
           
    //respect user settings, unless they are all empty. 
    $user_search_params = pls_get_option($args['theme_option_id']);
    
    if (isset($user_search_params['hide_all']) && $user_search_params['hide_all'] == 1) {
      return '';
    }

    $args = wp_parse_args( $args, $user_search_params);    
    
    /** Extract the arguments after they merged with the defaults. */
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    // Set the default empty values for the all the form elements.
    // Dev can change defaults via a filter in process defaults.
    $pls_empty_value = self::process_default_value_array($pls_empty_value, $context);
    
    /**
     * Elements options arrays. Used to generate the HTML.
     */

    /** Prepend the default empty valued element. */
    $user_beds_start = pls_get_option('pls-option-bed-min');
    $user_beds_end = pls_get_option('pls-option-bed-max');
    if (is_numeric($user_beds_start) && is_numeric($user_beds_end) ) {
      $beds_range = range( $user_beds_start, $user_beds_end );
        $form_options['bedrooms'] = array( 'pls_empty_value' => $pls_empty_value['bedrooms'] ) + array_combine( $beds_range, $beds_range );
    } else {
        $form_options['bedrooms'] = array( 'pls_empty_value' => $pls_empty_value['bedrooms'] ) + range( 0, 16 );    
    }

    /** Prepend the default empty valued element. */
    $user_baths_start = pls_get_option('pls-option-bath-min');
    $user_baths_end = pls_get_option('pls-option-bath-max');
    if (is_numeric($user_baths_start) && is_numeric($user_baths_end) ) {
      $baths_range =  range( $user_baths_start, $user_baths_end );
        $form_options['bathrooms'] = array( 'pls_empty_value' => $pls_empty_value['bathrooms'] ) + array_combine( $baths_range, $baths_range );
    } else {
        $form_options['bathrooms'] = array( 'pls_empty_value' => $pls_empty_value['bathrooms'] ) + range( 0, 10 );
    }

    /** Prepend the default empty valued element. */
    $user_half_baths_start = pls_get_option('pls-option-half-bath-min');
    $user_half_baths_end = pls_get_option('pls-option-half-bath-max');
    if (is_numeric($user_half_baths_start) && is_numeric($user_half_baths_end) ) {
      $half_bath_range = range( $user_half_baths_start, $user_half_baths_end );
        $form_options['half_baths'] = array( 'pls_empty_value' => $pls_empty_value['half_baths'] ) + array_combine( $half_bath_range, $half_bath_range );
    } else {
        $form_options['half_baths'] = array( 'pls_empty_value' => $pls_empty_value['half_baths'] ) + range( 0, 10 );
    } 

    /** Generate an array with the next 12 months. */
    $current_month = (int) date('m');
    for ( $i = $current_month; $i < $current_month + 12; $i++ ) {
        $form_options['available_on'][date( 'd-m-Y', mktime( 0, 0, 0, $i, 1 ) )] = date( 'F Y', mktime( 0, 0, 0, $i, 1 ) );
    }
    
    /** Get the property type options */
    $get_type_response = PLS_Plugin_API::get_type_list();
    // error_log("GET_TYPE_RESPONSE\n" . serialize($get_type_response) . "\n");
		if ( empty($get_type_response) ) {
			$form_options['property_type'] = array( 'pls_empty_value' => $pls_empty_value['property_type'] );
		} else {
      // if API serves up 'false' key in the array, remove it, because we're going to add one.
      if (isset($get_type_response['false'])) {
        unset($get_type_response['false']);
      }
			$form_options['property_type'] = array_merge( array('pls_empty_value' => $pls_empty_value['property_type']), $get_type_response );
		}

    /** Get the listing type options. */
    $form_options['listing_types'] = array( 'pls_empty_value' => $pls_empty_value['listing_types'] ) + PLS_Plugin_API::get_type_values( 'listing' );

    /** Get the zoning type options. */
    $form_options['zoning_types'] = array( 'pls_empty_value' => $pls_empty_value['zoning_types']) + PLS_Plugin_API::get_type_values( 'zoning' );
		// removed "All" - it's not giving all listings. jquery needs to change to not include "[]"s
    // $form_options['zoning_types'] = PLS_Plugin_API::get_type_values( 'zoning' ); // for Multiple, not for single, see below

    /** Get the purchase type options. */
    $get_type_response = PLS_Plugin_API::get_type_list(false, true, 'purchase_types');
    // error_log("GET_TYPE_RESPONSE\n" . serialize($get_type_response) . "\n");
	// if API serves up 'false' key in the array, remove it, because we're going to add one.
	if (isset($get_type_response['false'])) {
		unset($get_type_response['false']);
	}
	$form_options['purchase_types'] = array_merge( array('pls_empty_value' => $pls_empty_value['purchase_types']), $get_type_response );

		// removed "All" - it's not giving all listings. jquery needs to change to not include "[]"s
		// $form_options['purchase_types'] = PLS_Plugin_API::get_type_values( 'purchase' );
		
    /** Prepend the default empty valued element. */
    $form_options['available_on'] = array( 'pls_empty_value' => $pls_empty_value['available_on']) + $form_options['available_on'];

    /** Prepend the default empty valued element. */
    
    $locations = PLS_Plugin_API::get_location_list();
    $neighborhood_polygons_options = PLS_Plugin_API::get_location_list_polygons($neighborhood_polygons_type);

    if (empty($locations['locality'])) {
        $form_options['cities'] = array('pls_empty_value' => $pls_empty_value['cities']);
    } else {
        unset($locations['locality']['false']);
        sort($locations['locality']);
        $form_options['cities'] = array('pls_empty_value' => $pls_empty_value['cities']) + $locations['locality'];
    }

    if (empty($locations['region'])) {
        $form_options['states'] = array('pls_empty_value' => $pls_empty_value['states']);
    } else {
        unset($locations['region']['false']);
        sort($locations['region']);
        $form_options['states'] = array('pls_empty_value' => $pls_empty_value['states']) + $locations['region'];  
    }

    if (empty($locations['postal'])) {
        $form_options['zips'] = array('pls_empty_value' => $pls_empty_value['zips']); 
    } else {
        unset($locations['postal']['false']);
        sort($locations['postal']);
        $form_options['zips'] = array('pls_empty_value' => $pls_empty_value['zips']) + $locations['postal'];
    }

    if (empty($locations['neighborhood'])) {
        $form_options['neighborhood'] = array('pls_empty_value' => $pls_empty_value['neighborhoods']); 
    } else {
        unset($locations['neighborhood']['false']);
        sort($locations['neighborhood']);
        $form_options['neighborhood'] = array('pls_empty_value' => $pls_empty_value['neighborhoods']) + $locations['neighborhood'];
    }
    
    if (empty($locations['county'])) {
        $form_options['county'] = array('pls_empty_value' => $pls_empty_value['county']); 
    } else {
        unset($locations['county']['false']);
        sort($locations['county']);
        $form_options['county'] = array('pls_empty_value' => $pls_empty_value['county']) + $locations['county'];
    }

    if (empty($neighborhood_polygons_options)) {
        $form_options['neighborhood_polygons'] = array('pls_empty_value' => $pls_empty_value['neighborhood_polygons']);  
    } else {
        unset($neighborhood_polygons_options['false']);
        sort($neighborhood_polygons_options);
        $form_options['neighborhood_polygons'] = array('pls_empty_value' => $pls_empty_value['neighborhood_polygons']) + $neighborhood_polygons_options;
    }
    
    // Min/Max Sqft 
    /** Define the minimum price options array. */
    $form_options['min_sqft'] = array(
          'pls_empty_value' => $pls_empty_value['min_sqft'],
          '200' => '200',
          '400' => '400',
          '600' => '600',
          '800' => '800',
          '1000' => '1,000',
          '1200' => '1,200',
          '1400' => '1,400',
          '1600' => '1,600',
          '1800' => '1,800',
          '2000' => '2,000',
          '2200' => '2,200',
          '2400' => '2,400',
          '2600' => '2,600',
          '2800' => '2,800',
          '3000' => '3,000',
          '3500' => '3,500',
          '4000' => '4,000',
          '4500' => '4,500',
          '5000' => '5,000',
          '6000' => '6,000',
          '7000' => '7,000',
          '8000' => '8,000'
    );

    $user_start_sqft = pls_get_option('pls-option-sqft-min') ? pls_get_option('pls-option-sqft-min') : 0;
    $user_end_sqft = pls_get_option('pls-option-sqft-max');
    $user_inc_sqft = pls_get_option('pls-option-sqft-inc');

    if (is_numeric($user_start_sqft) && is_numeric($user_end_sqft) && is_numeric($user_inc_sqft)) {
      
        // Handle when increment is larger than the range from start to end
        if ($user_inc_sqft > ($user_end_sqft - $user_start_sqft) ) {
          $user_inc_sqft = ($user_end_sqft - $user_start_sqft);
        }
        
        $range = range($user_start_sqft, $user_end_sqft, $user_inc_sqft);
        $form_options['min_sqft'] = array();
        foreach ($range as $sqft_value) {
            $form_options['min_sqft'][$sqft_value] = PLS_Format::number($sqft_value, array('abbreviate' => false));
        }
    }
    /** Set the maximum price options array. */
    $form_options['max_sqft'] = $form_options['min_sqft'];
    /* max_sqft default needs to be set too */
    $form_options['max_sqft']['pls_empty_value'] = __( $pls_empty_value['max_sqft'], pls_get_textdomain() );
    
    // Price Ranges
    /** Define the minimum price options array. */
    $form_options['min_price'] = array(
          'pls_empty_value' => __( $pls_empty_value['min_price'], pls_get_textdomain() ),
          '0' => '$0',
          '400' => '$400',
          '500' => '$500',
          '2000' => '$2,000',
          '3000' => '$3,000',
          '4000' => '$4,000',
          '5000' => '$5,000',
          '50000' => '$50,000',
          '100000' => '$100,000',
          '200000' => '$200,000',
          '350000' => '$350,000',
          '400000' => '$400,000',
          '450000' => '$450,000',
          '500000' => '$500,000',
          '600000' => '$600,000',
          '700000' => '$700,000',
          '800000' => '$800,000',
          '900000' => '$900,000',
          '1000000' => '$1,000,000'
    );

    /* Set the maximum price options array + its default */
    $form_options['max_price'] = $form_options['min_price'];
    $form_options['max_price']['pls_empty_value'] = __( $pls_empty_value['max_price'], pls_get_textdomain() );

    $user_price_start = pls_get_option('pls-option-price-min') ? pls_get_option('pls-option-price-min') : 0;
    $user_price_end = pls_get_option('pls-option-price-max');
    $user_price_inc = pls_get_option('pls-option-price-inc');

    if (is_numeric($user_price_start) && is_numeric($user_price_end) && is_numeric($user_price_inc)) {
        
        $range = range($user_price_start, $user_price_end, $user_price_inc);    
        
        // Create empty arrays
        $form_options['min_price'] = array();
        $form_options['max_price'] = array();
        
        // set empty values
        $form_options['min_price']['pls_empty_value'] = __( $pls_empty_value['min_price'], pls_get_textdomain() );
        $form_options['max_price']['pls_empty_value'] = __( $pls_empty_value['max_price'], pls_get_textdomain() );

        foreach ($range as $price_value) {
            $form_options['min_price'][$price_value] = PLS_Format::number($price_value, array('abbreviate' => false));
            $form_options['max_price'][$price_value] = PLS_Format::number($price_value, array('abbreviate' => false));
        }
    }

    // Price for Rentals 
    /** Define the minimum price options array. */
    $form_options['min_price_rental'] = array(
          'pls_empty_value' => __( $pls_empty_value['min_price_rental'], pls_get_textdomain() ),
          '200' => '$200',
          '400' => '$400',
          '600' => '$600',
          '800' => '$800',
          '1000' => '$1,000',
          '1100' => '$1,100',
          '1200' => '$1,200',
          '1300' => '$1,300',
          '1400' => '$1,400',
          '1500' => '$1,500',
          '1600' => '$1,600',
          '1700' => '$1,700',
          '1800' => '$1,800',
          '1900' => '$1,900',
          '2000' => '$2,000',
          '2100' => '$2,100',
          '2200' => '$2,200',
          '2300' => '$2,300',
          '2400' => '$2,400',
          '2500' => '$2,500',
          '2600' => '$2,600',
          '2700' => '$2,700',
          '2800' => '$2,800',
          '2900' => '$2,900',
          '3000' => '$3,000',
          '3500' => '$3,500',
          '4000' => '$4,000',
          '4500' => '$4,500',
          '5000' => '$5,000',
          '6000' => '$6,000',
          '7000' => '$7,000',
          '8000' => '$8,000'
    );

    /* Set the maximum price rental options array + its default */
    $form_options['max_price_rental'] = $form_options['min_price_rental'];
    $form_options['max_price_rental']['pls_empty_value'] = __( $pls_empty_value['max_price_rental'], pls_get_textdomain() );

    $user_price_start_rental = pls_get_option('pls-option-rental-price-min') ? pls_get_option('pls-option-rental-price-min') : 0;
    $user_price_end_rental = pls_get_option('pls-option-rental-price-max');
    $user_price_inc_rental = pls_get_option('pls-option-rental-price-inc');

    if (is_numeric($user_price_start_rental) && is_numeric($user_price_end_rental) && is_numeric($user_price_inc_rental)) {
      
        // Handle when increment is larger than the range from start to end
        if ($user_price_inc_rental > ($user_price_end_rental - $user_price_start_rental) ) {
          $user_price_inc_rental = ($user_price_end_rental - $user_price_start_rental);
        }
        
        $range = range($user_price_start_rental, $user_price_end_rental, $user_price_inc_rental);    
        
        // Create empty arrays
        $form_options['min_price_rental'] = array();
        $form_options['max_price_rental'] = array();
        
        // set empty values
        $form_options['min_price_rental']['pls_empty_value'] = __( $pls_empty_value['min_price_rental'], pls_get_textdomain() );
        $form_options['max_price_rental']['pls_empty_value'] = __( $pls_empty_value['max_price_rental'], pls_get_textdomain() );

        foreach ($range as $price_value) {
            $form_options['min_price_rental'][$price_value] = PLS_Format::number($price_value, array('abbreviate' => false));
            $form_options['max_price_rental'][$price_value] = PLS_Format::number($price_value, array('abbreviate' => false));
        }
    }

    // Price for Sales
    /** Define the minimum price options array. */
    $form_options['min_price_sales'] = array(
          'pls_empty_value' => __( $pls_empty_value['min_price_sales'], pls_get_textdomain() ),
          '20000' => '$20,000',
          '40000' => '$40,000',
          '60000' => '$60,000',
          '80000' => '$80,000',
          '100000' => '$100,000',
          '120000' => '$120,000',
          '140000' => '$140,000',
          '160000' => '$160,000',
          '180000' => '$180,000',
          '200000' => '$200,000',
          '250000' => '$250,000',
          '300000' => '$300,000',
          '350000' => '$350,000',
          '400000' => '$400,000',
          '450000' => '$450,000',
          '500000' => '$500,000',
          '550000' => '$550,000',
          '600000' => '$600,000',
          '650000' => '$650,000',
          '700000' => '$700,000',
          '750000' => '$750,000',
          '800000' => '$800,000',
          '850000' => '$850,000',
          '900000' => '$900,000',
          '950000' => '$950,000',
          '1000000' => '$1,000,000'
    );

    /* Set the maximum price sales options array + its default */
    $form_options['max_price_sales'] = $form_options['min_price_sales'];
    $form_options['max_price_sales']['pls_empty_value'] = __( $pls_empty_value['max_price_sales'], pls_get_textdomain() );

    $user_price_start_sales = pls_get_option('pls-option-sales-price-min') ? pls_get_option('pls-option-sales-price-min') : 0;
    $user_price_end_sales = pls_get_option('pls-option-sales-price-max');
    $user_price_inc_sales = pls_get_option('pls-option-sales-price-inc');

    if (is_numeric($user_price_start_sales) && is_numeric($user_price_end_sales) && is_numeric($user_price_inc_sales)) {
        
        // Handle when increment is larger than the range from start to end
        if ($user_price_inc_sales > ($user_price_end_sales - $user_price_start_sales) ) {
          $user_price_inc_sales = ($user_price_end_sales - $user_price_start_sales);
        }
        
        $range = range($user_price_start_sales, $user_price_end_sales, $user_price_inc_sales);
        
        // Create empty arrays
        $form_options['min_price_sales'] = array();
        $form_options['max_price_sales'] = array();
        
        // set empty values
        $form_options['min_price_sales']['pls_empty_value'] = __( $pls_empty_value['min_price_sales'], pls_get_textdomain() );
        $form_options['max_price_sales']['pls_empty_value'] = __( $pls_empty_value['max_price_sales'], pls_get_textdomain() );

        foreach ($range as $price_value) {
            $form_options['min_price_sales'][$price_value] = PLS_Format::number($price_value, array('abbreviate' => false));
            $form_options['max_price_sales'][$price_value] = PLS_Format::number($price_value, array('abbreviate' => false));
        }
    }

    // Set min_beds/max_beds form element
    $form_options['min_beds'] = array(
          'pls_empty_value' => __( $pls_empty_value['min_beds'], pls_get_textdomain() ),
          '0' => '0',
          '1' => '1',
          '2' => '2',
          '3' => '3',
          '4' => '4',
          '5' => '5',
          '6' => '6',
          '7' => '7',
          '8' => '8',
          '9' => '9',
          '10' => '10',
          '11' => '11',
          '12' => '12',
          '13' => '13',
          '14' => '14',
          '15' => '15'
    );

    $user_bed_start = pls_get_option('pls-option-bed-min') ? pls_get_option('pls-option-bed-min') : 0;
    $user_bed_end = pls_get_option('pls-option-bed-max') ? pls_get_option('pls-option-bed-max') : 15;
    $user_bed_inc = pls_get_option('pls-option-bed-inc') ? pls_get_option('pls-option-bed-inc') : 1;

    if (is_numeric($user_bed_start) && is_numeric($user_bed_end) && is_numeric($user_bed_inc)) {
        $range = range($user_bed_start, $user_bed_end, $user_bed_inc);
        $form_options['min_beds'] = array();
        foreach ($range as $bed_value) {
            $form_options['min_beds'][$bed_value] = $bed_value;
        }
        $form_options['min_beds'] = array('pls_empty_value' => $pls_empty_value['min_beds']) + $form_options['min_beds'];
    }

    /** Set the max beds array too. */
    $form_options['max_beds'] = $form_options['min_beds'];


    // Set min_baths/max_baths form element
    $form_options['min_baths'] = array(
          'pls_empty_value' => __( $pls_empty_value['min_baths'], pls_get_textdomain() ),
          '0' => '0',
          '1' => '1',
          '2' => '2',
          '3' => '3',
          '4' => '4',
          '5' => '5',
          '6' => '6',
          '7' => '7',
          '8' => '8',
          '9' => '9',
          '10' => '10'
    );

    $user_bath_start = pls_get_option('pls-option-bath-min') ? pls_get_option('pls-option-bath-min') : 0;
    $user_bath_end = pls_get_option('pls-option-bath-max') ? pls_get_option('pls-option-bath-max') : 10;
    $user_bath_inc = pls_get_option('pls-option-bath-inc') ? pls_get_option('pls-option-bath-inc') : 1;

    if (is_numeric($user_bath_start) && is_numeric($user_bath_end) && is_numeric($user_bath_inc)) {
        $range = range($user_bath_start, $user_bath_end, $user_bath_inc);
        $form_options['min_baths'] = array();
        foreach ($range as $bath_value) {
            $form_options['min_baths'][$bath_value] = $bath_value;
        }
        $form_options['min_baths'] = array('pls_empty_value' => $pls_empty_value['min_baths']) + $form_options['min_baths'];
    }

    /** Set the max baths array too. */
    $form_options['max_baths'] = $form_options['min_baths'];


    /** Define an array for extra attributes. */
    $form_opt_attr = array();

    /** Filter form fields. */
    $form_option_keys = array_keys( $form_options );
    $form_options_count = count( $form_option_keys );
    
    // foreach( $form_options as $option_name => &$opt_array ) {
    // replace the foreach with for to provide reference update functionality in the loop
    for( $i = 0; $i < $form_options_count; $i++ ) {
		$option_name = $form_option_keys[$i];
		$opt_array = $form_options[$option_name];
    	
        /** Filter each of the fields options arrays. */
        $opt_array = apply_filters( pls_get_merged_strings( array( "pls_listings_search_form_{$option_name}_array", $context ), '_', 'pre', false ), $opt_array, $context_var );

        /** Form options array. */
        $form_opt_attr[$option_name] = apply_filters( pls_get_merged_strings( array( "pls_listings_search_form_{$option_name}_attributes", $context ), '_', 'pre', false ), array(), $context_var );

        /** Make sure it is an array. */
        if ( ! is_array( $form_opt_attr[$option_name] ) ) {
            $form_opt_attr[$option_name] = array();    
        }          

        /** Append the data-placeholder attribute. */
        if ( isset( $opt_array['pls_empty_value'] ) ) {
            $form_opt_attr[$option_name] = $form_opt_attr[$option_name] + array( 'data-placeholder' => $opt_array['pls_empty_value'] );
        }
        
        $form_options[$option_name] = $opt_array;
    }

    if (!isset($_POST['metadata'])) {
      $_POST['metadata'] = array();
    }

    /**
     * Elements HTML.
     */
    /** Add the bedrooms select element. */
    if ($bedrooms == 1) {
    	$selected_beds = isset( $_POST['metadata']['beds']  ) ? wp_kses_post( $_POST['metadata']['beds'] ) : false;
        
        $form_html['bedrooms'] = pls_h( 
            'select',
            array( 'name' => 'metadata[beds]') + $form_opt_attr['bedrooms'],
                /** Get the list of options with the empty valued element selected. */
                pls_h_options( $form_options['bedrooms'], $selected_beds  )
            );
    }
  
    /** Add the bedrooms select element. */
    if ($min_beds == 1) {
    	$selected_min_beds = isset( $_POST['metadata']['min_beds']  ) ? $_POST['metadata']['min_beds'] : false; 
        
        $form_html['min_beds'] = pls_h( 
            'select',
            array( 'name' => 'metadata[min_beds]' ) + $form_opt_attr['min_beds'],
                /** Get the list of options with the empty valued element selected. */
                pls_h_options( $form_options['min_beds'], $selected_min_beds  )
            );
    }
    
    /** Add the bedrooms select element. */
    if ($max_beds == 1) {
    	$selected_max_beds = isset( $_POST['metadata']['max_beds']  ) ? $_POST['metadata']['max_beds'] : false;
        
        $form_html['max_beds'] = pls_h( 
            'select',
            array( 'name' => 'metadata[max_beds]') + $form_opt_attr['max_beds'],
                /** Get the list of options with the empty valued element selected. */
                pls_h_options( $form_options['max_beds'], $selected_max_beds  )
            );
    }

    /** Add the bathroms select element. */
    if ($bathrooms == 1) {
    	$selected_baths = isset( $_POST['metadata']['baths']  ) ? wp_kses_post( $_POST['metadata']['baths'] ) : false;
        
        $form_html['bathrooms'] = pls_h( 
            'select',
            array( 'name' => 'metadata[baths]' ) + $form_opt_attr['bathrooms'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['bathrooms'], $selected_baths )
        );            
    }

    /** Add the min baths select element. */
    if ($min_baths == 1) {
    	$selected_min_baths = isset( $_POST['metadata']['min_baths']  ) ? $_POST['metadata']['min_baths'] : false;
        
        $form_html['min_baths'] = pls_h( 
            'select',
            array( 'name' => 'metadata[min_baths]') + $form_opt_attr['min_baths'],
                /** Get the list of options with the empty valued element selected. */
                pls_h_options( $form_options['min_baths'], $selected_min_baths )
            );
    }

    /** Add the max baths select element. */
    if ($max_baths == 1) {
    	$selected_max_baths = isset( $_POST['metadata']['max_baths']  ) ? $_POST['metadata']['max_baths'] : false;
        
        $form_html['max_baths'] = pls_h( 
            'select',
            array( 'name' => 'metadata[max_baths]') + $form_opt_attr['max_baths'],
                /** Get the list of options with the empty valued element selected. */
                pls_h_options( $form_options['max_baths'], $selected_max_baths  )
            );
    }

    /** Add the bathroms select element. */
    if ($half_baths == 1) {
    	$selected_half_baths = isset( $_POST['metadata']['half_baths']  ) ? wp_kses_post( $_POST['metadata']['half_baths'] ) : false;
        
        $form_html['half_baths'] = pls_h( 
            'select',
            array( 'name' => 'metadata[half_baths]' ) + $form_opt_attr['half_baths'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['half_baths'], $selected_half_baths )
        );
    }
    

    /** Add the property type select element. */
    if ($property_type == 1) {
    	$selected_property_type = isset( $_POST['property_type']  ) ? wp_kses_post( $_POST['property_type'] ) : false;
        
        $form_html['property_type'] = pls_h(
            'select',
            array( 'name' => 'property_type' ) + $form_opt_attr['property_type'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['property_type'], $selected_property_type ) 
        );
    }

    /** Add the listing type select element. */
    if ($listing_types == 1) {
    	$selected_listing_types = isset( $_POST['listing_types']  ) ? wp_kses_post( $_POST['listing_types'] ) : false;
        
        $form_html['listing_types'] = pls_h(
            'select',
            array( 'name' => 'listing_types') + $form_opt_attr['listing_types'],
            pls_h_options( $form_options['listing_types'], $selected_listing_types )
        );
    }
    
    /** Add the zoning type select element. */
    if ($zoning_types == 1) {
    	$selected_zoning_types = isset( $_POST['zoning_types']  ) ? wp_kses_post( $_POST['zoning_types'] ) : false;
        
        $form_html['zoning_types'] = pls_h(
            'select',
            array( 'name' => 'zoning_types[]'  ) + $form_opt_attr['zoning_types'],
            pls_h_options( $form_options['zoning_types'], $selected_zoning_types )
        );
    }

    /** Add the purchase type select element. */
    if ($purchase_types == 1) {
      $default_purchase_types = @pls_get_option('default_form_options_purchase_types');
      // Set Default
      if ( empty($_POST['purchase_types']) ) {
        $purchase_types_select = array($default_purchase_types);
      } else {
        $purchase_types_select = wp_kses_post($_POST['purchase_types']);
      }
      
        $form_html['purchase_types'] = pls_h(
            'select',
            array( 'name' => 'purchase_types' ) + $form_opt_attr['purchase_types'],
            pls_h_options( $form_options['purchase_types'], $purchase_types_select )
        );
    }
    
    /** Add the availability select element. */
    if ($available_on == 1) {
    	$selected_avail_on = isset( $_POST['metadata']['avail_on']  ) ? wp_kses_post( $_POST['metadata']['avail_on'] ) : false;
    	
        $form_html['available_on'] = pls_h(
            'select',
            array( 'name' => 'metadata[avail_on]' ) + $form_opt_attr['available_on'],
            pls_h_options( $form_options['available_on'], $selected_avail_on )
        );
    }
    
    /** Add the cities select element. */
    if ( $multi_cities == 1 ) {
      // multi-city select option enabled
      $selected_locality = isset( $_POST['location']['locality']  ) ? wp_kses_post( $_POST['location']['locality'] ) : false;
      $form_html['cities'] = pls_h(
          'select',
          array( 'name' => 'location[locality][]', 'multiple' => 'multiple' ) + $form_opt_attr['cities'],
          pls_h_options( $form_options['cities'], $selected_locality, true )
      );
    } elseif ($cities == 1) {
      $selected_locality = isset( $_POST['location']['locality']  ) ? wp_kses_post( $_POST['location']['locality'] ) : false;
      $form_html['cities'] = pls_h(
          'select',
          array( 'name' => 'location[locality]' ) + $form_opt_attr['cities'],
          pls_h_options( $form_options['cities'], $selected_locality, true )
      );
    }
    
    /** Add the cities select element. */
    if ($multi_states == 1) {
      // multi-state select option enabled
      $selected_region = isset( $_POST['location']['region']  ) ? wp_kses_post( $_POST['location']['region'] ) : false;
      $form_html['states'] = pls_h(
          'select',
          array( 'name' => 'location[region][]', 'multiple' => 'multiple' ) + $form_opt_attr['states'],
          pls_h_options( $form_options['states'], $selected_region, true )
      );
    } elseif ($states == 1) {
      $selected_region = isset( $_POST['location']['region']  ) ? wp_kses_post( $_POST['location']['region'] ) : false;
      $form_html['states'] = pls_h(
          'select',
          array( 'name' => 'location[region]' ) + $form_opt_attr['states'],
          pls_h_options( $form_options['states'], $selected_region, true )
      );
    }

    /** Add the cities select element. */
    if ($zips == 1) {
    	$selected_postal = isset( $_POST['location']['postal']  ) ? wp_kses_post( $_POST['location']['postal'] ) : false;
    	
        $form_html['zips'] = pls_h(
            'select',
            array( 'name' => 'location[postal]' ) + $form_opt_attr['zips'],
            pls_h_options( $form_options['zips'], $selected_postal, true )
        );
    }

    /** Add the neighborhood select element. */
    if ($multi_neighborhoods == 1) {
      // multi-neighborhood select option enabled
      $selected_neighborhood = isset( $_POST['location']['neighborhood']  ) ? wp_kses_post( $_POST['location']['neighborhood'] ) : false;
      $form_html['neighborhood'] = pls_h(
          'select',
          array( 'name' => 'location[neighborhood][]', 'multiple' => 'multiple' ) + $form_opt_attr['neighborhood'],
          pls_h_options( $form_options['neighborhood'], $selected_neighborhood, true )
      );
    } elseif ($neighborhood == 1) {
      $selected_neighborhood = isset( $_POST['location']['neighborhood']  ) ? wp_kses_post( $_POST['location']['neighborhood'] ) : false;
      $form_html['neighborhood'] = pls_h(
          'select',
          array( 'name' => 'location[neighborhood]' ) + $form_opt_attr['neighborhood'],
          pls_h_options( $form_options['neighborhood'], $selected_neighborhood, true )
      );
    }

    /** Add the county select element. */
    if ($county == 1) {
    	$selected_county = isset( $_POST['location']['county']  ) ? wp_kses_post( $_POST['location']['county'] ) : false;
    	
        $form_html['county'] = pls_h(
            'select',
            array( 'name' => 'location[county]' ) + $form_opt_attr['county'],
            pls_h_options( $form_options['county'], $selected_county, true )
        );
    }

    /** Add the neighborhood / neighborhood_polygon select element. */
    if ($neighborhood_polygons == 1) {
        
        if ( count($form_options['neighborhood_polygons']) > 1 ) {
            $selected_polygons = isset( $_POST['neighborhood_polygons']  ) ? wp_kses_post( $_POST['neighborhood_polygons'] ) : false;
            $form_html['neighborhood_polygons'] = pls_h(
                'select',
                array( 'name' => 'neighborhood_polygons' ) + $form_opt_attr['neighborhood_polygons'],
                pls_h_options( $form_options['neighborhood_polygons'], $selected_polygons, true )
            );
          
        } else {
            // default to MLS data for neighborhoods if no polygons are set
            $selected_polygons = isset( $_POST['location']['neighborhood']  ) ? wp_kses_post( $_POST['location']['neighborhood'] ) : false;
            $form_html['neighborhood_polygons'] = pls_h(
                'select',
                array( 'name' => 'location[neighborhood]' ) + $form_opt_attr['neighborhood'],
                pls_h_options( $form_options['neighborhood'], $selected_neighborhood, true )
            );
        }
    }

    /** Add the minimum sqft select element. */
    if ($min_sqft == 1) {
      $selected_min_sqft = isset( $_POST['metadata']['min_sqft']  ) ? wp_kses_post( $_POST['metadata']['min_sqft'] ) : false;
      
        $form_html['min_sqft'] = pls_h(
            'select',
            array( 'name' => 'metadata[min_sqft]' ) + $form_opt_attr['min_sqft'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['min_sqft'], $selected_min_sqft )
        );
    }

    /** Add the minimum price select element. */
    if ($max_sqft == 1) {
      $selected_max_sqft = isset( $_POST['metadata']['max_sqft']  ) ? wp_kses_post( $_POST['metadata']['max_sqft'] ) : false;
      
        $form_html['max_sqft'] = pls_h(
            'select',
            array( 'name' => 'metadata[max_sqft]' ) + $form_opt_attr['max_sqft'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['max_sqft'], $selected_max_sqft )
        );
    }
    
    /** Add the minimum price select element. */
    if ($min_price == 1) {
    	$selected_min_price = isset( $_POST['metadata']['min_price']  ) ? wp_kses_post( $_POST['metadata']['min_price'] ) : false;
    	
        $form_html['min_price'] = pls_h(
            'select',
            array( 'name' => 'metadata[min_price]' ) + $form_opt_attr['min_price'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['min_price'], $selected_min_price )
        );
    }
    
    /** Add the maximum price select element. */
    if ($max_price == 1) {
    	$selected_max_price = isset( $_POST['metadata']['max_price']  ) ? wp_kses_post( $_POST['metadata']['max_price'] ) : false;
    	
        $form_html['max_price'] = pls_h(
            'select',
            array( 'name' => 'metadata[max_price]' ) + $form_opt_attr['max_price'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['max_price'], $selected_max_price )
        );
    }

    /** Add the minimum price select element. */
    if ($min_price_rental == 1) {
    	$selected_min_price = isset( $_POST['metadata']['min_price']  ) ? wp_kses_post( $_POST['metadata']['min_price'] ) : false;
    	
        $form_html['min_price_rental'] = pls_h(
            'select',
            array( 'name' => 'metadata[min_price]' ) + $form_opt_attr['min_price'],
            pls_h_options( $form_options['min_price_rental'], $selected_min_price )
        );
    }
    
    /** Add the maximum price select element. */
    if ($max_price_rental == 1) {
    	$selected_max_price = isset( $_POST['metadata']['max_price']  ) ? wp_kses_post( $_POST['metadata']['max_price'] ) : false;
    
        $form_html['max_price_rental'] = pls_h(
            'select',
            array( 'name' => 'metadata[max_price]' ) + $form_opt_attr['max_price'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['max_price_rental'], $selected_max_price )
        );
    }

    /** Add the minimum price select element. */
    if ($min_price_sales == 1) {
    	$selected_min_price = isset( $_POST['metadata']['min_price']  ) ? wp_kses_post( $_POST['metadata']['min_price'] ) : false;
    	
        $form_html['min_price_sales'] = pls_h(
            'select',
            array( 'name' => 'metadata[min_price]' ) + $form_opt_attr['min_price'],
            pls_h_options( $form_options['min_price_sales'], $selected_min_price )
        );
    }
    
    /** Add the maximum price select element. */
    if ($max_price_sales == 1) {
    	$selected_max_price = isset( $_POST['metadata']['max_price']  ) ? wp_kses_post( $_POST['metadata']['max_price'] ) : false;
    
        $form_html['max_price_sales'] = pls_h(
            'select',
            array( 'name' => 'metadata[max_price]' ) + $form_opt_attr['max_price'],
            /** Get the list of options with the empty valued element selected. */
            pls_h_options( $form_options['max_price_sales'], $selected_max_price )
        );
    }

    $section_title = array(
        'bedrooms' => 'Beds',
        'min_beds' => 'Min Beds',
        'max_beds' => 'Max Beds',
        'bathrooms' => 'Baths',
        'min_baths' => 'Min Baths',
        'max_baths' => 'Max Baths',
        'half_baths' => 'Half Baths',
        'property_type' => 'Property Type',
        'zoning_types' => 'Zoning Type',
        'listing_types' => 'Listing Type',
        'purchase_types' => 'Purchase Type',
        'available_on' => 'Available',
        'cities' => 'Near',
        'states' => 'State',
        'zips' => 'Zip Code',
        'min_price' => 'Min Price',
        'max_price' => 'Max Price',
        'neighborhood' => 'Neighborhood',
        'county' => 'County',
        'min_beds' => 'Min Beds',
        'max_beds' => 'Max Beds',
        'min_price_rental' => 'Min Price Rental',
        'max_price_rental' => 'Max Price Rental',
        'min_price_sales' => 'Min Price Sales',
        'max_price_sales' => 'Max Price Sales',
        'neighborhood_polygons' => 'Neighborhood Polygon',
        'min_sqft' => 'Min Sqft',
        'max_sqft' => 'Max Sqft'
    );

    // In case user somehow disables all filters.
    if (empty($form_html)) {
        return '';
    }

    /** Apply filters on all the form elements html. */
    $form_html_keys = array_keys( $form_html );
    $form_html_count = count( $form_html_keys );
    
    // foreach( $form_html as $option_name => &$opt_html ) {
    for( $i = 0; $i < $form_html_count; $i++ ) {
    	$option_name = $form_html_keys[$i];
    	$opt_html = $form_html[$option_name];
    	
        $opt_html = apply_filters( pls_get_merged_strings( array( "pls_listings_search_form_{$option_name}_html", $context ), '_', 'pre', false ),
                                   $opt_html, $form_options[$option_name], $section_title[$option_name], $context_var );
        
        $form_html[$option_name] = $opt_html;
    }

    /** Combine the form elements. */
    $form = '';
    
    foreach ( $form_html as $label => $select ) {
        $form .= pls_h(
            'section',
            array( 'class' => $label . ' pls_search_form' ),
            pls_h_label( $section_title[$label], $label ) .
            $select
        );
    }

    /** Add the filtered submit button. */
    if ($include_submit) {
        $form_html['submit'] = apply_filters(
            pls_get_merged_strings( array( "pls_listings_search_submit", $context ), '_', 'pre', false ),
            pls_h( 'input', array('class' => 'pls_search_button', 'type' => 'submit', 'value' => 'Search' ) ),
            $context_var
        );
        /** Append the form submit. */
        $form .= $form_html['submit'];
    }
       

    /** Wrap the combined form content in the form element and filter it. */
    $form_id = pls_get_merged_strings( array( 'pls-listings-search-form', $context ), '-', 'pre', false );

    $form = pls_h(
        'form',
        array( 'action' => @$form_data->action, 'method' => 'post', 'id' => $form_id, 'class' => $class ),
        @$form_data->hidden_field . apply_filters( pls_get_merged_strings( array( "pls_listings_search_form_inner", $context ), '_', 'pre', false ), $form, $form_html, $form_options, $section_title, $context_var )
    );

    /** Filter the form. */
    $result = apply_filters( pls_get_merged_strings( array( "pls_listings_search_form_outer", $context ), '_', 'pre', false ), $form, $form_html, $form_options, $section_title, @$form_data, $form_id, $context_var ); 
    
    // Load the filters.js script...
    $result .= '<script type="text/javascript" src="' . PLACESTER_PLUGIN_URL . 'placester-search/js/filters.js"></script>';

    $cache->save($result);
    return $result;
	}

  public static function create_custom_select_element ($post_param, $post_sub_param, $options = array(), $placeholder = 'Any', $multi_select = false) {
    // at this point this is only ready for metadata params

    /* Option Examples: */
    // $post_param = 'location'
    // $post_sub_param = 'neighborhood'
    // $name = ''
    // $options = array(
    //  "beacon hill" => "Beacon Hill",
    //  "value" => "Label"
    // )
    // $placeholder = 'Neighborhoods' /* placeholder of the select element w/ Chosen library */
    // $multi_select = true /* you can search multiple neighborhoods at once */
    
    /* Create Option Elements */

    // check to see if $_POST has passed this value as selected
    $options_elements = '';

    if (isset($options['optgroups']) && !empty($options['optgroups'])) {

      foreach ($options['optgroups'] as $optgroup_value) {
        
        // add options to optgroup
        foreach ($optgroup_value as $og_value_key => $og_value_name) {
            
          $options_elements .= '<optgroup label="'.$og_value_key.'">';

          // Foreach option within the subgroup: $single_og_value_name
          foreach ($og_value_name[0] as $single_og_key => $single_og_value_name) {

            $selected = false;

            // Multiple values of same param, ie. more than one neighborhood
            if ($multi_select == true) {
              
                if (isset($_POST[$post_param][$post_sub_param]) && !empty($_POST[$post_param][$post_sub_param])) {
                  // If the param is set in the post value...

                  $posted_values = array();

                  // loop through each of the multiple $post_sub_param's values: $v
                  foreach ($_POST[$post_param][$post_sub_param] as $k => $v) {
                      
                      $selected = false;
                      // clean $value
                      $multi_value = wp_kses_post( $v );
                      // does $_POST param's current value match the option we're on now?
                      $selected = ($single_og_value_name == $multi_value) ? true : false;
                      
                      if ($selected == true) {
                        
                          // make sure this option element isn't created again
                          array_push($posted_values, $single_og_value_name);
                          // create selected option element
                          $options_elements .= self::create_option_element($single_og_value_name, $single_og_value_name, true);
                      
                      } 
                  }

                  // create unselected option element if $single_og_value_name isn't a $posted_values
                  if (!in_array($single_og_value_name, $posted_values) ) {
                      $options_elements .= self::create_option_element($single_og_value_name, $single_og_value_name, false);                  
                  }

              } else {
                  // If the param isn't set in the post value...

                  // Add unselected option
                  $options_elements .= self::create_option_element($single_og_value_name, $single_og_value_name);
              }

            } else {
                // Single param
                if (isset($_POST[$post_param][$post_sub_param]) && !empty($_POST[$post_param][$post_sub_param])) {
                  $selected_options = wp_kses_post( $_POST[$post_param][$post_sub_param] );
                  $selected = ($_POST[$post_param][$post_sub_param] == $value) ? true : false;  
                }
                $options_elements .= self::create_option_element($single_og_value_name, $single_og_value_name, $selected);
            }

          }

          $options_elements .= '</optgroup>';
        }
    
      }

    } else {
      foreach ($options as $key => $value) {
        $selected = false;
        
        // Multiple values of same param, ie. more than one neighborhood
        if ($multi_select == true) {
          if (isset($_POST[$post_param][$post_sub_param]) && !empty($_POST[$post_param][$post_sub_param])) {
            foreach ($_POST[$post_param][$post_sub_param] as $k => $v) {
              $multi_value = wp_kses_post( $_POST[$post_param][$post_sub_param][$k] );
              $selected = ($value == $multi_value) ? true : false;
            }
          }
          $options_elements .= self::create_option_element($value, $value, $selected);
        } else {
          // Single param
          if (isset($_POST[$post_param][$post_sub_param]) && !empty($_POST[$post_param][$post_sub_param])) {
            $selected_options = wp_kses_post( $_POST[$post_param][$post_sub_param] );
            $selected = ($_POST[$post_param][$post_sub_param] == $value) ? true : false;  
          }
          $options_elements .= self::create_option_element($value, $value, $selected);
        }
      }
    }

    if ($multi_select == true) {
      $select_name = $post_param.'['.$post_sub_param.'][]';
    } else {
      $select_name = $post_param.'['.$post_sub_param.']';
    }
    
    /* Create Select Element w/ Options Created */ 
    $select_element = self::create_select_element($select_name, $placeholder, $multi_select, $options_elements);

    return $select_element;
  }


  private static function create_option_element ($label, $value, $selected = false) {

    if ($selected == true) {
      $option = '<option value="'.$value.'" selected="selected">'.$label.'</option>';
    } else {
      $option = '<option value="'.$value.'">'.$label.'</option>';
    }

    return $option;
  }


  private static function create_select_element ($name, $placeholder, $multi_select, $options) {

    // Open select tag
    $select_element = '<select ';

    // add name attr
    if (isset($name)) {
      $select_element .= 'name="'.$name.'" ';
    }

    if (isset($placeholder)) {
      $select_element .= 'data-placeholder="'.$placeholder.'" title="'.$placeholder.'"';
    }

    // is multi_select select?
    if ($multi_select == true) {
      $select_element .= 'multiple="multiple" ';
    }

    $select_element .= '>';

    // Add options to select tags
    $select_element .= $options;

    // Close select tags
    $select_element .= '</select>';

    return $select_element;
  }


  private static function set_post_params_to_options ($post_param, $post_sub_param, $multi_select) {
    // check to see if $_POST has passed this value as selected
    $options_elements = '';

    // Multiple locations of same param, ie. more than one neighborhood
    if ($multi_select == true && is_array($_POST[$post_param][$post_sub_param])) {

      foreach ($_POST[$post_param][$post_sub_param] as $k => $v) {
        $multi_value = wp_kses_post( $_POST[$post_param][$post_sub_param][$k] );
        $selected = ($value == $multi_value) ? true : false;
        $options_elements .= self::create_option_element($key, $value, $selected);
      }

    } else {
      // Single location param
      $selected_options = wp_kses_post( $_POST[$post_param][$post_sub_param] );
      $selected = ($_POST[$post_param][$post_sub_param] == $value) ? true : false;
      $options_elements .= self::create_option_element($key, $value, $selected);
    }

    return $options_elements;
  }

  private static function process_default_value_array ($empty_values, $context) {
    $defaults = array(
      'min_beds' => 'Any',
      'max_beds' => 'Any',
      'min_baths' => 'Any',
      'max_baths' => 'Any',
      'bedrooms' => 'Any',
      'bathrooms' => 'Any',
      'half_baths' => 'Any',
      'min_price' => 'Any',
      'max_price' => 'Any',
      'min_price_rental' => 'All',
      'min_price_sales' => 'All',
      'max_price_rental' => 'All',
      'max_price_sales' => 'All',
      'property_type' => 'Any',
      'listing_types' => 'Any',
      'zoning_types' => 'Any',
      'purchase_types' => 'Any',
      'available_on' => 'Any',
      'cities' => 'All',
      'states' => 'All',
      'zips' => 'All',
      'neighborhoods' => 'All',
      'neighborhood_polygons' => 'All',
      'county' => 'All',
      'min_sqft' => 'All',
      'max_sqft' => 'All'
    );

    $empty_values = apply_filters( pls_get_merged_strings( array( "pls_listings_search_form_default_values", $context ), '_', 'pre', false ), $empty_values, $defaults );
    $empty_values = wp_parse_args($empty_values, $defaults);

    return $empty_values;
  }

}