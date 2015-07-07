<?php

require_once('../api/connection.php');

$global_conn = new PL_API_Connection("wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka", "PHP_Curl");

echo "All listings\n";
$listings = $global_conn->search_listings();
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
