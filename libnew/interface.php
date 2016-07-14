<?php


class PLX_Data_Internal {
	static protected $listing_interface;
	static protected $attribute_interface;
	static protected $search_interface;
	static protected $parameter_interface;
	static protected $image_interface;
	static protected $provider_interface;
}


interface PLX_Attribute_Interface {
}


interface PLX_Listing_Interface {
	static function create($args);
	static function read($args);
	static function update($args);
	static function delete($args);
}


interface PLX_Parameter_Interface {
}


interface PLX_Search_Interface {
	static function listings($args);
	static function locations($args);
	static function aggregates($args);
}


interface PLX_Image_Interface {
	static function upload($args, $file_name, $file_mime_type, $file_tmpname);
	static function resize($args);
}


interface PLX_Provider_Interface {
}


class PLX_Data_Interface extends PLX_Data_Internal {
	static public function get_listing_interface() {}
	static protected function set_listing_interface() {}

	static public function get_attribute_interface() {}
	static protected function set_attribute_interface() {}

	static public function get_search_interface() {}
	static protected function set_search_interface() {}

	static public function get_parameter_interface() {}
	static protected function set_parameter_interface() {}

	static public function get_image_interface() {}
	static protected function set_image_interface() {}

	static public function get_provider_interface() {}
	static protected function set_provider_interface() {}
}


class PLX_Listings extends PLX_Data_Internal implements PLX_Listing_Interface {

	private static function this() {
		if(!isset(self::$listing_interface))
			self::$listing_interface = new self; // null implementation below

		return self::$listing_interface;
	}

	public static function create ($args = array()) {
		return self::this()->_create($args);
	}

	public static function read ($args = array()) {
		return self::this()->_read($args);
	}

	public static function update ($args = array()) {
		return self::this()->_update($args);
	}

	public static function delete ($args = array()) {
		return self::this()->_delete($args);
	}


	protected function _create ($args = array()) {
		return null;
	}

	protected function _read ($args = array()) {
		return null;
	}

	protected function _update ($args = array()) {
		return null;
	}

	protected function _delete ($args = array()) {
		return null;
	}
}


class PLX_Images extends PLX_Data_Internal implements PLX_Image_Interface {

	private static function this() {
		if(!isset(self::$image_interface))
			self::$image_interface = new self; // null implementation below

		return self::$image_interface;
	}

	public static function upload ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		return self::this()->_upload($args, $file_name, $file_mime_type, $file_tmpname);
	}

	public static function resize ($args = array()) {
		return self::this()->_resize($args);
	}


	protected function _upload ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		return null;
	}

	protected function _resize ($args = array()) {
		return null;
	}
}


class PLX_Search extends PLX_Data_Internal implements PLX_Search_Interface {

	private static function this() {
		if(!isset(self::$search_interface))
			self::$search_interface = new self; // null implementation below

		return self::$search_interface;
	}

	public static function listings($args = array()) {
		return self::this()->_listings($args);
	}

	public static function locations($args = array()) {
		return self::this()->_locations($args);
	}

	public static function aggregates($args = array()) {
		return self::this()->_aggregates($args);
	}


	protected function _listings($args = array()) {
		return null;
	}

	protected function _locations($args = array()) {
		return null;
	}

	protected function _aggregates($args = array()) {
		return null;
	}
}
