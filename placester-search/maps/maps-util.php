<?php 

PLS_Map::init();
class PLS_Map {

	static $response;

	static $map_js_var;

	static $markers = array();

	public static function init() {
		add_action('wp_footer', array(__CLASS__, 'utilities'));
	}

	public static function get_lifestyle_controls ($map_args) {
		extract($map_args);
		ob_start();
		?>
		<div class="map_wrapper" style="position: relative">
				<div id="loading_overlay" class="loading_overlay" style="z-index: 50; display: none; position: absolute; width:<?php echo $width; ?>px; height:<?php echo $height; ?>px"><?php echo $loading_overlay ?></div>
				<div id="empty_overlay" class="empty_overlay" style="z-index: 50; display: none; position: absolute; width:<?php echo $width; ?>px; height:<?php echo $height; ?>px"><?php echo $empty_overlay ?></div>
				<div id="lifestyle_select_poi" class="lifestyle_select_poi" style="z-index: 50; display: none; position: absolute; width:<?php echo $width; ?>px;"><?php echo $lifestyle_select_poi ?></div>
				<div class="<?php echo $class ?>" id="<?php echo $canvas_id ?>" style="width:<?php echo $width; ?>px; height:<?php echo $height; ?>px"></div>
				<section class="lifestyle_form_wrapper" id="lifestyle_form_wrapper">
					<?php if ($show_lifestyle_controls): ?>
						<div class="location_wrapper">
							<?php echo implode(self::get_area_selectors($map_args), '') ?>
						</div>
						<div class="clear"></div>
					<?php endif ?>			
					<div class="checkbox_wrapper">
						<form>
						<?php if ($show_lifestyle_checkboxes): ?>
							<?php echo self::get_lifestyle_checkboxs($map_args); ?>
						<?php endif ?>
						</form>
					</div>
				</section>	
			</div>
		<?php
		return ob_get_clean();
	}

	private static function get_area_selectors ($map_args = array()) {

			$cache = new PLS_Cache('form');
			if ($result = $cache->get($map_args)) {
				return $result;
			}

		$response = array();
		$form_options = array();
		$form_options['locality'] = array_merge(array('false' => '---'), PLS_Plugin_API::get_location_list('locality'));
        $form_options['region'] = array_merge(array('false' => '---'), PLS_Plugin_API::get_location_list('region'));
        $form_options['postal'] = array_merge(array('false' => '---'),PLS_Plugin_API::get_location_list('postal')); 
        $form_options['neighborhood'] = array_merge(array('false' => '---'),PLS_Plugin_API::get_location_list('neighborhood')); 
        
	        $response['location'] = '<div class="location_select"><select name="location" class="location" style="width: 140px">
				<option value="locality">City</option>
				<option value="region">State</option>
				<option value="postal">Zip</option>
				<option value="neighborhood">Neighborhood</option>
			</select></div>';
	        $response['locality'] = '<div class="location_select_wrapper" style="display: none">' . pls_h( 'select', array( 'name' => 'location[locality]', 'class' => 'locality' ), pls_h_options( $form_options['locality'], wp_kses_post(@$_POST['location']['locality'] ), true )) . '</div>';
	        $response['region'] = '<div class="location_select_wrapper" style="display: none">' . pls_h( 'select', array( 'name' => 'location[region]', 'class' => 'region' ), pls_h_options( $form_options['region'], wp_kses_post(@$_POST['location']['region'] ), true )) . '</div>';
	        $response['postal'] = '<div class="location_select_wrapper" style="display: none">' . pls_h( 'select', array( 'name' => 'location[postal]', 'class' => 'postal' ), pls_h_options( $form_options['postal'], wp_kses_post(@$_POST['location']['postal'] ), true )) . '</div>';
	        $response['neighborhood'] = '<div class="location_select_wrapper" style="display: none">' . pls_h( 'select', array( 'name' => 'location[neighborhood]', 'class' => 'neighborhood' ), pls_h_options( $form_options['neighborhood'], wp_kses_post(@$_POST['location']['neighborhood'] ), true )) . '</div>';
	        if ($map_args['lifestyle_distance'] == 'miles') {
	        	$response['radius'] = '<div class="location_select"><select name="radius" class="radius" style="width: 140px">
											<option value="402">1/4 mile</option>
											<option value="804">1/2 mile</option>
											<option value="1207">3/4 mile</option>
											<option value="1609">1 mile</option>
											<option value="4828" selected>3 miles</option>
											<option value="8046">5 miles</option>
											<option value="16093">10 miles</option>
										</select></div>';
	        } else {
				$response['radius'] = '<div class="location_select"><select name="radius" class="radius" style="width: 140px">
											<option value="200">200 meters</option>
											<option value="500">500 meters</option>
											<option value="1000">1000 meters</option>
											<option value="2000">2000 meters</option>
											<option value="5000" selected>5000 meters</option>
											<option value="10000">10000 meters</option>
											<option value="20000">20000 meters</option>
										</select></div>';
	        }
	        $cache->save($response);
	        return $response;
	}

	private static function get_lifestyle_checkboxs () {
		$lifestyle_checkboxes = array('park', 'campground', 'food', 'restaurant', 'bar', 'bowling_alley', 'amusement_park', 'aquarium', 'movie_theater', 'stadium', 'school', 'university', 'pet_store', 'bus_station', 'subway_station', 'train_station', 'clothing_store', 'department_store', 'electronics_store', 'shopping_mall', 'grocery_or_supermarket');
		ob_start();
		?>
			<?php foreach ($lifestyle_checkboxes as $checkbox): ?>
				<section class="lifestyle_checkbox_item" id="lifestyle_checkbox_item">
					<input type="checkbox" name="<?php echo $checkbox ?>" id="<?php echo $checkbox ?>">
					<label for="<?php echo $checkbox ?>"><?php echo ucwords(str_replace('_', ' ', $checkbox)) ?></label>
				</section>
			<?php endforeach ?>	
		<?php
		return ob_get_clean();
	}

	public static function make_markers($listings, $marker_args, $map_args) {
		 self::$markers = array();
		 if ( isset($listings[0]) ) {
			foreach ($listings as $listing) {
				self::make_marker($listing, $marker_args);
			}
		} elseif (!empty($listings)) {
			self::make_marker($listings, $marker_args);
		} elseif ($map_args['featured_id']) {
			$api_response = PLS_Listing_Helper::get_featured($featured_option_id);
			foreach ($api_response['listings'] as $listing) {
				self::make_marker($listing, $marker_args);
			}
		} elseif ($map_args['auto_load_listings']) {
			$api_response = PLS_Plugin_API::get_listings($map_args['request_params']);
			foreach ($api_response['listings'] as $listing) {
				self::make_marker($listing, $marker_args);
			}
		}
	}

	public static function make_marker($listing = array(), $args = array()) {
		extract(self::process_marker_defaults($listing, $args), EXTR_SKIP);
		ob_start();
			?>
				pls_create_listing_marker(<?php echo json_encode($listing); ?>, <?php echo self::$map_js_var ?>);
			<?php
		self::$markers[] = trim(ob_get_clean());
	}

	public static function utilities () {

		// ob_start();
		wp_enqueue_script('map-object', trailingslashit(PLS_JS_URL) . 'scripts/map.js');
		wp_enqueue_script('poi-object', trailingslashit(PLS_JS_URL) . 'scripts/poi.js');
		wp_enqueue_script('status-object', trailingslashit(PLS_JS_URL) . 'scripts/status.js');
		wp_enqueue_script('neighborhood', trailingslashit(PLS_JS_URL) . 'scripts/neighborhood.js');
		wp_enqueue_script('lifestyle', trailingslashit(PLS_JS_URL) . 'scripts/lifestyle.js');
		// echo ob_get_clean();
	}

	public static function process_defaults ($args) {
		$defaults = array(
        	'lat' => '42.37',
        	'lng' => '-71.03',
        	'center_location' => false,
        	'zoom' => '14',
        	'width' => 300,
        	'height' => 300,
        	'canvas_id' => 'map_canvas',
        	'class' => 'custom_google_map',
        	'map_js_var' => 'pls_google_map',
        	'featured_id' => false,
        	'request_params' => '',
        	'auto_load_listings' => false,
        	'polygon_search' => false,
        	'life_style_search' => false,
        	'show_lifestyle_controls' => false,
        	'show_lifestyle_checkboxes' => false,
        	'loading_overlay' => '<div>Loading...</div>',
        	'empty_overlay' => '<div>No Results</div>',
        	'search_on_load' => false,
        	'polygon_options' => array(),
        	'ajax_form_class' => false,
        	'polygon' => false,
        	'polygon_click_action' => false,
        	'lifestyle_distance' => 'miles',
        	'search_class' => 'pls_listings_search_results',
        	'lifestyle_select_poi' => '<div style="width: 100%; background-color:rgba(0,0,0,0.5); color: white">Select a Point of Interest to start searching</div>'
        );
        $args = wp_parse_args( $args, $defaults );
        self::$map_js_var = $args['map_js_var'];	
        return $args;
	}

	public static function process_marker_defaults ($listing, $args) {
		if (isset($listing) && is_array($listing) && isset($listing['location']) && isset($listing['location']['coords'])) {
			if (isset($listing['location']['coords']['latitude'])) {
				$coords = $listing['location']['coords'];
				$args['lat'] = $coords['latitude'];
				$args['lng'] = $coords['longitude'];	
			} elseif (is_array($listing['location']['coords'])) {
				$coords = $listing['location']['coords'];
				$args['lat'] = $coords[0];
				$args['lng'] = $coords[1];	
			}
		}
		$defaults = array(
        	'lat' => '42.37',
        	'lng' => '71.03',
        );
        $args = wp_parse_args( $args, $defaults );
        return $args;		
	}

	//for compatibility
	public static function dynamic($listings = array(), $map_args = array(), $marker_args = array()) {
		return self::listings($listings, $map_args, $marker_args);
	}
	public static function listings($listings = array(), $map_args = array(), $marker_args = array()) {
		return PLS_Map_Listings::listings($listings, $map_args, $marker_args);
	}
	public static function neighborhood($listings = array(), $map_args = array(), $marker_args = array(), $polygon) {
		return PLS_Map_Polygon::polygon($listings, $map_args, $marker_args);	
	}
	public static function polygon($listings = array(), $map_args = array(), $marker_args = array()) {
		return PLS_Map_Polygon::polygon($listings, $map_args, $marker_args);	
	}
	public static function lifestyle($listings = array(), $map_args = array(), $marker_args = array()) {
		return PLS_Map_Lifestyle::lifestyle($listings, $map_args, $marker_args);	
	}
	public static function lifestyle_polygon($listings = array(), $map_args = array(), $marker_args = array()) {
		return PLS_Map_Lifestyle_Polygon::lifestyle_polygon($listings, $map_args, $marker_args);		
	}
}