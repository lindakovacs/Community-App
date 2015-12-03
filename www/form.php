<?php


require_once(BUILDER_DIR . 'api/search_context.php');
require_once(BUILDER_DIR . 'shortcodes/templates.php');
require_once('html.php');


class PL_POST_Element extends PL_Template_Element {
	protected static $incoming_data; // incoming post data overrides element values supplied in constructors
	protected static $outgoing_data; // filtered incoming post data, or defaults, for defined search elements
}


class PL_Text_Input extends PL_POST_Element {
	protected $name;
	protected $label;
	protected $input;

	// HTML5 auto-completion
	protected $datalist;

	public function __construct($name, $label, $value = null) {
		if(self::$incoming_data) $value = self::$incoming_data[$name];

		if(!$value && !is_numeric($value) || !is_scalar($value))
			$value = null;

		if(!is_null($value)) self::$outgoing_data[$name] = $value;

		$this->name = $name;
		$this->label = new HTML_Label($name, $label);
		$this->input = new HTML_Text_Input($name, $value);

		$this->html = new HTML_Div();
		$this->html->add_content($this->label);
		$this->html->add_content($this->input);
	}

	public function add_option($text) {
		if(!$this->datalist) {
			$this->input->list = $this->name . '-datalist';
			$this->datalist = new HTML_Datalist($this->input->list);
			$this->html->add_content($this->datalist);
		}

		$this->datalist->add_content($option = new HTML_Option($text, $text));
		return $option;
	}
}

class PL_Checkbox_Input extends PL_POST_Element {
	protected $label;
	protected $checkbox;

	public function __construct($name, $value, $label, $checked = false) {
		if(!$value && !is_numeric($value))
			$value = false;
		else if(!is_scalar($value))
			$value = true;

		if(self::$incoming_data) {
			if($value === true && self::$incoming_data[$name] === '_ne_null')
				$checked = true;
			else if($value === false && self::$incoming_data[$name] === '_eq_null')
				$checked = true;
			else if(is_scalar(self::$incoming_data[$name]) && $value === self::$incoming_data[$name])
				$checked = true;
			else if(is_array(self::$incoming_data[$name]) && in_array($value, self::$incoming_data[$name], true))
				$checked = true;
			else
				$checked = false;
		}

		if($checked) {
			if(is_bool($value)) {
				self::$outgoing_data[$name . '_match'] = 'exist';
				self::$outgoing_data[$name] = ($value ? 1 : 0);
			}
			else
				self::$outgoing_data[$name] = $value;
		}

		if($value === true)
			$value = '_ne_null';
		else if($value === false)
			$value = '_eq_null';

		$this->label = new HTML_Label($name, $label);
		$this->checkbox = new HTML_Checkbox_Input($name, $value, $checked);

		$this->html = new HTML_Div();
		$this->html->add_content($this->checkbox);
		$this->html->add_content($this->label);
	}
}

class PL_Select extends PL_POST_Element {
	protected $name;
	protected $label;
	protected $select;

	protected $multiple;
	protected $selection;

	public function __construct($name, $label, $multiple = false) {
		$this->name = $name;
		if($this->multiple = $multiple)
			$name = $name . '[]';

		$this->label = new HTML_Label($name, $label);
		$this->select = new HTML_Select($name, $multiple);

		$this->html = new HTML_Div();
		$this->html->add_content($this->label);
		$this->html->add_content($this->select);
	}

	public function add_option($value, $text, $selected = false) {
		if(!$value && !is_numeric($value))
			if(!$this->multiple)
				$value = '';
			else
				return null;
		else if(!is_scalar($value))
			return null;

		if($this->multiple || !$this->selection) {
			if(self::$incoming_data) {
				if(is_null(self::$incoming_data[$this->name]) && $value === '')
					$selected = true;
				else if(is_scalar(self::$incoming_data[$this->name]) && $value === self::$incoming_data[$this->name])
					$selected = true;
				else if(is_array(self::$incoming_data[$this->name]) && in_array($value, self::$incoming_data[$this->name], true))
					$selected = true;
				else
					$selected = false;
			}
		}
		else
			$selected = false;

		if($selected) {
			$this->selection = true;
			if($value !== '')
				if(!$this->multiple)
					self::$outgoing_data[$this->name] = $value;
				else
					self::$outgoing_data[$this->name][] = $value;
		}

		$this->select->add_content($option = new HTML_Option($value, $text, $selected));
		return $option;
	}
}

class PL_Radio_Select extends PL_POST_Element {
	protected $name;
	protected $selection;

	public function __construct($name, $label) {
		$this->html = new HTML_FieldSet($label);
		$this->name = $name;
	}

	public function add_option($value, $text, $selected = false) {
		if(!$value && !is_numeric($value))
			$value = '';
		else if(!is_scalar($value))
			return null;

		if(!$this->selection) {
			if(self::$incoming_data) {
				if(is_null(self::$incoming_data[$this->name]) && $value === '')
					$selected = true;
				else if(is_scalar(self::$incoming_data[$this->name]) && $value === self::$incoming_data[$this->name])
					$selected = true;
				else if(is_array(self::$incoming_data[$this->name]) && in_array($value, self::$incoming_data[$this->name], true))
					$selected = true;
				else
					$selected = false;
			}
		}
		else
			$selected = false;

		if($selected) {
			$this->selection = true;
			if($value !== '')
				self::$outgoing_data[$this->name] = $value;
		}

		$this->html->add_content($radio = new HTML_Div());
		$radio->add_content(new HTML_Radio_Input($this->name, $value, $selected));
		$radio->add_content(new HTML_Label($this->name, $text));
		return $radio;
	}
}

class PL_Checkbox_Select extends PL_POST_Element {
	protected $name;

	public function __construct($name, $label) {
		$this->html = new HTML_FieldSet($label);
		$this->name = $name;
	}

	public function add_option($value, $text, $selected = false) {
		if(!$value && !is_numeric($value) || !is_scalar($value))
			return null;

		if(self::$incoming_data) {
			if(is_scalar(self::$incoming_data[$this->name]) && $value === self::$incoming_data[$this->name])
				$selected = true;
			else if(is_array(self::$incoming_data[$this->name]) && in_array($value, self::$incoming_data[$this->name], true))
				$selected = true;
			else
				$selected = false;
		}

		if($selected) {
			$this->selection = true;
			self::$outgoing_data[$this->name][] = $value;
		}

		$this->html->add_content($checkbox = new HTML_Div());
		$checkbox->add_content(new HTML_Checkbox_Input($this->name . '[]', $value, $selected));
		$checkbox->add_content(new HTML_Label($this->name, $text));
		return $checkbox;
	}
}


class PL_Search_Form extends PL_POST_Element {
	protected $context; // a connection with external filters limiting menu and auto-suggest choices

	protected $form;
	protected $elements;

	public function __construct(PL_Search_Context $context, $action = null, $target = null) {
		$this->context = $context;

		$this->html = $this->form = new HTML_Form('pl_search_form', $action);
		$this->form->method = 'POST';
		$this->form->target = $target;
		$this->elements = array();
	}

	public function add_element($name, PL_POST_Element $element) {
		$this->form->add_content($this->elements[$name] = $element);
	}

}

class PL_Static_Search_Form extends PL_Search_Form {
	public function __construct(PL_Search_Context $context, $template = null) {
		parent::__construct($context);

		if($template) {
			$shortcode_context = new PL_Shortcode_Context(new PL_Template_Form($context, $this));
			do_shortcode($template);
			$shortcode_context = null;
		}
	}

	public function initialize($action = null, $target = null) {
		$this->html = $this->form = new HTML_Form('pl_search_form', $action);
		$this->form->method = 'POST';
		$this->form->target = $target;
		$this->elements = array();
	}
}

class PL_Active_Search_Form extends PL_Search_Form {
	protected $request;
	protected $request_data;

	public function __construct(PL_Search_Context $context, $template = null, $post_data = null) {
		self::$incoming_data = $post_data; // element constructors access this
		self::$outgoing_data = array();

		parent::__construct($context, get_permalink());

		if($template) {
			$shortcode_context = new PL_Shortcode_Context(new PL_Template_Form($context, $this));
			do_shortcode($template);
			$shortcode_context = null;
		}

		else {
			$this->add_content('<p>Location</p>');
			$this->add_element('locality', $this->get_search_filter_entry('locality', array('datalist' => true)));
			$this->add_element('region', $this->get_search_filter_menu('region'));
			$this->add_element('postal', $this->get_search_filter_menu('postal'));

			$this->add_content('<p>Listing Type</p>');
			$this->add_element('purchase_type', $this->get_search_filter_menu('purchase_type'));
			$this->add_element('property_type', $this->get_search_filter_menu('property_type', array('type' => 'checkbox')));
			$this->add_element('zoning_type', $this->get_search_filter_menu('zoning_type', array('type' => 'radio')));

			$this->add_content('<p>Price Range</p>');
			$this->add_element('min_price', $this->get_search_filter_menu('min_price', array('options' => array('1.0', '2.0', '3.0', '4.0'))));
			$this->add_element('max_price', $this->get_search_filter_menu('max_price'));

			$this->add_content('<p>Details</p>');
			$this->add_element('min_beds', $this->get_search_filter_menu('min_beds'));
			$this->add_element('min_baths', $this->get_search_filter_menu('min_baths'));

			$this->form->add_content(new HTML_Submit_Button());
		}

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
}
