<?php


require_once('html.php');


class PL_Input {
	protected $option;
	protected $attribute;
	protected $current_value;

	protected $label;
	protected $input;

	protected function __construct($option, PL_Attribute $attribute, $current_value = null) {
		$this->option = $option;
		$this->attribute = $attribute;
		$this->current_value = $current_value;

		$this->construct_label();
		$this->construct_input();
	}

	protected function construct_label() {
		$this->label = new HTML_Label($this->option, 'Min ' . $this->attribute->display_name);
	}

	protected function construct_input() {}
}

class PL_Text_Input extends PL_Input {
	public function construct_input() {
		$this->input = new HTML_Text_Input($this->option, $this->current_value);
	}
}

class PL_Checkbox_Input extends PL_Input {
	protected $checkbox_value;

	public function __construct($option, PL_Attribute $attribute, $checkbox_value, $current_value = null) {
		parent::__construct($option, $attribute, $current_value);
		$this->checkbox_value = $checkbox_value;
	}

	public function construct_input() {
		$checked = $this->checkbox_value === $this->current_value;
		$this->input = new HTML_Checkbox_Input($this->option, $this->checkbox_value, $checked);
	}
}

class PL_Datalist_Input extends PL_Text_Input {
	protected $datalist_values;
	protected $datalist;

	public function __construct($option, PL_Attribute $attribute, $datalist_values, $current_value = null) {
		parent::__construct($option, $attribute, $current_value);
		$this->datalist_values = $datalist_values;
	}

	public function construct_input() {
		parent::construct_input();
		foreach($this->datalist_values as $key => $value) {

		}
	}
}

class PL_Textarea_Input extends PL_Text_Input {
	public function construct_input() {
		$this->input = new HTML_TextArea($this->option, $this->current_value);
	}
}

class PL_Select extends PL_Input {
	protected $select_values;

	public function __construct($option, PL_Attribute $attribute, $select_values, $current_value = null) {
		parent::__construct($option, $attribute, $current_value);
		$this->select_values = $select_values;
	}
}

class PL_Single_Select extends PL_Select {
	public function construct_input() {
		$this->input = new HTML_Select($this->option);
		foreach($this->select_values as $key => $value) {

		}
	}
}

class PL_Multiple_Select extends PL_Select {
	public function construct_input() {
		$this->input = new HTML_Select($this->option, true);
		foreach($this->select_values as $key => $value) {

		}
	}
}

class PL_Radio_Select extends PL_Single_Select {
}

class PL_Checkbox_Select extends PL_Multiple_Select {
}

