<?php 

/**
 * Internal caching class for the framework
 * 
 * Serves as a wrapper for the PL_Cache class from the Placester plugin (if available)
 * 
 */
class PLS_Cache {
	
	const TTL_LOW  = 1800; // 30 minutes
	const TTL_HOUR = 3600; // 1 hour
	const TTL_MID = 43200; // 12 hours
	const TTL_DAY = 86400; // 24 hours
	const TTL_HIGH = 172800; // 48 hours
	
	public $pl_cache_object = NULL;
	
	function __construct ($group = 'general') {
		if (class_exists('PL_Cache')) {
			$this->pl_cache_object = new PL_Cache($group);
		}
	}
	
	public function get() {
		if ( !is_null($this->pl_cache_object) ) {
			$args = func_get_args();
			return $this->pl_cache_object->get($args);
		}
		
		return false;
	}
	
	public function save ($result, $duration = 172800) {
		if( ! is_null( $this->pl_cache_object ) ) {
			$this->pl_cache_object->save( $result, $duration );
		}
	}
}

$pls_widget_cache = new PLS_Widget_Cache();
/**
 * Based loosely on WP Widget Cache
 * Automatically caches widgets per page and only for GET requests
 * @see http://wordpress.org/extend/plugins/wp-widget-cache/screenshots/
 */
class PLS_Widget_Cache {

	public function __construct() {
		// WP Widget Cache ties into the wp_head hook, but Blueprint caches the page header
		// so wp_head might not get invoked. Hook into wp instead.
		add_action('wp', array(__CLASS__,'widget_cache_redirect_callback'), 99999);
	}

	public static function widget_cache_redirect_callback()
	{
		global $wp_registered_widgets;

		// For every widget on every registered sidebar...
		foreach ( $wp_registered_widgets as $id => $widget )
		{
			// Attach an id
			array_push($wp_registered_widgets[$id]['params'],$id);
			// Store the original callback so we can render it if needed
			$wp_registered_widgets[$id]['callback_wc_redirect']=$wp_registered_widgets[$id]['callback'];
			// Render our own callback so we're called to render it instead
			$wp_registered_widgets[$id]['callback']=array(__CLASS__, 'widget_cache_redirected_callback');
		}
	}

	public static function widget_cache_redirected_callback()
	{
		global $wp_registered_widgets;

		$params = func_get_args(); // get all the passed params
		$id = array_pop($params);  // take off the widget ID
		$params['widget_class'] = __CLASS__;
		$params['cache_url'] = $_SERVER['REQUEST_URI']; // Cache per page

		$cache = new PLS_Cache('Widget');
		if ('GET' === $_SERVER['REQUEST_METHOD'] && $html = $cache->get($params)) {
			// Cache hit -- return the HTML...
			echo $html;
		}
		else {
			// Cache miss -- render the HTML...
			$callback=$wp_registered_widgets[$id]['callback_wc_redirect'];		// find the real callback

			// Just in case the callback isn't callable...
			if (!is_callable($callback)) {
				return;
			}

			// Let the widget render itself into an output buffer
			// Cache it & echo the rendered HTML
			ob_start();
			call_user_func_array($callback, $params);
			$html = ob_get_clean();
			$cache->save($html, PLS_Cache::TTL_LOW);
			echo $html;
		}
	}
}
