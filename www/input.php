<?php


require_once('html.php');


interface PL_Search_Element {
	public function set_value($value);
	public function get_value();
}


class PL_Text_Input extends HTML_Text_Input implements PL_Search_Element {
	protected $container;
	protected $label;

	// HTML5 auto-completion
	protected $datalist;

	public function __construct($name, $label, $value = "") {
		parent::__construct($name, $value);
		$this->label = new HTML_Label($name, $label);

		$this->container = new HTML_Div();
		$this->container->add_content($this->label);
		$this->container->add_content($this);
	}

	public function add_option($text) {
		if(!$this->datalist) {
			$list = $this->attributes['name'] . '-datalist';
			$this->attributes['list'] = $list;
			$this->datalist = new HTML_Datalist($list);
			$this->container->add_content($this->datalist);
		}

		$this->datalist->add_content(new HTML_Option($text, $text));
	}

	public function get_value() { return $this->value; }
	public function set_value($value) {
		if(is_array($value)) $value = current($value);
		$this->value = $value;
	}

	public function html_string() {
		static $recursion = false;
		if($recursion)
			return parent::html_string();

		$recursion = true;
		$html = $this->container->html_string();
		$recursion = false;

		return $html;
	}
}

class PL_Radio_Input extends HTML_Radio_Input implements PL_Search_Element {
	protected $container;
	protected $label;

	public function __construct($name, $value, $label, $checked = false) {
		parent::__construct($name, $value, $checked);
		$this->label = new HTML_Label($name, $label);

		$this->container = new HTML_Div();
		$this->container->add_content($this);
		$this->container->add_content($this->label);
	}

	public function get_value() {
		return $this->attributes['checked'] ? $this->attributes['value'] : null;
	}

	public function set_value($value) {
		if(is_array($value)) $value = current($value);
		$this->attributes['checked'] = ($this->value === $value);
	}

	public function html_string() {
		static $recursion = false;
		if($recursion)
			return parent::html_string();

		$recursion = true;
		$html = $this->container->html_string();
		$recursion = false;

		return $html;
	}
}

class PL_Checkbox_Input extends HTML_Checkbox_Input implements PL_Search_Element {
	protected $container;
	protected $label;

	public function __construct($name, $value, $label, $checked = false) {
		parent::__construct($name, $value, $checked);
		$this->label = new HTML_Label($name, $label);

		$this->container = new HTML_Div();
		$this->container->add_content($this);
		$this->container->add_content($this->label);
	}

	public function get_value() {
		return $this->attributes['checked'] ? $this->attributes['value'] : null;
	}

	public function set_value($value) {
		if(is_scalar($value)) $value = array($value);
		$this->attributes['checked'] = in_array($this->value, $value, true);
	}

	public function html_string() {
		static $recursion = false;
		if($recursion)
			return parent::html_string();

		$recursion = true;
		$html = $this->container->html_string();
		$recursion = false;

		return $html;
	}
}

class PL_Select extends HTML_Select implements PL_Search_Element {
	protected $container;
	protected $label;

	public function __construct($name, $label, $multiple = false) {
		parent::__construct($name, $multiple);
		$this->label = new HTML_Label($name, $label);

		$this->container = new HTML_Div();
		$this->container->add_content($this->label);
		$this->container->add_content($this);
	}

	public function add_option($value, $text, $selected = false) {
		$this->add_content(new HTML_Option($value, $text, $selected));
	}

	public function set_value($value) {}
	public function get_value() {}

	public function html_string() {
		static $recursion = false;
		if($recursion)
			return parent::html_string();

		$recursion = true;
		$html = $this->container->html_string();
		$recursion = false;

		return $html;
	}
}

class PL_Radio_Select extends HTML_FieldSet implements PL_Search_Element {
	protected $name;

	public function __construct($name, $label) {
		parent::__construct($label);
		$this->name = $name;
	}

	public function add_option($value, $text, $selected = false) {
		$this->add_content(new PL_Radio_Input($this->name, $value, $text, $selected));
	}

	public function get_value() {
		$value = null;
		foreach($this->contents as $child) if(is_a($child, 'PL_Radio_Input')) {
			if(is_null($value))
				$value = $child->get_value(); // find the first checked radio
			else
				$child->set_value(null); // uncheck the rest
		}
		return $value;
	}

	public function set_value($value) {
		if(is_array($value)) $value = current($value);
		foreach($this->contents as $child) if(is_a($child, 'PL_Radio_Input')) {
			$child->set_value($value);
		}
	}
}

class PL_Checkbox_Select extends HTML_FieldSet implements PL_Search_Element {
	protected $name;

	public function __construct($name, $label) {
		parent::__construct($label);
		$this->name = $name;
	}

	public function add_option($value, $text, $selected = false) {
		$this->add_content(new PL_Checkbox_Input($this->name, $value, $text, $selected));
	}

	public function get_value() {
		$value = array();
		foreach($this->contents as $child) if(is_a($child, 'PL_Checkbox_Input')) {
			$val = $child->get_value();
			if(!is_null($val)) $value[] = $val;
		}
		return count($value) > 1 ? $value : current($value);
	}

	public function set_value($value) {
		if(!is_array($value)) $value = array($value);
		foreach($this->contents as $child) if(is_a($child, 'PL_Checkbox_Input')) {
			$child->set_value($value);
		}
	}
}
