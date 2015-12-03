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
require_once(BUILDER_DIR . 'display/display_listing.php');
require_once('shortcodes.php');
require_once('shortcode_listing_image.php');


class PL_Shortcode_Listing extends PL_Shortcode_Handler {
	protected $current_listing;
	protected $image_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'data', 'data_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'if:data', 'if_data_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'image', 'image_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'foreach:image', 'foreach_image_shortcode');

		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'url', 'url_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'link', 'link_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'display', 'display_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'img', 'img_shortcode');
	}

	static public function register_display_shortcodes(PL_Shortcode_Dispatcher $dispatcher, PL_Attribute_Map $map) {
		foreach($map->get_attributes() as $attribute)
			$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . $attribute->name, 'display_shortcodes');
	}

	public function __construct(PL_Listing $current_listing) {
		$this->current_listing = new PL_Display_Listing($current_listing);
	}

	public function data_shortcode($args) {
		extract(shortcode_atts(array('attribute' => null), $args));
		if($attribute)
			return $this->current_listing->get_value($attribute);

		return "";
	}

	public function if_data_shortcode($args, $content) {
		extract(shortcode_atts(array('attribute' => null, 'value' => null), $args));
		if($attribute)
			if(is_null($value) ? $this->current_listing->get_value($attribute) : $this->current_listing->get_value($attribute) == $value)
				return do_shortcode($content);

		return "";
	}

	public function image_shortcode($args, $content) {
		extract(shortcode_atts(array('index' => null, 'next' => null), $args));
		$output = "[$shortcode]";

		if(!$this->image_context || $index || $next) {
			if($current_image = $this->current_listing->get_images()->get_image($index)) {
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
			$count = $this->current_listing->get_images()->count();

		if($count) {
			$current_image = $this->current_listing->get_images()->get_image($index);
			while($current_image && $count-- > 0) {
				if($content) {
					$this->image_context = new PL_Shortcode_Context(new PL_Shortcode_Listing_Image($current_image));
					// modal?
					$output .= do_shortcode($content);
				}
				$current_image = $this->current_listing->get_images()->get_image();
			}
		}

		$this->image_context = null;
		return $output;
	}

	private function url($relative = false) {
		$url = '/property/' . $this->current_listing->get_value('pdx_id');
		if(!$relative)
			$url = home_url($url);
		return $url;
	}

	public function url_shortcode($args) {
		return $this->url();
	}

	public function link_shortcode($args, $content) {
		$anchor = new HTML_Anchor($this->url(true));
		$anchor->add_content($content ? do_shortcode($content) : $this->url(false));
		return $anchor->html_string();
	}

	public function display_shortcode($args) {
		extract(shortcode_atts(array('attribute' => null), $args));
		if($attribute)
			return $this->current_listing->get_display_value($attribute);

		return "";
	}

	public function display_shortcodes($args, $content, $shortcode) {
		if(!PL_SC_PREFIX)
			return $this->current_listing->get_display_value($shortcode);
		else if(strpos($shortcode, PL_SC_PREFIX) === 0)
			return $this->current_listing->get_display_value(substr($shortcode, strlen(PL_SC_PREFIX)));

		return "";
	}

	public function img_shortcode($args) {
		if($current_image = $this->current_listing->get_images()->get_image(0)) {
			$html = new HTML_Image($current_image->url, shortcode_atts(array('id' => '', 'class' => '', 'style' => ''), $args));
			return $html->html_string();
		}

		return "";
	}
}