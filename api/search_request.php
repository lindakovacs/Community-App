<?php


require_once('search_filter.php');
require_once('search_view.php');


class PL_Search_Request extends PL_Search_Filter {
	protected $view;

	public function __construct(PL_API_Connection $connection) {
		parent::__construct($connection);
		$view = new PL_Search_View($connection);
	}

	public function get_request_options() {}
	public function get_request_options_array($fill_value = null) {}

	public function get_view() {}
	public function get_filter() {}

	public function get_view_options() { return $this->view->get_view_options(); }
	public function get_view_options_array($fill_value = null) { return $this->view->get_view_options_array($fill_value); }
	public function get_view_option_values($option) { return $this->view->get_view_option_values($option); }

	public function get_filter_option_values($option, PL_Search_Filter $filter = null) {}
	public function get_request_option_values($option, PL_Search_Filter $filter = null) {}


	public function get($name) {}
	public function set($name, $value) {}

	public function query_string() {}
}

