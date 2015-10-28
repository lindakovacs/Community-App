<?php


require_once(BUILDER . 'api/connection.php');
require_once('input.php');


class PL_Page_Context extends PL_Search_Request {
	public function __construct(PL_API_Connection $connection, PL_Search_Filter $filter = null) {
		parent::__construct($connection);
		$this->set_filter($filter);
	}

	public function get_connection() { return $this->connection; }
}

class PL_Search_Form extends PL_Search_Request {
	protected $context; // may have an external filter limiting menu and auto-suggest choices
	protected $elements;
	protected $form;

	public function __construct(PL_Page_Context $context, $template = null, $postdata = null) {
		parent::__construct($context->connection);
		$this->elements = array();
		$this->form = new HTML_Form('pl_search_form', 'http://awesome.placeter.net');

		$group = new HTML_FieldSet('Location');
		$group->add_content($this->elements['locality'] = $this->get_search_filter_menu('locality'));
		$group->add_content($this->elements['region'] = $this->get_search_filter_menu('region'));
		$group->add_content($this->elements['postal'] = $this->get_search_filter_menu('postal'));
		$this->form->add_content($group);

		$group = new HTML_FieldSet('Listing Type');
		$group->add_content($this->elements['purchase_type'] = $this->get_search_filter_menu('purchase_type'));
		$group->add_content($this->elements['property_type'] = $this->get_search_filter_menu('property_type'));
		$group->add_content($this->elements['zoning_type'] = $this->get_search_filter_menu('zoning_type'));
		$this->form->add_content($group);

		$group = new HTML_FieldSet('Price Range');
		$group->add_content($this->elements['min_price'] = $this->get_search_filter_menu('min_price'));
		$group->add_content($this->elements['max_price'] = $this->get_search_filter_menu('max_price'));
		$this->form->add_content($group);

		$group = new HTML_FieldSet('Details');
		$group->add_content($this->elements['min_beds'] = $this->get_search_filter_menu('min_beds'));
		$group->add_content($this->elements['min_baths'] = $this->get_search_filter_menu('min_baths'));
		$this->form->add_content($group);

		if($postdata) {

		}
	}

	public function get_search_filter_checkbox($filter_option, $checkbox_config = null) {
		return null;
	}

	public function get_search_filter_entry($filter_option, $entry_config = null) {
		$label = true; $value = null; $datalist = false;
		if(is_array($entry_config))
			extract($entry_config, EXTR_IF_EXISTS);

		$filter_options = $this->connection->get_filter_attributes();
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
				$datalist = $this->connection->read_attribute_values($attribute->name, $this->context);
			else
				$datalist = array();
		}

		$element = new PL_Text_Input($filter_option, $label, $value);
		foreach($datalist as $option)
			$element->add_option($option);

		return $element;
	}

	private static function is_selected($option, $selected) {
		return false;
	}

	public function get_search_filter_menu($filter_option, $menu_config = null) {
		$type = 'select'; $label = true; $options = true; $selected = null; $display_values = array();
		if(is_array($menu_config))
			extract($menu_config, EXTR_IF_EXISTS);

		$filter_options = $this->connection->get_filter_attributes();
		if(!($attribute = $filter_options[$filter_option]))
			return null;

		if($label === true) {
			$label = $attribute->display_name;
			if(strpos($filter_option, 'min_') === 0)
				$label = 'Min ' . $label;
			else if(strpos($filter_option, 'max_') === 0)
				$label = 'Max ' . $label;
		}

		if(!is_array($options)) {
			if($options)
				$options = $this->connection->read_attribute_values($attribute->name, $this->filter);
			else
				$options = array();
		}

		$default_values = array_combine($options, $options);
		$display_values = array_replace($default_values, $display_values);

		switch($type) {
			case 'select':
				$menu = new PL_Select($filter_option, $label, false);
				$menu->add_option('', 'Any', self::is_selected('', $selected));
				break;
			case 'multiple':
				$menu = new PL_Select($filter_option, $label, true);
				break;
			case 'radio':
				$menu = new PL_Radio_Select($filter_option, $label);
				$menu->add_option('', 'Any', self::is_selected('', $selected));
				break;
			case 'checkbox':
				$menu = new PL_Checkbox_Select($filter_option, $label);
				break;
			default:
				return null;
		}

		foreach($options as $option)
			$menu->add_option($option, $display_values[$option], self::is_selected($option, $selected));

		return $menu;
	}

	public function html_string() { return $this->form->html_string(); }
}
