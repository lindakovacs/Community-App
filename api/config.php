<?php

class PL_Config {

	public static function PL_API_LISTINGS () {
		global $PL_API_LISTINGS;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_API_LISTINGS, $args, $num_args);
	}

	public static function PL_API_CUST_ATTR () {
		global $PL_API_CUST_ATTR;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_API_CUST_ATTR, $args, $num_args);
	}

	public static function PL_API_USERS () {
		global $PL_API_USERS;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_API_USERS, $args, $num_args);
	}

	public static function PL_API_PEOPLE () {
		global $PL_API_PEOPLE;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_API_PEOPLE, $args, $num_args);
	}

	public static function PL_API_INTEGRATION () {
		global $PL_API_INTEGRATION;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_API_INTEGRATION, $args, $num_args);
	}

	public static function PL_API_WORDPRESS () {
		global $PL_API_WORDPRESS;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_API_WORDPRESS, $args, $num_args);
	}

	public static function PL_MY_LIST_FORM () {
		global $PL_MY_LIST_FORM;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_MY_LIST_FORM, $args, $num_args);
	}

	public static function PL_TP_GOOGLE_PLACES () {
		global $PL_TP_GOOGLE_PLACES;
		$args = func_get_args();
		$num_args = func_num_args();
		return self::config_finder($PL_TP_GOOGLE_PLACES, $args, $num_args);
	}

	public static function bundler ($config_function, $keys, $bundle) {
		$config_items = array();
		foreach ($bundle as $key) {
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $item) {
							$config_items[$k][$item] = call_user_func_array(array(__CLASS__, $config_function), array_merge($keys,(array)$k,(array)$item));
						}
					} else {
						$config_items[$k][$v] = call_user_func_array(array(__CLASS__, $config_function), array_merge($keys,(array)$k,(array)$v));
					}
				}
			} else {
				$config_items[$key] = call_user_func_array(array(__CLASS__, $config_function), array_merge($keys,(array)$key));
			}
		}
		return $config_items;
	}

	private static function config_finder($config, $args, $num_args) {
		switch ($num_args) {
			case '0':
				return $config;
				break;
			case '1':
				if (isset($config[$args[0]])) {
					return $config[$args[0]];
				}
				break;
			case '2':
				if (isset($config[$args[0]][$args[1]])) {
					return $config[$args[0]][$args[1]];
				}
				break;
			case '3':
				if (isset($config[$args[0]][$args[1]][$args[2]])) {
					return $config[$args[0]][$args[1]][$args[2]];
				}
				break;
			case '4':
				if (isset($config[$args[0]][$args[1]][$args[2]][$args[3]])) {
					return $config[$args[0]][$args[1]][$args[2]][$args[3]];
				}
				break;
		}

		return null;
	}
}


class PL_Validate {

	public static function attributes ($args, $defaults) {
		$merged_args = wp_parse_args($args, $defaults);
		foreach ($merged_args as $key => $value) {
			if( is_array($value) && isset($defaults[$key]) ) {
				$merged_args[$key] = wp_parse_args($value, $defaults[$key]);
			}
		}
		return $merged_args;
	}

	// build request, respect incoming args, populate defaults as passed via configs
	public static function request ($args, $config) {
		// error_log(var_export($args, true));
		foreach ($config as $arg => $value) {
			if( !isset($args[$arg]) && is_array($value) && isset($value['default']) && !empty($value['default'])) {
				$args[$arg] = $value['default'];
			}
		}
		// Needs to be refactored. Strips out empty values from long, nested, params
		foreach ($args as $arg => $value) {
			if ($value == '' || $value == 'false') {
				unset($args[$arg]);
			} elseif (is_array($value)) {
				foreach ($value as $k => $v) {
					if ($v == '' || $v == 'false') {
						unset($args[$arg][$k]);
					}
				}
			}
		}
		return $args;
	}
}
