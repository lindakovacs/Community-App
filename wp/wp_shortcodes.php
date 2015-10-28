<?php


require_once(BUILDER . 'shortcodes/shortcodes.php');
require_once(BUILDER . 'shortcodes/shortcodes_www.php');
require_once(BUILDER . 'shortcodes/shortcode_connection.php');
require_once(BUILDER . 'shortcodes/shortcode_search_result.php');
require_once(BUILDER . 'shortcodes/shortcode_listing.php');
require_once(BUILDER . 'shortcodes/shortcode_listing_image.php');


class PL_WP_Shortcode_System extends PL_Shortcode_System {
	static protected $singleton;
	static protected $global_context;

	public function __construct() {
		if(!self::$singleton) {
			self::$singleton = $this;

			self::initialize();
			self::register_wp_shortcodes();
		}
	}

	protected static function register_wp_shortcodes() {
		self::register_handler('PL_Shortcode_Connection');
		self::register_handler('PL_Shortcode_Search_Result');
		self::register_handler('PL_Shortcode_Listing');
		self::register_handler('PL_Shortcode_Listing_Image');

		foreach(self::$dispatcher->get_registered_shortcodes() as $shortcode)
			add_shortcode($shortcode, array(__CLASS__, 'wp_shortcode'));
	}

	public static function wp_shortcode($args, $content, $shortcode) {
		return self::$dispatcher->dispatch_shortcode($shortcode, $args, $content);
	}
}
