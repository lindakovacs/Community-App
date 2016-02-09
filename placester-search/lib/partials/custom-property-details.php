<?php 

class PLS_Partials_Property_Details {
	
	public static function init ($content) {
		
		global $post;

	    if ($post->post_type == 'property') {

	    	$html = '';

          	$listing_data = PLS_Plugin_API::get_listing_in_loop();
          
			// re-order images by assigned order
   			$property_images = ( is_array($listing_data['images']) ? $listing_data['images'] : array() );
			usort($property_images, array('PLS_Partials_Property_Details','sort_images_by_order'));
			// reset the images
			$listing_data['images'] = $property_images;

          	// Problems with API key or inconsistent data lead to notices due to null listings
          	if (!is_null( $listing_data)) {
		    	$listing_data['location']['full_address'] = $listing_data['location']['address'] . ' ' . $listing_data['location']['locality'] . ' ' . $listing_data['location']['region'];
		    	
		    	// This has to happen here to ensure it's not filtered out by whatever might be filtering this output...
		    	echo PLS_Plugin_API::log_snippet_js('listing_view', array('prop_id' => $listing_data['id']));

		        ob_start();
		        ?>
					<h2 itemprop="name" itemscope itemtype="http://schema.org/PostalAddress">
						<span itemprop="streetAdress"><?php echo $listing_data['location']['address']; ?></span> <span itemprop="addressLocality"><?php echo $listing_data['location']['locality']; ?></span>, <span itemprop="addressRegion"><?php echo $listing_data['location']['region']; ?></span>
					</h2>
	
					<?php echo PLS_Plugin_API::placester_favorite_link_toggle(array('property_id' => $listing_data['id'], 'add_text' => 'Add To Favorites', 'remove_text' => 'Remove From Favorites')); ?>
	
					<p itemprop="price"><?php echo PLS_Format::number($listing_data['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true)); ?> <span><?php echo PLS_Format::translate_lease_terms($listing_data); ?></span></p>
	
					<p class="listing_type"><?php if(isset($listing_data['zoning_types'][0]) && isset($listing_data['purchase_types'][0])) { echo ucwords(@$listing_data['zoning_types'][0] . ' ' . @$listing_data['purchase_types'][0]); } ?></p>

					<div class="clearfix"></div>
	
					<?php if ($listing_data['images']): ?>
						<div class="theme-default property-details-slideshow">
							<?php echo PLS_Image::load($listing_data['images'][0]['url'], array('resize' => array('w' => 590, 'h' => 300), 'fancybox' => false, 'as_html' => true, 'html' => array('itemprop' => 'image'))); ?>
							<?php // echo PLS_Slideshow::slideshow( array( 'anim_speed' => 1000, 'pause_time' => 15000, 'control_nav' => true, 'width' => 620, 'height' => 300, 'context' => 'home', 'data' => PLS_Slideshow::prepare_single_listing($listing_data) ) ); ?>
						</div>

						<div class="details-wrapper grid_8 alpha">
							<div id="slideshow" class="clearfix theme-default left bottomborder">
								<div class="grid_8 alpha">
									<ul class="property-image-gallery grid_8 alpha">
										<?php foreach ($listing_data['images'] as $images): ?>
											<li><?php echo PLS_Image::load($images['url'], array('resize' => array('w' => 100, 'h' => 75), 'fancybox' => true, 'as_html' => true, 'html' => array('itemprop' => 'image'))); ?></li>
										<?php endforeach ?>
									</ul>
								</div>

							</div>
						</div>
					<?php endif ?>
	                
	                <div class="basic-details grid_8 alpha">
	                    <ul>
	                        <li><span>Beds: </span><?php echo $listing_data['cur_data']['beds'] ?></li>
	                        <li><span>Baths: </span><?php echo $listing_data['cur_data']['baths'] ?></li>
	                        <?php if (isset($listing_data['cur_data']['half_baths']) && ($listing_data['cur_data']['half_baths'] != null)): ?>
	                        	<li><span>Half Baths: </span><?php echo $listing_data['cur_data']['half_baths'] ?></li>
	                        <?php endif; ?>
	                        <li><span>Square Feet: </span><?php echo PLS_Format::number($listing_data['cur_data']['sqft'], array('abbreviate' => false, 'add_currency_sign' => false)); ?></li>
	                        <?php if (isset($listing_data['cur_data']['avail_on']) && ($listing_data['cur_data']['avail_on'] != null)): ?>
	                        	<li itemprop="availability"><span>Available: </span><?php echo @$listing_data['cur_data']['avail_on'] ?></li>
	                        <?php endif; ?>
	                        <li>Property Type: <?php echo PLS_Format::translate_property_type($listing_data); ?></li>
	                        <?php if (isset($listing_data['rets']) && isset($listing_data['rets']['mls_id'])): ?>
	                        	<li><span>MLS #: </span><?php echo $listing_data['rets']['mls_id'] ?></li>	
	                        <?php endif; ?>
	                    </ul>
	                </div>
	
	                <div class="details-wrapper grid_8 alpha">
	                    <h3>Property Description</h3>
	                    <?php if (!empty($listing_data['cur_data']['desc'])): ?>
	                        <p itemprop="description"><?php echo $listing_data['cur_data']['desc']; ?></p>
	                    <?php else: ?>
	                        <p> No description available </p>
	                    <?php endif ?>
	                </div>
	
	                
	
	                <?php $amenities = PLS_Format::amenities_but($listing_data, array('half_baths', 'beds', 'baths', 'url', 'sqft', 'avail_on', 'price', 'desc')); ?>
	               
	                <?php if (!empty($amenities['list'])): ?>
	                  <div class="amenities-section grid_8 alpha">
	                    <h3>Listing Amenities</h3>
	                    <ul>
	                    <?php $amenities['list'] = PLS_Format::translate_amenities($amenities['list']); ?>
	                      <?php foreach ($amenities['list'] as $amenity => $value): ?>
	                        <li><span><?php echo $amenity; ?></span> <?php echo $value ?></li>
	                      <?php endforeach ?>
	                    </ul>
		                </div>	
	                <?php endif ?>
	                <?php if (!empty($amenities['ngb'])): ?>
		                <div class="amenities-section grid_8 alpha">
		                  <h3>Local Amenities</h3>
	                    <ul>
		                  <?php $amenities['ngb'] = PLS_Format::translate_amenities($amenities['ngb']); ?>
		                    <?php foreach ($amenities['ngb'] as $amenity => $value): ?>
		                      <li><span><?php echo $amenity; ?></span> <?php echo $value ?></li>
		                    <?php endforeach ?>
		                  </ul>
		                </div>
	                <?php endif ?>
	                
	                <?php if (!empty($amenities['uncur'])): ?>
		                <div class="amenities-section grid_8 alpha">
		                  <h3>Custom Amenities</h3>
	                    <ul>
		                  <?php $amenities['uncur'] = PLS_Format::translate_amenities($amenities['uncur']); ?>
		                    <?php foreach ($amenities['uncur'] as $amenity => $value): ?>
		                      <li><span><?php echo $amenity; ?></span> <?php echo $value ?></li>
		                    <?php endforeach ?>
	                    </ul>
		                </div>	
	                <?php endif ?>
	
		            <div class="map-wrapper grid_8 alpha">
		                <h3>Property Map</h3>
                        <script type="text/javascript">
                          jQuery(document).ready(function( $ ) {
                            var map = new Map();
                            var listing = new Listings({
                              single_listing : <?php echo json_encode($listing_data) ?>,
                              map: map
                            });
                            map.init({
                              type: 'single_listing', 
                              listings: listing,
                              lat : <?php echo json_encode($listing_data['location']['coords'][0]) ?>,
                              lng : <?php echo json_encode($listing_data['location']['coords'][1]) ?>,
                              zoom : 14
                            });
                            listing.init();
                          });
     	                </script>
	                    <div class="map">
     	                  <?php echo PLS_Map::dynamic($listing_data, array('lat'=>$listing_data['location']['coords'][0], 'lng'=>$listing_data['location']['coords'][1], 'height' => 250, 'zoom' => 16)); ?>
	                    </div>
		            </div>
	              
	              	<?php PLS_Listing_Helper::get_compliance(array('context' => 'listings', 'agent_name' => @$listing_data['rets']['aname'] , 'office_name' => @$listing_data['rets']['oname'])); ?>
	
		      	<?php
	      		// Store output...
	        	$html = ob_get_clean();
          	}

          	// Enable Lead Capture
			$lead_capture_enable = pls_get_option('pd-lc-enable');
			if ($lead_capture_enable == 1 && !is_user_logged_in()) {
			  ob_start();
	      do_shortcode('[pl_lead_capture_form]');
        $lead_capture_block = ob_get_clean();
        	}

	        $html = apply_filters('property_details_filter', $html, $listing_data);

	        // Add lead capture block to HTML
	        if (isset($lead_capture_block)) {
	        	$html = $lead_capture_block . $html;
	        }
	        
	        return $html; 
      	}

      	// Post is not of type property, so just return what was initially passed in...
    	return $content;
	}

	private static function sort_images_by_order($a, $b) {
 		if ($a['order'] == $b['order']) {
 			return 0;
 		}
  		return ($a['order'] < $b['order']) ? -1 : 1;
  	}
}