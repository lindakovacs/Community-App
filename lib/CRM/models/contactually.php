<?php

PL_CRM_Contactually::init();

class PL_CRM_Contactually extends PL_CRM_Base {
	
	private static $apiOptionKey = "pl_contactually_api_key";
	private static $apiURL = "https://www.contactually.com/api";
	private static $version = "v1";

	private static $contactFieldMeta = array();

	public static function init () {
		// Register this CRM implementation with the controller...
		if (class_exists("PL_CRM_Controller")) {
			$crm_info = array(
				"id" => "contactually",
				"class" => "PL_CRM_Contactually",
				"display_name" => "Contactually",
				"referral_url" => "http://plcstr.com/14gAEUf",
				"cred_lookup_url" => "https://www.contactually.com/settings/integrations",
				"logo_img" => "contactually-logo.png"
			);

			PL_CRM_Controller::registerCRM($crm_info);
		}

		// Initialize contact field -- NOTE: Specific to this CRM's API!!!
		self::$contactFieldMeta = array(
			"id" => array(
				"label" => "ID",
				"data_format" => "string",
				"searchable" => false
			),
			"first_name" => array(
				"label" => "First Name",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"last_name" => array(
				"label" => "Last Name",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"email_addresses" => array(
				"label" => "E-mail(s)",
				"data_format" => "array",
				"searchable" => false
			),
			"phone_numbers" => array(
				"label" => "Phone(s)",
				"data_format" => "array",
				"searchable" => false
			),
			"company" => array(
				"label" => "Company",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"user_bucket" => array(
				"label" => "User Bucket",
				"data_format" => "object",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"tags" => array(
				"label" => "Tags",
				"data_format" => "array",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"last_contacted" => array(
				"label" => "Last Contacted",
				"data_format" => "datetime",
				"searchable" => false
			),
			"hits" => array(
				"label" => "Hits",
				"data_format" => "string",
				"searchable" => false
			)
		);
	}

	public function __construct () {
		// Nothing yet...
	}

	protected function getAPIOptionKey () {
		return self::$apiOptionKey;
	}

	protected function setCredentials (&$handle, &$args) {
		// Attach the API as the first query arg for authentication purposes...
		if (!empty($args["query_params"]) && is_array($args["query_params"])) {
			$args["query_params"]["api_key"] = $this->getAPIKey();
		}
		else {
			$args["query_params"] = array("api_key" => $this->getAPIKey());
		}
	}

	protected function constructURL ($endpoint) {
		$url = self::$apiURL;
		$version = self::$version;

		return "{$url}/{$version}/{$endpoint}.json";
	}

	/*
	 * Contacts
	 */

	public function contactFieldMeta () {
		return self::$contactFieldMeta;
	}  

	public function contactFieldLabels () {
		$labels = array();

		foreach (self::$contactFieldMeta as $field => $meta) {
			$labels[] = $meta["label"]; 
		}

		return $labels;
	}

	public function formatContactData ($value, $format) {
		$newVal = $value;
		
		switch($format) {
			case "boolean":
				$newVal = empty($value) ? "No" : "Yes";
				break;
			case "datetime":
				$date = new DateTime($value);
				$newVal = $date->format("m/d/Y");
				break;
			case "array":
				$newVal = "";
				if (is_array($value)) {
					foreach ($value as $item) {
						$newVal .= "{$item}, ";
					}
					$newVal = rtrim($newVal, ", ");
				}
				break;
			case "object":
				$newVal = empty($value["name"]) ? "" : $value["name"];
				break;
			case "string":
				$newVal = trim($value);
				break;
			default:
				// Do nothing...
				break;
		}

		return $newVal;
	}

	public function getContacts ($filters = array()) {
		// Need to set these as this API does enforce sane defaults..
		$filters["limit"] = ( empty($filters["limit"]) || !is_numeric($filters["limit"]) ? 10 : $filters["limit"] );
		$filters["page"] = ( empty($filters["page"]) || !is_numeric($filters["page"]) ? 1 : $filters["page"] );

		// Translate traditional "offset" field into a valid page number...
		if (isset($filters["offset"])) {
			$limit = $filters["limit"];
			$offset = $filters["offset"];

			// Pages are indexed from 1, so an offset of 0 must translate to the first page and so on...
			$filters["page"] = round(($offset + $limit)/$limit);
			unset($filters["offset"]);
		}

		// Transform field-related filters into CRM-specific search term string...
		$term_str = "";
		$new_filters = array();
		foreach ($filters as $key => $value) {
			// These filters don't need transformation -- copy them as is...
			if ($key == "limit" || $key == "page") {
				$new_filters[$key] = $value;
			}
			else {
				$term_str .= ( empty($term_str) ? "{$key}:{$value}" : " and {$key}:{$value}" );
			}
		}
		
		// If search term string isn't empty/is valid, add it as a filter...
		if (!empty($term_str)) {
			$new_filters["term"] = $term_str;
		}

		// This is a GET request, so mark all filters as query string params...
		$args = array("query_params" => $new_filters);

		// If search term string is set, use a different endpoint...
		$endpoint = ( isset($new_filters["term"]) ? "contacts/search" : "contacts" );

		// Make API Call...
		$response = $this->callAPI($endpoint, "GET", $args);

		// error_log(var_export($response, true));

		// Translate API specific response into standard contacts collection...
		$data = array();
		$data["total"] = empty($response["total_count"]) ? 0 : $response["total_count"];
		$data["contacts"] = (empty($response["contacts"]) || !is_array($response["contacts"])) ? array() : $response["contacts"];

		return $data;
	}

	public function getContact ($id) {
		// Make API Call...
		$response = $this->callAPI("contacts/{$id}", "GET");
		// error_log(var_export($response, true));
		
		$details = array();
		$field_meta = $this->contactFieldMeta();

		if (!empty($response) && is_array($response)) {
			foreach ($response as $key => $value) {
				// Format value with CRM specific method...
				if (!empty($field_meta[$key]["data_format"])) {
					$details[$field_meta[$key]["label"]] = $this->formatContactData($value, $field_meta[$key]["data_format"]);
				}
				else {
					$details[$key] = $value;
				}
			}
		}
		
		$contact = array("name" => "", "details" => array());

		// Translate CRM-specific name field to top-level and remove from details...
		if (!empty($details["full_name"])) {
			$contact["name"] = $details["full_name"];
			unset($details["full_name"]);
		}

		// Handle CRM-specific image field...
		if (!empty($details["avatar"])) {
			$contact["img_url"] = $details["avatar"];
			unset($details["avatar"]);
		}

		$contact["details"] = $details;

		return $contact;
	}

	public function createContact ($args) {
		// error_log("Contactually 'createContact' called...");
		// error_log(var_export($args, true));

		// If a full name has been passed, Split first and last name by the space...
		$name_arr = empty($args["name"]) ? array() : explode(" ", $args["name"]);
		
		// If first name doesn't exist use generic string -- if no last name exists, use a random integer...
		$contact["first_name"] = empty($name_arr[0]) ? "Site User" : $name_arr[0];
		$contact["last_name"] = empty($name_arr[1]) ? (string)rand() : $name_arr[1];

		// Set e-mail...
		$contact["email"] = empty($args["email"]) ? "<none provided>" : $args["email"];

		// TODO: Pass the site's URL as a note, push to a site-specific bucket, pass along the message, etc.
		//
		// Can't do this until Contactually actually documents their API properly AND gives us the ability to
		// create a contact along with appropriate tags, notes + identify the correct bucket in a single call...

		$response = $this->callAPI("contacts", "POST", array("body" => array("contact" => $contact)));

		return $response;
	}

	public function pushEvent ($event_args) {
		// Nothing here yet...
	}

}

?>