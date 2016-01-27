<?php 

class PLS_Map_Lifestyle_Polygon extends PLS_Map {

	public static function lifestyle_polygon($listings = array(), $map_args = array(), $marker_args = array()) {
		$map_args = self::process_defaults($map_args);
		self::make_markers($listings, $marker_args, $map_args);
		extract($map_args, EXTR_SKIP);
		
     	wp_enqueue_script('google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false');
		wp_register_script('text-overlay', trailingslashit( PLS_JS_URL ) . 'libs/google-maps/text-overlay.js' );
		wp_enqueue_script('text-overlay');

		wp_register_script('lifestyle_polygon', trailingslashit( PLS_JS_URL ) . 'scripts/lifestyle_polygon.js' );
		wp_enqueue_script('lifestyle_polygon');

		ob_start();
		?>
			<?php echo self::get_lifestyle_controls($map_args); ?>
		<?php
		return ob_get_clean();
	}

}