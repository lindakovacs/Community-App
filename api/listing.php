<?php


require_once(PLACESTER_PLUGIN_DIR . 'placester-local/placester-local.php');


class PL_Listing extends PLX_Data_Interface {
	static function create($args) {
		return PLX_Listings::create($args);
	}

	static function read($args) {
		return PLX_Listings::read($args);
	}

	static function update($args) {
		return PLX_Listings::update($args);
	}

	static function delete($args) {
		return PLX_Listings::delete($args);
	}

	static function temp_image($args, $file_name, $file_mime_type, $file_tmpname) {
		return PLX_Listings::image($args, $file_name, $file_mime_type, $file_tmpname);
	}

	static function get($args) {
		return PLX_Search::listings($args);
	}

	static function locations($args) {
		return PLX_Search::locations($args);
	}

	static function aggregates($args) {
		return PLX_Search::aggregates($args);
	}
};
