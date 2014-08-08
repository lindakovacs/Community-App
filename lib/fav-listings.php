<?php

PL_Favorite_Listings::init();

class PL_Favorite_Listings {

	public static function init () {
		// AJAX endpoints...
		add_action('wp_ajax_get_favorites', array(__CLASS__, 'ajax_get_favorites'));
		add_action('wp_ajax_add_favorite_property', array(__CLASS__,'ajax_add_favorite_property'));
		add_action('wp_ajax_nopriv_add_favorite_property', array(__CLASS__,'ajax_add_favorite_property'));
		add_action('wp_ajax_remove_favorite_property', array(__CLASS__,'ajax_remove_favorite_property'));

		add_shortcode('favorite_link_toggle', array(__CLASS__,'placester_favorite_link_toggle'));
	}

	/*
	 * Core functionality...
	 */

	public static function get_favorites () {
		$favorites = array();
		$lead = PL_People_Helper::person_details();

		if (isset($lead['fav_listings']) && is_array($lead['fav_listings'])) {
			$favorites = $lead['fav_listings'];
		}

		return $favorites;
	}

	public static function get_favorite_ids () {
		$fav_ids = array();

		// Retrieve favorite listings in their verbose format...
		$favs = self::get_favorites();

		if (!empty($favs) && is_array($favs)) {
			foreach ($favs as $index => $fav_array) {
				$fav_ids[] = $fav_array['id'];
			}
		}

		return $fav_ids;
	}

	public static function associate_property ($property_id) {
		$placester_person = PL_People_Helper::person_details();
		$new_favorites = array($property_id);

		if (isset($placester_person['fav_listings']) && is_array($placester_person['fav_listings'])) {
			foreach ($placester_person['fav_listings'] as $fav_listings) {
				$new_favorites[] = $fav_listings['id'];
			}
		}

		return PL_People::update(array('id' => $placester_person['id'], 'fav_listing_ids' => $new_favorites));
	}

	public static function unassociate_property ($property_id) {
		$placester_person = PL_People_Helper::person_details();
		$new_favorites = array();
		if (is_array($placester_person['fav_listings'])) {
			foreach ($placester_person['fav_listings'] as $fav_listings) {
				if ($fav_listings['id'] != $property_id) {
					$new_favorites[] = $fav_listings['id'];
				}
			}
		}

		return PL_People::update(array('id' => $placester_person['id'], 'fav_listing_ids' => $new_favorites));
	}

	public static function is_favorite_property ($property_id) {
		$fav_listing_ids = self::get_favorite_ids();
		$is_fav = false;

		if (is_array($fav_listing_ids)) {
			foreach ($fav_listing_ids as $listing_id) {
				if ($listing_id == $property_id) {
					$is_fav = true;
				}
			}
		}

		return $is_fav;
	}

	/*
	 * AJAX Endpoints...
	 */

	public static function ajax_get_favorites () {
		echo json_encode(self::get_favorites());	
		die();
	}

	public static function ajax_add_favorite_property () {
		// Check to see if user is an admin (at this point, we know the user is logged in...)
		if (current_user_can('manage_options')) {
			$val = json_encode(array('is_admin' => true));
		}
		else if ($_POST['property_id']) {
			$api_response = self::associate_property($_POST['property_id']);
			$val = json_encode($api_response);
		} 
		else {
			$val = false;
		}

		echo $val;
		die();
	}

	public static function ajax_remove_favorite_property () {
		if ($_POST['property_id']) {
			$api_response = self::unassociate_property($_POST['property_id']);
			echo json_encode($api_response);
		}

		die();
	}

	/**
	 * Adds a "Add property to favorites" link if the user is not logged in, or if the property is not in the favorite list,
	 * and a "Remove property from favorites" otherwise
	 *
	 * TODO: If logged in and not a lead, register the user as such...
	 */
	public static function placester_favorite_link_toggle ($atts) {
		$defaults = array(
			'add_text' => 'Add property to favorites',
			'remove_text' => 'Remove property from favorites',
			'spinner' => admin_url( 'images/wpspin_light.gif' ),
			'property_id' => false,
			'wrapping_div' => true
		);

		$args = wp_parse_args( $atts, $defaults );
		extract( $args, EXTR_SKIP );

		$is_lead = current_user_can('placester_lead');
		$is_favorite = is_user_logged_in() ? self::is_favorite_property($property_id) : "";

		ob_start();
		?>
		<?php pls_do_atomic( 'before_add_to_fav' ); ?>

		<?php if (is_user_logged_in()): ?>
			<?php pls_do_atomic( 'before_add_to_fav_registered' ); ?>
			<a href="<?php echo "#" . $property_id ?>" id="pl_add_favorite" class="pl_prop_fav_link" <?php echo $is_favorite ? 'style="display:none;"' : '' ?>><?php echo $add_text ?></a>
		<?php else: ?>
			<?php pls_do_atomic( 'before_add_to_fav_unregistered' ); ?>
			<a class="pl_register_lead_favorites_link" href="#pl_lead_register_form"><?php echo $add_text ?> </a>
		<?php endif ?>

		<a href="<?php echo "#" . $property_id ?>" id="pl_remove_favorite" class="pl_prop_fav_link" <?php echo !$is_favorite ? 'style="display:none;"' : '' ?>><?php echo $remove_text ?></a>
		<img class="pl_spinner" src="<?php echo $spinner ?>" alt="ajax-spinner" style="display:none; margin-left:5px;">

		<?php pls_do_atomic( 'after_add_to_fav' ); ?>

		<?php
		$contents = ob_get_clean();

		if ($wrapping_div == true) {
			$contents = '<div id="pl_add_remove_lead_favorites" class="pl_add_remove_lead_favorites" style="display:none;">' . $contents . '</div>';
		}

		return $contents;
	}

}

?>