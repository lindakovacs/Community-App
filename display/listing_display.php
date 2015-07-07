<?php


require_once('../api/listing.php');
require_once('format.php');


class PL_Listing_Display extends PL_Listing {
	protected $formats;
	protected $display;

	public function __construct($data, PL_Attributes $attributes = null, PL_Attribute_Formats $formats = null) {
		parent::__construct($data, $attributes);
		$this->formats = $formats ?: new PL_Standard_Formats();
		$this->display = array();
	}

	public function __get($name) {
		if(!(($attribute = $this->attributes->get_attribute($name)) && $attribute->access_name))
			return null;

		if(isset($this->display[$name]))
			return $this->display[$name];

		if(!is_null($value = $this->get_value($this->listing, $attribute->access_name))) {
			$this->display[$name] = $value = $this->format_value($attribute, $value);
		}

		return $value;
	}

	protected function format_value(PL_Attribute $attribute, $value) {
		if($format = $this->formats->get_format($attribute->name, $attribute->type))
			return $format->format($value);

		// fallback formats
		switch($attribute->type) {
			case PL_BOOLEAN:
				return $value ? 'Yes' : 'No';

			case PL_NUMERIC:
				return $value;

			case PL_CURRENCY:
				return '$' . number_format($value);

			case PL_TEXT_VALUE:
				return ucwords(implode(' ', explode('_', $value)));

			case PL_SHORT_TEXT:
				return $value;

			case PL_LONG_TEXT:
				return $value;

			case PL_DATE_TIME:
				return substr($value, 0, 10);
		}
		return $value;
	}
}