<?php
/**
 * Post type/Shortcode to generate a property search form
 *
 */

class PL_Form_CPT extends PL_SC_Base {

	protected $pl_post_type = 'pl_form';

	protected $shortcode = 'search_form';

	protected $title = 'Search Form';

	protected $help =
		'<p>
		You can insert your "activated" Search Form snippet by using the [search_form] shortcode in a page or a post.
		This control is intended to be used alongside the [search_listings] shortcode to display the search
		form\'s results.
		</p>';

	protected $options = array(
		'context'			=> array( 'type' => 'select', 'label' => 'Template', 'default' => '' ),
		'width'				=> array( 'type' => 'int', 'label' => 'Width', 'default' => 250, 'description' => '(px)' ),
		'height'			=> array( 'type' => 'int', 'label' => 'Height', 'default' => 250, 'description' => '(px)' ),
		'widget_class'	=> array( 'type' => 'text', 'label' => 'CSS Class', 'default' => '', 'description' => '(optional)' ),
		'formaction'		=> array( 'type' => 'text', 'label' => 'Enter URL of listings page', 'default' => '', 'description'=>'Only required if different from the page where this shortcode will be used.' ),
		'modernizr'			=> array( 'type' => 'checkbox', 'label' => 'Drop Modernizr', 'default' => false ),
	);

	//TODO build from the api
	protected $subcodes = array(
		'bedrooms'			=> array('help' => 'Drop list to select an exact number of bedrooms.'),
		'min_beds'			=> array('help' => 'Drop list to select the minimum number of bedrooms.'),
		'max_beds'			=> array('help' => 'Drop list to select the maximum number of bedrooms.'),
		'bathrooms'			=> array('help' => 'Drop list to select an exact number of bathrooms.'),
		'min_baths'			=> array('help' => 'Drop list to select the minimum number of bathrooms.'),
		'max_baths'			=> array('help' => 'Drop list to select the maximum number of bathrooms.'),
		'property_type'		=> array('help' => 'Drop list to select the property type.'),
		'listing_types'		=> array('help' => 'Drop list to select the listing type (housing/land/etc).'),
		'zoning_types'		=> array('help' => 'Drop list to select the zoning (commercial/residential/etc).'),
		'purchase_types'	=> array('help' => 'Drop list to select the purchase type (rent/buy).'),
		'available_on'		=> array('help' => 'Drop list to select date of when the property should be available.'),
		'neighborhood'		=> array('help' => 'Drop list to select a neighborhood.'),
		'cities'			=> array('help' => 'Drop list to select a city.'),
		'states'			=> array('help' => 'Drop list to select a state.'),
		'zips'				=> array('help' => 'Drop list to select a zip/postal code.'),
		'min_price'			=> array('help' => 'Drop list to select the minimum price.'),
		'max_price'			=> array('help' => 'Drop list to select the maximum price.'),
		'min_price_rental'	=> array('help' => 'Drop list to select the minimum rental price.'),
		'max_price_rental'	=> array('help' => 'Drop list to select the maximum rental price.'),
		'custom'			=> array('help' => 'Use to create a form element for a custom listing attribute (or to make a customized version of one of the built in tags above).<br />
Format is as follows:<br />
<code>[custom attribute=\'some_attribute_name\' type=\'select\' options=\'|Any,5,10\' value=\'some_value\' css=\'some_css_class_name\']</code><br />
where:<br />
<code>attribute</code> - (required) The unique identifier of the custom listing attribute.<br />
<code>group</code> - (default is \'metadata\') Group of data that the attribute belongs to, for example <code>location</code>, <code>rets</code> <code>metadata</code>.<br />
<code>type</code> - (default is \'text\') Can be <code>text</code>, <code>textarea</code>, <code>radio</code>, <code>checkbox</code>, <code>select</code> or <code>multiselect</code>. Used to indicate what type of form element should be created.<br />
<code>options</code> - (required if type is set to <code>select</code> or <code>multiselect</code>) Contains a comma separated list of options that should be displayed in the select list.
If the text displayed in the list is to be different from the value for that item, then the list entry for that item would be of the form <code>5|Five</code>, for example <code>options=\'|Any,5,10,20\'</code> would create a list containing \'Any,5,10\' with corresponding values of \'null,5,10\'.<br />
<code>value</code> - (required for type <code>radio</code>, optional for others) Contains the default value for the field.<br />
<code>strict_match</code> - (optional, default true) Set to <code>false</code> to match similar values. 
In the following example a search field is created for the custom attribute \'roofing\'. If the user entered a value of \'shingle\' the results would contain listings that had the word \'shingle\' in the roof attribute, for example \'cedar shingle\', \'Composite Shingles\', etc:<br />
<code>[custom attribute=\'roofing\' strict_match=\'false\']</code><br />
<code>css</code> - (optional) Use this if you wish to set one or more css class names to the element.'),
	);

	protected $template = array(
		'snippet_body'	=> array(
			'type' => 'textarea',
			'label' => 'HTML',
			'css' => 'mime_html',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description'	=> 'You can use any valid HTML in this field to format the template tags.'
		),

		'css' => array(
			'type' => 'textarea',
			'label' => 'CSS',
			'css' => 'mime_css',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid CSS in this field to customize the form, which will also inherit the CSS from the theme.'
		),

		'before_widget'	=> array(
			'type' => 'textarea',
			'label' => 'Add content before the form',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid HTML in this field and it will appear before the form. For example, you can wrap the whole form with a <div> element to apply borders, etc, by placing the opening <div> tag in this field and the closing </div> tag in the following field.'
		),

		'after_widget'	=> array(
			'type' => 'textarea',
			'label' => 'Add content after the form',
			'default' => '',	// loaded dynamically from views/shortcodes
			'description' => 'You can use any valid HTML in this field and it will appear after the form.'
		),
	);

	private static $singleton = null;
	private static $form_data = array();




	public static function init() {
		self::$singleton = parent::_init(__CLASS__);
	}

	public static function do_templatetags($content, &$data) {
		self::$form_data = &$data;
		return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
	}

	public static function templatetag_callback($m) {
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}

		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		if ( isset( self::$form_data[$tag] ) ) {
			// use form data from partial to construct
			return $m[1] . self::$form_data[$tag] . $m[6];
		}
		elseif ($tag == 'custom' && !empty($attr['attribute'])) {
			$attr = wp_parse_args($attr, array('group'=>'metadata', 'type'=>'text', 'strict_match' => '', 'value' => '', 'css_class' => ''));
			$field = $m[1] . self::form_item($attr['attribute'], $attr, $attr['value'], $attr['group']) . $m[6];
			if (!empty($attr['strict_match']) && $attr['strict_match']=='false')  {
				$field .= self::form_item($attr['attribute'].'_match', array('type'=>'hidden'), 'like', $attr['group']);
			}
			return self::wrap( 'search_form_sub', $field );
		}
		else {
			return $m[0];
		}
	}
}

PL_Form_CPT::init();
