<?php

// this is the basic class for filtering property listings on the Placester API
class PL_Page_Filter {
	protected $size;
	protected $sequence;

	public function __construct($size = 12) {
		$this->size = $size;
		$this->sequence = 0;
	}

	public function api_query() {
		return 'limit=' . $this->size . '&offset=' . $this->size * $this->sequence;
	}

	public function page($sequence) {
		$this->sequence = $sequence;
	}

	public function clear() {}
}
