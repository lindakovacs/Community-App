<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'api/search_context.php');
require_once('shortcodes.php');
require_once('shortcode_listing.php');
require_once('shortcode_search_result.php');


class PL_Shortcode_Search_Context extends PL_Shortcode_Handler {
	protected $search_context;
	protected $sc_listing_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'filter', 'filter_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'search', 'search_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'listing', 'listing_shortcode');
	}

	public function __construct(PL_Search_Context $search_context) {
		$this->search_context = $search_context;
		$this->sc_listing_context = null;
	}

	protected function new_shortcode_search_request($args) {
		$request = $this->search_context->get_connection()->new_search_request();

		if(is_array($args)) {
			$request_options = $request->get_request_options_array(true);
			foreach($args as $field => $value)
				if($request_options[$field]) {
					if(is_string($value) && strpos($value, '||') !== false)
						$value = explode('||', $value);
					$request->set($field, $value);
				}
		}

		return $request;
	}

	public function filter_shortcode($args, $content, $shortcode) {
		$output = "[$shortcode]";

		$filter = $this->new_shortcode_search_request($args);
		$limit1 = $this->search_context->get_connection()->new_search_request(array('limit' => 1));

		$this->search_context->add_filter($filter);
		if($filter_results = $this->search_context->search_listings($limit1))
			$output = "[$shortcode total=" . $filter_results->total() . "]";

		return $output;
	}

	public function search_shortcode($args, $content, $shortcode) {
		$this->sc_listing_context = null;
		$output = "[$shortcode]";

		$request = $this->new_shortcode_search_request($args);
		if($current_result = $this->search_context->search_listings($request)) {
			$this->sc_listing_context = new PL_Shortcode_Context(new PL_Shortcode_Search_Result($current_result));
			$output = "[$shortcode total={$current_result->total()} count={$current_result->count()}]";
		}

		return $output;
	}

	public function listing_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('id' => null), $args));
		$output = "[$shortcode]";

		if(!is_null($id)) {
			$connection = $this->search_context->get_connection();
			if($connection && $result = $connection->get_private_listing($id)) {
				$current_listing = new PL_Listing($result, $connection);
				$this->sc_listing_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($current_listing));
				if(!$content) $output = "[$shortcode id={$current_listing->pdx_id}]";
			}
			else
				$this->sc_listing_context = null;
		}
		else if(is_null($content)) {
			$this->sc_listing_context = null;
		}

		if($this->sc_listing_context && $content) {
			$output = do_shortcode($content);
		}

		return $output;
	}
}
