<?php

$template = array(

'css' => '
/*
This template has no html body because it uses the built in listing renderer.
It can be used as a guide for making a custom template that styles output created by the built in renderer.
*/

.pl-tpl-lss-twentyten {
	clear: both;
	float: none;
}

/* controls background of caption area */
.pl-tpl-lss-twentyten .orbit-caption {
	padding: 20px 0 !important;
	height: auto !important;
	background: none repeat scroll 0 0 rgba(0, 0, 0, 0.6) !important;
}
.pl-tpl-lss-twentyten p {
	float: none !important;
	margin: 0 20px !important;
	padding: 0 !important;
	font-size: 14px !important;
	font-family: Georgia,"Bitstream Charter",serif !important;
	color: #fff !important;
}
.pl-tpl-lss-twentyten p.caption-title {
	font-size: 1.5em !important;
}
.pl-tpl-lss-twentyten a,
.pl-tpl-lss-twentyten a:visited {
	color: #fff !important;
	text-decoration: none !important;
}
.pl-tpl-lss-twentyten img {
	border: none !important;
	max-width: none !important;
}
.pl-tpl-lss-twentyten ul.orbit-bullets {
	float: none !important;
	position: absolute !important;
	right: 10px !important;
	bottom: 2px !important;
	z-index: 1000 !important;
	list-style: none outside none !important;
	margin: 0 !important;
	padding: 0 !important;
	width: auto !important;
}
',

'snippet_body' => '',

'before_widget' => '<div class="pl-tpl-lss-twentyten">',

'after_widget' => '</div>',

);
