<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'api/search_result.php');
require_once('shortcode_listing.php');


class PL_Shortcode_Listing_Block extends PL_Shortcode_Handler {
	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
	}
}

class PL_Shortcode_Listing_Loop extends PL_Shortcode_Handler {
	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
	}
}

class PL_Shortcode_Search_Result extends PL_Shortcode_Handler {
	protected $current_result;
	protected $listing_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode('listing', __CLASS__, 'listing_shortcode');
		$dispatcher->register_shortcode('foreach:listing', __CLASS__, 'foreach_listing_shortcode');

		$dispatcher->register_shortcode('search:total', __CLASS__, 'search_total_shortcode');
		$dispatcher->register_shortcode('search:limit', __CLASS__, 'search_limit_shortcode');
		$dispatcher->register_shortcode('search:count', __CLASS__, 'search_count_shortcode');

		$dispatcher->register_shortcode('search:page', __CLASS__, 'search_page_shortcode');
		$dispatcher->register_shortcode('search:first', __CLASS__, 'search_first_shortcode');
		$dispatcher->register_shortcode('search:last', __CLASS__, 'search_last_shortcode');
	}

	public function __construct(PL_Search_Result $current_result) {
		$this->current_result = $current_result;
	}

	public function listing_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('id' => null, 'index' => null, 'next' => null), $args));
		$output = "[$shortcode]";

		if($id)
			return new PL_Shortcode_Yield();

		if(!$this->listing_context || $index || $next) {
			if($current_listing = $this->current_result->get_listing($index)) {
				$this->listing_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($current_listing));
				if(!$content) $output = "[$shortcode id={$this->current_listing->pdx_id}]";
			}
			else
				$this->listing_context = null;
		}

		if($this->listing_context && $content) {
			$disable_connection_shortcodes = new PL_Shortcode_Context(new PL_Shortcode_Listing_Block);
			$output = do_shortcode($content);
			$disable_connection_shortcodes = null;
			$this->listing_context = null;
		}

		return $output;
	}

	public function foreach_listing_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('index' => null, 'count' => null), $args));
		$output = "";

		if(!is_numeric($index))
			$index = null;

		if(!is_numeric($count))
			$count = $this->current_result->count();

		if($count) {
			$current_listing = $this->current_result->get_listing($index);
			while($current_listing && $count-- > 0) {
				if($content) {
					$this->listing_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($current_listing));
					$disable_connection_shortcodes = new PL_Shortcode_Context(new PL_Shortcode_Listing_Block);
					$output .= do_shortcode($content);
					$disable_connection_shortcodes = null;
				}
				$current_listing = $this->current_result->get_listing();
			}
		}

		$this->listing_context = null;
		return $output;
	}
}