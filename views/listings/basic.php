<?php

$template = array(

'title' => 'Basic',

'css' => '
.basic-property-listing {
}

.basic-property-listing .slideshow-wrapper ul {
	list-style: none !important;
}

.basic-property-listing .slideshow-wrapper li {
	display: inline-block;
	margin: 5px 5px 0 0;
}
',

'snippet_body' => '
<h2 itemprop="name" itemscope itemtype="http://schema.org/PostalAddress">
	<span itemprop="streetAdress">[address]</span>
	<span itemprop="addressLocality">[locality]</span>,
	<span itemprop="addressRegion">[region]</span>
</h2>


<div class="price-wrapper">
	[if group=\'\' attribute=\'compound_type\' value=\'res_sale\']<span>Residential Sale</span>[/if]
	[if group=\'\' attribute=\'compound_type\' value=\'res_rental\']<span>Residential Rental</span>[/if]
	[if group=\'\' attribute=\'compound_type\' value=\'comm_sale\']<span>Commercial Sale</span>[/if]
	[if group=\'\' attribute=\'compound_type\' value=\'comm_rental\']<span>Commercial Rental</span>[/if]
	[if group=\'\' attribute=\'compound_type\' value=\'vac_rental\']<span>Vacation Rental</span>[/if]
	<span itemprop="streetAdress">[price]</span>
</div>

<div class="favorite-wrapper">
	[favorite_link_toggle]
</div>

<div class="slideshow-wrapper">
	<h3></h3>
	[image width=750 height=500]
	[gallery]
</div>

<div class="details-wrapper">
	<h3>Basic Details</h3>
	<ul>
    	<li><span>Beds: </span>[beds]</li>
		<li><span>Baths: </span>[baths]</li>
		<li><span>Half Baths: </span>[half_baths]</li>
		<li><span>Square Feet: </span>[sqft]</li>
		<li><span>MLS #: </span>[mls_id]</li>
	</ul>
</div>

<div class="description-wrapper">
	<h3>Property Description</h3>
	<p itemprop="description">[desc]</p>
</div>

<div class="amenities-wrapper">
	<h3>Listing Amenities</h3>
	[amenities]
</div>

<div class="map-wrapper">
	<h3>Property Map</h3>
	[map width=750 height=375]
</div>
',

'before_widget' => '
<!-- Place content here that you want to appear before the listing. May include shortcodes -->
<div class="basic-property-listing">
',

'after_widget' => '
<!-- Place content here that you want to appear after the listing. May include shortcodes -->
<div class="compliance-wrapper">
	<h3></h3>
	[compliance]
</div>
</div>'
);
