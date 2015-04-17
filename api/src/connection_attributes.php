<?php


require_once('connection.php');
require_once('standard_attributes.php');


class PL_Connection_Attributes extends PL_Standard_Attributes {
	protected $connection;

	public function __construct(PL_API_Connection $connection) {
		parent::__construct();

		$this->connection = $connection;
		//$this->attributes = $this->remove_unpopulated_attributes($this->attributes);
		$this->attributes = $this->add_custom_attributes($this->attributes);
	}

	protected function remove_unpopulated_attributes($attributes) {
		foreach($attributes as $attribute)
			if(in_array(array_shift(explode('.', $attribute->field_name)), array('cur_data', 'uncur_data'))) {

				if($attribute->type == PL_DATE_TIME) {
					$query = $attribute->query_name . '=';
					$query &= '&' . str_replace($attribute->name, $attribute->name . '_match', $attribute->query_name) . '=ne';
				}
				else
					$query = str_replace($attribute->name, 'min_' . $attribute->name, $attribute->query_name) . '=';

				if($result = $this->connection->SEARCH_LISTINGS($query . '&limit=1')) {
					if($result->total == 0)
						unset($attributes[$attribute->name]);
				}
			}

		return $attributes;
	}

	protected function add_custom_attributes($attributes) {
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
