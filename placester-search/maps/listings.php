<?php 

class PLS_Map_Listings extends PLS_Map {

	public static function listings($listings = array(), $map_args = array(), $marker_args = array()) {
		$map_args = self::process_defaults($map_args);
		self::make_markers($listings, $marker_args, $map_args);
		extract($map_args, EXTR_SKIP);
		
    	wp_enqueue_script('google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false&libraries=places');
		wp_register_script('text-overlay', trailingslashit( PLS_JS_URL ) . 'libs/google-maps/text-overlay.js' );
		wp_enqueue_script('text-overlay');

		ob_start();

		?>
		<?php echo self::get_lifestyle_controls($map_args); ?>
		<?php
		return ob_get_clean();
	}

}