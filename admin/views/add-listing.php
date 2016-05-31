<?php


require_once(trailingslashit(PLACESTER_PLUGIN_DIR) . 'libnew/attributes.php');
require_once(trailingslashit(PLACESTER_PLUGIN_DIR) . 'libnew/forms.php');


if(isset($_GET['id'])) {
	if($_POST['action'] == 'update_listing' && isset($_POST['id']) && $_GET['id'] == $_POST['id']) {
		$api_response = PL_Listing_Admin_Helper::update_listing();
		if(is_array($api_response) && isset($api_response['id'])) {
			$_POST = array(); // update listing successful, reread it from the server
			$view_url = site_url('/property/' . $api_response['id']);
			$edit_url = admin_url('admin.php?page=placester_property_edit&id=' . $api_response['id']);
			$message = '<div id="message" class="updated below-h2"><p>
				Listing successfully updated! You may
					<a href="' . $view_url . '" class="button-secondary">View</a> or
					<a href="' . $edit_url . '" class="button-secondary">Edit</a>
				</p></div>';
		}
		else if(is_array($api_response) && isset($api_response['message']))
			$message = $api_response['message'];
		else
			$message = 'Update failed';
	}
	else
		$_POST = array();

	if(empty($_POST)) {
		$listing = PL_Listing::read(array('id' => $_GET['id']));
		$_POST = $listing;
	}
}

else if(isset($_POST['action']) && $_POST['action'] == 'add_listing') {
	$api_response = PL_Listing_Admin_Helper::add_listing();
	if(is_array($api_response) && isset($api_response['id'])) {
		$_POST = array(); // add listing successful, add another
		$view_url = site_url('/property/' . $api_response['id']);
		$edit_url = admin_url('admin.php?page=placester_property_edit&id=' . $api_response['id']);
		$message = '<div id="message" class="created below-h2"><p>
				Listing successfully created! You may
					<a href="' . $view_url . '" class="button-secondary">View</a> or
					<a href="' . $edit_url . '" class="button-secondary">Edit</a>
				</p></div>';
	}
	else if(is_array($api_response) && isset($api_response['message']))
		$message = $api_response['message'];
	else
		$message = 'Create failed';
}

else
	$_POST = array();


// fill in default country for new listing
if(empty($_POST)) $_POST = array('country' => PL_Helper_User::get_default_country());

?>


<?php if (isset($_GET['id'])): ?>
	<div id="loading_overlay" style="display:none">Updating Listing...</div>
<?php else: ?>
	<div id="loading_overlay" style="display:none">Creating Listing...</div>
<?php endif ?>

<div id="manage_listing_message"><?php echo isset($message) ? $message : ''; ?></div>

<form method="POST" enctype="multipart/form-data" id="add_listing_form">
	<?php if (isset($_GET['id'])): ?>
		<input id="hidden-form-action" type="hidden" name="action" value="update_listing">
		<input id="hidden-property-id" type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
	<?php else: ?>
		<input id="hidden-form-action" type="hidden" name="action" value="add_listing">
	<?php endif ?>

	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">

				<?php include('partials/publish-box-sidebar.php'); ?>

			</div>
		</div>
		<div id="post-body">
			<div id="post-body-content">
				<?php $form = new PLX_Add_Listing_Form(); $form_attributes = $form->get_form_attributes(); ?>
				<div id="shared-Listing-attributes">
					<?php foreach($form_attributes['Listing'] as $attribute)
						echo $form->get_form_item($attribute['name']); ?>
				</div>

				<div id="shared-Location-attributes" class="postbox">
					<h3 class="hndle"><span><?php echo PLX_Attributes::get_group_title('Location'); ?></span></h3>
					<div class="inside">
						<div id="location-entry">
							<?php foreach($form_attributes['Location'] as $attribute)
								if(!in_array($attribute['name'], array('latitude', 'longitude')))
									echo $form->get_form_item($attribute['name']); ?>
						</div>
						<div id="location-map-canvas"></div>
						<div id="location-coord-display">
							<?php echo $form->get_form_item('latitude'); ?>
							<?php echo $form->get_form_item('longitude'); ?>
						</div>
					</div>
				</div>

				<?php foreach($form_attributes['basic'] as $type => $groups) { ?>
					<div id="<?php echo "basic-$type-attributes"; ?>" class="<?php echo "basic-attributes $type-attributes"; ?>" style="display: none;">
						<?php foreach($groups as $group => $attributes) { ?>
							<div id="<?php echo "basic-$type-$group-attributes"; ?>" class="postbox">
								<h3 class="hndle"><span><?php echo PLX_Attributes::get_group_title($group, $type); ?></span></h3>
								<div class="inside">
									<?php foreach($attributes as $attribute)
										echo $form->get_form_item($attribute['name']); ?>
								</div>
							</div>
						<?php } ?>
					</div>
				<?php } ?>

				<?php foreach($form_attributes['extended'] as $type => $groups) { ?>
					<div id="<?php echo "extended-$type-attributes"; ?>" class="<?php echo "extended-attributes $type-attributes"; ?>" style="display: none;">
						<?php foreach($groups as $group => $attributes) { ?>
							<div id="<?php echo "extended-$type-$group-attributes"; ?>" class="postbox">
								<h3 class="hndle"><span><?php echo PLX_Attributes::get_group_title($group, $type); ?></span></h3>
								<div class="inside">
									<?php foreach($attributes as $attribute)
										echo $form->get_form_item($attribute['name']); ?>
								</div>
							</div>
						<?php } ?>
					</div>
				<?php } ?>

				<div id="listing-images" class="postbox">
					<h3 class="hndle"><span>Images</span></h3>
					<div class="inside">
						<?php
							$images = $_POST['images'];
							include 'partials/add-listing-image.php';
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br class="clear">
</form>
