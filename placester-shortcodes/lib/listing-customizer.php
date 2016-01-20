<?php
/**
 * Class for creating a custom listing page layout type.
 */

class PL_Listing_Customizer {

	const LIST_KEY = 'pl_listing_template_list';
	const LIST_COUNT_KEY = 'pl_listing_template_count';
	const MAP_KEY = 'pl_listing_template_map';
	const TPL_KEY = 'pl_listing_template__';

	// layout for template editor
	protected static $template = array(
		'before_widget'	=> array(
			'type' => 'textarea',
			'label' => 'Add content before the listing',
			'css' => 'mime_html',
			'default' => '',
			'description' => 'You can use any valid HTML in this field and it will appear before the listing.'
		),

		'snippet_body'		=> array(
			'type'			=> 'textarea',
			'label'			=> 'Page Body',
			'description'	=> '
You can use any valid HTML in this field to format the template tags.
If you leave this section empty the page will be rendered using the default template, which you can style using CSS in the block below.',
			'css'			=> 'mime_html', 					// used for CodeMirror
			'default'		=> '',
		),

		'after_widget'	=> array(
				'type' => 'textarea',
				'label' => 'Add content after the listing',
				'css' => 'mime_html',
				'default' => '[compliance]',
				'description' => 'You can use any valid HTML in this field and it will appear after the listing.
It is recommended that you include the [compliance] shortcode to display the compliance statement from your MLS.'
		),

		'css'	=> array(
			'type'			=> 'textarea',
			'label'			=> 'CSS',
			'description'	=> '
You can use any valid CSS in this field to customize the listing, which will also inherit the CSS from the theme.',
			'css'			=> 'mime_css', 						// used for CodeMirror
			'default'		=> '',
		),

	);
	// empty template when no template is selected
	private static $default_template = array( 'title' => '', 'before_widget' => '', 'snippet_body' => '', 'after_widget' => '', 'css' => '' );
	// list of built in templates
	private static $default_tpls = array();
	// holds currently selected template
	private static $active_template = array();




	/**
	 * Create an instance and hook in the listing templates
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'post_type_templating' ), 1 );
	}


	/**
	 * Return the parameters that describe the listing template object
	 */
	public static function get_args() {
		return array('template' => self::$template, 'template_tags' => PL_Component_Entity::$listing_tags);
	}


	/**
	 * Load the template by id
	 * Returns empty default if none found
	 */
	public static function get_template($id) {

		// see if its a custom one
		$tpl = self::_get_custom_template($id);
		if (!empty($tpl)) {
			return $tpl;
		}

		// get builtin/default templates
		$tpls = self::get_builtin_templates();
		if (in_array($id, array_keys($tpls))) {
			$template = array();
			$filename = PL_SHORTCODES_DIR . 'views/listings/' . $id . '.php';
			include $filename;
			return ($template + array('title'=>$id));
		}

		return self::$default_template;
	}


	/**
	 * Load a custom template by id
	 */
	private static function _get_custom_template($id) {
		if (strpos($id, self::TPL_KEY) === 0) {
			$data = get_option($id, null);
			if (is_array($data)) {
				return ($data + array('title' => ''));
			}
		}
		return null;
	}


	/**
	 * Save/create a template
	 */
	public static function save_template($id, $atts) {
		$atts = (array)$atts;
		// sanity check
		if (empty($atts['title']) || ($id && strpos($id, self::TPL_KEY)!==0)) {
			return '';
		}
		if (empty($id)) {
			$count = get_option(self::LIST_COUNT_KEY, 1) + 1;
			$id = self::TPL_KEY . $count;
			update_option(self::LIST_COUNT_KEY, $count);
		}
		$data = array_combine(array_keys(self::$template), array_pad(array(), count(self::$template), '')) + array('title'=>'', 'date'=>time());
		foreach($data as $key => &$val) {
			if (isset($atts[$key])) {
				$val = trim(stripslashes($atts[$key]));
			}
		}
		update_option($id, $data);
		self::build_tpl_list();
		return $id;
	}


	/**
	 * Delete a template
	 */
	public static function delete_template($id) {
		if (strpos($id, self::TPL_KEY) === 0) {
			delete_option($id);
			self::build_tpl_list();
		}
	}


	/**
	 * Get id of active template
	 */
	public static function get_active_template_id() {
		$map = self::get_listing_map();
		return $map['default'];
	}


	/**
	 * Get the active template
	 */
	public static function get_active_template() {
		if (empty(self::$active_template)) {
			$map = self::get_listing_map();
			self::$active_template = self::get_template($map['default']);
		}
		return self::$active_template;
	}


	/**
	 * Set the active template by id
	 */
	public static function set_active_template_id($id) {
		$tpl_list = self::get_template_list();
		if (empty($id) || in_array($id, array_keys($tpl_list))) {
			$map = array('default'=>$id);
			self::save_listing_map($map);
		}
	}


	/* Template list management */


	/**
	 * Return a list of all templates
	 */
	public static function get_template_list($all = false) {
		$tpl_type_map = array();

		// add default templates
		$default_tpls = self::get_builtin_templates(true);
		foreach ($default_tpls as $id => $name) {
			$tpl_type_map[$id] = array('type'=>'default', 'title'=>$name, 'id'=>$id);
		}

		// get custom templates
		$tpl_list = get_option(self::LIST_KEY, array());
		foreach ($tpl_list as $id => $name) {
			if ($id == self::TPL_KEY . '_preview' && !$all) continue;
			$tpl_type_map[$id] = array('type'=>'custom', 'title'=>$name, 'id'=>$id);
		}

		return $tpl_type_map;
	}


	/**
	 * Return a list of built-in templates as id/name pairs. By default does not fetch the actual name.
	 */
	public static function get_builtin_templates($getname = false) {
		if (empty(self::$default_tpls)) {
			if (file_exists($dir = PL_SHORTCODES_DIR . 'views/listings')) {
				foreach (new DirectoryIterator($dir) as $fileInfo) {
					if ($fileInfo->isDot()) continue;
					$matches = array();
					if (preg_match('/^(.+)\.php$/', $fileInfo->getFilename(), $matches)) {
						$template = array();
						if ($getname) {
							include $fileInfo->getPathname();
						}
						$template += array('title'=>$matches[1]);
						self::$default_tpls[$matches[1]] = $template['title'];
					}
				}
			}
		}
		return self::$default_tpls;
	}


	/**
	 * Rebuild the list of custom templates
	 */
	public static function build_tpl_list() {
		global $wpdb;
		$tpls = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE option_name LIKE '".str_replace('_', '\_', self::TPL_KEY)."%'");
		$tpl_list = array();
		foreach($tpls as $tpl){
			$tpl_data = get_option($tpl->option_name, array());
			if(empty($tpl_data['title'])) {
				$tpl_data['title'] = '';
			}
			$tpl_list[$tpl->option_name] = $tpl_data['title'];
		}

		uasort($tpl_list, array(__CLASS__, '_tpl_list_sort'));
		update_option(self::LIST_KEY, $tpl_list);
		return $tpl_list;
	}


	/**
	 * Comparator to sort template list in alphabetical order
	 */
	public static function _tpl_list_sort($a, $b) {
		return strcasecmp($a, $b);
	}


	/* Template usage mapping */


	public static function templates_in_use() {
		return array();
	}


	/**
	 * Save list of templates in use
	 */
	public static function save_listing_map($map) {
		update_option(self::MAP_KEY, $map);
	}


	/**
	 * Get list of templates in use
	 */
	public static function get_listing_map() {
		$map = get_option(self::MAP_KEY, array('default'=>''));
		return $map;
	}


	/**
	 * Get list of where given template is used
	 */
	public static function template_used_by($id) {
		$usedby = array();
		$map = self::get_listing_map();
		foreach($map as $type=>$tpl_id) {
			if ($tpl_id == $id) $usedby[] = $type;
		}
		return $usedby;
	}


	/* Render page */


	/**
	 * Called every time a post is rendered. Make sure we only hook into the output if this is a property listing
	 */
	public static function post_type_templating( $single, $skipdb = false ) {
		global $post;

		if( !empty($post) && $post->post_type == 'property') {
			$tpl = self::get_active_template();
			if (!empty($tpl['css'])) {
				// Hook in to render our css
				add_filter('wp_head', array( __CLASS__ ,'custom_property_details_css_filter'));
			}

			if (!empty($tpl['snippet_body'])) {
				// Hook in to render our template
				remove_all_filters('the_content');
			}

			if (!empty($tpl['before_widget'])) {
				// Hook in to render our before listing template
				add_filter('the_content', array( __CLASS__ ,'custom_property_details_before_widget_filter'), 1);
			}

			if (!empty($tpl['snippet_body'])) {
				// Hook in to render our template
				add_filter('the_content', array( __CLASS__ ,'custom_property_details_html_filter'));
			}

			if (!empty($tpl['after_widget'])) {
				// Hook in to render our after listing template
				add_filter('the_content', array( __CLASS__ ,'custom_property_details_after_widget_filter'), 15);
			}
		}
	}


	/**
	 * output css from the template
	 */
	public static function custom_property_details_css_filter() {
		echo '<style type="text/css">'.self::$active_template['css'].'</style>';
	}


	/**
	 * output the before listing content from the template
	 */
	public static function custom_property_details_before_widget_filter($content) {
		return $content.do_shortcode(self::$active_template['before_widget']);
	}


	/**
	 * output the body of the listing using the template
	 */
	public static function custom_property_details_html_filter($content) {
		global $post;

		$listing_data = PL_Listing_Helper::get_listing_in_loop();

		if (is_null($listing_data)) {
			return $content;
		}

		// add in js to init the map
		// TODO: move this to subcode handler?
		$js = "
			<script type=\"text/javascript\">
			jQuery(document).ready(function( $ ) {
				var map = new Map();
				var listing = new Listings({
					single_listing : ".json_encode($listing_data).",
					map: map
				});
				map.init({
					type: 'single_listing',
					listings: listing,
					lat : ".json_encode($listing_data['location']['coords'][0]).",
					lng : ".json_encode($listing_data['location']['coords'][1]).",
					zoom : 14
				});
				listing.init();
			});
			</script>
		";

		PL_Component_Entity::$listing = $listing_data;
		return $content.PL_Component_Entity::do_templatetags(array('PL_Component_Entity', 'listing_templatetag_callback'), array_keys(PL_Component_Entity::$listing_tags), self::$active_template['snippet_body']).$js;
	}


	/**
	 * output the before listing content from the template
	 */
	public static function custom_property_details_after_widget_filter($content) {
		return $content.do_shortcode(self::$active_template['after_widget']);
	}
}

PL_Listing_Customizer::init();