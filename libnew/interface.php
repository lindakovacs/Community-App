<?php


class PLX_Data_Internal {
	static protected $listing_interface;
	static protected $attribute_interface;
	static protected $search_interface;
	static protected $terms_interface;
	static protected $provider_interface;
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
