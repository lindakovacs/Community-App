<?php

PL_User_Saved_Search::init();
class PL_User_Saved_Search {

	public static function init () {
		// AJAX endpoints for attaching saved searches to users
		add_action('wp_ajax_save_favorite_search', array(__CLASS__,'ajax_save_favorite_search'));
		add_action('wp_ajax_get_favorite_search', array(__CLASS__, 'ajax_get_favorite_search'));
		add_action('wp_ajax_clear_favorite_search', array(__CLASS__, 'ajax_clear_favorite_search'));
		add_action('wp_ajax_enable_favorite_search', array(__CLASS__, 'ajax_enable_favorite_search'));

		// Cron jobs to send user emails for new and updated listings
		add_action('wp_cron_email_favorite_search',  array(__CLASS__, 'cron_email_favorite_search'), 10, 2);
		add_action('wp_cron_batch_favorite_searches',  array(__CLASS__, 'cron_batch_favorite_searches'));

		// Configure cron to handle additional frequencies we use
		add_filter( 'cron_schedules', 'cron_add_frequencies' );
		function cron_add_frequencies ($schedules) {
			$schedules['weekly'] = array('interval' => 604800, 'display' => __('Once Weekly'));
			$schedules['debug'] = array('interval' => 60, 'display' => __('Once per Minute'));
			return $schedules;
		}

		// Add saved search shortcodes
		add_shortcode('favorite_search_toggle', array(__CLASS__, 'placester_search_link_toggle'));
		add_shortcode('favorite_search_list', array(__CLASS__, 'placester_favorite_search_list'));
	}

	public static function get_favorite_searches () {
		$favorites = get_user_option('pl_member_searches');
		if(!$favorites) {
			$favorites = array();
		}

		return $favorites;
	}

	public static function update_favorite_searches ($favorites) {
		return update_user_option(get_current_user_id(), 'pl_member_searches', $favorites);
	}

	public static function save_favorite_search ($search_url) {
		if(!($hash_id = str_replace('/', '', array_pop(explode('#', $search_url))))) return false;

		$favorites = self::get_favorite_searches();
		if($favorites["/$hash_id"]) return $hash_id;

		$favorites["/$hash_id"] = array('hash' => $hash_id, 'url' => $search_url, 'timestamp' => null);
		return self::update_favorite_searches($favorites) ? $hash_id : false;
	}

	public static function get_favorite_search ($hash_id) {
		if(!($hash_id = str_replace('/', '', array_pop(explode('#', $hash_id))))) return null;

		$favorites = self::get_favorite_searches();
		return $favorites["/$hash_id"];
	}

	public static function clear_favorite_search ($hash_id) {
		if(!($hash_id = str_replace('/', '', array_pop(explode('#', $hash_id))))) return false;

		$favorites = self::get_favorite_searches();
		if(!$favorites["/$hash_id"]) return $hash_id;

		unset($favorites["/$hash_id"]);
		wp_clear_scheduled_hook('wp_cron_email_favorite_search', array('user_id' => get_current_user_id(), 'hash_id' => $hash_id));
		return self::update_favorite_searches($favorites) ? $hash_id : false;
	}

	public static function enable_favorite_search ($hash_id) {
		if(!($hash_id = str_replace('/', '', array_pop(explode('#', $hash_id))))) return false;

		$favorites = self::get_favorite_searches();
		if(!$favorites["/$hash_id"]) return false;

		$favorites["/$hash_id"]['timestamp'] = time() - 43200;
		if($return = self::update_favorite_searches($favorites)) {
			wp_clear_scheduled_hook('wp_cron_email_favorite_search', array('user_id' => get_current_user_id(), 'hash_id' => $hash_id));
			// wp_schedule_event(time() + 30, 'debug', 'wp_cron_email_favorite_search', array('user_id' => get_current_user_id(), 'hash_id' => $hash_id));
			wp_schedule_event(time() + 43200, 'daily', 'wp_cron_email_favorite_search', array('user_id' => get_current_user_id(), 'hash_id' => $hash_id));
		}
		return $return ? $hash_id : false;
	}

	public static function disable_favorite_search ($hash_id) {
		if(!($hash_id = str_replace('/', '', array_pop(explode('#', $hash_id))))) return false;

		$favorites = self::get_favorite_searches();
		if(!$favorites["/$hash_id"]) return false;

		$favorites["/$hash_id"]['timestamp'] = null;
		wp_clear_scheduled_hook('wp_cron_email_favorite_search', array('user_id' => get_current_user_id(), 'hash_id' => $hash_id));
		return self::update_favorite_searches($favorites) ? $hash_id : false;
	}

	public static function is_favorite_search ($hash_id) {
		$favorite = self::get_favorite_search($hash_id);
		return isset($favorite);
	}

	public static function is_favorite_search_enabled ($hash_id) {
		$favorite = self::get_favorite_search($hash_id);
		return $favorite ? isset($favorite['timestamp']) : false;
	}

	public static function ajax_save_favorite_search () {
		if($_POST['search_url']) {
			if($hash_id = self::save_favorite_search($_POST['search_url'])) {
				//self::enable_favorite_search($hash_id);
				if ($favorite = self::get_favorite_search($hash_id))
					echo json_encode($favorite);
				else
					echo json_encode(array('hash' => $hash_id));
			}
			else {
				echo json_encode(array('error' => 'Unable to save search.'));
			}
		}
		die();
	}

	public static function ajax_get_favorite_search () {
		if($_POST['search_hash']) {
			if ($favorite = self::get_favorite_search($_POST['search_hash'])) {
				echo json_encode($favorite);
			}
			else {
				echo json_encode(array('hash' => ''));
			}
		}
		die();
	}

	public static function ajax_clear_favorite_search () {
		if($_POST['search_hash']) {
			if ($hash_id = self::clear_favorite_search($_POST['search_hash'])) {
				echo json_encode(array('hash' => $hash_id));
			}
			else {
				echo json_encode(array('error' => 'Unable to clear saved search.'));
			}
		}
		die();
	}

	public static function ajax_enable_favorite_search () {
		if(($hash_id = $_POST['search_hash']) && isset($_POST['search_enable'])) {
			if(($enable = $_POST['search_enable']) ? self::enable_favorite_search($hash_id) : self::disable_favorite_search($hash_id)) {
				if ($favorite = self::get_favorite_search($hash_id))
					echo json_encode($favorite);
				else
					echo json_encode(array('hash' => $hash_id));
			}
			else {
				$message = 'Unable to ' . ($enable ? 'enable' : 'disable') . ' saved search.';
				echo json_encode(array('error' => $message));
			}
		}
		die();
	}

	public static function favorite_search_email ($user, $favorite) {
		$listings = PL_Listing_Helper::results(array_merge(
			PL_Permalink_Search::get_saved_search_filters('/' . $favorite['hash']),
			array('created_at' => gmstrftime("%b %d %Y %H:%M:%S", $favorite['timestamp']), 'created_at_match' => 'gte',
				'sort_by' => 'cur_data.dom', 'sort_type' => 'asc', 'limit' => 12, 'offset' => 0)));

		if(empty($listings) || empty($listings['listings'])) return false;

		if(count($listings['listings']) <= 12) {
			// when we show all the new listings, we also show the total number that match, old and new
			$all_listings = PL_Listing_Helper::results(array_merge(
				PL_Permalink_Search::get_saved_search_filters('/' . $favorite['hash']),
				array('sort_by' => 'cur_data.dom', 'sort_type' => 'asc', 'limit' => 1, 'offset' => 0)));
		}

		$from = 'listings@' . implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2));
		$subject = 'Updated property listings from ' . $_SERVER['SERVER_NAME'];
		$boundary = 'favorite-search-email-' . md5(time());
		$criteria = PL_Permalink_Search::display_saved_search_filters('/' . $favorite['hash']);

		$headers = "From: $from\n";
		$headers.= "MIME-Version: 1.0\n";
		$headers.= "Content-Type: multipart/alternative; boundary=\"$boundary\"\n\n";

		$message = "This is a multipart message in MIME format.\n";
		$message.= "\n--$boundary\n";
		$message.= self::favorite_search_email_text($user, $favorite, $criteria, $listings, $all_listings);
		$message.= "\n--$boundary\n";
		$message.= self::favorite_search_email_html($user, $favorite, $criteria, $listings, $all_listings);
		$message.= "\n--$boundary--\n";

		mail($user->data->user_email, $subject, $message, $headers);
		return $message;
	}

	public static function favorite_search_email_text ($user, $favorite, $criteria, $listings, $all_listings) {
		$headers = "Content-Type: text/plain\n\n";

		$search = $favorite['url'] . "\n\n";
		$search.= get_the_title(url_to_postid($favorite['url'])) . "\n";

		$criteria = str_replace(array('<ul>', '</ul>'), '', $criteria);
		$criteria = str_replace(array('<li>', '</li>'), array('  ', "\n"), $criteria);
		$criteria = str_replace('&nbsp;', ' ', $criteria);

		if(count($listings['listings']) <= 12) {
			$results = $all_listings['total'] . ' Total Listings, ' . $listings['total'] . " New Listings\n";
		}
		else {
			// otherwise we indicate that we are just showing a sample
			$results = "Recent Listings\n";
		}

		foreach ($listings['listings'] as $listing) {
			$results.= "\n" . $listing['cur_data']['url'] . "\n";

			$results.= $listing['location']['address'] . ', ';
			$results.= $listing['location']['locality'] . ', ';
			$results.= $listing['location']['region'] . ' ' . $listing['location']['postal'] . "\n";

			if($listing['rets'] && $listing['rets']['mls_id'] || $listing['metadata']['price']) {
				if($listing['rets'] && $listing['rets']['mls_id'])
					$results.= 'MLS ID: ' . $listing['rets']['mls_id'] . ', ';

				$results.= PLS_Format::number($listing['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true));

				if($listing['cur_data']['beds'])
					$results.= ', ' . $listing['cur_data']['beds'] . ' beds';
				if($listing['cur_data']['baths'])
					$results.= ', ' . $listing['cur_data']['baths'] . ($listing['cur_data']['half_baths'] ? '+' : '') . ' baths';
				$results.= "\n";
			}

			if($listing['cur_data']['desc']) {
				$results.= html_entity_decode($listing['cur_data']['desc']) . "\n";
			}
		}

		return $headers . $search . $criteria . $results;
	}

	public static function favorite_search_email_html ($user, $favorite, $criteria, $listings, $all_listings) {
		$criteria = str_replace('</li><li>', ', ', $criteria);
		$criteria = str_replace(array('<ul>', '</ul>'), array(': ', ''), $criteria);
		$criteria = str_replace(array('<li>', '</li>'), '', $criteria);

		ob_start();
		echo "Content-Type: text/html\n\n";

?>
<html>
	<body style="width=1050px;">
		<a href="<?php echo $favorite['url']; ?>" style="font-size: 135%;">
			<?php echo get_the_title(url_to_postid($favorite['url'])) . $criteria; ?>
		</a>

		<?php if(count($listings['listings']) <= 12) {
			$results = $all_listings['total'] . ' Total Listings, ' . $listings['total'] . " New Listings";
		}
		else {
			// otherwise we indicate that we are just showing a sample
			$results = "Recent Listings";
		} ?>

		<p style="font-size: 120%; margin-top: .25em;"><?php echo $results; ?></p>

		<table style="vertical-align: top; width=1050px;">
			<?php foreach ($listings['listings'] as $listing) { ?>
				<tr style="vertical-align: top; margin: 15px 0;">

					<td class="listing-image" style="vertical-align: top; margin=5px; width=210px;">
						<a href="<?php echo @$listing['cur_data']['url']; ?>">
							<?php echo PLS_Image::load($listing['images'][0]['url'], array('resize' => array('w' => 210, 'h' => 140), 'fancybox' => true, 'as_html' => true, 'html' => array('alt' => $listing['location']['full_address']))); ?>
						</a>
					</td>

					<td class="listing-info" style="vertical-align: top;  margin=5px; width=840px;">
						<a class="listing-address" href="<?php echo @$listing['cur_data']['url']; ?>" style="font-size: 120%;">
							<?php echo $listing['location']['address'] . ', ' . $listing['location']['locality'] . ', ' . $listing['location']['region'] . ' ' . $listing['location']['postal']; ?>
						</a>

						<?php if ($listing['rets']['mls_id'] || $listing['cur_data']['price']): ?>
							<p class="listing-details" style="font-weight: bold; margin-bottom: 0.25em;">
								<?php if ($listing['rets']['mls_id']): ?>
									<span class="mls-id"><span class="label">MLS ID: </span><?php echo @$listing['rets']['mls_id']; ?></span><span class="comma">, </span>
								<?php endif; ?>
								<span class="price"><?php echo PLS_Format::number($listing['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true)); ?></span><?php
								if ($listing['cur_data']['beds']): ?><span class="comma">, </span>
									<span class="beds"><?php echo @$listing['cur_data']['beds']; ?><span class="label"> beds</span></span><?php
								endif;
								if ($listing['cur_data']['baths']): ?><span class="comma">, </span>
									<span class="baths"><?php echo @$listing['cur_data']['baths'] . ($listing['cur_data']['half_baths'] ? '+' : ''); ?><span class="label"> baths</span></span><?php
								endif; ?>
							</p>
						<?php endif;

						if ($listing['cur_data']['desc']): ?>
							<p class="listing-description" style="margin-top: 0.25em;"><?php echo $listing['cur_data']['desc']; ?></p><?php
						endif; ?>
					</td>

				</tr>
			<?php } ?>
		</table>
	</body>
</html>
<?php

		return ob_get_clean();
	}

	public static function cron_email_favorite_search ($user_id, $hash_id) {
		if(($favorites = get_user_option('pl_member_searches', $user_id)) && ($favorite = $favorites["/$hash_id"])) {
			if(!$favorite['timestamp']) {
				wp_clear_scheduled_hook('wp_cron_email_favorite_search', array('user_id' => $user_id, 'hash_id' => $hash_id));
				return;
			}

			if(($user = get_userdata($user_id)) && $user->data->user_email) {
				if($mail = self::favorite_search_email($user, $favorite)) {
					$favorites["/$hash_id"]['timestamp'] = time();
					update_user_option($user_id, 'pl_member_searches', $favorites);
				}
			}
		}
	}


	public static function cron_batch_favorite_searches () {
		$users = get_users(array('fields' => 'ID'));
		foreach ($users as $id) {
			$favorites = get_user_option('pl_member_searches', $id);
			foreach ($favorites as $favorite) {
				if($favorite['timestamp'])
					self::cron_email_favorite_search($id, $favorite['hash']);
			}
		}
	}

	/*
	 * Adds "Save/Clear Search" links and registration hook
	 */
	public static function placester_search_link_toggle ($args) {
		$defaults = array(
			'search_hash' => null,
			'wrapping_div' => true,
			'save_text' => 'Favorite Search',
			'clear_text' => 'Remove Favorite',
			'enable_text' => 'Subscribe',
			'disable_text' => 'Unsubscribe',
			'spinner' => admin_url('images/wpspin_light.gif')
		);

		$args = wp_parse_args($args, $defaults); extract($args, EXTR_SKIP);
		ob_start();

		if($search_hash) $favorite = self::get_favorite_search($search_hash);
		$display_save = empty($favorite);
		$display_enable = $favorite && empty($favorite['timestamp']);

		// Saved search options for a logged in user
		if (is_user_logged_in()) {
			if($favorite) { ?>
				<a id="pl_favorite_search_link" href="<?php echo $favorite['url']; ?>"></a>
			<?php } ?>
			<img class="pl_favorite_search_spinner" src="<?php echo $spinner ?>" alt="ajax-spinner" style="vertical-align:middle; visibility:hidden;">
			<span id="pl_save_favorite_search" class="pl_favorite_search_link"<?php echo !$display_save ? ' style="display:none;"' : '' ?>>
				<a href="#save-search"><?php echo $save_text ?></a> | <?php echo $enable_text ?></span>
			<span id="pl_clear_favorite_search" class="pl_favorite_search_link"<?php echo $display_save ? ' style="display:none;"' : '' ?>>
				<a href="#clear-search"><?php echo $clear_text ?></a> |
			<span id="pl_enable_favorite_search" class="pl_favorite_search_link"<?php echo !$display_enable ? ' style="display:none;"' : '' ?>>
				<a href="#enable-search"><?php echo $enable_text ?></a></span>
			<span id="pl_disable_favorite_search" class="pl_favorite_search_link"<?php echo $display_enable ? ' style="display:none;"' : '' ?>>
				<a href="#disable-search"><?php echo $disable_text ?></a></span></span>
			<?php }

		// Please register for a logged out user
		else { ?>
			<span id="pl_save_favorite_search" class="pl_favorite_search_link guest">
				<a class="pl_register_lead_favorites_link" href="#pl_lead_register_form"><?php echo $save_text ?></a> | <?php echo $enable_text ?></span>
		<?php }

		$contents = ob_get_clean();
		if ($wrapping_div) {
			$contents = '<div id="pl_favorite_search_links" class="pl_favorite_search_links" style="display:none;">' . $contents . '</div>';
		}
		return $contents;
	}

	/*
	 * Displays member saved searches and toggles
	 */
	public static function placester_favorite_search_list ($args) {
		$defaults = array(
			'save_text' => 'Favorite Search',
			'clear_text' => 'Remove Favorite',
			'enable_text' => 'Subscribe',
			'disable_text' => 'Unsubscribe',
			'spinner' => admin_url('images/wpspin_light.gif')
		);

		$args['wrapping_div'] = false;
		$favorites = self::get_favorite_searches();

		ob_start();
		if(count($favorites)) { ?>
			<ul class="pl_favorite_search_list">
			<?php foreach($favorites as $favorite) {
				$args['search_hash'] = $favorite['hash']; ?>
				<li class="pl_favorite_search_list_item">
					<div class="pl_favorite_search_list_link">
						<a href="<?php echo $favorite['url']; ?>">
							<div class="pl_favorite_search_list_page">
								<?php echo get_the_title(url_to_postid($favorite['url'])); ?>
							</div>
							<div class="pl_favorite_search_list_criteria">
								<?php echo PL_Permalink_Search::display_saved_search_filters('/' . $favorite['hash']); ?>
							</div>
						</a>
					</div>
					<div class="pl_favorite_search_list_toggle">
						<?php echo self::placester_search_link_toggle($args); ?>
					</div>
				</li>
			<?php } ?>
			</ul>
		<?php }
		else { ?>
			<p>You haven't saved any favorite searches.</p>
		<?php }

		$contents = ob_get_clean();
		return $contents;
	}
}