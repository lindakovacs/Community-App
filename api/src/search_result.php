<?php


require_once('connection.php');


class PL_Search_Result {
	protected $attributes;
	protected $listing_data;
	protected $listing_index;


	public function __construct($data, PL_Attributes $attributes = null) {
		$this->attributes = $attributes ?: new PL_Standard_Attributes();
		$this->listing_data = $data;
		$this->listing_index = 0;
	}

	public function get_count() {
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->count)
			return $this->listing_data->count;
		return 0;
	}
	public function get_total() {
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->total)
			return $this->listing_data->total;
		return 0;
	}
	public function get_offset() {
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->offset)
			return $this->listing_data->offset;
		return 0;
	}

	public function get_listing($index = null) {
		if($index !== null) $this->listing_index = 0 + $index;
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->count)
			return new PL_Listing($this->listing_data->listings[$this->listing_index++], $this->attributes);
		return null;
	}
}