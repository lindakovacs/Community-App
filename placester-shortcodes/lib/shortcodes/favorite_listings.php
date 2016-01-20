<?php
/**
 * Post type/Shortcode to generate a list of listings
 *
 */
include_once('search_listings.php');

class PL_Favorite_Listing_CPT extends PL_Search_Listing_CPT {

	protected $shortcode = 'favorite_listings';

	protected $title = 'Favorite Listings';

	protected $help =
		'<p>
		You can insert your Favorite Listings snippet by using the [favorite_listings id="<em>listingid</em>"] shortcode in a page or a post.
		</p>';

	protected $options = array(
		'context'		=> array( 'type' => 'select', 'label' => 'Template', 'default' => '' ),
		'width'			=> array( 'type' => 'int', 'label' => 'Width', 'default' => 250, 'description' => '(px)' ),
		'height'		=> array( 'type' => 'int', 'label' => 'Height', 'default' => 250, 'description' => '(px)' ),
		'widget_class'	=> array( 'type' => 'text', 'label' => 'CSS Class', 'default' => '', 'description' => '(optional)' ),
	);

	private $_template = array(
		'no_listings'	=> array(
				'type' => 'textarea',
				'label' => 'HTML to display if the user has not set any favorites',
				'css' => 'mime_html',
				'default' => '<p>You have not added any properties to your favorites list yet.</p>',
				'description' => 'You can use any valid HTML in this field.'
		),
				
		'not_logged_in'	=> array(
				'type' => 'textarea',
				'label' => 'HTML to display if the user is not logged in',
				'css' => 'mime_html',
				'default' => '<p>Please login to view your favorite listings.</p>',
				'description' => 'You can use any valid HTML in this field.'
		)
	);



	public static function init() {
		parent::_init(__CLASS__);
	}

	public function __construct() {
		parent::__construct(__CLASS__);
		$this->template = $this->_template + $this->template;
		add_shortcode($this->shortcode, array($this, 'shortcode_handler'));
	}

	public function get_options_list($with_choices = false) {
		return $this->options;
	}

	public function get_filters_list($with_choices = false) {
		return array();
	}

	/**
	 * Called when a shortcode is found in a post.
	 * @param array $atts
	 * @param string $content
	 */
	public function shortcode_handler($atts) {
		if (!empty($atts['id'])) {
			// if we are a custom shortcode fetch the record so we can display the correct options
			$options = PL_Shortcode_CPT::get_shortcode_options('favorite_listings', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}

		$atts = wp_parse_args($atts, array('limit' => 0, 'sort_type' => ''));
		$context = empty($atts['context']) ? 'shortcode' : $atts['context'];
		$atts['context'] = 'favorite_listings_'.$context;
		$atts['property_ids'] = PL_Favorite_Listings::get_favorite_properties();
		
		if (!has_filter('pls_listings_' . $atts['context'])) {
			add_filter('pls_listings_' . $atts['context'], array('PL_Component_Entity','pls_listings_callback'), 10, 5);
			add_filter('pls_listing_' . $atts['context'], array('PL_Component_Entity','pls_listing_callback'), 10, 4);
		}

		if (empty($atts['property_ids']) && !is_admin()) {
			$template = PL_Shortcode_CPT::load_template($context, 'favorite_listings');
			if (is_user_logged_in()) {
				if (isset($template['no_listings'])) {
					return $template['no_listings'];
				}
				// TODO: move to load_template
				return $this->template['no_listings']['default'];
			}
			if (isset($template['not_logged_in'])) {
				return $template['not_logged_in'];
			}
			// TODO: move to load_template
			return $this->template['not_logged_in']['default'];
		}

		return PLS_Partials::get_listings($atts);
	}
}

PL_Favorite_Listing_CPT::init();