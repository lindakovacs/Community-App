<?php 


interface PL_Listing_Interface {
	static function create($args);
	static function read($args);
	static function update($args);
	static function delete($args);

	static function get($args);
	static function locations($args);
	static function aggregates($args);
}


class PL_Local_Listing_Util {
	protected static function get_query_variable($array, $name, $default = null) {
		return isset($array[$name]) ? $array[$name] : $default;
	}

	protected static function get_attribute_type($name) {
		$name = array_pop(explode('.', $name));

		if(in_array($name, array('beds', 'baths', 'half_baths', 'sqft', 'price', 'deposit', 'hoa_fee')))
			return 'numeric';

		else if(in_array($name, array('lt_sz', 'bld_sz', 'floors', 'min_div', 'max_cont')))
			return 'numeric';

		else if(in_array($name, array('lst_dte', 'avail_on')))
			return 'date';

		else
			return 'char';
	}

	protected static function get_attribute_group($name) {
	}

	protected static function get_listing_type_map($compound_type) {
		static $compound_type_map;
		if(!isset($compound_type_map))
			$compound_type_map = array(
				'res_sale'    => array( null,      'residential', 'sale'  ),
				'res_rental'  => array( null,      'residential', 'rental'),
				'comm_sale'   => array( null,      'commercial',  'sale'  ),
				'comm_rental' => array( null,      'commercial',  'rental'),
				'vac_rental'  => array('vacation',  null,         'rental'),
				'park_rental' => array('parking',   null,         'rental')
			);

		if(isset($compound_type_map[$compound_type]))
			return $compound_type_map[$compound_type];

		return array($compound_type, null, null);
	}
}


class PL_SQL_Listing_Util extends PL_Local_Listing_Util {
	protected static function get_compare_from_match($match) {
		static $match_compare_map;
		if(!isset($match_compare_map))
			$match_compare_map = array(
				'eq' => '=', 'ne' => '!=', 'gt' => '>', 'ge' => '>=', 'lt' => '<', 'le' => '<=',
				'in' => 'in', 'nin' => 'not in', 'like' => 'rlike');

		if(isset($match_compare_map[$match]))
			return $match_compare_map[$match];

		return $match;
	}
}
