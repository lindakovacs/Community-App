<?php
/**
 * Construct a table to manage custom listing page templates
 */


require_once('list-table.php');


class PL_Listing_Tpl_Table extends PL_List_Table {

	private $base_page;
	private $per_page = 20;
	private static $_items = array();
	private static $_viewcnt = array('in_use'=>0, 'inactive'=>0, 'built_in'=>0);

	public function __construct( $args = array() ) {
		global $pagenow;

		parent::__construct($args);

		$page = $_REQUEST['page'];
		$this->base_page = $pagenow.'?page='.$page;

		$this->_column_headers = array(
			array(
					//'cb' 		=> '<input type="checkbox" />',
					'title'		=> 'Name',
					'id'		=> 'Template ID',
			),
			array(),
			array(
					'title' => array('title', ''),
			),
		);
		$this->items = array();

		// make sure the sort indicator is set
		if (empty($_GET['orderby'])) {
			$_GET['orderby'] = 'title';
			$_GET['order'] = 'asc';
		}
	}

	/**
	 * Static function to fetch and hold list of templates
	 * @return array:
	 */
	private static function _fetch_items() {
		if (empty(self::$_items)) {
			$tpls = PL_Listing_Customizer::get_template_list();
			$active_template_id = PL_Listing_Customizer::get_active_template_id();
			foreach($tpls as $tpl) {
				$status = $tpl['id']==$active_template_id ? 'in_use' : 'inactive';
				self::$_viewcnt[$status]++;
				if ($tpl['type']=='default') self::$_viewcnt['built_in']++;
				self::$_items[] = array_merge($tpl, array('status'=>$status));
			}
		}
		return self::$_items;
	}

	public function prepare_items() {

		$search = (!empty($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '');
		$status = (!empty($_REQUEST['status']) ? esc_attr($_REQUEST['status']) : '');
		$type = (!empty($_REQUEST['type']) ? esc_attr($_REQUEST['type']) : '');
		$orderby = empty($_GET['orderby']) ? 'title' : $_GET['orderby'];
		$order = empty($_GET['order']) ? 'asc' : $_GET['order'];
		$order = $order=='asc' ? 'asc' : 'desc';

		// get data
		$this->items = array();
		foreach($this->_fetch_items() as $item) {
			if ((!$status || $status==$item['status']) &&
				(!$type || $type==$item['type']) &&
				(!$search || strpos($item['title'], $search)!==false)) {
				$this->items[] = $item;
			}
		}

		// sort
		if ($orderby == 'title') {
			uasort($this->items, array($this, $order=='asc' ? 'sort_by_title_asc' : 'sort_by_title_desc'));
		}

		// get page counts
		$total_items = count($this->items);
		$total_pages = ceil($total_items / $this->per_page);

		$this->set_pagination_args(array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page' => $this->per_page
			));

		// paginate the results
		$page = $this->get_pagenum();
		$page--;
		if ($page >= 0 && $page < $total_pages) {
			$this->items = array_slice($this->items, $page*$this->per_page, $this->per_page);
		}
	}

	public static function sort_by_title_asc($a, $b) {
		return strcasecmp($a['title'], $b['title']);
	}

	public static function sort_by_title_desc($a, $b) {
		return -strcasecmp($a['title'], $b['title']);
	}

	public function no_items() {
		_e("No shortcode templates found.");
	}

	public function get_views() {
		$status_links = array();

		return $status_links;
	}

	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	public function display_rows( $templates = array() ) {
		global $per_page;

		if (empty($templates)) {
			$templates = $this->items;
		}

		add_filter( 'the_title', 'esc_html' );

		foreach ( $templates as $row_id=>$template ) {
			$template['row_id'] = $row_id;
			$this->single_row( $template );
		}
	}

	public function single_row( $template ) {
		$row_class = ($template['row_id']%2 ? '' : ' alternate');
		?>
		<tr id="sc-template-<?php echo $template['row_id'] ?>" class="<?php echo $row_class ?> sc-template-<?php echo $template['type'] ?>" valign="top">
		<?php

		list( $columns, $hidden ) = $this->get_column_info();

		$edit_link = admin_url('admin.php?page=placester_shortcode_detail_edit&action=edit&id=');
		$delete_link = admin_url('admin.php?page=placester_shortcode_detail_edit&action=delete&id=');
		$copy_link = admin_url('admin.php?page=placester_shortcode_detail_edit&action=copy&id=');

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array( $column_name, $hidden ) ) {
				$style = ' style="display:none;"';
			}

			$attributes = "$class$style";

			switch ( $column_name ) {

				case 'cb':
					?>
					<th scope="row" class="check-column">
						<label class="screen-reader-text" for="cb-select-<?php echo $template['id'] ?>"><?php printf( __( 'Select %s' ), $template['title'] ) ?></label>
						<input id="cb-select-<?php echo $template['id'] ?>" type="checkbox" name="post[]" value="<?php echo $template['id'] ?>" />
					</th>
					<?php
					break;

				case 'title':
					?>
					<td <?php echo $attributes ?>><strong><?php echo $template['title']?></strong>
					<?php
					$actions = array();
					if ($template['type'] == 'custom') {
						$actions['edit'] = '<a href="' . $edit_link . $template['id'] . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
						$actions['copy'] = '<a href="' . $copy_link . $template['id'] . '" title="' . esc_attr( __( 'Duplicate this item' ) ) . '">' . __( 'Duplicate' ) . '</a>';
						if ($template['status']=='in_use') {
							$actions['delete'] =  __( 'In Use' );
						}
						else {
							$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . $delete_link . $template['id'] . "'>" . __( 'Delete Permanently' ) . "</a>";
						}
					}
					else {
						$actions['edit'] = __('Non-editable built-in template.');
						$actions['copy'] = '<a href="' . $copy_link . $template['id'].'" title="' . esc_attr( __( 'Copy this item' ) ) . '">' . __( 'Copy' ) . '</a>';
					}
					echo $this->row_actions( $actions, true );
					?>
					</td>
					<?php
					break;

				case 'id':
					?>
					<td <?php echo $attributes ?>><?php echo $template['id']?></td>
					<?php
					break;

				default:
					break;
			}

		}
		?>
		</tr>
		<?php
	}
}
