<?php


require_once(BUILDER_DIR . 'www/page.php');


class PL_WP_Page extends PL_Page {
	protected $post;
	protected $shortcode_context;

	public function __construct(PL_Page_Context $page_context, $post) {
		$this->post = $post;
		parent::__construct($page_context, get_query_var('pdx_search_id'), get_query_var('pdx_search_pg'));

		// enable shortcodes to access the queried result set
		$this->shortcode_context = new PL_Shortcode_Context(new PL_Shortcode_Search_Result($this->search_result));
	}

	protected function get_search_id($search_data) {
		$hash = hash('crc32b', $this->post->post_name . ($string_data = serialize($search_data)));
		update_post_meta($this->post->ID, 's/' . $hash, $string_data);
		return '/' . $hash;
	}

	protected function get_search_data($search_id) {
		$string_data = get_post_meta($this->post->ID, 's' . $search_id, true);
		return $string_data ? unserialize($string_data) : null;
	}

	protected function get_url($search_id = null, $search_pg = null) {
		return home_url($this->post->post_name . $search_id . $search_pg . '/');
	}

	protected function redirect($search_id = null, $search_pg = null, $code = 302) {
		wp_redirect($this->get_url($search_id, $search_pg), $code);
		exit();
	}

	protected function get_content_internal() {
		return $this->post->post_content;
	}

	protected function set_content_internal($content) {
		$this->post->post_content = $content;
	}

	public function wp_the_content($content) {
		return do_shortcode($content);
	}

	public function get_content() {
		$content = parent::get_content();
		if(!is_null($content))
			return $this->wp_the_content($content);

		return null;
	}

	public function get_content_partial($name) {
		$content = parent::get_content_partial($name);
		if(!is_null($content))
			return $this->wp_the_content($content);

		return null;
	}
}