<?php
PLS_Partials_Get_Listings_Ajax::init();
class PLS_Partials_Get_Listings_Ajax {
    /**
     * Returns the list of listings managed by ajax. It includes pagination and 
     * 'sort by' controls.
     * 
     * The defaults are as follows:
     *     'placeholder_img' - Defaults to placeholder image. The path to the 
     *          listing image that should be use if the listing has no images.
     *     'loading_img' - Defaults to the Blueprint spinner. Path to the
     *          loader image.
     *     'image_width' - Defaults to 100. The width of the listing image.
     *     'crop_description' - Defaults to false. Wether the description 
     *          should be cropped or not.
     *     'context' - An execution context for the function. Used when the 
     *          filters are created.
     *     'context_var' - Any variable that needs to be passed to the filters 
     *          when function is executed.
     *
     * Defines the following hooks:
     *      pls_listings_list_ajax_item_html[_context] - Filters html for each 
     *          item in the list
     *      pls_listings_list_ajax_no_results_html[_context] - Filters what 
     *          should be displayed when no results are found.
     *      pls_listings_list_ajax_html[_context] - Filters the html for the 
     *          whole list.
     *      pls_listings_list_ajax_sort_by_options[_context] - Filters the 
     *          options from the "Sort by" select box.
     *
     * @static
     * @param array $args Optional. Overrides defaults.
     * @return string The html and js.
     * @since 0.0.1
     **/
    public static function init () {
        // Hook the callback for ajax requests
        add_action('wp_ajax_pls_listings_ajax', array(__CLASS__, 'get' ) );
        add_action('wp_ajax_nopriv_pls_listings_ajax', array(__CLASS__, 'get' ) );

        // Endpoints for rendering favorite listings in a search results datatable...
        add_action('wp_ajax_listings_fav_ajax', array(__CLASS__,'get_favorite_listings'));
        add_action('wp_ajax_nopriv_listings_fav_ajax', array(__CLASS__,'get_favorite_listings'));
    }

    public static function get_favorite_listings () {
        $favorite_ids = PLS_Plugin_API::get_favorite_properties();
        
        // Will echo listings as a JSON-encoded output...
        self::get(array('property_ids' => $favorite_ids, 'allow_id_empty' => true));

        die();
    }

    public static function load ($args = array()) {
        // Respect the "Sort By" theme option if it's set..(default to number of total images)
        $sort_by_dflt = pls_get_option('listings_default_sort_by', 'total_images');

        // Respect sort type theme option if it's set...(default to number of desc)
        $sort_type_dflt = pls_get_option('listings_default_sort_type', 'desc');

        // Set default options...
        $defaults = array(
            'loading_img' => trailingslashit( PLS_IMG_URL ) . 'preview_load_spin.gif',
            'image_width' => 100,
            'sort_type' => $sort_type_dflt,
            'sort_by' => $sort_by_dflt,
            'show_sort' => true,
            'crop_description' => 0,
            'listings_per_page' => pls_get_option('listings_default_list_length'),
            'context' => '',
            'context_var' => NULL,
            'append_to_map' => true,
            'search_query' => $_POST,
            'table_id' => 'placester_listings_list',
            'map_js_var' => 'pls_google_map',
            'search_class' => 'pls_search_form_listings',
            'query_limit' => pls_get_option('listings_default_list_limit')
        );

        // Extract the arguments after they merged with the defaults
        $args = wp_parse_args($args, $defaults);
        extract($args, EXTR_SKIP);

        // Now we need to pass the 'limit' parameter from defaults into the search_query to honor corresponding theme option...
        $query_limit = (int) $query_limit;

        // If limit is invalid or greater than API's upper limit, adjust accordingly... 
        // TODO: this seems to break the prop# form option
        $search_query['limit'] = ( (is_int($query_limit) && $query_limit <= 50) ? $query_limit : 50 );

        if (empty($sort_by_options) || !is_array($sort_by_options)) {
            $sort_by_options = array(
                'total_images' => 'Total Images',
                'location.address' => 'Address',
                'location.locality' => 'City',
                'location.region' => 'State',
                'location.postal' => 'Zip',
                // 'zoning_types' => 'Zoning',
                // 'purchase_types' => 'Purchase Type',
                // 'property_type' => 'Property Type',
                'cur_data.beds' => 'Beds',
                'cur_data.baths' => 'Baths',
                'cur_data.price' => 'Price',
                'cur_data.sqft' => 'Square Feet',
                // 'cur_data.avail_on' => 'Date Available'
            );
        }
        $sort_type_options = array('desc' => 'Descending','asc' => 'Ascending');

        // /** Filter the "Sort by"  and sort type options. */
        $sort_by_options = apply_filters("pls_listings_list_ajax_sort_by_options", $sort_by_options);
        $sort_type_options = apply_filters("pls_listings_list_ajax_sort_type_options", $sort_type_options);

        // Ultimately, sort params in the $_POST array take precedence if they exist...
        $sort_by = isset($_POST['sort_by']) ? $_POST['sort_by'] : $sort_by;
        $sort_type = isset($_POST['sort_type']) ? $_POST['sort_type'] : $sort_type;

        // This page should trigger a compliance message since it's set up to display listings
        PLS_Plugin_API::$listing_data_requested = true;

        ob_start();
        ?>
            <script type="text/javascript" src="<?php echo PLACESTER_PLUGIN_URL . 'placester-maps/js/listing-list.js'; ?>"></script>
            
            <!-- Sort Dropdown -->
            <?php if ($show_sort): ?>
              <form class="sort_wrapper">
                  <div class="sort_item">
                    <label for="sort_by">Sort By</label>
                    <select name="sort_by" id="sort_by">
                        <?php foreach ($sort_by_options as $key => $value): ?>
                        	<?php if ($sort_by == $key): ?>
                                <option value="<?php echo $key; ?>" selected="selected"><?php echo $value ?></option>
                            <?php else: ?>
                                <option value="<?php echo $key; ?>"><?php echo $value ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="sort_item">
                    <label for="sort_type">Sort Direction</label>
                    <select name="sort_type" id="sort_dir">
                    	<?php foreach ($sort_type_options as $key => $value): ?>
                        	<?php if ($sort_type == $key): ?>
                            	<option value="<?php echo $key; ?>" selected="selected"><?php echo $value; ?></option>
                        	<?php else: ?>
                        	   <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        	<?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                  </div>
              </form>
            <?php endif; ?>

            <!-- Datatable -->
            <div class="clear"></div>
            <div id="container" style="width: 99%">

                <!-- Load the spinner after dataTables has set itself up -->
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        var spinner = '<img id="spinner" src="<?php echo $loading_img; ?>" width="50" />';
                        $('.dataTables_processing').html(spinner);
                    });
                </script>

                <div id="context" class="<?php echo $context; ?>"></div>
                <table id="<?php echo $table_id; ?>" class="widefat post fixed placester_properties" cellspacing="0">
                    <thead>
                        <tr>
                            <th><span></span></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php

        echo ob_get_clean();
    }

    private static function order_images ($a, $b) {
        if ($a['order'] == $b['order']) { return 0; }
        return ($a['order'] < $b['order']) ? -1 : 1;
    }

    public static function get ($args = array()) {
        // Store this for use in the final output/response...
        $sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 0;
        unset($_POST['sEcho']);

        $context_orig  = isset($_POST['context']) ? $_POST['context'] : '';
        unset($_POST['context']);

        // If length is not set for number of listings to return, set it to our Theme Options default
        $_POST['limit'] = isset($_POST['iDisplayLength']) ? $_POST['iDisplayLength'] : pls_get_option('listings_default_list_length');
        unset($_POST['iDisplayLength']);    

        $_POST['offset'] = isset($_POST['iDisplayStart']) ? $_POST['iDisplayStart'] : 0;
        unset($_POST['iDisplayStart']);

        $saved_search_hash = isset($_POST['saved_search_hash']) ? $_POST['saved_search_hash'] : null;
        unset($_POST['saved_search_hash']);

        $saved_search_lookup = isset($_POST['saved_search_lookup']) ? $_POST['saved_search_lookup'] : null;
        unset($_POST['saved_search_lookup']);

        // Remove this so it doesn't accidentally end up as a superfluous argument of an API call...
        unset($_POST['action']);

        // Handle location edge-case...
        if (!empty($_POST['location']) && !empty($_POST['location']['address']) && empty($_POST['location']['address_match'])) {
            $_POST['location']['address_match'] = 'like';
        }

        // Handle saved search...
        if ($saved_search_lookup) {
            // Attempt to retrieve search filters associated with the given saved search lookup ID...
            $filters = PLS_Plugin_API::get_saved_search_filters($saved_search_lookup);
            if ($filters && is_array($filters)) {
                // For backwards compatibility, handle older fields that are no longer stored as saved search filters...
                $old_field_map = array(
                    'sEcho' => false,
                    'context' => false,
                    'iDisplayLength' => 'limit', 
                    'iDisplayStart' => 'offset',
                    'saved_search_lookup' => false,
                    'action' => false
                );

                foreach ($old_field_map as $old => $new) {
                    if (isset($filters[$old])) {
                        if ($new !== false) { 
                            $filters[$new] = $filters[$old];
                        }
                        unset($filters[$old]);
                    }
                }

                // Swap all existing POST filters for the ones associated with the retrieved saved search...
                $saved_search_hash = $saved_search_lookup;
                $_POST = $filters;
            }
        }
        else if ($saved_search_hash) {
            PLS_Plugin_API::save_search($saved_search_hash, $_POST);
        }

        // Is this one of the user's favorite searches?
        if(is_user_logged_in() && $saved_search_hash)
            $favorite_search = PLS_Plugin_API::get_favorite_search($saved_search_hash);

        // Define the default argument array
        $defaults = array(
            'image_width' => 100,
            'crop_description' => 0,
            'sort_type' => pls_get_option('listings_default_sort_type'),
            'listings_per_page' => pls_get_option('listings_default_list_length'),
            'context' => $context_orig,
            'context_var' => NULL,
            'append_to_map' => true,
            'search_query' => $_POST,
            'property_ids' => isset($_POST['property_ids']) ? $_POST['property_ids'] : '',
            'allow_id_empty' => false
        );

        // Resolve function args with default ones (which include any existing POST fields)...
        $merged_args = wp_parse_args($args, $defaults);
        extract($merged_args, EXTR_SKIP);

        $cache = new PLS_Cache('list');
        if ($cached_response = $cache->get($merged_args)) {
            // These fields are not cached
            $cached_response['sEcho'] = $sEcho;
            if ($favorite_search) {
                $cached_response['favorite_search'] = 1;
                $cached_response['favorite_search_email'] = ($favorite_search['timestamp'] ? 1 : 0);
            }
            else if ($saved_search_hash) {
                $cached_response['favorite_search'] = 0;
            }

            echo json_encode($cached_response);
            die();
        }

        // Start off with a placeholder in case the plugin is not active or there is no API key...
        $api_response = PLS_Listing_Helper::$default_listing;

        // If plugin is active, grab listings intelligently...
        if (!pls_has_plugin_error()) {

            // Set a hard limit on the number of listings retrievable in search results -- an MLS requirement
            if($mls_limit = pls_get_option('pls-mls-listing-limit')) {
                if($search_query['limit'] == 0 || $search_query['offset'] + $search_query['limit'] >= $mls_limit)
                    $search_query['limit'] = $mls_limit - $search_query['offset'];
            }
            if($search_query['limit'] <= 0)
                $api_response = array('total' => $mls_limit . '+', 'offset' => $_POST['offset'], 'limit' => $_POST['limit'] ?: 150, 'count' => 0, 'listings' => array());
            else

            // Get the listings list markup and JS
            if (!empty($property_ids) || $allow_id_empty) {
                // Sometimes property_ids are passed in as a flat screen from the JS post object
                if (is_string($property_ids)) {
                    $property_ids = explode(',', $property_ids);
                }

                $api_response = PLS_Plugin_API::get_listing_details(array('property_ids' => $property_ids, 'limit' => $_POST['limit'], 'offset' => $_POST['offset']));
            } 
            elseif (isset($search_query['neighborhood_polygons']) && !empty($search_query['neighborhood_polygons']) ) {
                $api_response = PLS_Plugin_API::get_polygon_listings($search_query);
            }
            else {
                $api_response = PLS_Plugin_API::get_listings($search_query);
            }

            // When using an MLS limit use that as a maximum for search result total
            if($mls_limit && $api_response['total'] > $mls_limit) $api_response['total'] = $mls_limit . '+';

        }

        $response = array();        
      
        // Build response for datatables.js
        $listings = array();
        $listings_cache = new PLS_Cache('Listing Thumbnail');

        foreach ($api_response['listings'] as $key => $listing) {
            // Check for cached listing thumbnail...
            $cache_id = array('context' => $context, 'listing_id' => $listing['id']);
            if (!($item_html = $listings_cache->get($cache_id))) {
                // Handle case of zero listing images...
                if (empty($listing['images'])) {
                    $listing['images'][0]['url'] = '';
                }

                ob_start();
                ?>
                    <div class="listing-item grid_8 alpha" itemscope itemtype="http://schema.org/Offer" data-listing="<?php echo $listing['id'] ?>">
                        <div class="listing-thumbnail grid_3 alpha">
                            <?php 
                                $property_images = is_array($listing['images']) ? $listing['images'] : array();
                                usort($property_images, array(__CLASS__, 'order_images'));
                                $listing['images'] = $property_images;
                            ?>
                              
                             <a href="<?php echo @$listing['cur_data']['url']; ?>" itemprop="url">
                                <?php echo PLS_Image::load($property_images[0]['url'], array('resize' => array('w' => 210, 'h' => 140), 'fancybox' => true, 'as_html' => true, 'html' => array('alt' => $listing['location']['full_address'], 'itemprop' => 'image', 'placeholder' => PLS_IMG_URL . "/null/listing-300x180.jpg"))); ?>
                            </a>
                        </div>

                        <div class="listing-item-details grid_5 omega">
                            <header>
                                <p class="listing-item-address h4" itemprop="name">
                                    <a href="<?php echo PLS_Plugin_API::get_property_url($listing['id'], $listing); ?>" rel="bookmark" title="<?php echo $listing['location']['address'] ?>" itemprop="url">
                                        <?php echo $listing['location']['address'] . ', ' . $listing['location']['locality'] . ' ' . $listing['location']['region'] . ' ' . $listing['location']['postal']  ?>
                                    </a>
                                </p>
                            </header>

                            <div class="basic-details">
                                <ul>
                                  	<?php if (!empty($listing['cur_data']['beds'])): ?>
                                  		<li class="basic-details-beds p1"><span>Beds:</span> <?php echo @$listing['cur_data']['beds']; ?></li>
                                  	<?php endif; ?>

                                  	<?php if (!empty($listing['cur_data']['baths'])): ?>
                                  		<li class="basic-details-baths p1"><span>Baths:</span> <?php echo @$listing['cur_data']['baths']; ?></li>
                                  	<?php endif; ?>

                                  	<?php if (!empty($listing['cur_data']['half_baths'])): ?>
                                  		<li class="basic-details-half-baths p1"><span>Half Baths:</span> <?php echo @$listing['cur_data']['half_baths']; ?></li>
                                  	<?php endif; ?>

                                  	<?php if (!empty($listing['cur_data']['price'])): ?>
                                  		<li class="basic-details-price p1" itemprop="price"><span>Price:</span> <?php echo PLS_Format::number($listing['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true)); ?></li>
                                  	<?php endif; ?>

                                  	<?php if (!empty($listing['cur_data']['sqft'])): ?>
                                  		<li class="basic-details-sqft p1"><span>Sqft:</span> <?php echo PLS_Format::number($listing['cur_data']['sqft'], array('abbreviate' => false, 'add_currency_sign' => false)); ?></li>
                                  	<?php endif; ?>

                                    <?php if (!empty($listing['rets']['mls_id'])): ?>
                                        <li class="basic-details-mls p1"><span>MLS ID:</span> <?php echo @$listing['rets']['mls_id']; ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <p class="listing-description p4" itemprop="description">
                                <?php echo substr($listing['cur_data']['desc'], 0, 300); ?>
                            </p>

                        </div>

                        <div class="actions">
                            <a class="more-link" href="<?php echo PLS_Plugin_API::get_property_url($listing['id'], $listing); ?>" itemprop="url">View Property Details</a>
                            <?php echo PLS_Plugin_API::placester_favorite_link_toggle(array('property_id' => $listing['id'])); ?>
                        </div>

                        <?php PLS_Listing_Helper::get_compliance(array('context' => 'inline_search', 'agent_name' => @$listing['rets']['aname'] , 'office_name' => @$listing['rets']['oname'])); ?>
                    </div>
                <?php
          
                $item_html = ob_get_clean();
                $item_html = apply_filters( pls_get_merged_strings( array("pls_listings_list_ajax_item_html", $context), '_', 'pre', false ), htmlspecialchars_decode($item_html), $listing, $context_var);
                $listings_cache->save($item_html);
            }

            $listings[$key][] = $item_html;
            $listings[$key][] = $listing;
        }

        // Required for datatables.js to function properly...
        $response['sFirst'] = 'Previous';
        $response['sPrevious'] = 'Next';
        $response['aaData'] = $listings;
        $response['iTotalRecords'] = isset($api_response['total']) ? $api_response['total'] : 0;
        $response['iTotalDisplayRecords'] = $response['iTotalRecords'];
        $response['iDisplayStart'] = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $response['iDisplayLength'] = isset($_POST['limit']) ? $_POST['limit'] : 0;

        $cache->save($response);

        // these can't be cached
        $response['sEcho'] = $sEcho;
        if($favorite_search) {
            $response['favorite_search'] = 1;
            $response['favorite_search_email'] = ($favorite_search['timestamp'] ? 1 : 0);
        }
        else if ($saved_search_hash) {
            $response['favorite_search'] = 0;
        }

        ob_start("ob_gzhandler");
        echo json_encode($response);

        // Wordpress echos out a "0" randomly -- die prevents this...
        die();
    }
}
