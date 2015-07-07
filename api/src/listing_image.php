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

	public function __get($name) {
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

	public function __set($name, $value) {
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
	protected $images;
	protected $index;

	protected $private;
	protected $sorted;
	protected $dirty;

	public function __construct($data, $private = false) { // internal image array
		$this->images = is_array($data) ? $data : array();
		$this->handles = array();
		$this->index = 0;

		$this->private = $private;
		$this->sorted = false;
		$this->dirty = false;
	}

	public function current()
	{
		return $this->offsetGet($this->index);
	}

	public function next()
	{
		$this->index++;
	}

	public function key()
	{
		if($this->valid())
			return $this->index;

		return null;
	}

	public function valid()
	{
		return $this->offsetExists($this->index);
	}

	public function rewind()
	{
		$this->index = 0;
	}

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

		if(!($this->images[$offset] instanceof PL_Listing_Image))
			$this->images[$offset] = new PL_Listing_Image($this->images[$offset], $this->private);

		return $this->images[$offset];
	}

	public function offsetSet($offset, $value)
	{
		if($this->private && $value instanceof PL_Listing_Image && (is_null($offset) || is_int($offset))) {
			if(!$this->sorted)
				$this->sort();

			if(is_null($offset) || (0 + $offset) >= $this->count()) {
				$this->images[] = $value;
				$this->dirty = true;
			}
			else if((0 + $offset) >= 0) {
				array_splice($this->images, $offset, 0, array($value));
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

		array_splice($this->images, $offset, 1);
		$this->dirty = true;
	}

	public function count()
	{
		return count($this->images);
	}

	protected function sort()
	{
		$sorted = array();
		while($element = array_shift($this->images))
			$sorted[$element->order ?: 0][] = $element;

		$this->images = array();
		while($element = array_shift($sorted))
			$this->images = array_merge($this->images, $element);

		$this->sorted = true;
	}
}
