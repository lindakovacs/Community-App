<?php

PL_Favorite_Listings::init();
class PL_Favorite_Listings {

	public static function init () {
		add_action('wp_ajax_get_favorite_properties', array(__CLASS__, 'ajax_get_favorite_properties'));
		add_action('wp_ajax_add_favorite_property', array(__CLASS__,'ajax_add_favorite_property'));
		add_action('wp_ajax_remove_favorite_property', array(__CLASS__,'ajax_remove_favorite_property'));

		add_shortcode('favorite_link_toggle', array(__CLASS__,'placester_favorite_link_toggle'));
	}

	public static function get_favorite_properties () {
		static $favorites = false;
		if($favorites !== false) return $favorites;

		// Check for locally saved favorites first...
		$favorites = get_user_option('pl_member_listings');

		// ...else try the Placester API
		if(!$favorites) {
			$lead = PL_People_Helper::person_details();
			if (isset($lead['fav_listings']) && is_array($lead['fav_listings'])) {
				$api_favorites = $lead['fav_listings'];

				// And save them locally from now on
				foreach ($api_favorites as $item) {
					$favorites[] = $item['id'];
				}
				update_user_option(get_current_user_id(), 'pl_member_listings', $favorites);
			}

			// None found
			else {
				$favorites = array();
			}
		}

		return $favorites;
	}

	public static function update_favorite_properties ($favorites) {
		if (!current_user_can('manage_options'))
			PL_People::update(array('id' => get_user_option('placester_api_id'), 'fav_listing_ids' => $favorites));

		return update_user_option(get_current_user_id(), 'pl_member_listings', $favorites);
	}

	public static function add_favorite_property ($property_id) {
		$favorites = self::get_favorite_properties();
		foreach ($favorites as $i => $id) {
			if($id == $property_id) unset($favorites[$i]);	// prevent duplicates
		}
		array_unshift($favorites, $property_id);
		return self::update_favorite_properties($favorites);
	}

	public static function remove_favorite_property ($property_id) {
		$favorites = self::get_favorite_properties();
		foreach ($favorites as $i => $id) {
			if($id == $property_id) unset($favorites[$i]);
		}
		return self::update_favorite_properties($favorites);
	}

	public static function is_favorite_property ($property_id) {
		$favorites = self::get_favorite_properties();
		return in_array($property_id, $favorites);
	}

	/*
	 * AJAX Endpoints...
	 */
	public static function ajax_get_favorite_properties () {
		echo json_encode(self::get_favorite_properties());
		die();
	}

	public static function ajax_add_favorite_property () {
		if ($_POST['property_id'] && self::add_favorite_property($_POST['property_id'])) {
			echo json_encode(array('id' => $_POST['property_id']));
		}
		else {
			echo json_encode(array('error' => 'Unable to add favorite.'));
		}
		die();
	}

	public static function ajax_remove_favorite_property () {
		if ($_POST['property_id'] && self::remove_favorite_property($_POST['property_id'])) {
			echo json_encode(array('id' => $_POST['property_id']));
		}
		else {
			echo json_encode(array('error' => 'Unable to remove favorite.'));
		}
		die();
	}

	/*
	 * Adds "Add to/Remove from favorites" links and registration hook
	 */
	public static function placester_favorite_link_toggle ($args) {
		$defaults = array(
			'property_id' => null,
			'wrapping_div' => true,
			'add_text' => 'Add property to favorites',
			'remove_text' => 'Remove property from favorites',
			'spinner' => admin_url('images/wpspin_light.gif')
		);

		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);
		$is_favorite = is_user_logged_in() && $property_id ? self::is_favorite_property($property_id) : false;

		ob_start();
?>
		<?php pls_do_atomic( 'before_add_to_fav' ); ?>
		<?php if (is_user_logged_in()): ?>
			<?php pls_do_atomic( 'before_add_to_fav_registered' ); ?>
			<a href="<?php echo "#" . $property_id ?>" id="pl_add_favorite" class="pl_prop_fav_link" <?php echo $is_favorite ? 'style="display:none;"' : '' ?>><?php echo $add_text ?></a>
			<a href="<?php echo "#" . $property_id ?>" id="pl_remove_favorite" class="pl_prop_fav_link" <?php echo !$is_favorite ? 'style="display:none;"' : '' ?>><?php echo $remove_text ?></a>
			<img class="pl_spinner" src="<?php echo $spinner ?>" alt="ajax-spinner" style="display:none; margin-left:5px;">
		<?php else: ?>
			<?php pls_do_atomic( 'before_add_to_fav_unregistered' ); ?>
			<a class="pl_register_lead_favorites_link" href="#pl_lead_register_form"><?php echo $add_text ?></a>
		<?php endif ?>
		<?php pls_do_atomic( 'after_add_to_fav' ); ?>
<?php
		$contents = ob_get_clean();

		if ($wrapping_div == true) {
			$contents = '<div id="pl_add_remove_lead_favorites" class="pl_add_remove_lead_favorites" style="display:none;">' . $contents . '</div>';
		}
		return $contents;
	}
}
