<?php


require_once('html.php');


class PL_POST_Element implements HTML {
	protected static $incoming_data; // incoming post data overrides element values supplied in constructors
	protected static $outgoing_data; // filtered incoming post data, or defaults, for defined search elements
	protected $html;

	public function __toString() { return $this->html ? $this->html->html_string() : ''; }
	public function html_string() { return $this->html ? $this->html->html_string() : ''; }
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

		self::$outgoing_data[$name] = $value;

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

		$this->datalist->add_content(new HTML_Option($text, $text));
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
				return;
		else if(!is_scalar($value))
			return;

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

		$this->select->add_content(new HTML_Option($value, $text, $selected));
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
			return;

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
			return;

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
	}
}
