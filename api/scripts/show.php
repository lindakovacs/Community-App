<?php

require_once('php_curl.php');
require_once('connection.php');
require_once('listing.php');

$global_conn = new PL_API_Connection("wvkGrh5nHYCPXVFmC17BeDn2KKxD7XE58rfg5BDksHka", "PHP_Curl");
$global_conn->INCLUDE_DISABLED = true;

$global_prop = new PL_Property_Listing();
$property =
	isset($_POST) && isset($_POST['property']) ? $_POST['property'] :
	isset($_GET) && isset($_GET['property']) ? $_GET['property'] :
	$argv[1];
$global_prop->listing_read($global_conn, $property);

var_dump($global_prop);

