<?php


require_once('admin-page.php');
require_once('admin-box.php');


define( 'PL_ADMIN_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'admin/' );
define( 'PL_ADMIN_VIEWS_DIR', trailingslashit(PL_ADMIN_DIR) . 'views/' );
define( 'PL_ADMIN_PARTIALS_DIR', trailingslashit(PL_ADMIN_VIEWS_DIR) . 'partials/' );

define( 'PL_MAPS_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'placester-maps/' );
define( 'PL_AREAS_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'placester-areas/' );
define( 'PL_CUSTOMIZER_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'placester-customizer/' );
define( 'PL_LEADS_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'placester-leads/' );
define( 'PL_SHORTCODES_DIR', trailingslashit(PLACESTER_PLUGIN_DIR) . 'placester-shortcodes/' );

define( 'PL_ADMIN_JS_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'admin/js/' );
define( 'PL_ADMIN_CSS_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'admin/css/' );

define( 'PL_MAPS_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'placester-maps/' );
define( 'PL_MAPS_JS_URL', trailingslashit(PL_MAPS_URL) . 'js/' );

define( 'PL_AREAS_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'placester-areas/' );
define( 'PL_AREAS_JS_URL', trailingslashit(PL_AREAS_URL) . 'admin/js/' );
define( 'PL_AREAS_CSS_URL', trailingslashit(PL_AREAS_URL) . 'admin/css/' );

define( 'PL_CUSTOMIZER_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'placester-customizer/' );
define( 'PL_CUSTOMIZER_JS_URL', trailingslashit(PL_CUSTOMIZER_URL) . 'admin/js/' );
define( 'PL_CUSTOMIZER_CSS_URL', trailingslashit(PL_CUSTOMIZER_URL) . 'admin/css/' );

define( 'PL_LEADS_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'placester-leads/' );
define( 'PL_LEADS_JS_URL', trailingslashit(PL_LEADS_URL) . 'admin/js/' );
define( 'PL_LEADS_CSS_URL', trailingslashit(PL_LEADS_URL) . 'admin/css/' );

define( 'PL_SHORTCODES_URL', trailingslashit(PLACESTER_PLUGIN_URL) . 'placester-shortcodes/' );
define( 'PL_SHORTCODES_JS_URL', trailingslashit(PL_SHORTCODES_URL) . 'admin/js/' );
define( 'PL_SHORTCODES_CSS_URL', trailingslashit(PL_SHORTCODES_URL) . 'admin/css/' );


class PL_Admin_Page extends PL_Admin_Page_v1A {
	public function page_enqueue_scripts() {
		// enqueue for all placester admin pages
		wp_enqueue_script('placester-global');
		wp_enqueue_style('placester-global');

		// and add the bit of dynamic script below
		add_action('admin_print_scripts', array($this, 'page_print_scripts'));

		// enqueue the page-specific scripts
		parent::page_enqueue_scripts();

		// special menu rendering for property edit page
		if($this->page_name == 'placester_property_edit') {
			$this->page_parent = 'placester_properties'; // make edit page visible in menu only when it is used
			if($_REQUEST['id'])
				$this->page_name .= '&id=' . $_REQUEST['id'];
		}
	}

	public function page_print_scripts() {
	?>
		<script type="text/javascript">
			var adminurl = '<?php echo trailingslashit(admin_url()) . 'admin.php'; ?>';
			var siteurl = '<?php echo site_url(); ?>';
		</script>
	<?php
	}
}


// shared resources
PL_Admin_Page::register_style('placester-global', PL_ADMIN_CSS_URL . 'global.css', array('jquery-ui'));
PL_Admin_Page::register_style('settings-all', PL_ADMIN_CSS_URL . 'settings.css');

PL_Admin_Page::register_script('placester-global', PL_ADMIN_JS_URL . 'global.js', array('jquery'));


// administration pages
$pl_admin_page = new PL_Admin_Page('placester', 100, 'placester_settings', 'Settings', 'General', PL_ADMIN_VIEWS_DIR . 'general.php');
$pl_admin_page->require_script('settings-general', PL_ADMIN_JS_URL . 'general.js', array('jquery-ui-core', 'jquery-ui-dialog'));
$pl_admin_page->require_style('settings-general', PL_ADMIN_CSS_URL . 'general.css', array('settings-all'));

$pl_admin_page = new PL_Admin_Page('placester_settings', 180, 'placester_international', 'International', 'International', PL_ADMIN_VIEWS_DIR . 'international.php');
$pl_admin_page->require_script('international', PL_ADMIN_JS_URL . 'international.js');
$pl_admin_page->require_style('settings-all');

$pl_admin_page = new PL_Admin_Page('placester', 200, 'placester_integration', 'IDX/MLS', 'IDX/MLS', PL_ADMIN_VIEWS_DIR . 'integration.php');
$pl_admin_page->require_script('integration', PL_ADMIN_JS_URL . 'integration.js');
$pl_admin_page->require_style('integration', PL_ADMIN_CSS_URL . 'integration.css');

$pl_admin_page = new PL_Admin_Page('placester', 300, 'placester_properties', 'Listings', 'All Listings', PL_ADMIN_VIEWS_DIR . 'my-listings.php');
$pl_admin_page->require_script('my-listings', PL_ADMIN_JS_URL . 'my-listings.js', array('jquery-ui-dialog', 'jquery-ui-datepicker', 'jquery-datatables'));
$pl_admin_page->require_style('my-listings', PL_ADMIN_CSS_URL . 'my-listings.css', array('jquery-datatables'));

$pl_admin_page = new PL_Admin_Page('placester_properties', 320, 'placester_property_add', 'Add Listing', 'Add Listing', PL_ADMIN_VIEWS_DIR . 'add-listing.php');
$pl_admin_page->require_script('jquery-iframe-transport', PL_ADMIN_JS_URL . 'blueimp/js/jquery.iframe-transport.js', array('jquery'));
$pl_admin_page->require_script('jquery-fileupload', PL_ADMIN_JS_URL . 'blueimp/js/jquery.fileupload.js', array('jquery-ui-widget', 'jquery-ui-sortable'));
$pl_admin_page->require_script('add-listing', PL_ADMIN_JS_URL . 'add-listing.js', array('jquery-ui-datepicker', 'jquery-iframe-transport', 'jquery-fileupload'));
$pl_admin_page->require_script('location-map', PL_ADMIN_JS_URL . 'location-map.js', array('add-listing', 'google-maps', 'text-overlay'));
$pl_admin_page->require_style('add-listing', PL_ADMIN_CSS_URL . 'add-listing.css');

$pl_admin_page = new PL_Admin_Page('placester_properties_x', 325, 'placester_property_edit', 'Edit Listing', 'Edit Listing', PL_ADMIN_VIEWS_DIR . 'add-listing.php');
$pl_admin_page->require_script('jquery-iframe-transport', PL_ADMIN_JS_URL . 'blueimp/js/jquery.iframe-transport.js', array('jquery'));
$pl_admin_page->require_script('jquery-fileupload', PL_ADMIN_JS_URL . 'blueimp/js/jquery.fileupload.js', array('jquery-ui-widget', 'jquery-ui-sortable'));
$pl_admin_page->require_script('add-listing', PL_ADMIN_JS_URL . 'add-listing.js', array('jquery-ui-datepicker', 'jquery-iframe-transport', 'jquery-fileupload'));
$pl_admin_page->require_script('location-map', PL_ADMIN_JS_URL . 'location-map.js', array('add-listing', 'google-maps', 'text-overlay'));
$pl_admin_page->require_style('add-listing', PL_ADMIN_CSS_URL . 'add-listing.css');

//$pl_admin_page = new PL_Admin_Page('placester', 9100, 'placester_support', 'Support', 'Support', PL_ADMIN_VIEWS_DIR . 'support.php');
//$pl_admin_page->require_style('support', PL_ADMIN_CSS_URL . 'support.css');


// miscellaneous touch-ups to standard admin pages
function placester_admin_scripts($hook) {
	if($hook == 'post.php' || $hook == 'post-new.php')
		wp_enqueue_style('post-screens', PL_ADMIN_CSS_URL . 'post-screens.css');
}
