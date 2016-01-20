<?php

$template = array(

'css' => '
.pl-tpl-fl-twentyeleven {
	font-family: "Helvetica Neue", Arial, Helvetica, "Nimbus Sans L", sans-serif;
	font-style: normal;
}
.pl-tpl-fl-twentyeleven .pls-listings {
	margin: 0 !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
}
.pl-tpl-fl-twentyeleven .clear {
	clear: both;
}
.pl-tpl-fl-twentyeleven p {
	display: block !important;
	float: none !important;
	border: none !important;
	margin: 0 0 .1em 0 !important;
	padding: 0 !important;
	background: none !important;
	line-height: 1.2em !important;
}

/* style each listing... */
.pl-tpl-fl-twentyeleven .listing-item {
	display: block !important;
	float: none !important;
	clear: both !important;
	margin: 0 !important;
	border: 1px solid #dfdfdf !important;
	border-width: 0 0 1px 0 !important;
	padding: 5px 0.5% !important;
	width: 99% !important;
	font-size: 14px !important;
	overflow: hidden !important;
	background: none !important;
}
.pl-tpl-fl-twentyeleven .listing-item>div {
	width: auto !important;
}
.pl-tpl-fl-twentyeleven .listing-item div {
	border: none !important;
	background: none !important;
}
/* heading */
.pl-tpl-fl-twentyeleven .listing-head {
	overflow: hidden !important;
}
.pl-tpl-fl-twentyeleven h4 {
	margin: 0 0 6px 0 !important;
	padding: 0 !important;
	background: none !important;
	font-size: 18px !important;
	font-weight: bold !important;
}
.pl-tpl-fl-twentyeleven h4 a {
	color: inherit !important;
	text-decoration: none !important;
}
.pl-tpl-fl-twentyeleven h4 a:visited {
	color: inherit !important;
}
/* image block */
.pl-tpl-fl-twentyeleven .listing-thumbnail {
	float: left;
	margin-right: 20px;
	width: 180px;
}
.pl-tpl-fl-twentyeleven .listing-thumbnail img {
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
.pl-tpl-fl-twentyeleven .listing-item-details ul {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
	min-width: 100px;
	list-style-type: none !important;
	list-style-image: none !important;
	overflow: hidden !important;
}
.pl-tpl-fl-twentyeleven .listing-item-details ul li {
	float: left !important;
	margin: 0 .8em 0.1em 0 !important;
	padding: 0 !important;
	list-style-type: none !important;
	list-style-image: none !important;
	line-height: 1.2em !important;
	font-size: 14px !important;
	font-weight: bold !important;
}
.pl-tpl-fl-twentyeleven .listing-item-details ul li:before {
	content: none !important;
}
.pl-tpl-fl-twentyeleven .listing-item-details ul li span {
	font-weight: 300;
}
.pl-tpl-fl-twentyeleven .basic-details {
}
.pl-tpl-fl-twentyeleven p.price {
	margin-bottom: .8em !important;
	font-size: 14px !important;
}
.pl-tpl-fl-twentyeleven p.price span {
	font-weight: bold;
}
.pl-tpl-fl-twentyeleven p.mls {
	font-size: 12px !important;
}
/* description and compliance */
.pl-tpl-fl-twentyeleven .listing-item .compliance-wrapper {
	clear: both !important;
}
.pl-tpl-fl-twentyeleven .listing-item .compliance-wrapper img,
.pl-tpl-fl-twentyeleven .listing-item .agent-details img {
	display: none !important;
}
.pl-tpl-fl-twentyeleven p.desc,
.pl-tpl-fl-twentyeleven .listing-item .compliance-wrapper p {
	margin-bottom: 5px !important;
	max-height: 52px !important;
	max-width: 370px !important;
	line-height: 17px !important;
	font-size: 14px !important;
	overflow: hidden !important;
}
.pl-tpl-fl-twentyeleven .listing-item .compliance-wrapper p,
.pl-tpl-fl-twentyeleven .pl-tpl-footer .compliance-wrapper p {
	font-size: .8em !important;
}
.pl-tpl-fl-twentyeleven .listing-item .clear {
	clear: none;
}
.pl-tpl-fl-twentyeleven .actions {
	display: block !important;
	clear: both !important;
	float: right !important;
	margin: 0 !important;
	padding: 0 !important;
	font-size: 15px !important;
	text-decoration: none !important;
}
.pl-tpl-fl-twentyeleven #pl_add_remove_lead_favorites,
.pl-tpl-fl-twentyeleven .pl_add_remove_lead_favorites,
.pl-tpl-fl-twentyeleven .view-details {
	float: left;
	margin: 0 !important;
	padding: 0 !important;
}
.pl-tpl-fl-twentyeleven .view-details a {
	margin-left: 2em !important;
}

/* compliance -shortcode- in the footer */
.pl-tpl-fl-twentyeleven .pl-tpl-footer .compliance-wrapper {
	clear: both !important;
	margin: 2em 0 !important;
	padding: 0 !important;
}
.pl-tpl-fl-twentyeleven .pl-tpl-footer .compliance-wrapper img {
	float: left !important;
	height: auto !important;
	width: auto !important;
	margin: 0 .75em .5em 0 !important;
	padding: 0 !important;
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
					<li>[beds]<span> Bed(s)</span></li>
					<li>[baths]<span> Bath(s)</span></li>
					<li>[sqft]<span> Sqft</span></li>
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

'before_widget' => '<div class="pl-tpl-fl-twentyeleven">',

'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
