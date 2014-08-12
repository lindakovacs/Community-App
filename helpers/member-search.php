<?php

PL_User_Saved_Search::init();
class PL_User_Saved_Search {

	public static function init () {
		// AJAX endpoints for attaching saved searches to users
		add_action('wp_ajax_get_favorite_search', array(__CLASS__, 'ajax_get_favorite_search'));
		add_action('wp_ajax_save_favorite_search', array(__CLASS__,'ajax_save_favorite_search'));
		add_action('wp_ajax_clear_favorite_search', array(__CLASS__, 'ajax_clear_favorite_search'));
		add_action('wp_ajax_configure_favorite_search', array(__CLASS__, 'ajax_configure_favorite_search'));
	}

	public static function get_favorite_search () {
		return get_user_option('pl_member_search');
	}

	public static function save_favorite_search ($search_url) {
		return update_user_option(get_current_user_id(), 'pl_member_search', $search_url);
	}

	public static function clear_favorite_search () {
		return delete_user_option(get_current_user_id(), 'pl_member_search');
	}

	public static function configure_favorite_search ($options) {
		return true;
	}

	public static function ajax_get_favorite_search () {
		echo json_encode(array('url' => self::get_favorite_search()));
		die();
	}

	public static function ajax_save_favorite_search () {
		if ($_POST['search_url'] && self::save_favorite_search($_POST['search_url'])) {
			echo json_encode(array('url' => $_POST['search_url']));
		}
		else {
			echo json_encode(array('error' => 'Unable to save search.'));
		}
		die();
	}

	public static function ajax_clear_favorite_search () {
		$search_url = self::get_favorite_search();
		if (self::clear_favorite_search()) {
			echo json_encode(array('url' => $search_url));
		}
		else {
			echo json_encode(array('error' => 'Unable to clear saved search.'));
		}
		die();
	}

	public static function ajax_configure_favorite_search () {
		$search_url = self::get_favorite_search();
		echo json_encode(array('url' => $search_url));
		die();
	}

	/*
	 * Adds "Set as/Update My Search" links and registration hook
	 */
	public static function placester_my_search_link_toggle ($args) {
		$defaults = array(
			'search_url' => null,
			'wrapping_div' => false,
			'set_text' => 'Set as My Saved Search',
			'update_text' => 'Update My Saved Search',
			'configure_text' => 'My Saved Search Options',
			'spinner' => admin_url('images/wpspin_light.gif')
		);

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);
		$my_favorite = self::get_favorite_search();
		$no_favorite = empty($my_favorite);
		$is_favorite = !$no_favorite && $my_favorite == $search_url;

		ob_start();
		?>
		<?php pls_do_atomic( 'before_set_search' ); ?>
		<?php if (is_user_logged_in()): ?>
			<?php pls_do_atomic( 'before_set_search_registered' ); ?>
			<a href="<?php echo "#" ?>" id="pl_set_search" class="pl_my_search_link" <?php echo !$no_favorite ? 'style="display:none;"' : '' ?>><?php echo $set_text ?></a>
			<a href="<?php echo "#" ?>" id="pl_update_search" class="pl_my_search_link" <?php echo $no_favorite || $is_favorite ? 'style="display:none;"' : '' ?>><?php echo $update_text ?></a>
			<a href="<?php echo "#" ?>" id="pl_configure_search" class="pl_my_search_link" <?php echo !$is_favorite ? 'style="display:none;"' : '' ?>><?php echo $configure_text ?></a>
			<img class="pl_spinner" src="<?php echo $spinner ?>" alt="ajax-spinner" style="display:none; margin-left:5px;">
		<?php else: ?>
			<?php pls_do_atomic( 'before_set_search_unregistered' ); ?>
			<a class="pl_lead_register_link" href="#pl_lead_register_form"><?php echo $set_text ?></a>
		<?php endif ?>
		<?php pls_do_atomic( 'after_set_search' ); ?>
		<?php
		$contents = ob_get_clean();

		if ($wrapping_div) {
			$contents = '<div id="pl_my_search_links" class="pl_my_search_links" style="display:none;">' . $contents . '</div>';
		}
		return $contents;
	}
}