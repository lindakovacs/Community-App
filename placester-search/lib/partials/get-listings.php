<?php 

class PLS_Partial_Get_Listings {
    /**
     * Returns a list of properties listed formated in a default html.
     *
     * This function takes the raw properties data returned by the plugin and
     * formats wrapps it in html. The returned html is filterable in multiple
     * ways.
     *
     * The defaults are as follows:
     *     'width' - Default 100. The listing image width. If set to 0,
     *          width is not added.
     *     'height' - Default false. The listing image height. If set to 0,
     *          width is not added.
     *     'placeholder_img' - Defaults to placeholder image. The path to the
     *          listing image that should be use if the listing has no images.
     *     'context' - An execution context for the function. Used when the
     *          filters are created.
     *     'context_var' - Any variable that needs to be passed to the filters
     *          when function is executed.
     *     'limit' - Default is 5. Total number of listings to retrieve. Maximum
     *          set to 50.
     * Defines the following filters:
     * pls_listings_request[_context] - Filters the request parameters.
     * pls_listing[_context] - Filters the individual listing html.
     * pls_listings[_context] - Filters the complete listings list html.
     *
     * @static
     * @param array|string $args Optional. Overrides defaults.
     * @return string The html with the list of properties.
     * @since 0.0.1
     */
    public static function init ($args = '') {
        
        $cache = new PLS_Cache('list');
        if ($result = $cache->get($args)) {
          // return $result;
        }

        /** Define the default argument array. */
        $defaults = array(
            'width' => 100,
            'height' => 0,
            'context' => '',
            'context_var' => false,
            'featured_option_id' => false,
            /** Placester API arguments. */
            'limit' => 5,
            'sort_by' => 'total_images',
            'sort_type' => 'desc',
            'request_params' => '',
            'neighborhood_polygons' => false
        );

        /** Merge the arguments with the defaults. */
        $args = wp_parse_args( $args, $defaults );

        /** Extract the arguments after they merged with the defaults. */
        extract( $args, EXTR_SKIP );
        
        // Sanitize the width
        if ($width) 
          { $width = absint($width); }
            
        // Sanitize the height
        if ($height) 
          { $height = absint($height); }

        // Filter the request parameters
        $request_params = apply_filters( pls_get_merged_strings( array( 'pls_listings_request', $context ), '_', 'pre', false ), $args, $context_var );
        
        // Start off with a placeholder in case the plugin is not active or there is no API key...
        $listings_raw = PLS_Listing_Helper::$default_listing;

        // If plugin is active, grab listings intelligently...
        if (!pls_has_plugin_error()) {

            if ($featured_option_id) {
              $listings_raw = PLS_Listing_Helper::get_featured($featured_option_id, $args);

              if (empty($listings_raw['listings'])) {
                $listings_raw = PL_Listing_Helper::results($request_params);
                if($request_params['sort_by'] == 'total_images' && is_array($listings_raw['listings']))
                  shuffle($listings_raw['listings']);  // when sorting by pretty (the default) make them less obviously sorted
              }
            }

            elseif ($neighborhood_polygons) {
              $listings_raw = PL_Taxonomy_Helper::get_listings_polygon_name( array('neighborhood_polygons' => $neighborhood_polygons ) );

              // Do we ever fall through to here?  And if so, will this give the result we want?
              if (empty($listings_raw['listings'])) {
                $listings_raw = PL_Listing_Helper::results($request_params);
              }
            }

            else {
              $listings_raw = PL_Listing_Helper::results($request_params);
            }
        }

        /** Define variable which will contain the html string with the listings. */
        $return = '';

        /** Set the listing image attributes. */
        $listing_img_attr = array();
        if ($width)
          { $listing_img_attr['width'] = $width; }
        if ($height)
          { $listing_img_attr['height'] = $height; }

        /** Collect the html for each listing. */

        $listings_html = array();

        // filter listings before output
        if (isset($featured_listing_id)) {
          $listings_raw = apply_filters( $context . '_partial_get_listings', $listings_raw,  $featured_listing_id );
        }

        // For repeated use in the loop...
        $listing_cache = new PLS_Cache('Listing');

        // Curate the listing_data...
        foreach ($listings_raw['listings'] as $listing_data) {
            $listing_html = '';

            $cache_id = array('context' => $context, 'featured_option_id' => $featured_option_id, 'listing_id' => $listing_data['id']);
            if ($cached_listing_html = $listing_cache->get($cache_id)) {
                $listing_html = $cached_listing_html;
            }

            if (empty($listing_html)) {
                // Sort images and use the placeholder image if the property has no photo
                $property_images = !empty($listing_data['images']) ? $listing_data['images'] : array(array('url' => '', 'order' => 0));
                usort($property_images, array(__CLASS__, 'order_listing_images'));
                $listing_data['images'] = $property_images;

                $location = $listing_data['location'];
                $full_address = $location['address'] . ' ' . $location['region'] . ', ' . $location['locality'] . ' ' . $location['postal'];
              
                ob_start();
                ?>
                <div class="listing-item grid_8 alpha" itemscope itemtype="http://schema.org/Offer">

                  <div class="listing-thumbnail grid_3 alpha">
                    <a href="<?php echo @$listing_data['cur_data']['url']; ?>">
                      <?php echo PLS_Image::load($property_images[0]['url'], array('resize' => array('w' => 210, 'h' => 140), 'fancybox' => true, 'as_html' => true, 'html' => array('alt' => $listing_data['location']['full_address'], 'itemprop' => 'image'))); ?>
                    </a>
                  </div>

                  <div class="listing-item-details grid_5 omega">
                    <p class="listing-item-address h4" itemprop="name">
                      <a href="<?php echo PL_Pages::get_url($listing_data['id'], $listing_data); ?>" rel="bookmark" title="<?php echo $listing_data['location']['address'] ?>" itemprop="url">
                        <?php echo $listing_data['location']['address'] . ', ' . $listing_data['location']['locality'] . ' ' . $listing_data['location']['region'] . ' ' . $listing_data['location']['postal']  ?>
                      </a>
                    </p>

                    <div class="basic-details">
                      <ul>
                        <?php if (!empty($listing_data['cur_data']['beds'])): ?>
                          <li class="basic-details-beds p1"><span>Beds:</span> <?php echo @$listing_data['cur_data']['beds']; ?></li>
                        <?php endif; ?>

                        <?php if (!empty($listing_data['cur_data']['baths'])): ?>
                          <li class="basic-details-baths p1"><span>Baths:</span> <?php echo @$listing_data['cur_data']['baths']; ?></li>
                        <?php endif; ?>

                        <?php if (!empty($listing_data['cur_data']['half_baths'])): ?>
                          <li class="basic-details-half-baths p1"><span>Half Baths:</span> <?php echo @$listing_data['cur_data']['half_baths']; ?></li>
                        <?php endif; ?>

                        <?php if (!empty($listing_data['cur_data']['price'])): ?>
                          <li class="basic-details-price p1" itemprop="price"><span>Price:</span> <?php echo PLS_Format::number($listing_data['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true)); ?></li>
                        <?php endif; ?>

                        <?php if (!empty($listing_data['cur_data']['sqft'])): ?>
                          <li class="basic-details-sqft p1"><span>Sqft:</span> <?php echo PLS_Format::number($listing_data['cur_data']['sqft'], array('abbreviate' => false, 'add_currency_sign' => false)); ?></li>
                        <?php endif; ?>

                        <?php if (!empty($listing_data['rets']['mls_id'])): ?>
                          <li class="basic-details-mls p1"><span>MLS ID:</span> <?php echo @$listing_data['rets']['mls_id']; ?></li>
                        <?php endif; ?>
                      </ul>
                    </div>

                    <p class="listing-description p4">
                      <?php echo substr($listing_data['cur_data']['desc'], 0, 300); ?>
                    </p>

                  </div>

                  <div class="actions">
                    <a class="more-link" href="<?php echo PL_Pages::get_url($listing_data['id'], $listing_data); ?>" itemprop="url">View Property Details</a>
                    <?php echo PL_Favorite_Listings::placester_favorite_link_toggle(array('property_id' => $listing_data['id'])); ?>
                  </div>

                  <?php PLS_Listing_Helper::get_compliance(array('context' => 'inline_search', 'agent_name' => @$listing_data['rets']['aname'] , 'office_name' => @$listing_data['rets']['oname'])); ?>

                </div>
                <?php
                // Store the output...
                $listing_html = ob_get_clean();

                // Filter (pls_listing[_context]) the resulting HTML for a single listing
                $listing_html = apply_filters( pls_get_merged_strings( array( 'pls_listing', $context ), '_', 'pre', false ), $listing_html, $listing_data, $request_params, $context_var );

                // Cache the result...
                $listing_cache->save($listing_html, PLS_Cache::TTL_LOW);
            }
          
            // Append the HTML to an array -- this will be passed to the final filter
            $listings_html[] = $listing_html;

            // Merge all the listings HTML
            $return .= $listing_html;
        }

        // Wrap the listings HTML
        $return = pls_h(
            'section',
            array( 'class' => "pls-listings pls-listings " . pls_get_merged_strings( array( 'pls-listing', $context ), '-', 'pre', false ) ),
            $return
        );

        // Filter (pls_listings[_context]) the resulting HTML that contains the collection of listings
        $return = apply_filters( pls_get_merged_strings( array( 'pls_listings', $context ), '_', 'pre', false ), $return, $listings_raw, $listings_html, $request_params, $context_var );
        $cache->save($return);  
        
        return $return;
    }

    private static function order_listing_images ($a, $b) {
        if ($a['order'] == $b['order']) { return 0; }
        return ($a['order'] < $b['order']) ? -1 : 1;
    }
}
// end class
?>