<?php


require_once(PLACESTER_PLUGIN_DIR . 'libnew/interface.php');
require_once('attributes.php');
require_once('listings.php');


class PLX_Legacy_Interface extends PLX_Data_Interface {
	public static function init() {
		self::$attribute_interface = new PLX_Legacy_Attributes();
		self::$parameter_interface = new PLX_Legacy_Parameters();
		self::$listing_interface = new PLX_Legacy_Listings();
		self::$search_interface = new PLX_Legacy_Search();
	}

	public static function map_listing_request($request) {
		$mapped = array();

		foreach($request as $name => $value) {
			if($name == 'listing_type')
				$mapped['compound_type'] = $value;
			else if($name == 'property_type')
				$mapped['metadata']['prop_type'] = $value;
			else if($name == 'status')
				$mapped['metadata']['status'] = $value;

			else if($name == 'latitude')
				$mapped['location']['coord_latitude'] = $value;
			else if($name == 'longitude')
				$mapped['location']['coord_longitude'] = $value;

			else if($group = self::get_attribute_group($name)) {
				if($group == 'location')
					$mapped['location'][$name] = $value;
				else if(in_array($group, array('cur_data', 'uncur_data')))
					$mapped['metadata'][$name] = $value;
			}
		}

		$mapped['images'] = $request['images'];
		return $mapped;
	}

	public static function map_listing_response($response) {
		$mapped = array('_legacy' => $response);
		$mapped['id'] = $response['id'];

		$mapped['listing_type'] = $response['compound_type'];
		$mapped['property_type'] = $response['cur_data']['prop_type'];
		$mapped['zoning_type'] = $response['zoning_types'][0];
		$mapped['purchase_type'] = $response['purchase_types'][0];

		$mapped['created_at'] = $response['created_at'];
		$mapped['updated_at'] = $response['updated_at'];
		$mapped['status'] = $response['cur_data']['status'];
		$mapped['list_date'] = $response['cur_data']['lst_dte'];
		$mapped['days_on'] = $response['cur_data']['dom'];

		$mapped['import_id'] = $response['import_id'];
		$mapped['provider_id'] = $response['provider_id'];

		$mapped['latitude'] = $response['location']['coords'][0];
		$mapped['longitude'] = $response['location']['coords'][1];

		unset($response['cur_data']['prop_type']);
		unset($response['cur_data']['status']);
		unset($response['cur_data']['lst_dte']);
		unset($response['cur_data']['dom']);
		unset($response['location']['coords']);

		$mapped = array_merge($mapped,
			(array) $response['rets'],
			(array) $response['location'],
			(array) $response['cur_data'],
			(array) $response['uncur_data']);

		$mapped['images'] = $response['images'];
		return $mapped;
	}

	public static function map_search_request($request) {
		if(isset($request['_blueprint']))
			return $request['_blueprint'];

		$mapped = array('address_mode' => 'exact');
		foreach($request as $name => $value) {
			if($value === '') continue;

			if($name == 'id')
				$mapped['listing_ids'] = (array) $value;

			else if($name == 'listing_type')
				$mapped['compound_type'] = $value;
			else if($name == 'zoning_type')
				$mapped['zoning_types'] = (array) $value;
			else if($name == 'purchase_type')
				$mapped['purchase_types'] = (array) $value;

			else if($name == 'property_type')
				$mapped['metadata']['prop_type'] = $value;
			else if($name == 'property_type_match')
				$mapped['metadata']['prop_type_match'] = $value;

			else if($name == 'status' || $name == 'status_match')
				$mapped['metadata'][$name] = $value;

			else if($name == 'list_date')
				$mapped['metadata']['lst_dte'] = $value;
			else if($name == 'min_list_date')
				$mapped['metadata']['min_lst_dte'] = $value;
			else if($name == 'max_list_date')
				$mapped['metadata']['max_lst_dte'] = $value;

			else if($name == 'days_on')
				$mapped['metadata']['dom'] = $value;
			else if($name == 'min_days_on')
				$mapped['metadata']['min_dom'] = $value;
			else if($name == 'max_days_on')
				$mapped['metadata']['max_dom'] = $value;

			else if($name == 'min_latitude' || $name == 'max_latitude')
				$mapped['box'][$name] = $value;
			else if($name == 'min_longitude' || $name == 'max_longitude')
				$mapped['box'][$name] = $value;

			else if($name == 'sort_by') {
				if($value == 'listing_type')
					$mapped['sort_by'] = 'compound_type';
				else if($value == 'property_type')
					$mapped['sort_by'] = 'cur_data.prop_type';
				else if($value == 'status')
					$mapped['sort_by'] = 'cur_data.status';
				else if($value == 'list_date')
					$mapped['sort_by'] = 'cur_data.lst_dte';
				else if($value == 'days_on')
					$mapped['sort_by'] = 'cur_data.dom';
				else if($value == 'images')
					$mapped['sort_by'] = 'total_images';

				else if($group = self::get_attribute_group($value))
					$mapped['sort_by'] = $group . '.' . $value;
				else
					$mapped['sort_by'] = $value;
			}

			else if($group = self::get_parameter_group($name)) {
				if(in_array($group, array('cur_data', 'uncur_data')))
					$mapped['metadata'][$name] = $value;
				else
					$mapped[$group][$name] = $value;
			}
			else
				$mapped[$name] = $value;
		}

		return $mapped;
	}

	public static function map_search_response($response) {
		if($response['listings']) {
			foreach($response['listings'] as $id => &$listing)
				$listing = self::map_listing_response($listing);
		}
		else {
			$response['total'] = $response['count'] = 0;
			$response['listings'] = array();
		}

		return $response;
	}

	public static function map_aggregate_request($request) {
		$keys = $request['keys'];
		$request = self::map_search_request($request);

		$mapped = array();
		foreach($keys as $key) {
			if($key == 'listing_type')
				$mapped[] = 'compound_type';
			else if($key == 'property_type')
				$mapped[] = 'cur_data.prop_type';
			else if($key == 'zoning_type')
				$mapped[] = 'zoning_types';
			else if($key == 'purchase_type')
				$mapped[] = 'purchase_types';
			else if($key == 'status')
				$mapped[] = 'cur_data.status';

			else if($group = self::get_attribute_group($key))
				$mapped[] = $group . '.' . $key;
		}

		$request['keys'] = $mapped;
		return $request;
	}

	public static function map_aggregate_response($response) {
		$mapped = array();

		foreach($response as $key => $values) {
			if($key == 'compound_type')
				$mapped['listing_type'] = $values;
			else if($key == 'cur_data.prop_type')
				$mapped['property_type'] = $values;
			else if($key == 'zoning_types')
				$mapped['zoning_type'] = $values;
			else if($key == 'purchase_types')
				$mapped['purchase_type'] = $values;
			else if($key == 'cur_data.status')
				$mapped['status'] = $values;

			else if(substr($key, 0, 9) == 'location.')
				$mapped[substr($key, 9)] = $values;

			else if(substr($key, 0, 9) == 'cur_data.')
				$mapped[substr($key, 9)] = $values;

			else if(substr($key, 0, 11) == 'uncur_data.')
				$mapped[substr($key, 11)] = $values;
		}

		return $mapped;
	}

	public static function get_attribute_group($attribute) {
		if($attribute = PLX_Attributes::get($attribute)) {
			if($attribute['group'] == 'Provider')
				return 'rets';
			else if($attribute['group'] == 'Location')
				return 'location';
			else if($attribute['group'] != 'Listing')
				return 'cur_data';
		}

		return null;
	}

	public static function get_parameter_group($parameter) {
		if(substr($parameter, strlen($parameter) - 6) == '_match')
			$parameter = substr($parameter, 0, strlen($parameter) - 6);
		else if(substr($parameter, 0, 4) == 'min_')
			$parameter = substr($parameter, 4);
		else if(substr($parameter, 0, 4) == 'max_')
			$parameter = substr($parameter, 4);

		return self::get_attribute_group($parameter);
	}
}


PLX_Legacy_Interface::init();
