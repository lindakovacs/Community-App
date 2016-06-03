<?php


require_once('interface.php');


class PLX_Listings extends PLX_Data_Internal {

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

	public static function image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		return self::this()->_image($args, $file_name, $file_mime_type, $file_tmpname);
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

	protected function _image ($args = array(), $file_name, $file_mime_type, $file_tmpname) {
		return null;
	}
}


class PLX_Search extends PLX_Data_Internal {

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