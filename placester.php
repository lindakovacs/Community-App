<?php
/**
Plugin Name: Real Estate Website Builder
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.3.4
Author URI: https://www.placester.com/
*/

/*  Copyright (c) 2013 - 2015  Placester, Inc. <matt@placester.com>
	All rights reserved.

	Placester Promoter is distributed under the GNU General Public License, Version 2,
	June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
	St, Fifth Floor, Boston, MA 02110, USA

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

define('PL_PLUGIN_VERSION','1.3.4');

define( 'PLACESTER_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define( 'PLACESTER_PLUGIN_URL', trailingslashit(plugins_url()) . 'placester/' );

define( 'PL_IMG_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'images/' );


// API Server
global $PL_API_SERVER;
$PL_API_SERVER = defined('PL_API_SERVER') ? PL_API_SERVER : 'https://api.placester.com';

// Demo Account API Key
define( 'DEMO_API_KEY', '7e63514ebfad7608bbe7b4469ab470ecef4dc651099ae06fc1df6807717f0deacd38809e3c314ca09c085125f773a4c7' );


// core WP functionality
include_once('lib/pages.php'); // area pages need to be moved

include_once('models/options.php');
include_once('helpers/option.php');

include_once('lib/http.php');
include_once('lib/caching.php');
include_once('helpers/caching.php');


// v2/v2.1 data interface
include_once('lib/config.php');
include_once('lib/validation.php');

include_once('config/api/users.php');
include_once('models/user.php');
include_once('helpers/user.php');

include_once('config/api/listings.php');
include_once('models/listing.php');
include_once('helpers/listing.php'); // polygon functionality needs to be moved, handled via a wp filter

include_once('config/api/integration.php');
include_once('models/integration.php');
include_once('helpers/integrations.php');

include_once('config/api/custom_attributes.php');
include_once('models/custom_attribute.php');
include_once('helpers/custom_attributes.php');

include_once('config/api/wordpress.php');
include_once('models/wordpress.php');
include_once('helpers/wordpress.php');

// search
include_once('lib/form.php');
include_once('lib/global-filters.php');
include_once('lib/permalink-search.php');


// analytics
include_once('lib/mixpanel.php');
include_once('config/analytics.php');
include_once('lib/analytics.php');
include_once('helpers/logging.php'); // this needs to be separated


// third party tools
include_once('lib/dragonfly-resize.php');

if ((!is_admin() && file_exists(WP_PLUGIN_DIR.'/wordpress-seo/inc/class-sitemaps.php') && strpos($_SERVER["REQUEST_URI"],'sitemap')!==false)
	|| is_admin()) {
	include_once('lib/sitemaps.php'); // refers to taxonomies
}


// needed on the wp-admin side only (eventually)
include_once('admin/admin.php');


// areas and polygons
include_once('placester-areas/placester-areas.php');

// mls compliance
include_once('placester-compliance/placester-compliance.php');

// lead capture and management
include_once('placester-leads/placester-leads.php');

// legacy shortcode system
include_once('placester-shortcodes/placester-shortcodes.php');

// real estate site add-ons
include_once('placester-tools/placester-tools.php');

// setup and customization
include_once('placester-customizer/placester-customizer.php');


// Register hook to load blueprint from plugin if the active theme has yet to do so...
add_action( 'after_setup_theme', 'load_blueprint_from_plugin', 18 );
function load_blueprint_from_plugin () {
	if (!class_exists('Placester_Blueprint')) {
		// Load script that contains main blueprint class, and instantiate an object...
		require_once('blueprint/blueprint.php');
		new Placester_Blueprint('2.5', 'plugin');
		add_action('init', 'plugin_blueprint_settings');
	}
}

// This takes care of what a theme's functions.php file normally handles...
function plugin_blueprint_settings () {
	remove_theme_support('pls-default-css');
	remove_theme_support('pls-default-style');
	remove_theme_support('pls-default-960');
	remove_theme_support('pls-default-normalize');
	remove_theme_support('pls-js');
	remove_theme_support('pls-routing-util-templates');

	// for property detail page lead capture
	add_theme_support( 'pls-js', array(
			'jquery-ui'    => array('script' => true, 'style' => true),
			'datatable'    => array('script' => true, 'style' => true),
			'lead-capture' => array('script' => true, 'style' => false)
		)
	);
}


register_activation_hook(__FILE__, 'placester_activate');
function placester_activate () {
	PL_WordPress_Helper::report_url();
}

add_action('wp_head', 'placester_info_bar');
function placester_info_bar() {
	if(PL_Option_Helper::get_demo_data_flag() && current_user_can('manage_options')) {
		include(PLACESTER_PLUGIN_DIR . 'admin/views/partials/infobar.php');
	}
}

add_action('wp_enqueue_scripts', 'placester_info_bar_enqueue');
function placester_info_bar_enqueue() {
	if(PL_Option_Helper::get_demo_data_flag() && current_user_can('manage_options')) {
		wp_enqueue_style('placester-global');
		wp_enqueue_script('placester-infobar', PLACESTER_PLUGIN_URL . 'admin/js/infobar.js', array('jquery'), PL_PLUGIN_VERSION);
	}
}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
function placester_post_slug() {
  return true;
}
