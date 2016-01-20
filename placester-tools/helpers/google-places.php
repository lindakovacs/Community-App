<?php 

class PL_Google_Places_Helper {

	public static function search ($request) {
		$response = PL_Google_Places::get($request);
		return $response;
	}

}