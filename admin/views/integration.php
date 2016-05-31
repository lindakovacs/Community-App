<?php


require_once(trailingslashit(PLACESTER_PLUGIN_DIR) . 'libnew/attributes.php');
PLX_Dynamic_Attributes::check_attributes();


$integration_obj = PL_Integration_Helper::get();
extract($integration_obj);
?>

<?php if (!empty($integration_status['integration'][0]['id'])): ?>
	<div class="header-wrapper">
		<h2>You have an integration pending</h2>
	</div>
	<div class="clear"></div>
	<div class="integration-wrapper">
		<?php
			$integration_box = new PL_Admin_Box(null, 'Pending Integration Status');
			$integration_box->open();

			foreach ($integration_status['integration'] as $integration): ?>
				<div class="integration">
					<h3><?php echo $integration['mls_name'] ?> <span>(status: <?php echo $integration['status'] ?>)</span></h3>
					<ul>
						<li>
							<div>Date Sumbitted:</div>
							<div><?php echo date_format(date_create($integration['created_at']), "jS F, Y g:i A." ); ?></div>
						</li>
						<li>
							<div>Last Updated:</div>
							<div><?php echo date_format(date_create($integration['updated_at']), "jS F, Y g:i A."); ?></div>
						</li>
						<li>
							<div>Url:</div>
							<div><?php echo $integration['url'] ?></div>
						</li>
						<li>
							<div>Request Id: </div>
							<div><?php echo $integration['id'] ?> <span class="small">(for support)</span></div>
						</li>
					</ul>
				</div>
			<?php endforeach;

			$integration_box->close();
		?>
		<p>Looking for multiple MLS integrations? Drop us a note at <a mailto="support@placester.com">support@placester.com</a> or give us a ring at (800) 728-8391 and we'll get you set up.</p>
	</div>
	<div class="help_prompt integration">
		<h3>Need Help?</h3>
		<ul>
			<li>Not sure where to get this information?</li>
			<li>Having trouble submitting the form?</li>
			<li>Have questions about how MLS integrations work?</li>
		</ul>
		<div class="real_person">
			<h3>Talk to a real person.</h3>
			<h4>Call us at 1 (800) 728-8391</h4>
			<h4>Email us at  <a mailto="support@placester.com"> support@placester.com</a></h4>
		</div>
	</div>
<?php endif ?>

<?php if (!empty($integration_status['whoami']['provider']['id'])): ?>
	<div class="header-wrapper">
		<h2>Your website is linked to <?php echo $integration_status['whoami']['provider']['name'] ?></h2>
	</div>
	<p class="import-message">(Last import was <?php echo date_format(date_create($integration_status['whoami']['provider']['last_import']), "jS F, Y g:i A.") ?>)</p>
	<?php
		$integration_box = new PL_Admin_Box(null, 'Listing Stats');
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
	<?php

		$integration_box->close();
	?>
	<p>Looking for multiple MLS integrations? Drop us a note at <a mailto="support@placester.com">support@placester.com</a> or give us a ring at (800) 728-8391 and we'll get you set up.</p>
<?php endif ?>

<?php if (empty($integration_status['integration'][0]['id']) && empty($integration_status['whoami']['provider']['id']) ): ?>
	<div class="header-wrapper">
		<h2>Link your Website to your local MLS</h2>
	</div>
	<div class="clear"></div>
	<p>The Real Estate Website Builder plugin can pull listings from your local MLS using a widely supported format called RETS. Once activated, the plugin will automatically update your website with listings as they are added, edited, and removed. All regulatory and compliance concerns will be handled automatically so long as you are using a theme built for the real estate website builder plugin (see <a href="https://placester.com/wordpress-themes/">here</a> for a complete list).</p>
	<p>Please note that MLS integrations require a <a href="https://placester.com/subscription/">Premium Subscription</a> to Placester which is $45 per month. Fill out the form below to get started.</p>
	<div class="clear"></div>

	<?php if (PL_Option_Helper::api_key()): ?>
		<?php
			$submit = true;
			include 'partials/integration-form.php';
		?>
	<?php endif; ?>

	<div class="clear"></div>
<?php endif ?>
