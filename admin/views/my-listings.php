<?php
?>

<form name="input" method="POST" class="complex-search" id="pls_admin_my_listings_filters">
	<section class="form_group form_group_show_filters" id="show_filters">
		<section id="listing_types" class="pls_search_form listing_types">
			<input id="listing_types" type="checkbox" name="listing_types" value="true"  />
			<label for="listing_types" class="checkbox">Type filters</label>
		</section><section id="location" class="pls_search_form location">
			<input id="location" type="checkbox" name="location" value="true"  />
			<label for="location" class="checkbox">Location filters</label>
		</section><section id="basic" class="pls_search_form basic">
			<input id="basic" type="checkbox" name="basic" value="true"  />
			<label for="basic" class="checkbox">Basic filters</label>
		</section><section id="advanced" class="pls_search_form advanced">
			<input id="advanced" type="checkbox" name="advanced" value="true"  />
			<label for="advanced" class="checkbox">Advanced filters</label>
		</section><section id="custom" class="pls_search_form custom">
			<input id="custom" type="checkbox" name="custom" value="true"  />
			<label for="custom" class="checkbox">Custom filters</label>
		</section><section id="address_search" class="pls_search_form address_search">
			<label for="address_search" class="text">Address Search</label>
			<input id="address_search" class="form_item_text" type="text" name="address_search" value="" data-attr_type="text" />
		</section>
	</section>
</form>

<?php
// to set filtering values on any of the available listing attributes
// PL_Form::generate_form(PL_Config::PL_API_LISTINGS('get', 'args'), array('method' => "POST", 'title' => true, 'include_submit' => false, 'id' => 'pls_admin_my_listings', 'textarea_as_text' => true));
//
//

$attributes = PLX_Attributes::get_attributes();
$parameters = PLX_Parameters::get_parameters();
$temp = false;

?>
<div id="container">
  <table id="placester_listings_list" class="widefat post fixed placester_properties" cellspacing="0">
    <thead>
      <tr>
        <th><span></span></th>
        <th><span>Address</span></th>
        <th><span>Zip</span></th>
        <th><span>Listing Type</span></th>
        <th><span>Property Type</span></th>
        <th><span>Beds</span></th>
        <th><span>Baths</span></th>
        <th><span>Price</span></th>
        <th><span>Sqft</span></th>
        <th><span>Status</span></th>
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
