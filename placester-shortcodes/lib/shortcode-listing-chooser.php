<?php

PL_Shortcode_Listing_Chooser::register();
class PL_Shortcode_Listing_Chooser {
	public static function register () {
		add_action('wp_ajax_listing_chooser_options', array(__CLASS__, 'get_listings' ));
	}

	public static function init ( $params = array() ) {
		extract( $params );

		ob_start();
?>
<div class="featured-listings-wrapper">
	<div class="head">
		<button type="button" class="featured-listings button" id="<?php echo $params['value']['id'] ?>" <?php echo isset($params['iterator']) ? 'rel="' . $params["iterator"] . '"' : ''; ?>>Pick featured listings</button>
</div>

<div class="featured-listings" id="<?php echo $params['option_name'] ?>" ref="<?php echo $params['value']['id'] ?>" <?php echo isset($params['iterator']) ? 'rel="' . $params["iterator"] . '"' : ''; ?> <?php echo ($params['for_slideshow'] == 1) ? 'data-max="1"' : ''; ?> >
	<?php do_action( 'pl_featured_listings_inner_top' ); ?>
	<?php if ( is_array($params['val']) ): ?>
		<ol>
			<?php if ($for_slideshow == 1): ?>
				<?php unset( $params['val']['image'], $params['val']['link'], $params['val']['html'], $params['val']['type'] ) ?>

				<?php if (isset($params['val'][0])): ?>
					<?php unset($params['val'][0]) ?>
				<?php endif ?>

				<?php foreach ($params['val'] as $id => $address): ?>
					<li>
						<div id="pls-featured-text" ref="<?php echo $id ?>"><?php echo $address ?></div>
						<input type="hidden" name="<?php echo $params['option_name'] . '[' . $params['value']['id'] . ']['.$params['iterator'].'][' . $id . ']' ?>=" value="<?php echo $address ?>">
					</li>
				<?php endforeach ?>

			<?php else: ?>
				<?php foreach ($params['val'] as $id => $address): ?>
					<li>
						<div id="pls-featured-text" ref="<?php echo $id ?>"><?php echo $address ?></div>
						<input type="hidden" name="<?php echo $params['option_name'] . '[' . $params['value']['id'] . '][' . $id . ']' ?>=" value="<?php echo $address ?>">
					</li>
				<?php endforeach ?>
			<?php endif ?>
		</ol>
	<?php else: ?>
		<p>You haven't set any featured listings yet. Currently, a random selection of listings are being displayed until you pick some. If you previously picked listings, and now they are missing, it's because you (or your MLS), has marked them inactive, sold, rented, or they've been deleted.</p>
	<?php endif ?>
</div>
</div>
<?php
		return ob_get_clean();
	}


	public static function load ( $params = array() ) {
		extract( $params );

		ob_start();
?>
<div style="display:none">
	<div id="featured-listing-wrapper">
		<!-- filters wrapper -->
		<div class="filter-wrapper">
			<h3>Search Filters</h3>
			<p class="after-note">Use the filters below to find the listings you'd like to feature</p>
			<form></form>
			<?php PL_Shortcode_Listing_Chooser::get_filters(); ?>
		</div>

		<!-- datatable wrapper -->
		<div class="datatable-wrapper">
			<hr />
			<!-- Search Results -->
			<div id="search-results" class="results">
				<h3>Search Results</h3>
				<p class="after-note">Available listings. Use the "Make Featured" link to featured them.</p>
				<?php PL_Shortcode_Listing_Chooser::get_datatable( array('dom_id' => 'datatable_search_results', 'image_preview' => true, 'add_remove' => 'Add') ); ?>
			</div>

			<!-- Featured Listings -->
			<div id="featured-lisitngs" class="results">
				<h3>Featured Listings</h3>
				<p class="after-note">Featured listings. Use the "Remove" link to unfeature them.</p>
				<?php PL_Shortcode_Listing_Chooser::get_datatable( array( 'dom_id' => 'datatable_featured_listings', 'add_remove' => 'Remove') ); ?>
			</div>

		</div>
		<div id="featured-button-group-wrapper">
			<button id="save-featured-listings">Save</button>
			<button id="cancel-featured-listings">Cancel</button>
		</div>
	</div>
</div>
<?php
		echo ob_get_clean();
	}


	public static function get_filters ( $params = array() ) {
		extract( $params );

		ob_start();
?>
<form id="options-filters" method="POST" >
	<div class="featured_listings_options">
		<div class="address big-option">
			<label>Street Address</label>
			<input type="text" name="location[address]">
		</div>
		<div class="featured-listing-form-city option">
			<label for="featured-listing-city-filter">City</label>
			<select id="featured-listing-city-filter" name="location[locality]">
				<?php $cities = PL_Listing_Helper::locations_for_options('locality');
					if (!empty($cities)) {
						foreach ($cities as $key => $v) {
							echo '<option value="' . $key . '">' . $v . '</option>';
						}
					}
				?>
</select>
</div>

<div class="featured-listing-form-zip option">
	<label for="featured-listing-zip-filter">Zip Code</label>
	<select id="featured-listing-zip-filter" name="location[postal]">
		<?php $zip = PL_Listing_Helper::locations_for_options('postal');
		if (!empty($zip)) {
			foreach ($zip as $key => $v) {
				echo '<option value="' . $key . '">' . $v . '</option>';
			}
		}
		?>
	</select>
</div>

<div class="featured-listing-form-beds option">
	<label for="featured-listing-beds-filter">Beds</label>
	<input id="featured-listing-beds-filter" type="text" name="metadata[beds]">
</div>

<div class="featured-listing-form-beds option">
	<label for="featured-listing-rent-filter">Rent/Sale</label>
	<select id="featured-listing-rent-filter" name="purchase_types[]">
		<?php
		echo '<option value="false">Any</option>';
		echo '<option value="rental">Rent</option>';
		echo '<option value="sale">Buy</option>';
		?>
	</select>
</div>

<div class="featured-listing-form-min-price option">
	<label for="featured-listing-min-price-filter">Min Price</label>
	<input id="featured-listing-min-price-filter" type="text" name="metadata[min_price]">
</div>

<div class="featured-listing-form-max-price option">
	<label for="featured-listing-max-price-filter">Max Price</label>
	<input id="featured-listing-max-price-filter" type="text" name="metadata[max_price]">
</div>

<div class="featured-listing-form-max-price option checkboxes">
	<label for="featured-listing-non-mls-filter">Non-MLS Listings</label>
	<input id="featured-listing-non-mls-filter" type="checkbox" name="non_import">
</div>

<div class="featured-listing-form-max-price option checkboxes">
	<label for="featured-listing-my-offices-filter">My Offices's Listings</label>
	<input id="featured-listing-my-offices-filter" type="checkbox" name="agency_only">
</div>

</div>
<input class="button" type="submit" value="Search">
</form>
<?php
		echo ob_get_clean();	
	}

	public static function get_datatable ( $params = array() ) {
		extract( $params );

		ob_start();
?>
<table id="<?php echo $dom_id ?>" class="widefat post fixed placester_properties" cellspacing="0">
    <thead>
      <tr>
        <th><span>Address</span></th>
        <?php if ( isset($image_preview) ): ?>
	<th><span>Image</span></th>
<?php endif ?>
<th><span><?php echo $add_remove ?></span></th>
</tr>
</thead>
<tbody></tbody>
<tfoot>
<tr>
	<th></th>
	<?php if ( isset($image_preview) ): ?>
		<th></th>
	<?php endif ?>
	<th></th>
</tr>
</tfoot>
</table>
<?php
		echo ob_get_clean();
	}


	public static function get_listings () {
		$response = array();
		//exact addresses should be shown. 
		$_POST['address_mode'] = 'exact';

		// Sorting
		$columns = array('location.address');
		$_POST['sort_by'] = $columns[$_POST['iSortCol_0']];
		$_POST['sort_type'] = $_POST['sSortDir_0'];
		if ( isset( $_POST['agency_only'] ) && $_POST['agency_only'] == 'on' ) {
			$_POST['agency_only'] = 1;
		}
		if ( isset( $_POST['non_import'] ) && $_POST['non_import'] == 'on' ) {
			$_POST['non_import'] = 1;
		}
		
		// text searching on address
		$_POST['location']['address_match'] = 'like';

		// Pagination
		$_POST['limit'] = $_POST['iDisplayLength'];
		$_POST['offset'] = $_POST['iDisplayStart'];		
		
		// Get listings from model
		$api_response = PL_Listing_Helper::results($_POST, false);
		
		// build response for datatables.js
		$listings = array();
		if (!empty($api_response['listings'])) {
			foreach ($api_response['listings'] as $key => $listing) {
				$listings[$key][] = $listing['location']['address'] . ', ' . $listing['location']['locality'] . ' ' . $listing['location']['region']; 
				$listings[$key][] = !empty($listing['images']) ? '<a id="listing_image" href="' . $listing['images'][0]['url'] . '"  style="display: inline-block" onclick=\'return false;\'>Preview</a>' :  'No Image'; 
				$listings[$key][] = '<a id="pls_add_option_listing" href="#" ref="'.$listing['id'].'">Make Featured</a>';
			}
		}

		// Required for datatables.js to function properly.
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $listings;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		echo json_encode($response);

		//wordpress echos out a 0 randomly. die prevents it.
		die();
	}
}