<?php


require_once('connection.php');
require_once('search_filter.php');
require_once('search_view.php');


class PL_Search_Request extends PL_Search_Filter {
	protected $view;

	public function __construct(PL_API_Connection $connection, $args = null) {
		parent::__construct($connection);
		$this->view = new PL_Search_View($connection);

		if(is_array($args)) {
			foreach($args as $field => $value)
				$this->set($field, $value);
		}
	}

	public function get_view() { return clone $this->view; }
	public function set_view(PL_Search_View $view) { $this->view = clone $view; }

	public function get_filter() {
		$filter = new PL_Search_Filter($this->attributes);
		$filter->filters = $this->filters;

		$filter->empty = $this->empty;
		$filter->error = $this->error;
		$filter->closed = $this->closed;
	}

	public function set_filter(PL_Search_Filter $filter) {
		assert($this->attributes == $filter->attributes);
		$this->filters = $filter->filters;

		$this->empty = $filter->empty;
		$this->error = $filter->error;
		$this->closed = $filter->closed;
	}

	public function get_view_options() { return $this->view->get_view_options(); }
	public function get_view_options_array($fill_value = null) { return $this->view->get_view_options_array($fill_value); }
	public function get_view_option_values($option) { return $this->view->get_view_option_values($option); }

	public function get_request_options() { return array_merge($this->get_filter_options(), $this->get_view_options()); }
	public function get_request_options_array($fill_value = null) { return array_fill_keys($this->get_view_options(), $fill_value); }

	public function get_filter_option_values($option, PL_Search_Filter $filter = null) {
		$filter_options = $this->get_filter_options_array(true);
		if($filter_options[$option]) {
			if(($attribute = $this->attributes->get_attribute($option)) && $attribute->type == PL_TEXT_VALUE)
				return $this->attributes->read_attribute_values($attribute->name, $filter);
			return array();
		}

		return null;
	}

	public function get_request_option_values($option, PL_Search_Filter $filter = null) {
		$view_options = $this->get_view_options_array(true);
		if($view_options[$option])
			return $this->view->get_view_option_values($option);

		$filter_options = $this->get_filter_options_array(true);
		if($filter_options[$option])
			return $this->get_filter_option_values($option);

		return null;
	}

	public function get($name) {
		$view_options = $this->get_view_options_array(true);
		if($view_options[$name])
			return $this->view->get($name);

		$filter_options = $this->get_filter_options_array(true);
		if($filter_options[$name])
			return parent::get($name);

		return null;
	}

	public function set($name, $value) {
		$view_options = $this->get_view_options_array(true);
		if($view_options[$name])
			return $this->view->set($name, $value);

		$filter_options = $this->get_filter_options_array(true);
		if($filter_options[$name])
			return parent::set($name, $value);

		return false;
	}

	public static function combine(PL_Search_Filter $left, $right) {
		assert(is_a($left->attributes, 'PL_API_Connection'));
		assert($left->attributes == $right->attributes);

		$result = new PL_Search_Request($left->attributes);
		if(is_a($right, 'PL_Search_Filter'))
			$result->set_filter(PL_Search_Filter::combine($left, $right));
		else
			$result->set_filter($left);

		$left_view = is_a($left, 'PL_Search_Request') ? $left->get_view() : null;
		$right_view = is_a($right, 'PL_Search_Request') ? $right->get_view() : (
			is_a($right, 'PL_Search_View') ? $right : null);

		if($left_view && $right_view)
			$result->set_view(PL_Search_View::combine($left_view, $right_view));
		else
			$result->set_view($left_view ?: $right_view);

		return $result;
	}

	public function query_string() {
		if($filter_string = parent::query_string()) {
			if($view_string = $this->view->query_string()) {
				return $filter_string . '&' . $view_string;
			}

			return $filter_string;
		}

		return $this->view->query_string();
	}
}

