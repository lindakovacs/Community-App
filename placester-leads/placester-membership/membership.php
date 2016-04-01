<?php

/**
 * Class Designed to Handle the rigors of Membership, and membership options...
 */

PL_Membership::init();
class PL_Membership {

	public static function init () {
		add_role('placester_lead', 'Property Lead', array('read' => true));

		add_action('wp_ajax_nopriv_pl_register_site_user', array(__CLASS__, 'ajax_register_site_user'));
		add_action('wp_ajax_nopriv_pl_login_site_user', array(__CLASS__, 'ajax_login_site_user'));
		add_action('wp_ajax_nopriv_pl_update_site_user', array(__CLASS__, 'ajax_update_site_user'));

		add_shortcode('pl_login_block', array(__CLASS__, 'placester_lead_control_panel'));
		add_shortcode('lead_user_navigation', array(__CLASS__, 'placester_lead_control_panel'));
	}

	// $lead_object array contains
	//  'username' - to use for new account
	//  'password' - to use for new account
	//
	// plus optional account metadata
	//  'name'
	//  'company'
	//  'email'
	//  'phone'
	public static function create_site_user ($lead_object, $local_only = false) {
		// create WordPress user
		$wordpress_user_id = wp_insert_user(array(
			'user_login' => $lead_object['username'],
			'user_pass' => $lead_object['password'],
			'user_email' => $lead_object['email'],
			'role' => 'placester_lead'));

		if (is_wp_error($wordpress_user_id))
			return false;

		$blogs = get_blogs_of_user($wordpress_user_id);
		update_user_meta($wordpress_user_id, 'primary_blog', current($blogs)->userblog_id);
		update_user_meta($wordpress_user_id, 'full_name', $lead_object['name']);
		update_user_meta($wordpress_user_id, 'phone', $lead_object['phone']);

		$lead_object['wp_id'] = $wordpress_user_id;
		$lead_object['wp_login'] = $lead_object['username'];

		// create linked Placester.com account
		if(!$local_only) {
			$response = $lead_object['crm_response'] = PL_People_Helper::add_person($lead_object);
			if ($response['id'])
				update_user_meta($wordpress_user_id, 'placester_api_id', $lead_object['pl_id'] = $response['id']);
		}

		// send notifications
		wp_new_user_notification($wordpress_user_id);
		if (PL_Options::get('pls_send_client_option'))
			wp_mail($lead_object['email'], 'Your new account on ' . site_url(), PL_Membership_Helper::parse_client_message($lead_object) );

		// login user
		wp_set_auth_cookie($wordpress_user_id, true, is_ssl());

		return $lead_object;
	}

	// returned array contains
	//  'wp_id' - wordpress id in wp_users table
	//
	// account metadata
	//  'name'
	//  'company'
	//  'email'
	//  'phone'
	//
	// plus, when available
	//  'pl_id' - placester unique tracking id
	//  'cur_data' - custom metadata from crm (array)
	//  'uncur_data' - custom metadata from crm (array)
	//  'location' - custom address metadata from crm (array)
	public static function get_site_user ($wp_id = null, $local_only = false) {
		$wp_user = $wp_id ? get_userdata($wp_id) : wp_get_current_user();
		if(!$wp_user || !$wp_user->ID)
			return array();

		$lead_object = array(
			'wp_id' => $wp_user->ID,
			'wp_login' => $wp_user->user_login,
			'name' => get_user_meta($wp_user->ID, 'full_name', true) ?:
				$wp_user->first_name . ($wp_user->first_name && $wp_user->last_name ? ' ' : '') . $wp_user->last_name,
			'email' => $wp_user->user_email,
			'phone' => get_user_meta($wp_user->ID, 'phone', true)
		);

		if($placester_id = get_user_meta($wp_user->ID, 'placester_api_id', true)) {
			$lead_object['pl_id'] = $placester_id;

			if (!$local_only) {
				$crm_data = PL_People_Helper::get_person($placester_id);
				if (is_array($crm_data) && $crm_data['id']) {
					$lead_object['crm_response'] = array('id' => $crm_data['id']); unset($crm_data['id']);
					$lead_object = array_merge($crm_data, $lead_object);
				}
				else
					$lead_object['crm_response'] = $crm_data;
			}
		}

		return $lead_object;
	}

	// $lead_object array contains
	//  'wp_id' - wordpress id in wp_users table (if not current user)
	//
	// account metadata
	//  'name'
	//  'company'
	//  'email'
	//  'phone'
	//
	//  'metadata' - custom metadata for crm (array)
	//  'location' - custom address metadata for crm (array)
	public static function update_site_user ($lead_object = array(), $local_only = false) {
		$local_user = self::get_site_user($lead_object['wp_id'], true);
		if(!$local_user || !$local_user['wp_id'])
			return false;

		$lead_object['wp_id'] = $local_user['wp_id'];
		$lead_object['wp_login'] = $local_user['wp_login'];

		// can't change email if it's the login id
		if($local_user['email'] == $local_user['wp_login'])
			$lead_object['email'] = $local_user['email'];

		// new values may come in metadata array, use crm merge
		$lead_object = PL_People_Helper::resolve_input($lead_object);

		// update local metadata
		if(isset($lead_object['name'])) update_user_meta($lead_object['wp_id'], 'full_name', $lead_object['name']);
		if(isset($lead_object['phone'])) update_user_meta($lead_object['wp_id'], 'phone', $lead_object['phone']);
		if($lead_object['email'] && $lead_object['email'] != $local_user['email'])
			wp_update_user(array('ID' => $lead_object['wp_id'], 'user_email' => $lead_object['email']));

		// update the Placester.com account
		if(!$local_only) {
			$response = $lead_object['crm_response'] = $local_user['pl_id'] ?
				PL_People_Helper::update_person(array_merge(array('id' => $local_user['pl_id']), $lead_object)) :
				PL_People_Helper::add_person($lead_object);

			if ($response['id'])
				update_user_meta($lead_object['wp_id'], 'placester_api_id', $lead_object['pl_id'] = $response['id']);
		}

		return $lead_object;
	}

	// Handles the frontend lead registration form -- see membership.js
	public static function ajax_register_site_user () {
		if ( !wp_verify_nonce($_POST['nonce'], 'placester_true_registration') ) {
			echo "Sorry, your nonce didn't verify -- try using the form on the site";
			die();
		}

		$lead_object = self::validate_registration($_POST);
		if($lead_object['errors'])
			echo json_encode(array("success" => false, "errors" => self::process_registration_errors($lead_object['errors'])));

		$lead_object = self::create_site_user($lead_object);
		echo json_encode($lead_object ? array("success" => true) : array("success" => false, "errors" => array('Unable to create account')));
		die();
	}

	// Handles the profile form -- see membership-edit.js
	public static function ajax_update_site_user () {
		$lead_object = self::update_site_user($_POST);
		echo json_encode($lead_object['crm_response']);
		die();
	}

	//  AJAX endpoint for authenticating a site user from the frontend
	public static function ajax_login_site_user () {
		extract($_POST);

		$sanitized_username = sanitize_user($username);
		$errors = array();

		if (empty($sanitized_username)) {
			$errors['user_login'] = "An email address is required";
		}
		elseif (empty($password)) {
			$errors['user_pass'] = "A password is required";
		}
		else {
			$userdata = get_user_by('login', $sanitized_username);

			if (empty($userdata)) {
				$errors['user_login'] = "The email address is invalid";
			}
			else if ($userdata && !wp_check_password($password, $userdata->user_pass, $userdata->ID)) {
				$errors['user_pass'] = "The password isn't correct";
			}
		}

		if (!empty($errors)) {
			$result = array("success" => false, "errors" => $errors);
		}
		else {
			$rememberme = ($remember == "forever") ? true : false;

			// Manually login user
			$creds['user_login'] = $sanitized_username;
			$creds['user_password'] = $password;
			$creds['remember'] = $rememberme;

			$user = wp_signon($creds, true);
			wp_set_current_user($user->ID);

			$result = array("success" => true);
		}

		echo json_encode($result);
		die();
	}

	// Validates all registration data
	private static function validate_registration ($post_vars) {
		if (is_array($post_vars)) {
			$lead_object['username'] = '';
			$lead_object['email'] = '';
			$lead_object['password'] = '';
			$lead_object['name'] = '';
			$lead_object['phone'] = '';
			$lead_object['errors'] = array();

			foreach ($post_vars as $key => $value) {
				switch ($key) {
					case 'username':
						$username['errors'] = array();
						$username['unvalidated'] = $value;
						$username['validated'] = '';

						//handles all random edge cases
						$username_validation = self::validate_username($username, $lead_object);
						$username = $username_validation['username'];
						$lead_object = $username_validation['lead_object'];

						// if no errors, set username
						if( empty($username['errors']) ){
							$lead_object['username'] = $username['validated'];
						}

						break;

					case 'email':
						$email['errors'] = array();
						$email['unvalidated'] = $value;
						$email['validated'] = '';

						$email_validation = self::validate_email($email, $lead_object);
						$email = $email_validation['email'];
						$lead_object = $email_validation['lead_object'];

						if ( empty($email['errors']) ) {
							$lead_object['email'] = $email['validated'];
						}

						break;

					case 'password':
						$password['errors'] = array();
						$password['unvalidated'] = $value;
						$confirm_password = $post_vars['confirm'];
						$password['validated'] = '';

						$password_validation = self::validate_password($password, $confirm_password, $lead_object);
						$password = $password_validation['password'];
						$lead_object = $password_validation['lead_object'];

						if ( empty($password['errors']) ) {
							$lead_object['password'] = $password['validated'];
						}
						break;

					case 'name':
						// we'll be fancy later.
						if ( !empty($value) ) {
							$lead_object['name'] = $value;
						}
						break;

					case 'phone':
						// we'll be fancy later.
						if ( !empty($value) ) {
							$lead_object['phone'] = $value;
						};
				}
			}
		}

		return $lead_object;
	}

	// Rules for validating passwords
	private static function validate_password ($password, $confirm_password, $lead_object) {
		// Make sure we have password and confirm.
		if ( !empty($password['unvalidated']) && !empty($confirm_password) ) {
			// Make sure they are the same
			if ($password['unvalidated'] == $confirm_password ) {
				$password['validated'] = $password['unvalidated'];
			} 
            else {
				// They aren't the same
				$lead_object['errors'][] = 'password_mismatch';
				$password['errors'] = true;
			}
		} 
        else {
			// Missing one...
			if (empty($password['unvalidated'])) {
				$lead_object['errors'][] = 'password_empty';
				$password['errors'] = true;
			}

			if (empty($confirm_password)) {
				$lead_object['errors'][] = 'confirm_empty';
				$password['errors'] = true;
			}
		}

		return array('password' => $password, 'lead_object' => $lead_object);
	}

	// Rules for validating email addresses
	private static function validate_email ($email, $lead_object) {
		if (empty($email['unvalidated'])) {
			$lead_object['errors'][] = 'email_required';
			$email['errors'] = true;
		} 
        else {
			// Something in email, is it valid?
			if ( is_email($email['unvalidated'] ) ) {
				if ( email_exists($email['unvalidated']) ) {
					$lead_object['errors'][] = 'email_taken';
					$email['errors'] = true;
				} 
                else {
					$email['validated'] = $email['unvalidated'];
				}

			} 
            else {
				$lead_object['errors'][] = 'email_invalid';
				$email['errors'] = true;
			}
		}

		return array('email' => $email, 'lead_object' => $lead_object);
	}

	// Rules for validating the username
	private static function validate_username ($username, $lead_object) {
		// Check for empty..
		if ( !empty($username['unvalidated']) ) {
			// Check to see if it's valid
			$username['unvalidated'] = sanitize_user($username['unvalidated']);

		} 
        else {
			// Generate one from the email, because wordpress requries it
			$lead_object['errors'][] = 'username_empty';
			$username['errors'] = true;

		}

		// Check if username exists...
		if ( username_exists($username['unvalidated']) ) {
			$lead_object['errors'][] = 'username_exists';
			$username['errors'] = true;
		} 
        else {
			$username['validated'] = $username['unvalidated'];
		}

		return array('username' => $username, 'lead_object' => $lead_object);

	}

	// Used for processing errors for the various forms.
	private static function process_registration_errors ($errors) {
		$error_messages = '';

		foreach ($errors as $error => $type) {

			switch ($type) {
				case 'username_exists':
					// $error_messages['username'][] .= 'That username already exists';
					$error_messages['user_email'] = 'That email is already taken';
					break;

				case 'username_empty':
					// $error_messages['username'][] .= 'Username is required.';
					$error_messages['user_email'] = 'Email is required';
					break;

				case 'email_required':
					$error_messages['user_email'] = 'Email is required';
					break;

				case 'email_invalid':
					$error_messages['user_email'] = 'Your email is invalid';
					break;

				case 'email_taken':
					$error_messages['user_email'] = 'That email is already taken';
					break;

				case 'password_empty':
					$error_messages['user_password'] = 'Password is required';
					break;

				case 'password_mismatch':
					$error_messages['user_confirm'] = 'Your passwords don\'t match';
					break;

				case 'confirm_empty':
					$error_messages['user_confirm'] = 'Confirm password is empty';
					break;

				default:
					$error_messages['user_email'] = 'There was an error, try again soon';
					break;
			}
		}

		return $error_messages;
	}

	/**
	* Creates a registration form
	*
	* The paramater will be used as an action for the registration form and it
	* will be used in the ajax callback at submission
	*
	* @param string $role The Wordpress role
	*
	*/
	public static function generate_lead_reg_form ($role = 'placester_lead')
	{
		if ( !is_user_logged_in() ) {
			ob_start();
			?>

			<?php if($USE_GRAVITY = false) : ?>

				<div class="am_register" style="display:none;">
					<div id="pl_lead_register_form" name="pl_lead_register_form" class="pl_login_reg_form pl_lead_register_form"><h3>Register:</h3>
						<?php gravity_form( 3, false, false, false, '', false ); ?>
					</div>
				</div>

			<?php else : ?>

				<div style="display:none;">
				<form method="post" action="#<?php echo $role; ?>" id="pl_lead_register_form" name="pl_lead_register_form" class="pl_login_reg_form pl_lead_register_form" autocomplete="off">
					<div style="display:none" class="success">You have been successfully signed up. This page will refresh momentarily.</div>
					<div id="pl_lead_register_form_inner_wrapper">

						<?php pls_do_atomic( 'register_form_before_title' ); ?>

						<h2>Sign Up</h2>

						<?php pls_do_atomic( 'register_form_before_email' ); ?>

						<p class="reg_form_email">
							<label for="user_email">Email</label>
							<input type="text" tabindex="25" size="20" required="required" class="input" id="reg_user_email" name="user_email" data-message="A valid email is needed." placeholder="Email">
						</p>

						<?php pls_do_atomic( 'register_form_before_password' ); ?>

						<p class="reg_form_pass">
							<label for="user_password">Password</label>
							<input type="password" tabindex="26" size="20" required="required" class="input" id="reg_user_password" name="user_password" data-message="Please enter a password." placeholder="Password">
						</p>

						<?php pls_do_atomic( 'register_form_before_confirm_password' ); ?>

						<p class="reg_form_confirm_pass">
							<label for="user_confirm">Confirm Password</label>
							<input type="password" tabindex="27" size="20" required="required" class="input" id="reg_user_confirm" name="user_confirm" data-message="Please confirm your password." placeholder="Confirm Password">
						</p>

						<?php pls_do_atomic( 'register_form_before_submit' ); ?>

						<p class="reg_form_submit">
							<a id="switch_to_login" class="" href="#">Already a User?</a>
							<input type="submit" tabindex="28" class="submit button" value="Register" id="pl_register" name="pl_register">
						</p>
						<?php echo wp_nonce_field( 'placester_true_registration', 'register_nonce_field' ); ?>
						<input type="hidden" tabindex="29" id="register_form_submit_button" name="_wp_http_referer" value="/listings/">

						<?php pls_do_atomic( 'register_form_after_submit' ); ?>

					</div>
				</form>
				</div>

			<?php endif; ?>

			<?php
			$result = ob_get_clean();
		}

		else {
			ob_start();
			?>
				<div style="display:none">
					<div class="pl_error error" id="pl_lead_register_form">
					You cannot register a user if you are logged in. You shouldn't even see a "Register" link.
					</div>
				</div>
			<?php
			$result = ob_get_clean();
		}

		return $result;
	}

	/**
	 * Creates a login form
	 *
	 */
	public static function generate_login_form ()
	{
		if ( !is_user_logged_in() ) {
			ob_start();
			?>

			<?php if($USE_GRAVITY = false) : ?>

				<div class="am_login" style="display:none;">
					<div id="pl_login_form" name="pl_login_form" class="pl_login_reg_form"><h3>Login:</h3>
						<?php gravity_form( 2, false, false, false, '', false ); ?>
					</div>
				</div>

			<?php else : ?>

				<div style='display:none;'>
				<form name="pl_login_form" id="pl_login_form" action="<?php echo wp_login_url(); ?>" method="post" class="pl_login_reg_form">

					<?php pls_do_atomic( 'login_form_before_title' ); ?>

					<div id="pl_login_form_inner_wrapper">
						<h2>Login</h2>

						<?php pls_do_atomic( 'login_form_before_email' ); ?>

						<p class="login-username">
							<label for="user_login">Email</label>
							<input type="text" name="user_login" id="user_login" class="input" required="required" value="" tabindex="20" data-message="A valid email is needed" placeholder="Email" />
						</p>

						<?php pls_do_atomic( 'login_form_before_password' ); ?>

						<p class="login-password">
							<label for="user_pass">Password</label>
							<input type="password" name="user_pass" id="user_pass" class="input" required="required" value="" tabindex="21" data-message="A password is needed" placeholder="Password" />
						</p>

						<?php pls_do_atomic( 'login_form_before_remember' ); ?>

						<p class="login-remember">
							<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="22" /> Remember Me</label>
						</p>

						<?php pls_do_atomic( 'login_form_before_submit' ); ?>

						<p class="login-submit">
							<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In" tabindex="23" />
							<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
						</p>

						<?php pls_do_atomic( 'before_login_title' ); ?>

					</div>

				</form>
				</div>

			<?php endif; ?>

			<?php
			$result = ob_get_clean();
		}

		else {
			ob_start();
			?>
				<div style="display:none">
					<div class="pl_error error" id="pl_lead_register_form">
						You cannot login if you are already logged in. You shouldn't even see a "Login" link.
					</div>
				</div>
			<?php
			$result = ob_get_clean();
		}

		return $result;
	}

	public static function get_client_area_url () {
		global $wpdb;
		$page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'client-profile' AND post_status = 'publish'");
		return $page_id ? get_permalink($page_id) : '';
	}

	/**
	 * Adds "Login | Register" if not logged in
	 * or "Logout | My account" if logged in
	 *
	 */
	public static function placester_lead_control_panel ($args) {
		$defaults = array(
			'loginout' => true,
			'profile' => true,
			'register' => true,
			'container_tag' => false,
			'container_class' => false,
			'anchor_tag' => false,
			'anchor_class' => false,
			'separator' => ' | ',
			'inside_pre_tag' => false,
			'inside_post_tag' => false,
			'no_forms' => false // use this to return just the links. Do this for all calls to this function after the first on a page.
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		$logged_in = is_user_logged_in();

		/** Login/Logout **/
		if ($loginout) {
			if (!$logged_in) {
				$loginout_link = '<a class="pl_login_link" href="#pl_login_form">Log in</a>';
			}
			else {
				$loginout_link = '<a href="' . esc_url(wp_logout_url(site_url())) . '" id="pl_logout_link">Log out</a>';
			}
			if ($anchor_tag) {
				$loginout_link = "<{$anchor_tag} class={$anchor_class}>" . $inside_pre_tag . $loginout_link . $inside_post_tag . "</{$anchor_tag}>";
			}
		}
		else
			$loginout_link = '';

		/** Register **/
		if ($register && !$logged_in) {
			$register_link = '<a class="pl_register_lead_link" href="#pl_lead_register_form">Register</a>';
			if ($anchor_tag) {
				$register_link = "<{$anchor_tag} class={$anchor_class}>" . $inside_pre_tag . $register_link . $inside_post_tag . "</{$anchor_tag}>";
			}
		}
		else
			$register_link = '';

		/** My Account **/
		if ($profile && $logged_in && current_user_can('placester_lead') && ($profile_url = self::get_client_area_url())) {
			$profile_link = '<a id="pl_lead_profile_link" target="_blank" href="' . $profile_url . '">My Account</a>';
			if ($anchor_tag) {
				$profile_link = "<{$anchor_tag} class={$anchor_class}>" . $inside_pre_tag . $profile_link . $inside_post_tag . "</{$anchor_tag}>";
			}
		}
		else
			$profile_link = '';

		$link = $loginout_link;
		if ($link && $register_link) $link .= $separator;
		$link .= $register_link;
		if ($link && $profile_link) $link .= $separator;
		$link .= $profile_link;

		// Enclose in container tag if set...
		if ($container_tag) {
			$link = "<{$container_tag} class={$container_class}>" . $link . "</{$container_tag}>";
		}

		// Append the form HTML...
		if ( !$logged_in && !$no_forms ) {
			$link .= self::generate_lead_reg_form() . self::generate_login_form();
		}

		return $link;
	}

// PL_COMPATIBILITY_MODE -- preserve the interface expected by certain previous versions of blueprint
	static function placester_favorite_link_toggle($args) {
		return PL_Favorite_Listings::placester_favorite_link_toggle($args);
	}
	static function get_favorite_ids() {
		return PL_Favorite_Listings::get_favorite_properties();
	}
}
