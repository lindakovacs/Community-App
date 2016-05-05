<?php
// fetch listing and store it in the $_POST global...
	if (isset($_GET['id'])) {
		$listings = PL_Listing::get(array('listing_ids' => array($_GET['id']), 'address_mode' => 'exact'));
		$_POST = empty($listings['listings']) ? null : $listings['listings'][0];

		// alter structure of $_POST to match listing edit/create structure
		$curated_data = is_array($_POST['cur_data']) ? $_POST['cur_data'] : array();
		$uncurated_data = is_array($_POST['uncur_data']) ? $_POST['uncur_data'] : array();
		$_POST['metadata'] = array_merge($curated_data, $uncurated_data);

		// no longer needed
		unset($_POST['cur_data'], $_POST['uncur_data']);
	}
?>

<?php if (isset($_GET['id'])): ?>
	<div id="loading_overlay" style="display:none">Updating Listing...</div>
<?php else: ?>
	<div id="loading_overlay" style="display:none">Creating Listing...</div>
<?php endif ?>

<div id="manage_listing_message"></div>
<form action="<?php echo admin_url('/admin-ajax.php')?>" method="<?php echo isset($_GET['id']) ? 'PUT' : 'POST' ?>" enctype="multipart/form-data" id="add_listing_form">
	<?php if (isset($_GET['id'])): ?>
		<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
	<?php endif ?>

	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar"> <!-- Right Sidebar -->
			<div id="side-sortables" class="meta-box-sortables ui-sortable">

				<?php include('partials/publish-box-sidebar.php'); ?>

			</div>
		</div>
		<div id="post-body">
			<div id="post-body-content">
				<div class="property-type-selects">

					<!-- Compound Type Select -->
					<?php echo PL_Form::item('compound_type',
						PL_Config::PL_API_LISTINGS('create', 'args', 'compound_type'),
						'POST'
					); ?>

					<!-- Property Type Input -->
					<?php echo PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array('property_type')),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false)
					); ?>

				</div>
				<div class="clear"></div>

				<?php echo new PL_Admin_Box(null, 'Location', null,
					'<div class="location-entry">' .
						PL_Form::generate_form(
							PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array('location')),
							array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false)
						) .
					'</div>' .
					'<div class="location-map">' .
						'<input id="location-coords-latitude" type="hidden" name="location[coords_latitude]" value="' . $_POST['location']['coords'][0] . '">' .
						'<input id="location-coords-longitude" type="hidden" name="location[coords_longitude]" value="' . $_POST['location']['coords'][1] . '">' .
						'<div id="location-map-canvas" style="height: 500px;"></div>' .
					'</div>'
				) ?>

				<!-- Residential Sales -->
				<?php echo new PL_Admin_Box('res_sale_details_admin_ui_basic', 'Basic Residential Sales Details', null,
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('beds', 'baths', 'half_baths', 'price', 'avail_on', 'sqft')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="res_sale" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('res_sale_details_admin_ui_advanced', 'Advanced Residential Sales Details', null,
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('lt_sz', 'lt_sz_unit', 'pk_spce', 'hoa_mand', 'hoa_fee', 'landr_own', 'style',
								'ngb_trans', 'ngb_shop', 'ngb_swim', 'ngb_court', 'ngb_park', 'ngb_trails', 'ngb_stbles', 'ngb_golf',
								'ngb_med', 'ngb_bike', 'ngb_cons', 'ngb_hgwy', 'ngb_mar', 'ngb_pvtsch', 'ngb_pubsch', 'ngb_uni',
								'grnt_tops', 'air_cond', 'cent_ac', 'frnshed', 'cent_ht', 'frplce', 'hv_ceil', 'wlk_clst', 'hdwdflr',
								'tle_flr', 'fm_lv_rm', 'lft_lyout', 'off_den', 'dng_rm', 'brkfst_nk', 'dshwsher', 'refrig',
								'stve_ovn', 'stnstl_app', 'attic', 'basemnt', 'washer', 'dryer', 'lndry_in', 'lndry_gar',
								'blc_deck_pt', 'yard', 'swm_pool', 'jacuzzi', 'sauna', 'cble_rdy', 'hghspd_net'
							)
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				<!-- Residential Rental -->
				<?php echo new PL_Admin_Box('res_rental_details_admin_ui_basic', 'Basic Residential Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('beds', 'baths', 'half_baths', 'price', 'avail_on', 'sqft')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="res_rental" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('res_rental_details_admin_ui_advanced', 'Advanced Residential Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('lt_sz', 'lt_sz_unit', 'pk_spce', 'lse_type', 'lse_trms', 'deposit', 'pk_lease',
								'ngb_trans', 'ngb_shop', 'ngb_swim', 'ngb_court', 'ngb_park', 'ngb_trails', 'ngb_stbles', 'ngb_golf',
								'ngb_med', 'ngb_bike', 'ngb_cons', 'ngb_hgwy', 'ngb_mar', 'ngb_pvtsch', 'ngb_pubsch', 'ngb_uni',
								'grnt_tops', 'air_cond', 'cent_ac', 'frnshed', 'cent_ht', 'frplce', 'hv_ceil', 'wlk_clst', 'hdwdflr',
								'tle_flr', 'fm_lv_rm', 'lft_lyout', 'off_den', 'dng_rm', 'brkfst_nk', 'dshwsher', 'refrig',
								'stve_ovn', 'stnstl_app', 'attic', 'basemnt', 'washer', 'dryer', 'lndry_in', 'lndry_gar',
								'blc_deck_pt', 'yard', 'swm_pool', 'jacuzzi', 'sauna', 'cble_rdy', 'hghspd_net'
							)
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				 <!-- Vacation Rentals -->
				<?php echo new PL_Admin_Box('vac_rental_details_admin_ui_basic', 'Basic Vacation Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('accoms', 'beds', 'baths', 'half_baths', 'price', 'avail_on', 'sqft', 'pk_spce')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="vac_rental" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('vac_rental_details_admin_ui_advanced', 'Advanced Vacation Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('avail_info', 'cats', 'dogs', 'cond', 'lse_type', 'lse_trms', 'deposit', 'pk_lease',
								'ngb_trans', 'ngb_shop', 'ngb_swim', 'ngb_court', 'ngb_park', 'ngb_trails', 'ngb_stbles', 'ngb_golf',
								'ngb_med', 'ngb_bike', 'ngb_cons', 'ngb_hgwy', 'ngb_mar', 'ngb_pvtsch', 'ngb_pubsch', 'ngb_uni',
								'grnt_tops', 'air_cond', 'cent_ac', 'frnshed', 'cent_ht', 'frplce', 'hv_ceil', 'wlk_clst', 'hdwdflr',
								'tle_flr', 'fm_lv_rm', 'lft_lyout', 'off_den', 'dng_rm', 'brkfst_nk', 'dshwsher', 'refrig',
								'stve_ovn', 'stnstl_app', 'attic', 'basemnt', 'washer', 'dryer', 'lndry_in', 'lndry_gar',
								'blc_deck_pt', 'yard', 'swm_pool', 'jacuzzi', 'sauna', 'cble_rdy', 'hghspd_net'
							)
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				<!-- Sublets -->
				<?php echo new PL_Admin_Box('sublet_details_admin_ui_basic', 'Basic Sublet Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('beds', 'baths', 'half_baths', 'price', 'avail_on', 'sqft', 'pk_spce')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="sublet" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('sublet_details_admin_ui_advanced', 'Advanced Sublet Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('cats', 'dogs', 'cond', 'lse_type', 'lse_trms', 'deposit', 'pk_lease',
								'ngb_trans', 'ngb_shop', 'ngb_swim', 'ngb_court', 'ngb_park', 'ngb_trails', 'ngb_stbles', 'ngb_golf',
								'ngb_med', 'ngb_bike', 'ngb_cons', 'ngb_hgwy', 'ngb_mar', 'ngb_pvtsch', 'ngb_pubsch', 'ngb_uni',
								'grnt_tops', 'air_cond', 'cent_ac', 'frnshed', 'cent_ht', 'frplce', 'hv_ceil', 'wlk_clst', 'hdwdflr',
								'tle_flr', 'fm_lv_rm', 'lft_lyout', 'off_den', 'dng_rm', 'brkfst_nk', 'dshwsher', 'refrig',
								'stve_ovn', 'stnstl_app', 'attic', 'basemnt', 'washer', 'dryer', 'lndry_in', 'lndry_gar',
								'blc_deck_pt', 'yard', 'swm_pool', 'jacuzzi', 'sauna', 'cble_rdy', 'hghspd_net'
							)
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				<!-- Commercial Rentals -->
				<?php echo new PL_Admin_Box('comm_rental_details_admin_ui_basic', 'Basic Commercial Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('prop_name', 'cons_stts', 'bld_suit', 'avail_on', 'sqft', 'min_div', 'max_cont', 'price')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="comm_rental" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('comm_rental_details_admin_ui_advanced', 'Advanced Commercial Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('lse_trms', 'lse_type', 'sublease', 'rate_unit', 'bld_sz', 'lt_sz', 'lt_sz_unit', 'year_blt')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				 <!-- Commercial Sales -->
				<?php echo new PL_Admin_Box('comm_sale_details_admin_ui_basic', 'Basic Commercial Sales Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('prop_name', 'cons_stts', 'sqft', 'min_div', 'max_cont', 'price', 'pk_spce')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="comm_sale" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('comm_sale_details_admin_ui_advanced', 'Advanced Commercial Sales Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('lt_sz', 'lt_sz_unit', 'year_blt')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				<!-- Parking -->
				<?php echo new PL_Admin_Box('park_rental_details_admin_ui_basic', 'Basic Parking Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('park_type', 'avail_on', 'price')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					) . '<a id="park_rental" class="advanced_toggle show_advanced" >Show Advanced</a>'
				) ?>

				<?php echo new PL_Admin_Box('park_rental_details_admin_ui_advanced', 'Advanced Parking Rental Details', 'display: none',
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array(
							'metadata' => array('lse_trms', 'lse_type', 'deposit', 'valet', 'guard', 'heat', 'carwsh')
						))),
						array('method' => 'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false, 'title' => true)
					)
				) ?>

				<?php
					$image_box = new PL_Admin_Box(null, 'Images');
					$image_box->open();

					$images = $_POST['images'];
					include 'partials/add-listing-image.php';

					$image_box->close();
				?>

				<?php echo new PL_Admin_Box(null, 'Description', null,
					PL_Form::generate_form(
						PL_Config::bundler('PL_API_LISTINGS', array('create', 'args'), array(array('metadata' => array('desc')))),
						array('method'=>'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => false)
					 )
				) ?>

			</div>
		</div>
	</div>
	<br class="clear">
</form>
