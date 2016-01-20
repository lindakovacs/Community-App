<?php
global $shortcode_subpages, $page_now, $plugin_page;

$action = empty($_REQUEST['action']) ? 'edit' : $_REQUEST['action'];
$id = empty($_REQUEST['id']) ? '' : $_REQUEST['id'];
$notice = $message = '';
$nonce_action = 'edit-sc-template_' . $id;
$template = PL_Listing_Customizer::get_template($id);
$listing_attributes = PL_Shortcode_CPT::get_listing_attributes();

if ($action == 'delete' && $id) {
	if (!PL_Listing_Customizer::template_used_by($id)) {
		PL_Listing_Customizer::delete_template($id);
	}
	wp_redirect(admin_url('admin.php?page=placester_shortcode_details'));
	die;
}
if ($action == 'edit' && !empty($_POST['save'])) {
	// trying to save changes
	if (empty($_POST['title'])) {
		$notice = 'Please provide a title for the template.';
	}
	else {
		$id = PL_Listing_Customizer::save_template($id, $_POST);
		if ($id) {
			$message = 'Settings saved.';
		}
	}
	// unescape form fields
	foreach($_POST as $key=>&$val) {
		if (!is_array($val)) {
			$val = stripcslashes($val);
		}
	}
	$template = array_merge($template, $_POST);
}
else if ($action == 'copy' && !empty($id)) {
	// copying an exiting template
	$template = PL_Listing_Customizer::get_template($id);
	if (!empty($template['title'])) {
		$template['title'] = 'Copy of '.$template['title'];
	}
	$id = '';
}
else if (empty($id)) {
	// get a built in if we are starting with a blank template
	$tpl = PL_Listing_Customizer::get_builtin_templates();
	$template = PL_Listing_Customizer::get_template(key($tpl));
	$template['title'] = '';
}

$form_link = '';
$delete_link = $page_now.'?page='.$plugin_page.'&action=delete&id='.$id;
$form_action = 'edit';
$used_by = PL_Listing_Customizer::template_used_by($id);
$tpl_args = PL_Listing_Customizer::get_args();

// pass data to our javascript -- possible because it's queued for the footer
wp_localize_script('listing-customizer', 'autosaveL10n', array('saveAlert' => __('The changes you made will be lost if you navigate away from this page.')));

?>
<div class="pl-sc-wrap">
	<div id="pl_sc_tpl_edit">
		<?php if ( $notice ) : ?>
		<div id="notice" class="error"><p><?php echo $notice ?></p></div>
		<?php endif; ?>
		<?php if ( $message ) : ?>
		<div id="message" class="updated"><p><?php echo $message; ?></p></div>
		<?php endif; ?>

		<p>
		Use this form to build a template that can be used to customize the page that displays an individual property listing.
		</p>

		<div id="notice" class="hide-if-js error"><p>JavaScript is required to use the template editor. Please enable JavaScript on your browser and reload this page.</p></div>

		<form name="post" action="<?php echo $form_link?>" method="post" id="post"<?php do_action('post_edit_form_tag'); ?> class="hide-if-no-js">
			<?php wp_nonce_field($nonce_action); ?>
			<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
			<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ) ?>" />
			<input type="hidden" id="id" name="id" value="<?php echo esc_attr($id) ?>" />

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">

						<div id="titlediv">
							<div id="titlewrap">
								<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo __( 'Enter a title for your template here' ); ?></label>
								<input type="text" name="title" size="30" value="<?php echo esc_attr( htmlspecialchars( empty($template['title']) ? '' : $template['title']) ); ?>" id="title" autocomplete="off" title="<?php _e('Please enter a title for this shortcode.')?>" />
							</div>
						</div><!-- /titlediv -->

						<div id="pl-sc-tpl-meta-box" class="pl-sc-meta-box">
							<div class="postbox ">
								<h3>Listing Page Template</h3>

								<div class="inside">

									<!-- Template Contents -->
									<section class="row-fluid lc-meta-section">

										<!-- Template HTML/CSS -->
										<div class="span8">
											<div class="pl_template_block">
												<?php
												foreach($tpl_args['template'] as $field => $f_args) {
													$_POST[$field] = !empty( $template[$field] ) ? $template[$field] : '';
													$f_args['css'] = (!empty($f_args['css'])?$f_args['css'].' ':'').$field;
													PL_Form::item($field, $f_args, 'POST', '', 'pl-lc-tpl-edit', true);
												}?>
											</div>
										</div>

										<!-- Search Sub-Shortcodes -->
										<div id="subshortcodes" class="span2">
											<h3>Template Tags</h3>
											<?php $template_tags = '';?>
											<?php foreach($tpl_args['template_tags'] as $template_tag=>$atts): ?>
												<?php $template_tags .= '<h4 class="subcode"><a href="#">[' . $template_tag . ']</a></h4>';?>
												<?php if (!empty($atts['help'])):?>
													<?php $template_tags .= '<div class="description subcode-help">'. $atts['help'];?>
													<?php if ($template_tag=='custom' || $template_tag=='if'): ?>
														<?php $template_tags = $template_tags . '<br />Click <a href="#" class="show_listing_attributes">here</a> to see a list of available listing attributes.';?>
													<?php endif;?>
													<?php $template_tags .= '</div>';?>
												<?php endif;?>
											<?php endforeach;?>
											<p>Use the following tags to customize the Page Body of your template. When the template is rendered in a web page, the tag will be replaced with the corresponding attribute of the property listing:<br /><?php echo $template_tags?></p>
										</div>
										<div id="listing_attributes" style="display:none;">
											<table>
												<tr>
													<th>Listing Field</th>
													<th>Attribute</th>
													<th>Group</th>
												</tr>
												<?php foreach($listing_attributes as $attr) :?>
													<tr>
														<td><strong><?php echo $attr['label']?></strong></td>
														<td><?php echo $attr['attribute']?></td>
														<td>
														<?php if ($attr['group']):?>
															<?php echo $attr['group']?>
														<?php endif;?>
														</td>
													</tr>
												<?php endforeach;?>
											</table>
										</div>
										
									</section><!-- /Template Contents -->

									<?php wp_nonce_field( 'pl_sc_meta_box_nonce', 'meta_box_nonce' );?>

									<div class="clear"></div>

								</div><!-- /.inside -->

							</div><!-- /.postbox -->
						</div><!-- /pl-lc-tpl-meta-box -->

					</div><!-- /post-body-content -->


					<div id="postbox-container-1" class="postbox-container">
						<div id="submitdiv" class="postbox">
							<?php $action_title = ($id=='' ? __('Create') : ($used_by ? __('Publish') : __('Update')))?>
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
												<?php echo ($id=='' ? __('Draft') : ($used_by ? __('In Use') : __('Not In Use')))?></span>
										</div>
									</div>

									<div id="major-publishing-actions">
										<?php if ($id && !$used_by):?>
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
					</div><!-- /postbox-container-1 -->


				</div><!-- /post-body -->
			</div>

		</form>

		<div id="ajax-response"></div>
	</div>
</div>