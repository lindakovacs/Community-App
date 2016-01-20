<?php 

$filter_filters = array(
	'listing_types' => array(
		'label' => 'Type filters',
		'group' => 'Show Filters',
		'type' => 'checkbox'
	),
	'location' => array(
		'label' => 'Location filters',
		'group' => 'Show Filters',
		'type' => 'checkbox'
	),
	'basic' => array(
		'label' => 'Basic filters',
		'group' => 'Show Filters',
		'type' => 'checkbox'
	),
	'advanced' => array(
		'label' => 'Advanced filters',
		'group' => 'Show Filters',
		'type' => 'checkbox'
	),
	'custom' => array(
		'label' => 'Custom filters',
		'group' => 'Show Filters',
		'type' => 'checkbox'
	),
	'address_search' => array(
		'label' => 'Address Search',
		'group' => 'Show Filters',
		'type' => 'text'
	)
);

// to make various groups of listing fields visible for filter selection
PL_Form::generate_form($filter_filters, array('method' => "POST", 'title' => false, 'include_submit' => false, 'id' => 'pls_admin_my_listings_filters') );

// to set filtering values on any of the available listing attributes
PL_Form::generate_form(PL_Config::PL_API_LISTINGS('get', 'args'), array('method' => "POST", 'title' => true, 'include_submit' => false, 'id' => 'pls_admin_my_listings', 'textarea_as_text' => true));

?>
<div id="container">
  <table id="placester_listings_list" class="widefat post fixed placester_properties" cellspacing="0">
    <thead>
      <tr>
        <th><span></span></th>
        <th><span>Street</span></th>
        <th><span>Zip</span></th>
        <th><span>Listing Type</span></th>
        <th><span>Property Type</span></th>
        <th><span>Beds</span></th>
        <th><span>Baths</span></th>
        <th><span>Price</span></th>
        <th><span>Sqft</span></th>
        <th><span>Available</span></th>
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
