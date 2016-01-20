<?php
$template = array(

'css' => '
.pl-tpl-stl-responsive .clear { clear: both; }
.visible-phone { display: none !important; }
.visible-tablet { display: none !important; }
.hidden-desktop { display: none !important; }
.visible-desktop { display: inherit !important; }
@media (min-width: 768px) and (max-width: 979px) { .hidden-desktop { display: inherit !important; }
  .visible-desktop { display: none !important; }
  .visible-tablet { display: inherit !important; }
  .hidden-tablet { display: none !important; } }
@media (max-width: 767px) { .hidden-desktop { display: inherit !important; }
  .visible-desktop { display: none !important; }
  .visible-phone { display: inherit !important; }
  .hidden-phone { display: none !important; } }
.visible-print { display: none !important; }
@media print { .visible-print { display: inherit !important; }
  .hidden-print { display: none !important; } }
.non-row-wrapper { padding-bottom: 40px; margin-left: -3%; max-width: 1080px; width: 100%; }
@media (min-width: 1280px) { .non-row-wrapper { margin-left: 1%; } }
@media (min-width: 768px) and (max-width: 979px) { .non-row-wrapper { margin-left: 0%; } }
@media (max-width: 767px) { .non-row-wrapper { margin-left: -1%; } }
@media (max-width: 420px) { .non-row-wrapper { margin-left: -1%; } }
.non-row-wrapper .sort_wrapper { margin-left: 3%; padding: 10px 0 !important; }
.non-row-wrapper .sort_wrapper .sort_item { float: left !important; width: 30% !important; }
.non-row-wrapper .sort_wrapper .sort_item label { float: left; width: 100%; }
.non-row-wrapper #container { width: 100% !important; }
.non-row-wrapper #container tr { width: 30%; display: inline-block; margin-left: 2.9%; }
.non-row-wrapper #container td { border: none; }
@media (min-width: 768px) and (max-width: 979px) { .non-row-wrapper #container tr { margin-left: 2%; } }
@media (max-width: 767px) { .non-row-wrapper #container tr { margin-left: 2%; width: 47%; } }
@media (max-width: 420px) { .non-row-wrapper #container tr { margin-left: 2%; width: 97%; } }
.non-row-wrapper #container tr .wf-listing { width: 100%; }
@media (min-width: 768px) and (max-width: 979px) { .non-row-wrapper #container tr .wf-listing { width: 100%; } }
@media (max-width: 767px) { .non-row-wrapper #container tr .wf-listing { width: 100%; } }
@media (max-width: 420px) { .non-row-wrapper #container tr .wf-listing { width: 100%; } }
.non-row-wrapper #container thead { display: none; }
.non-row-wrapper #container .dataTables_paginate .paginate_active { font-weight: 600; }
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
@media (min-width: 768px) and (max-width: 979px) { .non-row-wrapper .dataTables_info { margin-left: 2%; } }
@media (max-width: 767px) { .non-row-wrapper .dataTables_info { margin-left: 2%; } }
@media (max-width: 420px) { .non-row-wrapper .dataTables_info { margin-left: 2%; } }
.non-row-wrapper .dataTables_paginate { margin-top: 20px; text-align: center; }
@media (min-width: 768px) and (max-width: 979px) { .non-row-wrapper .dataTables_paginate { margin-left: 2%; } }
@media (max-width: 767px) { .non-row-wrapper .dataTables_paginate { margin-left: 2%; }
  .non-row-wrapper .dataTables_paginate a.first, .non-row-wrapper .dataTables_paginate a.last { display: none; } }
@media (max-width: 420px) { .non-row-wrapper .dataTables_paginate { margin-left: 2%; } }
.non-row-wrapper .dataTables_paginate a, .non-row-wrapper .dataTables_paginate a:visited { font-size: 11pt; padding: 6px 8px; text-decoration: none; cursor: pointer; }
.non-row-wrapper .dataTables_paginate a.first, .non-row-wrapper .dataTables_paginate a:visited.first { float: left; margin-top: -6px; }
.non-row-wrapper .dataTables_paginate a.last, .non-row-wrapper .dataTables_paginate a:visited.last { float: right; margin-top: -6px; }
.non-row-wrapper .dataTables_paginate a.paginate_active, .non-row-wrapper .dataTables_paginate a:visited.paginate_active { font-weight: 600; }
.non-row-wrapper .dataTables_paginate a.previous, .non-row-wrapper .dataTables_paginate a:visited.previous { padding-right: 30px; margin-top: -8px; }
.non-row-wrapper .dataTables_paginate a.next, .non-row-wrapper .dataTables_paginate a:visited.next { padding-left: 30px; margin-top: -8px; }
@media (max-width: 767px) { .non-row-wrapper #placester_listings_list_length, .non-row-wrapper .sort_wrapper { display: none !important; } }
.pl-tpl-footer .compliance-wrapper {margin: .2em 0;}
.pl-tpl-footer .compliance-wrapper p {margin: 0 !important; padding: 0 !important; line-height: 1.2em; font-size: .8em;}',

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
</div>',

'before_widget'	=> '<div class="pl-tpl-stl-responsive non-row-wrapper">',

'after_widget' => '<div class="pl-tpl-footer">[compliance]</div></div>',

);
