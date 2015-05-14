<?php
global $shortcode_subpages, $page_now, $plugin_page;

$action = (empty($_REQUEST['action'])?'':$_REQUEST['action']);
$ID = (empty($_REQUEST['id'])?'':$_REQUEST['id']);
$notice = $message = '';
$nonce_action = 'edit-sc-template_' . $ID;
$template = PL_Shortcode_CPT::load_custom_template($ID);

if ($action == 'delete' && $ID) {
	if (!PL_Shortcode_CPT::template_in_use($ID)) {
		PL_Shortcode_CPT::delete_custom_template($ID);
	}
	wp_redirect(admin_url('admin.php?page=placester_shortcodes_templates'));
	die;
}
if ($action == 'copy') {
	if(!$template) {
		$shortcode = empty($_REQUEST['shortcode']) ? '' : $_REQUEST['shortcode'];
		$default = empty($_REQUEST['default']) ? '' : $_REQUEST['default'];
		if ($default && $shortcode)
			$template = PL_Shortcode_CPT::load_template($default, $shortcode);
	}
	if ($template['title']) {
		$template['title'] = 'Copy of '.$template['title'];
		$action = 'edit';
	}
	else {
		$action = '';
	}
	$ID = '';
}
if ($action == 'edit' && !empty($_POST['save']) && !empty($_POST['shortcode'])) {
	$data = array_merge($_POST, $_POST[$_POST['shortcode']]);
	if (empty($_POST['title'])) {
		$notice = 'Please provide a title for the template.';
	}
	else {
		if (!empty($_POST[$_POST['shortcode']])) {
			$id = PL_Shortcode_CPT::save_custom_template($ID, $data);
			if ($id) {
				wp_redirect('admin.php?page=placester_shortcodes_templates');
				die;
			}
		}
	}
	// unescape form fields
	foreach($data as $key=>&$val) {
		if (!is_array($val)) {
			$val = stripcslashes($val);
		}
	}
	$template = array_merge($template, $data);
}

$title = (empty($_REQUEST['title'])?$template['title']:$_REQUEST['title']);
$shortcode = (empty($_REQUEST['shortcode'])?$template['shortcode']:$_REQUEST['shortcode']);
$form_link = '';
$delete_link = $page_now.'?page='.$plugin_page.'&action=delete&id='.$ID;
$form_action = 'edit';
$used_by = PL_Shortcode_CPT::template_used_by($ID);

?>
<div class="pl-sc-wrap">
	<?php echo PL_Helper_Header::pl_subpages('placester_shortcodes', $shortcode_subpages, 'Create Shortcode Template'); ?>

	<div id="pl_sc_tpl_edit">
		<?php if ( $notice ) : ?>
		<div id="notice" class="error"><p><?php echo $notice ?></p></div>
		<?php endif; ?>
		<?php if ( $message ) : ?>
		<div id="message" class="updated"><p><?php echo $message; ?></p></div>
		<?php endif; ?>

		<p>
		Use this form to build a shortcode template that can be used to control the appearance of Placester shortcodes.
		</p>

		<div id="notice" class="hide-if-js error"><p>JavaScript is required to use the shortcode template editor. Please enable JavaScript on your browser and reload this page.</p></div>

		<form name="post" action="<?php echo $form_link?>" method="post" id="post"<?php do_action('post_edit_form_tag'); ?> class="hide-if-no-js">
			<?php wp_nonce_field($nonce_action); ?>
			<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
			<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ) ?>" />
			<input type="hidden" id="id" name="id" value="<?php echo esc_attr($ID) ?>" />

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div id="titlediv">
							<div id="titlewrap">
								<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo __( 'Enter a title for your template here' ); ?></label>
								<input type="text" name="title" size="30" value="<?php echo esc_attr( htmlspecialchars( $title ) ); ?>" id="title" autocomplete="off" title="<?php _e('Please enter a title for this shortcode.')?>" />
							</div>
						</div><!-- /titlediv -->

						<div id="pl-sc-tpl-meta-box" class="pl-sc-meta-box">
							<?php PL_Router::load_builder_partial('shortcode-template-box.php', array(
									'action'=>$action,
									'title'=>$title,
									'shortcode'=>$shortcode,
									'values'=>$template,
								));?>
						</div>
					</div><!-- /post-body-content -->

					<div id="postbox-container-1" class="postbox-container">
						<div id="submitdiv" class="postbox">
							<?php $action_title = ($ID=='' ? __('Create') : ($used_by ? __('Publish') : __('Update')))?>
							<h3 class="hndle"><span><?php echo $action_title;?></span></h3>
							<div class="inside">
								<div class="submitbox" id="submitpost">

									<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
									<div style="display:none;">
									<?php submit_button( __( 'Update' ), 'button', 'save' ); ?>
									</div>

									<div id="misc-publishing-actions">
										<div class="misc-pub-section">
											<span>Status:</span> <span
												id="post-status-display">
												<?php echo ($ID=='' ? __('Draft') : ($used_by ? '<a id="pl_sc_tpl_csc_link" href="#">'.__('In Use').'</a>' : __('Not In Use')))?></span>
											<div id="pl_sc_tpl_csc_list" style="display:none">
												<p>Used by the following custom shortcodes:</p>
												<?php foreach($used_by as $csc):?>
													- <a href="<?php echo admin_url('admin.php?page=placester_shortcodes_shortcode_edit&amp;ID='.$csc['ID'])?>"><?php echo $csc['post_title']?></a><br />
												<?php endforeach;?>
											</div>
										</div>
									</div>

									<div id="major-publishing-actions">
										<?php if ($ID && !$used_by):?>
										<div id="delete-action">
											<a class="submitdelete deletion" href="<?php echo $delete_link; ?>"><?php echo __('Delete'); ?></a>
										</div>
										<?php endif;?>
										<div id="publishing-action">
											<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php echo $action_title; ?>" />
										</div>
										<div class="clear"></div>
									</div>

								</div>
							</div>
						</div>
						<?php
						PL_Router::load_builder_partial('shortcode-preview.php', array('iframe'=>'', 'title'=>'Shortcode Template Preview'));
						?>
					</div>
				</div><!-- /post-body -->
			</div>
		</form>

		<div id="ajax-response"></div>
	</div>
</div>