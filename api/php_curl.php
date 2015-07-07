<?php


class PHP_Curl {
	private $handle;
	private $status;

	public function __construct() {
		$this->handle = curl_init();
	}

	public function __destruct() {
		curl_close($this->handle);
	}

	public function get($endpoint, $http_args = null) {
		//curl_reset($this->handle);
		//curl_setopt_array($this->handle, $http_args);

		curl_setopt($this->handle, CURLOPT_URL, $endpoint);
		curl_setopt($this->handle, CURLOPT_HTTPGET, true);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($this->handle);
		$this->status = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
		if($this->status >= 200 && $this->status < 300)
			return ($result ? $result : true);

		return null;
	}

	public function post($endpoint, $form_data, $http_args = null) {
		//curl_reset($this->handle);
		//curl_setopt_array($this->handle, $http_args);

		curl_setopt($this->handle, CURLOPT_URL, $endpoint);
		curl_setopt($this->handle, CURLOPT_POST, true);
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, $form_data);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($this->handle);
		$this->status = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
		if($this->status >= 200 && $this->status < 300)
			return ($result ? $result : true);

		return null;
	}

	public function put($endpoint, $form_data, $http_args = null) {
		//curl_reset($this->handle);
		//curl_setopt_array($this->handle, $http_args);

		curl_setopt($this->handle, CURLOPT_URL, $endpoint);
		curl_setopt($this->handle, CURLOPT_POST, true);
		curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, $form_data);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($this->handle);
		$this->status = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
		if($this->status >= 200 && $this->status < 300)
			return ($result ? $result : true);

		return null;
	}

	public function delete($endpoint, $http_args = null) {
		//curl_reset($this->handle);
		//curl_setopt_array($this->handle, $http_args);

		curl_setopt($this->handle, CURLOPT_URL, $endpoint);
		curl_setopt($this->handle, CURLOPT_HTTPGET, true);
		curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($this->handle);
		$this->status = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
		if($this->status >= 200 && $this->status < 300)
			return ($result ? $result : true);

		return null;
	}
}