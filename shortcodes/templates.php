<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'www/html.php');
require_once('shortcodes.php');


class PL_Template_Element implements HTML {
	protected $html;

	public function __toString() { return $this->html ? $this->html->html_string() : ''; }
	public function html_string() { return $this->html ? $this->html->html_string() : ''; }

	// add arbitrary template content
	public function add_content($content) { $this->html->add_content($content); }
}


class PL_Template_Handler extends PL_Shortcode_Handler {

	// override this to leave some shortcodes for later processing
	protected function is_template_shortcode($shortcode) {
		return true;
	}

	// process shortcodes to produce output and simultaneously build a DOM-ish structure
	protected function do_template_shortcodes(PL_Template_Element $element, $content) {
		$pattern = get_shortcode_regex(); $matches = array();
		$new_content = ''; $content_index = 0;

		if(preg_match_all('/'. $pattern .'/s', $content, $matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE))
			foreach($matches as $match)
				if($this->is_template_shortcode($match[2][0])) {
					$static_content = substr($content, $content_index, $match[0][1] - $content_index);
					if($element) $element->add_content($static_content);

					$new_content .= $static_content;
					$new_content .= do_shortcode($match[0][0]);
					$content_index = $match[0][1] + strlen($match[0][0]);
				}

		$static_content = substr($content, $content_index);
		if($element) $element->add_content($static_content);

		$new_content .= $static_content;
		return $new_content;
	}
}
