<?php

require_once('php_curl.php');
require_once('connection.php');
require_once('listing.php');

$global_conn = new PDX_API_Connection("wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka", "PHP_Curl");
$global_conn->INCLUDE_DISABLED = true;

$user = $global_conn->GET_WHOAMI();
var_dump($user);

$locations = $global_conn->GET_LOCATIONS();
var_dump($locations);

$attributes = $global_conn->GET_ATTRIBUTES();
var_dump($attributes);

$listings = $global_conn->GET_LISTINGS();
var_dump($listings);
