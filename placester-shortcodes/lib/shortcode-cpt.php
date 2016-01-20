<?php
/**
 * Manage available shortcodes that can be customized via the admin pages.
 * The customized shortcodes are stored as a custom post type of pl_general_widget.
 * Each references a shortcode template/layout that controls how its drawn.
 * The templates come from a file in the (Placester aware) theme or are user defined.
 */

class PL_Shortcode_CPT {

	// holds instances of shortcodes we have installed
	private static $shortcodes = array();
	// holds the configuration parameters for the shortcode classes we have installed
	private static $shortcode_config = array();




	/**
	 * Called by shortcode object to register itself
	 * @param string $shortcode	: shortcode
	 * @param object $instance	: instance of shortcode object
	 */
	public static function register_shortcode($shortcode, $instance) {
		self::$shortcodes[$shortcode] = $instance;
	}

	public function __construct() {
		$path = trailingslashit( __DIR__ ) . 'shortcodes/';
		include_once($path . 'sc_base.php');

		// get list of shortcodes that can be widgetized:
		if ($handle = opendir($path)) {
			$ignore = array('sc_base.php', 'pl_neighborhood.php');
			while (false !== ($file = readdir($handle))) {
				if (pathinfo($file, PATHINFO_EXTENSION) == 'php' && !(in_array($file, $ignore))) {
					include_once($path . $file);
				}
			}
			closedir($handle);
		}

		add_action( 'init', array( $this, 'register_post_type' ) );
		// sc editing
		add_filter( 'get_edit_post_link', array( $this, 'shortcode_edit_link' ), 10, 3);
		add_action( 'wp_ajax_pl_sc_changed', array( $this, 'ajax_shortcode_changed') );
		add_action( 'wp_ajax_pl_sc_preview', array( $this, 'shortcode_preview') );
		// tpl editing
		add_action( 'wp_ajax_pl_sc_template_changed', array( $this, 'template_changed') );
		add_action( 'wp_ajax_pl_sc_template_preview', array( $this, 'template_preview') );
		// embedded sc support (fetch-widget.js)
		add_action( 'wp_ajax_handle_widget_script', array( $this, 'handle_iframe_cross_domain' ) );
		add_action( 'wp_ajax_nopriv_handle_widget_script', array( $this, 'handle_iframe_cross_domain' ) );
	}

	/**
	 * Register the CPT used to create customized shortcodes
	 */
	public function register_post_type() {

		// custom post type to hold a customized shortcode
		$args = array(
			'labels' => array(
				'name' => __( 'Custom Shortcodes', 'pls' ),
				'singular_name' => __( 'Custom Shortcode', 'pls' ),
				'add_new_item' => __('Add New Custom Shortcode', 'pls'),
				'edit_item' => __('Edit Custom Shortcode', 'pls'),
				'new_item' => __('New Custom Shortcode', 'pls'),
				'all_items' => __('All Custom Shortcodes', 'pls'),
				'view_item' => __('View Custom Shortcodes', 'pls'),
				'search_items' => __('Search Custom Shortcodes', 'pls'),
				'not_found' =>  __('No custom shortcodes found', 'pls'),
				'not_found_in_trash' => __('No custom shortcodes found in Trash', 'pls')),
			'menu_icon' => null,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title'),
		);

		register_post_type('pl_general_widget', $args );
	}


	/**
	 * Return a list of available shortcodes objects
	 * @return array	: array of shortcode types
	 */
	public static function get_shortcode_list() {
		return self::$shortcodes;
	}

	/**
	 * Return an array of shortcodes with their respective arguments that can be used to
	 * construct admin pages for creating a custom instance of a shortcode
	 * @return array	: array of shortcode type arrays
	 */
	public static function get_shortcode_attrs($shortcode='', $with_choices = false) {
		if ($shortcode) {
			if (empty(self::$shortcodes[$shortcode])) {
				return array();
			}
			if (empty(self::$shortcode_config[$shortcode]) || $with_choices) {
				$instance = self::$shortcodes[$shortcode];
				self::$shortcode_config[$shortcode] = $instance->get_args($with_choices);
			}
			return self::$shortcode_config[$shortcode];
		}
		if (empty(self::$shortcode_config) || $with_choices) {
			foreach(self::$shortcodes as $sc => $instance){
				self::$shortcode_config[$sc] = $instance->get_args($with_choices);
			}
		}
		return self::$shortcode_config;
	}


	/***************************************************
	 * Admin pages
	 ***************************************************/


	public function shortcode_edit_link($url, $ID, $context) {
		global $pagenow;
		if (get_post_type($ID) == 'pl_general_widget') {
			return admin_url('admin.php?page=placester_shortcode_edit&ID='.$ID);
		}
		return $url;
	}


	/***************************************************
	 * Custom Shortcode helper functions
	 ***************************************************/

	/**
	 * Called by js when editing - pass back enough info to generate the preview pane
	 */
	public function ajax_shortcode_changed() {
		$response = array('sc_str'=>'');

		// generate shortcode string
		if ( isset($_POST['shortcode']) && !empty($_POST[$_POST['shortcode']])) {
			$args = array_merge($_POST, $_POST[$_POST['shortcode']]);
			$response['sc_long_str'] = $this->generate_shortcode_str($_POST['shortcode'], $args);
			$response['sc_str'] = '';
			$response['width'] = $args['width'];
			$response['height'] = $args['height'];
		}

		header( "Content-Type: application/json" );
		echo json_encode($response);
		die;
	}


	/**
	 * Helper function to generate a shortcode string from a set of arguments
	 */
	public static function generate_shortcode_str($shortcode, $args) {
		if (empty(self::$shortcodes[$shortcode])) {
			return '';
		}
		return self::$shortcodes[$shortcode]->generate_shortcode_str($args);
	}

	/**
	 * Generate preview for the shortcode edit page.
	 */
	public function shortcode_preview() {

		$sc_str = '';
		$sc_id = (!empty($_GET['sc_id']) ? stripslashes($_GET['sc_id']) : '');
		if ($sc_id) {
			$sc_vals = $this->load_shortcode($sc_id);
			if (!empty($sc_vals)) {
				$sc_str = $this->generate_shortcode_str($sc_vals['shortcode'], $sc_vals);
			}
		}
		if (!empty($_GET['sc_str'])) {
			$sc_str = stripslashes($_GET['sc_str']);
		}

		include(PL_SHORTCODES_DIR . 'admin/views/preview.php');
		die;
	}

	/**
	 * Get filter settings for the custom shortcode
	 * @param string $sc_type	: shortcode type
	 * @param string $id		: id of a saved custom shortcode
	 * @return array
	 */
	public static function get_shortcode_filters($sc_type, $id) {
		if (empty(self::$shortcodes[$sc_type])) {
			return array();
		}
		return self::$shortcodes[$sc_type]->get_filters($id);
	}

	/**
	 * Get option settings for the custom shortcode
	 * @param string $sc_type	: shortcode type
	 * @param string $id		: id of a saved custom shortcode
	 * @return array
	 */
	public static function get_shortcode_options($sc_type, $id) {
		if (empty(self::$shortcodes[$sc_type])) {
			return array();
		}
		return self::$shortcodes[$sc_type]->get_options($id);
	}

	/**
	 * Handle cross-domain sc insertion using script embed.
	 * This is called by fetch-widget when embedded - pass back the template, and dimensions
	 * the embedded script will create an iframe wrapped with the template, then fetch the shortcode
	 * inside the iframe by fetching the post id of this shortcode in sc_base.php  
	 * This allows the body of the form, map, etc to be the size specified by the shortcode.
	 */
	public function handle_iframe_cross_domain() {
		// don't process if widget ID is missing
		if( ! isset( $_GET['id'] ) ) {
			die();
		}
			
		// defaults
		$args = array('width'=>'250', 'height'=>'250');

		// get the post and the meta
		$sc = $this->load_shortcode($_GET['id']);
		if (!empty($sc)) {
			// clean it up to just the options needed to wrap the shortcode body
			// (width, height, context, etc)
			$sc_attrs = self::get_shortcode_attrs($sc['shortcode']);
			foreach($sc as $key=>$val) {
				if (!empty($sc_attrs['options'][$key]) && !empty($val)) {
					$args[$key] = $val;
				}
			}
			// return the template if one is set, to use css, before, after 
			if (!empty($sc['context'])) {
				$args = array_merge($args, $this->load_template($sc['context'], $sc['shortcode']));
			}
		}
	
		$args['width'] = ! empty( $_GET['width'] ) ? $_GET['width'] : $args['width'];
		$args['height'] = ! empty( $_GET['height'] ) ? $_GET['height'] : $args['height'];
	
		unset( $args['action'] );
		unset( $args['callback'] );
	
		$args['post_id'] = $_GET['id'];
	
		// setup url for js to request the shortcode in embedded form
		$query = '&embedded=1';
		if (!empty($args['widget_class'])) {
			$query .= '&widget_class='.urlencode($args['widget_class']);
		}
		if( isset( $args['widget_original_src'] ) ) {
			$args['widget_url'] =  $args['widget_original_src'] . '/?p=' . $_GET['id'] . $query;
			unset( $args['widget_original_src'] );
		} else {
			$args['widget_url'] =  home_url() . '/?p=' . $_GET['id'] . $query;
		}
	
		header("content-type: application/javascript");
		echo $_GET['callback'] . '(' . json_encode( $args ) . ');';
	}
	
	
	/***************************************************
	 * Custom Shortcode storage functions
	 ***************************************************/


	/**
	 * Fetch custom shortcode attributes using record id.
	 * @param int $id			: shortcode record id
	 * @param string $shortcode	: optional shortcode type as sanity check
	 * @return array			: custom shortcode's attributes
	 */
	public static function load_shortcode($id, $shortcode='') {
		if ($post = get_post($id, ARRAY_A, array('post_type'=>'pl_general_widget'))) {
			$postmeta = get_post_meta($id);
			if (!empty($postmeta['shortcode'])) {
				$p_shortcode = $postmeta['shortcode'][0];
				if (!$shortcode || $p_shortcode==$shortcode) {
					$options = array();
					foreach($postmeta as $key=>$val) {
						if ($key=='pl_filters') {
							// filters
							$options = maybe_unserialize($val[0]);
							continue;
						}
						elseif ($key=='pl_featured_listing_meta') {
							// featured listings are stored as JSON
							$post[$key] = json_decode($val[0], true);
							continue;
						}
						elseif ($key=='pl_cpt_template') {
							$key = 'context';
						}
						$post[$key] = maybe_unserialize($val[0]);
					}
					$post = array_merge($post, $options);
				}
			}
			return $post;
		}
		return array();
	}

	/**
	 * Save custom shortcode attributes.
	 * @param int $id			: id of record to update, 0 for new record
	 * @param string $shortcode	: shortcode type as sanity check
	 * @param array $args		: custom shortcode's attributes
	 * @return int				: record id if saved
	 */
	public static function save_shortcode($id, $shortcode, $args) {
		$sc_attrs = self::get_shortcode_attrs($shortcode, true);
		if (!empty($sc_attrs)) {
			if ($id) {
				// sanity check and make sure we are not changing the shortcode type
				$post = get_post($id);
				$p_shortcode = get_post_meta($id, 'shortcode', true);
				if (empty($post) || $post->post_type!='pl_general_widget' || $p_shortcode != $shortcode) {
					$id = 0;
				}
			}
			if (!$id) {
				// creating new one or changing type
				$id = wp_insert_post(array('post_type'=>'pl_general_widget'));
			}
			if ($id) {

				$sc_str = self::generate_shortcode_str($shortcode, $args);
				wp_update_post(array('ID'=>$id, 'post_title'=>$args['post_title'], 'post_content'=>$sc_str, 'post_status'=>'publish'));
				update_post_meta( $id, 'shortcode', $shortcode);

				// Save options
				foreach( $sc_attrs['options'] as $option => $values ) {
					if ($option=='context') {
						$key = 'pl_cpt_template';
					}
					else {
						$key = $option;
					}
					switch($values['type']) {
						case 'checkbox':
							// in some places having the option set counts as on..
							if (empty($args[$option])) {
								// so delete if not set
								delete_post_meta($id, $key);
							}
							else {
								update_post_meta($id, $key, 'true');
							}
							break;
						case 'int':
							if( !empty($args) && !empty($args[$option])) {
								$args[$option] = (int)$args[$option];
							}
						case 'text':
							if( !empty($args) && !empty($args[$option])) {
								update_post_meta($id, $key, trim($args[$option]));
							}
							else {
								// save default in case default changes in the future
								update_post_meta( $id, $key, $values['default'] );
							}
							break;
						case 'featured_listing_meta':
							// featured listings (pl_featured_listing_meta field) save as json
							$val = $values['default'];
							if(!empty($args[$option])) {
								$val = $args[$option];
							}
							update_post_meta( $id, $key, json_encode($val, JSON_HEX_APOS) );
							break;
						case 'select':
						default:
							if( !empty($args) && !empty($args[$option])) {
								update_post_meta($id, $key, $args[$option]);
							}
							else {
								// save default in case default changes in the future
								update_post_meta( $id, $key, $values['default'] );
							}
							break;
					}
				}

				// Save filters - only save if they are valid
				$filters = array();
				foreach( $sc_attrs['filters'] as $filter ) {
					if ($filter['group']) {
						if (!empty($args[$filter['group']][$filter['attribute']])) {
							$filters[$filter['group']][$filter['attribute']] = $args[$filter['group']][$filter['attribute']];
						}						
					}
					elseif(!empty($args[$filter['attribute']])) {
						$filters[$filter['attribute']] = $args[$filter['attribute']];
					}
				}
				$db_key = 'pl_filters';
				update_post_meta($id, $db_key, $filters);
			}

			return $id;
		}
		return 0;
	}


	/***************************************************
	 * Shortcode Template helper functions
	 ***************************************************/


	/**
	 * Have to save settings as a template in order for preview to work. 
	 * Use the special id 'pls_<shortcode>__preview' for the preview template name.
	 */
	public function template_changed() {
		$response = array();
		$shortcode = (!empty($_POST['shortcode']) ? stripslashes($_POST['shortcode']) : '');
		$shortcode_args = $this->get_shortcode_attrs();
		if (!$shortcode || empty($shortcode_args[$shortcode]) || empty($_POST[$shortcode])) {
			die;
		}
		// set the defaults
		$template_id = 'pls_'.$shortcode.'___preview';

		$args = wp_parse_args($_POST[$shortcode], array('shortcode'=>$shortcode, 'title'=>'_preview'));
		$this->save_custom_template($template_id, $args);

		header( "Content-Type: application/json" );
		echo json_encode($response);
		die;
	}

	/**
	 * Generate a preview of the template.
	 * We always use the same id 'pls_<shortcode>__preview' for the template name.
	 */
	public function template_preview() {
		$shortcode = (!empty($_GET['shortcode']) ? stripslashes($_GET['shortcode']) : '');
		$shortcode_args = $this->get_shortcode_attrs();
		if (!$shortcode || empty($shortcode_args[$shortcode])) {
			die;
		}
		// set the defaults
		$template_id = 'pls_'.$shortcode.'___preview';
		$args = wp_parse_args($_GET, array('context'=>$template_id));
		$sc_str = $this->generate_shortcode_str($shortcode, $args);

		include(PL_SHORTCODES_DIR . 'admin/views/preview.php');
		die;
	}

	/**
	 * Checks if the given template is being used and returns the number of custom shortcodes using it
	 * @param string $id
	 * @return int
	 */
	public static function template_in_use($id) {
		global $wpdb;

		return $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(*)
			FROM $wpdb->posts, $wpdb->postmeta
			WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			AND $wpdb->postmeta.meta_key = 'pl_cpt_template'
			AND $wpdb->postmeta.meta_value = '%s'
			AND $wpdb->posts.post_type = 'pl_general_widget'", $id));
	}

	/**
	 * Checks if the given template is being used and returns an array of custom shortcode ID's and titles using it
	 * @param string $id
	 * @return array
	 */
	public static function template_used_by($id) {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare("
			SELECT $wpdb->posts.ID, $wpdb->posts.post_title
			FROM $wpdb->posts, $wpdb->postmeta
			WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			AND $wpdb->postmeta.meta_key = 'pl_cpt_template'
			AND $wpdb->postmeta.meta_value = '%s'
			AND $wpdb->posts.post_type = 'pl_general_widget'", $id), ARRAY_A);
	}

	/**
	 * Gets a list of templates in use for a given shortcode
	 * @param string $shortcode	: shortcode type
	 * @return array
	 */
	public static function templates_in_use($shortcode) {
		global $wpdb;

		return $wpdb->get_col("
			SELECT DISTINCT($wpdb->postmeta.meta_value) 
				FROM $wpdb->postmeta JOIN (
					SELECT $wpdb->posts.ID AS id
					FROM $wpdb->postmeta 
					JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
					AND $wpdb->posts.post_type = 'pl_general_widget' 
					AND $wpdb->postmeta.meta_key = 'shortcode' 
					AND $wpdb->postmeta.meta_value = '$shortcode') posts 
				ON $wpdb->postmeta.post_id = posts.id 
				WHERE $wpdb->postmeta.meta_key='pl_cpt_template'");
	}
	
	
	/***************************************************
	 * Shortcode Template storage functions
	 * TODO: maybe move to model
	 ***************************************************/


	/**
	 * Load a template
	 * @param string $id		: unique template id
	 * @param string $shortcode	: required to get the built in templates
	 * @return array
	 */
	public static function load_template($id, $shortcode) {
		$default = array();

		if ($id && $shortcode) {
			// get custom template list
			$option_key = ('pls_' . $shortcode.'_list');
			$tpl_list = get_option($option_key, array());
			if (!empty($tpl_list) && !empty($tpl_list[$id])) {
				return self::load_custom_template($id);
			}

			// get builtin/default templates
			$tpls = self::get_builtin_templates($shortcode);
			if (!in_array($id, $tpls)) {
				// use twenty ten if there's no template or it's not found
				$id = 'twentyten';
			}

			$template = array();
			$filename = PL_SHORTCODES_DIR . 'views/shortcodes/' . trailingslashit($shortcode) . $id . '.php';

			ob_start();			// support old style built in templates
			include $filename;
			$raw = ob_get_clean();
			if (empty($template)) {
				$template['snippet_body'] = $raw;
			}

			return array_merge($template, array('shortcode'=>$shortcode, 'title'=>$id));
		}

		return $default;
	}

	public static function load_custom_template($id) {
		$default = array('shortcode'=>'', 'title'=>'');
		if (strpos($id, 'pls_') !== 0) {
			return $default;
		}
		$data = get_option($id, $default);
		if (!is_array($data) || empty($data['shortcode']) || empty($data['title'])) {
			return $default;
		}
		return $data;
	}


	/**
	 * Save a shortcode template
	 * We save it in the options table using the name:
	 * pls_<shortcode_type>__<unique identifier>
	 * and also track it in a list stored in the option table using the shortcode:
	 * pls_<shortcode_type>_list
	 * @param string $id		: template id
	 * @param string $shortcode	: shortcode name
	 * @param string $title		: user name for the shortcode template
	 * @param array $data		:
	 * @return string			: unique id used to reference the template
	 */
	public static function save_custom_template($id, $atts) {
		$atts = (array)$atts;
		// sanity check
		$shortcode = empty($atts['shortcode'])?'':$atts['shortcode'];
		if (!$shortcode || empty(self::$shortcodes[$shortcode]) || empty($atts['title'])) {
			return '';
		}
		// if we change the shortcode of an existing record create a new one with new shortcode
		if (empty($id) || strpos($id, 'pls_'.$shortcode.'__')!==0) {
			$count = get_option('pl_shortcode_tpl_counter', 0) + 1;
			$id = 'pls_' . $shortcode . '__' . $count;
			update_option('pl_shortcode_tpl_counter', $count);
		}
		$sc_args = self::get_shortcode_attrs();
		$data = $sc_args[$shortcode]['template'] + array('shortcode'=>'', 'title'=>'');
		foreach($data as $key => &$val) {
			if (isset($atts[$key])) {
				$val = stripslashes($atts[$key]);
			}
		}
		update_option($id, $data);

		// Add to the list of custom snippet IDs for this shortcode...
		$tpl_list_DB_key = ('pls_' . $shortcode . '_list');
		$tpl_list = get_option($tpl_list_DB_key, array()); // If it doesn't exist, create a blank array to append...
		$tpl_list[$id] = $data['title'];

		// sort alphabetically
		uasort($tpl_list, array(__CLASS__, '_tpl_list_sort'));
		update_option($tpl_list_DB_key, $tpl_list);
		self::build_tpl_list($shortcode);
		return $id;
	}

	/**
	 * Delete a template
	 * @param string $id
	 * @return void
	 */
	public static function delete_custom_template($id) {
		// sanity check - ids should be in the form 'pls_<sc>__<some unique identifier>
		$parts = explode('_', $id);
		if (count($parts) < 4 || $parts[0]!=='pls') {
			return;
		}
		$valid = false;
		foreach(self::$shortcodes as $shortcode=>$inst) {
			if (strpos($id, $shortcode)===4) {
				$valid = true;
				break;
			}
		}
		if (!$valid) {
			return;
		}

		delete_option($id);

		// Remove from the list of custom template IDs for this shortcode...
		self::build_tpl_list($shortcode);
	}

	/**
	 * Return the list of available templates for the given shortcode.
	 * List includes default templates and user created ones
	 * @param string $shortcode
	 * @param bool $all			: true to include hidden templates like the preview one
	 * @return array
	 */
	public static function template_list($shortcode, $all = false) {
		// sanity check
		if (empty(self::$shortcodes[$shortcode])) {
			return array();
		}

		$tpl_type_map = array();

		// add default templates
		$default_tpls = self::get_builtin_templates($shortcode);
		foreach ($default_tpls as $name) {
			$tpl_type_map[$name] = array('type'=>'default', 'title'=>$name, 'id'=>$name);
		}

		// get custom templates
		$snippet_list_DB_key = ('pls_' . $shortcode . '_list');
		$tpl_list = get_option($snippet_list_DB_key, array());
		foreach ($tpl_list as $id => $name) {
			if ($id == 'pls_' . $shortcode . '___preview' && !$all) continue;
			$tpl_type_map[$id] = array('type'=>'custom', 'title'=>$name, 'id'=>$id);
		}
		return $tpl_type_map;
	}

	/**
	 * Return the list of built-in templates for the given shortcode.
	 * List includes default templates and user created ones
	 * @param string $shortcode
	 * @return array
	 */
	public static function get_builtin_templates($shortcode) {
		// sanity check
		if (empty(self::$shortcodes[$shortcode])) {
			return array();
		}

		return self::$shortcodes[$shortcode]->get_builtin_templates();
	}

	/**
	 * Comparator to sort template list in alphabetical order
	 */
	public static function _tpl_list_sort($a, $b) {
		return strcasecmp($a, $b);
	}

	/**
	 * Rebuild template list for the given shortcode
	 * @param string $shortcode	:
	 * @return array			: updated template list
	 */
	public static function build_tpl_list($shortcode) {
		global $wpdb;
		// sanity check
		if (empty(self::$shortcodes[$shortcode])) {
			return array();
		}
		$tpls = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", 'pls\_'.$shortcode.'\_\__%'));
		$tpl_list = array();
		foreach($tpls as $tpl){
			$tpl_data = get_option($tpl->option_name, array());
			if(empty($tpl_data['title'])) {
				$tpl_data['title'] = '';
			}
			$tpl_list[$tpl->option_name] = $tpl_data['title'];
		}

		uasort($tpl_list, array(__CLASS__, '_tpl_list_sort'));
		$tpl_list_DB_key = ('pls_' . $shortcode . '_list');
		update_option($tpl_list_DB_key, $tpl_list);
		return $tpl_list;
	}
	
	
	public static function get_listing_attributes($sort = false) {
		global $PL_API_CUST_ATTR;

		$attrs = array();
		$config = PL_Config::PL_API_LISTINGS('get', 'args');
		$form_types = PL_Config::PL_API_CUST_ATTR('get');
		$form_types = $form_types['args']['attr_type']['options'];

		foreach($config as $g_key => &$g_attrs) {

			$group = '';
			$search_form_group = '';
			switch($g_key) {
				case 'include_disabled':
					continue;
				// TODO: fields used for fetching data that aren't relevant to a single listing
				case 'location':
				case 'rets':
					$group = $g_key;
					$search_form_group = $g_key;
					break;
				case 'metadata':
					$group = 'cur_data';
					$search_form_group = $g_key;
					break;
				case 'custom':
					$group = 'uncur_data';
					$search_form_group = 'metadata';
					break;
			}
			if (!empty($g_attrs['type']) && $g_attrs['type']=='bundle') {
				if (!empty($g_attrs['bound']) && is_array($g_attrs['bound'])) {
					$params = ( isset($g_attrs['bound']['params']) ? $g_attrs['bound']['params'] : array() ) ;
					$params = array($params);
					$g_attrs = call_user_func_array(array($g_attrs['bound']['class'], $g_attrs['bound']['method']), $params);
					if (!$group) $group = $g_key;
					foreach($g_attrs as $f_attrs ) {
						$attr_type = (!isset($f_attrs['attr_type']) || empty($form_types[$f_attrs['attr_type']])) ? 'text' : $form_types[$f_attrs['attr_type']];
						$attr_cat = empty($f_attrs['cat']) ? $g_key : $f_attrs['cat'];
						if (!empty($f_attrs['name'])) {
							$f_attrs['label'] = $f_attrs['name'];
							unset($f_attrs['name']);
						}
						if ($f_attrs['key'] == 'days_on_market' && $group == 'uncur_data')	{
							// TODO: remove when we no longer have days_on_market in uncur_data
							foreach ($attrs as $key=>$attr) {
								if ($attr['attribute'] == 'dom') {
									unset($attrs[$key]);
									break;
								}
							}
						}
						$attrs[] = array_merge($f_attrs, array('attribute' => $f_attrs['key'], 'group' => $group, 'search_form_group' => $search_form_group, 'attr_type' => $attr_type, 'type' => $attr_type, 'cat'=>ucwords($attr_cat)));
					}
				}
				continue;
			}
			if ($group) {
				foreach($g_attrs as $f_key => $f_attrs ) {
					if (!empty($f_attrs['label']) && strpos($f_key, 'min_')!==0 && strpos($f_key, 'max_')!==0) {
						$attr_type = isset($f_attrs['attr_type']) ? $f_attrs['attr_type'] : $f_attrs['type'];
						$attr_cat = empty($f_attrs['cat']) ? $f_attrs['group'] : $f_attrs['cat'];
						$attrs[] = array_merge($f_attrs, array('attribute' => $f_key, 'group' => $group, 'search_form_group' => $search_form_group, 'attr_type' => $attr_type, 'cat'=>ucwords($attr_cat)));
					}
				}
			}
			else {
				if (!empty($g_attrs['label'])) {
					$attr_type = isset($g_attrs['attr_type']) ? $g_attrs['attr_type'] : $g_attrs['type'];
					$attr_cat = $g_attrs['group'];
					$attrs[] = array_merge($g_attrs, array('attribute' => $g_key, 'group' => $group, 'search_form_group' => $search_form_group, 'attr_type' => $attr_type, 'cat'=>ucwords($attr_cat)));
				}
			}
		}
		if ($sort) {
			uasort($attrs, array(__CLASS__, '_attr_sort'));
		}
		else {
			uasort($attrs, array(__CLASS__, '_cat_sort'));
		}
		return $attrs;
	}

	public static function get_listing_filters($sort = false, $with_choices = false) {
		$filters = array();
		$attrs = self::get_listing_attributes($sort);
		foreach ($attrs as $attr) {
			if ($attr['type'] == 'select' && !empty($attr['bound']['class'])) {
				// get options for this filter
				$attr['options'] = $with_choices ? call_user_func_array(array($attr['bound']['class'], $attr['bound']['method']), $attr['bound']['params']) : array();
				unset($attr['bound']);
			}
			elseif ($attr['type'] == 'textarea') {
				$attr['type'] = 'text';
			}
			$key = $attr['group'];
			switch ($attr['group']) {
				case 'cur_data':
					$key = $attr['group'] = 'metadata';
					break;
				// note: we store custom filters as 'custom', but in requests they are 'metadata'
				case 'uncur_data':
					$attr['group'] = 'custom';
					$key = 'metadata';
					break;
			}
			$key .= empty($key) ? '' : '.';
			if ($attr['attr_type'] == 'int' || $attr['attr_type'] == 'date') {
				$label = empty($attr['label_min']) ? 'Min ' . $attr['label'] : $attr['label_min'];
				$filters[$key.$attr['attribute'].'_min'] = array_merge($attr, array('attribute' => 'min_'.$attr['attribute'], 'label' => $label, 'multi'=>false));
				$label = empty($attr['label_max']) ? 'Max ' . $attr['label'] : $attr['label_max'];
				$filters[$key.$attr['attribute'].'_max'] = array_merge($attr, array('attribute' => 'max_'.$attr['attribute'], 'label' => $label, 'multi'=>false));
			}
			else
				$filters[$key.$attr['attribute']] = $attr;
		}
		
		return $filters;
	}
	
	private static function _attr_sort($a, $b) {
		return strcmp($a['label'], $b['label']);
	}
	
	private static function _cat_sort($a, $b) {
		$comp = strcmp($a['cat'], $b['cat']);
		if ($comp == 0) {
			$comp = strcmp($a['label'], $b['label']);
		}
		return $comp;
	}
}

new PL_Shortcode_CPT();
