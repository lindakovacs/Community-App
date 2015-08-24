<?php
/**
 * Based on WP Terms List Table class.
 * 
 */
require_once PL_THIRD_PARTY_DIR . 'wp-core/class-wp-list-table.php';
class PL_Property_Terms_Table extends PL_List_Table {

	var $callback_args;
	private $base_page;
	private $action = '';
	private $taxonomy = '';
	private $post_type = 'property';
	
	function __construct($args = array()) {
		global $pagenow;
		$args = wp_parse_args($args, array(
			'taxonomy' => ''
		));
		if (!taxonomy_exists($args['taxonomy'])) {
			wp_die(__('Invalid taxonomy'));
		}
		parent::__construct($args);

		$page = $_REQUEST['page'];
		$this->base_page = admin_url($pagenow.'?page='.$page);
		$this->taxonomy = $args['taxonomy'];
		$this->_column_headers = array(
			array(
				'cb'          => '<input type="checkbox" />',
				'name'        => _x('Name', 'term name'),
				'description' => __('Description'),
				'slug'        => __('Slug'),
			),
			array(),
			array(
				'name' => array('title', ''),
				'slug' => array('slug', ''),
			),
		);
	}

	function ajax_user_can() {
		return current_user_can(get_taxonomy($this->taxonomy)->cap->manage_terms);
	}

	function prepare_items() {
		$tags_per_page = $this->get_items_per_page('edit_' . $this->taxonomy . '_per_page');
		$search = !empty($_REQUEST['s']) ? trim(stripslashes($_REQUEST['s'])) : '';

		$args = array(
			'search' => $search,
			'page' => $this->get_pagenum(),
			'number' => $tags_per_page,
		);

		if (!empty($_REQUEST['orderby']))
			$args['orderby'] = trim(stripslashes($_REQUEST['orderby']));

		if (!empty($_REQUEST['order']))
			$args['order'] = trim(stripslashes($_REQUEST['order']));

		$this->callback_args = $args;

		$this->set_pagination_args(array(
			'total_items' => wp_count_terms($this->taxonomy, compact('search')),
			'per_page' => $tags_per_page,
		));
	}

	function has_items() {
		return true;
	}

	function get_bulk_actions() {
		$actions = array();
		$actions['delete'] = __('Delete');
		return $actions;
	}
	function current_action() {
		if (isset($_REQUEST['action']) && isset($_REQUEST['delete_tags']) && ('delete' == $_REQUEST['action'] || 'delete' == $_REQUEST['action2'])) {
			return 'bulk-delete';
		}
		return parent::current_action();
	}

	function display_rows_or_placeholder() {
		$taxonomy = $this->taxonomy;

		$args = wp_parse_args($this->callback_args, array(
			'page' => 1,
			'number' => 20,
			'search' => '',
			'hide_empty' => 0
		));
		extract($args, EXTR_SKIP);

		$args['offset'] = $offset = ($page - 1) * $number;

		// convert it to table rows
		$count = 0;
		$terms = array();

		if (is_taxonomy_hierarchical($taxonomy) && !isset($orderby)) {
			// We'll need the full set of terms then.
			$args['number'] = $args['offset'] = 0;
		}
		$terms = get_terms($taxonomy, $args);
		if (empty($terms)) {
			list($columns, $hidden) = $this->get_column_info();
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		if (is_taxonomy_hierarchical($taxonomy) && !isset($orderby)) {
			if (!empty($search)) // Ignore children on searches.
				$children = array();
			else
				$children = $this->_get_term_hierarchy($taxonomy);

			// Some funky recursion to get the job done(Paging & parents mainly) is contained within, Skip it for non-hierarchical taxonomies for performance sake
			$this->_rows($taxonomy, $terms, $children, $offset, $number, $count);
		} else {
			$terms = get_terms($taxonomy, $args);
			foreach ($terms as $term)
				$this->single_row($term);
			$count = $number; // Only displaying a single page.
		}
	}
	
	function _get_term_hierarchy() {
		if (!is_taxonomy_hierarchical($this->taxonomy))
			return array();
		$children = get_option("{$this->taxonomy}_children");
	
		if (is_array($children))
			return $children;
		$children = array();
		$terms = get_terms($this->taxonomy, array('get' => 'all', 'orderby' => 'id', 'fields' => 'id=>parent'));
		foreach ($terms as $term_id => $parent) {
			if ($parent > 0)
				$children[$parent][] = $term_id;
		}
		update_option("{$this->taxonomy}_children", $children);
	
		return $children;
	}

	function _rows($taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0) {
		$end = $start + $per_page;
		
		foreach ($terms as $key => $term) {
			if ($count >= $end)
				break;
			if ($term->parent != $parent && empty($_REQUEST['s']))
				continue;

			// If the page starts in a subtree, print the parents.
			if ($count == $start && $term->parent > 0 && empty($_REQUEST['s'])) {
				$my_parents = $parent_ids = array();
				$p = $term->parent;
				while ($p) {
					$my_parent = get_term($p, $taxonomy);
					$my_parents[] = $my_parent;
					$p = $my_parent->parent;
					if (in_array($p, $parent_ids)) // Prevent parent loops.
						break;
					$parent_ids[] = $p;
				}
				unset($parent_ids);

				$num_parents = count($my_parents);
				while ($my_parent = array_pop($my_parents)) {
					echo "\t";
					$this->single_row($my_parent, $level - $num_parents);
					$num_parents--;
				}
			}

			if ($count >= $start) {
				echo "\t";
				$this->single_row($term, $level);
			}
			++$count;

			unset($terms[$key]);

			if (isset($children[$term->term_id]) && empty($_REQUEST['s']))
				$this->_rows($taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1);
		}
	}

	function single_row($item, $level = 0) {
		static $row_class = '';
		$row_class = ($row_class == '' ? ' class="alternate"' : '');

		$this->level = $level;

		echo '<tr id="tag-' . $item->term_id . '"' . $row_class . '>';
		list($columns, $hidden) = $this->get_column_info();
		
		foreach ($columns as $column_name => $column_display_name) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if (in_array($column_name, $hidden))
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ('cb' == $column_name) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb($item);
				echo '</th>';
			}
			elseif (method_exists($this, 'column_' . $column_name)) {
				echo "<td $attributes>";
				echo call_user_func(array(&$this, 'column_' . $column_name), $item);
				echo "</td>";
			}
			else {
				echo "<td $attributes>";
				echo $this->column_default($item, $column_name);
				echo "</td>";
			}
		}
		echo '</tr>';
	}

	function column_cb($tag) {
		$default_term = get_option('default_' . $this->taxonomy);

		if (current_user_can(get_taxonomy($this->taxonomy)->cap->delete_terms) && $tag->term_id != $default_term) {
			return '<label class="screen-reader-text" for="cb-select-' . $tag->term_id . '">' . sprintf(__('Select %s'), $tag->name) . '</label>'
				. '<input type="checkbox" name="delete_tags[]" value="' . $tag->term_id . '" id="cb-select-' . $tag->term_id . '" />';
		}
		return '&nbsp;';
	}

	function column_name($tag) {
		$taxonomy = $this->taxonomy;
		$tax = get_taxonomy($taxonomy);

		$default_term = get_option('default_' . $taxonomy);

		$pad = str_repeat('&#8212; ', max(0, $this->level));
		$name = apply_filters('term_name', $pad . ' ' . $tag->name, $tag);
		$qe_data = get_term($tag->term_id, $taxonomy, OBJECT, 'edit');
		$edit_link = $this->base_page . "&action=edit&taxonomy=$taxonomy&tag_ID={$tag->term_id}";

		$out = '<strong><a class="row-title" href="' . $edit_link . '" title="' . esc_attr(sprintf(__('Edit &#8220;%s&#8221;'), $name)) . '">' . $name . '</a></strong><br />';

		$actions = array();
		if (current_user_can($tax->cap->edit_terms)) {
			$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
		}
		if (current_user_can($tax->cap->delete_terms) && $tag->term_id != $default_term)
			$actions['delete'] = "<a class='delete-tag' href='" . wp_nonce_url($this->base_page."&amp;action=delete&amp;taxonomy=$taxonomy&amp;tag_ID=$tag->term_id", 'delete-tag_' . $tag->term_id) . "'>" . __('Delete') . "</a>";
		$actions['view'] = '<a href="' . get_term_link($tag) . '">' . __('View') . '</a>';

		$actions = apply_filters('tag_row_actions', $actions, $tag);
		$actions = apply_filters("{$taxonomy}_row_actions", $actions, $tag);

		$out .= $this->row_actions($actions);
		$out .= '<div class="hidden" id="inline_' . $qe_data->term_id . '">';
		$out .= '<div class="name">' . $qe_data->name . '</div>';
		$out .= '<div class="slug">' . apply_filters('editable_slug', $qe_data->slug) . '</div>';
		$out .= '<div class="parent">' . $qe_data->parent . '</div></div>';

		return $out;
	}

	function column_description($tag) {
		return $tag->description;
	}

	function column_slug($tag) {
		return apply_filters('editable_slug', $tag->slug);
	}

	function column_posts($tag) {
		$count = number_format_i18n($tag->count);

		$tax = get_taxonomy($this->taxonomy);

		$ptype_object = get_post_type_object($this->post_type);
		if (! $ptype_object->show_ui)
			return $count;

		if ($tax->query_var) {
			$args = array($tax->query_var => $tag->slug);
		} else {
			$args = array('taxonomy' => $tax->name, 'term' => $tag->slug);
		}

		if ('post' != $this->post_type)
			$args['post_type'] = $this->post_type;

		if ('attachment' == $this->post_type)
			return "<a href='" . esc_url (add_query_arg($args, 'upload.php')) . "'>$count</a>";

		return "<a href='" . esc_url (add_query_arg($args, 'edit.php')) . "'>$count</a>";
	}

	function column_links($tag) {
		$count = number_format_i18n($tag->count);
		if ($count)
			$count = "<a href='link-manager.php?cat_id=$tag->term_id'>$count</a>";
		return $count;
	}

	function column_default($tag, $column_name) {
		return apply_filters("manage_{$this->taxonomy}_custom_column", '', $column_name, $tag->term_id);
	}
}
