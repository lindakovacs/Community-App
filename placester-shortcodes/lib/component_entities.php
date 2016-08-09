<?php

/**
 * Generate output for the shortcodes and templates
 *
 */

PL_Component_Entity::init();

class PL_Component_Entity {

	public static $defaults = array( 'twentyten', 'twentyeleven' );

	public static $listing;

	public static $form_html;

	public static $neighborhood_term;

	public static $slideshow_caption_index;
	
	public static $template_tags = array();

	public static $listing_tags = array(
			'price'			=> array('help' => 'Property price'),
			'sqft'			=> array('help' => 'Total square feet'),
			'beds'			=> array('help' => 'Number of bedrooms'),
			'baths'			=> array('help' => 'Number of bathrooms'),
			'half_baths'	=> array('help' => 'Number of half bathrooms'),
			'avail_on'		=> array('help' => 'Date the property will be available'),
			'url'			=> array('help' => 'Link to page for the listing'),
			'address'		=> array('help' => 'Street address'),
			'locality'		=> array('help' => 'Locality'),
			'region'		=> array('help' => 'Region'),
			'postal'		=> array('help' => 'Zip/postal code'),
			'neighborhood'	=> array('help' => 'Neighborhood'),
			'county'		=> array('help' => 'County'),
			'country'		=> array('help' => 'Country'),
			//'coords'		=> array('help' => ''),
			'unit'			=> array('help' => 'Unit'),
			'full_address'	=> array('help' => 'Full address'),
			'email'			=> array('help' => 'Email address for this listing'),
			'phone'			=> array('help' => 'Contact phone'),
			'desc'			=> array('help' => 'Property description.  You can use the <code>maxlen</code> attribute to override default description length, for example: <code>[desc maxlen=\'140\']</code>'),
			'image'			=> array('help' => 'First property image'),
			'image_url'		=> array('help' => 'Image URL for the listing if one exists. You can use the optional <code>index</code> attribute (defaults to 0, the first image) to specify the index of the listing image and <code>placeholder</code> to specify the URL of an image to use if the listing does not have an image, for example: <code>[image_url index=\'1\' placeholder=\'http://www.domain.com/path/to/image\']</code>'),
			'mls_id'		=> array('help' => 'MLS #'),
			'map'			=> array('help' => ''),
			'listing_type'	=> array('help' => 'Type of listing'),
			'gallery'		=> array('help' => 'Image gallery'),
			'amenities'		=> array('help' => 'List of amenties'),
			'price_unit'	=> array('help' => 'Unit price'),
			'compliance'	=> array('help' => 'MLS compliance statement for an individual listing'),
			'favorite_link_toggle' => array('help' => 'Link to add/remove from favorites'),
			'custom'		=> array('help' => 'Use to display a custom listing attribute.<br />
Format is as follows:<br />
<code>[custom group=\'group_name\' attribute=\'some_attribute_name\' type=\'text\' value=\'some_value\']</code><br />
where:<br />
<code>group</code> - The group identifier if the listing attribute is part of a group. Possible values are <code>location</code>, <code>rets</code>, <code>metadata</code>, <code>uncur_data</code>.<br />
<code>attribute</code> - (required) The unique identifier of the listing attribute.<br />
<code>type</code> - (optional, default is \'text\') Can be <code>text</code>, <code>currency</code>, <code>list</code>. Used to indicate how the attribute should be formatted.<br />
<code>value</code> - (optional) Indicates text to be displayed if the listing attribute is empty.<br />
'),
			'if'			=> array('help' => 'Use to conditionally display some content depending on the value of a listing\'s attribute.<br />
Format is as follows:<br />
<code>[if group=\'group_name\' attribute=\'some_attribute_name\' value=\'some_value\']some HTML that will be displayed if the condition is true[/if]</code><br />
where:<br />
<code>group</code> - The group identifier if the listing attribute is part of a group. Possible values are <code>location</code>, <code>rets</code>, <code>metadata</code>, <code>uncur_data</code>.<br />
<code>attribute</code> - (required) The unique identifier of the listing attribute.<br />
<code>value</code> - (optional) By default the condition is true if the attribute has any value other than being empty. If you wish to test if the attribute matches a specific value, then set that value in this parameter.<br />
For example, to only display bedroom and bathroom details if the property is residential:<br />
<code>[if attribute=\'compound_type\' value=\'res_sale\']Beds: [beds] Baths: [baths][/if]</code><br />
To add some text to your listings:<br />
<code>[if group=\'rets\' attribute=\'aid\' value=\'MY_MLS_AGENT_ID\']&lt;span&gt;Featured Listing&lt;/span&gt;[/if]</code>'),
	);

	private static $shortcode_groups = array();
	private static $current_shortcode_group = '';



	public static function init() {
		// add_action('init', array( __CLASS__, 'filter_featured_context' ) );

		// TODO: make dynamic function control over templates
		// currently they have different logic and diff input parameters

		// add formatting for individual listings here because they are fetched using ajax
		$search_listings_templates = PL_Shortcode_CPT::template_list('search_listings', true);
		foreach ($search_listings_templates as $id => $attr) {
			add_filter( 'pls_listings_list_ajax_item_html_search_listings_' . $id, array(__CLASS__,'pls_listings_list_ajax_item_html_callback'), 10, 3 );
		}
		if (!has_filter('pls_listings_search_listings_shortcode')) {
			add_filter( 'pls_listings_list_ajax_item_html_search_listings_shortcode', array(__CLASS__,'pls_listings_list_ajax_item_html_callback'), 10, 3 );
		}

		// add formatting for individual listings here because they are fetched using ajax
		$static_listings_templates = PL_Shortcode_CPT::template_list('static_listings', true);
		foreach ($static_listings_templates as $id => $attr) {
			add_filter( 'pls_listings_list_ajax_item_html_static_listings_' . $id, array(__CLASS__, 'pls_listings_list_ajax_item_html_callback'), 10, 3 );
		}
		if (!has_filter('pls_listings_static_listings_shortcode')) {
			add_filter( 'pls_listings_list_ajax_item_html_static_listings_shortcode', array(__CLASS__, 'pls_listings_list_ajax_item_html_callback'), 10, 3 );
		}

		$neighborhood_templates = PL_Shortcode_CPT::template_list('pl_neighborhood', true);
		foreach ($neighborhood_templates as $id => $attr) {
			add_filter( 'pls_neighborhood_html_' . $id, array(__CLASS__, 'neighborhood_templates'), 10, 4 );
		}

		add_action('wp_footer', array(__CLASS__, 'add_js'));
	}

	public static function featured_listings_entity( $atts, $filters = '' ) {
		if (!empty($atts['id'])) {
			// if we are a custom shortcode fetch the record so we can display the correct options
			$options = PL_Shortcode_CPT::get_shortcode_options('featured_listings', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
				if($property_ids = self::get_property_ids($atts['id']))
					$atts['property_ids'] = array_keys($property_ids);
			}
			else {
				unset($atts['id']);
			}
		}

		if (empty($atts['property_ids'])) {
			$limit_default = 6;	// if none have been explicitly selected, show 6
			$sort_by_default = 'total_images';	// pretty ones
		}
		else {
			$limit_default = 0;	// if selected, show all
			$sort_by_default = 'cur_data.price';
		}

		$atts = wp_parse_args($atts, array('limit' => $limit_default, 'sort_by' => $sort_by_default));
		$atts['context'] = 'featured_listings_'.(empty($atts['context']) ? 'shortcode' : $atts['context']);
		
		if (!has_filter('pls_listings_' . $atts['context'])) {
			add_filter('pls_listings_' . $atts['context'], array(__CLASS__,'pls_listings_callback'), 10, 5);
			add_filter('pls_listing_' . $atts['context'], array(__CLASS__,'pls_listing_callback'), 10, 4);
		}

		return PLS_Partials::get_listings($atts);
	}

	/**
	 * Generate static_listings shortcode output
	 */
	public static function static_listings_entity( $atts, $filters = '' ) {

		if (empty($atts['id'])) {
			// default filter options
			$filters_string = '';
		}
		else {
			// if we are a custom shortcode fetch the record so we can display the correct filters
			// for the js
			$listing_filters = PL_Shortcode_CPT::get_shortcode_filters('static_listings', $atts['id']);
			$filters_string = self::convert_filters( $listing_filters );
			// and template and other attributes
			$options = PL_Shortcode_CPT::get_shortcode_options('static_listings', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}
		// get default values
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs('static_listings');
		foreach ($sc_attrs['options'] as $key=>$vals) {
			if (empty($atts[$key]) && !empty($vals['default'])) {
				$atts[$key] = $vals['default'];
			}
			if (!empty($atts[$key]) && $vals['type']=='multiselect') {
				if (!is_array($atts[$key])) {
					$atts[$key] = array_map('trim',explode(",", $atts[$key]));
				}
				$values = array();
				$optvals = get_option('pl_static_listings_formval_'.$key, $vals['options']);
				foreach($atts[$key] as $okey) {
					if (!empty($optvals[$okey])) {
						$values[$okey] = $optvals[$okey];
					}
				}
				if (empty($values)) {
					unset($atts[$key]);
				}
				else {
					$atts[$key] = $values;
				}
			}
		}
		$atts = wp_parse_args($atts, array('id' => 0, 'query_limit' => 5, 'featured_id' => 'custom', 'sort_by' => 'cur_data.price', 'table_id' => 'placester_listings_list'));
		$atts['context'] = 'static_listings_' . (empty($atts['context']) ? 'shortcode' : $atts['context']);
		
		// set limit per page if any
		if( ! empty( $atts['query_limit'] ) ) {
			global $pl_listings_query_limit;
			$pl_listings_query_limit = $atts['query_limit'];
			// TODO init the js directly instead
			add_action( 'listings_limit_default', array( __CLASS__, 'add_length_limit_default'  ));
			unset ( $pl_listings_query_limit );
		}

		if (!has_filter('pls_listings_' . $atts['context'])) {
			// formatting filter for individual listings already registered above
			add_filter('pls_listings_' . $atts['context'], array(__CLASS__,'pls_listings_callback'), 10, 5);
		}

		// we support a filter for wrapping the whole shortcode here
		// TODO: move applyfilter to blueprint 
		ob_start();
		self::hide_unnecessary_controls($atts);
		self::add_to_group('static_listings', $atts, $filters.$filters_string);
		echo "<a id={$atts['table_id']}_anchor name={$atts['table_id']}_anchor></a>";
		PLS_Partials_Get_Listings_Ajax::load($atts);
		// support shortcodes in the header or footer
		$return = ob_get_clean();
		return apply_filters('pls_listings_' . $atts['context'], $return, array(), '', $atts, null);
	}

	public static function add_length_limit_default() {
		global $pl_listings_query_limit;

		echo "limit_default: " . $pl_listings_query_limit . ",";
	}


	/**
	 * Generate search_listings shortcode output
	 */
	public static function search_listings_entity( $atts, $filters = '' ) {

		if (empty($atts['id'])) {
			// default filter options
			$filters_string = '';
		}
		else {
			// if we are a custom shortcode fetch the record so we can display the correct filters
			// for the js
			$listing_filters = PL_Shortcode_CPT::get_shortcode_filters('search_listings', $atts['id']);
			$filters_string = self::convert_filters( $listing_filters );
			// and template and other attributes
			$options = PL_Shortcode_CPT::get_shortcode_options('search_listings', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}
		// get default values
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs('search_listings');
		foreach ($sc_attrs['options'] as $key=>$vals) {
			if (empty($atts[$key]) && !empty($vals['default'])) {
				$atts[$key] = $vals['default'];
			}
			if (!empty($atts[$key]) && $vals['type']=='multiselect') {
				if (!is_array($atts[$key])) {
					$atts[$key] = array_map('trim',explode(",", $atts[$key]));
				}
				$values = array();
				$optvals = get_option('pl_search_listings_formval_'.$key, $vals['options']);
				foreach($atts[$key] as $okey) {
					if (!empty($optvals[$okey])) {
						$values[$okey] = $optvals[$okey];
					}
				}
				if (empty($values)) {
					unset($atts[$key]);
				}
				else {
					$atts[$key] = $values;
				}
			}
		}
		$atts = wp_parse_args($atts, array('table_id' => 'placester_listings_list'));
		$atts['context'] = 'search_listings_'.(empty($atts['context']) ? 'shortcode' : $atts['context']);
		
		// set limit per page if any
		if( ! empty( $atts['query_limit'] ) ) {
			global $pl_listings_query_limit;
			$pl_listings_query_limit = $atts['query_limit'];
			// TODO init the js directly instead
			add_action( 'listings_limit_default', array( __CLASS__, 'add_length_limit_default'  ));
			unset ( $pl_listings_query_limit );
		}

		if (!has_filter('pls_listings_' . $atts['context'])) {
			// formatting filter for individual listings already registered above
			add_filter('pls_listings_' . $atts['context'], array(__CLASS__,'pls_listings_callback'), 10, 5);
		}

		// we support a filter for wrapping the whole shortcode here
		// TODO: move applyfilter to blueprint 
		ob_start();
		self::add_to_group('search_listings', $atts, $filters.$filters_string);
		echo "<a id={$atts['table_id']}_anchor name={$atts['table_id']}_anchor></a>";
		PLS_Partials_Get_Listings_Ajax::load($atts);
		$return = ob_get_clean();
		return apply_filters('pls_listings_' . $atts['context'], $return, array(), '', $atts, null);
	}

	/**
	 * Generate search_map shortcode output
	 */
	public static function search_map_entity( $atts ) {

		if (!empty($atts['id'])) {
			// get template and other attributes
			$options = PL_Shortcode_CPT::get_shortcode_options('search_map', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}
		// get default values
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs('search_map');
		foreach ($sc_attrs['options'] as $key=>$vals) {
			if (empty($atts[$key]) && !empty($vals['default'])) {
				$atts[$key] = $vals['default'];
			}
		}
		$atts = wp_parse_args($atts, array('context' => 'shortcode', 'type' => 'listings', 'sync_map_to_list' => false));
		$atts['context'] = empty($atts['context']) ? 'shortcode' : $atts['context'];

		if (!has_filter('pls_search_map_' . $atts['context'])) {
			add_filter('pls_search_map_' . $atts['context'], array(__CLASS__,'pls_search_map_callback'), 10, 3);
		}

		ob_start();
		// we support a filter for wrapping the whole shortcode here
		// TODO: move applyfilter to blueprint 
		$listings = null;
		self::add_to_group('search_map', $atts);
		echo PLS_Map::listings(null, $atts);
		$return = ob_get_clean();
		return apply_filters('pls_search_map_' . $atts['context'], $return, $listings, $atts);
	}

	/**
	 * Generate listing_slideshow shortcode output
	 */
	public static function listing_slideshow( $atts ) {

		// fix attribute name case so js slideshow gets correct value names
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs('listing_slideshow');
		foreach ($sc_attrs['options'] as $key=>$vals) {
			if (empty($atts[$key]) && !empty($atts[strtolower($key)])) {
				$atts[$key] = $atts[strtolower($key)];
				unset($atts[strtolower($key)]);
			}
		}

		if (!empty($atts['id'])) {
			// get template and other attributes
			$options = PL_Shortcode_CPT::get_shortcode_options('listing_slideshow', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}
		// get default values
		foreach ($sc_attrs['options'] as $key=>$vals) {
			if (empty($atts[$key]) && !empty($vals['default'])) {
				$atts[$key] = $vals['default'];
			}
		}
		$atts = wp_parse_args($atts, array(
				'startClockOnMouseOut' => true,			// if clock should start on MouseOut
				'startClockOnMouseOutAfter' => 1000,	// how long after MouseOut should the timer start again
				'directionalNav' => true,				// manual advancing directional navs
				'captions' => true,						// do you want captions?
				'captionAnimation' => 'fade',			// fade, slideOpen, none
				'captionAnimationSpeed' => 800,			// if so how quickly should they animate in
				'afterSlideChange' => 'function(){}',	// empty function
				'bullets' => 'false',
				'featured_option_id' => 'slideshow-featured-listings',
				'listings' => 'limit=5&is_featured=true&sort_by=price'
		));
		$atts['context'] = empty($atts['context']) ? 'shortcode' : $atts['context'];

		if (!has_filter('pls_slideshow_html_' . $atts['context'])) {
			add_filter( 'pls_slideshow_html_' . $atts['context'], array(__CLASS__,'pls_slideshow_html_shortcode_callback'), 10, 5 );
			add_filter( 'pls_slideshow_single_caption_' . $atts['context'], array(__CLASS__,'pls_slideshow_single_caption_callback'), 10, 5 );
		}
		
		return PLS_Slideshow::slideshow($atts);
	}

	/**
	 * Fetch fields for formatting individual items in the listing_slideshow shortcode output
	 */
	public static function listing_slideshow_sub_entity( $atts, $content, $tag ) {
		if( empty( self::$listing ) ) {
			return '';
		}

		$listing = self::$listing;

		if( $tag === 'ls_index' ) {
			return self::$slideshow_caption_index;
		} else if( $tag === 'ls_url' ) {
			return $listing['cur_data']['url'];

		} else if( $tag === 'ls_address' ) {
			return $listing['location']['address'];

		} else if( $tag === 'ls_beds' ) {
			return $listing['cur_data']['beds'];

		} else if( $tag === 'ls_baths' ) {
			return $listing['cur_data']['baths'];
		}

		return '';
	}

	public static function neighborhood_sub_entity( $atts, $content, $tag ) {
		$val = '';

		// blank term - shouldn't happen
		if( empty( self::$neighborhood_term ) ) {
			return '';
		}

		$term = self::$neighborhood_term;
		$taxonomy_name = $term->taxonomy;

		if( $tag === 'nb_title' ) {
			$val = apply_filters( 'pls_neighborhood_title', $term->name );
		} else if( $tag === 'nb_description' ) {
			$val = apply_filters( 'pls_neighborhood_description', $term->description );
		} else if( $tag === 'nb_featured_image' ) {
			// take the first off the listing, otherwise - default
			$taxonomy_maps_name = self::translate_taxonomy_type( $term->taxonomy );
			$term_name = $term->name;

			$api_response = PL_Listing_Helper::results(array('location[' . $taxonomy_maps_name . ']' => $term_name, 'limit' => 1));

			$featured_image_src = PLS_IMG_URL . '/null/listing-300x180.jpg';

			if( ! empty( $api_response['listings'] ) &&
				! empty( $api_response['listings'][0] ) &&
				! empty( $api_response['listings'][0]['images'] )
			) {
				$featured_image_src = $api_response['listings'][0]['images'][0]['url'];
			}

			$val = "<img src='$featured_image_src'></img>";
		} else if( $tag === 'nb_link' ) {
			$term_link = get_term_link( $term );
			if( ! is_wp_error( $term_link ) ) {
				$val = $term_link;
			}
		} else if( $tag === 'nb_map' ) {
			ob_start();
			$taxonomy_maps_name = self::translate_taxonomy_type( $taxonomy_name );

			?>
			<script type="text/javascript">
			if (typeof bootloader !== 'object') {
				var bootloader;
			}

			jQuery(document).ready(function( $ ) {
				var map = new Map();
				var listings = new Listings({
					map: map
				});
				debugger;

				var neighborhood = new Neighborhood({
					map: map,
					type: '<?php echo $taxonomy_maps_name; ?>',
					name: '<?php echo $term->name; ?>',
					slug: '<?php echo $term->slug; ?>'
				});

				map.init({
					type: 'neighborhood',
					neighborhood: neighborhood,
					listings: listings
				});

				if (typeof bootloader !== 'object') {
					bootloader = new SearchLoader();
					bootloader.add_param({map: map});
				} else {
					bootloader.add_param({map: map});
				}

				listings.init();

			});
			</script>
			<?php
			echo PLS_Map::polygon( null, array(
					'width' => 629,
					'height' => 303,
					'zoom' => 16,
					'polygon_search' => $taxonomy_name,
					'polygon' => $term->slug,
					'loading_overlay' => '<div id="spinner"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div></div>',
					'class' => 'polygon_search')
				);
			$val = ob_get_clean();
		}

		return $val;
	}

	/**
	 * Helper function for formatting individual listing fields.
	 * self::$listing should contain the listing values.
	 */
	public static function listing_sub_entity( $atts, $content, $tag ) {
		$listing_list = array();

		if( !empty( self::$listing ) ) {
			$listing_list = self::$listing;
		} else if ( !empty( PL_Shortcodes::$listing ) ) {
			$listing_list = PL_Shortcodes::$listing;
		} else {
			return;
		}

		if (array_key_exists($tag, $listing_list['cur_data'])) {
			$val = $listing_list['cur_data'][$tag];
		}else if (array_key_exists($tag, $listing_list['location'])) {
			$val = $listing_list['location'][$tag];
		}else if (array_key_exists($tag, $listing_list['contact'])) {
			$val = $listing_list['contact'][$tag];
		}else if (array_key_exists($tag, $listing_list['rets'])) {
			$val = $listing_list['rets'][$tag];
		}
		else {
			$val = '';
		}

		// This is an example of handling a specific tag in a different way
		// TODO: make this more elegant...
		switch ($tag) {
			case 'desc':
				// default is to use full description for Listing Detail Template
				if ( !empty($atts['maxlen']) ) {
					// use max length attribute if present
					$val = substr($val, 0, (int)$atts['maxlen']);
				} else if ( !is_single() ) {
					// restrict display for search_results and static_listings to 15 words
					$val = wp_trim_words($val, 15, '...');
				}
				break;
			case 'image':
				$width = !empty($atts['width']) ? (int)$atts['width'] : 180;
				$height = !empty($atts['height']) ? (int)$atts['height'] : 120;
				$val = PLS_Image::load(!empty($listing_list['images'][0]['url']) ? $listing_list['images'][0]['url'] : '',
					array('resize' => array('w' => $width, 'h' => $height),
						'fancybox' => true,
						'as_html' => true,
						'html' => array('alt' => empty($listing_list['location']['full_address']) ? $listing_list['location']['address'] : $listing_list['location']['full_address'], 'itemprop' => 'image')));
				break;
			case 'image_url':
				$index = empty($atts['index']) ? 0 : (int)$atts['index'];
				$placeholder = empty($atts['placeholder']) ? '' : $atts['placeholder'];
				$val = PLS_Image::load(empty($listing_list['images'][$index]['url']) ? $placeholder : $listing_list['images'][$index]['url'],
					array('allowresize' => false,
						'fancybox' => false,
						'as_html' => false));
				break;
			case 'gallery':
				ob_start();
				?>
				<div id="slideshow" class="clearfix theme-default left bottomborder">
					<div class="grid_8 alpha">
						<ul class="property-image-gallery grid_8 alpha">
							<?php foreach ($listing_list['images'] as $image): ?>
							<li><?php echo PLS_Image::load($image['url'],
									array('resize' => array('w' => 100, 'h' => 75),
										'fancybox' => true,
										'as_html' => false,
										'html' => array('itemprop' => 'image'))); ?>
							</li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>
				<?php
				$val = ob_get_clean();
				break;
			case 'map':
				$width = !empty($atts['width']) ? (int)$atts['width'] : 590;
				$height = !empty($atts['height']) ? (int)$atts['height'] : 250;
				if (!empty($atts['type']) && $atts['type']=='lifestyle') {
					$val = PLS_Map::lifestyle($listing_list, array('width' => $width, 'height' => $height, 'zoom' => 16, 'life_style_search' => true,
					'show_lifestyle_controls' => true, 'show_lifestyle_checkboxes' => true,
					'lat' => $listing_list['location']['coords'][0], 'lng' => $listing_list['location']['coords'][1]));
				}
				else {
					$val = PLS_Map::dynamic($listing_list, array('width' => $width, 'height' => $height, 'zoom' => 16, 'life_style_search' => true,
					'show_lifestyle_controls' => false, 'show_lifestyle_checkboxes' => false,
					'lat' => $listing_list['location']['coords'][0], 'lng' => $listing_list['location']['coords'][1]));
				}
				break;
			case 'price':
				$val = PLS_Format::number($listing_list['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true));
				break;
			case 'listing_type':
				$val = PLS_Format::translate_property_type($listing_list);
				break;
			case 'amenities':
				$amenities = PLS_Format::amenities_but($listing_list, array('half_baths', 'beds', 'baths', 'url', 'sqft', 'avail_on', 'price', 'desc'));
				$amen_type = empty($atts['type']) ? 'list' : (string)$atts['type'];
				ob_start();
				?>
				<div class="amenities-section grid_8 alpha">
					<ul>
						<?php if (is_array($amenities[$amen_type])): ?>
						<?php $amenities[$amen_type] = PLS_Format::translate_amenities($amenities[$amen_type]); ?>
						<?php foreach ($amenities[$amen_type] as $amenity => $value): ?>
						<li><span><?php echo $amenity; ?> </span> <?php echo $value ?></li>
						<?php endforeach ?>
						<?php endif ?>
					</ul>
				</div>
				<?php
				$val = ob_get_clean();
				break;
			case 'compliance':
				ob_start();
				PLS_Listing_Helper::get_compliance(array('context' => 'inline_search',
					'agent_name' => $listing_list['rets']['aname'] ,
					'office_name' => $listing_list['rets']['oname'],
					'office_phone' => PLS_Format::phone($listing_list['contact']['phone'])));
				$val = ob_get_clean();
				break;
			case 'favorite_link_toggle':
				$val = PL_Favorite_Listings::placester_favorite_link_toggle(array_merge($atts, array('property_id' => $listing_list['id'])));
				break;
			case 'custom':
				// TODO: format based on data type
				if (!empty($atts['attribute'])) {
					if (empty($atts['group']) && isset($listing_list[$atts['attribute']])) {
						$val = $listing_list[$atts['attribute']];
					}
					elseif (!empty($atts['group']) && isset($listing_list[$atts['group']]) && isset($listing_list[$atts['group']][$atts['attribute']])) {
						$val = $listing_list[$atts['group']][$atts['attribute']];
					}
					if ($val == '' && !empty($atts['value'])) {
						$val = $atts['value'];
					}
					if (!empty($atts['type'])) {
						switch($atts['type']) {
							case 'list':
								$vals = array_map('trim', explode(',', $val));
								if (!empty($vals[0])) {
									$val = '<ul>';
									foreach($vals as $item) {
										$val .= '<li>'.$item.'</li>';
									}
									$val .= '</ul>';
								}
								break;
							case 'currency':
								$val = PLS_Format::number($val, array('abbreviate' => false, 'add_currency_sign' => true));
								break;
						}
					}
				}
				else {
					$val = '[custom]';
				}
			default:
				// print as is
		}

		return $val;
	}

	public static function pl_neighborhood_entity( $atts ) {
		ob_start();
		$taxonomy_type = 'state';
		$taxonomy = null;
		$term_slug = '';
		$term_name = '';
		$neighborhood_term = '';

		// Type of neighborhood is set as radio_type from the radio box in the admin
		// get key and value to test for neighborhood object
		if( ! isset( $atts['radio_type'] ) ) {
			return;
		}
		$key = $atts['radio_type'];
		if( ! isset( $atts['nb_select_' . $key] ) ) {
			return;
		}
		$value = $atts['nb_select_' . $key];

		// API searches for neighborhood by slug
		if( in_array( $key, array( 'state', 'city', 'neighborhood', 'zip', 'street' ) ) ) {
			$term = get_term_by('id', $value, $key);
			if( ! empty( $term ) ) {
				$taxonomy_type = $key;
				$taxonomy = get_taxonomy( $key );
				$atts[$key] = $term->slug;
				$term_slug = $term->slug;
				$term_name = $term->name;
				$neighborhood_term = $term;
			}
		}

		if( empty( $taxonomy ) ) {
			return;
		}

		$taxonomy_maps_type = self::translate_taxonomy_type( $taxonomy_type );

		$args = wp_parse_args($atts, array('state' => false, 'city' => false,
			'neighborhood' => false, 'zip' => false, 'street' => false, 'image_limit' => 20,
			'width' => 400, 'height' => 400, 'zoom' => 14, 'context' => false, 'context_var' => '' ));

		?>
		<script type="text/javascript">
			if (typeof bootloader !== 'object') {
				var bootloader;
			}

			jQuery(document).ready(function( $ ) {
				var taxonomy = jQuery.parseJSON(' <?php echo json_encode( $taxonomy ); ?> ');
				var map = new Map();
				var listings = new Listings({
					map: map
				});
				debugger;

				var neighborhood = new Neighborhood({
					map: map,
					type: '<?php echo $taxonomy_maps_type; ?>',
					name: '<?php echo $term_name; ?>',
					slug: '<?php echo $term_slug; ?>'
				});

				map.init({
					type: 'neighborhood',
					neighborhood: neighborhood,
					listings: listings
				});

				if (typeof bootloader !== 'object') {
					bootloader = new SearchLoader();
					bootloader.add_param({map: map});
				} else {
					bootloader.add_param({map: map});
				}

				listings.init();

			});
		</script>
		<?php

		echo PLS_Map::polygon( null, array(
					'width' => 629,
					'height' => 303,
					'zoom' => 16,
					'polygon_search' => $taxonomy->name,
					'polygon' => $taxonomy->rewrite['slug'],
					'loading_overlay' => '<div id="spinner"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div></div>',
					'class' => 'polygon_search')
				);
		$neighborhood_html = ob_get_clean();

		$neighborhood_html = apply_filters( pls_get_merged_strings(
						array( 'pls_neighborhood_html', $args['context'] ), '_', 'pre', false ),
						$neighborhood_html, $neighborhood_term, $args['context'], $args['context_var'] );

		return $neighborhood_html;
	}

	/**
	 * Generate output for search_form shortcode
	 */
	public static function search_form_entity( $atts ) {

		if (!empty($atts['id'])) {
			// get template and other attributes
			$options = PL_Shortcode_CPT::get_shortcode_options('search_form', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}
		// get default values
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs('search_form');
		foreach ($sc_attrs['options'] as $key=>$vals) {
			if (empty($atts[$key]) && !empty($vals['default'])) {
				$atts[$key] = $vals['default'];
			}
		}
		$atts['context'] = empty($atts['context']) ? 'shortcode' : $atts['context'];

		// Setup form action
		$form_data = array('action'=>'');
		// Handle attributes using shortcode_atts...
		$form_data['action'] = '';
		// TODO deprecate this attr
		if( !empty($atts['form_action_url']) ) {
			$form_data['action'] = $atts['form_action_url'];
		}
		// use this one
		if( !empty($atts['formaction']) ) {
			$form_data['action'] = $atts['formaction'];
		}
		$atts['ajax'] = empty($form_data['action']) ? true : false;
		$atts['form_data'] = (object)$form_data;

		if (!has_filter('pls_listings_search_form_outer_' . $atts['context'])) {
			add_filter( 'pls_listings_search_form_outer_' . $atts['context'], array(__CLASS__,'pls_listings_search_form_outer_callback'), 10, 7 );
			add_filter( 'pls_listings_search_form_inner_' . $atts['context'], array(__CLASS__,'pls_listings_search_form_inner_callback'), 10, 5 );
		}
		
		return PLS_Partials_Listing_Search_Form::init($atts);
	}

	/**
	 * Helpers
	 */

	private static function get_property_ids( $featured_listing_id ) {
		// if( ! is_int( $featured_listing_id ) ) { }
		$values = get_post_custom( $featured_listing_id );

		$property_ids = isset( $values['keatingbrokerage_meta'] ) ? @unserialize($values['keatingbrokerage_meta'][0]) : '';
		$pl_featured_listing_meta = isset( $values['pl_featured_listing_meta'] ) ? @json_decode($values['pl_featured_listing_meta'][0], true) : '';
		// $pl_featured_meta_value = empty( $pl_featured_listing_meta ) ? array('listings' => array()) : $pl_featured_listing_meta['featured-listings-type'];
		// $pl_featured_meta_value = empty( $pl_featured_listing_meta ) ? array('listings' => array()) : @json_decode($pl_featured_listing_meta[0], true);

		if( empty( $pl_featured_listing_meta ) ) {
			$pl_featured_listing_meta = isset( $values['pl_featured_listing_meta'] ) ? @unserialize($values['pl_featured_listing_meta'][0]) : '';
			if( empty( $pl_featured_listing_meta ) ) {
				return array( );
			}
		}

		// remove the top array key if any
		if( isset( $pl_featured_listing_meta['featured-listings-type'] ) ) {
			$pl_featured_listing_meta = $pl_featured_listing_meta['featured-listings-type'];
		}

		return $pl_featured_listing_meta;
	}

	private static function add_to_group($shortcode, $atts, $filters = '') {

		if (self::$current_shortcode_group === '') {
			self::$current_shortcode_group = count(self::$shortcode_groups);
			self::$shortcode_groups[self::$current_shortcode_group] = array();
		}
		$current_group = &self::$shortcode_groups[self::$current_shortcode_group];

		switch($shortcode) {
			case 'search_listings':
			case 'static_listings':
				if (!empty($current_group['listings'])) {
					self::$current_shortcode_group = count(self::$shortcode_groups);
					self::$shortcode_groups[self::$current_shortcode_group] = array();
					$current_group = &self::$shortcode_groups[self::$current_shortcode_group];
				}
				$current_group['listings']['shortcode'] = $shortcode;
				$current_group['listings']['atts'] = $atts;
				$current_group['listings']['filters'] = $filters;
				break;
			case 'search_map':
				$current_group['map']['atts'] = $atts;
				break;
		}
	}

	public static function partial_one( $listing, $featured_listing_id ) {
		$property_ids = PL_Component_Entity::get_property_ids( $featured_listing_id );
		$property_ids = array_flip( $property_ids );

		$api_response = PL_Listing_Helper::details(array('property_ids' => $property_ids));
		//response is expected to be of fortmat api response
		//no addiitonal formatting needed.
		return $api_response;
	}

	public static function print_property_listing_args() {
		global $property_ids;
		echo "property_ids: ['" . implode("','", $property_ids) . "'],";
	}

	private static function convert_filters( $filters ) {
		$av_filters = PL_Shortcode_CPT::get_listing_filters();
		ob_start();
		if( is_array( $filters ) ) {
			foreach( $filters as $key1 => $value1 ) {
				if( is_array( $value1 ) ) {
					// we store custom data as custom but it uses filter name metadata
					$key = $key1 == 'custom' ? 'metadata' : $key1;
					if (array_diff_key($value1,array_keys(array_keys($value1)))) {
						foreach( $value1 as $key2 => $value2 ) {
							$skey = count($value2) > 1 ? '[]' :'';
							foreach( $value2 as $value3 ) {
								echo 'listings.default_filters.push( { "name": "' . $key .  '['.$key2.']'.$skey . '", "value" : "'. $value3 . '" } );';
							}
							if ($skey) {
								echo 'listings.default_filters.push( { "name": "' . $key . '['.$key2.'_match]", "value" : "in" } );';
							}
							elseif (!empty($av_filters[$key1.'.'.$key2]['type']) && ($av_filters[$key.'.'.$key2]['type']=='text'|| $av_filters[$key.'.'.$key2]['type']=='textarea')) {
								// perform an exact match for agent and office id
								switch($key2) {
									case 'aid':
									case 'oid':
										$match_by = 'exact';
										break;
									default:
										$match_by = 'like';
								}
								echo 'listings.default_filters.push( { "name": "' . $key . '['.$key2.'_match]", "value" : "'. $match_by . '" } );';
							}
						}
					}
					else {
						// list
						$skey = count($value1) > 1 ? '[]' :'';
						foreach( $value1 as $value2 ) {
							echo 'listings.default_filters.push( { "name": "' . $key .  $skey . '", "value" : "'. $value2 . '" } );';
						}
						if ($skey) {
							echo 'listings.default_filters.push( { "name": "' . $key . '_match", "value" : "in" } );';
						}
						elseif (!empty($av_filters[$key]['type']) && ($av_filters[$key]['type']=='text' || $av_filters[$key]['type']=='textarea')) {
							echo 'listings.default_filters.push( { "name": "' . $key . '_match", "value" : "like" } );';
						}
					}
				}
			}
		}
		return ob_get_clean();
	}


	/**
	 * Templating functions for the shortcodes
	 */


	/**
	 * Callback to wrap formatting around search_map
	 */
	public static function pls_search_map_callback( $return, $listings, $request_params ) {
		// add template formatting
		$header = $footer = '';
		$template_id = substr(current_filter(), strlen('pls_search_map_'));
		$template = PL_Shortcode_CPT::load_template($template_id, 'search_map');
		if (!empty($template['before_widget']) && empty($_GET['embedded'])) {
			$header = $template['before_widget'].$header;
		}
		if (!empty($template['css'])) {
			$header = '<style type="text/css">'.$template['css'].'</style>'.$header;
		}
		if (!empty($template['after_widget']) && empty($_GET['embedded'])) {
			$footer .= $template['after_widget'];
		}
		// support shortcodes in the header or footer
		return do_shortcode($header).$return.do_shortcode($footer);
	}

	/**
	 * Wrap formatting around list of listings (featured_listings, search_listings, static_listings)
	 */
	public static function pls_listings_callback( $return, $listings_raw, $listings_html, $request_params, $context_var ) {
		// add template formatting
		$header = $footer = '';
		$context = explode('_', substr(current_filter(), strlen('pls_listings_')), 3);
		$shortcode = $context[0].'_'.$context[1];
		$template_id = $context[2];
		$template = PL_Shortcode_CPT::load_template($template_id, $shortcode);
		if (!empty($template['before_widget']) && empty($_GET['embedded'])) {
			$header = $template['before_widget'].$header;
		}
		if (!empty($template['css'])) {
			$header = '<style type="text/css">'.$template['css'].'</style>'.$header;
		}
		if (!empty($template['after_widget']) && empty($_GET['embedded'])) {
			$footer .= $template['after_widget'];
		}
		// support shortcodes in the header or footer
		return do_shortcode($header).$return.do_shortcode($footer);
	}

	/**
	 * Format single featured listing
	 */
	public static function pls_listing_callback( $item_html, $listing, $request_params, $context_var ) {
		// get the template and shortcode from the filter prefix
		// TODO: use params instead
		$context = explode('_', substr(current_filter(), strlen('pls_listing_')), 3);
		$shortcode = $context[0].'_'.$context[1];
		$template_id = $context[2];

		$snippet_body = PL_Shortcodes::get_active_snippet_body( $shortcode, $template_id );
		if (empty($snippet_body)) {
			return $item_html;
		}
		return PL_Featured_Listings_CPT::do_templatetags($snippet_body, $listing);
	}

	/**
	 * Format the search form body using any template we might have.
	 * Called from PLS_Partials_Listing_Search_Form
	 */
	public static function pls_listings_search_form_inner_callback($form, $form_html, $form_options, $section_title, $context_var) {
		$shortcode = 'search_form';
		self::$form_html = $form_html;
		PL_Shortcodes::$form_html = $form_html;

		// get the template id from the filter name
		$template_id = substr(current_filter(), strlen('pls_listings_search_form_inner_'));
		$template = PL_Shortcode_CPT::load_template($template_id, $shortcode);
		if (empty($template['snippet_body'])) {
			return $form;
		}
		return PL_Form_CPT::do_templatetags($template['snippet_body'], $form_html);
	}

	/**
	 * Format and style the search form using any template we might have.
	 * Called from PLS_Partials_Listing_Search_Form
	 */
	public static function pls_listings_search_form_outer_callback($form, $form_html, $form_options, $section_title, $form_data, $form_id, $context_var) {
		// get the template id from the filter name
		$template_id = substr(current_filter(), strlen('pls_listings_search_form_outer_'));
		$template = PL_Shortcode_CPT::load_template($template_id, 'search_form');

		// form enclosure and add template formatting
		$header = $footer = '';
		if (!empty($context_var['header'])) {
			$header .= $context_var['header'];
		}
		if (!empty($template['css'])) {
			$header .= '<style type="text/css">'.$template['css'].'</style>';
		}
		if (!empty($template['before_widget'])) {
			$header .= $template['before_widget'];
		}
		if (!empty($template['after_widget'])) {
			$footer = $template['after_widget'];
		}
		return $header.$form.$footer;
	}

	/**
	 * Wrap formatting around slideshow
	 */
	public function pls_slideshow_html_shortcode_callback($html, $data, $context, $context_var, $args) {
		// basic slideshow style
		$css = '
			.orbit-wrapper .orbit-caption {
				z-index: 999999 !important;
				margin-top: -113px;
				position: absolute;
				right: 0;
				bottom: 0;
				width: 100%;
			}
			.orbit-caption {
				display: none;
			}';
		// add template formatting
		$header = $footer = '';
		$template = PL_Shortcode_CPT::load_template($context, 'listing_slideshow');
		if (!empty($template['css'])) {
			$css .= $template['css'];
		}
		if ($css) {
			$header = '<style type="text/css">'.$css.'</style>';
		}
		if (!empty($template['before_widget']) && empty($_GET['embedded'])) {
			$header .= $template['before_widget'];
		}
		if (!empty($template['after_widget']) && empty($_GET['embedded'])) {
			$footer .= $template['after_widget'];
		}
		
		return $header.$html.$footer;
	}

	/**
	 * Format single slideshow caption
	 */
	public static function pls_slideshow_single_caption_callback( $caption_html, $listing, $context, $context_var, $index ) {
		$shortcode = 'listing_slideshow';
		self::$listing = $listing;
		self::$slideshow_caption_index = $index;

		$snippet_body = PL_Shortcodes::get_active_snippet_body( $shortcode, $context );
		if (empty($snippet_body)) {
			return $caption_html;
		}
		return do_shortcode($snippet_body);
	}

	// that would work fine for output styling, not caption-specific
	public static function listing_slideshow_templates3( $html, $data, $context, $context_var, $args ) {
		$shortcode = 'listing_slideshow';
		if( ! isset( $data['listing'] ) ) {
			return '';
		}
		self::$listing = $data['listing'];

		$snippet_body = PL_Shortcodes::get_active_snippet_body( $shortcode, $context );
		return do_shortcode($snippet_body . $html);
	}

	/**
	 * Generate individual items for ajax - fetched listings (search_listings, static_listings)
	 */
	public static function pls_listings_list_ajax_item_html_callback( $item_html, $listing, $context_var ) {
		// get the template and shortcode from the filter prefix
		// TODO: use params instead
		$context = explode('_', substr(current_filter(), strlen('pls_listings_list_ajax_item_html_')), 3);
		$shortcode = $context[0].'_'.$context[1];
		$template_id = $context[2];
		
		$snippet_body = PL_Shortcodes::get_active_snippet_body( $shortcode, $template_id );
		if (empty($snippet_body)) {
			return $item_html;
		}
		return PL_Search_Listing_CPT::do_templatetags($snippet_body, $listing);
	}

	/**
	 * Neighborhoods and their templates
	 */
	public static function neighborhood_templates( $neighborhood_html, $term, $context, $context_var ) {
		$shortcode = 'pl_neighborhood';
		self::$neighborhood_term = $term;

		// get the template attached as a context arg, 33 is the length of the filter prefix
		$template = $context;

		$snippet_body = PL_Shortcodes::get_active_snippet_body($shortcode, $template);
		return do_shortcode($snippet_body);
	}

	public static function compliance_entity( $atts ) {
		$content = '';
		if( !empty( self::$listing ) ) {
			$listing = self::$listing;
			ob_start();
			PLS_Listing_Helper::get_compliance(array(
					'context' => 'inline_search',
					'agent_name' => $listing['rets']['aname'],
					'office_name' => $listing['rets']['oname'],
					'office_phone' => PLS_Format::phone($listing['contact']['phone']),
					'agent_license' => ( isset( $listing['rets']['alicense'] ) ? $listing['rets']['alicense'] : false ),
					'co_agent_name' => ( isset( $listing['rets']['aconame'] ) ? $listing['rets']['aconame'] : false ),
					'co_office_name' => ( isset( $listing['rets']['oconame'] ) ? $listing['rets']['oconame'] : false )
				));

			// No compliance found
			if( ! isset( $_POST['compliance_message'] ) ) {
				return $content;
			}
			ob_clean();
			ob_start();

			$compliance_message = wp_kses_post($_POST['compliance_message']);
			$compliance_message = wp_parse_args($compliance_message, array(
					'agent_name' => false,
					'office_name' => false,
					'office_phone' => false,
					'img' => false,
					'disclaimer' => false,
					'agent_license' => false,
					'co_agent_name' => false,
					'co_office_name' => false
				));
			?>
			<div class="clear"></div>
			<div class="compliance-wrapper">
				<?php if ($compliance_message['img']): ?>
				<img src="<?php echo $compliance_message['img'] ?>" alt="">
				<?php endif ?>
				<?php if ($compliance_message['agent_name']): ?>
				<p class="inline-compliance">
					Listing Agent:
					<?php echo $compliance_message['agent_name'] ?>
				</p>
				<?php endif ?>
				<?php if (!empty($compliance_message['agent_license'])): ?>
				<p class="inline-compliance">
					DRE#:
					<?php echo $compliance_message['agent_license'] ?>
				</p>
				<?php endif ?>
				<?php if (!empty($compliance_message['office_name'])): ?>
				<p class="inline-compliance">
					Courtesy of:
					<?php echo $compliance_message['office_name'] ?>
				</p>
				<?php endif ?>
				<?php if (!empty($compliance_message['office_phone'])): ?>
				<p class="inline-compliance">
					Agent Phone:
					<?php echo $compliance_message['office_phone'] ?>
				</p>
				<?php endif ?>
				<?php if (!empty($compliance_message['co_agent_name'])): ?>
				<p class="inline-compliance">
					Co-Listing Agent:
					<?php echo $compliance_message['co_agent_name'] ?>
				</p>
				<?php endif ?>
				<?php if (!empty($compliance_message['co_office_name'])): ?>
				<p class="inline-compliance">
					Co-Listing Office:
					<?php echo $compliance_message['co_office_name'] ?>
				</p>
				<?php endif ?>
				<?php if ($compliance_message['disclaimer']): ?>
				<p class="disclaimer">
					<?php echo $compliance_message['disclaimer'] ?>
				</p>
				<?php endif ?>
			</div>
			<div class="clear"></div>
			<?php
			$content = ob_get_clean();
		}

		return $content;
	}

	/**
	 * Convert the Neighborhood taxonomy type to a Maps-accepted one
	 * @param string $taxonomy_type
	 */
	public static function translate_taxonomy_type( $taxonomy_type ) {
		switch( $taxonomy_type ) {
			case 'city': return 'locality';
			case 'zip': return 'postal';
			case 'state': return 'region';
			default: return $taxonomy_type;
		}
		return $taxonomy_type;
	}

	/**
	 * Helper, add CSS to template to hide dropdowns
	 */
	public static function hide_unnecessary_controls( $atts ) {

		$css = '';

		if( ! empty( $atts ) ) {

			if( $atts['hide_sort_by'] == 'true' && $atts['hide_sort_direction'] == 'true') {
				$css .= '.sort_wrapper { display: none !important; } ';
			}
			if( $atts['hide_sort_by'] == 'true' ) {
				$css .= '.sort_wrapper .sort_item:first-child { display: none !important; } ';
			}
			if( $atts['hide_sort_direction'] == 'true' ) {
				$css .= '.sort_wrapper .sort_item:last-child { display: none !important; } ';
			}
			if( $atts['hide_num_results'] == 'true' ) {
				$css .= '#placester_listings_list_length { display: none; } ';
			}
		}

		if ($css) {
			$css = '<style type="text/css">' . $css . '</style>';
		}
		echo $css;
	}
	
	/**
	 * Helper function to parse template tags out of a template and replace them with their values
	 * @param function $callback	: callback that will provide a value for any tags found
	 * @param array $tags			: list of tags the callback supports
	 * @param string $content		: actual content to be parsed
	 * @return string
	 */
	static function do_templatetags($callback, $tags, $content) {
		self::$template_tags = $tags;
		$subcode = implode('|', $tags);
		$pattern =
		'\\['                              // Opening bracket
		. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
		. "($subcode)"                       // 2: Shortcode name
		. '\\b'                              // Word boundary
		. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
		.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
		.     '(?:'
				.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
				.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
				.     ')*?'
				. ')'
				. '(?:'
				.     '(\\/)'                        // 4: Self closing tag ...
				.     '\\]'                          // ... and closing bracket
				. '|'
				.     '\\]'                          // Closing bracket
				.     '(?:'
						.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
						.             '[^\\[]*+'             // Not an opening bracket
						.             '(?:'
								.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
								.                 '[^\\[]*+'         // Not an opening bracket
								.             ')*+'
								.         ')'
								.         '\\[\\/\\2\\]'             // Closing shortcode tag
								.     ')?'
								. ')'
								. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	
		return preg_replace_callback( "/$pattern/s", $callback, $content );
	}
	
	/**
	 * Helper function to substitute values for template tags.
	 * Used by templates for: individual listing pages.
	 */
	public static function listing_templatetag_callback($m) {
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
	
		$tag = $m[2];
		$atts = shortcode_parse_atts($m[3]);
		$content = $m[5];
	
		if ($tag == 'if') {
			$val = isset($atts['value']) ? $atts['value'] : null;
			if (empty($atts['group']) && !empty($atts['attribute'])) {
				if (!empty(self::$listing['cur_data']) && array_key_exists($atts['attribute'], self::$listing['cur_data'])) {
					$atts['group'] = 'cur_data';
				}else if (!empty(self::$listing['location']) && array_key_exists($atts['attribute'], self::$listing['location'])) {
					$atts['group'] = 'location';
				}else if (!empty(self::$listing['contact']) && array_key_exists($atts['attribute'], self::$listing['contact'])) {
					$atts['group'] = 'contact';
				}else if (!empty(self::$listing['rets']) && array_key_exists($atts['attribute'], self::$listing['rets'])) {
					$atts['group'] = 'rets';
				}
			}
			if (empty($atts['group'])) {
				if ((!isset(self::$listing[$atts['attribute']]) && $val==='') ||
				(isset(self::$listing[$atts['attribute']]) && (PL_Component_Entity::$listing[$atts['attribute']]===$val || (is_null($val) && PL_Component_Entity::$listing[$atts['attribute']])))) {
					return self::do_templatetags(array(__CLASS__, 'listing_templatetag_callback'), self::$template_tags, $content);
				}
			}
			elseif ((!isset(self::$listing[$atts['group']][$atts['attribute']]) && $val==='') ||
					(isset(self::$listing[$atts['group']][$atts['attribute']]) && (PL_Component_Entity::$listing[$atts['group']][$atts['attribute']]===$val || (is_null($val) && PL_Component_Entity::$listing[$atts['group']][$atts['attribute']])))) {
				return self::do_templatetags(array(__CLASS__, 'listing_templatetag_callback'), self::$template_tags, $content);
			}
			return '';
		}
		if ($tag == 'image_url') {
			$index = empty($atts['index']) ? 0 : (int)$atts['index'];
			$placeholder = empty($atts['placeholder']) ? '' : $atts['placeholder'];
			return PLS_Image::load(empty(self::$listing['images'][$index]['url']) ? $placeholder : self::$listing['images'][$index]['url'],
					array('allowresize' => false,
							'fancybox' => false,
							'as_html' => false));
		}
		$content = self::listing_sub_entity( $atts, $content, $tag );
		return self::wrap( 'listing_sub', $content );
	}
	
	/**
	 * Give theme, etc a chance to customize template output on a per item basis
	 */
	public static function wrap( $shortcode, $content = '' ) {
		ob_start();
		do_action( $shortcode . '_pre_header' );
		echo $content;
		do_action( $shortcode . '_post_footer' );
		return ob_get_clean();
	}

	public static function add_js() {
		$js_files = array();

		//foreach(self::$shortcode_groups as $key=>$group) {
		$group = reset(self::$shortcode_groups); if(!empty($group)) {
		?>
		<script type="text/javascript">

			jQuery(document).ready(function($) {
				<?php if (!empty($group['map'])):?>
					<?php $js_files['map'] = true ?>
					var map = new Map ();
					<?php
					if (empty($group['map']['atts']['type'])) {
						$group['map']['atts']['type'] = empty($group['listings']) ? 'lifestyle' : 'listings';
					}
					?>
				<?php endif ?>

				<?php if (!empty($group['listings'])):?>
					var list = new List ();
				<?php endif ?>
				var filter = new Filters ();
				<?php $js_files['filters'] = true ?>
				var listings = new Listings ({
						filter: filter,
						<?php if (!empty($group['listings'])):?>
						list: list,
						<?php endif ?>
						disable_saved_search: <?php echo $group['listings']['shortcode'] == 'search_listings' ? '0' : '1' ?>,
						<?php if (!empty($group['map'])):?>
						map: map,
						<?php endif ?>
						<?php echo do_action('featured_filters_featured_ids') ?>
					});

				<?php if (!empty($group['map'])):?>
					var init_args = new Object();
					init_args.listings = listings;
					<?php if ($group['map']['atts']['sync_map_to_list']): ?>
						init_args.sync_map_to_list = true;
					<?php endif ?>
					<?php if ($group['map']['atts']['lat'] && $group['map']['atts']['lng']): ?>
						init_args.lat = '<?php echo $group['map']['atts']['lat'] ?>';
						init_args.lng = '<?php echo $group['map']['atts']['lng'] ?>';
					<?php endif ?>
					init_args.type = '<?php echo $group['map']['atts']['type'] ?>';
					init_args.filter_by_bounds = false;

					<?php if ($group['map']['atts']['type'] == 'lifestyle'): ?>
						var lifestyle = new Lifestyle({
							map: map
						});
						init_args.lifestyle = lifestyle;
						<?php $js_files['lifestyle'] = true ?>
					<?php elseif ($group['map']['atts']['type'] == 'lifestyle_polygon'): ?>
						var lifestyle_polygon = new Lifestyle_Polygon({
							map: map
						});
						init_args.lifestyle_polygon = lifestyle_polygon;
						<?php $js_files['lifestyle_polygon'] = true ?>
					<?php endif ?>

					map.init(init_args);
				<?php endif ?>

				<?php if (!empty($group['listings'])):?>
					filter.init({
						list: list,
						listings: listings,
						<?php if ($group['listings']['shortcode'] == 'search_listings'):?>
							'class': '.pls_search_form_listings',
						<?php else: ?>
							// static listings should ignore the search form
							'class': '.no_search_form__',
						<?php endif ?>
					});

					list.init({
						filter : filter,
						listings: listings,
						context: '<?php echo $group['listings']['atts']['context'] ?>',
						dom_id: '#<?php echo $group['listings']['atts']['table_id'] ?>',
						limit_default: <?php echo $group['listings']['atts']['query_limit'] ?>
					});

					<?php if (!empty($group['listings']['filters'])): ?>
						<?php echo $group['listings']['filters'] ?>
					<?php endif ?>
					listings.init();
				<?php endif ?>

				// if we paginate we want to be able to scroll results to the top
				$('body').on('click', '#placester_listings_list_wrapper .paginate_button', function(){
					$('html,body').animate({scrollTop: $('#<?php echo $group['listings']['atts']['table_id'] ?>_anchor').offset().top}, 1000);
				});

			});
		</script>
		<?php
		}

		if (!empty($js_files['filters'])) {
			wp_enqueue_script('pl-filters.js', PLACESTER_PLUGIN_URL . 'placester-search/js/filters.js', array('jquery'));
		}
		if (!empty($js_files['lifestyle'])) {
			wp_enqueue_script('pl-lifestyle.js', PLACESTER_PLUGIN_URL . 'placester-maps/js/lifestyle.js', array('jquery'));
		}
		if (!empty($js_files['lifestyle_polygon'])) {
			wp_enqueue_script('pl-lifestyle_polygon.js', PLACESTER_PLUGIN_URL . 'placester-maps/js/lifestyle_polygon.js', array('jquery'));
		}
	}
}
