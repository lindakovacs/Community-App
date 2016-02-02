<?php 

class PLS_Map_Neighborhood extends PLS_Map {

	public static function neighborhood($listings = array(), $map_args = array(), $marker_args = array(), $polygon) {
		$map_args = self::process_defaults($map_args);
		self::make_markers($listings, $marker_args, $map_args);
		extract($map_args, EXTR_SKIP);
		
		// wp_enqueue_script('google-maps', 'http://maps.googleapis.com/maps/api/js?libraries=places');
		wp_register_script('text-overlay', PLACESTER_PLUGIN_URL . 'placester-maps/js/text-overlay.js' );
		wp_enqueue_script('text-overlay');

		ob_start();
		?>
		<script src="http://maps.googleapis.com/maps/api/js?libraries=places" type="text/javascript"></script>
		<?php
		return ob_get_clean();
	}
}