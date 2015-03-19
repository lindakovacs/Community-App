<?php

require_once('../src/php_curl.php');
require_once('../src/connection.php');
require_once('../src/search.php');

$global_conn = new PDX_API_Connection("wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka", "PHP_Curl");
$global_conn->INCLUDE_DISABLED = true;

echo "<pre>\n";
echo "All listings\n";
$listings = $global_conn->GET_LISTINGS();
if($listings) {
	foreach($listings->listings as $listing) {
		echo $listing->id . " " . $listing->location->address . " " . $listing->location->locality . "\n";
	}

	echo "Showing $listings->count of $listings->total listings\n\n";
}

echo "Somerville listings\n";
$filter = new PL_Property_Filter();
$filter->set_locality("Somerville");
$listings = $filter->search($global_conn);
if($listings) {
	foreach($listings->listings as $listing) {
		echo $listing->id . " " . $listing->location->address . " " . $listing->location->locality . "\n";
	}

	echo "Showing $listings->count of $listings->total listings\n\n";
}
