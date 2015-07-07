<?php


require_once('../../api/api.php');


class PL_Search_Request {
	protected $connection;
	protected $search_filter;
	protected $search_view;

	public function __construct(PL_API_Connection $connection = null, $request = null) {
		if(!$request)
			$request = $_REQUEST;

		if(!$connection && isset($request['api_key']))
			$connection = new PL_API_Connection($request['api_key']);

		$this->search_filter = $connection->new_search_filter($request);
		$this->search_view = $connection->new_search_view($request);
	}

	public function search_listings() {
		return $this->connection->search_listings($this->search_filter, $this->search_view);
	}
}
