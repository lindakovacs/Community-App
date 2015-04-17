<?php


class PL_Search_Page {
	protected $limit;
	protected $offset;

	public function __construct() {
		$this->limit = 12;
		$this->offset = 0;
	}

	public function get_page_options() {
		return array('offset', 'limit');
	}

	public function set($name, $value) {
		if($name == 'offset') {
			$this->offset = $value;
		}
		else if($name == 'limit') {
			$this->limit = $value;
		}
	}

	public function query_string() {
		$limit = $this->limit ? 'limit=' . $this->limit : '';
		$offset = $this->offset ? 'offset=' . $this->offset : '';
		$amp = $limit && $offset ? '&' : '';

		return $limit . $amp . $offset;
	}
}
