<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'api/listing.php');
require_once('shortcode_listing_image.php');


class PL_Shortcode_Listing extends PL_Shortcode_Handler {
	protected $current_listing;
	protected $image_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode('data', __CLASS__, 'data_shortcode');
		$dispatcher->register_shortcode('if:data', __CLASS__, 'if_data_shortcode');
		$dispatcher->register_shortcode('image', __CLASS__, 'image_shortcode');
		$dispatcher->register_shortcode('foreach:image', __CLASS__, 'foreach_image_shortcode');

		// www shortcode
		$dispatcher->register_shortcode('img', __CLASS__, 'img_shortcode');
	}

	public function __construct(PL_Listing $current_listing) {
		$this->current_listing = $current_listing;
	}

	public function data_shortcode($args) {
		extract(shortcode_atts(array('attribute' => null), $args));
		if($attribute)
			return $this->current_listing->{$attribute};

		return "";
	}

	public function if_data_shortcode($args, $content) {
		extract(shortcode_atts(array('attribute' => null, 'value' => null), $args));
		if($attribute)
			if(is_null($value) ? $this->current_listing->{$attribute} : $this->current_listing->{$attribute} == $value)
				return do_shortcode($content);

		return "";
	}

	public function image_shortcode($args, $content) {
		extract(shortcode_atts(array('index' => null, 'next' => null), $args));
		$output = "[$shortcode]";

		if(!$this->image_context || $index || $next) {
			if($current_image = $this->current_listing->images->get_image($index)) {
				$this->image_context = new PL_Shortcode_Context(new PL_Shortcode_Listing_Image($current_image));
				if(!$content) $output = "[$shortcode id={$current_image->id}]";
			}
			else
				$this->image_context = null;
		}

		if($this->image_context && $content) {
			// modal?
			$output = do_shortcode($content);
			$this->image_context = null;
		}

		return $output;
	}

	public function foreach_image_shortcode($args, $content) {
		extract(shortcode_atts(array('index' => null, 'count' => null), $args));
		$output = "";

		if(!is_numeric($index))
			$index = $index ? 0 : null;

		if(!is_numeric($count))
			$count = $this->current_listing->images->count();

		if($count) {
			$current_image = $this->current_listing->images->get_image($index);
			while($current_image && $count-- > 0) {
				if($content) {
					$this->image_context = new PL_Shortcode_Context(new PL_Shortcode_Listing_Image($current_image));
					// modal?
					$output .= do_shortcode($content);
				}
				$current_image = $this->current_listing->images->get_image();
			}
		}

		$this->image_context = null;
		return $output;
	}

	public function img_shortcode($args) {
		return $this->image_shortcode($args, '[img]');
	}
}