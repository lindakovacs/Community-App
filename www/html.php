<?php


interface HTML {
	public function __toString();
	public function html_string();
}

class HTML_Element implements HTML {
	protected $element;
	protected $attributes;
	protected $contents;

	public $id;
	public $class;
	public $style;

	// abstract
	protected function __construct($element) {
		$this->element = $element;
		$this->attributes = array();
		$this->contents = null;
	}

	// read-only access to protected attributes
	public function __get($name) { return $this->attributes[$name]; }

	// render html
	public function __toString() { return $this->html_string(); }
	public function html_string() {
		$html = '<' . $this->element;
		if($attributes = $this->html_attributes())
			$html .= $attributes;
		$html .= '>';

		if($contents = $this->html_contents())
			$html .= $contents;

		if($contents || !is_null($this->contents))
			$html .= "</$this->element>";

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

	protected function html_content($content) {
		if(is_object($content)) {
			if(method_exists($content, 'html_string')) {
				return $content->html_string(); }
			else if(method_exists($content, '__toString')) {
				return $content->__toString(); }
		}

		else if(is_scalar($content)) {
			return $content; }

		return "";
	}

	protected function html_contents() {
		$html = "";

		if(is_array($this->contents) && !empty($this->contents)) {
			foreach($this->contents as $content)
				$html .= $this->html_content($content);
			return $html;
		}

		return $this->html_content($this->contents);
	}
}


class HTML_Anchor extends HTML_Element {
	public function __construct($href = null) {
		parent::__construct('a');
		$this->attributes['href'] = $href;
	}

	public function add_content($content) { $this->contents[] = $content; }
}


class HTML_Div extends HTML_Element {
	public function __construct($id = null) {
		parent::__construct('div');
		$this->id = $id;
		$this->contents = array();
	}

	public function add_content($content) { $this->contents[] = $content; }
}

class HTML_Span extends HTML_Element {
	public function __construct($id = null) {
		parent::__construct('span');
		$this->id = $id;
		$this->contents = array();
	}

	public function add_content($content) { $this->contents[] = $content; }
}


class HTML_Image extends HTML_Element {
	public function __construct($src, $attributes = null) {
		parent::__construct('img');
		$this->attributes['src'] = $src;

		if($attributes) foreach($attributes as $name => $value) {
			$this->$name = $value;
		}
	}
}


class HTML_Form extends HTML_Element {
	public $method;
	public $target;

	public function __construct($name, $action) {
		parent::__construct('form');
		$this->attributes['name'] = $name;
		$this->attributes['action'] = $action;
		$this->contents = array();
	}

	public function add_content($content) { $this->contents[] = $content; }
}


class HTML_Input extends HTML_Element {
	protected function __construct($type, $name) {
		parent::__construct('input');
		$this->attributes['type'] = $type;
		$this->attributes['name'] = $name;
	}
}

class HTML_Label extends HTML_Element {
	public function __construct($for, $text) {
		parent::__construct('label');
		$this->attributes['for'] = $for;
		$this->contents = is_null($text) ? "" : $text;
	}
}


class HTML_Text_Input extends HTML_Input {
	public $list;
	public $value;

	public function __construct($name, $value = null) {
		parent::__construct('text', $name);
		$this->value = $value;
	}
}

class HTML_Hidden_Input extends HTML_Input {
	public function __construct($name, $value) {
		parent::__construct('hidden', $name, $value);
		$this->attributes['value'] = $value;
	}
}

class HTML_Checkbox_Input extends HTML_Input {
	public $checked;

	public function __construct($name, $value, $checked = false) {
		parent::__construct('checkbox', $name);
		$this->attributes['value'] = $value;
		$this->checked = $checked;
	}
}

class HTML_Radio_Input extends HTML_Input {
	public $checked;

	public function __construct($name, $value, $checked = false) {
		parent::__construct('radio', $name);
		$this->attributes['value'] = $value;
		$this->checked = $checked;
	}
}


class HTML_Button extends HTML_Element {
	public $name;
	public $value;

	public function __construct($label = "") {
		parent::__construct('button');
		$this->attributes['type'] = 'button';
		$this->contents = $label;
	}
}

class HTML_Submit_Button extends HTML_Button {
	public function __construct($label = "Submit") {
		parent::__construct($label);
		$this->attributes['type'] = 'submit';
	}
}

class HTML_Reset_Button extends HTML_Button {
	public function __construct($label = "Reset") {
		parent::__construct($label);
		$this->attributes['type'] = 'reset';
	}
}


class HTML_TextArea extends HTML_Element {
	public function __construct($name, $text = "") {
		parent::__construct('textarea');
		$this->attributes['name'] = $name;
		$this->contents = $text;
	}
}


class HTML_Select extends HTML_Element {
	public $size;

	public function __construct($name, $multiple = false) {
		if($multiple && substr($name, -2) != '[]')
			$name .= '[]';

		parent::__construct('select');
		$this->attributes['name'] = $name;
		$this->attributes['multiple'] = $multiple;
		$this->contents = array();
	}

	public function add_content($content) { $this->contents[] = $content; }
}

class HTML_Option extends HTML_Element {
	public $selected;

	public function __construct($value, $text, $selected = false) {
		parent::__construct('option');
		$this->attributes['value'] = $value;
		$this->selected = $selected;
		$this->contents = $text;
	}
}

class HTML_Datalist extends HTML_Element {
	public function __construct($id) {
		parent::__construct('datalist');
		$this->id = $id;
		$this->contents = array();
	}

	public function add_content($content) { $this->contents[] = $content; }
}


class HTML_FieldSet extends HTML_Element {
	protected $legend;

	public function __construct($legend) {
		parent::__construct('fieldset');
		$this->legend = (is_a($legend, 'HTML_Legend') ? $legend : new HTML_Legend($legend));
		$this->contents = array($this->legend);
	}

	public function add_content($content) { $this->contents[] = $content; }
}

class HTML_Legend extends HTML_Element {
	public function __construct($text) {
		parent::__construct('legend');
		$this->contents = is_null($text) ? "" : $text;
	}
}
