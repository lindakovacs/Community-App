<?php
	// Render a contact's details...
	if (!isset($contact_id)) { return; }

	// If no CRM ID is passed, use the active one... 
	if (!isset($crm_id)) {
		$crm_id = PL_CRM_Controller::getActiveCRM();

		// No valid active CRM ID?  Just return...
		if (empty($crm_id)) { return; }
	}

	$crm_obj = PL_CRM_Controller::getCRMInstance($crm_id);
	$contact_data = $crm_obj->getContact($contact_id);
?>

<div class="contact-details-overlay"></div>

<div class="contact-details-pane">
	<?php // error_log(var_export($contact_data, true)); ?>
	<h2>Lead Detail: <span><?php echo $contact_data["name"]; ?></span></h2>
	<table>
		<?php foreach ($contact_data["details"] as $key => $value): ?>
			<tr>
				<td class="field-key"><?php echo $key; ?></td>
				<td class="field-value"><?php echo (empty($value) ? "N/A" : $value); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>