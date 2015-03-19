<?php

class PDX_API_Connection {
	public $API_KEY;
	public $NON_IMPORT;
	public $ADDRESS_MODE;
	public $INCLUDE_DISABLED;

	public $HTTP_ARGS;
	private $http_class;
	private $http;

	public function __construct($key, $http_class = null) {
		if(empty($http_class))
			$http_class = (class_exists('WP_Http') ? 'WP_Http' : 'PHP_Curl');

		$this->http_class = $http_class;
		$this->http = new $http_class();
		$this->API_KEY = $key;
	}

	protected function GET($endpoint) {
		$response = $this->http->get($endpoint, $this->HTTP_ARGS);
		if(is_array($response))
			return $response['body'];
		return null;
	}

	protected function POST($endpoint, $form_data) {
		if($this->http_class == 'WP_Http') {
			$args = array('method' => 'POST', 'body' => $form_data);
			if(is_array($this->HTTP_ARGS)) $args = array_merge($args, $this->HTTP_ARGS);
			$response = $this->http->post($endpoint, $args);
		}
		else {
			$response = $this->http->post($endpoint, $form_data, $this->HTTP_ARGS);
		}

		if(is_array($response))
			return $response['body'];
		return null;
	}

	public function GET_WHOAMI() {
		$endpoint = "http://api.placester.com/api/v2/organizations/whoami?api_key=$this->API_KEY";
		return $this->GET($endpoint);
	}

	public function GET_LOCATIONS() {
		$endpoint = "http://api.placester.com/api/v2/listings/locations?api_key=$this->API_KEY";
		return $this->GET($endpoint);
	}

	public function GET_ATTRIBUTES() {
		$endpoint = "http://api.placester.com/api/v2/custom/attributes?api_key=$this->API_KEY";
		return $this->GET($endpoint);
	}

	public function GET_LISTING($id) {
		$endpoint = "http://api.placester.com/api/v2.1/listings/$id?api_key=$this->API_KEY" .
			"&address_mode=" . ($this->ADDRESS_MODE ? $this->ADDRESS_MODE : "exact") .
			"&include_disabled=" . ($this->INCLUDE_DISABLED ? "1" : "0") .
			"&non_import=" . ($this->NON_IMPORT ? "1" : "0");
		return $this->GET($endpoint);
	}

	public function GET_LISTINGS($search = null) {
		$endpoint = "http://api.placester.com/v2.1/listings?api_key=$this->API_KEY" .
			"&address_mode=" . ($this->ADDRESS_MODE ? $this->ADDRESS_MODE : "exact") .
			"&include_disabled=" . ($this->INCLUDE_DISABLED ? "1" : "0") .
			"&non_import=" . ($this->NON_IMPORT ? "1" : "0");
		return $this->POST($endpoint, $search);
	}

	public function GET_AGGREGATE($attribute, $search = null) {
		$endpoint = "http://api.placester.com/api/v2.1/listings/aggregate?api_key=$this->API_KEY" .
			"&address_mode=" . ($this->ADDRESS_MODE ? $this->ADDRESS_MODE : "exact") .
			"&include_disabled=" . ($this->INCLUDE_DISABLED ? "1" : "0") .
			"&non_import=" . ($this->NON_IMPORT ? "1" : "0");

		if(is_scalar($attribute)) {
			$endpoint .= "&keys[]=" . $attribute;
		}
		elseif(is_array($attribute)) {
			foreach($attribute as $a) {
				$endpoint .= "&keys[]=" . $a;
			}
		}

		return $this->GET($endpoint);
	}

	public function CREATE_LISTING($form_data) {
		$endpoint = "http://api.placester.com/api/v2/listings";
		$form_data = "api_key=$this->API_KEY&" . $form_data;
		return $this->POST($endpoint, $form_data);
	}

	public function UPDATE_LISTING($id, $form_data) {
		$endpoint = "http://api.placester.com/api/v2/listings/$id";
		$form_data = "api_key=$this->API_KEY&" . $form_data;
		return $this->POST($endpoint, $form_data, $this->HTTP_ARGS);
	}

	public function DELETE_LISTING($id) {
		$endpoint = "http://api.placester.com/api/v2/listings/$id?api_key=$this->API_KEY";
		if($this->http_class == 'WP_Http') {
			$args = array('method' => 'DELETE');
			if(is_array($this->HTTP_ARGS)) $args = array_merge($args, $this->HTTP_ARGS);
			$response = $this->http->get($endpoint, $args);
		}
		else {
			$response = $this->http->delete($endpoint, $this->HTTP_ARGS);
		}
	}
}
