<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER . 'api/listing_image.php');
require_once(BUILDER . 'www/image.php');


class PL_Shortcode_Listing_Image extends PL_Shortcode_Handler {
	protected $current_image;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode('data', __CLASS__, 'data_shortcode');

		// www shortcode
		$dispatcher->register_shortcode('img', __CLASS__, 'img_shortcode');
	}

	public function __construct(PL_Listing_Image $image) {
		$this->current_image = $image;
	}

	public function data_shortcode($args) {
		extract(shortcode_atts(array('attribute' => null), $args));
		if(in_array($attribute, array('id', 'url', 'caption')))
			return $this->current_image->{$attribute};

		return new PL_Shortcode_Yield();
	}

	public function img_shortcode($args) {
		if(!($url = $this->data_shortcode(array('attribute' => 'url'))))
			return "";

		$html = new HTML_Image($url, shortcode_atts(array('id' => '', 'class' => '', 'style' => ''), $args));
		return $html->html_string();
	}
}