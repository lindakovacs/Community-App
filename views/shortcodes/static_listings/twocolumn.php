<?php

$template = array(

	'css' => '
.pl-tpl-stl-twocolumn {
}
.pl-tpl-stl-twocolumn .clear {
	clear: both;
}
.pl-tpl-stl-twocolumn form {
	clear: both;
	padding: 1em 0 0 0;
}
.pl-tpl-stl-twocolumn p {
	display: block !important;
	float: none !important;
	border: none !important;
	margin: 0 0 .1em 0 !important;
	padding: 0 !important;
	background: none !important;
	line-height: 1.2em !important;
}

/* style each listing... */
.pl-tpl-stl-twocolumn .listing-item {
	position: relative;
	display: block !important;
	float: none !important;
	clear: both !important;
	margin: 0 !important;
	border: none !important;
	padding: 10px 0.5% 0 !important;
	width: 99% !important;
	font-size: 14px;
	font-weight: 300 !important;
	overflow: hidden !important;
	background: none !important;
}
.pl-tpl-stl-twocolumn .listing-item>div {
	width: auto !important;
}
.pl-tpl-stl-twocolumn .listing-item div {
	border: none !important;
	background: none !important;
}
/* thumbnail */
.pl-tpl-stl-twocolumn .listing-thumbnail img {
	display: block !important;
	margin: 0 0 5px !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
	height: auto !important;
}
/* defaults for text */
.pl-tpl-stl-twocolumn .listing-item-details a {
	margin: 0 !important;
	padding: 0 !important;
	text-decoration: none !important;
}
/* info block */
.pl-tpl-stl-twocolumn .listing-item-details {
	margin: 0 !important;
	padding: 0 !important;
}
/* heading */
.pl-tpl-stl-twocolumn header {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.pl-tpl-stl-twocolumn p.h4 {
	font-size: 18px !important;
}
.pl-tpl-stl-twocolumn .h4 a {
	color: inherit;
}
.pl-tpl-stl-twocolumn .basic-details ul {
	float: none !important;
	margin: .3em 0 !important;
	padding: 0 !important;
	width: auto !important;
	list-style-type: none !important;
	list-style-image: none !important;
	overflow: hidden !important;
}
.pl-tpl-stl-twocolumn .basic-details li {
	list-style: square outside none !important;
	float: left !important;
	margin: 0 .8em 0.1em 0 !important;
	padding: 0 !important;
	list-style-type: none !important;
	list-style-image: none !important;
	line-height: 1.2em !important;
	font-size: 14px !important;
	font-weight: bold !important;
	font-family: Georgia,"Bitstream Charter",serif !important;
}
.pl-tpl-stl-twocolumn .basic-details li:before {
	content: none !important;
}
/* description and compliance */
.pl-tpl-stl-twocolumn p.listing-description,
.pl-tpl-stl-twocolumn .listing-item .compliance-wrapper p {
	margin: 0 0 .2em 0 !important;
	max-height: 52px !important;
	line-height: 17px !important;
	font-size: 14px !important;
	font-family: Georgia,"Bitstream Charter",serif !important;
	overflow: hidden !important;
}
.pl-tpl-stl-twocolumn .listing-item .compliance-wrapper p,
.pl-tpl-stl-twocolumn .pl-tpl-footer .compliance-wrapper p {
	font-size: .8em !important;
}
.pl-tpl-stl-twocolumn .listing-item .compliance-wrapper {
}
.pl-tpl-stl-twocolumn .listing-item .clear {
	clear: none;
}
.pl-tpl-stl-twocolumn .actions {
	margin: 10px 0 0 !important;
	padding: 0 !important;
}
.pl-tpl-stl-twocolumn .actions a{
	font-size: 88% !important;
}
.pl-tpl-stl-twocolumn a.more-link {
	float: left !important;
	margin-right: 1em !important;
}
.pl-tpl-stl-twocolumn #pl_add_remove_lead_favorites,
.pl-tpl-stl-twocolumn .pl_add_remove_lead_favorites {
	float: right !important;
}

/* compliance -shortcode- in the footer */
.pl-tpl-stl-twocolumn .pl-tpl-footer .compliance-wrapper {
	margin: .5em 0;
	padding: 0;
}

/* controls */
.pl-tpl-stl-twocolumn #pls_num_results_found {
	float: right !important;
	font-size: 115% !important;
	margin: 0 1em 1em 0 !important;
	padding: 0 !important;
}
.pl-tpl-stl-twocolumn .sort_wrapper {
	margin: 1em 0 0 1em !important;
	padding: 0 !important;
	border: 0 !important;
	background: none !important;
	height: auto !important;
	width: auto !important;
}
.pl-tpl-stl-twocolumn .sort_wrapper::before {
	content: "Sort by" !important;
	display: inline-block !important;
	margin: 0 1em 0 0 !important;
	padding: 0 !important;
}
.pl-tpl-stl-twocolumn .sort_wrapper .sort_item {
	display: inline-block !important;
	float: none !important;
	vertical-align: middle !important;
	margin: 0 1em 0 0 !important;
	padding: 0 !important;
	height: auto !important;
	width: 130px !important;
	max-width: 35% !important;
}
.pl-tpl-stl-twocolumn .sort_wrapper .sort_item label {
	display: none !important;
}
.pl-tpl-stl-twocolumn .dataTables_length {
	float: right !important;
	margin: -2.35em 1em 0 0 !important;
	border: 0 !important;
	background: none !important;
	height: auto !important;
	width: auto !important;
}
.pl-tpl-stl-twocolumn .dataTables_length::before {
	content: "Show" !important;
	display: inline-block !important;
	margin: 0 1em 0 0 !important;
	padding: 0 !important;
}
.pl-tpl-stl-twocolumn .dataTables_length label {
	display: inline-block !important;
	float: none !important;
	vertical-align: middle !important;
	margin: 0 !important;
	padding: 0 !important;
	height: auto !important;
	width: 85px !important;
}
.pl-tpl-stl-twocolumn .dataTables_length span#dataTables_length_show,
.pl-tpl-stl-twocolumn .dataTables_length span#dataTables_length_results {
	display: none !important;
}
.pl-tpl-stl-twocolumn .sort_wrapper .sort_item .chzn-container,
.pl-tpl-stl-twocolumn .dataTables_length label .chzn-container {
	width: 100% !important;
}
.pl-tpl-stl-twocolumn .sort_wrapper .sort_item .chzn-drop,
.pl-tpl-stl-twocolumn .dataTables_length label .chzn-container {
	width: 94% !important;
}
.pl-tpl-stl-twocolumn .dataTables_processing {
	visibility: hidden !important;
}
.pl-tpl-stl-twocolumn .dataTables_info {
	display: none !important;
}
.pl-tpl-stl-twocolumn .dataTables_paginate {
	clear: both !important;
	margin: 0 !important;
	padding: 1em 0 0 0 !important;
	border: 0 !important;
	background: none !important;
}
.pl-tpl-stl-twocolumn .dataTables_paginate a {
	margin: 0 0.5em !important;
	padding: 0 !important;
	font-weight: 200 !important;
}
.pl-tpl-stl-twocolumn .dataTables_paginate a.paginate_active {
	font-weight: 400 !important;
}
.pl-tpl-stl-twocolumn .dataTables_paginate a.first,
.pl-tpl-stl-twocolumn .dataTables_paginate a.last {
	display: none !important;
}

/* table formatting */
.pl-tpl-stl-twocolumn #container {
	width: 100% !important;
}
.pl-tpl-stl-twocolumn table,
.pl-tpl-stl-twocolumn thead,
.pl-tpl-stl-twocolumn tfoot,
.pl-tpl-stl-twocolumn tbody,
.pl-tpl-stl-twocolumn tr,
.pl-tpl-stl-twocolumn th,
.pl-tpl-stl-twocolumn td {
	display: block !important;
	margin: 0 !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
}
.pl-tpl-stl-twocolumn table#placester_listings_list {
	position: static !important;
}
.pl-tpl-stl-twocolumn table#placester_listings_list:after {
	display: none !important;
}
.pl-tpl-stl-twocolumn table#placester_listings_list tbody tr {
	background: none !important;
}
.pl-tpl-stl-twocolumn table#placester_listings_list tbody tr td {
	border: 1px solid #dfdfdf !important;
	border-width: 0 0 1px 0 !important;
	background: none !important;
}

@media (min-width: 480px) {
	.pl-tpl-stl-twocolumn table#placester_listings_list tbody tr.odd {
		clear: both !important;
		float: left !important;
		width: 48% !important;
		background: none !important;
	}
	.pl-tpl-stl-twocolumn table#placester_listings_list tbody tr.even {
		float: right !important;
		width: 48% !important;
		background: none !important;
	}
}
',

	'snippet_body' => '
		<div class="listing-item">
			<div class="listing-thumbnail">[image width=375 height=250]</div>
			<div class="listing-item-details">
				<header>
					<p class="listing-item-address h4"><a href="[url]">[address] [locality], [region]</a></p>
				</header>
				<div class="basic-details">
					<ul>
						[if group=\'rets\' attribute=\'mls_id\']<li><span>MLS#: </span>[mls_id]</li>[/if]
						<li>[price]</li>
						[if group=\'cur_data\' attribute=\'beds\']<li>[beds]<span> Bed(s)</span></li>[/if]
						[if group=\'cur_data\' attribute=\'baths\']<li>[baths]<span> Bath(s)</span></li>[/if]
					</ul>
				</div>
				<p class="listing-description p4">[desc]</p>
				[compliance]
			</div>
			<div class="actions">
				<a class="more-link" href="[url]" itemprop="url">View Property Details</a>
				[favorite_link_toggle]
			</div>
		</div>
	',

	'before_widget' => '<div class="pl-tpl-stl-twocolumn">',

	'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
