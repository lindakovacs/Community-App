<?php

require_once(PLACESTER_PLUGIN_DIR . 'libnew/forms.php');


PL_Admin_Listings::init();

class PL_Admin_Listings extends PL_Admin_Page {
	public function __construct() {
		parent::__construct('placester', 300, 'placester_properties', 'Listings', 'All Listings', null);
		$this->require_script('placester_properties', PL_ADMIN_JS_URL . 'listings.js', array('jquery-ui-dialog', 'jquery-ui-datepicker', 'jquery-datatables'));
		$this->require_style('placester_properties', PL_ADMIN_CSS_URL . 'listings.css', array('jquery-datatables'));
	}

	public function render_admin_content() {
		$search_form = new PL_Admin_Listings_Form();
		$parameters = $search_form->get_form_parameters();
		?>

		<form name="input" method="POST" class="plx-search-form" id="pl-admin-listings-form">
			<section class="form-group" id="quick-search">
				<?php echo $search_form->get_form_item('mls_id', 'Search by MLS ID'); ?>
				<?php echo $search_form->get_form_item('address', 'Search by Address'); ?>
				<?php echo $search_form->get_form_item('agency_only', 'Agency Listings'); ?>
				<?php echo $search_form->get_form_item('images', 'Images'); ?>
			</section>

			<section class="form-group" id="search-filters">
				<?php $search_form->render_filter_form($parameters); ?>
			</section>

			<section class="form-group" id="basic-search">
				<?php $search_form->render_basic_form($parameters['basic']); ?>
			</section>

			<section class="form-group" id="advanced-search" style="display: none;">
				<?php $search_form->render_advanced_form($parameters['extended']); ?>
			</section>
		</form>

		<div id="container">
			<table id="placester_listings_list" class="widefat post fixed placester_properties" cellspacing="0">
				<thead>
				<tr>
					<th><span></span></th>
					<th><span>Address</span></th>
					<th><span>Zip</span></th>
					<th><span>Listing Type</span></th>
					<th><span>Property Type</span></th>
					<th><span>Status</span></th>
					<th><span>Beds</span></th>
					<th><span>Baths</span></th>
					<th><span>Price</span></th>
					<th><span>Sqft</span></th>
				</tr>
				</thead>
				<tbody></tbody>
				<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
				</tfoot>
			</table>
		</div>

		<div style="display:none" id="delete_listing_confirm">
			<div id="delete_response_message"></div>
			<div>Are you sure you want to permanently delete <span id="delete_listing_address"></span>?</div>
		</div>
		<?php
	}


	public static function init() {
		$pl_admin = new self();
		add_action('wp_ajax_listings_table', array(__CLASS__, 'listings_table_ajax'));
		add_action('wp_ajax_delete_listing', array(__CLASS__, 'delete_listing_ajax'));
	}

	public static function listings_table_ajax() {
		$columns = array('images', 'address', 'postal', 'listing_type', 'property_type', 'status', 'beds', 'baths', 'price', 'sqft');

		// pagination
		$args['limit'] = $_POST['iDisplayLength'];
		$args['offset'] = $_POST['iDisplayStart'];
		$args['sort_by'] = $columns[$_POST['iSortCol_0']];
		$args['sort_type'] = $_POST['sSortDir_0'];

		// listing filters
		foreach($_POST as $key => $value)
			if(PLX_Parameters::get($key))
				$args[$key] = $value;

		// text searching on address
		if(isset($args['address']))
			$args['address_match'] = 'like';

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
			$listings[$key][] = $listing["status"] ? $listing["status"] : '';
			$listings[$key][] = $listing["beds"] === false ? '' : $listing["beds"];
			$listings[$key][] = $listing["baths"] === false ? '' : $listing["baths"];
			$listings[$key][] = is_null($listing["price"]) ? '' : $listing["price"];
			$listings[$key][] = is_null($listing["sqft"]) ? '' : $listing["sqft"];
		}

		// Required for datatables.js to function properly.
		$response = array();
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $listings;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		echo json_encode($response);

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
}


class PL_Admin_Listings_Form extends PLX_Parameter_Form {
	public function get_form_parameters() {
		$form_parameters = array('basic' => array(), 'extended' => array());

		$form_parameters['basic']['Listing'] = array(
			'listing_type' =>     PLX_Parameters::get('listing_type'),
			'property_type' =>    PLX_Parameters::get('property_type'),
			'zoning_type' =>      PLX_Parameters::get('zoning_type'),
			'purchase_type' =>    PLX_Parameters::get('purchase_type'),
			'status' =>           PLX_Parameters::get('status'));

		$form_parameters['basic']['Location'] = array(
			'locality' =>         PLX_Parameters::get('locality'),
			'region' =>           PLX_Parameters::get('region'),
			'postal' =>           PLX_Parameters::get('postal'),
			'neighborhood' =>     PLX_Parameters::get('neighborhood'));

		$form_parameters['basic']['Basic'] = array(
			'min_price' =>        PLX_Parameters::get('min_price'),
			'max_price' =>        PLX_Parameters::get('max_price'),
			'min_sqft' =>         PLX_Parameters::get('min_sqft'),
			'min_beds' =>         PLX_Parameters::get('min_beds'),
			'min_baths' =>        PLX_Parameters::get('min_baths'),
			'min_half_baths' =>   PLX_Parameters::get('min_half_baths'));

		$form_parameters['extended'] = PLX_Parameters::get_extended_parameters();

		return $form_parameters;
	}

	public function render_filter_form($search_parameters) {
		$filter_form = new PLX_Data_Form(array());
		?>

		<fieldset>
			<legend>Show Additional Filters</legend>
			<div id="basic-filters"><?php
				echo $filter_form->get_form_item('filters[]', 'Listing Types', PLX_Form::CHECKBOX, 'listing');
				echo $filter_form->get_form_item('filters[]', 'Location', PLX_Form::CHECKBOX, 'location');
				echo $filter_form->get_form_item('filters[]', 'Basic Details', PLX_Form::CHECKBOX, 'basic');
				echo $filter_form->get_form_item('filters[]', 'More...', PLX_Form::CHECKBOX, 'advanced'); ?>
			</div>
			<div id="advanced-filters" style="display: none;"><?php
				foreach(array_keys($search_parameters['extended']) as $option)
					echo $filter_form->get_form_item('filters[]', $option, PLX_Form::CHECKBOX, $option); ?>
			</div>
		</fieldset>

		<?php
	}

	public function render_basic_form($basic_parameters) {
		?>

		<div id="listing-parameters" class="postbox" style="display: none;">
			<h3 class="hndle"><span><?php echo PLX_Parameters::get_group_title('Listing'); ?></span></h3>
			<div class="inside">
				<?php foreach($basic_parameters['Listing'] as $parameter)
					echo $this->get_form_item($parameter['name']); ?>
			</div>
		</div>
		<?php unset($basic_parameters['Listing']); ?>

		<div id="location-parameters" class="postbox" style="display: none;">
			<h3 class="hndle"><span><?php echo PLX_Parameters::get_group_title('Location'); ?></span></h3>
			<div class="inside">
				<?php foreach($basic_parameters['Location'] as $parameter)
					echo $this->get_form_item($parameter['name']); ?>
			</div>
		</div>
		<?php unset($basic_parameters['Location']); ?>

		<div id="basic-parameters" style="display: none;">
			<?php foreach($basic_parameters as $group => $parameters) { ?>
				<div id="<?php echo "basic-$group-parameters"; ?>" class="postbox">
					<h3 class="hndle"><span><?php echo PLX_Parameters::get_group_title($group); ?></span></h3>
					<div class="inside">
						<?php foreach($parameters as $parameter)
							echo $this->get_form_item($parameter['name']); ?>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php
	}

	public function render_advanced_form($extended_parameters) {
		?>

		<div id="extended-parameters">
			<?php foreach($extended_parameters as $group => $parameters) { ?>
				<div id="<?php echo "extended-$group-parameters"; ?>" class="postbox" style="display: none;">
					<h3 class="hndle"><span><?php echo PLX_Parameters::get_group_title($group); ?></span></h3>
					<div class="inside">
						<?php foreach($parameters as $parameter)
							echo $this->get_form_item($parameter['name']); ?>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php
	}
}
