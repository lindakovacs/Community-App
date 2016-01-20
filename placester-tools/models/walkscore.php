<?php

class PL_Walkscore {

	public static function get_score ($args = array()) {
		$score = array();

		// Parse args + merge with defaults...
		$parsed_args = wp_parse_args($args, array('lat' => false, 'lng' => false, 'address' => false, 'ws_api_key' => false));
		extract($parsed_args);

		// Only proceed if all args exist...
		if ($lat && $lng && $address && $ws_api_key) {
			// Check for cached value...
			$cache = new PL_Cache('walkscore');
			if ($result = $cache->get($parsed_args)) {
				$score = $result;
			}
			else {
				$response = wp_remote_get('http://api.walkscore.com/score?format=json&address=' . urlencode($address) .'&lat=' . $lat . '&lon=' . $lng . '&wsapikey=' . $ws_api_key, array('timeout' => 10));
				if (is_array($response) && isset($response['body']) ) {
					$score = json_decode($response['body'], true);
					$cache->save($score);
				}
			}
		}

		return $score;
	}

}