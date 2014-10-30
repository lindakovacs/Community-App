<?php

$template = array(

	'css' => '
.pl-tpl-fvl-twocolumn {
}
.pl-tpl-fvl-twocolumn .clear {
	clear: both;
}
.pl-tpl-fvl-twocolumn form {
	clear: both;
	padding: 1em 0 0 0;
}
.pl-tpl-fvl-twocolumn p {
	display: block !important;
	float: none !important;
	border: none !important;
	margin: 0 0 .1em 0 !important;
	padding: 0 !important;
	background: none !important;
	line-height: 1.2em !important;
}

/* style each listing... */
.pl-tpl-fvl-twocolumn,
.pl-tpl-fvl-twocolumn .pls-listings {
	margin: 0 !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
	background: none !important;
}
.pl-tpl-fvl-twocolumn .pls-listings:after {
	display: none !important;
}
.pl-tpl-fvl-twocolumn .listing-item {
	position: relative;
	display: block !important;
	margin: 0 !important;
	border: 1px solid #dfdfdf !important;
	border-width: 0 0 1px 0 !important;
	padding: 10px 0.5% 0 !important;
	width: 99% !important;
	font-size: 14px;
	font-weight: 300 !important;
	overflow: hidden !important;
	background: none !important;
}
.pl-tpl-fvl-twocolumn .listing-item>div {
	width: auto !important;
}
.pl-tpl-fvl-twocolumn .listing-item div {
	border: none !important;
	background: none !important;
}
/* thumbnail */
.pl-tpl-fvl-twocolumn .listing-thumbnail img {
	display: block !important;
	margin: 0 0 5px !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
	height: auto !important;
}
/* defaults for text */
.pl-tpl-fvl-twocolumn .listing-item-details a {
	margin: 0 !important;
	padding: 0 !important;
	text-decoration: none !important;
}
/* info block */
.pl-tpl-fvl-twocolumn .listing-item-details {
	margin: 0 !important;
	padding: 0 !important;
}
/* heading */
.pl-tpl-fvl-twocolumn header {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.pl-tpl-fvl-twocolumn p.h4 {
	font-size: 18px !important;
}
.pl-tpl-fvl-twocolumn .h4 a {
	color: inherit;
}
.pl-tpl-fvl-twocolumn .basic-details ul {
	float: none !important;
	margin: .3em 0 !important;
	padding: 0 !important;
	width: auto !important;
	list-style-type: none !important;
	list-style-image: none !important;
	overflow: hidden !important;
}
.pl-tpl-fvl-twocolumn .basic-details li {
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
.pl-tpl-fvl-twocolumn .basic-details li:before {
	content: none !important;
}
/* description and compliance */
.pl-tpl-fvl-twocolumn p.listing-description,
.pl-tpl-fvl-twocolumn .listing-item .compliance-wrapper p {
	margin: 0 0 .2em 0 !important;
	max-height: 52px !important;
	line-height: 17px !important;
	font-size: 14px !important;
	font-family: Georgia,"Bitstream Charter",serif !important;
	overflow: hidden !important;
}
.pl-tpl-fvl-twocolumn .listing-item .compliance-wrapper p,
.pl-tpl-fvl-twocolumn .pl-tpl-footer .compliance-wrapper p {
	font-size: .8em !important;
}
.pl-tpl-fvl-twocolumn .listing-item .compliance-wrapper {
}
.pl-tpl-fvl-twocolumn .listing-item .clear {
	clear: none;
}
.pl-tpl-fvl-twocolumn .actions {
	margin: 10px 0 0 !important;
	padding: 0 !important;
}
.pl-tpl-fvl-twocolumn .actions a{
	font-size: 88% !important;
}
.pl-tpl-fvl-twocolumn a.more-link {
	float: left !important;
	margin-right: 1em !important;
}
.pl-tpl-fvl-twocolumn #pl_add_remove_lead_favorites,
.pl-tpl-fvl-twocolumn .pl_add_remove_lead_favorites {
	float: right !important;
}

/* compliance -shortcode- in the footer */
.pl-tpl-fvl-twocolumn .pl-tpl-footer .compliance-wrapper {
	margin: .5em 0;
	padding: 0;
}

@media (min-width: 480px) {
	.pl-tpl-fvl-twocolumn .pls-listings .listing-item:nth-child(odd) {
		clear: both !important;
		float: left !important;
		width: 48% !important;
		background: none !important;
	}
	.pl-tpl-fvl-twocolumn .pls-listings .listing-item:nth-child(even) {
		clear: none !important;
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

	'before_widget' => '<div class="pl-tpl-fvl-twocolumn">',

	'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
