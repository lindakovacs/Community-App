<?php
/**
 * Generates a drop list of available shortcode templates grouped by default & custom
 */
$selected = !empty($selected) ? $select_name : ''; // name of the drop list box
$value = !empty($value) ? $value : ''; // current value
$class = !empty($class) ? $class : ''; // css class for box

$pl_snippet_list = PL_Shortcode_CPT::template_list($shortcode);
$pl_snippet_types = array('default' => 'Default', 'custom' => 'Custom'); // Order matters, here...

$curr_type = '';
?>

<section class="shortcode_ref">
	<select class="snippet_list <?php echo $class;?>"
	<?php if( ! empty( $select_name ) ) { echo 'name="'. $select_name . '"'; } ?>>
		<option value="" class=""
			<?php echo $value == '' ? 'selected="selected"' : '' ?>>
			none
		</option>
		<?php $count = count($pl_snippet_list);?>
		<?php for ($i=0; $i<=$count; ): ?>

			<?php list($id, $template) = each($pl_snippet_list);?>

			<?php if($i==$count || ($i && ($curr_type != $template['type']) && !empty($pl_snippet_types[$curr_type]))):?>
					</optgroup>
			<?php endif;?>

			<?php if ($i==$count) break;?>

			<?php if($curr_type != $template['type']):?>
				<?php $curr_type = $template['type'];?>
				<?php if(!empty($pl_snippet_types[$curr_type])):?>
					<optgroup label="<?php echo $pl_snippet_types[$curr_type];?>">
				<?php endif;?>
			<?php endif;?>

			<option value="<?php echo $id ?>" class="<?php echo $curr_type ?>"
				<?php echo $value == $id ? 'selected="selected"' : '' ?>>
				<?php echo $template['title']; ?>
			</option>
			<?php $i++;?>

		<?php endfor;?>
	</select>
</section>
