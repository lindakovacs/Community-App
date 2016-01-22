<?php
/**
 * Generates preview metabox used in the shortcode edit view
 */
$title = empty($title) ? 'Preview' : $title;
?>
<div id="pl-previewer-metabox-id" class="postbox ">
	<h3 class="hndle"><span>Preview</span></h3>
	<div class="inside">
		<div id='preview-wrapper'>
			<div class="preview_load_spinner" style="display:none;">
				<img src="<?php echo PL_SHORTCODES_CSS_URL . 'spinner.gif'; ?>" alt="Widget options are Loading..." />
			</div>
			<div id='preview_meta_widget'><?php echo $iframe;?></div>
			<div id="pl-review-wrapper">
				<a href="" class="pl_review_link button" style="display:none;" title="<?php echo $title;?>">Preview in a popup window</a>
				<div id="pl_review_popup" class="dialog" style="display:none;">Loading preview...</div>
			</div>
		</div>
	</div>
</div>