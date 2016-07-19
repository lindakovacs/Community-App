<?php


require_once(PLACESTER_PLUGIN_DIR . 'libnew/forms.php');


PL_Admin_Listing::init();

class PL_Admin_Listing extends PL_Admin_Page {
	public function __construct($page_parent, $order, $page_name, $page_title) {
		parent::__construct($page_parent, $order, $page_name, $page_title, $page_title, null);
		$this->require_script('jquery-iframe-transport', PL_ADMIN_JS_URL . 'blueimp/js/jquery.iframe-transport.js', array('jquery'));
		$this->require_script('jquery-fileupload', PL_ADMIN_JS_URL . 'blueimp/js/jquery.fileupload.js', array('jquery-ui-widget', 'jquery-ui-sortable'));
		$this->require_script('placester-property', PL_ADMIN_JS_URL . 'listing.js', array('jquery-ui-datepicker', 'jquery-iframe-transport', 'jquery-fileupload'));
		$this->require_script('placester-location', PL_ADMIN_JS_URL . 'location.js', array('google-maps', 'text-overlay'));
		$this->require_style('placester-property', PL_ADMIN_CSS_URL . 'listing.css');
	}

	public function render_admin_content() {
		if(isset($_GET['id'])) {
			$overlay = 'Updating Listing...';

			// submit an update to an existing listing
			if($_POST['action'] == 'update_listing' && isset($_POST['id']) && $_GET['id'] == $_POST['id']) {
				$api_response = self::update_listing();

				if(is_array($api_response) && isset($api_response['id'])) {
					$view_url = site_url('/property/' . $api_response['id']);
					$edit_url = admin_url('admin.php?page=placester_property_add');
					$message = '<div id="message" class="updated below-h2"><p>
						Listing successfully updated. You may
							<a href="' . $view_url . '" class="button-secondary" style="vertical-align: middle;">View</a> it or
							<a href="' . $edit_url . '" class="button-secondary" style="vertical-align: middle;">Add</a> another.
						</p></div>';

					if($listing = PLX_Listings::read(array('id' => $_GET['id'])))
						$_POST = $listing;
				}

				else if(is_array($api_response) && isset($api_response['message']))
					$message = $api_response['message'];
				else
					$message = 'Update failed';
			}

			// request an existing listing for edit
			else {
				if(!($_POST = PLX_Listings::read(array('id' => $_GET['id'])))) {
					unset($_GET['id']);
					$message = 'Listing not found';
					$overlay = 'Creating Listing...';
				}
			}
		}

		else {
			$overlay = 'Creating Listing...';

			if(isset($_POST['action']) && $_POST['action'] == 'add_listing') {
				$api_response = self::add_listing();

				if(is_array($api_response) && isset($api_response['id'])) {
					$view_url = site_url('/property/' . $api_response['id']);
					$edit_url = admin_url('admin.php?page=placester_property_edit&id=' . $api_response['id']);
					$message = '<div id="message" class="created below-h2"><p>
						Listing successfully created. You may
							<a href="' . $view_url . '" class="button-secondary" style="vertical-align: middle;">View</a> or
							<a href="' . $edit_url . '" class="button-secondary" style="vertical-align: middle;">Edit</a> it.
						</p></div>';

					$_POST = array(); // add listing successful, add another
				}

				else if(is_array($api_response) && isset($api_response['message']))
					$message = $api_response['message'];
				else
					$message = 'Create failed';
			}

			else
				$_POST = array();
		}

		// fill in default country for new listing
		if(empty($_POST))
			$_POST = array('country' => PL_Option_Helper::get_default_country());
		?>

		<div id="loading_overlay" style="display:none"><?php echo $overlay; ?></div>
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
						<?php $this->render_publish_box(); ?>
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
								<?php $this->render_image_box($_POST['images']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br class="clear">
		</form>
		<?php
	}

	public function render_publish_box() {
		?>
		<div id="submitdiv" class="postbox ">
			<div class="handlediv" title="Click to toggle">
				<br></div>
			<h3 class="hndle">
				<span>Publish</span>
			</h3>
			<div class="inside">
				<div class="submitbox" id="submitpost">
					<div id="minor-publishing">
						<div style="display:none;">
							<p class="submit">
								<input type="submit" name="save" id="save" class="button" value="Save">
							</p>
						</div>
						<div id="misc-publishing-actions">
							<div class="misc-pub-section">
								<label for="post_status">Status:</label>
								<span id="post-status-display">Draft</span>
							</div>
							<div class="misc-pub-section " id="visibility">
								Visibility:
								<span id="post-visibility-display">Public</span>
							</div>
							<div class="misc-pub-section curtime misc-pub-section-last">
						<span id="timestamp">
							Publish <b>immediately</b>
						</span>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<div id="major-publishing-actions">
						<div id="delete-action">
							<a class="submitdelete deletion" href="admin.php?page=placester_properties">Cancel</a>
						</div>

						<div id="publishing-action">
							<input name="original_publish" type="hidden" id="original_publish" value="Publish">
							<input type="submit" name="publish" id="add_listing_publish" class="button-primary" value="Publish" tabindex="5" accesskey="p"></div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_image_box($images) {
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
								<img width="100px" height="100px" src="<?php echo self::ui_image_url($image); ?>"><a id="remove_image">Remove</a>
								<input id="hidden_images" type="hidden" name="images[]" value="<?php echo self::ui_image_value($image); ?>">
							</div>
						</li>
					<?php endforeach ?>
				<?php endif ?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	public function page_enqueue_scripts() {
		parent::page_enqueue_scripts();

		// special menu rendering for property edit page
		if($this->page_name == 'placester_property_edit') {
			$this->page_parent = 'placester_properties'; // make edit page visible in menu only when it is used
			if($_REQUEST['id'])
				$this->page_name .= '&id=' . $_REQUEST['id'];
		}
	}


	public static function init() {
		$pl_admin_page = new self('placester_properties', 320, 'placester_property_add', 'Add Listing');
		$pl_admin_page = new self('placester_no_display', 325, 'placester_property_edit', 'Edit Listing');
		add_action('wp_ajax_upload_image', array(__CLASS__, 'upload_image_ajax'));
	}

	protected static function prepare_post_array() {
		$_POST = stripslashes_deep($_POST);

		if(is_array($_POST['images'])) {
			$post_images = array();

			foreach($_POST['images'] as $image) {
				$image = explode('=', $image);

				switch($image[0]) {
					case 'image_id':
						array_push($post_images, array('order' => count($post_images), 'image_id' => $image[1]));
						break;
					case 'filename':
						array_push($post_images, array('order' => count($post_images), 'filename' => $image[1]));
						break;
				}
			}

			$_POST['images'] = $post_images;
		}
	}

	public static function add_listing() {
		self::prepare_post_array();
		$api_response = PLX_Listings::create($_POST);

		if(isset($api_response['id']))
			PL_Option_Helper::set_demo_data_flag(false);

		return $api_response;
	}

	public static function update_listing() {
		self::prepare_post_array();
		return PLX_Listings::update($_POST);
	}

	public static function ui_image_url($image) {
		if(isset($image['url'])) return $image['url'];
		return '';
	}

	public static function ui_image_value($image) {
		if(isset($image['id'])) return 'image_id=' . $image['id'];
		if(isset($image['image_id'])) return 'image_id=' . $image['image_id'];
		if(isset($image['filename'])) return 'filename=' . $image['filename'];
		return '';
	}

	public static function upload_image_ajax() {
		$response = $api_response = array();

		if (isset($_FILES['files'])) {
			foreach ($_FILES as $key => $image) {
				if (isset($image['name']) && is_array($image['name']) && (count($image['name']) == 1))
					$image['name'] = implode($image['name']);

				if (isset($image['type']) && is_array($image['type']) && (count($image['type']) == 1))
					$image['type'] = implode($image['type']);

				if (isset($image['tmp_name']) && is_array($image['tmp_name']) && (count($image['tmp_name']) == 1))
					$image['tmp_name'] = implode($image['tmp_name']);

				if (isset($image['size']) && is_array($image['size']) && (count($image['size']) == 1))
					$image['size'] = implode($image['size']);

				if (in_array($image['type'], array('image/jpeg','image/jpg','image/png','image/gif')))
					$api_response = PLX_Images::upload($_POST, $image['name'], $image['type'], $image['tmp_name']);
				else
					$api_response['message'] = "Unsupported file type - the image file must be a jpeg, jpg, png or gif file.".$image['type'];

				$api_response = wp_parse_args( $api_response, array('filename'=>'', 'url'=>'', 'message'=>''));
				if (isset($api_response['url']))
					$response[$key]['url'] = $api_response['url'];
				else
					$response[$key]['message'] = $api_response['message'];

				$response[$key]['name'] = $api_response['filename'];
				$response[$key]['orig_name'] = $image['name'];
			}
		}

		header('Vary: Accept');
		header('Content-type: text/html');
		echo json_encode($response);
		die();
	}
}