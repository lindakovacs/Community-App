<?php
global $shortcode_subpages;

if(!class_exists('PL_Listing_Tpl_Table')){
	require_once( PL_SHORTCODES_DIR . 'lib/listing-tpl-table.php' );
}
$wp_list_table = new PL_Listing_Tpl_Table();
$wp_list_table->prepare_items();


$notice = $message = '';
$action = empty($_REQUEST['action']) ? '' : $_REQUEST['action'];
$search = empty($_REQUEST['s']) ? '' : esc_attr($_REQUEST['s']);

if ($action == 'select_active_template' && !empty($_REQUEST['save']) && isset($_REQUEST['template_id'])) {
	PL_Listing_Customizer::set_active_template_id($_REQUEST['template_id']);
	$message = 'Settings saved.';
}

$template_list_types = array('default' => 'Default', 'custom' => 'Custom');
$template_list = PL_Listing_Customizer::get_template_list();
$current_template_id = PL_Listing_Customizer::get_active_template_id();

?>
<div class="pl-sc-wrap">
	<div id="pl_template_all">
		<?php if ( $notice ) : ?>
		<div id="notice" class="error"><p><?php echo $notice ?></p></div>
		<?php endif; ?>
		<?php if ( $message ) : ?>
		<div id="message" class="updated"><p><?php echo $message; ?></p></div>
		<?php endif; ?>

		<form action="<?php echo admin_url("admin.php").'?page='.$_REQUEST['page']?>" method="post">
			<input type="hidden" name="action" value="select_active_template" />
			
			<label for="template_id">Select the template you would like to use to display your listings</label>
			<select name="template_id">
				<option value="" <?php echo $current_template_id == '' ? 'selected="selected"' : '' ?>>None</option>
				
				<?php $curr_type = ''; ?>
				<?php foreach ($template_list as $id => $template): ?>
					<?php if ($curr_type && $curr_type != $template['type']):?>
						</optgroup>
					<?php endif;?>

					<?php if($curr_type != $template['type']):?>
						<?php $curr_type = $template['type'] ?>
						<optgroup label="<?php echo $curr_type ?>">
					<?php endif;?>

					<option value="<?php echo $id ?>" class="<?php echo $curr_type ?>"
						<?php echo $current_template_id == $id ? 'selected="selected"' : '' ?>>
						<?php echo $template['title'] ?>
					</option>
				<?php endforeach;?>
				<?php if ($curr_type):?>
					</optgroup>
				<?php endif;?>
			</select>
			<input type="submit" name="save" value="Save" class="button-primary" />
		</form>

		<p>The following templates are available for formatting your property listing pages.</p>
		
		<?php if ($search):?>
		<h2>
			<?php printf( ' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', $search )?>
		</h2>
		<?php endif?>

		<?php $wp_list_table->views(); ?>

		<form id="posts-filter" action="<?php echo admin_url("admin.php")?>" method="get">

		<?php $wp_list_table->search_box( 'Search Listings Templates', 'pl_sc_tpl' ); ?>

		<input type="hidden" name="page" class="post_page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />

		<?php $wp_list_table->display(); ?>

		</form>
	</div>

	<br class="clear" />
</div>
<?php
