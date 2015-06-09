<?php

require_once('php_curl.php');
require_once('connection.php');
require_once('listing.php');

$global_conn = new PL_HTTP_Connection("wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka", "PHP_Curl");
$global_conn->INCLUDE_DISABLED = true;

$global_prop = new PL_Placester_Listing();
$global_prop->set_listing_type("res_rental");
$global_prop->set_property_type("Luxury Condo");
$global_prop->set_address("1313 Washington Street");
$global_prop->set_unit("707");
$global_prop->set_locality("Boston");
$global_prop->set_region("MA");
$global_prop->set_county("Suffolk");
$global_prop->set_country("US");
$global_prop->listing_create($global_conn);

var_dump($global_prop);
