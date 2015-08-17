<?php


require_once(BUILDER . 'api/connection.php');


class PL_Request_Filter extends PL_Search_Filter {
	public function __construct(PL_Attribute_Map $attributes, $request = null) {
		parent::__construct($attributes);

		if(!$request)
			$request = $_REQUEST;

		$filter_options = $this->get_filter_options_array(true);
		foreach($request as $field => $value) {
			if($filter_options[$field]) {
				if(is_array($value) && !($this->allow_array($field)))
					continue;
				$this->set($field, $value);
			}
		}
	}
}

class PL_Request_View extends PL_Search_View {
	public function __construct(PL_Attribute_Map $attributes, $request = null) {
		parent::__construct($attributes);

		if(!$request)
			$request = $_REQUEST;

		$view_options = $this->get_view_options_array(true);
		foreach($request as $field => $value) {
			if($view_options[$field])
				$this->set($field, $value);
		}
	}
}


