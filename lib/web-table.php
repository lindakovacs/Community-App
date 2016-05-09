<?php


class Web_Data_Set {
	protected $args;

	public function __construct($args = array()) {
		$this->args = $args;
	}

	public function select_data($offset = 0, $limit = 0) {
	}

	public function get_args() { return $this->args; }
	public function get_total() { return 0; }
	public function get_count() { return 0; }
	public function get_data($index = null) { return null; }
}


class Web_Data_Filter {
	protected $id;
	protected $args;

	public function __construct($args = array()) {
		$this->args = $args;
	}

	public function get_args() { return $this->args; }
	public function get_query_string() {}
}


class Web_Data_Form extends Web_Data_Filter {
	protected $filters;
	protected $inputs;

	public function __construct($inputs, $args = array()) {
		$this->inputs = $inputs;
		parent::__construct($args);
	}

	public function add_filter($id, Web_Data_Filter $filter) {

	}

	public function html() {
		$html = $this->html_open();
		$html .= $this->html_inner();
		$html .= $this->html_close();
		$html .= $this->html_script();
		return $html;
	}
}


class Web_Data_Table {
	protected $data;
	protected $offset;
	protected $limit;

	protected $id;
	protected $classes;

	public function __construct(Web_Data_Set $data, $offset = 0, $limit = 0) {
		$this->id = 'ajax-data-table';
		$this->classes = 'ajax-data-table';

		$this->data = $data;
		$this->data->select_data($this->offset = $offset, $this->limit = $limit);

		$this->total = $this->data->get_total();
		$this->count = $this->data->get_count();
	}

	public function html() {
		$html = $this->html_open();
		$html .= $this->html_inner();
		$html .= $this->html_close();
		$html .= $this->html_script();
		return $html;
	}

	public function html_inner() {
		$html = '';
		if($this->total) {
			$html .= '<div class="ajax-table-message">';
			$html .= $this->html_message($this->total, $this->offset, $this->limit, $this->count);
		}
		else {
			$html .= '<div class="ajax-table-message ajax-table-empty">' . "\n";
			$html .= $this->html_empty();
		}
		$html .= '</div>' . "\n";

		if($this->total) {
			$html .= $this->html_header();
			while ($row_data = $this->data->get_data())
				$html .= $this->html_format($row_data);
			$html .= $this->html_footer();

			$html .= $this->html_nav_form($this->data->get_args());
		}

		return $html;
	}

	protected function html_open() {
		$html = '<div';
		if($this->id) $html .= ' id="' . $this->id . '"';
		if($this->classes) $html .= ' class="' . (is_array($this->classes) ? implode(' ', $this->classes) : $this->classes) . '"';
		$html .= ">\n";

		return $html;
	}
	protected function html_close() {
		return "</div>\n";
	}

	protected function html_empty() { return '<span>No records found.</span>'; }
	protected function html_message($total, $offset, $limit, $count) {
		$html = '<span>' . $total . ' record' . ($total > 1 ? 's' : '') . ' found.';
		if($total > $count && $count > 0) {
			$html .= '  Showing ' . ($offset + 1);
			if ($count > 1)
				$html .= ' - ' . ($offset + $count);
			$html .= '.';
		}
		$html .= '</span>';
		return $html;
	}

	protected function html_header() { return '<table class="ajax-table-table"><thead></thead><tbody>' . "\n"; }
	protected function html_format($row_data) { return '<tr><td>' . implode('</td><td>', $row_data) . '</td></tr>'; }
	protected function html_footer() { return '</tbody><tfoot></tfoot></table>' . "\n"; }

	protected function html_nav_form($args) {
		$html = '<form class="ajax-table-form" method="post">';
		$html .= '<input class="ajax-table-action" type="hidden" name="action" value="listings_table_update">';

		foreach($args as $name => $value)
			$html .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';

		if($this->limit) {
			$html .= '<input class="ajax-table-limit" type="hidden" name="limit" value="' . $this->limit . '">';
			$html .= $this->html_nav_buttons();
		}

		$html .= '</form>';
		return $html;
	}

	protected function html_nav_buttons() {
		$html = $this->html_first_button() . '&nbsp;';
		$html .= $this->html_prev_button() . '&nbsp;';
		$html .= $this->html_page_buttons() . '&nbsp;';
		$html .= $this->html_next_button() . '&nbsp;';
		$html .= $this->html_last_button();
		return $html;
	}

	protected function html_nav_button($id, $offset, $text) {
		return '<button id="ajax-table-' . $id . '" class="ajax-table-button" type="submit" name="offset" value="'
			. $offset . ($offset == $this->offset ? '" disabled' : '"') . '>' . $text . '</button>';
	}

	protected function html_first_button() {
		return $this->html_nav_button('first', 0, '&lt;&lt;');
	}

	protected function html_last_button() {
		if($this->limit && $this->total)
			$offset = $this->total - ($this->total % $this->limit ?: $this->limit);
		else
			$offset = 0;

		return $this->html_nav_button('last', $offset, '&gt;&gt;');
	}

	protected function html_prev_button() {
		if($this->limit && $this->offset)
			$offset = $this->offset - ($this->offset % $this->limit ?: $this->limit);
		else
			$offset = 0;

		return $this->html_nav_button('prev', $offset, '&lt;');
	}

	protected function html_next_button() {
		if($this->limit && $this->offset + $this->limit < $this->total)
			$offset = $this->offset + ($this->limit - $this->offset % $this->limit);
		else
			$offset = $this->offset;

		return $this->html_nav_button('next', $offset, '&gt;');
	}

	protected function html_page_buttons($n = 5) {
		if(!$this->total || !$this->limit)
			return $this->html_page_button(1);

		$h = (int) floor($n / 2);
		$p = (int) ceil($this->offset / $this->limit) + 1;

		$max = (int) ceil($this->total / $this->limit);
		$p1 = max(1, min($p - $h, $max - 2 * $h));
		$p2 = min($max, max($p + $h, 1 + 2 * $h));

		$buttons = array();
		for($i = $p1; $i <= $p2; ++$i)
			$buttons[] = $this->html_page_button($i);

		return implode('&nbsp;', $buttons);
	}

	protected function html_page_button($page) {
		$offset = $this->limit * ($page - 1);

		if($offset >= 0 && $offset < $this->total)
			return $this->html_nav_button('ajax-table-page-' . $page, $this->limit * ($page - 1), $page);
		else
			return '';
	}

	protected function html_script() {
		$ajax_url = admin_url('admin-ajax.php');
		$selector = ".ajax-table-form";
		$container = '#' . $this->id;

		$html = '<script>';
		$html .= <<<"SCRIPT"

jQuery(document).ready(function () {
	function ajax_table_refresh(offset) {
		var postdata = jQuery("$selector").serialize();
		if(offset) postdata += '&offset=' + offset;

		var container = jQuery("$container");
		container.addClass("updating");

		jQuery.post("$ajax_url", postdata, function (response) {
			container.html(response);
			container.removeClass("updating");

			ajax_table_activate();
		});
	}

	function ajax_table_activate() {
		jQuery(".ajax-table-button").click(function (e) {
			e.preventDefault();
			ajax_table_refresh(jQuery(this).val());
		});
	}

	ajax_table_activate();
});

SCRIPT;
		$html .= '</script>';
		return $html;
	}
}


class Listing_Data_Set extends Web_Data_Set {
	protected $listings;
	protected $data;
	protected $index;

	public function __construct($args) {
		parent::__construct($args);

		$this->data = array();
		$this->index = 0;
	}

	public function select_data($offset = 0, $limit = 0) {
		$this->listings = PL_Listing_Helper::results(array_merge(array('offset' => $offset, 'limit' => $limit), $this->args));
		$this->data = array();
		$this->index = 0;


		foreach($this->listings['listings'] as $listing) {
			$location = $listing['location'];
			$this->data[] = array($location['full_address'], $location['postal'], $location['neighborhood']);
		}
	}

	public function get_total() { return $this->listings['total']; }
	public function get_count() { return count($this->data); }

	public function get_data($index = null) {
		if(is_scalar($index))
			$this->index = intval($index);

		return $this->data[$this->index++];
	}
}


class Web_Listing_Table extends Web_Data_Table {
}


add_action('wp_enqueue_scripts', 'update_table_enqueue');
function update_table_enqueue() {
	wp_enqueue_script('jquery');
}

add_shortcode('listings_table', 'placester_insert_table');
function placester_insert_table($args = array()) {
	if($_REQUEST['action'] == 'listings_table_update')
		$args = $_REQUEST;
	else if(!is_array($args))
		$args = array();

	$offset = is_scalar($args['offset']) ? intval($args['offset']) : 0;
	$limit = is_scalar($args['limit']) ? intval($args['limit']) : 0;

	unset($args['action']);
	unset($args['offset']);
	unset($args['limit']);
	$data = new Listing_Data_Set($args);

	$table = new Web_Listing_Table($data, $offset, $limit);
	return $table->html();
}

add_action('wp_ajax_listings_table_update', 'placester_update_table');
add_action('wp_ajax_nopriv_listings_table_update', 'placester_update_table');
function placester_update_table() {
	$args = $_REQUEST;

	$offset = is_scalar($args['offset']) ? intval($args['offset']) : 0;
	$limit = is_scalar($args['limit']) ? intval($args['limit']) : 0;

	unset($args['action']);
	unset($args['offset']);
	unset($args['limit']);
	$data = new Listing_Data_Set($args);

	$table = new Web_Listing_Table($data, $offset, $limit);
	echo $table->html_inner();
	die();
}
