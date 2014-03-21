<?php
/**
 * Displays meta box used in the shortcode template edit view
 */

$action = empty($action)?'':$action;
$title = empty($title)?'':$title; // template name
$shortcode = empty($shortcode)?'':$shortcode; // shortcode type we are making a template for
$values = empty($values)?array():$values; // current template values
$pl_shortcodes_attr = PL_Shortcode_CPT::get_shortcode_attrs();
$listing_attributes = PL_Shortcode_CPT::get_listing_attributes();

?>

<div class="postbox ">
	<h3>Create Shortcode Template</h3>

	<div class="inside">
		<!-- Template Type -->
		<section class="row-fluid">

			<div class="span2">
				<label for="pl_sc_tpl_shortcode" class="section-label">Template Type:</label>
			</div>

			<div class="span10">
				<select id="pl_sc_tpl_shortcode" name="shortcode">
						<?php
						$shortcode_refs = array();
						foreach( $pl_shortcodes_attr as $pl_shortcode => $sct_args ):
							$link_class = $selected = '';
							if (!$shortcode) {
								$shortcode = $pl_shortcode;
							}
							if ($shortcode == $pl_shortcode) {
								$link_class = 'selected_type';
								$selected = 'selected="selected"';
							}
							?>
							<option id="pl_sc_tpl_shortcode_<?php echo $pl_shortcode; ?>" class="<?php echo $link_class; ?>" value="<?php echo $pl_shortcode; ?>" <?php echo $selected; ?>>
								<?php echo $sct_args['title']; ?>
							</option>
							<?php
						endforeach;
						?>
				</select> (for use with shortcode <span id="pl_sc_tpl_shortcode_selected">[<?php echo $shortcode?>]</span>)
			</div>

		</section>
		<!-- /Template Type -->

		<!-- Template Contents -->
		<section class="row-fluid sc-meta-section">

			<!-- Template HTML/CSS -->
			<div class="span8">

				<?php
				foreach( $pl_shortcodes_attr as $pl_shortcode => $sc_attrs ) {?>
					<div class="pl_template_block <?php echo $pl_shortcode;?>" style="display:none;">
					<?php
					foreach($sc_attrs['template'] as $field => $f_args) {
						if ($action!='edit' || $values['shortcode']!=$pl_shortcode) {
							$_POST[$pl_shortcode][$field] = !empty($f_args['default']) ? $f_args['default'] : '';
						}
						else {
							$_POST[$pl_shortcode][$field] = !empty( $values[$field] ) ? htmlentities($values[$field]) : '';
						}
						$f_args['css'] = (!empty($f_args['css'])?$f_args['css'].' ':'').$field;
						PL_Form::item($field, $f_args, 'POST', $pl_shortcode, 'pl-sc-tpl-edit', true);
					}?>
					</div>
					<?php
				}
				?>

			</div>

			<!-- Search Sub-Shortcodes -->
			<div id="subshortcodes" class="span2">
				<?php foreach( $pl_shortcodes_attr as $pl_shortcode => $sct_args ) :?>
					<?php if(!empty($sct_args['subcodes'])):?>
						<div class="shortcode_block <?php echo $pl_shortcode?>" style="display: none;">
							<h3>Template Tags</h3>
							<?php $subcodes = '';?>
							<?php foreach($sct_args['subcodes'] as $subcode=>$atts): ?>
								<?php $subcodes .= '<h4 class="subcode"><a href="#">[' . $subcode . ']</a></h4>';?>
								<?php if (!empty($atts['help'])):?>
									<?php
									if ($subcode=='custom' || $subcode=='if') {
										switch($pl_shortcode) {
											case 'search_listings':
											case 'static_listings':
											case 'featured_listings':
											case 'favorite_listings':
												$atts['help'] = $atts['help'] . '<br />Click <a href="#" class="show_listing_attributes">here</a> to see a list of available listing attributes.';
												break;
											case 'search_form':
												$atts['help'] = $atts['help'] . '<br />Click <a href="#" class="show_search_form_attributes">here</a> to see a list of available listing attributes.';
												break;
										}
									}
									?>
									<?php $subcodes .= '<div class="description subcode-help">'. $atts['help'] .'</div>';?>
								<?php endif;?>
							<?php endforeach;?>
							<div>Use the following tags to customize your shortcode template:<br /><?php echo $subcodes?></div>
						</div>
					<?php endif;?>
				<?php endforeach;?>
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
			<div id="search_form_attributes" style="display:none;">
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
							<?php if ($attr['search_form_group']):?>
								<?php echo $attr['search_form_group']?>
							<?php endif;?>
							</td>
						</tr>
					<?php endforeach;?>
				</table>
			</div>

		</section><!-- /Template Contents -->

		<?php wp_nonce_field( 'pl_cpt_meta_box_nonce', 'meta_box_nonce' );?>

		<div class="clear"></div>

	</div><!-- /.inside -->

</div><!-- /.postbox -->
