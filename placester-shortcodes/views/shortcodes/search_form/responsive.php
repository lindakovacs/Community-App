<?php
$template = array(

'css' => '
form.pls_search_form_listings .search-item { float: left; margin-bottom: 20px; width: 30%; display: inline-block; margin-left: 2.9%; }
@media (max-width: 979px) { form.pls_search_form_listings .search-item { margin-bottom: 5px; } }
form.pls_search_form_listings .search-item label { float: left; width: 100%; }
@media (min-width: 768px) and (max-width: 979px) { form.pls_search_form_listings .search-item { margin-left: 2%; } }
@media (max-width: 767px) { form.pls_search_form_listings .search-item { margin-left: 2%; width: 47%; } }
@media (max-width: 420px) { form.pls_search_form_listings .search-item { margin-left: 2%; width: 97%; } }
form.pls_search_form_listings .search-item select, form.pls_search_form_listings .search-item .chzn-container { width: 80% !important; }',

'snippet_body' => '
<div class="search-item">
	<label>Min Beds:</label>[min_beds]
</div>
<div class="search-item">
	<label>Min Baths:</label>[min_baths]
</div>
<div class="search-item">
	<label>Min Price:</label>[min_price]
</div>
<div class="search-item">
	<label>Max Price:</label>[max_price]
</div>
<div class="search-item">
	<label>City:</label>[cities]
</div>
<div class="search-item	">
	<label>Property Type:</label>[property_type]
</div>
<div class="search-item	">
	<input type="submit" name="submit" value="Search Now!">
</div>
',

'before_widget'	=> '
<div class="pl-tpl-sf-responsive">',

'after_widget' => '
	<div style="clear:both"></div>
</div>',
);
