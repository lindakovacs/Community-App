<?php
/**
 * Base class for creating a custom post type based on a shortcode.
 * Subclass this for each shortcode to provide an admin suitable for that shortcode.
 */

abstract class PL_SC_Base {

	// subclass should use this to set its post type
	protected $pl_post_type = '';
	// subclass should use this to set its shortcode
	protected $shortcode = '';
	// subclass should use this for form/widget titles, etc
	protected $title = '';
	// help text
	protected $help = '';
	// subclass should use this for basic display options/shortcode arguments
	protected $options = array(
		'context'			=> array( 'type' => 'select', 'label' => 'Template', 'default' => ''),		// these should always exist
		'width'				=> array( 'type' => 'int', 'label' => 'Width(px)', 'default' => 250 ),
		'height'			=> array( 'type' => 'int', 'label' => 'Height(px)', 'default' => 250 ),
		'widget_class'		=> array( 'type' => 'text', 'label' => 'Widget Class', 'default' => '' ),
	//	'<field_name>'		=> array(
	//			'type'		=> '[text|numeric|select|subgrp]'	// type of form control:
	//															// text:	text field
	//															// numeric:	integer field
	//															// select:	drop list
	//															// subgrp:	contains a subgroup of controls
	//			'label'		=> '<Pretty Form Name>',			// field label for use in a form
	//			'options'	=> array(							// present if control type is 'select'
	//				'<value>'	=> '<Pretty Form Name>',		// field label for use in a form
	//				...
	//			),
	//			'default'	=> '<default val>'					// default value - type should be appropriate to the control type
	//	),
	);
	// subclass should use this for a list of shortcode filter subcodes
	private $filters = array(
		//		'<field_name>'		=> array(
		//			'type'		=> '[text|select|subgrp]'		// type of form control
		//														// text:	text field
		//														// select:	drop list
		//														// subgrp:	contains a group of filters
		//			'label'		=> '<Pretty Form Name>',		// field label for use in a form
		//			'default'	=> '<default val>'				// default value - type should be appropriate to the control type
		//	),
	);
	// subclass should use this for a list of shortcode subcodes
	protected $subcodes = array(
		//		'<subcode_name>'	=> array(
		//			'help'		=> '<help text>'				// description of what the subcode does
		//	),
	);
	// tags allowed inside text boxes
	protected $allowable_tags = "<a><p><script><div><span><section><label><br><h1><h2><h3><h4><h5><h6><scr'+'ipt><style><article><ul><ol><li><strong><em><button><aside><blockquote><footer><header><form><nav><input><textarea><select>";
	// built in templates
	// TODO: build dynamically
	protected $default_tpls = array('twentyten', 'twentyeleven', 'responsive');
	// default layout for template
	protected $template = array(								// defines template fields
		//		'snippet_body'	=> array(
		//		'type'		=> 'textarea',
		//		'label'		=> '<Pretty Form Name>',
		//		'css'		=> '<css_class mime_type>', 		// used for CodeMirror
		//		'default'	=> '',
		//	),
	);




	static function init() {}

	/**
	 * Create an instance and register it with the custom shortcode manager
	 */
	protected static function _init($class) {
		if (class_exists('PL_Shortcode_CPT')) {
			$inst = new $class();
 			PL_Shortcode_CPT::register_shortcode($inst->shortcode, $inst);
 			return $inst;
 		}
 		return null;
	}

	public function __construct() {
 		add_action( 'template_redirect', array( $this, 'post_type_templating' ) );

		// Dynamically load default markup for new shortcode templates
		// Note: This (necessarily) runs after the member variable initializations of the subclass
		$default_template = PL_Shortcode_CPT::load_template('default', $this->shortcode);
		foreach($this->template as $key => &$value) {
			$value['default'] = $default_template[$key];
		}
	}

	/**
	 * Return the parameters that describe this shortcode type
	 * @return multitype:
	 */
	public function get_args($with_choices = false) {
		if (empty($this->default_tpls)) {
			$this->default_tpls = $this->get_builtin_templates();
		}
		return array(
				'shortcode'		=> $this->shortcode,
				'pl_post_type'	=> $this->pl_post_type,
				'title'			=> $this->title,
				'help'			=> $this->help,
				'options'		=> $with_choices ? $this->get_options_list($with_choices) : $this->options,
				'filters'		=> $with_choices ? $this->get_filters_list($with_choices) : $this->filters,
				'subcodes'		=> $this->subcodes,
				'default_tpls'	=> $this->default_tpls,
				'template'		=> $this->template,
		);
	}


	/*******************************************
	 * Override the following as necessary
	 *******************************************/


	/**
	 * Called when the post is being formatted for display by an embedded js tag for example
	 * Make the shortcode and render it. The template will already have been rendered by the embedded js.
	 * @param object $single	: post object
	 * @param bool $skipdb
	 */
	public function post_type_templating( $single, $skipdb = false ) {
		global $post;

		if( !empty($post) && $post->post_type == 'pl_general_widget') {
			$sc_str = $post->post_content;
			$sc_options = PL_Shortcode_CPT::load_shortcode($post->ID);
			include(PL_VIEWS_DIR . 'shortcode-embedded.php');
			die;
		}
	}

	/**
	 * Return array of templates for this shortcode supplied with the plugin.
	 */
	public function get_builtin_templates() {
		if (empty($this->default_tpls)) {
			if (file_exists($dir = PL_VIEWS_SHORT_DIR . $this->shortcode)) {
				foreach (new DirectoryIterator($dir) as $fileInfo) {
					if($fileInfo->isDot()) continue;
					$matches = array();
					if (preg_match('/^(.+)\.php/', $fileInfo->getFilename(), $matches)) {
						$this->default_tpl[] = $matches[1];
					}
				}
			}
		}
		return $this->default_tpls;
	}

	/**
	 * Return array of options used to configure this shortcode type with any available choice values.
	 */
	protected function get_options_list($with_choices = false) {
		return $this->options;
	}

	/**
	 * Return array of filters used to configure this shortcode type with any available choice values.
	 */
	protected function get_filters_list($with_choices = false) {
		return $this->filters;
	}

	/**
	 * Return array of filters used to configure this custom shortcode
	 * @param $id int	: id of custom shortcode record
	 * @return array
	 */
	public function get_filters($id) {
		if ($post = get_post($id, ARRAY_A, array('post_type'=>'pl_general_widget'))) {
			$postmeta = get_post_meta($id);
			if (!empty($postmeta['pl_filters'])) {
				$filters = maybe_unserialize($postmeta['pl_filters'][0]);
				return $filters;
			}
		}
		return array();
	}

	/**
	 * Return array of options used to configure this custom shortcode
	 * @param $id int		: id of custom shortcode record
	 * @return array/bool	: array of results/false if id invalid/trashed
	 */
	public function get_options($id) {
		$options = array();
		if ($id && ($post = get_post($id, ARRAY_A, array('post_type'=>'pl_general_widget'))) && $post['post_status']=='publish') {
			$postmeta = get_post_meta($id);
			foreach($this->options as $attr=>$vals) {
				if ($attr == 'context') {
					$key = 'pl_cpt_template';
				}
				else {
					$key = $attr;
				}
				if (isset($postmeta[$key])) {
					$options[$attr] = maybe_unserialize($postmeta[$key][0]);
				}
			}
			return $options;
		}
		return false;
	}

	/**
	 * Generate a shortcode for this shortcode type from arguments
	 * Used by shortcode edit page, template edit page for the preview pane
	 * @param array $args				: set of key value pairs
	 * @return string					: returned shortcode
	 */
	public function generate_shortcode_str($args) {
		// prepare args
		$sc_args = '';
		$class_options = $this->options;
		foreach($args as $option => $value) {
			if (!empty($value)) {
				// only output options that are valid for this type
				if (!empty($class_options[$option])
					&& $class_options[$option]['type'] != 'featured_listing_meta'
					) {
					if (is_array($value)) {
						$sc_args .= ' '.$option."='".implode(',',$value)."'";
					}
					else {
						$sc_args .= ' '.$option."='".$value."'";
					}
				}
			}
		}

		$shortcode = '[' . $this->shortcode . $sc_args;

		// prepare filters
		$subcodes = '';
		$class_filters = $this->get_filters_list();

		foreach($class_filters as $f_atts) {
			if (empty($f_atts['group'])) {
				if (!empty($args[$f_atts['attribute']])) {
					$subcodes .= " [pl_filter filter='" . $f_atts['attribute'] . "' value='". htmlentities(implode('||', (array)$args[$f_atts['attribute']])) . "'] ";
				}
			}
			else {
				$gname = $f_atts['group'];
				if (!empty($args[$f_atts['group']][$f_atts['attribute']])) {
					if ($gname == 'custom') {
						$gname = 'metadata';
					}
					$subcodes .= " [pl_filter group='" . $gname. "' filter='" . $f_atts['attribute'] . "' value='" . htmlentities(implode('||', (array)$args[$f_atts['group']][$f_atts['attribute']])) . "'] ";
				}
			}
		}

		// build the shortcode
		if ($subcodes) {
			$shortcode = $shortcode . ']'.$subcodes."[/".$this->shortcode."]";
		}
		else {
			$shortcode .= ']';
		}

		return $shortcode;
	}

	protected static function _do_templatetags($class, $tags, $content) {
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

		return preg_replace_callback( "/$pattern/s", array($class, 'templatetag_callback'), $content );
	}

	protected static function form_item($item, $attr, $value = '', $parent = false, $echo = false) {
		$name = $item;
		$name = preg_replace('/[^a-zA-Z\-_\[\]]/', '_', $name);
		if (empty($parent)) {
			if (!empty($_REQUEST[$name])) $value = $_REQUEST[$name];
		}
		else {
			$parent = preg_replace('/[^a-zA-Z\-_\[\]]/', '_', $parent);
			if (!empty($_REQUEST[$parent][$name])) $value = $_REQUEST[$parent][$name];
			$name = $parent.'['.$name.']';
		}
		$id = str_replace(array('[',']'), array('-',''), $name);
		$attr = $attr + array('type' => 'text', 'options' => '', 'css_class' => '');
		$class = empty($attr['css']) ? '' : 'class="'. $attr['css'] .'"';

		ob_start();
		switch ($attr['type']) {
			case 'checkbox':
				?>
				<input id="<?php echo $id ?>" <?php echo $class ?> type="<?php echo $attr['type'] ?>"
						name="<?php echo $name ?>" value="<?php echo (empty($attr['value']) ? 'true' : $attr['value']) ?>" />
				<?php
				break;
			case 'radio':
				?>
				<input id="<?php echo $id.'-'.preg_replace('/[^a-zA-Z\-_\[\]]/', '_', $value) ?>" <?php echo $class ?> type="radio"
					name="<?php echo $name ?>" value="<?php echo $value ?>" />
				<?php
				break;
			case 'textarea':
				$rows = ! empty ( $attributes ['rows'] ) ? $attributes ['rows'] : 2;
				$cols = ! empty ( $attributes ['cols'] ) ? $attributes ['cols'] : 20;
				?>
				<textarea id="<?php echo $id ?>" <?php echo $class ?> name="<?php echo $name ?>"
					rows="<?php echo $rows ?>" cols="<?php echo $cols ?>"><?php echo $value ?></textarea>
				<?php
				break;
			case 'select':
				$options = self::_option_explode($attr['options']);
				?>
				<select id="<?php echo $id ?>" <?php echo $class ?> name="<?php echo $name ?>">
				<?php foreach ($options as $key => $text): ?>
					<option value="<?php echo htmlentities($key) ?>"
					<?php echo ($key == $value ? 'selected="selected"' : '' ) ?>><?php echo htmlentities($text) ?></option>
				<?php endforeach ?>
				</select>
				<?php
				break;
			case 'multiselect':
				$options = self::_option_explode($attr['options']);
				if (!is_array($value)) {
					$value = self::_option_explode($value);
				}
				?>
				<select id="<?php echo $id ?>" <?php echo $class ?> multiple="multiple" name="<?php echo $name ?>[]">
				<?php foreach ($options as $key => $text): ?>
					<option value="<?php echo htmlentities($key) ?>"
					<?php echo ((is_array($value) && in_array($key, $value) ) ? 'selected="selected"' : '' ) ?>><?php echo htmlentities($text) ?></option>
				<?php endforeach ?>
				</select>
				<?php
				break;
			case 'hidden':
				?>
				<input id="<?php echo $id ?>" type="hidden" name="<?php echo $name ?>"
						value="<?php echo htmlentities($value) ?>" />
				<?php
				break;
			case 'date':
			default:
				?>
				<input id="<?php echo $id ?>" <?php echo $class ?> type="text"
					name="<?php echo $name ?>" <?php echo !empty($value) ? 'value="'.$value.'"' : '' ?>
					data-attr_type="<?php echo $attr['type'] ?>" />
				<?php
				break;
		}
		return ob_get_clean();
	}

	public static function wrap( $shortcode, $content = '' ) {
		ob_start();
		do_action( $shortcode . '_pre_header' );
		echo $content;
		do_action( $shortcode . '_post_footer' );
		return ob_get_clean();
	}
	
	private static function _option_explode($option) {
		// TODO: regex?
		$options = array_map('trim', explode(',', $option));
		$vals = array();
		foreach ($options as $option) {
			$ex = array_map('trim', explode('|', $option, 2));
			if (empty($ex[1])) $ex[1] = $ex[0];
			$vals[$ex[0]] = $ex[1];
		}
		return $vals;
	}
}
