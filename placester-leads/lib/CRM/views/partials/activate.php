<?php
	// Returns activation functionality for turning on an integrated CRM.

	if (empty($id))
		return;

	if (empty($api_key)) {
		$crm_obj = PL_CRM_Controller::getCRMInstance($id);
		$api_key = $crm_obj->getAPIKey();
	}
?>

<div class="activate-crm-box">
	<div class="current-key">
		API Key: <span><?php echo $api_key; ?></span>
	</div>
	<div class="button-group">
		<?php if (PL_CRM_Controller::isActiveCRM($id)): ?>
			<a href="#" id="browse_<?php echo $id ?>" class="browse-button button-secondary">Browse Contacts</a>
			<a href="#" id="deactivate_<?php echo $id ?>" class="deactivate-button button-primary">Deactivate</a>
		<?php else: ?>
			<a href="#" id="reset_<?php echo $id ?>" class="reset-creds-button button-secondary">Enter new API Key</a>
			<a href="#" id="activate_<?php echo $id ?>" class="activate-button button-primary">Activate</a>
		<?php endif; ?>
	</div>
</div>