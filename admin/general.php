<?php


PL_Admin_General::init();

class PL_Admin_General extends PL_Admin_Page {
	public function __construct() {
		parent::__construct('placester', 100, 'placester_settings', 'Settings', 'General', null);
		$this->require_script('placester-general', PL_ADMIN_JS_URL . 'general.js', array('jquery-ui-core', 'jquery-ui-dialog'));
		$this->require_style('placester-general', PL_ADMIN_CSS_URL . 'general.css', array('placester-settings'));
	}

	public function render_admin_content() {
		$pls_whoami = PL_User::whoami();
		if( is_array( $pls_whoami ) )
			extract( $pls_whoami );

		$places_api_key = PL_Option_Helper::get_google_places_key();
		$block_address = PL_Option_Helper::get_block_address();
		$demo_data_flag = PL_Option_Helper::get_demo_data_flag();
		$error_logging = PL_Option_Helper::get_log_errors();

		if (PL_Option_Helper::api_key() && isset($email)): ?>

			<div class="header-wrapper" id="settings-header-wrapper">
				<h2 id="settings-page-email-title">This plugin is linked to <span id="settings-page-email-in-title"><?php echo $email ?></span> <span class="check-icon"></span></h2>
				<a class="button-secondary" id="new_email" >Create a New Placester Account</a>
				<a class="button-secondary" id="existing_placester" href="#">Change to an Existing Placester Account</a>
				<a class="button-secondary" href='https://placester.com/user/login'>Login to Placester.com</a>
				<a class="button-secondary" href='https://placester.com/user/password/new'>Forgot Password?</a>
			</div>
			<div class="clear"></div>
			<form action="">
				<div id="" class="meta-box-sortables ui-sortable">
					<div id="div" class="postbox ">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle">
							<span>Placester.com Account Details</span>
						</h3>
						<div class="inside">
							<p>Here's your account details being pulled directly from Placester.com. You may edit your <a href="https://placester.com/user/profile">personal information</a> and  <a href="https://placester.com/company/settings">company information</a> any time on Placester.com. Some themes may automatically use this information (to save you time entering data). However, you can always enter information directly into a theme so you have more control over the look of your website.</p>
							<div class="personal-column">
								<h3>Personal Details</h3>
								<div class="third">
									<?php if (isset($user['headshot'])): ?>
										<img src="<?php echo $user['headshot'] ?>" alt="" width=100 height=90>
									<?php else: ?>
										<img src="" alt="">
									<?php endif ?>
								</div>
								<div class="third">
									<ul>
										<li><b><?php echo $user['first_name'] . " " . $user['last_name']; ?></b></li>
										<li><?php echo $user['email'] ?></li>
										<li><?php echo $user['phone'] ?></li>
										<li><?php echo $user['website'] ?></li>
									</ul>
								</div>
							</div>
							<div class="company-column">
								<h3>Company Details</h3>
								<div class="third">
									<?php if (isset($logo)): ?>
										<img src="<?php echo $logo ?>" alt="" width=100 height=90>
									<?php else: ?>
										<img src="" alt="">
									<?php endif ?>
								</div>
								<div class="third">
									<ul>
										<li><b><?php echo $name; ?></b></li>
										<li><?php echo $email; ?></li>
										<li><?php echo $phone ?></li>
										<li><?php echo $website ?></li>
									</ul>
								</div>
								<div class="third">
									<ul>
										<li><?php echo $location['address']; ?><?php echo isset($location['unit']) ? ', Unit: ' . $location['unit'] : '';  ?></li>
										<li><?php echo $location['locality'] . ' ,' .  $location['region'] . ' ' . $location['postal']; ?></li>
										<li><?php echo $location['country']; ?></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</form>

		<?php else: ?>

			<div class="header-wrapper" id="settings-header-wrapper">
				<h2 id="settings-page-email-title">This plugin is not set up</h2>
				<a class="button-secondary" id="new_email" >Create a New Placester Account</a>
				<a class="button-secondary" id="existing_placester" href="#">Use an Existing Placester Account</a>
				<a class="button-secondary" href='https://placester.com/user/login'>Login to Placester.com</a>
				<a class="button-secondary" href='https://placester.com/user/password/new'>Forgot Password?</a>
			</div>
			<div class="clear"></div>
			<form action="">
				<div id="" class="meta-box-sortables ui-sortable">
					<div id="div" class="postbox ">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle">
							<span>Placester.com Account Details</span>
						</h3>
						<div class="inside">
							<div class="not-set-up"><h3>Plugin not set up! <a href="#" id="settings_get_started_signup">Get started.</a></h3></div>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</form>

		<?php endif; ?>

		<div class="header-wrapper">
			<h2>Google Maps API Key</h2>
			<div id="default_googe_places_message"></div>
		</div>
		<div class="clear"></div>
		<p><strong>Add a Google Maps API Key to enable search and property detail maps on your website.</strong>  To obtain an API Key (directly from Google), click <a target="_blank" rel="noopener noreferrer" href="https://developers.google.com/maps/documentation/javascript/get-api-key">here</a> and follow the instructions.
		<div>
			<label for="google_places_api">Google Maps API Key</label>
			<input type="text" id="google_places_api" value="<?php echo $places_api_key ?>">
			<a href="#" id="google_places_api_button" class="button">Update</a>
		</div>

		<div class="header-wrapper">
			<h2>Listings Settings</h2>
			<div class="ajax_message" id="listing_settings_message"></div>
		</div>
		<div class="clear"></div>
		<ul>
			<li>
				<input id="block_address" type="checkbox" name="block_address" <?php echo $block_address ? 'checked="checked"' : '' ?>>
				<label for="block_address">Use <b>Block Addresses</b> rather than exact addresses. Using block addresses will suppress the display of exact street addresses for all properties on your website.</label>
			</li>
			<li>
				<input id="demo_data" type="checkbox" name="demo_data" <?php echo $demo_data_flag ? 'checked="checked"' : '' ?>>
				<label for="demo_data">Use <b>Demo Data</b> for listings (note: this will hide any listings that you have created, or that are coming in from any MLS)</label>
			</li>
		</ul>

		<div class="header-wrapper">
			<h2>Other Settings</h2>
			<div id="error_logging_message"></div>
		</div>
		<ul>
			<li>
				<input id="error_logging_click" type="checkbox" name="error_logging" <?php echo $error_logging ? 'checked="checked"' : '' ?>>
				<label for="error_logging">You can help improve Placester. Allow the Real Estate Website Builder Plugin to anonymously report errors and usage information so we can fix errors and add new features.</label>
			</li>
		</ul>

		<?php
	}


	public static function init() {
		$pl_admin_page = new self();

		add_action( 'wp_ajax_create_account', array(__CLASS__, 'create_account') );
		add_action( 'wp_ajax_set_placester_api_key', array(__CLASS__, 'set_placester_api_key') );
		add_action( 'wp_ajax_new_api_key_view', array(__CLASS__, 'new_api_key_view') );
		add_action( 'wp_ajax_existing_api_key_view', array(__CLASS__, 'existing_api_key_view') );

		add_action( 'wp_ajax_update_google_places', array(__CLASS__, 'update_google_places') );

		add_action( 'wp_ajax_ajax_block_address', array(__CLASS__, 'ajax_block_address') );
		add_action( 'wp_ajax_demo_data_on', array(__CLASS__, 'toggle_listing_demo_data' ) );
		add_action( 'wp_ajax_demo_data_off', array(__CLASS__, 'toggle_listing_demo_data' ) );

		add_action( 'wp_ajax_ajax_log_errors', array(__CLASS__, 'ajax_log_errors') );
	}

	public static function create_account() {
		if ($_POST['email']) {
			$success = PL_User::create(array('email'=>$_POST['email']));
			$response = $success ? $success : array('outcome' => false, 'message' => 'There was an error. Is that a valid email address?');
		} else {
			$response = array('outcome' => false, 'message' => 'No Email Provided');
		}

		echo json_encode($response);
		die();
	}

	public static function set_placester_api_key() {
		$result = PL_Option_Helper::set_api_key($_POST['api_key']);
		echo json_encode($result);
		die();
	}

	public static function new_api_key_view() {
		$email = get_option('admin_email');
		include 'partials/sign-up.php';
		die();
	}

	public static function existing_api_key_view() {
		include 'partials/existing-placester.php';
		die();
	}

	public static function update_google_places() {
		if (isset($_POST['places_key'])) {
			$response = PL_Option_Helper::set_google_places_key($_POST['places_key']);
			echo json_encode($response);
		}
		die();
	}

	public static function ajax_block_address() {
		$block_address = ($_POST['use_block_address'] == 'true') ? true : false;
		$result = PL_Option_Helper::set_block_address($block_address);

		if ($result) {
			$action = ($block_address ? 'on' : 'off');
			$message = "You successfully turned {$action} block addresses";
		}
		else {
			$message = 'There was an error -- please try again';
		}

		echo json_encode(array('result' => $result, 'message' => $message));
		die();
	}

	public static function toggle_listing_demo_data() {
		// Determine the new state of this flag...
		switch ($_POST["action"]) {
			case "demo_data_on":
				$state = true;
				$msg = "Your site is now set to use demo data";
				break;
			case "demo_data_off":
				$state = false;
				$msg = "Demo data successfully turned off";
				break;
			default:
				$state = false;
				$msg = "Demo data is off";
		}

		PL_Option_Helper::set_demo_data_flag($state);
		PL_Cache::flush_all();

		echo json_encode(array("message" => $msg));
		die();
	}

	public static function ajax_log_errors() {
		$report_errors = ($_POST['report_errors'] == 'true') ? true : false;
		$result = PL_Option_Helper::set_log_errors($report_errors);

		if ($result) {
			$action = ($report_errors ? 'on' : 'off');
			$message = "You successfully turned {$action} error reporting";
		}
		else {
			$message = 'There was an error -- please try again';
		}

		echo json_encode(array('result' => $result, 'message' => $message));
		die();
	}


}
