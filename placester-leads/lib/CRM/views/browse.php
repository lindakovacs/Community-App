<?php

if (!is_array($crm_info)) { return; }
extract($crm_info);

$crm_obj = new $class();
$field_labels = $crm_obj->contactFieldLabels();

$table_id = "contacts_grid";

?>

<div class="crm-browse-box">
	<div class="browse-header">
		<?php if(PL_CRM_Controller::isActiveCRM($id)) : // as opposed to the internal CRM ?>
			<h2>Showing contacts from <?php echo $display_name; ?></h2>
			<a href="#" class="settings-button button-secondary">CRM Settings</a>
		<?php endif ?>
	</div>

	<div class="crm-search-box">
		<?php echo $crm_obj->generateContactSearchForm(); ?>
	</div>

	<div class="grid-container" style="width: 99%">
		<table id="<?php echo $table_id; ?>" data-crm-id="<?php echo $id; ?>" class="widefat post" cellspacing="0">
			<thead>
			<tr>
				<?php foreach ($field_labels as $label): ?>
					<th><span><?php echo $label; ?></span></th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody></tbody>
			<tfoot>
			<tr>
				<?php for ($i = 0; $i < count($field_labels); $i++): ?>
					<th></th>
				<?php endfor; ?>
			</tr>
			</tfoot>
		</table>
	</div>
</div>
