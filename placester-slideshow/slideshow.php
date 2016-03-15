<?php 

/*
 * Slideshow - A slideshow that integrates with the Placester Plugin
 */

PLS_Slideshow::init();
class PLS_Slideshow {

    private static $default_img_url = '';
    public static $listings_to_delete = array();

    /*
     * Initializes the slideshow
     */
    public static function init() {
        // Set default image URL
        self::$default_img_url = (PLACESTER_PLUGIN_URL . "placester-slideshow/listing-1200x720.jpg");

        // For Wordpress 3.3.0
        if (!is_admin()) {
            add_action('init', array(__CLASS__,'enqueue'));
        }
    }

    public static function enqueue() {
        $slideshow_support = get_theme_support( 'pls-slideshow' );

        // Eventually, these will be set as args/from config...
        $slideshow_type = 'pls-slideshow-orbit';
        $slideshow_js_file = PLACESTER_PLUGIN_URL . 'placester-slideshow/orbit/jquery.orbit.js';
        $slideshow_css_file = PLACESTER_PLUGIN_URL . 'placester-slideshow/orbit/orbit.css';

        wp_register_script( $slideshow_type, $slideshow_js_file, array( 'jquery' ), NULL, false );
        wp_register_style( $slideshow_type, $slideshow_css_file);

        if ( is_array( $slideshow_support ) ) {
            if ( in_array( 'script', $slideshow_support[0] ) )
                wp_enqueue_script($slideshow_type);

            if ( in_array( 'style', $slideshow_support[0] ) ) {
                wp_enqueue_style($slideshow_type);
            }
            return;
        }

        wp_enqueue_script($slideshow_type);
        wp_enqueue_style($slideshow_type);
    }

    /**
     * Slideshow
     * 
     * @param string $args 
     * @param mixed $data 
     * @static
     * @access public
     * @return void
     */
    public static function slideshow( $args = '' ) {

        /** Define the default argument array */
        $defaults = array(
            'animation' => 'fade', // fade, horizontal-slide, vertical-slide, horizontal-push
            'animationSpeed' => 800, // how fast animtions are
            'timer' => true, // true or false to have the timer
            'advanceSpeed' => 4000, // if timer is enabled, time between transitions 
            'pauseOnHover' => true, // if you hover pauses the slider
            'startClockOnMouseOut' => true, // if clock should start on MouseOut
            'startClockOnMouseOutAfter' => 500, // how long after MouseOut should the timer start again
            'directionalNav' => true, // manual advancing directional navs
            'captions' => true, // do you want captions?
            'captionAnimation' => 'fade', // fade, slideOpen, none
            'captionAnimationSpeed' => 800, // if so how quickly should they animate in
            'afterSlideChange' => 'function(){}', // empty function
            'bullets' => 'false', // true or false to activate the bullet navigation
            'width' => 620,
            'height' => 300,
            'container_height' => false,
            'context' => '',
            'context_var' => false,
            'featured_option_id' => false,
            'allow_user_slides' => false,
            'user_slides_header_id' => false,
            'listings' => 'limit=5&sort_by=price',
            'data' => false,
            'post_id' => false,
            'post_meta_key' => false,
            'fluid' => false,
            'span_not_img' => false
        );
        $args = wp_parse_args( $args, $defaults );
        
        /** Check cache, return something is there **/
        $cache = new PLS_Cache('slide');
        if ($result = $cache->get($args)) {
            return $result;
        }
        
        /** Extract all args for easy usage **/
        extract($args, EXTR_SKIP); 

        /** If the slideshow data is null or not an array AND the plugin is working, try to fetch the proper data... **/
        if ( (!$data || !is_array($data)) && !pls_has_plugin_error() ) {
            /** Data assumed to take this form. */
            $data = array('images' => array(),'links' => array(),'captions' => array(),'titles' => array());

            // If the calling theme allows user input, get slideshow config option...
            if ($allow_user_slides && $user_slides_header_id) {
                $slides = pls_get_option($user_slides_header_id, array());

                // Check to see if slides are set to custom, but are empty 
                $custom_but_empty = isset($slides[0]) && ($slides[0]['type'] == 'custom') && empty($slides[0]['image']);
                
                // Populate slides when '$custom_but_empty' is true OR when no slides exist...
                if ( $custom_but_empty || empty($slides) ) {
                    $slides = self::empty_slides_and_add_random_listings();
                }

                foreach ($slides as $index => $slide) {
                    switch ($slide['type']) {
                        case 'listing':
                            unset($slide['html'], $slide['image'], $slide['type'], $slide['link']);
                            
                            // In this case, the slide's remaining key will correspond to it's property ID...
                            $property_id = key($slide);
                            $api_response = PL_Listing_Helper::details(array('property_ids' => array($property_id)));
                            
                            if (!empty($api_response['listings']) && $api_response['listings'][0]['id'] === false ) {
                                self::$listings_to_delete[] = $property_id;
                            }
                            
                            if ($api_response['total'] == '1') {
                                $listing = $api_response['listings'][0];
                                $first_valid_img_url = null;

                                // Overwrite the placester url with the local url...
                                $listing_url = PL_Pages::get_url( $listing['id'], $listing );
                                $data['links'][] = $listing_url;

                                // Try to retrieve the image url if order is set...
                                if (is_array($listing['images']) && isset($listing['images'][0]['order'])) {
                                    foreach ($listing['images'] as $key => $image) {
                                        if ($image['order'] == 1) {
                                            $data['images'][$index] = $image['url'];
                                            // break, just in case the listing has more than one '1' in the 'order' param
                                            break;
                                        }
                                        // Record the first valid image URL in case no image has the top order...
                                        if (!isset($first_valid_img_url) && isset($image['url'])) {
                                            $first_valid_img_url = $image['url'];
                                        }
                                    }
                                }
                                
                            	// If image still isn't set, use first valid image URL discovered above, or just set to default...
                            	if (empty($data['images'][$index])) {
                                    $data['images'][$index] = isset($first_valid_img_url) ? $first_valid_img_url : self::$default_img_url;
                                }

                                $data['type'][] = 'listing';
                                $data['listing'][] = $listing;
                                
                                /** Get the listing caption **/
                                $data['captions'][] = trim( self::render_listing_caption($listing, $index) );
                                $data['titles'][] = $listing['location']['address'];
                            }
                            break;

                        case 'custom':
                            $is_empty = empty($slide['image']) && empty($slide['link']) && empty($slide['image']) && empty($slide['html']);

                            // Only include a custom slide if it's not entirely empty...
                            if (!$is_empty) {
                                $data['images'][] = $slide['image'];
                                $data['links'][] = $slide['link'];
                                $data['type'][] = 'custom';
                                $data['captions'][] = trim( self::render_custom_caption($slide['html'], $index) );
                                $data['titles'][] = strip_tags($slide['html']);
                            }
                            break;
                    }
                }
            } 
            else {
                if ( !empty($args['post_id']) && !empty($args['post_meta_key']) ) {
                    $api_response = PLS_Listing_Helper::get_featured_from_post($args['post_id'], $args['post_meta_key']);
                }
                elseif ($featured_option_id) {
                    $api_response = PLS_Listing_Helper::get_featured($featured_option_id);
                } 

                if (empty($api_response['listings'])) {
                    $api_response = PL_Listing_Helper::results($listings);
                }
                
                foreach ($api_response['listings'] as $index => $listing) {
                    if (empty($listing['id'])) { continue; }
                    $listing_url = PL_Pages::get_url( $listing['id'], $listing );
                    
                    /** Overwrite the placester url with the local url. */
                    $data['links'][] = $listing_url;
                    $data['images'][] = !empty($listing['images']) ?  $listing['images'][0]['url'] : self::$default_img_url;
                    $data['listing'][] = $listing;

                    // Get the listing caption
                    $listing_caption = trim( self::render_listing_caption($listing, $index) );
                    $data['titles'][] = $listing['location']['address'];

                    // Add a filter for a single caption, to be edited via a template
                    $single_caption = apply_filters( pls_get_merged_strings( array( 'pls_slideshow_single_caption', $context ), '_', 'pre', false ), $listing_caption, $listing, $context, $context_var, $index );
                    $data['captions'][] = $single_caption;
                }
            }
        }

        /** Filter the data array */
        $data = apply_filters( pls_get_merged_strings( array( 'pls_slideshow_data', $context ), '_', 'pre', false ), $data, $context, $context_var );

        /** Create the slideshow */
        $html = array('slides' => '', 'captions' => '');

        if (is_array($data['images'])) {
	        foreach ($data['images'] as $index => $slide_src) {
	            $extra_attr = array();

	            /** Save the caption and the title attribute for the img. */
	            if ( isset($data['captions'][$index]) ) {
	                $html['captions'] .= $data['captions'][$index];
	                $extra_attr['data-caption'] = "#caption-{$index}";
	            }
	            
	            if (isset($data['type'][$index])) {
	                // Get image, but only Dragonfly listing images
                    switch ($data['type'][$index]) {
                        case "listing":
                            $slide_src = PLS_Image::load($slide_src, array('resize' => array('w' => $width, 'h' => $height), 'fancybox' => false, 'as_html' => false));
                            break;
                        case "custom":
                            $slide_src = PLS_Image::load($slide_src, array('allow_resize' => false, 'fancybox' => false, 'as_html' => false));
                            break;
                    }
	            }
	            
	            /** Create the img or span element. */
                if ($span_not_img) {
                    $slide = '<span style="background-image: url(' . $slide_src . ');"></span>';
                } else {
                    $slide = pls_h_img($slide_src, $data['titles'][$index], $extra_attr);
                }
	            
	
	            /** Wrap it in an achor if the anchor exists. */
	            if ( isset( $data['links'][$index] ) )
	                $slide = pls_h_a( $data['links'][$index], $slide, $extra_attr );
	
	            $html['slides'] .= $slide;
			}
		}

        /** Combine the HTML **/
        $html = pls_h_div($html['slides'], array( 'id' => 'slider', 'class' => 'orbitSlider' )) . $html['captions'];
        
        /** Filter the HTML array */
        $html = apply_filters( pls_get_merged_strings( array( 'pls_slideshow_html', $context ), '_', 'pre', false ), $html, $data, $context, $context_var, $args );

        if (!$container_height) {
          $container_height = $height;
        }

        /** Render the necessary inline CSS... */
        $css_args = array('width' => $width, 'height' => $height, 'container_height' => $container_height);
        $css = self::render_inline_css($css_args);

        /** Render the necessary inline JS... **/
        $args['data'] = is_string($data) ? $data : '';  // For compatibility...    
        $js = self::render_inline_js($args);
        
        /** Filter inline JS **/
        $js = apply_filters( pls_get_merged_strings( array( 'pls_slideshow_js', $context ), '_', 'pre', false ), $js, $html, $data, $context, $context_var );
        
        /** Filter the final output **/
        $full_slideshow = apply_filters( pls_get_merged_strings( array( 'pls_slideshow', $context ), '_', 'pre', false ), $css . $html . $js, $html, $js, $data, $context, $context_var, $args );
        
        /** Cache rendered slideshow for future retrieval **/
        $cache->save($full_slideshow);
        
        return $full_slideshow;
    }

    /*
     * 
     */
    public static function prepare_single_listing ($listing = false, $width = 600, $height = 300 ) {
        // Start with an empty array...
        $slide_array = array('images' => array());

        if ($listing && isset($listing['images'])) {
            // For listings with no images or a value that is not an array...
            if (empty($listing['images']) || !is_array($listing['images'])) {
                // Create default single image slide array
                $slide_array['images'][] = self::$default_img_url;
            }
            else {
                foreach ($listing['images'] as $image) {
                    $slide_array['images'][] = PLS_Image::load($image['url'], array('resize' => array('w' => $width, 'h' => $height), 'fancybox' => false, 'as_html' => false));
                    $slide_array['captions'] = array('');
                }
            }
        }
        else {
            $slide_array = false;
        }

        return $slide_array;
    }

    /* 
     * Return the first six random listings -- if no listings are associated with this API key AND demo data is off, 
     * simply return an empty array as there are no listings to include in the slideshow.
     */
    private static function empty_slides_and_add_random_listings() {
        $slides = array();
        $api_response = PL_Listing_Helper::results(array('limit' => 6, 'offset' => 10));
        
        foreach ($api_response['listings'] as $listing) {
          $slides[] = array(
            'type' => 'listing',
            $listing['id'] => $listing['location']['address']
          );
        }

        return $slides;
    }

    /*
     * Renders and returns the caption for a listing slide
     */
    private static function render_listing_caption ($listing, $slide_index) {
        // NOTE: This will eventually be read from some config or class var...
        $css_class = 'orbit-caption';

        ob_start();
        ?>
            <div id="caption-<?php echo $slide_index; ?>" class="<?php echo $css_class; ?>">
                <p class="caption-title"><a href="<?php echo $listing['cur_data']['url'] ?>"><?php echo $listing['location']['address']; ?></a></p>
                <p class="caption-subtitle"><?php printf( ' <span class="price">%1$s beds</span>, <span class="baths">%2$s baths</span>', $listing['cur_data']['beds'], $listing['cur_data']['baths']); ?></p>
                <a class="button details" href="<?php echo $listing['cur_data']['url']; ?>"><span><?php 'See Details' ?></span></a>
            </div>
        <?php

        return ob_get_clean();
    }

    /*
     * Renders and returns the caption for a custom slide
     */
    private static function render_custom_caption ($content, $slide_index) {
        // NOTE: This will eventually be read from some config or class var...
        $css_class = 'orbit-caption';

        ob_start();
        ?>
            <div id="caption-<?php echo $slide_index; ?>" class="<?php echo $css_class; ?>">
                <?php echo $content; ?>
            </div>
        <?php

        return ob_get_clean();
    }

    /*
     * Renders and returns any necessary inline styling
     */
    private static function render_inline_css ($args = array()) {
        extract($args);

        ob_start();
        ?>
            <style type="text/css">
                .orbit-wrapper {
                    width:<?php echo $width; ?>px !important;
                    height:<?php echo $container_height; ?>px !important;
                    overflow: hidden;
                }
                #slider, #slider a {
                    width:<?php echo $width; ?>px !important;
                    height:<?php echo $height; ?>px !important;
                    overflow: hidden;
                }
                #slider img {
                    width:<?php echo $width; ?>px !important;
                    height:<?php echo $height; ?>px !important;
                    background: #000 url('<?php echo PLS_EXT_URL; ?>orbit/orbit/loading.gif') no-repeat center center;
                }
            </style>
        <?php 
        
        return ob_get_clean();
    }

    /*
     * Renders and returns any necessary inline JavaScript 
     */
    private static function render_inline_js ($args = array()) {
        extract($args);

        ob_start();    
        ?>
            <script type="text/javascript">
            jQuery(window).load(function($) {
                jQuery('#slider').orbit({
                    animation: '<?php echo $animation; ?>', // fade, horizontal-slide, vertical-slide, horizontal-push
                    animationSpeed: <?php echo $animationSpeed; ?>, // how fast animations are
                    timer: <?php echo $timer ?>, // true or false to have the timer
                    advanceSpeed: <?php echo $advanceSpeed; ?>, // if timer is enabled, time between transitions 
                    pauseOnHover: <?php echo $pauseOnHover; ?>, // if you hover pauses the slider
                    startClockOnMouseOut: <?php echo $startClockOnMouseOut; ?>, // if clock should start on MouseOut
                    startClockOnMouseOutAfter: <?php echo $startClockOnMouseOutAfter; ?>, // how long after MouseOut should the timer start again
                    directionalNav: <?php echo $directionalNav; ?>, // manual advancing directional navs
                    captions: <?php echo $captions; ?>, // do you want captions?
                    captionAnimation: '<?php echo $captionAnimation; ?>', // fade, slideOpen, none
                    captionAnimationSpeed: <?php echo $captionAnimationSpeed; ?>, // if so how quickly should they animate in
                    bullets: <?php echo ( $bullets ? 'true' : 'false' ); ?>, // true or false to activate the bullet navigation
                    afterSlideChange: <?php echo ( !empty($afterSlideChange) ? $afterSlideChange : 'false' ); ?>,
                    width: 620,
                    height: 300,
                    context: '',
                    context_var: false,
                    listings: '<?php echo $listings ?>',
                    data: '<?php echo $data; ?>',
                    fluid: <?php echo $fluid ? 'true' : 'false'; ?>
            
                });
            });
            </script>
        <?php

        return ob_get_clean();
    }

//end of class
}
