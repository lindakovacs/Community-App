<?php 

PL_Membership_Helper::init();
class PL_Membership_Helper {

	public static function init () {
		add_action('wp_ajax_set_client_settings', array(__CLASS__, 'set_client_settings')); 
		add_action('wp', array(__CLASS__, 'admin_bar')); 
	}

	public static function admin_bar () {
		if (current_user_can('placester_lead')) {
			add_filter('show_admin_bar', '__return_false');
		}
	}

	public static function get_client_settings () {
		$send_client_message = PL_Options::get('pls_send_client_option');
		$send_client_message_text = PL_Options::get('pls_send_client_text');
		if (!$send_client_message_text) {
			$send_client_message_text = "Hey %client_email%,\n";
			$send_client_message_text .= "\n";
			$send_client_message_text .= "Thanks for signing up for an account on %website_url%. We update the site regularly with new listings. Feel free to reach out at %email_address% with any questions. We\'d be more then happy to help.\n";
			$send_client_message_text .= "\n";
			$send_client_message_text .= "Best,\n";
			$send_client_message_text .= "%full_name%\n";
		}
		
		return array('send_client_message' => $send_client_message, 'send_client_message_text' => $send_client_message_text);
	}

	public static function set_client_settings () {
		$send_client_message = isset($_POST['send_client_message']) ? $_POST['send_client_message'] : false;
		$send_client_message_text = isset($_POST['send_client_message_text']) ? stripslashes($_POST['send_client_message_text']) : false;
		PL_Options::set('pls_send_client_option', $send_client_message);
		PL_Options::set('pls_send_client_text', $send_client_message_text);
		
		echo json_encode(array('result' => true, 'message' => 'You\'ve successfully updated your options'));
		die();
	}

	public static function parse_client_message ($client) {
		$settings = self::get_client_settings();
		$send_client_message_text = $settings['send_client_message_text'];
		$admin_details = PL_Helper_User::whoami();
		$replacements = array('%client_email%' => $client['username'], '%email_address%' => $admin_details['user']['email'], '%full_name%' => $admin_details['user']['first_name'] . ' ' . $admin_details['user']['last_name'], '%first_name%' => $admin_details['user']['first_name'], '%website_url%' => site_url()); 
		foreach ($replacements as $key => $value) {
			$send_client_message_text = str_replace($key, $value, $send_client_message_text);
		}
		
		return $send_client_message_text;
	}
}

// override the default registration notification function from wp-includes/pluggable.php
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message,
		PL_Lead_Capture_Helper::merge_bcc_forwarding_addresses_for_sending());

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
	$message .= wp_login_url() . "\r\n";

	wp_mail($user->user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);
}
