<?php

// stubbed out blueprint core functionality

function pls_get_option($arg1 = null, $arg2 = null, $arg3 = null) { return $arg2; }
function pls_has_plugin_error($arg1 = null) { return false; }
function pls_do_atomic($arg1 = null, $arg2 = null) { return false; }
function pls_apply_atomic($arg1 = null, $arg2 = null) { return $arg2; }
function pls_get_textdomain() { return get_template(); }

include_once('smallprint/caching.php');
include_once('smallprint/util.php');
include_once('smallprint/html.php');
include_once('smallprint/formatting.php');
include_once('smallprint/image-util.php');
include_once('smallprint/internationalization.php');
