<?php
/**
 * Construct a table to manage custom shortcode templates
 */
require_once PL_THIRD_PARTY_DIR . 'wp-core/class-wp-list-table.php';
class PL_Shortcodes_Table extends PL_List_Table {

	private $post_type = 'pl_general_widget';
	private $base_page;
	private $per_page = 20;
	protected $_column_headers = array();
	private $shortcode_types = array();
	private $filter = array();
	private $search = '';

	public function __construct( $args = array() ) {
		global $pagenow, $wpdb;

		// set screen to this post type
		$screen = get_current_screen();
		$screen->id = 'edit-'.$this->post_type;
		$page = $_REQUEST['page'];
		$this->base_page = $pagenow.'?page='.$page;

		$args = wp_parse_args($args, array(
				'plural' => 'posts',	// for nonce since we manage the records using wp-admin/edit.php
				'screen' => $screen,
		));

		parent::__construct($args);

		$doaction = empty($_REQUEST['action']) ? '-1' : $_REQUEST['action'];
		if ($doaction!='-1') {
			$post_ids = empty($_REQUEST['post']) ? array() : (array)$_REQUEST['post'];
			if (empty($post_ids)) {
				$doaction = '';
			}
		}
		else {
			$doaction = '';
		}
		if (isset($_REQUEST['delete_all'])) {
			$post_status = preg_replace('/[^a-z0-9_-]+/i', '', $_REQUEST['post_status']);
			if ( get_post_status_object($post_status) ) // Check the post status exists first
				$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", $this->post_type, $post_status ) );
			$doaction = 'delete';
		}
		if ($doaction) {
			$sendback = remove_query_arg( array('action', 'action2', '_status', 'post', 'bulk_edit', 'post_view'), wp_get_referer() );
			$post_type_object = get_post_type_object( $this->post_type );
			switch ( $doaction ) {
				case 'trash':
					$trashed = 0;
					foreach( (array) $post_ids as $post_id ) {
						if ( !current_user_can($post_type_object->cap->delete_post, $post_id) )
							wp_die( __('You are not allowed to move this item to the Trash.') );

						if ( !wp_trash_post($post_id) )
							wp_die( __('Error in moving to Trash.') );

						$trashed++;
					}
					$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids) ), $sendback );
					break;
				case 'untrash':
					$untrashed = 0;
					foreach( (array) $post_ids as $post_id ) {
						if ( !current_user_can($post_type_object->cap->delete_post, $post_id) )
							wp_die( __('You are not allowed to restore this item from the Trash.') );

						if ( !wp_untrash_post($post_id) )
							wp_die( __('Error in restoring from Trash.') );

						$untrashed++;
					}
					$sendback = add_query_arg('untrashed', $untrashed, $sendback);
					break;
				case 'delete':
					$deleted = 0;
					foreach( (array) $post_ids as $post_id ) {
						$post_del = & get_post($post_id);

						if ( !current_user_can($post_type_object->cap->delete_post, $post_id) )
							wp_die( __('You are not allowed to delete this item.') );

						if ( $post_del->post_type == 'attachment' ) {
							if ( ! wp_delete_attachment($post_id) )
								wp_die( __('Error in deleting...') );
						} else {
							if ( !wp_delete_post($post_id) )
								wp_die( __('Error in deleting...') );
						}
						$deleted++;
					}
					$sendback = add_query_arg('deleted', $deleted, $sendback);
					break;
				case 'edit':
					if ( isset($_REQUEST['bulk_edit']) ) {
						$done = bulk_edit_posts($_REQUEST);

						if ( is_array($done) ) {
							$done['updated'] = count( $done['updated'] );
							$done['skipped'] = count( $done['skipped'] );
							$done['locked'] = count( $done['locked'] );
							$sendback = add_query_arg( $done, $sendback );
						}
					}
					break;
			}
			wp_redirect($sendback);
			exit();
		}

		$this->items = array();
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs();
		foreach($sc_attrs as $sc=>$attrs) {
			$this->shortcode_types[$sc] = $attrs['title'];
		}

	}

	public function ajax_user_can() {
		return current_user_can( get_post_type_object( $this->post_type )->cap->edit_posts );
	}

	public function prepare_items() {
		global $wpdb, $wp_query;

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
		$status = $this->is_trash ? "= 'trash'" : "!= 'trash'";

		$type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';
		if (!empty($this->shortcode_types[$type])) {
			$where = "AND $wpdb->postmeta.meta_value = '$type'";
		}
		else {
			$where = '';
		}
		$this->filter['type'] = $type;

		$this->search = empty($_REQUEST['s']) ? '' : $_REQUEST['s'];
		if ($this->search) {
			$where .= $wpdb->prepare(" AND $wpdb->posts.post_title LIKE '%%%s%%'", $this->search);
		}

		$orderby = empty($_GET['orderby']) ? 'title' : $_GET['orderby'];
		$order = empty($_GET['order']) ? 'asc' : $_GET['order'];
		$order = $order=='asc' ? 'asc' : 'desc';
		$orderstr = $orderby == 'title' ? "ORDER BY $wpdb->posts.post_title $order" : '';
		$this->items = $wpdb->get_results("
				SELECT $wpdb->posts.ID, $wpdb->posts.post_status, $wpdb->posts.post_title AS title, $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value AS type
				FROM $wpdb->posts LEFT JOIN $wpdb->postmeta
				ON $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = 'shortcode'
				WHERE $wpdb->posts.post_type = 'pl_general_widget' $where
				AND $wpdb->posts.post_status $status
				$orderstr");

		$total_items = count($this->items);

		foreach($this->items as $item) {
			if (empty($this->shortcode_types[$item->type])) {
				$item->shortcode = 'Unknown';
			}
			else {
				$item->shortcode = $this->shortcode_types[$item->type];
			}
		}
		if ($orderby == 'type') {
			uasort($this->items, array($this, $order=='asc' ? 'sort_by_type_asc' : 'sort_by_type_desc'));
		}

		$post_type = $this->post_type;
 		$per_page = apply_filters( 'edit_posts_per_page', $this->per_page, $post_type );
		$total_pages = ceil($total_items/$per_page);

		$this->_column_headers = array(
			array(
					'cb' 		=> '<input type="checkbox" />',
					'title'		=> 'Name',
					'type'		=> 'Shortcode Type',
					'shortcode'	=> 'Shortcode/Embed Code',
			),
			array(),
			array(
					'title' => array('title', ''),
					'type' => array('type', ''),
			),
		);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page
		) );

		// paginate the results
		$page = $this->get_pagenum();
		$page--;
		if ($page >= 0 && $page < $total_pages) {
			$this->items = array_slice($this->items, $page*$per_page, $per_page);
		}
	}

	public function sort_by_type_asc($a, $b) {
		$cmp = strcmp($a->shortcode, $b->shortcode);
		if ($cmp != 0) return $cmp;
		return strcasecmp($a->title, $b->title);
	}

	public function sort_by_type_desc($a, $b) {
		$cmp = strcmp($a->shortcode, $b->shortcode);
		if ($cmp != 0) return -$cmp;
		return strcasecmp($a->title, $b->title);
	}

	public function has_items() {
		return count($this->items);
	}

	public function no_items() {
		if ( isset( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] )
			echo get_post_type_object( $this->post_type )->labels->not_found_in_trash;
		else
			echo get_post_type_object( $this->post_type )->labels->not_found;
	}

	public function get_views() {
		global $locked_post_status;

		$avail_post_stati = array('trash');
		$post_type = $this->post_type;
		$base_page = $this->base_page;

		if ( !empty($locked_post_status) )
			return array();

		$status_links = array();
		$num_posts = wp_count_posts( $post_type, 'readable' );
		$class = '';

		$total_posts = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array('show_in_admin_all_list' => false) ) as $state )
			$total_posts -= $num_posts->$state;

		$class = empty( $class ) && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';
		$status_links['all'] = '<a href="'.$base_page.'" $class>' . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( !in_array( $status_name, $avail_post_stati ) )
				continue;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			$status_links[$status_name] = "<a href='$base_page&post_status=$status_name'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}

	public function get_bulk_actions() {
		$actions = array();

		if ( $this->is_trash )
			$actions['untrash'] = __( 'Restore' );

		if ( $this->is_trash || !EMPTY_TRASH_DAYS )
			$actions['delete'] = __( 'Delete Permanently' );
		else
			$actions['trash'] = __( 'Move to Trash' );

		return $actions;
	}

	public function extra_tablenav( $which ) {
		global $cat;
?>
		<div class="alignleft actions">
<?php
		if ( 'top' == $which && !is_singular() ) {
			?>
			<select name="type">
				<option value="">Show all shortcode types</option>
				<?php foreach($this->shortcode_types as $type=>$title): ?>
				<option value="<?php echo $type ?>"<?php echo ($this->filter['type']==$type ? 'selected="selected"' : '')?>><?php echo $title ?></option>
				<?php endforeach;?>
			</select>
			<?php
			submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'pl_general_widget-query-submit' ) );
		}

		if ( $this->is_trash && current_user_can( get_post_type_object( $this->post_type )->cap->edit_posts ) ) {
			submit_button( __( 'Empty Trash' ), 'apply', 'delete_all', false );
		}
?>
		</div>
<?php
	}

	public function display_rows( $posts = array() ) {
		if (empty($posts)) {
			$posts = $this->items;
		}

		foreach ( $posts as $post )
			$this->single_row( $post );
	}

	public function single_row( $post ) {
		static $alternate;

		$post_type_object = get_post_type_object( $this->post_type );
		$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );
		$sc_options = PL_Shortcode_CPT::get_shortcode_options($post->type, $post->ID);
		if ($sc_options!==false && !empty($sc_options['width']) && !empty($sc_options['height'])) {
			$embed_str = htmlentities('<script id="plwidget-'.$post->ID.'" src="'.PL_PARENT_URL.'js/fetch-widget.js?id='.$post->ID.'" style="width:'.$sc_options['width'].'px;height:'.$sc_options['height'].'px"></script>');
			$shortcode_str = '['.$post->type." id='".$post->ID."']";
		}
		else {	
			$shortcode_str = $embed_str = '';
		}

		$alternate = 'alternate' == $alternate ? '' : 'alternate';
		$classes = $alternate;

		?>
		<tr id="sc-shortcode-<?php echo $post->ID; ?>" class="<?php echo $classes;?> sc-shortcode-<?php echo $post->type?>" valign="top">
		<?php

		list( $columns, $hidden ) = $this->get_column_info();

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
						<?php if ( $can_edit_post ) { ?>
						<label class="screen-reader-text" for="cb-select-<?php $post->ID; ?>"><?php printf( __( 'Select %s' ), $post->title ); ?></label>
						<input id="cb-select-<?php echo $post->ID; ?>" type="checkbox" name="post[]" value="<?php echo $post->ID; ?>" />
						<?php } ?>
					</th>
					<?php
					break;

				case 'title':
					?>
					<td <?php echo $attributes ?>><strong><?php echo $post->title?></strong>
					<?php
					$actions = array();
					if ( $can_edit_post && 'trash' != $post->post_status ) {
						$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
						$actions['copy'] = '<a href="' . get_edit_post_link( $post->ID ) . '&action=copy" title="' . esc_attr( __( 'Duplicate this item' ) ) . '">' . __( 'Duplicate' ) . '</a>';
					}
					if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
						if ( 'trash' == $post->post_status )
							$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-'.$this->post_type.'_' . $post->ID ) . "'>" . __( 'Restore' ) . "</a>";
						elseif ( EMPTY_TRASH_DAYS )
							$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
						if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
							$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
					}
					echo $this->row_actions( $actions, true );
					?>
					</td>
					<?php
					break;

				case 'type':
					?>
					<td <?php echo $attributes ?>><strong><?php echo $post->shortcode?></strong><br /><?php echo '['.$post->type.']'?></td>
					<?php
					break;

				case 'shortcode':
					?>
					<td <?php echo $attributes ?>><?php echo $shortcode_str;?><br /><?php echo $embed_str;?></td>
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
