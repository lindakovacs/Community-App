<?php

$template = array(

'css' => '
.pl-tpl-sf-twentyten {
	margin: 10px 0;
	padding: 0;
	width: 100%;
	font-family: Georgia, "Bitstream Charter", serif;
}
.pl-tpl-sf-twentyten h3 {
}
.pl-tpl-sf-twentyten h6 {
	font-family: Georgia, "Bitstream Charter", serif;
}
.pl-tpl-sf-twentyten form {
	clear: both;
	margin: 0 !important;
	border: none !important;
	padding: 0 !important;
}
.pl-tpl-sf-twentyten .clear {
	clear: both;
}
.pl-tpl-sf-twentyten .form-grp {
	float: left !important;
	margin: 0 0 7px 0 !important;
	width: 50% !important;
	min-width: 12em !important;
}
.pl-tpl-sf-twentyten h6 {
	margin: 0 0 7px 0 !important;
	padding: 0px !important;
	line-height: 1em !important;
	text-transform: uppercase !important;
	font-size: 16px !important;
	font-weight: bold !important;
}
.pl-tpl-sf-twentyten .select-grp {
	margin: 0 !important;
	padding: 0 !important;
	padding-bottom: 10px;
}
.pl-tpl-sf-twentyten label {
	display: block !important;
	float: left;
	margin: 0 0 .1em 0 !important;
	padding: 0 !important;
	width: 9em !important;
	line-height: 1.4em !important;
	font-size: 14px !important;
	font-family: Georgia, "Bitstream Charter", serif !important;
	font-weight: normal !important;
}
.pl-tpl-sf-twentyten .select-grp select {
	vertical-align: top !important;
	margin: 0 0 .2em .1em !important;
	width: 10em !important;
	line-height: 1.2em !important;
	font-size: 14px !important;
}
/* styling for Chosen if used */
.pl-tpl-sf-twentyten .chzn-container {
	margin: -.2em 0 .3em 0;
}
/* search button */
.pl-tpl-sf-twentyten #search-button {
	float: left !important;
	margin: 0 1em 0 0 !important;
}
/* result count */
.pl-tpl-sf-twentyten .search_results {
	float: right !important;
	margin: 0 !important;
	padding: 0 !important;
	font-size: 14px !important;
	font-weight: bold !important;
	font-family: "Helvetica Neue";
}
',

'snippet_body' => '
<!-- <div> -->
<h3>Search Listings</h3>

<div class="form-grp">
	<h6>Location</h6>
	<div class="select-grp">
		<label>City</label>
		[cities]
	</div>
	<div class="select-grp">
		<label>State</label>
		[states]
	</div>
	<div class="select-grp">
		<label>Zipcode</label>
		[zips]
	</div>
</div>

<div class="form-grp">
	<h6>Listing Type</h6>
	<div id="purchase_type_container" class="select-grp">
		<label>Transaction Type</label>
		[purchase_types]
	</div>
	<div class="select-grp">
		<label>Property Type</label>
		[property_type]
	</div>
	<div class="select-grp">
		<label>Zoning Type</label>
		[zoning_types]
	</div>
</div>

<div class="form-grp">
	<h6>Price Range</h6>
	<div id="min_price_container" class="select-grp">
		<label>Price From</label>
		[min_price]
	</div>
	<div id="max_price_container" class="select-grp">
		<label>Price To</label>
		[max_price]
	</div>
</div>

<div class="form-grp">
	<h6>Details</h6>
	<div class="select-grp">
		<label>Bed(s)</label>
		[bedrooms]
	</div>
	<div class="select-grp">
		<label>Bath(s)</label>
		[bathrooms]
	</div>
</div>

<!-- </div> -->
<div class="clr"></div>

<input type="submit" name="submit" value="Search Now!">
',

'before_widget' => '<div class="pl-tpl-sf-twentyten">',

'after_widget' => '</div>',

);
