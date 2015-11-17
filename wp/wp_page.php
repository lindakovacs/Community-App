<?php


require_once(BUILDER_DIR . 'www/page.php');
require_once(BUILDER_DIR . 'www/form.php');


class PL_WP_Page extends PL_Page_Context {
	protected $wp_filtering;

	protected $form;
	protected $search_request;
	protected $search_result;
	protected $shortcode_context;

	public function __construct($connection, $post) {
		parent::__construct($connection);

		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->form = new PL_Search_Form($this, null, $_REQUEST);
			set_query_var('pdx_search_id', $this->get_search_id($post, $this->form->get_search_data()));
			set_query_var('pdx_search_pg', null);

			wp_redirect($this->wp_post_type_link(null, $post), 302);
			exit();
		}

		else if(($search_id = get_query_var('pdx_search_id')) && is_array($search_data = $this->get_search_data($post, $search_id))) {
			$this->form = new PL_Search_Form($this, null, $search_data);
			if($search_id != ($new_search_id = $this->get_search_id($post, $this->form->get_search_data()))) {
				set_query_var('pdx_search_id', $new_search_id);
				set_query_var('pdx_search_pg', null);

				wp_redirect($this->wp_post_type_link(null, $post), 302);
				exit();
			}
		}

		else {
			$this->form = new PL_Search_Form($this, null, null);
			if($search_id) {
				set_query_var('pdx_search_id', null);
				set_query_var('pdx_search_pg', null);

				wp_redirect($this->wp_post_type_link(null, $post), 302);
				exit();
			}
		}

		$search_offset = $this->get_search_offset($search_pg = get_query_var('pdx_search_pg'));
		if($search_offset == 0 && $search_pg !== '' && $search_pg !== null) {
			set_query_var('pdx_search_pg', null);

			wp_redirect($this->wp_post_type_link(null, $post), 302);
			exit();
		}

		$this->search_request = $this->form->get_search_request();
		$this->search_request->set('offset', $search_offset);
		$this->search_result = $this->search_listings($this->search_request);

		if($search_pg && $search_offset >= ($search_total = $this->search_result->total())) {
			set_query_var('pdx_search_pg', $this->get_search_pg($search_total - 1));

			wp_redirect($this->wp_post_type_link(null, $post), 302);
			exit();
		}

		$this->shortcode_context = new PL_Shortcode_Context(new PL_Shortcode_Search_Result($this->search_result));
	}

	public function get_search_id($post, $search_data) {
		$hash = hash('crc32b', $post->post_name . ($string_data = serialize($search_data)));
		update_post_meta($post->ID, 's/' . $hash, $string_data);
		return '/' . $hash;
	}

	public function get_search_data($post, $search_id) {
		$string_data = get_post_meta($post->ID, 's' . $search_id, true);
		return $string_data ? unserialize($string_data) : null;
	}

	public function get_search_pg($search_offset) {
		$pg = intval($search_offset / 12);
		return $pg ? '/' . ++$pg : null;
	}

	public function get_search_offset($search_pg) {
		$pg = intval(ltrim($search_pg, '/'));
		return $pg ? 12 * --$pg : 0;
	}

	public function form_content() {
		return $this->form ? $this->form->html_string() : '';
	}

	public function query_string() {
		return $this->search_request ? $this->search_request->query_string() : '';
	}

	public function wp_the_content($content) {
		$content = str_replace('[form]', $this->form_content(), $content);
		$content = str_replace('[debug]', $this->query_string(), $content);
		return $content;
	}

	public function wp_post_type_link($permalink, $post) {
		$permalink = $post->post_name;
		if($pdx_search_id = get_query_var('pdx_search_id'))
			$permalink .= $pdx_search_id;
		if($pdx_search_pg = get_query_var('pdx_search_pg'))
			$permalink .= $pdx_search_pg;

		return home_url($permalink . '/');
	}
}