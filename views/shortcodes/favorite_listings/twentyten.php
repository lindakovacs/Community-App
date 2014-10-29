<?php

$template = array(

'css' => '
/*
This template has no html body because it uses the built in listing renderer.
It can be used as a guide for making a custom template that styles output created by the built in renderer.
*/
.pl-tpl-fvl-twentyten {
	font-style: normal;
}
.pl-tpl-fvl-twentyten .clear {
	clear: both;
}
.pl-tpl-fvl-twentyten form {
	clear: both;
	padding: 1em 0 0 0;
}
.pl-tpl-fvl-twentyten p {
	display: block !important;
	float: none !important;
	border: none !important;
	margin: 0 0 .1em 0 !important;
	padding: 0 !important;
	background: none !important;
	line-height: 1.2em !important;
}

/* style each listing... */
.pl-tpl-fvl-twentyten .pls-listings {
	margin: 0 !important;
	border: 0 !important;
	padding: 0 !important;
	width: 100% !important;
}
.pl-tpl-fvl-twentyten .listing-item {
	position: relative;
	display: block !important;
	float: none !important;
	clear: both !important;
	margin: 0 !important;
	border: 1px solid #dfdfdf !important;
	border-width: 0 0 1px 0 !important;
	padding: 10px 0.5% 25px !important;
	width: 99% !important;
	font-size: 14px;
	font-weight: 300 !important;
	overflow: hidden !important;
	background: none !important;
}
.pl-tpl-fvl-twentyten .listing-item>div {
	width: auto !important;
}
.pl-tpl-fvl-twentyten .listing-item div {
	border: none !important;
	background: none !important;
}
/* thumbnail */
.pl-tpl-fvl-twentyten .listing-thumbnail {
	float: left !important;
	margin: 0 20px 5px 0 !important;
	width: 180px !important;
}
.pl-tpl-fvl-twentyten .listing-thumbnail img {
	display: block !important;
	margin: 0 !important;
	border: none !important;
	padding: 0 !important;
	width: 180px !important;
	height: 120px !important;
}
/* defaults for text */
.pl-tpl-fvl-twentyten .listing-item-details a {
	margin: 0 !important;
	padding: 0 !important;
	text-decoration: none !important;
}
/* info block */
.pl-tpl-fvl-twentyten .listing-item-details {
	margin: 0 !important;
	padding: 0 !important;
}
/* heading */
.pl-tpl-fvl-twentyten header {
	float: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.pl-tpl-fvl-twentyten p.h4 {
	max-width: 570px !important;
	font-size: 18px !important;
}
.pl-tpl-fvl-twentyten .h4 a {
	color: inherit;
}
.pl-tpl-fvl-twentyten .basic-details ul {
	float: none !important;
	margin: .3em 0 !important;
	padding: 0 !important;
	width: auto !important;
	max-width: 370px !important;
	list-style-type: none !important;
	list-style-image: none !important;
	overflow: hidden !important;
}
.pl-tpl-fvl-twentyten .basic-details li {
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
.pl-tpl-fvl-twentyten .basic-details li:before {
	content: none !important;
}
/* description and compliance */
.pl-tpl-fvl-twentyten p.listing-description,
.pl-tpl-fvl-twentyten .listing-item .compliance-wrapper p {	
	float: left !important;
	margin: 0 0 .2em 0 !important;
	max-height: 52px !important;
	max-width: 370px !important;
	line-height: 17px !important;
	font-size: 14px !important;
	font-family: Georgia,"Bitstream Charter",serif !important;
	overflow: hidden !important;
}
.pl-tpl-fvl-twentyten .listing-item .compliance-wrapper p,
.pl-tpl-fvl-twentyten .pl-tpl-footer .compliance-wrapper p {
	font-size: .8em !important;
}
.pl-tpl-fvl-twentyten .listing-item .compliance-wrapper {
	float: right;
}
.pl-tpl-fvl-twentyten .listing-item .clear {
	clear: none;
}
.pl-tpl-fvl-twentyten .actions {
	float: none !important;
	position: absolute;
	bottom: 0;
	right: 0;
	margin: 0 !important;
	padding: 0 0 .2em 0 !important;
}
.pl-tpl-fvl-twentyten a.more-link {
	float: right !important;
	margin-left: 1em !important;
}
.pl-tpl-fvl-twentyten #pl_add_remove_lead_favorites,
.pl-tpl-fvl-twentyten .pl_add_remove_lead_favorites {
	float: right !important;
}

/* compliance -shortcode- in the footer */
.pl-tpl-fvl-twentyten .pl-tpl-footer .compliance-wrapper {
	margin: .5em 0;
	padding: 0;
}
',

'snippet_body' => '',

'before_widget' => '<div class="pl-tpl-fvl-twentyten">',

'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
