<?php

global $PL_API_SERVER;
global $PL_API_INTEGRATION;
$PL_API_INTEGRATION = array(
	'get' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/integration/requests",
			'type' => 'GET'
		),
		'args' => array(),
		'returns' => array(
			'id' => false,
			'mls_name' => false,
			'url' => false,
			'updated_at' => false,
			'created_at' => false,
			'completed_at' => false,
			'status' => false
		)
	),	
	'create' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/integration/requests",
			'type' => 'POST'
		),
		'args' => array(
			'mls_id' => array('type' => 'text', 'group' => 'basic', 'label' => 'MLS Name'),
			'office_name' => array('type' => 'text', 'group' => 'basic', 'label' => 'Office Name'),
			'feed_agent_id' => array('type' => 'text', 'group' => 'basic', 'label' => 'Agent ID')
		),
		'returns' => array(
			'id' => false
		)
	),
	'mls_list' => array(
		'request' => array(
			'url' => "$PL_API_SERVER/v2/integration/requests/mls",
			'type' => 'GET'
		),
		'args' => array(),
		'returns' => array()
	)	
);