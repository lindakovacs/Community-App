<?php


require_once('attribute.php');


class PL_Attribute_Format {
	public $name;
	public $type;

	protected function __construct($name, $type) {
		$this->name = $name;
		$this->type = $type;
	}

	public function format($value) {
		return $value;
	}
}


class PL_Boolean_Format extends PL_Attribute_Format {
	public $t_string;
	public $f_string;

	public function __construct($name, $t_string = 'Yes', $f_string = 'No') {
		parent::__construct($name, PL_BOOLEAN);
		$this->t_string = $t_string;
		$this->f_string = $f_string;
	}

	public function format($value) {
		return $value ? $this->t_string : $this->f_string;
	}
}


class PL_Numeric_Format extends PL_Attribute_Format {
	public $locale;
	public $places;
	public $point;
	public $comma;

	public function __construct($name, $comma = null, $point = null, $places = null) {
		parent::__construct($name, PL_NUMERIC);
		setlocale(LC_MONETARY, get_locale());

		$this->locale = localeconv();
		$this->places = $point === false ? 0 : $places;

		$this->point =
			($point === null ? $this->locale['mon_decimal_point'] :
			($point === false ? '' : $point));

		$this->comma =
			($comma === null ? $this->locale['mon_thousands_sep'] :
			($comma === false ? '' : $comma));
	}

	public function format($value) {
		if($this->places === null && ($fraction = strrchr($value, '.')))
			$places = strlen($fraction) - 1;
		else
			$places = $this->places;

		return number_format($value, $places, $this->point, $this->comma);
	}
}


class PL_Currency_Format extends PL_Numeric_Format {
	public $symbol;
	public $before;

	public function __construct($name, $symbol = null, $before = null, $comma = null, $point = null, $places = 0) {
		parent::__construct($name, $comma, $point, $places);
		$this->type = PL_CURRENCY;

		$this->symbol =
			($symbol === null ? $this->locale['currency_symbol'] :
			($symbol === false ? '' : $symbol));

		$this->before = $before === null ? $this->locale['p_cs_precedes'] : $before;
	}

	public function format($value) {
		return $this->before ? $this->symbol . parent::format($value) : parent::format($value) . $this->symbol;
	}
}


class PL_Text_Format extends PL_Attribute_Format {
	public $pattern;
	public $replacement;

	protected function __construct($name, $type, $pattern = null, $replacement = null) {
		parent::__construct($name, $type);
		$this->pattern = $pattern;
		$this->replacement = $replacement;
	}

	public function format($value) {
		if($this->pattern && $this->replacement)
			return preg_replace($this->pattern, $this->replacement, $value);

		return $value;
	}
}


class PL_Text_Value_Format extends PL_Text_Format {
	public function __construct($name, $pattern = null, $replacement = null) {
		parent::__construct($name, PL_TEXT_VALUE, $pattern, $replacement);
	}
}


class PL_Short_Text_Format extends PL_Text_Format {
	public function __construct($name, $pattern = null, $replacement = null) {
		parent::__construct($name, PL_SHORT_TEXT, $pattern, $replacement);
	}
}


class PL_Long_Text_Format extends PL_Text_Format {
	public function __construct($name, $pattern = null, $replacement = null) {
		parent::__construct($name, PL_LONG_TEXT, $pattern, $replacement);
	}
}


class PL_Text_Value_Mapping extends PL_Attribute_Format {
	public $table;

	public function __construct($name, $table) {
		parent::__construct($name, PL_TEXT_VALUE);
		$this->table = $table;
	}

	public function format($value) {
		if($this->table && isset($this->table[$value]))
			return $this->table[$value];

		return $value;
	}
}


class PL_Attribute_Formats {
	protected $formats;

	public function get_format($name, $type = null) {
		return $this->formats[$name] ?: ($type ? $this->formats[$type] : null);
	}

	public function get_formats() {
		return $this->formats;
	}
}


class PL_Standard_Formats extends PL_Attribute_Formats {
	static protected $standard_formats;

	public function __construct() {
		if(!self::$standard_formats)
			self::$standard_formats = self::construct_standard_formats();

		$this->formats = self::$standard_formats;
	}

	static protected function construct_standard_formats() {
		$formats = array();

		$formats[PL_BOOLEAN] = new PL_Boolean_Format("Boolean Default");
		$formats[PL_NUMERIC] = new PL_Numeric_Format("Numeric Default");
		$formats[PL_CURRENCY] = new PL_Currency_Format("Currency Default");
		$formats[PL_TEXT_VALUE] = new PL_Short_Text_Format("Text Value Default");
		$formats[PL_SHORT_TEXT] = new PL_Short_Text_Format("Short Text Default");
		$formats[PL_LONG_TEXT] = new PL_Long_Text_Format("Long Text Default");

		$formats['listing_type'] = new PL_Text_Value_Mapping('listing_type', array(
			'res_sale' => 'Residential Sale',
			'comm_sale' => 'Commercial Sale',
			'res_rental' => 'Residential Rental',
			'comm_rental' => 'Commercial Rental',
			'sublet' => 'Sublet',
			'park_rental' => 'Parking',
			'vac_rental' => 'Vacation Rental'));

		$formats['property_type'] = new PL_Text_Value_Mapping('property_type', array(
			'duplex' => 'Duplex',
			'penthouse' => 'Penthouse',
			'apartment' => 'Apartment',
			'condo' => 'Condominium',
			'coop' => 'Cooperative',
			'fam_home' => 'Single Family Home',
			'manuf' => 'Manufactured Home',
			'multi_fam' => 'Multi-Family Home',
			'tic' => 'Tenancy in Common',
			'townhouse' => 'Townhouse',
			'vacant' => 'Vacant',
			'ret_anchor' => 'Retail - Anchor',
			'ret_comm' => 'Retail - Community Center',
			'ret_free_stnd' => 'Retail - Free Standing Building',
			'ret_nghbr' => 'Retail - Neighborhood Center',
			'ret_other' => 'Retail - Other',
			'ret_pad' => 'Retail - Pad Site',
			'ret_reg' => 'Retail - Regional Center / Mall',
			'ret_resta' => 'Retail - Restaurant',
			'ret_special' => 'Retail - Speciality Center',
			'ret_strip' => 'Retail - Strip Mall',
			'ret_strt_ret' => 'Retail - Street Retail',
			'ret_sup_reg' => 'Retail - Super Regional Center',
			'ret_theme' => 'Retail - Theme / Festival Center',
			'ret_veh_rel' => 'Retail - Vehicle Related',
			'lan_comm' => 'Land - Commercial / Other',
			'lan_indust' => 'Land - Industrial',
			'lan_office' => 'Land - Office',
			'lan_resid' => 'Land - Residential',
			'lan_ret' => 'Land - Retail',
			'lan_ret_pad' => 'Land - Retail Pad Site',
			'off_med' => 'Office - Medical',
			'off_inst_gov' => 'Office - Institutional / Govermental',
			'off_rd' => 'Office - Research and Development',
			'off_gen' => 'Office - General',
			'off_loft' => 'Office - Loft',
			'ret_outlet' => 'Retail - Outlet',
			'ind_dist_warh' => 'Industrial - Distribution Warehouse',
			'ind_flex' => 'Industrial - Flex Space',
			'ind_manuf' => 'Industrial - Manufacturing',
			'ind_off_shw' => 'Industrial - Office Showroom',
			'ind_ref_str' => 'Industrial - Refigerated / Cold Storage',
			'ind_term_trans' => 'Industrial - Truck Teminal / Hub / Transit',
			'ind_warh' => 'Industrial - Warehouse'));

		$formats['style'] = new PL_Text_Value_Mapping('style', array(
			'colonial' => 'Colonial',
			'garrison' => 'Garrison',
			'cape' => 'Cape Cod',
			'contemp' => 'Contemporary',
			'ranch' => 'Ranch',
			'rai_ranch' => 'Raised Ranch',
			'split_ent' => 'Split Entry',
			'victor' => 'Victorian',
			'tudor' => 'Tudor',
			'gamb_dutc' => 'Gambrel/Dutch',
			'antiq' => 'Antique',
			'farmh' => 'Farmhouse',
			'saltb' => 'Saltbox',
			'cott' => 'Cottage',
			'bungal' => 'Bungalow',
			'mult_lvl' => 'Multi-level',
			'fnt_bk_splt' => 'Front to Back Split',
			'loft_splt' => 'Lofted Split',
			'greek_rev' => 'Greek Revival'));

		return $formats;
	}
}
