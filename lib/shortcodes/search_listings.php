<?php
/**
 * Post type/Shortcode to generate a list of listings
 *
 */
class PL_Search_Listing_CPT extends PL_SC_Base {

	protected $pl_post_type = 'pl_search_listings';

	protected $shortcode = 'search_listings';

	protected $title = 'Search Listings';

	protected $help =
		'<p>
        You can insert your "activated" Listings snippet by using the [search_form] shortcode in a page or a post.
        The listings view is intended to be used alongside the [search_form] shortcode defined above as a container
        for the results of the search, with the snippet representing how an <i>individual</i> listing that matches
        the search criteria will be displayed.
		</p>';

	protected $options = array(
		'context'			=> array( 'type' => 'select', 'label' => 'Template', 'default' => '' ),
		'width'				=> array( 'type' => 'int', 'label' => 'Width', 'default' => 250, 'description' => '(px)' ),
		'height'			=> array( 'type' => 'int', 'label' => 'Height', 'default' => 250, 'description' => '(px)' ),
		'widget_class'	=> array( 'type' => 'text', 'label' => 'CSS Class', 'default' => '', 'description' => '(optional)' ),
		'sort_by_options'	=> array( 'type' => 'multiselect', 'label' => 'Items in "Sort By" list', 
			'options'	=> array(	// options we always want to show even if they are not part of the filter set
				'location.address'	=> 'Address', 
				'cur_data.price'	=> 'Price',
				'cur_data.sqft'		=> 'Square Feet',
				'cur_data.lt_sz'	=> 'Lot Size',
				'compound_type'		=> 'Listing Type',
				'cur_data.avail_on'	=> 'Available On',
			),
			'default'	=> array('cur_data.price','cur_data.beds','cur_data.baths','cur_data.sqft','location.locality','location.postal'), 
		),
		'sort_by'			=> array( 'type' => 'select', 'label' => 'Default sort by', 'options' => array(), 'default' => 'cur_data.price' ),
		'sort_type'			=> array( 'type' => 'select', 'label' => 'Default sort direction', 'options' => array('asc'=>'Ascending', 'desc'=>'Descending'), 'default' => 'desc' ),
		// TODO: sync up with js list			
		'query_limit'		=> array( 'type' => 'select', 'label' => 'Default number of results', 'options' => array('10'=>'10', '25'=>'25', '25'=>'25', '50'=>'50'), 'default' => '10' ),
	);

	protected $subcodes = array(
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
		'image'			=> array('help' => 'Property thumbnail image. You can use <code>width</code> and <code>height</code> to set the dimensions of the thumbnail in pixels, for example: <code>[image width=\'180\' height=\'120\']</code>'),
		'image_url'		=> array('help' => 'Image URL for the listing if one exists. You can use the optional <code>index</code> attribute (defaults to 0, the first image) to specify the index of the listing image and <code>placeholder</code> to specify the URL of an image to use if the listing does not have an image, for example: <code>[image_url index=\'1\' placeholder=\'http://www.domain.com/path/to/image\']</code>'),
		'mls_id'		=> array('help' => 'MLS #'),
		'listing_type'	=> array('help' => 'Type of listing'),
		//'gallery'		=> array('help' => 'Image gallery'),
		//'amenities'	=> array('help' => 'List of amenties'),
		'price_unit'	=> array('help' => 'Unit price'),
		'compliance'	=> array('help' => 'MLS compliance statement'),
		'favorite_link_toggle' => array('help' => 'Link to add/remove from favorites'),
		'aname'			=> array('help' => 'Agent name'),
		'oname'			=> array('help' => 'Office name'),
		'custom'		=> array('help' => 'Use to display a custom listing attribute.<br />
Format is as follows:<br />
<code>[custom group=\'group_name\' attribute=\'some_attribute_name\' type=\'text\' value=\'some_value\']</code><br />
where:<br />
<code>group</code> - The group identifier if the listing attribute is part of a group. Possible values are <code>location</code>, <code>rets</code>, <code>metadata</code>, <code>uncur_data</code>.<br />
<code>attribute</code> - (required) The unique identifier of the listing attribute.<br />
<code>type</code> - (optional, default is \'text\') Can be <code>text</code>, <code>currency</code>, <code>list</code>. Used to indicate how the attribute should be formatted.<br />
<code>value</code> - (optional) Indicates text to be displayed if the listing attribute is empty.
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

	protected $default_tpls = array('twentyten', 'twentyeleven', 'responsive', 'twocolumn');

	protected $template = array(
		'snippet_body'	=> array(
			'type' => 'textarea',
			'label' => 'HTML to format each individual listing',
			'css' => 'mime_html',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid HTML in this field to format the template tags.'
		),

		'css' => array(
			'type' => 'textarea',
			'label' => 'CSS',
			'css' => 'mime_css',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid CSS in this field to customize the listings, which will also inherit the CSS from the theme.'
		),

		'before_widget'	=> array(
			'type' => 'textarea',
			'label' => 'Add content before the listings',
			'css' => 'mime_html',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid HTML in this field and it will appear before the listings. For example, you can wrap the whole list with a <div> element to apply borders, etc, by placing the opening <div> tag in this field and the closing </div> tag in the following field.'
		),

		'after_widget'	=> array(
			'type' => 'textarea',
			'label' => 'Add content after the listings',
			'css' => 'mime_html',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid HTML in this field and it will appear after the listings. It is suggested that you include the [compliance] shortcode to display the compliance statement for your MLS.'
		),
	);

	// stores fetched listing attributes value
	protected static $sl_listing_attributes = array();

	// stores sort list
	protected static $sl_sort_list = array();

	// stores fetched filter value
	protected static $sl_filter_options = array();

	private static $singleton = null;


	public static function init() {
		self::$singleton = parent::_init(__CLASS__);
	}

	/**
	 * Get list of filter options from the api.
	 */
	public function get_options_list($with_choices = false) {
		if (empty(self::$sl_listing_attributes)) {
			self::$sl_listing_attributes = PL_Shortcode_CPT::get_listing_attributes(true);
			self::$sl_sort_list = array();
			foreach(self::$sl_listing_attributes as $args) {
				$group = $args['group'];
				switch($group) {
					case 'metadata':
						$group = 'cur_data';
						break;
					case 'custom':
						$group = 'uncur_data';
						break;
					case 'rets':
						continue;
						break;

				}
				$key = (empty($group) ? '' : $group.'.').$args['attribute'];
				self::$sl_sort_list[$key] = $args['label'];
			}
		}
		if (!empty(self::$sl_sort_list)) {
			// save the full list of sort by names so we can use on the front end
			update_option('pl_'.$this->shortcode.'_formval_sort_by_options', self::$sl_sort_list);
		}
		$this->options['sort_by_options']['options'] = self::$sl_sort_list;
		$this->options['sort_by']['options'] = self::$sl_sort_list;		
		return $this->options;
	}

	/**
	 * Get list of filter options from the api.
	 */
	public function get_filters_list($with_choices = false) {
		if (empty(self::$sl_filter_options)) {
			self::$sl_filter_options = PL_Shortcode_CPT::get_listing_filters(false, $with_choices);
		}
		return self::$sl_filter_options;
	}
	
	public static function do_templatetags($content, $listing_data) {
		PL_Component_Entity::$listing = $listing_data;
		return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
	}

	public static function templatetag_callback($m) {
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
		
		$tag = $m[2];
		$atts = shortcode_parse_atts($m[3]);
		$content = $m[5];
		
		if ($tag == 'if') {
			$val = isset($atts['value']) ? $atts['value'] : null;
			if (empty($atts['group'])) { 
				if ((!isset(PL_Component_Entity::$listing[$atts['attribute']]) && $val==='') ||
					(isset(PL_Component_Entity::$listing[$atts['attribute']]) && (PL_Component_Entity::$listing[$atts['attribute']]===$val || (is_null($val) && PL_Component_Entity::$listing[$atts['attribute']])))) {
					return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
				}
			}
			elseif ((!isset(PL_Component_Entity::$listing[$atts['group']][$atts['attribute']]) && $val==='') ||
				(isset(PL_Component_Entity::$listing[$atts['group']][$atts['attribute']]) && (PL_Component_Entity::$listing[$atts['group']][$atts['attribute']]===$val || (is_null($val) && PL_Component_Entity::$listing[$atts['group']][$atts['attribute']])))) {
				return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
			}
			return '';
		}
		$content = PL_Component_Entity::listing_sub_entity( $atts, $content, $tag );
		return self::wrap( 'listing_sub', $content );
	}
}

PL_Search_Listing_CPT::init();
