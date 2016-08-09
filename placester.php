<?php
/**
Plugin Name: Real Estate Website Builder
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.6.3J
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

define('PL_PLUGIN_VERSION','1.6.3J');

define( 'PLACESTER_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define( 'PLACESTER_PLUGIN_URL', trailingslashit(plugins_url()) . 'placester/' );


// API Server
global $PL_API_SERVER;
$PL_API_SERVER = defined('PL_API_SERVER') ? PL_API_SERVER : 'https://api.placester.com';

// Demo Account API Key
define( 'DEMO_API_KEY', '7e63514ebfad7608bbe7b4469ab470ecef4dc651099ae06fc1df6807717f0deacd38809e3c314ca09c085125f773a4c7' );


// core WP functionality
include_once('lib/pages.php'); // area pages need to be moved
include_once('lib/options.php');
include_once('lib/http.php');
include_once('lib/caching.php');
include_once('lib/listings.php'); // polygon functionality needs to be moved, handled via a wp filter


// v2/v2.1 data interface
include_once('api/config.php');
include_once('api/user.php');
include_once('api/listing.php');
include_once('api/custom_attribute.php');
include_once('api/integration.php');


// needed on the wp-admin side only (eventually)
include_once('admin/admin.php');
include_once('admin/helpers/users.php');
include_once('admin/helpers/listings.php');
include_once('admin/helpers/integrations.php');


// search
include_once('placester-search/placester-search.php');
include_once('placester-maps/placester-maps.php');

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

// listing image slideshow
include_once('placester-slideshow/placester-slideshow.php');


// if we're using a blueprint theme, don't duplicate its functionality
add_action( 'after_setup_theme', 'placester_check_for_blueprint', 18 );
function placester_check_for_blueprint () {
	if (!class_exists('Placester_Blueprint')) {
		define('PLS_JS_URL', PLACESTER_PLUGIN_URL . 'placester-search/js/');
		define('PLS_IMG_URL', PLACESTER_PLUGIN_URL . 'placester-search/images/');
		define('PLS_EXT_URL', PLACESTER_PLUGIN_URL . 'placester-slideshow/');

		include_once('lib/smallprint.php');
		include_once('placester-agents/widgets/agent.php');
	}
}


// shared front-end libraries used by various components
add_action('wp_enqueue_scripts', 'placester_register_library_scripts', 5); // must come before the actual enqueueing done by sub-plugins
function placester_register_library_scripts()
{
	wp_register_style('jquery-ui', PLACESTER_PLUGIN_URL . 'lib/jquery-ui/css/smoothness/jquery-ui-1.8.17.custom.css');

	wp_register_script('jquery-datatables', PLACESTER_PLUGIN_URL . 'lib/datatables/jquery.dataTables.js', array('jquery'), NULL, true);
	wp_register_style('jquery-datatables', PLACESTER_PLUGIN_URL . 'lib/datatables/jquery.dataTables.css', array('jquery-ui'), NULL, true);

	// stupid hack for tampa and other old themes, remove after new version comes out
	if (class_exists('Placester_Blueprint')) {
		wp_enqueue_script('datatable', PLACESTER_PLUGIN_URL . 'lib/datatables/jquery.dataTables.js', array('jquery'), NULL, true);
	}

	else { //if (!class_exists('Placester_Blueprint')) {
		wp_register_script('jquery-fancybox', PLACESTER_PLUGIN_URL . 'lib/fancybox/jquery.fancybox-1.3.4.js', array('jquery'), '1.3.4', true);
		wp_register_script('jquery-fancybox-settings', PLACESTER_PLUGIN_URL . 'lib/fancybox/default-settings.js', array('jquery-fancybox'), '1.3.4', true);
		wp_register_style('jquery-fancybox', PLACESTER_PLUGIN_URL . 'lib/fancybox/jquery.fancybox-1.3.4.css', array());
	}
}


// shared front-end libraries used by plugin administration
add_action('admin_enqueue_scripts', 'placester_register_admin_scripts', 5); // must come before the actual enqueueing done by sub-plugins
function placester_register_admin_scripts()
{
	wp_register_style('jquery-ui', PLACESTER_PLUGIN_URL . 'lib/jquery-ui/css/smoothness/jquery-ui-1.8.17.custom.css');

	wp_register_script('jquery-datatables', PLACESTER_PLUGIN_URL . 'lib/datatables/jquery.dataTables.js', array('jquery'), NULL, true);
	wp_register_style('jquery-datatables', PLACESTER_PLUGIN_URL . 'lib/datatables/jquery.dataTables.css', array('jquery-ui'), NULL, true);
}


// handle the demo data message bar
add_action('wp_head', 'placester_info_bar');
add_action('admin_head', 'placester_info_bar');
function placester_info_bar() {
	if(PL_Option_Helper::get_demo_data_flag() && current_user_can('manage_options')) {
		include(PLACESTER_PLUGIN_DIR . 'admin/views/partials/infobar.php');
	}
}


// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
function placester_activate() {
	return true;
}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
function placester_post_slug() {
	return true;
}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
class PL_Analytics {
	public static function log_snippet_js() {
		return null;
	}
}
