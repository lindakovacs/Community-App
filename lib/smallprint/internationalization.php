<?php

class PLS_Internationalization {

	public static function get_currency_symbol ( ) {
		$current_symbol = pls_get_option('pls-currency-symbol');
		if (! $current_symbol) {
			return '$';
		}
		return $current_symbol;
	}

}

function pls_get_currency_symbol () { return PLS_Internationalization::get_currency_symbol(); }