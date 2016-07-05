<?php
// The file upload form used as target for the file upload widget
?>

<div class="row fileupload-buttonbar here" data-url="<?php echo admin_url('/admin-ajax.php'); ?>">
	<span class="btn btn-success fileinput-button">
		<input type="file" name="files[]" multiple="multiple" >
	</span>

	<div class="clear"></div>
	<div id="fileupload-holder-message">
		<?php if (isset($images)): ?>
			<?php usort($images, function ($a, $b) { return $a['order'] < $b['order'] ? -1 : 1; }); ?>
			<?php foreach ($images as $key => $image): ?>
				<li class="image_container">
					<div>
						<img width="100px" height="100px" src="<?php echo PL_Listing_Admin_Helper::ui_image_url($image); ?>"><a id="remove_image">Remove</a>
						<input id="hidden_images" type="hidden" name="images[]" value="<?php echo PL_Listing_Admin_Helper::ui_image_value($image); ?>">
					</div>
				</li>
			<?php endforeach ?>
		<?php endif ?>
	</div>
	<div class="clear"></div>
</div>
