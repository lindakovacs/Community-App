<?php

/****** 
	This class defines the shortcodes that can be used to access blueprint 
	functionality from any page or post.

	It handles both the implementation of each code in addition to hooking 
	them into the proper events and filters.
******/

PL_Shortcodes::init();
class PL_Shortcodes
{
	public static $codes = array(
		'search_form', 
		'search_listings', 
		'prop_details', 
		'search_map', 
		'listing_slideshow', 
		'advanced_slideshow',
		'featured_listings', 
		'static_listings', 
		'post_listing', 
		'pl_neighborhood'
	);

	// TODO: Construct these lists dynamically by examining the doc hierarchy...
	public static $defaults = array(
		'search_form' => array('twentyten', 'twentyeleven'),
		'search_listings' => array('twentyten', 'twentyeleven'),
		'prop_details' => array('twentyten', 'twentyeleven'),
		'search_map' => array('twentyten', 'twentyeleven'),
		'listing_slideshow'	=> array('twentyten', 'twentyeleven'),
		'advanced_slideshow' => array('twentyten', 'twentyeleven'),
		'featured_listings' => array('twentyten', 'twentyeleven'),
		'static_listings' => array('twentyten', 'twentyeleven'),
		'post_listing' => array('twentyten', 'twentyeleven'),
		'pl_neighborhood' => array('twentyten', 'twentyeleven'),
		'listings' => array('twentyten', 'twentyeleven') 
	);

	public static $subcodes = array(
		'search_form' => array(
		),
		'listing' => array(
			'price',
			'sqft',
			'beds',
			'baths',
			'half_baths',
			'avail_on',
			'url',
			'address',
			'locality',
			'region',
			'postal',
			'neighborhood',
			'county',
			'country',
			'coords',
			'unit',
			'full_address',
			'email',
			'phone',
			'desc',
			'image',
			'mls_id',
			'map',
			'listing_type',
			'gallery',
			'amenities',
			'price_unit',
			//'compliance'
		),
		'neighborhood' => array(
			'nb_title',
			'nb_featured_image',
			'nb_description',
			'nb_link',
			'nb_map'
		),
		'listing_slideshow' => array(
			'ls_index',
			'ls_url',
			'ls_address',
			'ls_beds',
			'ls_baths',
		),
	);

	// TODO: These are a temporary solution, come up with a better convention...
	public static $form_html = false;
	public static $listing = false;
	public static $prop_details_enabled_key = 'pls_prop_details_enabled';

	public static function init() {
		// pulls in all the macro shortcodes, static list defined above
		foreach (self::$codes as $shortcode) {
			add_shortcode($shortcode, array(__CLASS__, $shortcode . '_shortcode_handler'));			
		}

		// For any shortcodes that use subcodes, register them to a single handler that bears the shortcode's name
		/* no, these can't be used as standalones, outside of their proper context
		foreach (self::$subcodes as $code => $subs) {
			foreach ($subs as $sub) {
				add_shortcode($sub, array(__CLASS__, $code . '_sub_shortcode_handler'));
			}
		}
		*/

		// register several helpful shortcodes for filters, for metadata, locaiton and common
		add_shortcode('pl_filter', array(__CLASS__, 'pl_filter_shortcode_handler'));
		
		// Register hooks to customize the html for the wrapper functions
		/* we dont want to do this here		
		add_filter('pls_listings_search_form_outer_shortcode', array(__CLASS__, 'searchform_shortcode_context'), 10, 6);
		add_filter('pls_listings_list_ajax_item_html_shortcode', array(__CLASS__, 'listings_shortcode_context'), 10, 3);
		add_filter('property_details_filter', array(__CLASS__, 'prop_details_shortcode_context'), 10, 2);
		*/

		// TODO: sc cleanup
		// Ensure all of shortcodes are set to some snippet...
		foreach (self::$codes as $code) {
			add_option( ('pls_' . $code), self::$defaults[$code][0] );
		}
		
		// Separately register the Compliance shortcode as it's not completely relevant
		// to the widget types
		add_shortcode( 'compliance', array( __CLASS__, 'compliance_shortcode_handler' ) );

		// Handle the special case of turning property details functionality on/off...
		add_option( self::$prop_details_enabled_key, 'false' ); 

		//basically initializes the bootloader object if it's been defined because a
		//shortcode has been called
		add_action('wp_footer', array(__CLASS__, 'init_bootloader'));
	}


	/*** Shortcode Handlers ***/


	/**
	 * [compliance] handler
	 */
	public static function compliance_shortcode_handler( $atts ) {
		$context = PL_Listing_Helper::get_listing_in_loop() ? 'listings' : 'search';

		ob_start();
		PLS_Listing_Helper::get_compliance(array(
			'context' => $context
		));
		$content = ob_get_clean();

		return self::wrap( 'compliance', $content );
		
	} 
	
	/**
	 * [search_form] handler
	 */
	public static function search_form_shortcode_handler($atts) {
		$content = PL_Component_Entity::search_form_entity( $atts );
		
		return self::wrap( 'search_form', $content );
	}
	
	public static function neighborhood_shortcode_handler($atts) {
		//$content = PL_Component_Entity::search_form_entity( $atts );
		$content = '';
		
		return self::wrap( 'neighborhood', $content );
	}

	/**
	 * [listing_slideshow] handler
	 */
	public static function listing_slideshow_shortcode_handler ($atts) {
		$content = PL_Component_Entity::listing_slideshow( $atts );
		
		return self::wrap( 'listing_slideshow', $content );
	}

	public static function advanced_slideshow_shortcode_handler ($atts) {
		$content = PL_Component_Entity::listing_slideshow( $atts, false );
		
		return self::wrap( 'advanced_slideshow', $content );
	}
	
	/**
	 * [featured_listings] handler
	 */
	public static function featured_listings_shortcode_handler ($atts, $content = '') {

		$content = PL_Component_Entity::featured_listings_entity( $atts );
		
		return self::wrap( 'featured_listings', $content );	
	}
	
	/**
	 * [static_listings] handler
	 */
	public static function static_listings_shortcode_handler ( $atts, $content = '' ) {
		add_filter('pl_filter_wrap_filter', array( __CLASS__, 'pl_filter_wrap_default_filters' ));
		$filters = '';

		// call do_shortcode for all pl_filter shortcodes
		// Note: don't leave whitespace or other non-valuable symbols
		if( ! empty( $content ) ) {
			$filters = do_shortcode( strip_tags( $content ) );
		}
		$filters = str_replace('&nbsp;', '', $filters);
				
		$content = PL_Component_Entity::static_listings_entity( $atts, $filters );
		
		return self::wrap( 'static_listings', $content );
	}

	/**
	 * [search_listings] handler
	 */
	public static function search_listings_shortcode_handler( $atts, $content ) {		
		add_filter('pl_filter_wrap_filter', array( __CLASS__, 'pl_filter_wrap_default_filters' ));
		$filters = '';
		
		// call do_shortcode for all pl_filter shortcodes
		// Note: don't leave whitespace or other non-valuable symbols
		if( ! empty( $content ) ) {
			$filters = do_shortcode( strip_tags( $content ) );
		}
		
		$filters = str_replace('&nbsp;', '', $filters);
		
		// Handle attributes using shortcode_atts...
		// These attributes will hand the look and feel of the listing form container, as 
		// the context func applies to each individual listing.
		$content = PL_Component_Entity::search_listings_entity( $atts, $filters );
		
		return self::wrap( 'search_listings', $content );
	}

	/**
	 * [search_map] handler
	 */
	public static function search_map_shortcode_handler( $atts ) {
		$content = PL_Component_Entity::search_map_entity( $atts );
		
		return self::wrap( 'search_map', $content );
	}
	

	public static function pl_neighborhood_shortcode_handler( $atts ) {
		$content = PL_Component_Entity::pl_neighborhood_entity( $atts );
	
		return self::wrap( 'pl_neighborhood', $content );
	}

	
	/*** Context Filter Handlers ***/	


	/**
	 * Get search form body from template
	 * -
	 */
	public static function searchform_shortcode_context($form, $form_html, $form_options, $section_title, $form_data) {
		$shortcode = 'search_form';
		self::$form_html = $form_html;

		$snippet_body = self::get_active_snippet_body($shortcode);
		return do_shortcode($snippet_body);
	}

	/**
	 *  It's important to note that this is called for every individual listing...
	 *  -
	 */
	public static function listings_shortcode_context($item_html, $listing) {
		$shortcode = 'listings';
		self::$listing = $listing;

	  	$snippet_body = self::get_active_snippet_body($shortcode);
	  	return do_shortcode($snippet_body);
	}

	/**
	 * -
	 */
	public static function prop_details_shortcode_context($html, $listing_data)	{
		// Check to see if this functionality is enabled...
		$enabled = get_option( self::$prop_details_enabled_key, 'false' );
		
		if ($enabled == 'true') 
		{
			$shortcode = 'prop_details';
			self::$listing = $listing_data;

		  	$snippet_body = self::get_active_snippet_body($shortcode);
		  	return do_shortcode($snippet_body);
	  	}
	  	else 
	  	{
	  		// Simply pass on what was originally sent the filter...
	  		return $html;
	  	}
	}

	/**
	 * Format single property listing
	 */
	public static function single_listing_template( $template, $listing ) {
		self::$listing = $listing;
	
		return do_shortcode($template);
	}
	
	
/*** Sub-Shortcode Handlers ***/

	
	/**
	 * -
	 */
	public static function asearch_form_sub_shortcode_handler ($atts, $content, $tag) { 
		return isset( self::$form_html[$tag] ) ? self::$form_html[$tag] : '';
	}

	public static function alisting_sub_shortcode_handler ($atts, $content, $tag) {
		$content = PL_Component_Entity::listing_sub_entity( $atts, $content, $tag );
		
		return self::wrap( 'listing_sub', $content );
	}
	
	public static function listing_slideshow_sub_shortcode_handler ($atts, $content, $tag) {
		$content = PL_Component_Entity::listing_slideshow_sub_entity( $atts, $content, $tag );
	
		return self::wrap( 'listing_slideshow_sub', $content );
	}
	
	public static function neighborhood_sub_shortcode_handler ($atts, $content, $tag) {
		$content = PL_Component_Entity::neighborhood_sub_entity( $atts, $content, $tag );
	
		return self::wrap( 'neighborhood_sub', $content );
	}
	
	/** Helpcode shortcode handler **/

	/**
	 * Handle filters for listing
	 * 
	 * Expected attributes:
	 * 
	 * group - group="metadata" or group="location", for wrapping filter calls by group
	 * filter - filter="listing_types", filter="zoning_types" and used together with a group call
	 * value - the value of the filter
	 * 
	 * //TODO: merge with component_entities convert_filters
	 * 
	 * @param unknown_type $atts
	 * @param unknown_type $content
	 */
	public static function pl_filter_shortcode_handler( $atts, $content = '' ) {
		$out = '';
		$av_filters = PL_Shortcode_CPT::get_listing_filters();
		
		if( !isset( $atts['filter'] ) || ! isset( $atts['value'] ) ) {
			return "";
		}
		
		extract($atts);
		
		$filterlogic = $filter . '_match';
		$av_filter = $filter;
		$filterstr = $filter;
		if( isset( $group ) ) {
			$filterstr = $group . '[' . $filter . ']';
			$filterlogic = $group . '[' . $filterlogic . ']';
			$av_filter = $group . '.' . $av_filter;
		}
		elseif( $filterstr == 'zoning_types' || $filterstr == 'purchase_types') {
			$filterstr .= '[]';
		}
		$jsfilter = '';
		if (strpos($value, '||') !==false ) {//print_r($atts);die;
			$values = explode('||', $value);
			if (count($values) > 1) {
				if (substr($filterstr, -2) != '[]') $filterstr .= '[]';
				$jsfilter .= apply_filters('pl_filter_wrap_filter', "{ 'name': '" . $filterlogic . "', 'value' : 'in'} ");
			}
			foreach ($values as $value) {
				$jsfilter .= apply_filters('pl_filter_wrap_filter', "{ 'name': '" . $filterstr . "', 'value' : '" . $value . "'} ");
			}
		}
		else {
			if (!empty($av_filters[$av_filter]['type']) && ($av_filters[$av_filter]['type']=='text' || $av_filters[$av_filter]['type']=='textarea')) {
				switch ($av_filters[$av_filter]['attribute']) {
					case 'aid':
					case 'oid':
						$match_by = 'exact';
						break;
					default:
						$match_by = 'like';
				}
				$jsfilter .= apply_filters('pl_filter_wrap_filter', "{ 'name': '$filterlogic', 'value' : '$match_by'} ");
			}
			$jsfilter .= apply_filters('pl_filter_wrap_filter', "{ 'name': '" . $filterstr . "', 'value' : '" . $value . "'} ");
		}
		return $jsfilter;
	}	
	
	public static function pl_filter_wrap_default_filters ($filter) {
		return "listings.default_filters.push(" . trim( strip_tags( $filter ) ) . "); ";
	}

	
	/*** Helper Functions ***/

	
	/**
	 * Give themes a chance to wrap output and individual fields rendered by shortcodes  
	 */
	public static function wrap( $shortcode, $content = '' ) {
		ob_start();
		do_action( $shortcode . '_pre_header' );
		// do some real shortcode work
		echo $content;
		do_action( $shortcode . '_post_footer' );
		return ob_get_clean();
	}
	
	/**
	 * Get the body for a shortcode's output from a template
	 */
	public static function get_active_snippet_body ($shortcode, $template_name = '') {
		$html = '';
		$template = PL_Shortcode_CPT::load_template($template_name, $shortcode);
		if (!empty($template['snippet_body'])) {
			$html = $template['snippet_body'];
		}
		return $html;
	}

	public static function init_bootloader () {
		ob_start();
		?>
			<script type="text/javascript">
			jQuery(document).ready(function( $ ) {
				if (typeof bootloader === 'object') {
		  			bootloader.init();
			  	}	
			});
			</script>
		<?php
		echo ob_get_clean();
	}

	
	/*** Admin Functions ***/

	
	/**
	 * Buffer shortcode admin pages to give us a chance to redirect if necessary
	 */
	public static function admin_buffer_op($page_hook) {
		add_action('load-'.$page_hook, array(__CLASS__, 'admin_header'));
		add_action('admin_footer-'.$page_hook, array(__CLASS__, 'admin_footer'));
	}	

	public static function admin_header() {
		ob_start();
	}	
	
	public static function admin_footer() {
		ob_end_flush();
	}
	
	public static function debug($var, $lines = 1) {
		echo '<pre>';
		$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		for ($trace=1; $trace<=$lines; $trace++) {
			echo $traces[$trace]['file'].':'.(!empty($traces[$trace]['class'])?$traces[$trace]['class'].':':'').$traces[$trace]['function'].':'.(!empty($traces[$trace]['line'])?$traces[$trace]['line'].':':'')."\n";
		}
		var_dump($var);
		echo '</pre>';
	}
}

?>