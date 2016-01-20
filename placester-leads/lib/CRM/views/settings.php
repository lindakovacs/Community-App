<?php
	// Ensure the var containing info about the registered CRM providers is a valid array...
	if (!is_array($crm_list)) { return; }
?>

<div class="login-header-wrapper">
	<h2>Select a CRM Provider:</h2>
</div>

<div class="crm-options-grid">
	<?php foreach ($crm_list as $id => $info): ?>
		<div id="<?php echo $id; ?>-box" class="crm-box">
			<div class="login-logo">
				<img src="<?php echo $info["logo_img"]; ?>" />
			</div>
			<?php
				$crm_class = $info["class"];
				$crm_obj = new $crm_class();

				// Find out if this particular CRM has been "integrated"
				// (i.e., user has already entered account credentials for this provider)
				$api_key = $crm_obj->getAPIKey();
				$not_integrated = is_null($api_key);
			?>
			<div class="action-box">
				<?php if ($not_integrated): ?>
					<?php include("partials/integrate.php"); ?>
				<?php else: ?>
					<?php include("partials/activate.php"); ?>
				<?php endif; ?>
			</div>				
		</div>
	<?php endforeach; ?>
</div>

