<?php

$template = array(

'css' => '
.pl-tpl-sl-twentyeleven {
	font-family: "Helvetica Neue", Arial, Helvetica, "Nimbus Sans L", sans-serif;
}
.pl-tpl-sl-twentyeleven .clear {
	clear: both;
}
.pl-tpl-sl-twentyeleven form {
	clear: both;
	padding: 1em 0 0 0;
}
.pl-tpl-sl-twentyeleven p {
	display: block !important;
	float: none !important;
	border: none !important;
	margin: 0 0 .1em 0 !important;
	padding: 0 !important;
	background: none !important;
	line-height: 1.2em !important;
}

/* style each listing... */
.pl-tpl-sl-twentyeleven .listing-item {
	display: block !important;
	float: none !important;
	clear: both !important;
	margin: 0 !important;
	border: none !important;
	padding: 5px 0.5% !important;
	width: 99% !important;
	font-size: 14px !important;
	overflow: hidden !important;
	background: none !important;
}
.pl-tpl-sl-twentyeleven .listing-item>div {
	width: auto !important;
}
.pl-tpl-sl-twentyeleven .listing-item div {
	border: none !important;
	background: none !important;
}
/* heading */
.pl-tpl-sl-twentyeleven .listing-head {
	overflow: hidden;
}
.pl-tpl-sl-twentyeleven h4 {
	margin: 0 0 6px 0 !important;
	padding: 0 !important;
	background: none !important;
	font-size: 18px !important;
	font-weight: bold !important;
}
.pl-tpl-sl-twentyeleven h4 a {
	color: inherit !important;
	text-decoration: none !important;
}
.pl-tpl-sl-twentyeleven h4 a:visited {
	color: inherit !important;
}
/* image block */
.pl-tpl-sl-twentyeleven .listing-thumbnail {
	float: left;
	margin-right: 20px;
	width: 180px;
}
.pl-tpl-sl-twentyeleven .listing-thumbnail img {
	display: block !important;
	-moz-box-sizing: border-box !important;
	margin: 0 0 10px 0 !important;
	border: 0  !important;
	border-radius: 0 !important;
	box-shadow: 3px 3px 3px 0 rgba(0, 0, 0, 0.25) !important;
	background: none repeat scroll 0 0 #F2F2F2 !important;
	padding: 0 !important;
	width: 180px !important;
	height: 120px !important;
}
/* details block */
.pl-tpl-sl-twentyeleven .listing-item-details ul {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
	min-width: 100px;
	list-style-type: none !important;
	list-style-image: none !important;
	overflow: hidden !important;
}
.pl-tpl-sl-twentyeleven .listing-item-details ul li {
	float: left !important;
	margin: 0 .8em 0.1em 0 !important;
	padding: 0 !important;
	list-style-type: none !important;
	list-style-image: none !important;
	line-height: 1.2em !important;
	font-size: 14px !important;
	font-weight: bold !important;
}
.pl-tpl-sl-twentyeleven .listing-item-details ul li:before {
	content: none !important;
}
.pl-tpl-sl-twentyeleven .listing-item-details ul li span {
	font-weight: 300;
}
.pl-tpl-sl-twentyeleven .basic-details {
}
.pl-tpl-sl-twentyeleven p.price {
	margin-bottom: .8em !important;
	font-size: 14px !important;
}
.pl-tpl-sl-twentyeleven p.price span {
	font-weight: bold;
}
.pl-tpl-sl-twentyeleven p.mls {
	font-size: 12px !important;
}
/* description and compliance */
.pl-tpl-sl-twentyeleven .listing-item .compliance-wrapper {
	clear: both !important;
}
.pl-tpl-sl-twentyeleven .listing-item .compliance-wrapper img,
.pl-tpl-sl-twentyeleven .listing-item .agent-details img {
	display: none !important;
}
.pl-tpl-sl-twentyeleven p.desc,
.pl-tpl-sl-twentyeleven .listing-item .compliance-wrapper p {
	margin-bottom: 5px !important;
	max-height: 52px !important;
	max-width: 370px !important;
	line-height: 17px !important;
	font-size: 14px !important;
	overflow: hidden !important;
}
.pl-tpl-sl-twentyeleven .listing-item .compliance-wrapper p,
.pl-tpl-sl-twentyeleven .pl-tpl-footer .compliance-wrapper p {
	font-size: .8em !important;
}
.pl-tpl-sl-twentyeleven .listing-item .clear {
	clear: none;
}
.pl-tpl-sl-twentyeleven .actions {
	display: block !important;
	clear: both !important;
	float: right !important;
	margin: 0 !important;
	padding: 0 !important;
	font-size: 15px !important;
	text-decoration: none !important;
}
.pl-tpl-sl-twentyeleven #pl_add_remove_lead_favorites,
.pl-tpl-sl-twentyeleven .pl_add_remove_lead_favorites,
.pl-tpl-sl-twentyeleven .view-details {
	float: left;
	margin: 0 !important;
	padding: 0 !important;
}
.pl-tpl-sl-twentyeleven .view-details a {
	margin-left: 2em !important;
}

/* compliance -shortcode- in the footer */
.pl-tpl-sl-twentyeleven .pl-tpl-footer .compliance-wrapper {
	clear: both !important;
	margin: 2em 0 !important;
	padding: 0 !important;
}
.pl-tpl-sl-twentyeleven .pl-tpl-footer .compliance-wrapper img {
	float: left !important;
	height: auto !important;
	width: auto !important;
	margin: 0 .75em .5em 0 !important;
	padding: 0 !important;
}

/* controls */
.pl-tpl-sl-twentyeleven #pls_num_results_found {
	float: right !important;
	font-size: 115% !important;
	margin: 0 1em 1em 0 !important;
	padding: 0 !important;
}
.pl-tpl-sl-twentyeleven .sort_wrapper {
	margin: 1em 0 0 1em !important;
	padding: 0 !important;
	border: 0 !important;
	background: none !important;
	height: auto !important;
	width: auto !important;
}
.pl-tpl-sl-twentyeleven .sort_wrapper::before {
	content: "Sort by" !important;
	display: inline-block !important;
	margin: 0 1em 0 0 !important;
	padding: 0 !important;
}
.pl-tpl-sl-twentyeleven .sort_wrapper .sort_item {
	display: inline-block !important;
	float: none !important;
	vertical-align: middle !important;
	margin: 0 1em 0 0 !important;
	padding: 0 !important;
	height: auto !important;
	width: 130px !important;
	max-width: 35% !important;
}
.pl-tpl-sl-twentyeleven .sort_wrapper .sort_item label {
	display: none !important;
}
.pl-tpl-sl-twentyeleven .dataTables_length {
	display: none !important;
	float: right !important;
	margin: -2.35em 1em 0 0 !important;
	border: 0 !important;
	background: none !important;
	height: auto !important;
	width: auto !important;
}
.pl-tpl-sl-twentyeleven .dataTables_length::before {
	content: "Show" !important;
	display: inline-block !important;
	margin: 0 1em 0 0 !important;
	padding: 0 !important;
}
.pl-tpl-sl-twentyeleven .dataTables_length label {
	display: inline-block !important;
	float: none !important;
	vertical-align: middle !important;
	margin: 0 !important;
	padding: 0 !important;
	height: auto !important;
	width: 85px !important;
}
.pl-tpl-sl-twentyeleven .dataTables_length span#dataTables_length_show,
.pl-tpl-sl-twentyeleven .dataTables_length span#dataTables_length_results {
	display: none !important;
}
.pl-tpl-sl-twentyeleven .sort_wrapper .sort_item .chzn-container,
.pl-tpl-sl-twentyeleven .dataTables_length label .chzn-container {
	width: 100% !important;
}
.pl-tpl-sl-twentyeleven .sort_wrapper .sort_item .chzn-drop,
.pl-tpl-sl-twentyeleven .dataTables_length label .chzn-container {
	width: 94% !important;
}
.pl-tpl-sl-twentyeleven .dataTables_processing {
	visibility: hidden !important;
}
.pl-tpl-sl-twentyeleven .dataTables_info {
	display: none !important;
}
.pl-tpl-sl-twentyeleven .dataTables_paginate {
	clear: both !important;
	margin: 0 !important;
	padding: 1em 0 0 0 !important;
	border: 0 !important;
	background: none !important;
}
.pl-tpl-sl-twentyeleven .dataTables_paginate a {
	margin: 0 0.5em !important;
	padding: 0 !important;
	font-weight: 200 !important;
}
.pl-tpl-sl-twentyeleven .dataTables_paginate a.paginate_active {
	font-weight: 400 !important;
}
.pl-tpl-sl-twentyeleven .dataTables_paginate a.first,
.pl-tpl-sl-twentyeleven .dataTables_paginate a.last {
	display: none !important;
}

/* table formatting */
.pl-tpl-sl-twentyeleven #container {
	width: 100% !important;
}
.pl-tpl-sl-twentyeleven table,
.pl-tpl-sl-twentyeleven thead,
.pl-tpl-sl-twentyeleven tfoot,
.pl-tpl-sl-twentyeleven tbody,
.pl-tpl-sl-twentyeleven tr,
.pl-tpl-sl-twentyeleven th,
.pl-tpl-sl-twentyeleven td {
	display: block !important;
	margin: 0 !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
}
.pl-tpl-sl-twentyeleven table#placester_listings_list {
	position: static !important;
}
.pl-tpl-sl-twentyeleven table#placester_listings_list:after {
	display: none !important;
}
.pl-tpl-sl-twentyeleven table#placester_listings_list tbody tr {
	background: none !important;
}
.pl-tpl-sl-twentyeleven table#placester_listings_list tbody tr td {
	border: 1px solid #dfdfdf !important;
	border-width: 0 0 1px 0 !important;
	background: none !important;
}

/* styling for alternate rows */
.pl-tpl-sl-twentyeleven table tbody tr.odd td {
		clear: both !important;
		float: none !important;
}
.pl-tpl-sl-twentyeleven table tbody tr.even td {
		clear: both !important;
		float: none !important;
}
',

'snippet_body' => '
<div class="listing-item">

	<div class="listing-head">
		<h4><a href="[url]">[address] [locality], [region]</a></h4>
	</div>

	<div class="listing-body">

		<div class="listing-thumbnail">
			[image]
		</div>

		<div class="listing-item-details">

			<div class="basic-details">
				<ul>
					[if group=\'cur_data\' attribute=\'beds\']<li>[beds]<span> Bed(s)</span></li>[/if]
					[if group=\'cur_data\' attribute=\'baths\']<li>[baths]<span> Bath(s)</span></li>[/if]
					[if group=\'cur_data\' attribute=\'sqft\']<li>[sqft]<span> Sqft</span></li>[/if]
				</ul>

				<p class="mls">MLS #: [mls_id]</p>
				<p class="price">Price: <span>[price]</span></p>
			</div>

			<p class="desc">[desc]</p>
			[compliance]

		</div><!--listing-item-details-->
		<div class="actions">
			[favorite_link_toggle]
			<div class="view-details">
				<a href="[url]">View Listing Details</a>
			</div>
		</div>
		<div class="clearfix"></div>

	</div>

</div><!--listing-item-->
',

'before_widget' => '<div class="pl-tpl-sl-twentyeleven">
<div id="pls_num_results_found" class="search_results">
	<span id="pls_num_results"></span> listings match your search
</div>',

'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
