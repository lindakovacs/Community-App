<?php

abstract class PL_CRM_Base {

	abstract protected function getAPIOptionKey ();

	public function getAPIKey () {
		$option_key = $this->getAPIOptionKey();
		return PL_Options::get($option_key, null);
	}

	public function setAPIKey ($api_key) {
		$option_key = $this->getAPIOptionKey();
		return PL_Options::set($option_key, $api_key);
	}

	public function resetAPIKey () {
		$option_key = $this->getAPIOptionKey();
		return PL_Options::delete($option_key);
	}

	protected function constructQueryString ($query_params = array()) {
		$query_string = "";

		if (!empty($query_params) && is_array($query_params)) {
			$query_string = "?";
			foreach ($query_params as $key => $value) {
				$val = urlencode($value);
				$query_string .= "{$key}={$val}&";
			}
		}

		// Remove trailing "&" if one exists...
		$query_string = rtrim($query_string, "&");

		return $query_string;
	}

	abstract protected function setCredentials (&$handle, &$args);

	abstract protected function constructURL ($endpoint);

	public function callAPI ($endpoint, $method, $args = array()) {
		// init cURL handle...
		$handle = curl_init();
		$api_key = $this->getAPIKey();
		
		// Set call credentials using CRM specific method...
		$this->setCredentials($handle, $args);

		// error_log(var_export($args, true));

		// Construct URL...
		$query_str = isset($args["query_params"]) ? $this->constructQueryString($args["query_params"]) : "";
		$url = $this->constructURL($endpoint) . $query_str;

		// error_log($url);

		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

		// Use a local cert to make sure we have a valid one when not on the hosted network...
		if (!defined("HOSTED_PLUGIN_KEY")) {
			curl_setopt($handle, CURLOPT_CAINFO, trailingslashit(PLACESTER_PLUGIN_DIR) . "config/cacert.pem");
		}

		// Set the HTTP method...
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);

		// Set connection and transaction timeout values... (upper-bound of 10 seconds if a timeout value isn't defined)
		$timeout = empty($args["timeout"]) ? 10 : $args["timeout"];
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($handle, CURLOPT_TIMEOUT, $timeout); 
		
		// Set payload if it exists...
		if (!empty($args["body"])) {
			curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($args["body"]));
		}

		// make API call
		$response = curl_exec($handle);
		if ($response === false) {
		    $response = array("error" => ("cURL error: " . curl_error($handle) . "\n"));
		}
		else {
			$response = json_decode($response, true);
		}

		return $response;
	}

	/*
	 * Contacts
	 */

	abstract public function contactFieldMeta ();

	abstract public function contactFieldLabels ();

	public function generateContactSearchForm () {
		// Get all "searchable" contact fields...
		$search_fields = array();
		foreach ($this->contactFieldMeta() as $field => $meta) {
			if (isset($meta["searchable"]) && $meta["searchable"] === true) {
				$search_fields[$field] = $meta;
			}
		}

		$form_args = array(
			"method" => "POST", 
			"title" => true, 
			"include_submit" => false, 
			"echo_form" => false, 
			"id" => "contacts_grid_search"
		);

		return PL_Form::generate_form($search_fields, $form_args);
	}

	abstract public function formatContactData ($value, $format);

	abstract public function getContacts ($filters = array());

	abstract public function getContact ($id);

	abstract public function createContact ($args);

	// abstract public function updateContact ($contact_id, $args);

	// abstract public function deleteContact ($contact_id);

	/*
	 * Events
	 */

	abstract public function pushEvent ($event_args);

	/*
	 * Tasks
	 */

	// abstract public function getTasks ($filters);

	// abstract public function createTask ($contact_id, $args);

	// abstract public function updateTask ($task_id, $args);

	// abstract public function deleteTask ($task_id);

	/*
	 * Notes
	 */

	// abstract public function getNotes ($filters);

	// abstract public function createNote ($contact_id, $args);

	// abstract public function updateNote ($note_id, $args);

	// abstract public function deleteNote ($note_id);

	/*
	 * Tags
	 */

	// abstract public function getTags ($filters);

	// abstract public function createTag ($contact_id, $args);

	// abstract public function updateTag ($tag_id, $args);

	// abstract public function deleteTag ($tag_id);

	/* 
	 * Groups/Buckets
	 */

	// abstract public function getGroups ($filters);

	// abstract public function createGroup ($args);

	// abstract public function updateGroup ($group_id, $args);

	// abstract public function deleteGroup ($group_id);
}

?>