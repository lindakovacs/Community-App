<?php


require_once(BUILDER_DIR . 'api/search_context.php');
require_once('form.php');


class PL_Page_Context extends PL_Search_Context {
	public function get_request_options() {
		return parent::get_request_options();
	}

	public function get_request_option_label($request_option) {
		$filter_options = $this->get_connection()->get_filter_attributes();
		if(!($attribute = $filter_options[$request_option]))
			return null;

		$label = $attribute->display_name;
		if(strpos($request_option, 'min_') === 0)
			$label = 'Min ' . $label;
		else if(strpos($request_option, 'max_') === 0)
			$label = 'Max ' . $label;
	}

	public function get_request_option_values($request_option) {
		return parent::get_request_option_values($request_option);
	}

	public function get_request_option_checkbox($request_option, $checkbox_config = null) {
		return null;
	}

	public function get_request_option_input($request_option, $input_config = null) {
		$label = true; $value = null; $datalist = false;
		if(is_array($input_config))
			extract($input_config, EXTR_IF_EXISTS);

		$filter_options = $this->get_connection()->get_filter_attributes();
		if(!($attribute = $filter_options[$request_option]))
			return null;

		if($label === true) {
			$label = $attribute->display_name;
			if(strpos($request_option, 'min_') === 0)
				$label = 'Min ' . $label;
			else if(strpos($request_option, 'max_') === 0)
				$label = 'Max ' . $label;
		}

		if(!is_array($datalist)) {
			if($datalist)
				$datalist = $this->get_request_option_values($attribute->name);
			else
				$datalist = array();
		}

		$element = new PL_Text_Input($request_option, $label, $value);
		foreach($datalist as $option)
			$element->add_option($option);

		return $element;
	}

	public function get_request_option_select($request_option, $select_config = null) {
		$type = 'select'; $label = true; $options = true; $selected = null; $display_values = array();
		if(is_array($select_config))
			extract($select_config, EXTR_IF_EXISTS);

		$filter_options = $this->get_connection()->get_filter_attributes();
		if(!($attribute = $filter_options[$request_option]))
			return null;

		if($label === true) {
			$label = $attribute->display_name;
			if(strpos($request_option, 'min_') === 0)
				$label = 'Min ' . $label;
			else if(strpos($request_option, 'max_') === 0)
				$label = 'Max ' . $label;
		}

		if(!is_array($options) && $options)
			$options = $this->get_request_option_values($attribute->name);

		if($options)
			$display_values = array_replace(array_combine($options, $options), $display_values);
		else
			$display_values = $options = array();

		switch($type) {
			case 'select':
				$menu = new PL_Select($request_option, $label, false);
				$menu->add_option('', 'Any', true);
				break;
			case 'multiple':
				$menu = new PL_Select($request_option, $label, true);
				break;
			case 'radio':
				$menu = new PL_Radio_Select($request_option, $label);
				$menu->add_option('', 'Any', true);
				break;
			case 'checkbox':
				$menu = new PL_Checkbox_Select($request_option, $label);
				break;
			default:
				return null;
		}

		foreach($options as $option)
			$menu->add_option($option, $display_values[$option], false);

		return $menu;
	}
}


class PL_Page_Content extends HTML_Div {}


abstract class PL_Page {
	protected $page_context;
	protected $form;

	protected $search_id;
	protected $search_pg;

	protected $search_request;
	protected $search_result;


	protected function __construct(PL_Page_Context $page_context, $search_id = null, $search_pg = null) {
		$this->page_context = $page_context;
		$this->search_id = $search_id;
		$this->search_pg = $search_pg;

		// process incoming form values and redirect as a GET request with a search id
		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->form = new PL_Active_Search_Form($this->page_context, $this->get_form_template(), $_REQUEST);
			$this->redirect($this->get_search_id($this->form->get_search_data())); // exit
		}

		// check for extraneous page number (only to make canonical)
		$search_offset = $this->get_search_offset($this->search_pg);
		if($search_offset == 0 && $this->search_pg !== '' && $this->search_pg !== null)
			$this->redirect($this->search_id); // 301?

		// look up an incoming search id
		if($this->search_id) {
			$search_data = $this->get_search_data($this->search_id);

			// the search data is no longer available, so redirect to a fresh page
			if(!is_array($search_data))
				$this->redirect(); // 404?

			// fill in the form with appropriate values
			$this->form = new PL_Active_Search_Form($this->page_context, $this->get_form_template(), $search_data);

			// check for updated search id -- e.g. we need to update an old bookmark
			if($this->search_id != ($new_search_id = $this->get_search_id($this->form->get_search_data())))
				$this->redirect($new_search_id); // 301?
		}

		// fresh search, fresh form
		else
			$this->form = new PL_Active_Search_Form($this->page_context, $this->get_form_template(), null);


		// set up the request and result set for this page
		$this->search_request = $this->form->get_search_request();
		$this->search_request->set('offset', $search_offset);

		$this->search_result = $this->page_context->search_listings($this->search_request);


		// check for overly high page number (only to make canonical)
		if($this->search_pg && $search_offset >= ($search_total = $this->search_result->total()))
			$this->redirect($this->search_id, $this->get_search_pg($search_total - 1));
	}

	abstract protected function get_search_id($search_data);
	abstract protected function get_search_data($search_id);

	protected function get_search_pg($search_offset, $search_limit = null) {
		if(!$search_limit)
			$search_limit = 12;

		$pg = intval($search_offset / $search_limit);
		return $pg ? '/' . ++$pg : null;
	}

	protected function get_search_offset($search_pg, $search_limit = null) {
		if(!$search_limit)
			$search_limit = 12;

		$pg = intval(ltrim($search_pg, '/'));
		return $pg ? $search_limit * --$pg : 0;
	}

	abstract protected function get_url($search_id = null, $search_pg = null);
	abstract protected function redirect($search_id = null, $search_pg = null, $code = 302);

	public function get_base_url() {
		return $this->get_url();
	}

	public function get_current_page_url() {
		return $this->get_url($this->search_id, $this->search_pg);
	}

	public function get_first_page_url() {
		return $this->get_url($this->search_id);
	}

	public function get_previous_page_url() {
		$previous_offset = $this->search_result->offset() - $this->search_result->limit();

		if($previous_offset >= 0)
			return $this->get_url($this->search_id, $this->get_search_pg($previous_offset));
		else
			return null;
	}

	public function get_next_page_url() {
		$next_offset = $this->search_result->offset() + $this->search_result->limit();

		if($next_offset < $this->search_result->total())
			return $this->get_url($this->search_id, $this->get_search_pg($next_offset));
		else
			return null;
	}

	public function get_last_page_url() {
		$last_page = $this->get_search_pg($this->search_result->total() - 1);
		return $this->get_url($this->search_id, $last_page);
	}

	abstract protected function get_content_internal();
	abstract protected function set_content_internal($content);

	public function get_content() {
		return $this->get_content_internal();
	}

	protected function get_template_partial($template, $partial, $name = null, $inner = false) {
		$pattern = get_shortcode_regex(); $matches = array();

		if(preg_match_all('/'. $pattern .'/s', $template, $matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE))
			foreach($matches as $match)
				if($match[2][0] == $partial) {
					if($name) {
						$params = shortcode_parse_atts($match[3][0]);
						if($params['name'] != $name)
							continue;
					}
					return $match[$inner ? 5 : 0][0];
				}

		return null;
	}

	public function get_content_partial($name) {
		$content = $this->get_template_partial($this->get_content_internal(), 'content', $name);
		return $content;
	}

	protected function get_form_template() {
		if($content = $this->get_template_partial($this->get_content_internal(), 'content', 'search-form', true))
			if($content = $this->get_template_partial($content, 'form'))
				return $content;

		return null;
	}

	public function get_form_content() {
		return $this->form ? $this->form->html_string() : '';
	}

	public function get_query_string() {
		return $this->search_request ? $this->search_request->query_string() : '';
	}
}