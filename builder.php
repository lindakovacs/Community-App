<?php

/**
Plugin Name: PDX Website Builder
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */

define('BUILDER', __DIR__ . '/');

require_once(BUILDER . 'shortcodes/api_shortcodes.php');
require_once(BUILDER . 'shortcodes/www_shortcodes.php');

require_once(BUILDER . 'transitional/compatibility-api.php');
