<?php


require_once('connection.php');
require_once('attribute.php');


class PL_Connection_Attributes extends PL_Attributes {
	protected $custom_attributes;
	protected $connection;

	public function __construct(PL_API_Connection $connection) {
		parent::__construct();

		$this->connection = $connection;
		$this->custom_attributes = $this->read_custom_attributes();
	}

	public function add_attribute_by_name($name) {
		if($result = $this->custom_attributes[$name])
			return $this->attributes[$name] = $result;

		return parent::add_attribute_by_name($name);
	}

	public function add_attribute_by_field($field) {
		return parent::add_attribute_by_field($field);
	}

	public function get_custom_attributes() {
		return $this->custom_attributes;
	}

	protected function read_custom_attributes() {
		$attributes = array();
		if($result = $this->connection->ATTRIBUTES()) {
			foreach($result as $item) {

				$field = 'uncur_data.' . $item->key;
				switch($item->attr_type) {
					case 0: $type = PL_NUMERIC; break;
					case 1: $type = PL_NUMERIC; break;
					case 2: $type = PL_TEXT_VALUE; break;
					case 3: $type = PL_SHORT_TEXT; break;
					case 4: $type = PL_DATE_TIME; break;
					case 5: $type = PL_SHORT_TEXT; break;
					case 6: $type = PL_BOOLEAN; break;
					case 7: $type = PL_CURRENCY; break;
					case 8: $type = PL_SHORT_TEXT; break;
					case 9: $type = PL_SHORT_TEXT; break;
					default: $type = PL_SHORT_TEXT; break;
				}

				$attributes[$item->key] = new PL_Attribute($item->key, $type, $field, $item->cat, $item->name);
			}
		}
		return $attributes;
	}
}
