<?php

class PL_Dragonfly {

	const HOST = 'd2frgvzmtkrf4d.cloudfront.net';

	public static function resize($args) {
		extract(wp_parse_args(parse_url($args['old_image']), array('query' => '') ));

		if (!defined('PLACESTER_DF_SECRET')) {
			return false;
		}
		//finds the extension, "jpeg" in this case
		$pathinfo = pathinfo($path);
		$ext = $pathinfo['extension'];
		$size = $args['resize']['w'] . 'x' . $args['resize']['h'] . ($args['nocrop'] ? '!' : '#');
		$action = 'thumb';
		// $action = 'resize';
		// $action = 'crop';

		//corrects image path to remove starting "/" included in $path
		$path = ltrim($path, '/');

		$request_tabs_newlines = "f\t" . $path . "\np" . "\t". $action . "\t". $size . "\ne" . "\t" . $ext;
		$request_clean = 'f' . $path . 'p' . $action . $size . 'e' . $ext;
		$job = base64_encode($request_tabs_newlines);
		$secret = substr(sha1($request_clean . PLACESTER_DF_SECRET), 0, 16);
		$new_image = $scheme . '://' . self::HOST . '/' . $secret . '/' . rtrim($job, '=') . '.' . $ext . '?' . $query;

		return $new_image ? $new_image : false;
	}

}