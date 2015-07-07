<?php


require_once('connection.php');


class PL_Search_Result implements Countable, ArrayAccess, Iterator {
	protected $attributes;
	protected $listing_data;
	protected $listing_index;


	public function __construct($data, PL_Attributes $attributes = null) {
		$this->attributes = $attributes ?: new PL_Standard_Attributes();
		$this->listing_data = $data;
		$this->listing_index = 0;
	}

	// Placester API Fields
	public function count() {
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->count)
			return $this->listing_data->count;
		return 0;
	}
	public function total() {
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->total)
			return $this->listing_data->total;
		return 0;
	}
	public function offset() {
		if($this->listing_data && $this->listing_data->listings && $this->listing_data->offset)
			return $this->listing_data->offset;
		return 0;
	}

	public function get_listing($index = null) {
		if(is_scalar($index) && $this->valid($index))
			$this->listing_index = $index;

		$listing = $this->current();
		$this->next();

		return $listing;
	}

	// Iterator
	public function current()
	{
		return $this->offsetGet($this->listing_index);
	}
	public function next()
	{
		if($this->valid())
			$this->listing_index++;
	}
	public function key()
	{
		return $this->listing_index;
	}
	public function valid()
	{
		return $this->offsetExists($this->listing_index);
	}
	public function rewind()
	{
		$this->listing_index = 0;
	}

	// ArrayAccess -- nothing to do with the API field called offset
	public function offsetExists($offset)
	{
		return $offset >= 0 && $offset < $this->count();
	}
	public function offsetGet($offset)
	{
		if(!$this->offsetExists($offset))
			return null;

		if(!($this->listing_data->listings[$offset] instanceof PL_Listing))
			$this->listing_data->listings[$offset] = new PL_Listing($this->listing_data->listings[$offset], $this->attributes);

		return $this->listing_data->listings[$offset];
	}
	public function offsetSet($offset, $value)
	{
	}
	public function offsetUnset($offset)
	{
	}
}