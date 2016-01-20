<?php
$template = array(

'css' => '
/* sample div used to wrap the slideshow plus any additional html */
.pl-tpl-ls-responsive {
	overflow: hidden;;
}
/* sample div used to wrap the slideshow */
.pl-tpl-ls-wrapper {
	float: left;
	border: 1px solid #7f7f7f;
	padding: 5px;
}
/* controls background of caption area */
.orbit-wrapper .orbit-caption {
	background: none repeat scroll 0 0 rgba(0, 0, 0, 0.6);
	color: #fff;
}
/* controls general layout of caption items */
.orbit-wrapper .orbit-caption p {
	margin: 0;
	padding: 10px 20px 0;
}
/* caption title */
.orbit-wrapper .orbit-caption .caption-title {
	font-weight: bold;
	font-size: 1.8em;
}
/* caption sub-title */
.orbit-wrapper .orbit-caption .caption-subtitle {
	padding-top: 0;
	padding-bottom: 10px;
	font-size: 1.2em;
}
/* make sure caption links are visible! */
#main .pl-tpl-ls-responsive .orbit-wrapper .orbit-caption a {
	color: #fff;
	text-decoration: none;
}
#main .pl-tpl-ls-responsive .orbit-wrapper .orbit-caption a:hover {
	color: #fff;
	text-decoration: underline;
}
',

'snippet_body' => '
<div id="caption-[ls_index]" class="orbit-caption">
	<p class="caption-title"><a href="[ls_url]">[ls_address]</a></p>
	<p class="caption-subtitle"><span class="price">[ls_beds] beds</span>, <span class="baths">[ls_baths] baths</span></p>
	<a class="button details" href="[ls_url]"><span></span></a>
</div>',

'before_widget'	=> '<div class="pl-tpl-ls-responsive">
	<div class="pl-tpl-ls-wrapper">',

'after_widget' => '	</div>
</div>',
);
