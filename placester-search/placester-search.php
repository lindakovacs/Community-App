<?php
/**
Plugin Name: Placester Property Search System
Plugin URI: https://placester.com/
Author: Placester.com
Version: 1.0.0
Author URI: https://www.placester.com/
*/


include_once('lib/form.php');
include_once('lib/permalink-search.php');


add_action( 'after_setup_theme', 'placester_add_search_components', 20 );
function placester_add_search_components () {
	if (!class_exists('Placester_Blueprint')) {
		include_once('lib/listings.php');
		include_once('lib/partials.php');

		include_once('widgets/listings.php');
//		include_once('widgets/quick-search.php');

		define('PLS_JS_URL', PLACESTER_PLUGIN_URL . 'placester-search/js/');
		define('PLS_IMG_URL', PLACESTER_PLUGIN_URL . 'placester-search/images/');
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

		wp_register_style('jquery-ui', PL_ADMIN_JS_URL . 'jquery-ui/css/smoothness/jquery-ui-1.8.17.custom.css');
		wp_register_style('jquery-datatables', PLACESTER_PLUGIN_URL . 'admin/js/datatables/jquery.dataTables.css', array('jquery-ui'));
		wp_register_script('jquery-datatables', PLACESTER_PLUGIN_URL . 'admin/js/datatables/jquery.dataTables.js', array('jquery'), PL_PLUGIN_VERSION, true);

		wp_register_script('jquery-address', PLACESTER_PLUGIN_URL . 'placester-search/js/jquery.address.js', array('jquery'), PL_PLUGIN_VERSION, true);
		wp_enqueue_script('placester-listings', PLACESTER_PLUGIN_URL . 'placester-search/js/listings.js', array('jquery', 'jquery-address', 'jquery-datatables'), PL_PLUGIN_VERSION, true);
	}
}

