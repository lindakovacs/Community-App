<?php
/**
 * Edit taxonomies for displaying groups of properties
 */
global $pagenow;

$page = $_REQUEST['page'];

$taxlist = array('neighborhood'=>'neighborhood', 'zip'=>'postal', 'city'=>'locality', 'state'=>'region');
$taxnow = empty($_REQUEST['taxonomy']) ? '' : $_REQUEST['taxonomy'];
if (!array_key_exists($taxnow, $taxlist)) {
	$taxnow = current(array_keys($taxlist));
	wp_redirect("$pagenow?page=$page&taxonomy=$taxnow");
	exit;
}
$taxonomy = $taxnow;
// table needs tax in the url to construct nav links
$baseurl = "$pagenow?page=$page&taxonomy=$taxonomy";

$tax = get_taxonomy($taxonomy);
if (!$tax) {
	wp_die(__('Invalid taxonomy'));
}
if (!current_user_can($tax->cap->manage_terms)) {
	wp_die(__('Cheatin&#8217; uh?'));
}

if(!class_exists('PL_Property_Terms_Table')) {
	require_once(PL_LIB_DIR . 'property-terms-table.php');
}
// Include Yoast SEO for taxonomy if available
if (!class_exists('WPSEO_Taxonomy') && defined('WPSEO_PATH') && file_exists(WPSEO_PATH.'admin/class-taxonomy.php')) {
	require WPSEO_PATH.'admin/class-taxonomy.php';
}

$wp_list_table = new PL_Property_Terms_Table(array('singular'=>strtolower($tax->labels->singular_name), 'plural'=>strtolower($tax->labels->name), 'taxonomy'=>$taxonomy));
$pagenum = $wp_list_table->get_pagenum();
$current_screen = get_current_screen();
$title = $tax->labels->name;
$post_type = 'property';

add_screen_option('per_page', array('label' => 'Per page', 'default' => 20, 'option' => 'edit_' . $tax->name . '_per_page'));
$action = $wp_list_table->current_action();
$message = isset($_REQUEST['message']) ? (int)$_REQUEST['message'] : 0;
$error = false;
$message_str = '';

switch ($action) {

case 'add-tag':
	check_admin_referer('add-tag', '_wpnonce_add-tag');
	if (!current_user_can($tax->cap->edit_terms)) {
		wp_die(__('Cheatin&#8217; uh?'));
	}
	if (empty($_POST['tag-name'])) {
		$message = 7;
	}
	else {
		$_POST['slug'] = sanitize_title($_POST['tag-name']);
		$ret = wp_insert_term($_POST['tag-name'], $taxonomy, $_POST);
		$location = "$pagenow?page=$page&taxonomy=$taxonomy";
		if ($ret) {
			if (is_wp_error($ret)) {
				$message_str = $ret->get_error_message();
			}
			else {
				$term = get_term($ret['term_id'], $taxonomy);
				if ($term->slug == $_POST['slug']) {
					$location = add_query_arg('message', 1, $location);
					wp_redirect( $location );
					exit;
				}
				// WP has a bug where there can be only one term name for a term slug, so if the slug is already used with a different name
				// we cannot create our term.
				wp_delete_term($ret['term_id'], $taxonomy);
				$error = true;
				$taxonomies = get_taxonomies(array('show_ui' => true), 'objects');
				$message_str = "Unable to create the location page because the slug \"{$_POST['slug']}\" is in use with a name other than \"{$_POST['tag-name']}\"!<br>";
				foreach ($taxonomies as $tax_type => $tax) {
					// try to find where the slug has been used so we can help the user fix it
					$term = get_term_by('slug', $_POST['slug'], $tax_type);
					if ($term && !is_wp_error($term)) {
						$tax_name = ($tax_type == 'post_tag' || $tax_type == 'category' ? 'Post ' : '').$tax->labels->singular_name;
						$message_str .= "The {$tax_name} item \"$term->name\" is using the slug \"{$_POST['slug']}\". Please rename it to \"{$_POST['tag-name']}\" or change its slug. ";
						$message_str .= "Click <a href=\"".admin_url('edit-tags.php?action=edit&tag_ID='.$term->term_id.'&taxonomy='.$tax_type)."\" target=\"_blank\">here</a> to edit it.<br/>";
					}
				}
			}
		} else {
			$message = 4;
		}
	}
	break;

case 'delete':
	if (!empty($_REQUEST['tag_ID'])) {
		$tag_ID = (int) $_REQUEST['tag_ID'];
		check_admin_referer('delete-tag_' . $tag_ID);
		if (!current_user_can($tax->cap->delete_terms)) {
			wp_die(__('Cheatin&#8217; uh?'));
		}
		wp_delete_term($tag_ID, $taxonomy);
		wp_redirect("$pagenow?page=$page&taxonomy=$taxnow&message=2");
		exit;
	}
	break;

case 'bulk-delete':
	// TODO: check_admin_referer('bulk-'.strtolower($tax->labels->name));
	if (!current_user_can($tax->cap->delete_terms)) {
		wp_die(__('Cheatin&#8217; uh?'));
	}
	$tags = (array) $_REQUEST['delete_tags'];
	foreach ($tags as $tag_ID) {
		wp_delete_term($tag_ID, $taxonomy);
	}
	wp_redirect("$pagenow?page=$page&taxonomy=$taxnow&message=6");
	exit;
	break;

case 'edit':
	$title = $tax->labels->edit_item;
	$tag_ID = (int) $_REQUEST['tag_ID'];
	$tag = get_term($tag_ID, $taxonomy, OBJECT, 'edit');
	if (! $tag) {
		wp_die(__('You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?'));
	}
	PL_Router::load_builder_partial('property-terms-form.php', array('taxonomy'=>$taxonomy, 'tax'=>$tax, 'tag_ID'=>$tag_ID, 'tag'=>$tag));
	return;

case 'editedtag':
	$tag_ID = (int) $_POST['tag_ID'];
	check_admin_referer('update-tag_' . $tag_ID);
	if (!current_user_can($tax->cap->edit_terms)) {
		wp_die(__('Cheatin&#8217; uh?'));
	}
	$tag = get_term($tag_ID, $taxonomy);
	if (! $tag) {
		wp_die(__('You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?'));
	}
	// allow tags in description field
	remove_all_filters('pre_term_description');
	$ret = wp_update_term($tag_ID, $taxonomy, $_POST);
	$location = "$pagenow?page=$page&taxonomy=$taxonomy";
	if ($ret && !is_wp_error($ret))
		$location = add_query_arg('message', 3, $location);
	else
		$location = add_query_arg('message', 5, $location);
	wp_redirect($location);
	exit;
	break;
}

$wp_list_table->prepare_items();

if (!current_user_can($tax->cap->edit_terms)) {
	wp_die(__('You are not allowed to edit this item.'));
}

$locations = (array)PL_Listing_Helper::locations_for_options();
$curr_locations = array();
if (empty($locations[$taxlist[$taxonomy]])) {
	$locations = array();
}
else {
	$locations = $locations[$taxlist[$taxonomy]];
	sort($locations);
	$terms = get_terms($taxonomy, array('offset'=>0, 'hide_empty'=>0));
	foreach($terms as $term) {
		$curr_locations[] = $term->name;
	}
}
$_POST += array('tag-name'=>'', 'description'=>'');

$messages = array();
$messages[1] = __('Item added.');
$messages[2] = __('Item deleted.');
$messages[3] = __('Item updated.');
$messages[4] = __('Item not added.');
$messages[5] = __('Item not updated.');
$messages[6] = __('Items deleted.');
$messages[7] = __('No item selected.');
$error = $error || in_array($message, array(4, 5, 7)); 
$message_str = $message_str ? $message_str : ($message ? $messages[$message] : '');

?>
<div class="wrap nosubsub">
	<?php echo PL_Helper_Header::pl_settings_subpages(); ?>

	<h2>Display Properties Grouped By Location</h2>
	<p>If your theme supports custom pages for displaying properties by location, you can use this screen to customize pages displayed for different location types.</p>
	<form id="taxonomy-select" action="<?php echo $baseurl; ?>" method="get">
		<input type="hidden" name="page" class="post_page" value="<?php echo $page ?>" />
		<label for="page-type">Edit Pages For:</label>
		<select name="taxonomy" id="page-type">
		<?php
		foreach($taxlist as $tlslug=>$tlloc) {
			$tltax = get_taxonomy($tlslug);
			if ($tltax) {
			?>
				<option value="<?php echo $tlslug ?>" <?php echo ($tlslug==$taxonomy ? 'selected="selected"' : '') ?>><?php echo $tltax->labels->name ?></option>
			<?php
			}
		}
		?>
		</select>
		<input type="submit" name="submit" class="button" value="Select" />
	</form>

	<h3>
	<?php if (!empty($_REQUEST['s'])): ?>
		<?php printf('<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html(stripslashes($_REQUEST['s']))); ?>
	<?php endif ?>
	</h3>
	<?php if ($message_str) : ?>
	<div id="message" class="<?php echo ($error ? 'error' : 'updated') ?>"><p><?php echo $message_str; ?></p></div>
	<?php $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
	endif; ?>
	<div id="ajax-response"></div>
	<form class="search-form" action="<?php echo $pagenow?>" method="get">
		<input type="hidden" name="page" class="post_page" value="<?php echo $page ?>" />
		<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />
		<?php $wp_list_table->search_box('Search Current '.$tax->labels->singular_name.' Pages', 'tag'); ?>
	</form>
	<br class="clear" />
	<div id="col-container">

		<div id="col-right">
			<div class="col-wrap">
				<form id="posts-filter" action="<?php echo $baseurl; ?>" method="post">
					<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />
					<?php $wp_list_table->display(); ?>
					<br class="clear" />
				</form>
			</div>
		</div><!-- /col-right -->

		<div id="col-left">
			<div class="col-wrap">
			<?php if (current_user_can($tax->cap->edit_terms)): ?>
				<div class="form-wrap">
					<h3>Add A <?php echo $tax->labels->singular_name; ?></h3>
					<?php if (empty($locations)):?>
						<p>Your MLS does not have any <?php echo $tax->labels->name; ?>. 
						<?php if (current_theme_supports('pls-custom-polygons')): ?>
							You can still create custom <?php echo $tax->labels->singular_name; ?>
							pages by using <a href="admin.php?page=placester_settings_polygons">Custom Drawn Areas</a> for <?php echo $tax->labels->name; ?>.
						<?php endif; ?>
						</p>
					<?php else:?>
						<p>Select from the list of <?php echo $tax->labels->name; ?> provided by your MLS below. 
						<?php if (current_theme_supports('pls-custom-polygons')): ?>
							If you want to create your own custom <?php echo $tax->labels->singular_name; ?>
							use the <a href="admin.php?page=placester_settings_polygons">Custom Drawn Areas</a> tool.
						<?php endif; ?>
						</p>
						<form id="addtag" method="post" action="<?php echo $baseurl; ?>">
							<input type="hidden" name="action" value="add-tag" />
							<input type="hidden" name="screen" value="<?php echo esc_attr($current_screen->id); ?>" />
							<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />
							<?php wp_nonce_field('add-tag', '_wpnonce_add-tag'); ?>
		
							<div class="form-field form-required">
								<label for="tag-name"><?php _ex('Name', 'Taxonomy Name'); ?></label>
								<select name="tag-name" id="tag-name">
									<option value="">Select</option>
									<?php foreach($locations as $location):?>
										<?php if (trim($location)!=''):?>
											<option <?php echo ($location == $_POST['tag-name'] ? 'selected="selected"' : '') ?> <?php echo (in_array($location, $curr_locations) ? 'disabled="disabled"' : '') ?>><?php echo $location ?></option>
										<?php endif ?>
									<?php endforeach;?>
								</select>
							</div>
							<div class="form-field">
								<label for="tag-description"><?php _ex('Description', 'Taxonomy Description'); ?></label>
								<textarea name="description" id="tag-description" rows="5" cols="40"><?php echo $_POST['description'] ?></textarea>
								<p><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></p>
							</div>
		
							<?php
							do_action($taxonomy . '_add_form_fields', $taxonomy);
	
							submit_button('Add '.$tax->labels->singular_name, 'submit');
	
							do_action($taxonomy . '_add_form', $taxonomy);
							?>
						</form>
					<?php endif; ?>
				</div>
			<?php endif ?>
			</div>
		</div><!-- /col-left -->

	</div><!-- /col-container -->

</div><!-- /wrap -->
<?php
