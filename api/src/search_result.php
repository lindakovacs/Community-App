<?php


require_once('connection.php');


class PL_Search_Result implements Iterator {

	public function __construct($data, PL_API_Connection $connection = null) {
	}

	public function current () { return new PL_Listing(); }
	public function key () { return 0; }
	public function next () {}
	public function rewind () {}
	public function valid () { return false; }
}