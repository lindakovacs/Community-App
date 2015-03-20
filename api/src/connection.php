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

	protected function process_wp_http_response($response) {
		if(is_array($response) && is_array($response['response']))
			if($response['response']['code'] >= 200 && $response['response']['code'] < 300)
				return ($response['body'] ? $response['body'] : true);

		return null;
	}

	protected function PUT($endpoint, $form_data) {
		if($this->http_class == 'WP_Http') {
			$args = array('method' => 'PUT', 'body' => $form_data);
			if(is_array($this->HTTP_ARGS)) $args = array_merge($args, $this->HTTP_ARGS);
			return $this->process_wp_http_response($this->http->post($endpoint, $args));
		}

		return $this->http->put($endpoint, $form_data, $this->HTTP_ARGS);
	}

	protected function GET($endpoint) {
		if($this->http_class == 'WP_Http') {
			return $this->process_wp_http_response($this->http->get($endpoint, $this->HTTP_ARGS));
		}

		return $this->http->get($endpoint, $this->HTTP_ARGS);
	}

	protected function POST($endpoint, $form_data) {
		if($this->http_class == 'WP_Http') {
			$args = array('method' => 'POST', 'body' => $form_data);
			if(is_array($this->HTTP_ARGS)) $args = array_merge($args, $this->HTTP_ARGS);
			return $this->process_wp_http_response($this->http->post($endpoint, $args));
		}

		return $this->http->post($endpoint, $form_data, $this->HTTP_ARGS);
	}

	protected function DELETE($endpoint) {
		if($this->http_class == 'WP_Http') {
			$args = array('method' => 'DELETE');
			if(is_array($this->HTTP_ARGS)) $args = array_merge($args, $this->HTTP_ARGS);
			return $this->process_wp_http_response($this->http->get($endpoint, $args));
		}

		return $this->http->delete($endpoint, $this->HTTP_ARGS);
	}

	public function WHOAMI() {
		$endpoint = "http://api.placester.com/api/v2/organizations/whoami?api_key=$this->API_KEY";
		return json_decode($this->GET($endpoint));
	}

	public function LOCATIONS() {
		$endpoint = "http://api.placester.com/api/v2/listings/locations?api_key=$this->API_KEY";
		return json_decode($this->GET($endpoint));
	}

	public function ATTRIBUTES() {
		$endpoint = "http://api.placester.com/api/v2/custom/attributes?api_key=$this->API_KEY";
		return json_decode($this->GET($endpoint));
	}

	public function SEARCH_LISTINGS($search = null) {
		$endpoint = "http://api.placester.com/v2.1/listings?api_key=$this->API_KEY" .
			"&address_mode=" . ($this->ADDRESS_MODE ? $this->ADDRESS_MODE : "exact") .
			"&include_disabled=" . ($this->INCLUDE_DISABLED ? "1" : "0") .
			"&non_import=" . ($this->NON_IMPORT ? "1" : "0");
		$result = $this->POST($endpoint, $search);

		if($result && $result !== true && ($result = json_decode($result)))
			if(isset($result->total))
				return $result;

		// TODO error handling
		return null;
	}

	public function SEARCH_AGGREGATE($attributes = 'keys[]=compound_type', $search = null) {
		$endpoint = "http://api.placester.com/api/v2.1/listings/aggregate?api_key=$this->API_KEY" .
			"&address_mode=" . ($this->ADDRESS_MODE ? $this->ADDRESS_MODE : "exact") .	// needed because of PRODUCT-1298
			"&include_disabled=" . ($this->INCLUDE_DISABLED ? "1" : "0") .
			"&non_import=" . ($this->NON_IMPORT ? "1" : "0");

		$endpoint .= $attributes ? '&' . $attributes : '';
		$endpoint .= $search ? '&' . $search : '';
		return json_decode($this->GET($endpoint));	// does the server support POST on this endpoint?
	}

	public function CREATE_LISTING($form_data) {
		$endpoint = "http://api.placester.com/api/v2/listings";
		$form_data = "api_key=$this->API_KEY&" . $form_data;
		$result = $this->POST($endpoint, $form_data);

		if($result && $result !== true && ($result = json_decode($result)))
			if(isset($result->id))
				return $result->id;

		// TODO error handling
		return null;
	}

	public function GET_LISTING($id) {
		$endpoint = "http://api.placester.com/api/v2.1/listings/$id?api_key=$this->API_KEY" .
			"&address_mode=" . ($this->ADDRESS_MODE ? $this->ADDRESS_MODE : "exact");
		$result = $this->GET($endpoint);

		if($result && $result !== true && ($result = json_decode($result)))
			if(isset($result->id))
				return $result;

		// TODO error handling
		return null;
	}

	public function UPDATE_LISTING($id, $form_data) {
		$endpoint = "http://api.placester.com/api/v2/listings/$id";
		$form_data = "api_key=$this->API_KEY&" . $form_data;
		$result = $this->PUT($endpoint, $form_data, $this->HTTP_ARGS);

		if($result && $result !== true && ($result = json_decode($result)))
			if(isset($result->id))
				return $result->id;

		// TODO error handling
		return null;
	}

	public function DELETE_LISTING($id) {
		$endpoint = "http://api.placester.com/api/v2/listings/$id?api_key=$this->API_KEY";
		return $this->DELETE($endpoint);
	}
}
