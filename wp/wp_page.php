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
			$search_id = $this->get_search_id($post, $this->form->get_search_data());

			set_query_var('pdx_search_id', $search_id);
			wp_redirect($this->wp_post_type_link(null, $post), 302);
			exit();
		}

		if($search_id = get_query_var('pdx_search_id'))
			if($search_data = $this->get_search_data($post, $search_id))
				$this->form = new PL_Search_Form($this, null, $search_data);

			else {
				set_query_var('pdx_search_id', '');
				wp_redirect($this->wp_post_type_link(null, $post), 302);
				exit();
			}

		else
			$this->form = new PL_Search_Form($this, null, null);

		$this->search_request = $this->form->get_search_request();
		$this->search_request->set('offset', 12 * (get_query_var('page', 1) - 1));

		$this->search_result = $this->search_listings($this->search_request);
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

	public function the_page_content() {
		$this->wp_filtering = true;
		$content = $this->get_the_page_content();
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$this->wp_filtering = false;
		echo $content;
	}

	public function get_the_page_content() {
		return get_the_content();
	}

	public function form_content() {
		return $this->form->html_string();
	}

	public function message_content() {
		$content = '';
		$content .= '<br><br>';
		$content .= '<pre>' . $this->search_request->query_string() . '</pre>';
		$content .= '<br><br>';
		$content .= 'Showing ' . ($this->search_result->offset() + 1);
		$content .= ' - ' . ($this->search_result->offset() + $this->search_result->count());
		$content .= ' of ' . $this->search_result->total();
		$content .= '<br><br>';
		return $content;
	}

	public function listing_content() {
		$content = '';
		$content .= '<ul>';
		$content .= '[foreach:listing index=0]';
		$content .= '<li>[data attribute=address], [data attribute=locality], [data attribute=region]';
		$content .= '<br>[data attribute=beds] beds, [data attribute=baths] baths, [data attribute=price]';
		$content .= '[/foreach:listing]';
		$content .= '</ul>';
		return do_shortcode($content);
	}

	public function wp_the_content($content) {
		if($this->wp_filtering) return $content;

		$content = '';
		$content .= $this->get_the_page_content();
		$content .= $this->form_content();
		$content .= $this->message_content();
		$content .= $this->listing_content();
		return $content;
	}

	public function wp_post_type_link($permalink, $post) {
		$permalink = $post->post_name;
		if($pdx_search_id = get_query_var('pdx_search_id'))
			$permalink .= $pdx_search_id;

		return home_url($permalink . '/');
	}
}