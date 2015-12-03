<?php


class PL_Listing_Image {
	protected $image;
	protected $private;
	protected $dirty;

	public function __construct(stdClass $data, $private = false) { // internal image object
		$this->image = $data;
		$this->private = $private;
		$this->dirty = false;
	}

	public function __get($name) { return $this->get_value($name); }
	public function get_value($name) {
		switch($name) {
			case 'id':
			case 'url':
			case 'caption':
				return $this->image->{$name};
				break;
			case 'order':
				if($this->private)
					return $this->image->{$name};
		}
		return null;
	}

	public function __set($name, $value) { $this->set_value($name, $value); }
	public function set_value($name, $value) {
		switch($name) {
			case 'caption':
			case 'order':
				if($this->private) {
					$this->dirty = $this->dirty || $this->image->{$name} != $value;
					$this->image->{$name} = $value;
				}
				break;
		}
	}

	public function __toString() {
		return $this->image ? $this->image->url : '';
	}
}


class PL_Listing_Images implements Countable, ArrayAccess, Iterator {
	protected $image_data;
	protected $image_index;

	protected $private;
	protected $sorted;
	protected $dirty;

	public function __construct($data, $private = false) { // internal image array
		$this->image_data = is_array($data) ? $data : array();
		$this->image_index = 0;

		$this->private = $private;
		$this->sorted = false;
		$this->dirty = false;
	}

	public function count()
	{
		return count($this->image_data);
	}

	public function get_image($index = null) {
		if(is_scalar($index) && $this->valid($index))
			$this->image_index = $index;

		$image = $this->current();
		$this->next();

		return $image;
	}

	protected function sort()
	{
		$sorted = array();
		while($element = array_shift($this->image_data))
			$sorted[$element->order ?: 0][] = $element;

		$this->image_data = array();
		while($element = array_shift($sorted))
			$this->image_data = array_merge($this->image_data, $element);

		$this->sorted = true;
	}

	// Iterator
	public function current()
	{
		return $this->offsetGet($this->image_index);
	}
	public function next()
	{
		$this->image_index++;
	}
	public function key()
	{
		if($this->valid())
			return $this->image_index;

		return null;
	}
	public function valid()
	{
		return $this->offsetExists($this->image_index);
	}
	public function rewind()
	{
		$this->image_index = 0;
	}

	// ArrayAccess
	public function offsetExists($offset)
	{
		return $offset >= 0 && $offset < $this->count();
	}
	public function offsetGet($offset)
	{
		if(!$this->offsetExists($offset))
			return null;

		if(!$this->sorted)
			$this->sort();

		if(!($this->image_data[$offset] instanceof PL_Listing_Image))
			$this->image_data[$offset] = new PL_Listing_Image($this->image_data[$offset], $this->private);

		return $this->image_data[$offset];
	}
	public function offsetSet($offset, $value)
	{
		if($this->private && $value instanceof PL_Listing_Image && (is_null($offset) || is_int($offset))) {
			if(!$this->sorted)
				$this->sort();

			if(is_null($offset) || (0 + $offset) >= $this->count()) {
				$this->image_data[] = $value;
				$this->dirty = true;
			}
			else if((0 + $offset) >= 0) {
				array_splice($this->image_data, $offset, 0, array($value));
				$this->dirty = true;
			}
		}
	}
	public function offsetUnset($offset)
	{
		if(!$this->offsetExists($offset))
			return null;

		if(!$this->sorted)
			$this->sort();

		array_splice($this->image_data, $offset, 1);
		$this->dirty = true;
	}
}
