<?php


require_once(BUILDER_DIR . 'api/listing.php');
require_once('display_format.php');
require_once('display_attribute.php');


class PL_Display_Listing extends PL_Listing {
	protected $display;

	public function __get($name) { return $this->get_display_value($name); }
	public function get_display_value($name) {
		if(isset($this->display[$name]))
			return $this->display[$name];

		if($attribute = $this->attributes->get_attribute($name)) {
			if(is_a($attribute, 'PL_Display_Attribute') && $attribute->content) // a calculated display field
				$value = do_shortcode($attribute->content);
			else
				$value = $this->get_value($attribute->name);

			if(is_a($attribute, 'PL_Display_Attribute') && $attribute->format)
				$value = $attribute->format->format($value);

			else switch($attribute->type) { // fallback formats
				case PL_BOOLEAN:
					static $default_boolean_format;
					if(!$default_boolean_format)
						$default_boolean_format = new PL_Boolean_Format();
					$value = $default_boolean_format->format($value);
					break;

				case PL_NUMERIC:
					static $default_numeric_format;
					if(!$default_numeric_format)
						$default_numeric_format = new PL_Numeric_Format(false);
					$value = $default_numeric_format->format($value);
					break;

				case PL_CURRENCY:
					static $default_currency_format;
					if(!$default_currency_format)
						$default_currency_format = new PL_Currency_Format();
					$value = $default_currency_format->format($value);
					break;

				case PL_DATE_TIME:
					static $default_datetime_format;
					if(!$default_datetime_format)
						$default_datetime_format = new PL_Datetime_Format();
					$value = $default_datetime_format->format($value);
					break;

				case PL_TEXT_VALUE:
					switch($attribute->name) {
						case 'property_type':
							static $property_type_format;
							if(!$property_type_format)
								$property_type_format = new PL_Property_Type_Mapping();
							$value = $property_type_format->format($value);
							break;

						case 'listing_type':
							static $listing_type_format;
							if(!$listing_type_format)
								$listing_type_format = new PL_Listing_Type_Mapping();
							$value = $listing_type_format->format($value);
							break;

						case 'style':
							static $style_format;
							if(!$style_format)
								$style_format = new PL_Style_Mapping();
							$value = $style_format->format($value);
							break;

						default:
							static $default_text_format;
							if(!$default_text_format)
								$default_text_format = new PL_Text_Format();
							$value = $default_text_format->format($value);
							break;
					}
					break;
			}
		}
		else
			$value = null;

		return $this->display[$name] = $value;
	}
}