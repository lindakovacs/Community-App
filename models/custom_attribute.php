<?php

class PL_Custom_Attributes {

	private static $get_memo = null;

	public static function get($args = array()) {
		// If args array is empty, check for memoized response...
		if (empty($args) && !is_null(self::$get_memo)) {
			// error_log("Using memoized custom_attributes!!!");
			return self::$get_memo;
		}

		$config = PL_Config::PL_API_CUST_ATTR('get');
		$request = array_merge(array( "api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, $config['args']));
		$response = PL_HTTP::send_request($config['request']['url'], $request);

		$attributes = array();
		if ($response) {
			global $PL_API_LISTINGS; // config/api/listings.php
			$metadata = $PL_API_LISTINGS['get']['args']['metadata'];

			foreach ($response as $attribute) {
				if(is_array($attribute) && !$metadata[$attribute['key']]) // skip attributes that have already been explicitly defined
					$attributes[] = PL_Validate::attributes($attribute, $config['returns']);
		 	}
		}

		// Memoize response if args array is empty...
		if (empty($args)) {
			// error_log("Memoizing custom_attributes...");
			self::$get_memo = $attributes;
		}
		
		return $attributes;
	}

//end class
}