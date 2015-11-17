<?php


class PL_Display_Format {
	public function format_value($value) {
		return $value;
	}
}


class PL_Boolean_Format extends PL_Display_Format {
	protected $t_string;
	protected $f_string;

	public function __construct($t_string = 'Yes', $f_string = 'No') {
		$this->t_string = $t_string;
		$this->f_string = $f_string;
	}

	public function format($value) {
		return $value ? $this->t_string : $this->f_string;
	}
}


class PL_Numeric_Format extends PL_Display_Format {
	protected $locale;
	protected $places;
	protected $point;
	protected $comma;

	public function __construct($comma = null, $point = null, $places = null) {
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
	protected $symbol;
	protected $before;

	public function __construct($symbol = null, $before = null, $comma = null, $point = null, $places = 0) {
		parent::__construct($comma, $point, $places);

		$this->symbol =
			($symbol === null ? $this->locale['currency_symbol'] :
			($symbol === false ? '' : $symbol));

		$this->before = $before === null ? $this->locale['p_cs_precedes'] : $before;
	}

	public function format($value) {
		return $this->before ? $this->symbol . parent::format($value) : parent::format($value) . $this->symbol;
	}
}


class PL_Text_Format extends PL_Display_Format {
	public function format($value) {
		$value = ucwords(implode(' ', explode('_', $value)));
		return $value;
	}
}


class PL_Datetime_Format extends PL_Display_Format {
	public function format($value) {
		$value = substr($value, 0, 10);
		return $value;
	}
}


class PL_Text_Regex extends PL_Text_Format {
	protected $pattern;
	protected $replacement;

	public function __construct($pattern = null, $replacement = null) {
		$this->pattern = $pattern;
		$this->replacement = $replacement;
	}

	public function format($value) {
		if($this->pattern && $this->replacement)
			return preg_replace($this->pattern, $this->replacement, $value);

		return $value;
	}
}


class PL_Text_Mapping extends PL_Display_Format {
	protected $table;

	public function __construct($table) {
		$this->table = $table;
	}

	public function format($value) {
		if($this->table && isset($this->table[$value]))
			return $this->table[$value];

		return $value;
	}
}


class PL_Listing_Type_Mapping extends PL_Text_Mapping {
	public function __construct() {
		parent::__construct(array(
			'res_sale' => 'Residential Sale',
			'comm_sale' => 'Commercial Sale',
			'res_rental' => 'Residential Rental',
			'comm_rental' => 'Commercial Rental',
			'sublet' => 'Sublet',
			'park_rental' => 'Parking',
			'vac_rental' => 'Vacation Rental'));
	}
}


class PL_Property_Type_Mapping extends PL_Text_Mapping {
	public function __construct() {
		parent::__construct(array(
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
	}
}


class PL_Style_Mapping extends PL_Text_Mapping {
	public function __construct() {
		parent::__construct(array(
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
	}
}
