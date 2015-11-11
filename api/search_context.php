<?php


require_once('connection.php');
require_once('search_request.php');


class PL_Search_Context extends PL_Search_Request {
	public function __construct(PL_API_Connection $connection, PL_Search_Filter $filter = null, PL_Search_View $view = null) {
		parent::__construct($connection);
		if($filter) $this->set_filter($filter);
		if($view) $this->set_view($view);
	}

	public function get_connection() { return $this->connection; }

	public function add_filter(PL_Search_Filter $filter) {
		$this->set_filter(PL_Search_Filter::combine($this->get_filter(), $filter));
	}

	public function get_filter_option_values($option) {
		return parent::get_filter_option_values($option, $this);
	}

	public function get_request_option_values($option) {
		return parent::get_request_option_values($option, $this);
	}

	public function search_listings(PL_Search_Request $request = null) {
		$request = $request ? PL_Search_Request::combine($this, $request) : $this;
		return $this->connection->search_listings($request);
	}
}
