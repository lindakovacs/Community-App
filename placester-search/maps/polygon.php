<?php 

class PLS_Map_Polygon extends PLS_Map {

	public static function polygon ($listings = array(), $map_args = array(), $marker_args = array()) {
		$map_args = self::process_defaults($map_args);
		self::make_markers($listings, $marker_args, $map_args);
		extract($map_args, EXTR_SKIP);
		wp_enqueue_script('google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false&libraries=places');
		$polygon_html = '';
		
		// Doesn't seem to always be an array
		if (!is_array($listings)) {
			$listings = array($listings);
		}

		// Try to retrieve from the cache...
		$cache = new PLS_Cache('Map Polygon');
		if ($polygon_html_cached = $cache->get(array_merge($listings, $map_args, $marker_args))) {
			$polygon_html = $polygon_html_cached;
		}
		
		if ($polygon_html === '') {
			ob_start();
		?>

		  <script src="<?php echo trailingslashit( PLS_JS_URL ) . 'libs/google-maps/text-overlay.js' ?>"></script>
			<?php echo self::get_lifestyle_controls($map_args); ?>
		<?php
			$polygon_html = ob_get_clean();
			$cache->save($polygon_html);
		}
		return $polygon_html;
	}

}