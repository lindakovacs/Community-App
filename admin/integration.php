<?php


PL_Admin_Integration::init();

class PL_Admin_Integration extends PL_Admin_Page {
	public function __construct() {
		parent::__construct('placester', 200, 'placester_integration', 'IDX/MLS', 'IDX/MLS', null);
		$this->require_script('placester-integration', PL_ADMIN_JS_URL . 'integration.js');
		$this->require_style('placester-integration', PL_ADMIN_CSS_URL . 'integration.css');
	}

	public function render_admin_content() {
		$integration_status = $this->get_integration_status();

		if (!empty($integration_status['whoami']['provider']['id'])) { ?>
			<div class="header-wrapper">
				<h2>Your website is linked to <?php echo $integration_status['whoami']['provider']['name'] ?></h2>
			</div>
			<p class="import-message">(Last import was <?php echo date_format(date_create($integration_status['whoami']['provider']['last_import']), "jS F, Y g:i A.") ?>)</p>

			<?php $integration_box = new PL_Admin_Box(null, 'Listing Stats');
				$integration_box->open(); ?>

			<div class="c4">
				<p class="large-number"><?php echo number_format($integration_status['listings']['total']); ?></p>
				<p class="label">Listings</p>
			</div>
			<div class="c4">
				<p class="large-number"><?php echo count($integration_status['locations']['locality']) ?></p>
				<p class="label">Cities</p>
			</div>
			<div class="c4">
				<p class="large-number"><?php echo count($integration_status['locations']['postal']) ?></p>
				<p class="label">Zips</p>
			</div>
			<div class="c4 omega">
				<p class="large-number"><?php echo count($integration_status['locations']['region']) ?></p>
				<p class="label">States</p>
			</div>

			<?php $integration_box->close(); ?>

			<p>Looking for multiple MLS integrations? Drop us a note at <a mailto="support@placester.com">support@placester.com</a> or give us a ring at (800) 728-8391 and we'll get you set up.</p>
		<?php }

		else if (!empty($integration_status['integration']['id'])) {
			switch($integration_status['integration']['status']) {
				case 4:
				case 'Approved - Awaiting Activation':
					$status = ' was approved';
					break;
				case 1:
				case 'Rejected':
					$status = ' was rejected';
					break;
				case 3:
				case 'Completed':
					$status = ' was completed';
					break;
				case 'In Progress':
					$status = ' is pending';
					break;
				default:
					if(!is_numeric($integration_status['integration']['status']))
						$status = ' has status: ' . $integration_status['integration']['status'];
					else
						$status = ' is pending';
			}
			?>

			<div class="header-wrapper">
				<h2>Your integration request <?php echo $integration_status['integration']['id'] . $status; ?></h2>
			</div>
			<p class="import-message">(Submitted <?php echo date_format(date_create($integration_status['integration']['created_at']), "jS F, Y g:i A.") ?>)</p>

			<?php $integration_box = new PL_Admin_Box(null, 'Listing Stats');
			$integration_box->open(); ?>

			<div class="c4">
				<p class="large-number">&nbsp;</p>
				<p class="label">Listings</p>
			</div>
			<div class="c4">
				<p class="large-number">&nbsp;</p>
				<p class="label">Cities</p>
			</div>
			<div class="c4">
				<p class="large-number">&nbsp;</p>
				<p class="label">Zips</p>
			</div>
			<div class="c4 omega">
				<p class="large-number">&nbsp;</p>
				<p class="label">States</p>
			</div>

			<?php $integration_box->close(); ?>

			<p>Looking for multiple MLS integrations? Drop us a note at <a mailto="support@placester.com">support@placester.com</a> or give us a ring at (800) 728-8391 and we'll get you set up.</p>
		<?php }

		else { ?>

			<div class="header-wrapper">
				<h2>Link your Website to your local MLS</h2>
			</div>

			<p>The Real Estate Website Builder plugin can pull listings from your local MLS using a widely supported format called RETS. Once activated, the plugin will automatically update your website with listings as they are added, edited, and removed.
				Note that MLS integrations require a <a href="https://placester.com/subscription/">Premium Subscription</a> to Placester which is $45 per month.</p>

			<?php if ($integration_status['whoami'])
				$this->render_integration_form();
		}
	}

	public function render_integration_form() {
		$whoami = PL_User::whoami();
		$phone = !empty($whoami['phone']) ? $whoami['phone'] : (!empty($whoami['user']['phone']) ? $whoami['user']['phone'] : '');
		$email = !empty($whoami['email']) ? $whoami['email'] : (!empty($whoami['user']['email']) ? $whoami['user']['email'] : '');

		$mls_list = PL_Integration::mls_list();
		?>

		<div class="ajax_message" id="rets_form_message"></div>

		<div class="rets_form">
			<form id="pls_integration_form">
				<div class="row">
					<div class="info">
						<h3>MLS Provider</h3>
						<p>Pick which MLS provides your listing data.</p>
					</div>
					<div class="elements">
						<p>
							<strong>Email us at <a href="mailto:support@placester.com">support@placester.com</a> if you don't see your MLS listed.</strong>
						</p>

						<select id="mls_id" name="mls_id">
							<option value=""> --- </option>
							<?php if(is_array($mls_list)) foreach($mls_list as $mls_group => $mls_arr): ?>
								<optgroup label="<?php echo $mls_group; ?>">
									<?php foreach ($mls_arr as $mls_pair): ?>
										<option value="<?php echo $mls_pair[1]; ?>"><?php echo $mls_pair[0]; ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="row">
					<div class="info">
						<h3>Office Name</h3>
						<p>The name of your office or brokerage.</p>
					</div>
					<div class="elements">
						<input id="office_name" name="office_name" size="30" type="text" />
					</div>
				</div>

				<div class="row">
					<div class="info">
						<h3>Agent ID</h3>
						<p>Unique ID used to login to your MLS.</p>
					</div>
					<div class="elements">
						<input id="feed_agent_id" name="feed_agent_id" size="30" type="text" />
					</div>
				</div>

				<div class="row">
					<div class="info">
						<h3>Agent Email Address</h3>
						<p>The email address of the agent, used for MLS correspondence.</p>
					</div>
					<div class="elements">
						<input id="feed_agent_email" name="feed_agent_email" size="30" type="text" value="<?php echo $email ?>">
					</div>
				</div>

				<div class="row">
					<div class="info">
						<h3>Broker Email Address</h3>
						<p>Broker approval is required by some MLS associations.</p>
					</div>
					<div class="elements">
						<input id="broker_email" name="broker_email" size="30" type="text" value="<?php echo $email ?>">
					</div>
				</div>

				<div class="row">
					<div class="info">
						<h3>Phone Number</h3>
						<p>This will help us provide prompt support to get your integration setup.</p>
					</div>
					<div class="elements">
						<input id="phone" name="phone" size="30" type="text" value="<?php echo $phone ?>">
					</div>
				</div>

				<div class="row">
					<input type="submit" class="button-primary" />
				</div>
			</form>
		</div>

		<?php
	}

	public function get_integration_status() {
		return array('whoami' => PL_User::whoami(), 'integration' => PL_Integration::get(),
			'listings' => PLX_Search::listings(array('limit' => 1)), 'locations' => PLX_Search::locations());
	}


	public static function init() {
		$pl_admin_page = new self();
		add_action('wp_ajax_check_subscription', array(__CLASS__, 'check_subscription' ) );
		add_action('wp_ajax_create_integration', array(__CLASS__, 'create_integration' ) );
	}

	public static function check_subscription() {
		echo json_encode(PL_User::subscriptions());
		die();
	}

	public static function create_integration () {
		$api_response = PL_Integration::create(wp_kses_data($_POST));

		if (isset($api_response['id']))
			$response = array('result' => true, 'message' => 'You\'ve successfully submitted your integration request. This page will update momentarily');

		else if (isset($api_response['validations']))
			$response = $api_response;

		else if (isset($api_response['code']) && $api_response['code'] == '102')
			$response = array('result' => false, 'message' => 'You are already integrated with an MLS. To enable multiple integrations call sales at (800) 728-8391');

		else
			$response = array('result' => false, 'message' => 'There was an error. Please try again.');

		echo json_encode($response);
		die();
	}
}