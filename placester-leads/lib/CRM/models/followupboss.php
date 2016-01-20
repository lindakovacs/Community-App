<?php

PL_CRM_Followupboss::init();

class PL_CRM_Followupboss extends PL_CRM_Base {
	
	private static $apiOptionKey = "pl_followupboss_api_key";
	private static $apiURL = "https://api.followupboss.com";
	private static $version = "v1";

	private static $contactFieldMeta = array();

	public static function init () {
		// Register this CRM implementation with the controller...
		if (class_exists("PL_CRM_Controller")) {
			$crm_info = array(
				"id" => "followupboss",
				"class" => "PL_CRM_Followupboss",
				"display_name" => "Follow Up Boss",
				"referral_url" => "http://plcstr.com/14gAU5y",
				"cred_lookup_url" => "https://app.followupboss.com/settings/user",
				"logo_img" => "follow-up-boss-color.png"
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
			"firstName" => array(
				"label" => "First Name",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"lastName" => array(
				"label" => "Last Name",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"emails" => array(
				"label" => "E-mail(s)",
				"data_format" => "array",
				"searchable" => false
			),
			"phones" => array(
				"label" => "Phone(s)",
				"data_format" => "array",
				"searchable" => false
			),
			"stage" => array(
				"label" => "Stage",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"source" => array(
				"label" => "Source",
				"data_format" => "string",
				"searchable" => true,
				"group" => "Search",
				"type" => "text"
			),
			"lastActivity" => array(
				"label" => "Last Activity",
				"data_format" => "datetime",
				"searchable" => false
			),
			"contacted" => array(
				"label" => "Contacted",
				"data_format" => "boolean",
				"searchable" => true,
				"group" => "Search",
				"type" => "checkbox"
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
		$api_key = $this->getAPIKey();

		// HTTP authentication using the API key as user name with no password...
		curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($handle, CURLOPT_USERPWD, $api_key . ":");
	}

	public function constructURL ($endpoint) {
		$url = self::$apiURL;
		$version = self::$version;

		return "{$url}/{$version}/{$endpoint}";
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
						if (is_array($item)) {
							$newVal .= $item['value'] . ", ";
						}
					}
					$newVal = rtrim($newVal, ", ");
				}
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
		// This is a GET request, so mark all filters as query string params...
		$args = array("query_params" => $filters);

		// Make API Call...
		$response = $this->callAPI("people", "GET", $args);

		// error_log(var_export($response, true));

		// Translate API specific response into standard contacts collection...
		$data = array();
		$data["total"] = empty($response["_metadata"]["total"]) ? 0 : $response["_metadata"]["total"];
		$data["contacts"] = (empty($response["people"]) || !is_array($response["people"])) ? array() : $response["people"];

		return $data;
	}

	public function getContact ($id) {
		// Make API Call...
		$response = $this->callAPI("people/{$id}", "GET");
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
		if (!empty($details["name"])) {
			$contact["name"] = $details["name"];
			unset($details["name"]);
		}

		$contact["details"] = $details;

		return $contact;
	}

	public function createContact ($args) {
		$placester_type = empty($args["action"]) ? "pl_general" : $args["action"];
		$args["type"] = $this->translateEventType($placester_type);

		$response = $this->pushEvent($args);
		return $response;
	}
	
	// Translate Placester event type to Follow Up Boss nomenclature...
	public function translateEventType ($placester_type) {
		$type = "";

		switch ($placester_type) {
			case "pl_register_site_user":
				$type = "Registration";
				break;
			// case "":
			// 	$type = "Property Inquiry"
			// 	break;
			// case "":
			// 	$type = "Viewed Property"
			// 	break;
			// case "":
			// 	$type = "Saved Property"
			// 	break;
			// case "":
			// 	$type = "Property Search"
			// 	break;
			// case "":
			// 	$type = "Saved Property Search"
			// 	break;
			case "pl_general":
			default:
				$type = "General Inquiry";
		}

		return $type;
	}

	public function pushEvent ($event_args) {
		// Create event array that will be the POSTFIELDS payload (encoded as JSON)...
		// $event = array(
		    // "property" => array(
		    //     "street" => "1234 High Oak St",
		    //     "city" => "Rochester",
		    //     "state" => "WA",
		    //     "code" => "98579",
		    //     "mlsNumber" => "1234567",
		    //     "price" => 449000,
		    //     "forRent" => false,
		    //     "url" => "http://www.myawesomewebsite.com/property/1234567-1234-high-oak-st-rochester-wa-98579/",
		    //     "type" => "Single-Family Home",
		    //     "bedrooms" => 3,
		    //     "bathrooms" => 2,
		    //     "area" => 2888,
		    //     "lot" => 0.98
		    // ),
	    	// "propertySearch" => array(
		    //     "type" => "Apartment",
		    //     "neighborhood" => "East Boston",
		    //     "city" => "Boston",
		    //     "state" => "MA",
		    //     "code" => "02128",
		    //     "minPrice" => 50000,
		    //     "maxPrice" => 850000,
		    //     "minBedrooms" => 2,
		    //     "maxBedrooms" => 3,
		    //     "minBathrooms" => 1,
		    //     "maxBathrooms" => 2,
		    //     "minArea" => 1000,
		    //     "maxArea" => 2000,
		    //     "forRent" => false
		    // ),
		    // "campaign" => array(
		    //     "source" => "google",
		    //     "medium" => "organic",
		    //     "term" => "east boston homes",
		    //     "content" => "",
		    //     "campaign" => ""
		    // )
		// );

		$event = array();

		// Necessary attributes...
		$event["source"] = site_url();
		$event["type"] = empty($event_args["type"]) ? "Site Lead" : $event_args["type"];
		$event["person"] = array();

		// Person attributes...
		if (!empty($event_args["name"])) {
			// Split first and last name by space...
			$name_arr = explode(" ", $event_args["name"]);
			
			$event["person"]["firstName"] = @$name_arr[0];
			$event["person"]["lastName"] = @$name_arr[1];
		}
		else {
			// If no name is present, enter generic one using a random integer...
			$event["person"]["firstName"] = "Site User";
			$event["person"]["lastName"] = (string)rand();
		}

		if (!empty($event_args["email"])) {
			$event["person"]["emails"] = array(array("value" => $event_args["email"], "type" => "home"));
		}

		if (!empty($event_args["phone"])) {
			$event["person"]["phones"] = array(array("value" => $event_args["phone"], "type" => "home"));
		}

		// Include message if one exists...
		if (!empty($event_args["question"])) {
			$event["message"] = $event_args["question"];
		}

		// Property attributes...
		// TODO!

		// Property search attributes...
		// TODO!

		// Campaign attributes...
		// TODO!

		// Set field the caller is expecting to set request payload...
		$args = array("body" => $event);

		// Make API Call...
		$response = $this->callAPI("events", "POST", $args);
		
		// error_log("Event push response: ");
		// error_log(var_export($response, true));
		return $response;
	}
}

?>