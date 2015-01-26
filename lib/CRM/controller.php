<?php

PL_CRM_Controller::init();

class PL_CRM_Controller {

	private static $activeCRMKey = "pl_active_CRM";
	private static $registeredCRMList = array();

	public static function init () {
		// Load CRM libs...
		include_once("models/base.php");
		include_once("models/contactually.php");
		include_once("models/followupboss.php");

		// Load any necessary non-CRM plugin libs...
		$curr_dir = trailingslashit(dirname(__FILE__));
		include_once("{$curr_dir}../../models/options.php");
		include_once("{$curr_dir}../../lib/caching.php");
		include_once("{$curr_dir}../../lib/form.php");

		// Register main AJAX endpoint for all CRM calls...
		add_action("wp_ajax_crm_ajax_controller", array(__CLASS__, "ajaxController"));
	}

	public static function ajaxController () {
		// error_log("In ajaxController...");
		// error_log(var_export($_POST, true));
		// die();

		// TODO: A better default message...
		$response = "";

		// CRM-related AJAX calls (i.e., to the single endpoint defined in init) MUST specify a
		// field called "crm_method" that corresponds to the class function it wants to execute,
		// along with the properly labeled fields as subsequent arguments...
		if (!empty($_POST["crm_method"])) {
			// Set args array if it exists...
			$args = ( !empty($_POST["crm_args"]) && is_array($_POST["crm_args"]) ? array_values($_POST["crm_args"]) : array() );

			// Special handling for AJAX requests to populate contact "dataTable" grids...
			if (isset($_POST["sEcho"])) {
				$args = array($_POST);
			}

			// Execute primary function...
			$callback = array(__CLASS__, $_POST["crm_method"]);
			$response = call_user_func_array($callback, $args);

			// Check to see if a separate callback is specified for what is returned...
			if (!empty($_POST["return_spec"]) && is_array($_POST["return_spec"])) {
				$ret = $_POST["return_spec"];
				$ret_args = ( !empty($ret["args"]) && is_array($ret["args"]) ? array_values($ret["args"]) : array() );

				// Set response to return method's value...
				$ret_callback = array(__CLASS__, $ret["method"]);
				$response = call_user_func_array($ret_callback, $ret_args);
			}

			// Handle formatting response if set to JSON...
			if (!empty($_POST["response_format"]) && strtoupper($_POST["response_format"]) == "JSON") {
		 		$response = json_encode($response);
	 		}
 		}

		// Write payload to response...
		echo $response;

		die();
	}

	/*
	 * Utility CRM methods...
	 */

	public static function registerCRM ($crm_info) {
		// We need an id...
		if (empty($crm_info["id"])) { return; }

		// Translate logo image file into valid URL path...
		if (!empty($crm_info["logo_img"])) {
			$crm_info["logo_img"] = self::getImageURL($crm_info["logo_img"]);
		}

		self::$registeredCRMList[$crm_info["id"]] = $crm_info;
	}

	public static function getCRMInfo ($crm_id) {
		$info = array();

		if (!empty(self::$registeredCRMList[$crm_id])) {
			$info = self::$registeredCRMList[$crm_id];	
		}

		return $info;
	}

	public static function integrateCRM ($crm_id, $api_key) {
		// Try to create an instance...
		$crm_obj = self::getCRMInstance($crm_id);

		// Set (i.e., store) credentials/API key for this CRM so that it can be activated...
		$result = false;
		if (!is_null($crm_obj)) {
			$crm_obj->setAPIkey($api_key);
			
			// Activate the newly integrated API key by default...
			$result = self::setActiveCRM($crm_id);
		}

		return $result;
	}

	/* The opposite of integration -- remove key/credentials associated with the passed CRM... */
	public static function resetCRM ($crm_id) {
		// Try to create an instance...
		$crm_obj = self::getCRMInstance($crm_id);

		// Reset (i.e., remove) credentials/API key associated with the CRM so new ones can be entered...
		return ( is_null($crm_obj) ? false : $crm_obj->resetAPIkey() );
	}

	public static function getActiveCRM () {
		return PL_Options::get(self::$activeCRMKey, null);
	}

	public static function setActiveCRM ($crm_id) {
		return PL_Options::set(self::$activeCRMKey, $crm_id);
	}

	public static function resetActiveCRM () {
		return PL_Options::delete(self::$activeCRMKey);
	}

	/* Exposes all public CRM library methods... */
	public static function callCRMLib ($method, $args = array()) {
		$retVal = null;

		// Try to create an instance...
		$crm_id = self::getActiveCRM();
		$crm_obj = self::getCRMInstance($crm_id);

		if (!is_null($crm_obj) && method_exists($crm_obj, $method)) {
			$retVal = $crm_obj->$method($args);
		}

		return $retVal;
	}

	public static function getContactGridData ($args = array()) {
		// error_log("In getGridData...");
		// error_log(var_export($args, true));

		// Try to create an instance...
		$crm_id = self::getActiveCRM();
		$crm_obj = self::getCRMInstance($crm_id);
		
		// Grab field keys for setting filters and returning a subset of the data in order...
		$field_meta = $crm_obj->contactFieldMeta();

		$filters = array();

		// Check for keys in the args array that match the CRM's predefined contact fields
		// in order to build a list of filters for the getContacts call...
		foreach ($field_meta as $field_key => $meta) {
			if (isset($args[$field_key])) {
				$filters[$field_key] = $args[$field_key];
			}
		}

		// Pagination
		$filters["limit"] = $args["iDisplayLength"];
		$filters["offset"] = $args["iDisplayStart"];
		
		// Get grid data...
		$data = $crm_obj->getContacts($filters);

		// Format grid data in a form dataTables.js expects for rendering...
		$grid_rows = array();
		if (!empty($data["contacts"]) && is_array($data["contacts"])) {
			foreach ($data["contacts"] as $index => $contact) {
				foreach ($field_meta as $field_key => $meta) {
					$val = empty($contact[$field_key]) ? "" : $contact[$field_key];

					// Format value with CRM specific method...
					if (!empty($meta["data_format"])) {
						$val = $crm_obj->formatContactData($val, $meta["data_format"]);
					}

					$grid_rows[$index][] = $val;
				}
			}
		}

		// Set total from API response -- corresponds to all possible contacts available...
		$total = empty($data["total"]) ? 0 : $data["total"];

		// Required for datatables.js grid to render and function properly...
		$grid_data["sEcho"] = $args["sEcho"];
		$grid_data["aaData"] = $grid_rows;
		$grid_data["iTotalRecords"] = $total;
		$grid_data["iTotalDisplayRecords"] = $total;

		// error_log(var_export($grid_data, true));
		return $grid_data;
	}

	/*
	 * Helpers...
	 */

	private static function getCRMInstance ($crm_id) {
		$crm_obj = null;

		// Lookup CRM info by ID to make sure it is supported...
		if (!empty(self::$registeredCRMList[$crm_id])) {
			// Get class and construct an instance...
			$crm_info = self::$registeredCRMList[$crm_id];
			$crm_class = $crm_info["class"];
			$crm_obj = new $crm_class();
		}
	
		return $crm_obj;
	}

	private static function sanitizeInput ($str_input) {
		// Removes backslashes then proceeds to remove all HTML tags...
		$sanitized = strip_tags(stripslashes($str_input));

		return $sanitized;
	}

	/*
	 * Serve up view(s)...
	 */

	public static function getView () {
		// Check if a CRM is active...
		$active_crm = self::getActiveCRM();
		
		// Render HTML...
		$html = ( empty($active_crm) ? self::settingsView() : self::browseView($active_crm) );

		return $html;
	}

	public static function settingsView () {
		ob_start();	
			// Set this var for use in the settings view...
			$crm_list = self::$registeredCRMList;
			include("views/settings.php");
		$html = ob_get_clean();

		return $html;
	}

	public static function browseView ($active_crm_id = null) {
		// Get active CRM's id...
		$active_crm = empty($active_crm_id) ? self::getActiveCRM() : $active_crm_id;

		if (!empty($active_crm)) {
			ob_start();
				// Set this var for use in the browse view...
				$crm_info = self::$registeredCRMList[$active_crm];
				include("views/browse.php");
			$html = ob_get_clean();
		}

		return $html;
	}

	public static function getPartial ($partial, $args = array()) {
		// Establish partials dir...
		$file_path = trailingslashit(dirname(__FILE__)) . "views/partials/{$partial}.php";
		$html = "";

		// Make sure partial file exists...
		if (file_exists($file_path)) {
			// Extract args to be used by the partial...
			extract($args);

			ob_start();
				include($file_path);
			$html = ob_get_clean();
		}

		return $html;
	}

	public static function getImageURL ($img_file) {
		// $img_path = trailingslashit(dirname(__FILE__)) . "views/images/{$img_file}";
		// $img_url = plugin_dir_url($img_path) . $img_file;
		$img_url = PL_LIB_URL . 'CRM/views/images/' . $img_file;

		return $img_url;
	}
}

?>