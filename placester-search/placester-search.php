<?php
/**
Plugin Name: Placester Property Search System
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/


include_once('lib/form.php');
include_once('lib/filters.php');
include_once('lib/search-permalinks.php');


add_action( 'after_setup_theme', 'placester_add_search_components', 20 );
function placester_add_search_components () {
	if (!class_exists('Placester_Blueprint')) {
		include_once('lib/search.php');
		include_once('lib/partials.php');

		include_once('widgets/listings.php');
//		include_once('widgets/quick-search.php');
	}
}


add_action('wp_enqueue_scripts', 'placester_add_search_scripts', 20);
function placester_add_search_scripts() {
	if (!class_exists('Placester_Blueprint')) {
		ob_start();
		?>
		<script type="text/javascript">//<![CDATA[
			var info = {"ajaxurl": "<?php echo admin_url( 'admin-ajax.php' ); ?>"};
			//]]>
		</script>
		<?php
		echo ob_get_clean();

		wp_register_script('jquery-address', PLACESTER_PLUGIN_URL . 'placester-search/js/jquery.address.js', array('jquery'), PL_PLUGIN_VERSION, true);
		wp_enqueue_script('placester-listings', PLACESTER_PLUGIN_URL . 'placester-search/js/listings.js', array('jquery', 'jquery-address', 'jquery-datatables'), PL_PLUGIN_VERSION, true);
	}
}

$pl_admin_page = new PL_Admin_Page('placester_settings', 150, 'placester_filtering', 'Global Filters', 'Global Filters', PLACESTER_PLUGIN_DIR . 'placester-search/admin/filtering.php');
$pl_admin_page->require_script('filtering', PLACESTER_PLUGIN_URL . 'placester-search/admin/filtering.js');
$pl_admin_page->require_style('filtering', PLACESTER_PLUGIN_URL . 'placester-search/admin/filtering.css', array('placester-settings'));

