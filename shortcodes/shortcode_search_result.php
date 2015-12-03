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
require_once('shortcodes.php');
require_once('shortcode_listing.php');


class PL_Shortcode_Search_Result extends PL_Shortcode_Handler {
	protected $current_result;
	protected $listing_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'listing', 'listing_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'foreach:listing', 'foreach_listing_shortcode');

		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'total', 'total_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'limit', 'limit_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'offset', 'offset_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'count', 'count_shortcode');

		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'page', 'page_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'first', 'first_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'last', 'last_shortcode');
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
			$output = do_shortcode($content);
			$this->listing_context = null;
		}

		return $output;
	}

	public function foreach_listing_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('index' => null, 'count' => null), $args));
		$output = "";

		if(!is_numeric($index)) {
			$this->current_result->rewind();
			$index = null;
		}

		if(!is_numeric($count))
			$count = $this->current_result->count();

		if($count) {
			$current_listing = $this->current_result->get_listing($index);
			while($current_listing && $count-- > 0) {
				if($content) {
					$this->listing_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($current_listing));
					$output .= do_shortcode($content);
				}
				$current_listing = $this->current_result->get_listing();
			}
		}

		$this->listing_context = null;
		return $output;
	}

	public function total_shortcode($args, $content, $shortcode) { return $this->current_result->total(); }
	public function limit_shortcode($args, $content, $shortcode) { return $this->current_result->limit(); }
	public function offset_shortcode($args, $content, $shortcode) { return $this->current_result->offset(); }
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