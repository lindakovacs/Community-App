<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'api/connection.php');
require_once('shortcode_listing.php');
require_once('shortcode_search_result.php');


class PL_Shortcode_Connection extends PL_Shortcode_Handler {
	protected $connection;
	protected $current_filter;
	protected $listing_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode('connection', __CLASS__, 'shortcode_disabled');
		$dispatcher->register_shortcode('filter', __CLASS__, 'filter_shortcode');
		$dispatcher->register_shortcode('search', __CLASS__, 'search_shortcode');
		$dispatcher->register_shortcode('listing', __CLASS__, 'listing_shortcode');
	}

	public function __construct(PL_API_Connection $connection) {
		$this->connection = $connection;
		$this->current_filter = null;
		$this->listing_context = null;
	}

	public function connection_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('api_key' => null), $args));
		$this->__construct(null);
		$output = "[$shortcode]";

		if($api_key) {
			$this->connection = new PL_API_Connection($api_key);
			$this->connection->enable_attribute(array_keys($this->connection->get_standard_attributes()));
			$this->connection->enable_attribute(array_keys($this->connection->get_custom_attributes()));
			$output = "[connection api_key=" . $api_key . "]";
		}

		return $output;
	}

	protected function new_shortcode_search_filter(PL_API_Connection $connection, $args) {
		$filter = $connection->new_search_request();
		$filter->set('limit', 1);

		if(is_array($args)) {
			$filter_options = $filter->get_filter_options_array(true);
			foreach($args as $field => $value)
				if($filter_options[$field]) {
					if(is_string($value) && strpos($value, '||') !== false)
						$value = explode('||', $value);
					$filter->set($field, $value);
				}
		}
		return $filter;
	}

	function filter_shortcode($args, $content, $shortcode) {
		$output = "[$shortcode]";

		$this->current_filter = $this->new_shortcode_search_filter($this->connection, $args);
		if($filter_results = $this->connection->search_listings($this->current_filter))
			$output = "[$shortcode total=" . $filter_results->total() . "]";

		return $output;
	}

	protected function new_shortcode_search_request(PL_API_Connection $connection, $args) {
		$request = $connection->new_search_request();

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

	function search_shortcode($args, $content, $shortcode) {
		$this->listing_context = null;
		$output = "[$shortcode]";

		$search_request = $this->new_shortcode_search_request($this->connection, $args);
		if($this->current_filter)
			$search_request = PL_Search_Request::combine($this->current_filter, $search_request);

		if($current_result = $this->connection->search_listings($search_request)) {
			$this->listing_context = new PL_Shortcode_Context(new PL_Shortcode_Search_Result($current_result));
			$output = "[$shortcode total={$current_result->total()} count={$current_result->count()}]";
		}

		return $output;
	}

	public function listing_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('id' => null, 'index' => null, 'next' => null), $args));
		$output = "[$shortcode]";

		if(!is_null($id) && is_null($index) && is_null($next)) {
			if($this->connection && $result = $this->connection->get_private_listing($id)) {
				$current_listing = new PL_Listing($result, $this->connection);
				$this->listing_context = new PL_Shortcode_Context(new PL_Shortcode_Listing($current_listing));
				if(!$content) $output = "[$shortcode id={$this->current_listing->pdx_id}]";
			}
			else
				$this->listing_context = null;
		}
		else if(is_null($content) || !is_null($index) || !is_null($next)) {
			$this->listing_context = null;
		}

		if($this->listing_context && $content) {
			$disable_connection_shortcodes = new PL_Shortcode_Context(new PL_Shortcode_Listing_Block);
			$output = do_shortcode($content);
			$disable_connection_shortcodes = null;
		}

		return $output;
	}

	function api_test_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('api_key' => null, 'test_id' => 0), $args));

		if($api_key) {
			$test_connection = new PL_API_Connection($api_key);
			$test_connection->enable_attribute(array_keys($test_connection->get_standard_attributes()));
			$test_connection->enable_attribute(array_keys($test_connection->get_custom_attributes()));

			switch($test_id) {
				case 1:
					$test_listing = $test_connection->new_private_listing($this->current_listing);
					$test_result = $test_connection->create_listing($test_listing);
					$test_result = $test_result ? 'create id=' . $test_result : null;
					break;
				case 2:
					$test_listing = $test_connection->get_private_listing($this->current_listing);
					$test_listing->latitude = -50.0;
					$test_listing->longitude = -50.0;
					$test_listing->price = $this->current_listing->price + 500;
					$test_listing->beds = $this->current_listing->beds + 1;
					$test_result = $test_connection->update_listing($test_listing);
					$test_result = $test_result ? 'update id=' . $test_result : null;
					break;
				case 3:
					$test_listing = $this->current_listing->pdx_id;
					$test_result = $test_connection->delete_listing($test_listing);
					$test_result = $test_result ? 'delete id=' . $test_listing : null;
					break;
			}

			if($test_result)
				return "[" . $test_result . "]";
		}

		return null;
	}

	function search_test_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('field' => ''), $args));

		$values = $this->connection->read_attribute_values($field, $this->current_filter);
		$result = '';

		if(count($values) > 1) {
			$args = array($field => $values[0]);
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$args = array($field . '_match' => 'ne', $field => $values[0]);
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$result .= '<br>';
			$args = array($field . '_match' => 'like', $field => substr($values[0], 0, 3));
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$args = array($field . '_match' => 'and_like', $field => substr($values[0], 0, 3) . '||' . substr($values[0], -3));
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$args = array($field . '_match' => 'or_like', $field => substr($values[0], 0, 3) . '||' . substr($values[0], -3));
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$result .= '<br>';
			$args = array('min_' . $field => substr($values[0], 0, 3));
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$args = array('max_' . $field => substr($values[0], 0, 3));
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';
		}

		if(count($values) > 2) {
			$result .= '<br>';

			$args = array($field . '_match' => 'in', $field => $values[0] . '||' . $values[1]);
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';

			$args = array($field . '_match' => 'nin', $field => $values[0] . '||' . $values[1]);
			$result .= $this->search_shortcode($args, null, 'search') . ' ';
			foreach($args as $name => $value)
				$result .= ' (' . $name . '=' . $value . ') ';
			$result .= '<br>';
		}

		return $result;
	}
}
