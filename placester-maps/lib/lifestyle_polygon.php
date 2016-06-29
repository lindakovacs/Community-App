<?php 

class PLS_Map_Lifestyle_Polygon extends PLS_Map {

	public static function lifestyle_polygon($listings = array(), $map_args = array(), $marker_args = array()) {
		$map_args = self::process_defaults($map_args);
		self::make_markers($listings, $marker_args, $map_args);
		extract($map_args, EXTR_SKIP);
		
     	wp_enqueue_script('google-maps', PL_Option_Helper::get_google_maps_js_url());
		wp_register_script('text-overlay', PLACESTER_PLUGIN_URL . 'placester-maps/js/text-overlay.js' );
		wp_enqueue_script('text-overlay');

		wp_register_script('lifestyle_polygon', PLACESTER_PLUGIN_URL . 'placester-maps/js/lifestyle_polygon.js' );
		wp_enqueue_script('lifestyle_polygon');

		ob_start();
		?>
			<?php echo self::get_lifestyle_controls($map_args); ?>
		<?php
		return ob_get_clean();
	}

}