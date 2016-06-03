<?php


class PLX_Data_Internal {
	static protected $listing_interface;
	static protected $attribute_interface;
	static protected $search_interface;
	static protected $parameter_interface;
	static protected $provider_interface;
}


interface PLX_Listing_Interface {
	static function create($args);
	static function read($args);
	static function update($args);
	static function delete($args);
	static function image($args);
}


interface PLX_Search_Interface {
	static function listings($args);
	static function locations($args);
	static function aggregates($args);
}


class PLX_Data_Interface extends PLX_Data_Internal {
	static protected function get_listing_interface() {}
	static protected function set_listing_interface() {}

	static protected function get_attribute_interface() {}
	static protected function set_attribute_interface() {}

	static protected function get_search_interface() {}
	static protected function set_search_interface() {}

	static protected function get_parameter_interface() {}
	static protected function set_parameter_interface() {}

	static protected function set_provider_interface() {}
	static protected function get_provider_interface() {}
}
