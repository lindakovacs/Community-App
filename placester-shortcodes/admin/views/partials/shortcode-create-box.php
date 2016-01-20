<?php
/**
 * Displays main shortcode edit meta box used in the shortcode edit view for shortcodes related to property listings
 */

$options_class = $filters_class = '';

function placester_shortcode_template_list($shortcode, $post_type, $select_name, $class, $value) {
	include 'shortcode-template-list.php';
}

?>
<div class="postbox">

	<h3>Create Shortcode</h3>

	<div class="inside">

		<!-- Type and Template -->
		<div class="sc_type_and_template">

			<!-- Type -->
			<div id="choose_type" class="row-fluid">

				<div class="span2">
					<label class="section-label" for="pl_sc_shortcode_type">Type:</label>
				</div>

				<div class="span8">

					<select id="pl_sc_shortcode_type" name="shortcode" class="">

						<option id="pl_sc_shortcode_undefined" value="undefined">Select</option>

						<?php
						foreach( $pl_shortcodes_attr as $pl_shortcode => $sct_args ):
							$link_class = ($pl_shortcode == $values['shortcode']) ? 'selected-type' : '';
							$selected = ( !empty($link_class) ) ? 'selected="selected"' : '';
							?>
							<option id="pl_sc_shortcode_<?php echo $sct_args['shortcode']; ?>" class="<?php echo $link_class; ?>" value="<?php echo $pl_shortcode; ?>" <?php echo $selected; ?>>
								<?php echo $sct_args['title']; ?>
							</option>
							<?php
							// build our class list for the Options and Templates sections in this loop
							if (!empty($sct_args['options'])) {
								$options_class .= ' '.$pl_shortcode;
							}
							if (!empty($sct_args['filters'])) {
								$filters_class .= ' '.$pl_shortcode;
							}
						endforeach;
						?>
					</select>

				</div>

			</div><!-- /type -->

			<!-- Template / Layout -->
			<div id="choose_template" class="row-fluid" style="display:none;">
				<div class="span2">
					<label class="section-label" for="pl_template">Template:</label>
				</div>
				<div class="span8">
					<?php foreach( $pl_shortcodes_attr as $pl_shortcode => $sct_args ): ?>
						<?php if(!empty($sct_args['options']['context'])):?>
							<div class="pl_template_block <?php echo $pl_shortcode; ?>" id="<?php echo $sct_args['shortcode'];?>_template_block" style="display: none;">
								<?php
									$value = isset( $values[$pl_shortcode]['context'] ) ? $values[$pl_shortcode]['context'] : $sct_args['options']['context']['default'];
									placester_shortcode_template_list($sct_args['shortcode'], $pl_shortcode, $pl_shortcode . '[context]', '', $value);
								?>
							</div>
						<?php endif;?>
					<?php endforeach;?>
					<div class="edit-sc-template-edit">
						<a id="edit_sc_template_edit" href="" class="create-new-template-link" style="display:none;">Edit</a>
						<a id="edit_sc_template_duplicate" href="" class="duplicate-new-template-link">Duplicate</a>
						<a id="edit_sc_template_copy" href="" class="create-new-template-link">Copy</a>
						<a id="edit_sc_template_create" href="" class="create-new-template-link">Create</a>
					</div>
				</div>
			</div><!-- /edit-sc-choose-template -->

		</div><!-- /#post_types_list -->


		<!-- Options / Filters -->
		<div id="widget_meta_wrapper"  class="sc-meta-section" style="display: none;">

			<div class="pl_widget_block <?php echo $options_class;?>">
				<div>
					<h3>Options:</h3>
				</div>
				<?php
				// build options for each shortcode type
				foreach( $pl_shortcodes_attr as $pl_shortcode => $sct_args ) {?>
					<div class="pl_widget_block sc_options <?php echo $pl_shortcode;?>">
					<?php
					foreach($sct_args['options'] as $field => &$f_args) {
						if ($field == 'context') {
							// template field already handled
							continue;
						}
						elseif ($field == 'pl_featured_listing_meta') {
							// create button and placeholder for selected listings
							echo PL_Shortcode_Listing_Chooser::init(array(
								'value' => array(
									'name' => 'Featured Meta',
									'desc' => '',
									'id' => $pl_shortcode.'-pl_featured_listing_meta',
									'type' => 'featured_listing'
								),
								'val' => $pl_featured_meta_value,
								'option_name' => $pl_shortcode,
								'iterator' => false,
								'for_slideshow' => false
								));
							continue;
						}
						$value = isset( $values[$pl_shortcode][$field] ) ? $values[$pl_shortcode][$field] : $f_args['default'];
						$_POST[$pl_shortcode][$field] = $value;
						$f_args['css'] = (!empty($f_args['css']) ? $f_args['css'].' ' : '') . $pl_shortcode;
						PL_Form::item($field, $f_args, 'POST', $pl_shortcode, 'sc_edit_', true);
					}
					?>
					</div>
					<?php
				}
				?>
			</div>

			<div class="pl_widget_block <?php echo $filters_class;?>">
				<div>
					<h3>Filters:</h3>
				</div>
				<?php
				// fill POST array for the forms (required after new widget is created)
				$js_filters = array();
				foreach( $pl_shortcodes_attr as $pl_shortcode => $sct_args ) {
					if (!empty($sct_args['filters'])) {
						?>
						<div class="pl_widget_block filters <?php echo $pl_shortcode?>">
							<?php
								$select = $filter = $cat = '';
								foreach($sct_args['filters'] as $f_args) {
									$f_args['css'] = (!empty($f_args['css']) ? $f_args['css'].' ' : '') . $pl_shortcode;
									$parent = 'filter_options['.$pl_shortcode.']';
									$selectvalue = 'sc_edit-filter_options-'.$pl_shortcode.'-';
									$value = '';
									if ($f_args['group']) {
										$parent .= '['.$f_args['group'].']';
										$selectvalue .= $f_args['group'] . '-';
										if (isset( $values[$pl_shortcode][$f_args['group']][$f_args['attribute']] )) {
											$js_filters[] = array(
												'shortcode'=>$pl_shortcode,
												'id'=>$selectvalue.$f_args['attribute'],
												'value'=>$values[$pl_shortcode][$f_args['group']][$f_args['attribute']]);
										}
									}
									else {
										if (isset( $values[$pl_shortcode][$f_args['attribute']] )) {
											$js_filters[] = array(
												'shortcode'=>$pl_shortcode,
												'id'=>$selectvalue.$f_args['attribute'],
												'value'=>$values[$pl_shortcode][$f_args['attribute']]);
										}
									}
									$filter .= PL_Form::item($f_args['attribute'], $f_args, 'POST', $parent, 'sc_edit-', false);
									if ($cat!=$f_args['cat']) {
										if ($cat) {
											$select .= '</optgroup>';
										}
										$cat = $f_args['cat'];
										$select .= '<optgroup label="'.$cat.'">';
									}
									$select .='<option value="'.$selectvalue.$f_args['attribute'].'">'.$f_args['label'].'</option>';
								}
								if ($cat) {
									$select .= '</optgroup>';
								}
							?>
							<select name="<?php echo $pl_shortcode ?>[filter]" class="filter_select"><?php echo $select ?></select>
							<div class="pl_filters">
								<?php echo $filter ?>
							</div>
							<a href="#" class="button-secondary add_filter">Add Filter</a>
							<div class="active_filters"></div>
						</div>
						<?php
					}
				}
				?>
				<script>var active_filters = <?php echo json_encode($js_filters) ?>;</script>
			</div><!-- /.pl_widget_block -->

			<?php wp_nonce_field( 'pl_cpt_meta_box_nonce', 'meta_box_nonce' );?>

			<div class="clear"></div>

		</div> <!-- /#widget-meta-wrapper -->
	</div><!-- /.inside -->
</div><!-- /.postbox -->