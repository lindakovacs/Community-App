<?php

PL_CRM_Controller::init();

class PL_CRM_Controller {

	private static $primaryCRM = "internal";
	private static $availableCRMList = array();

	public static function init () {
		include_once("models/base.php");
		include_once("models/internal.php");
		include_once("models/contactually.php");
		include_once("models/followupboss.php");

		add_action("wp_ajax_crm_ajax_controller", array(__CLASS__, "ajaxController"));
	}

	public static function ajaxController () {
		$response = "";

		if (!empty($_POST["crm_method"])) {
			$args = ( !empty($_POST["crm_args"]) && is_array($_POST["crm_args"]) ? array_values($_POST["crm_args"]) : array() );

			// indicates request by dataTables
			if (isset($_POST["sEcho"]))
				$args = array($_POST);

			$callback = array(__CLASS__, $_POST["crm_method"]);
			$response = call_user_func_array($callback, $args);

			if (!empty($_POST["return_spec"]) && is_array($_POST["return_spec"])) {
				$ret = $_POST["return_spec"];
				$ret_args = ( !empty($ret["args"]) && is_array($ret["args"]) ? array_values($ret["args"]) : array() );

				$ret_callback = array(__CLASS__, $ret["method"]);
				$response = call_user_func_array($ret_callback, $ret_args);
			}

			if (!empty($_POST["response_format"]) && strtoupper($_POST["response_format"]) == "JSON")
		 		$response = json_encode($response);
 		}

		echo $response;
		die();
	}

	public static function getCRMInfo ($crm_id) {
		if (is_array(self::$availableCRMList[$crm_id]))
			return self::$availableCRMList[$crm_id];

		return array();
	}

	public static function getCRMInstance ($crm_id = null) {
		if(empty($crm_id)) $crm_id = self::$primaryCRM;

		if (self::$availableCRMList[$crm_id]) {
			$crm_info = self::$availableCRMList[$crm_id];

			if(!empty($crm_info["instance"]))
				return $crm_info["instance"];

			return $crm_info["instance"] = new $crm_info["class"]();
		}

		return null;
	}

	public static function registerCRM ($crm_info) {
		if ($crm_info["id"]) {
			if ($crm_info["logo_img"])
				$crm_info["logo_img"] = PL_LEADS_URL . 'lib/CRM/views/images/' . $crm_info["logo_img"];

			self::$availableCRMList[$crm_info["id"]] = $crm_info;
			return true;
		}

		return false;
	}

	public static function integrateCRM ($crm_id, $api_key) {
		if($crm_obj = self::getCRMInstance($crm_id)) {
			$crm_obj->setAPIkey($api_key);
			return self::activateCRM($crm_id);
		}

		return false;
	}

	public static function resetCRM ($crm_id) {
		if($crm_obj = self::getCRMInstance($crm_id)) {
			self::deactivateCRM($crm_id);
			return $crm_obj->resetAPIkey();
		}

		return false;
	}

	public static function activateCRM ($crm_id) {
		return PL_Options::set('pl_activeCRM', $crm_id);
	}

	public static function deactivateCRM ($crm_id) {
		return PL_Options::delete('pl_activeCRM');
	}

	public static function getActiveCRMs () {
		return PL_Options::get('pl_activeCRM', null);
	}

	public static function isActiveCRM ($crm_id) {
		return PL_Options::get('pl_activeCRM', null) == $crm_id ? true : false;
	}


	// interface to crm libraries
	public static function callCRMLib ($method, $args = array()) {
		$crm_obj = self::getCRMInstance(self::getActiveCRMs()); // only a single CRM is supported currently
		if($crm_obj && method_exists($crm_obj, $method))
			return $crm_obj->$method($args);

		return null;
	}

	public static function getContactGridData ($args = array()) {
		$crm_id = empty($args['crm_id']) ? self::$primaryCRM : $args['crm_id'];
		$crm_obj = self::getCRMInstance($crm_id);

		$filters = array('limit' => $args["iDisplayLength"], 'offset' => $args["iDisplayStart"]);
		$field_meta = $crm_obj->contactFieldMeta();

		foreach ($field_meta as $field_key => $meta)
			if (isset($args[$field_key]))
				$filters[$field_key] = $args[$field_key];

		$grid_rows = array(); $total = 0;
		if($data = $crm_obj->getContacts($filters)) {

			if (isset($data["total"])) $total = $data["total"];

			if (is_array($data["contacts"])) foreach ($data["contacts"] as $index => $contact) {
				foreach ($field_meta as $field_key => $meta) {
					$val = empty($contact[$field_key]) ? "" : $contact[$field_key];
					if (!empty($meta["data_format"]))
						$val = $crm_obj->formatContactData($val, $meta["data_format"]);

					$grid_rows[$index][] = $val;
				}
			}
		}

		return array(
			"sEcho" => $args["sEcho"],
			"aaData" => $grid_rows,
			"iTotalRecords" => $total,
			"iTotalDisplayRecords" => $total
		);
	}

	public static function settingsView () {
		ob_start();	

		$crm_list = self::$availableCRMList;
		unset($crm_list[self::$primaryCRM]); // can't enable/disable primary CRM
		include("views/settings.php");

		return ob_get_clean();
	}

	public static function browseView ($crm_id = null) {
		ob_start();

		$crm_info = self::$availableCRMList[$crm_id ?: self::$primaryCRM];
		include("views/browse.php");

		return ob_get_clean();
	}

	public static function getPartial ($partial, $args = array()) {
		if(!file_exists($file_path = trailingslashit(dirname(__FILE__)) . "views/partials/{$partial}.php"))
			return '';

		extract($args);
		ob_start();

		include($file_path);

		return ob_get_clean();
	}
}
