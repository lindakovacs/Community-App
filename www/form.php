<?php


require_once(BUILDER_DIR . 'api/search_context.php');
require_once('input.php');


class PL_Search_Form extends PL_POST_Element {
	protected $context; // a connection with external filters limiting menu and auto-suggest choices

	protected $form;
	protected $elements;

	protected $request;
	protected $request_data;

	public function __construct(PL_Search_Context $context, $template = null, $post_data = null) {
		$this->context = $context;

		self::$incoming_data = $post_data; // element constructors access this
		self::$outgoing_data = array();

		$this->html = $this->form = new HTML_Form('pl_search_form', get_permalink());
		$this->form->method = 'POST';
		$this->elements = array();

		$group = new HTML_FieldSet('Location');
		$group->add_content($this->elements['locality'] = $this->get_search_filter_entry('locality', array('datalist' => true)));
		$group->add_content($this->elements['region'] = $this->get_search_filter_menu('region'));
		$group->add_content($this->elements['postal'] = $this->get_search_filter_menu('postal'));
		$this->form->add_content($group);

		$group = new HTML_FieldSet('Listing Type');
		$group->add_content($this->elements['purchase_type'] = $this->get_search_filter_menu('purchase_type'));
		$group->add_content($this->elements['property_type'] = $this->get_search_filter_menu('property_type', array('type' => 'checkbox')));
		$group->add_content($this->elements['zoning_type'] = $this->get_search_filter_menu('zoning_type', array('type' => 'radio')));
		$this->form->add_content($group);

		$group = new HTML_FieldSet('Price Range');
		$group->add_content($this->elements['min_price'] = $this->get_search_filter_menu('min_price', array('options' => array('1.0', '2.0', '3.0', '4.0'))));
		$group->add_content($this->elements['max_price'] = $this->get_search_filter_menu('max_price'));
		$this->form->add_content($group);

		$group = new HTML_FieldSet('Details');
		$group->add_content($this->elements['min_beds'] = $this->get_search_filter_menu('min_beds'));
		$group->add_content($this->elements['min_baths'] = $this->get_search_filter_menu('min_baths'));
		$this->form->add_content($group);

		$this->form->add_content(new HTML_Submit_Button());

		$this->request_data = self::$outgoing_data;
		self::$incoming_data = self::$outgoing_data = null;
	}

	public function get_search_data() {
		return $this->request_data;
	}

	public function get_search_request() {
		if(!$this->request)
			$this->request = new PL_Search_Request($this->context->get_connection(), $this->get_search_data());
		return $this->request;
	}

	public function get_search_filter_checkbox($filter_option, $checkbox_config = null) {
		return null;
	}

	public function get_search_filter_entry($filter_option, $entry_config = null) {
		$label = true; $value = null; $datalist = false;
		if(is_array($entry_config))
			extract($entry_config, EXTR_IF_EXISTS);

		$filter_options = $this->context->get_connection()->get_filter_attributes();
		if(!($attribute = $filter_options[$filter_option]))
			return null;

		if($label === true) {
			$label = $attribute->display_name;
			if(strpos($filter_option, 'min_') === 0)
				$label = 'Min ' . $label;
			else if(strpos($filter_option, 'max_') === 0)
				$label = 'Max ' . $label;
		}

		if(!is_array($datalist)) {
			if($datalist)
				$datalist = $this->context->get_filter_option_values($attribute->name);
			else
				$datalist = array();
		}

		$element = new PL_Text_Input($filter_option, $label, $value);
		foreach($datalist as $option)
			$element->add_option($option);

		return $element;
	}

	public function get_search_filter_menu($filter_option, $menu_config = null) {
		$type = 'select'; $label = true; $options = true; $selected = null; $display_values = array();
		if(is_array($menu_config))
			extract($menu_config, EXTR_IF_EXISTS);

		$filter_options = $this->context->get_connection()->get_filter_attributes();
		if(!($attribute = $filter_options[$filter_option]))
			return null;

		if($label === true) {
			$label = $attribute->display_name;
			if(strpos($filter_option, 'min_') === 0)
				$label = 'Min ' . $label;
			else if(strpos($filter_option, 'max_') === 0)
				$label = 'Max ' . $label;
		}

		if(!is_array($options) && $options)
			$options = $this->context->get_filter_option_values($attribute->name);

		if($options)
			$display_values = array_replace(array_combine($options, $options), $display_values);
		else
			$display_values = $options = array();

		switch($type) {
			case 'select':
				$menu = new PL_Select($filter_option, $label, false);
				$menu->add_option('', 'Any', true);
				break;
			case 'multiple':
				$menu = new PL_Select($filter_option, $label, true);
				break;
			case 'radio':
				$menu = new PL_Radio_Select($filter_option, $label);
				$menu->add_option('', 'Any', true);
				break;
			case 'checkbox':
				$menu = new PL_Checkbox_Select($filter_option, $label);
				break;
			default:
				return null;
		}

		foreach($options as $option)
			$menu->add_option($option, $display_values[$option], false);

		return $menu;
	}
}
