<?php

global $PL_API_SERVER;
global $PL_API_WORDPRESS;
$PL_API_WORDPRESS = array(
	'set' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/wordpress/filters/",
			'type' => 'POST'
		),
		'args' => array(
			'url' => ''
		),
		'returns' => array()
	),
	'delete' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/wordpress/filters/",
			'type' => 'delete'
		),
		'args' => array(
			'url' => ''
		),
		'returns' => array()
	)
);