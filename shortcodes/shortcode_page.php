<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'www/page.php');
require_once(BUILDER_DIR . 'www/form.php');
require_once('shortcodes.php');


class PL_Shortcode_Page extends PL_Shortcode_Handler {
	protected $page_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'form', 'form_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'debug', 'debug_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'content', 'content_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'link_first_page', 'link_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'link_last_page', 'link_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'link_previous_page', 'link_shortcode');
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'link_next_page', 'link_shortcode');
	}

	public function __construct(PL_Page $page_context) {
		$this->page_context = $page_context;
	}

	public function form_shortcode($args, $content, $shortcode) {
		return $this->page_context->get_form_content();
	}

	public function debug_shortcode($args, $content, $shortcode) {
		return $this->page_context->get_query_string();
	}

	public function content_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('name' => '', 'id' => '', 'class' => '', 'style' => ''), $args));

		$html = new PL_Page_Content($id);
		$html->class = 'pdx-content' . ($name ? ' pdx-content-' . $name : '') . ($class ? ' ' . $class : '');
		$html->style = $style;

		$html->add_content(do_shortcode($content));

		return $html->html_string();
	}

	public function link_shortcode($args, $content, $shortcode) {
		$parts = explode('_', $shortcode); $name = $parts[1];
		$function = "get_{$name}_page_url";

		if(!$content)
			$content = ucfirst($name);

		if(($href = $this->page_context->$function()) && $href != $this->page_context->get_current_page_url()) {
			$html = new HTML_Anchor($href);
			$html->add_content($content ?: ucfirst($name));

			return $html->html_string();
		}

		return $content;
	}
}
