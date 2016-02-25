<?php

class PL_Custom_Attributes {

	private static $attributes = null;
	private static $translations = null;

	public static function get($args = array()) {
		if (empty($args) && !is_null(self::$attributes))
			return self::$attributes;

		$config = PL_Config::PL_API_CUST_ATTR('get');
		$request = array_merge(array( "api_key" => PL_Option_Helper::api_key()), PL_Validate::request($args, $config['args']));
		$response = PL_HTTP::send_request($config['request']['url'], $request);

		$attributes = array();
		if ($response) {
			global $PL_API_LISTINGS; // config/api/listings.php
			$metadata = $PL_API_LISTINGS['get']['args']['metadata'];

			if(is_array($response)) foreach ($response as $attribute) {
				if(is_array($attribute) && !$metadata[$attribute['key']]) // skip attributes that have already been explicitly defined
					$attributes[] = PL_Validate::attributes($attribute, $config['returns']);
		 	}
		}

		if (empty($args))
			self::$attributes = $attributes;

		return $attributes;
	}

	public static function get_translations () {
		if (!is_null(self::$translations))
			return self::$translations;

		$api_dictionary = self::get();

		$dictionary = array();
		foreach ($api_dictionary as $item) {
			$dictionary[$item['key']] = $item['name'];
		}

		self::$translations = $dictionary;

		return $dictionary;
	}
}


// v2 ruby core API
global $PL_API_SERVER;
global $PL_API_CUST_ATTR;

$PL_API_CUST_ATTR = array(
	'get' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/custom/attributes",
			'type' => 'GET'
		),
		'args' => array(
			'cat' => array('type' => 'text'),
			'name' => array('type' => 'text'),
			'attr_type' => array(
				'type' => 'select',
				'options' => array(
					'0' => 'int',
					'1' => 'text',
					'2' => 'text',
					'3' => 'textarea',
					'4' => 'date',
					'5' => 'date',
					'6' => 'checkbox',
					'7' => 'text'
				)
			),
			'attr_class' => array(
				'type' => 'select',
				'options' => array(
					'0' => 'deal',
					'1' => 'people',
					'2' => 'listing',
					'3' => 'building'
				)
			),
		),
		'returns' => array(
			'id' => false,
			'cat' => false,
			'name' => false,
			'attr_type' => false,
			'attr_class' => false,
			'always_show' => false
		)
	)
);


// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
class PL_Custom_Attribute_Helper {
	public static function get_translations () {
		return PL_Custom_Attributes::get_translations();
	}
}
