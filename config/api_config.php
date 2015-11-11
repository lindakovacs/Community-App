<?php


require_once(BUILDER_DIR . 'api/connection.php');


class PL_API_Config {
	protected $api_connection;
	protected $config_storage;

	protected $saved_attributes;
	protected $added_attributes;

	protected $attribute_values;
}
