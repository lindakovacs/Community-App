<?php


require_once('listing.php');
require_once('format.php');


class PL_Search_Result implements Iterator {

	public function __construct($data, PL_Attributes $attributes = null, PL_Attribute_Formats $formats = null) {
	}

	public function current () { return new PL_Listing(); }
	public function key () { return 0; }
	public function next () {}
	public function rewind () {}
	public function valid () { return false; }
}