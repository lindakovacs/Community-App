<?php
	if (empty($contact_id)) { return; }

	if (!isset($crm_id)) {
		$crm_id = PL_CRM_Controller::getActiveCRMs();
		if (empty($crm_id)) { return; }
	}

	$crm_obj = PL_CRM_Controller::getCRMInstance($crm_id);
	$contact_data = $crm_obj->getContact($contact_id);
?>

<div class="contact-details-overlay"></div>

<div class="contact-details-pane">
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