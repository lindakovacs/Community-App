<?php

$template = array(

'css' => '
/* controls background of caption area */
.pl-tpl-lss-twentyeleven .orbit-caption {
	padding: 20px 0 !important;
	height: auto !important;
	background: none repeat scroll 0 0 rgba(0, 0, 0, 0.6) !important;
}
.pl-tpl-lss-twentyeleven p {
	float: none !important;
	margin: 0 20px !important;
	padding: 0 !important;
	font-size: 14px !important;
	font-family: "Helvetica Neue", Arial, Helvetica, "Nimbus Sans L", sans-serif !important;
	color: #fff !important;
}
.pl-tpl-lss-twentyeleven p.caption-title {
	font-size: 1.5em !important;
}
.pl-tpl-lss-twentyeleven a,
.pl-tpl-lss-twentyeleven a:visited {
	color: #fff !important;
	text-decoration: none !important;
}
.pl-tpl-lss-twentyeleven img {
	border: none !important;
	max-width: none !important;
}
.pl-tpl-lss-twentyeleven ul.orbit-bullets {
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

'snippet_body' => '
<!-- twentyeleven -->
<div id="caption-[ls_index]" class="orbit-caption">
	<p class="caption-title"><a href="[ls_url]">[ls_address]</a></p>
	<p class="caption-subtitle"><span class="price">[ls_beds] beds</span>, <span class="baths">[ls_baths] baths</span></p>
	<a class="button details" href="[ls_url]"><span></span></a>
</div>
',

'before_widget' => '<div class="pl-tpl-lss-twentyeleven">',

'after_widget' => '</div>',

);
