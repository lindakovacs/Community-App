<?php


class HTML_Element {
	protected $element;
	protected $attributes;

	public $id;
	public $class;
	public $style;

	// abstract
	protected function __construct($element) { $this->element = $element; $this->attributes = array(); }

	public function __to_string() { return $this->html_string(); }
	public function html_string() {
		$html = '<' . $this->element;
		if($attributes = $this->html_attributes())
			$html .= $attributes;
		$html .= ">";
		return $html;
	}

	protected function html_attribute($name, $value) {
		$html = "";

		if(!is_null($value) && $value !== false) {
			if($value === true)
				$html = " $name";
			else
				$html = " $name=\"$value\"";
		}

		return $html;
	}

	protected function html_attributes() {
		$html = "";

		// loop through protected html attributes
		foreach ($this->attributes as $name => $value)
			$html .= $this->html_attribute($name, $value);

		// all public properties are html attributes
		$reflection = new ReflectionObject($this);
		foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
			$html .= $this->html_attribute($property->getName(), $property->getValue($this));

		return $html;
	}
}

class HTML_Compound_Element extends HTML_Element {
	public function html_string() {
		$html = parent::html_string();
		$html .= $this->html_contents();
		$html .= "</$this->element>";
		return $html;
	}

	protected function html_contents() { return ""; }
}

class HTML_Input extends HTML_Element {
	public $value;

	protected function __construct($type, $name, $value) {
		parent::__construct('input');
		$this->attributes['type'] = $type;
		$this->attributes['name'] = $name;
		$this->value = $value;
	}
}

class HTML_Text_Input extends HTML_Input {
	public function __construct($name, $value = null) {
		parent::__construct('text', $name, $value);
	}
}

class HTML_Hidden_Input extends HTML_Input {
	public function __construct($name, $value) {
		parent::__construct('hidden', $name, $value);
	}
}

class HTML_Button_Input extends HTML_Input {
	public function __construct($name, $value) {
		parent::__construct('button', $name, $value);
	}
}

class HTML_Submit_Input extends HTML_Input {
	public function __construct($name, $value) {
		parent::__construct('submit', $name, $value);
	}
}

class HTML_Checkbox_Input extends HTML_Input {
	public $checked;

	public function __construct($name, $value, $checked = false) {
		parent::__construct('checkbox', $name, $value);
		$this->checked = $checked;
	}
}

class HTML_Radio_Input extends HTML_Input {
	public $checked;

	public function __construct($name, $value, $checked = false) {
		parent::__construct('radio', $name, $value);
		$this->checked = $checked;
	}
}

class HTML_TextArea extends HTML_Compound_Element {
	protected $text;

	public function __construct($name, $text = null) {
		parent::__construct('textarea');
		$this->attributes['name'] = $name;
		$this->text = $text;
	}

	public function set_text($text) { $this->text = $text; }
	public function get_text() { return $this->text; }

	protected function html_contents() { return $this->text; }
}

class HTML_Select extends HTML_Compound_Element {
	protected $options;
	public $multiple;
	public $size;

	public function __construct($name, $multiple = false) {
		parent::__construct('select');
		$this->attributes['name'] = $name;
		$this->options = array();
		$this->multiple = $multiple;
	}

	public function add_option(HTML_Option $option) { $this->options[] = $option; }
	public function get_options() { return $this->options; }
	public function clear_options() { $this->options = array(); }

	protected function html_contents() {
		$html = "";
		foreach($this->options as $option)
			$html .= $option->html_string();

		return $html;
	}
}

class HTML_Option extends HTML_Compound_Element {
	protected $text;
	public $selected;

	public function __construct($value, $text, $selected = false) {
		parent::__construct('option');
		$this->attributes['value'] = $value;
		$this->selected = $selected;
		$this->text = $text;
	}

	protected function html_contents() { return $this->text; }
}

class HTML_FieldSet extends HTML_Compound_Element {
	protected $legend;
	protected $fields;

	public function __construct($legend) {
		parent::__construct('fieldset');
		$this->legend = is_a($legend, 'HTML_Legend') ? $legend : new HTML_Legend($legend);
	}

	public function add_field(HTML_Element $field) { $this->fields[] = $field; }
	public function get_fields() { return $this->fields; }
	public function clear_fields() { $this->fields = array(); }

	protected function html_contents() {
		$html = $this->legend ? $this->legend->html_string() : "";
		foreach($this->fields as $field)
			$html .= $field->html_string();

		return $html;
	}
}

class HTML_Label extends HTML_Compound_Element {
	protected $text;

	public function __construct($for, $text) {
		parent::__construct('label');
		$this->attributes['for'] = $for;
		$this->text = $text;
	}

	protected function html_contents() { return $this->text; }
}

class HTML_Legend extends HTML_Compound_Element {
	protected $text;

	public function __construct($text) {
		parent::__construct('legend');
		$this->text = $text;
	}

	protected function html_contents() { return $this->text; }
}

