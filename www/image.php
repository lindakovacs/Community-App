<?php


require_once('html.php');


class HTML_Image extends HTML_Element {
	public function __construct($src, $attributes = null) {
		parent::__construct('img');
		$this->attributes['src'] = $src;

		if($attributes) foreach($attributes as $name => $value) {
			$this->$name = $value;
		}
	}
}
