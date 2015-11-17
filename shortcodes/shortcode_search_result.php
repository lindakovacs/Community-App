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

		$dispatcher->register_shortcode('total', __CLASS__, 'total_shortcode');
		$dispatcher->register_shortcode('limit', __CLASS__, 'limit_shortcode');
		$dispatcher->register_shortcode('count', __CLASS__, 'count_shortcode');

		$dispatcher->register_shortcode('page', __CLASS__, 'page_shortcode');
		$dispatcher->register_shortcode('first', __CLASS__, 'first_shortcode');
		$dispatcher->register_shortcode('last', __CLASS__, 'last_shortcode');
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

	public function total_shortcode($args, $content, $shortcode) { return $this->current_result->total(); }
	public function limit_shortcode($args, $content, $shortcode) { return $this->current_result->limit(); }
	public function count_shortcode($args, $content, $shortcode) { return $this->current_result->count(); }

	public function page_shortcode($args, $content, $shortcode) {
		return $this->current_result->count() ? intval($this->current_result->offset() / $this->current_result->limit()) + 1 : 0;
	}
	public function first_shortcode($args, $content, $shortcode) {
		return $this->current_result->count() ? $this->current_result->offset() + 1 : 0;
	}
	public function last_shortcode($args, $content, $shortcode) {
		return $this->current_result->count() ? $this->current_result->offset() + $this->current_result->count() : 0;
	}
}