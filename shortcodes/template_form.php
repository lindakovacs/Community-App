<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'www/form.php');
require_once('shortcodes.php');


class PL_Template_Form extends PL_Template_Handler {
	protected $search_context;
	protected $current_form;
	protected $current_element;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'form', 'form_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'input', 'input_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'select', 'select_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'option', 'option_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'checkbox', 'checkbox_shortcode');
	}

	static public function register_search_shortcodes(PL_Shortcode_Dispatcher $dispatcher, PL_Attribute_Map $map) {
		foreach($map->get_attributes() as $attribute)
			$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . $attribute->name, 'search_shortcodes');
	}

	protected function is_template_shortcode($shortcode) {
		$form_shortcodes = array();

		if(!$this->current_form)
			$form_shortcodes[] = PL_SC_PREFIX . 'form';

		if($this->current_element)
			$form_shortcodes[] = PL_SC_PREFIX . 'option';

		else {
			$form_shortcodes[] = PL_SC_PREFIX . 'input';
			$form_shortcodes[] = PL_SC_PREFIX . 'select';
			$form_shortcodes[] = PL_SC_PREFIX . 'checkbox';
		}

		return in_array($shortcode, $form_shortcodes);
	}

	public function __construct(PL_Search_Context $search_context, PL_Search_Form $form_context = null) {
		$this->search_context = $search_context;
		$this->form_context = $form_context;
	}

	public function form_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('action' => null, 'target' => null), $args));
		if($this->current_form || $this->current_element) return null;

		// if we have a form context, we're building a form object from a template
		if($this->form_context) {
			$this->current_form = $this->form_context;
			if(method_exists($this->current_form, 'initialize'))
				$this->current_form->initialize($action, $target);
		}
		else {
			$this->current_form = new PL_Search_Form($this->search_context, $action, $target);
		}

		if($content)
			$this->do_template_shortcodes($this->current_form, $content);

		$this->current_element = null;
		$this->current_form = null;
	}

	public function input_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('name' => null, 'label' => null, 'value' => null), $args));
		if($this->current_element || is_null($name)) return null;

		$element = new PL_Text_Input($name, $label, $value);

		if($content) {
			$this->current_element = $element;
			$this->do_template_shortcodes($element, $content);
			$this->current_element = null;
		}

		if($this->current_form)
			$this->current_form->add_element($name, $element);

		return $element->html_string();
	}

	public function select_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('type' => null, 'name' => null, 'label' => null, 'value' => null), $args));
		if($this->current_element || is_null($name)) return null;

		switch($type) {
			case 'select':
				$element = new PL_Select($name, $label);
				break;

			case 'multiple':
				$element = new PL_Select($name, $label, true);
				break;

			case 'radio':
				$element = new PL_Radio_Select($name, $label);
				break;

			case 'checkbox':
				$element = new PL_Checkbox_Select($name, $label);
				break;

			default:
				if(is_null($type))
					$element = new PL_Select($name, $label);
				else
					return null;
				break;
		}

		if($content) {
			$this->current_element = $element;
			$this->do_template_shortcodes($element, $content);
			$this->current_element = null;
		}

		if($this->current_form)
			$this->current_form->add_element($name, $element);

		return $element->html_string();
	}

	public function option_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('value' => null, 'display' => null, 'selected' => null), $args));
		if(!$this->current_element || (is_null($value) && is_null($display))) return null;

		if(method_exists($this->current_element, 'add_option')) {
			if(is_a($this->current_element, 'PL_Text_Input'))
				$option = $this->current_element->add_option($value ?: $display);
			else
				$option = $this->current_element->add_option($value, $display ?: $value, $selected);
		}

		else {
			$option = new HTML_Option($value, $display, $selected);
			$this->current_element->add_content($option->html_string());
		}

		return $option->html_string();
	}

	public function checkbox_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('name' => null, 'value' => null, 'label' => null, 'checked' => null), $args));
		if($this->current_element || is_null($name)) return null;

		$element = new PL_Checkbox_Input($name, $value, $label, $checked);

		if($this->current_form)
			$this->current_form->add_element($name, $element);

		return $element->html_string();
	}
}
