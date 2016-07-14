<?php 

PL_Listing_Admin_Helper::init();
class PL_Listing_Admin_Helper {

	public static function init() {
		add_action('wp_ajax_datatable_ajax', array(__CLASS__, 'datatable_ajax'));
		add_action('wp_ajax_add_listing', array(__CLASS__, 'add_listing_ajax'));
		add_action('wp_ajax_update_listing', array(__CLASS__, 'update_listing_ajax'));
		add_action('wp_ajax_add_temp_image', array(__CLASS__, 'add_temp_image'));
		add_action('wp_ajax_delete_listing', array(__CLASS__, 'delete_listing_ajax'));
	}

	public static function datatable_ajax() {
		$response = array();

		// Start the args array -- exact addresses should always be shown in this view...
		$args = array('address_mode' => 'exact');

		// Sorting
		// Controls the order of columns returned to the datatable
		$columns = array(
			'images',
			'address',
			'postal',
			'listing_type', // the data API doesn't actually support this
			'property_type',
			'beds',
			'baths',
			'price',
			'sqft',
			'status'
		);

		$args['sort_by'] = $columns[$_POST['iSortCol_0']];
		$args['sort_type'] = $_POST['sSortDir_0'];
		
		// text searching on address
		$args['address'] = @$_POST['sSearch'];
		$args['address_match'] = 'like';

		// Pagination
		$args['limit'] = $_POST['iDisplayLength'];
		$args['offset'] = $_POST['iDisplayStart'];		

		$api_response = PLX_Search::listings($args);

		$listings = array();
		foreach ($api_response['listings'] as $key => $listing) {
			$images = $listing['images'];
			$listings[$key][] = ((is_array($images) && isset($images[0])) ? '<img width=50 height=50 src="' . $images[0]['url'] . '" />' : '');

			$address = $listing["address"] . ' ' . $listing["locality"] . ' ' . $listing["region"];

			if((isset($listing['import_id']) && $listing['import_id']) || (isset($listing['provider_id']) && $listing['provider_id'])) { // imported MLS listing, cannot be edited
				$view_url = PL_Pages::get_url($listing['id'], $listing);
				$address_link = '<a class="address" href="' . $view_url . '">' . $address . '</a>';
				$action_links = '<span class="grey">Edit</span>';
				$action_links .= '<span>|</span><a href=' . $view_url . '>View</a>';
				$action_links .= '<span>|</span><span class="grey">Delete</span>';
			}
			else {
				$edit_url = admin_url('admin.php?page=placester_property_edit&id=' . $listing['id']);
				$address_link = '<a class="address" href="' . $edit_url . '">' . $address . '</a>';
				$action_links = '<a href="' . $edit_url . '">Edit</a>';
				$action_links .= '<span>|</span><a href=' . PL_Pages::get_url($listing['id'], $listing) . '>View</a>';
				$action_links .= '<span>|</span><a class="red" id="pls_delete_listing" href="#" data-ref="' . $listing['id'] . '">Delete</a>';
			}

			$listings[$key][] = $address_link . '<div class="row_actions">' . $action_links . '</div>';
			$listings[$key][] = $listing["postal"];

			$listings[$key][] = $listing["listing_type"];

			$listings[$key][] = $listing["property_type"];
			$listings[$key][] = $listing["beds"] === false ? '' : $listing["beds"];
			$listings[$key][] = $listing["baths"] === false ? '' : $listing["baths"];
			$listings[$key][] = is_null($listing["price"]) ? '' : $listing["price"];
			$listings[$key][] = is_null($listing["sqft"]) ? '' : $listing["sqft"];
			$listings[$key][] = $listing["status"] ? $listing["status"] : '';
		}

		// Required for datatables.js to function properly.
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $listings;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		echo json_encode($response);

		// WordPress echos out a 0 randomly, 'die' prevents it...
		die();
	}

	private static function prepare_post_array() {
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

	public static function add_listing_ajax() {
		$api_response = self::add_listing();
		echo json_encode($api_response);
		die();
	}

	public static function update_listing() {
		self::prepare_post_array();
		return PLX_Listings::update($_POST);
	}

	public static function update_listing_ajax() {
		$api_response = self::update_listing();
		echo json_encode($api_response);
		die();
	}

	public static function delete_listing_ajax() {
		$api_response = PLX_Listings::delete($_POST);

		if(empty($api_response))
			echo json_encode(array('response' => true, 'message' => 'Listing successfully deleted. This page will reload momentarily.'));

		else if(isset($api_response['code']) && $api_response['code'] == 1800)
			echo json_encode(array('response' => false, 'message' => 'Cannot find listing. Try <a href="'.admin_url().'?page=placester_settings">emptying your cache</a>.'));

		else if($api_response['message'])
			echo json_encode(array('response' => false, 'message' => 'Unable to delete listing. ' . $api_response['message']));

		die();
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

	public static function add_temp_image() {
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
