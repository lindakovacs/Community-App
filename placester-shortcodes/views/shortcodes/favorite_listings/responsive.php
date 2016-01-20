<?php
$template = array(

'css' => '
.pl-tpl-fvl-responsive .clear { clear: both; }
.wf-listing { width: 30%; display: inline-block; margin-left: 2.9%; }
@media (min-width: 768px) and (max-width: 979px) { .wf-listing { margin-left: 2%; } }
@media (max-width: 767px) { .wf-listing { margin-left: 2%; width: 47%; } }
@media (max-width: 420px) { .wf-listing { margin-left: 2%; width: 97%; } }
.wf-listing .wf-image { width: 100%; }
.wf-listing .wf-image a img { width: 100%; }
.wf-listing { vertical-align: top; padding-bottom: 30px; }
.wf-listing .wf-image img { border: none !important; float: left !important; width: 100% !important; max-width: 100% !important; }
.wf-listing .wf-image .wf-price { color: white; text-decoration: none; font-size: 0.9em; padding: 6px 12px; margin: -37px 0 0 0 !important; float: left; background: black; background: rgba(0, 0, 0, 0.8); }
.wf-listing .wf-address, .wf-listing .wf-basics { float: left; width: 100%; font-family: Arial, sans-serif; }
.wf-listing .wf-address { margin: 10px 0 0 !important; font-size: 18px; line-height: 20px; height: 42px; overflow: hidden; }
.wf-listing .wf-basics { margin: 10px 0 0; font-size: 14px; color: #4b4b4b; }
.pl-tpl-footer .compliance-wrapper { margin: .2em 0; }
.pl-tpl-footer .compliance-wrapper p { margin: 0 !important; padding: 0 !important; line-height: 1.2em; font-size: .8em; }',

'snippet_body' => '
<!-- Listing -->
<div class="wf-listing">
	<div class="wf-image">
		<a href="[url]">
			[image width=300]
		</a>
		<p class="wf-price">[price]</p>
	</div>
	<p class="wf-address">
		<a href="[url]">[address] [locality], [region]</a>
		</p>
	<p class="wf-basics">
		[if group=\'cur_data\' attribute=\'beds\']<span class="hidden-phone">Beds: <strong>[beds]</strong>&nbsp;</span> [/if][if group=\'cur_data\' attribute=\'baths\']<span class="hidden-phone">Baths: <strong>[baths]</strong>&nbsp;</span> [/if]<span class="wf-mls">MLS #: [mls_id]</span>
	</p>
	<div class="actions">[favorite_link_toggle]</div>
</div>
',

'before_widget'	=> '<div class="pl-tpl-fvl-responsive non-row-wrapper">',

'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
