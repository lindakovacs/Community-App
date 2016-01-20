<?php 
	$pls_whoami = PL_Helper_User::whoami();
	if( is_array( $pls_whoami ) ) {
		extract( $pls_whoami ); 
	}
	$places_api_key = PL_Option_Helper::get_google_places_key(); 
	$error_logging = PL_Option_Helper::get_log_errors(); 
	$block_address = PL_Option_Helper::get_block_address(); 
	$enable_community_pages = PL_Option_Helper::get_community_pages();
	$demo_data_flag = PL_Option_Helper::get_demo_data_flag(); 
?>

	<?php if (PL_Option_Helper::api_key() && isset($email)): ?>
		<div class="header-wrapper" id="settings-header-wrapper">
			<h2 id="settings-page-email-title">This plugin is linked to <span id="settings-page-email-in-title"><?php echo $email ?></span> <span class="check-icon"></span></h2>	
			<a class="button-secondary" href='https://placester.com/user/login'>Login to Placester.com</a>
			<a class="button-secondary" href='https://placester.com/user/password/new'>Forgot Password?</a>	
			<a class="button-secondary" id="new_email" >Create a New Placester Account</a>
			<a class="button-secondary" id="existing_placester" href="#">Change to an Existing Placester Account</a>	
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
		<h2 style="clear: both">This plugin is not set up! Click anywhere to start</h2>
		<form action="">
			<div id="" class="meta-box-sortables ui-sortable">
				<div id="div" class="postbox ">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle">
						<span>Placester.com Account Details</span>
					</h3>
					<div class="inside">
						<div class="not-set-up"><h2>Plugin not Set Up! <a href="#" id="settings_get_started_signup">Get Started.</a></h2></div>
					</div>
					<div class="clear"></div>
				</div>	
			</div>
		</form>
	<?php endif; ?>

		<div class="header-wrapper">
			<h2>Google Places API Key</h2>
			<div id="default_googe_places_message"></div>
		</div>
		<div class="clear"></div>
		<p><strong>Add a Google Places API Key to enable lifestyle search for your clients!</strong> Here's how you can get a key. 1) Navigate to the <a href="https://code.google.com/apis/console/?pli=1">google api console</a>. 2) Login with your google account. 3) In the left menu, navigate to the services section. Scroll down to the Places Services and click the "on" switch. 4) Scroll back to the top, and click on the "API Access" option on the left side of the page. 5) At the bottom of the page click "Create New Server Key". A pop up will appear 6) Click the create button in the pop up (you don't need to enter anything). 7) Copy the new api key. Labeled "API Key" and paste it into the form below and click save. Reach out at <a mailto="support@placester.com">support@placester.com</a> if you have any questions or problems and we'll be happy to help you. </p>
		<div>
			<label for="google_places_api">Google Places API Key</label>
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
			<div id="community_pages_message"></div>
		</div>
		<ul>
			<li>
				<input id="error_logging_click" type="checkbox" name="error_logging" <?php echo $error_logging ? 'checked="checked"' : '' ?>>
				<label for="error_logging">You can help improve Placester. Allow the Real Estate Website Builder Plugin to anonymously report errors and usage information so we can fix errors and add new features.</label>
			</li>
		  <?php if (current_theme_supports('pls-community-pages')): ?>
			<li>
				<input id="enable_community_pages" type="checkbox" name="enable_community_pages" <?php echo $enable_community_pages ? 'checked="checked"' : '' ?>>
				<label for="enable_community_pages">Enable Community Pages</label>
			</li>
		  <?php endif; ?>
		</ul>
