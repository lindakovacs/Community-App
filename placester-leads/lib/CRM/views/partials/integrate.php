<?php 
	// Display when no creds are stored for this CRM, allow user to enter them OR sign-up for a new account.
	//
	// NOTE: $id string and $info array should be defined by the script including this one...

	if (empty($id)) { return; }

	// Populate the CRM info object if it's not set...
	if (empty($info)) {
		$info = PL_CRM_Controller::getCRMInfo($id);
	}
?>

<div class="integrate-crm-box">
	<div class="enter-creds">
		<span>Enter your API Key:</span>
		<input id="<?php echo $id; ?>_api_key" class="api-key-field" type="text" />
		<a href="#" id="integrate_<?php echo $id; ?>" class="integrate-button button-secondary">Integrate</a>
	</div>
	<div class="cred-lookup">
		<span> Don't know your API key?</span>
		<a href="<?php echo $info["cred_lookup_url"]; ?>" class="api-lookup" target="blank">Find it here</a>
	</div>
	<div class="sign-up">
		<span>Don't have an account with this provider?</span>
		<a href="<?php echo $info["referral_url"]; ?>" target="_blank">Sign-up here</a>
	</div>
</div>