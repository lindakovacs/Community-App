<?php


class PL_Input {
	protected $type;
	public $name;
	public $value;

	public $disabled;
	public $readonly;

	// abstract
	protected function __construct($name, $value = null) {
		$this->name = $name;
		$this->value = $value;
	}

	public function html_string() {
		$html = "<input type='$this->type' name='$this->name'";
		if(!is_null($this->value)) $html .= " value='$this->value'";
		if(!is_null($this->disabled)) $html .= " disabled";
		if(!is_null($this->readonly)) $html .= " readonly";
		$html .= ">";

		return $html;
	}

	public function __to_string() { return $this->html_string(); }
}


class PL_Text_Input extends PL_Input {
	protected function __construct($name, $value = null) {

	}
}

class PL_Hidden_Input extends PL_Input {
	protected function __construct() {}
}

class PL_Button_Input extends PL_Input {

}

class PL_Checkbox_Input extends PL_Input {

}


class PL_Select extends PL_Input {

}

class PL_Radio_Fieldset extends PL_Input {

}

class PL_Multi_Select extends PL_Select {

}

class PL_Checkbox_Fieldset extends PL_Multi_Select {

}


}