<?php

$template = array(

'title' => 'Columbus',


'css' => '.property-listing {
  overflow: hidden;
  line-height: 1.3em !important;
}

.property-listing ul {
  float: none !important;
  margin: 0 !important;
  padding: 0 !important;
  list-style: none !important;
  overflow: hidden;
}
.property-listing li {
  display: block;
  float: left !important;
}
.property-listing .subsection ul {
  float: none !important;
  padding: 5px 10px 0 20px !important;
}
.property-listing .subsection li {
  margin: 0 1.2em .5em 0 !important;
  width: 250px !important;		
  font-weight: bold !important;
  font-size: 13px !important;
  word-wrap: break-word !important;
}
.property-listing h2,
.property-listing h3 {
  clear: both;
  margin: 1em 0 0 0 !important;
  padding: 0 !important;
  text-transform: uppercase;
}
.property-listing .subsection h3 {
  padding: .3em .8em !important;
  background: #4782bd;
  background: -webkit-gradient(linear, left top, left bottom, from(#4782bd), to(#5d98d3));
  background: -moz-linear-gradient(top, #4782bd, #5d98d3);
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#4782bd\', endColorstr=\'#5d98d3\');
  border: 1px solid #3d6995;
  color: white !important;
  font-size: 17px;
  text-shadow: 1px 1px black !important;
}
.property-listing p {
  margin: 0 !important;
}

.property-listing .image {
}
.property-listing .image img {
  display: block;
  width: 100% !important;
  height: auto !important;
  max-width: 100% !important;
}

.property-listing .gallery {
  overflow: hidden;
  margin-right: -1%;
  height: auto;
}
.property-listing .gallery .grid_8 {
  width: 100%;
  max-height: 80px;
}
.property-listing .gallery li {
  margin: 0 !important;
  padding: 0 !important;
  width: 10%;
  height: 40px;
  overflow: hidden;
}
.property-listing .gallery a {
  display: block;
  margin: 0 !important;
  border: none !important;
  padding: 5px 5px 0 0 !important;
}
.property-listing .gallery img {
  display: block;
  margin: 0 !important;
  border: none !important;
  padding: 0 !important;
  width: 100% !important;
  height: 100% !important;
}

.property-listing .price,
.property-listing .mls {
  float: left;
  margin: .5em 1em 1em 0 !important;
  font-weight: 600;
}
.property-listing .address {
  clear: both;
  margin: .3em 0 0 0 !important;
  font-size: 1.2em;
  font-weight: 600;
}
.property-listing .features {
  margin: 0 0 .2em 0 !important;
  font-size: 13px;
}
.property-listing .features li {
  margin: 0 1em 0 0 !important;
}
.property-listing .features span {
  font-weight: 800;
}
.property-listing .amenities {
  padding: .5em 0 1em 0 !important;
  background: -webkit-gradient(linear, left top, left bottom, from(#e7e7e7), to(#e4e4e4));
  background: -moz-linear-gradient(top, #e7e7e7, #e4e4e4);
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#e7e7e7\', endColorstr=\'#e4e4e4\');
  overflow: hidden;
}
.property-listing .amenities span {
  padding: 0 .3em 0 0;
}
.property-listing .amenities span:after {
  content: ":";
}
.property-listing .desc {
  margin: .5em 0 0 0 !important;
}
.property-listing .custom_google_map {
  width: 100% !important;
}
.property-listing .actions {
  clear: both;
  float: right;
  margin: .5em 0 0 0 !important;
  overflow: hidden;
}
.property-listing .actions a {
  float: left !important;
  text-decoration: none !important;
}
.property-listing .compliance {
  clear: both;
  margin: .5em 0;
  font-size: .8em;
}
.property-listing .clearfix {
  clear: both;
}

.page-compliance {
  clear: both;
  margin: 0;
}
.page-compliance .compliance-wrapper {
  margin: .8em 0;
}
.page-compliance p {
  margin: 0 !important;
  padding: 0 !important;
  line-height: 1.1em !important;
  font-size: 10px !important;
}',


'snippet_body' => '<div class="property-listing">
  <h2 class="address">[address] [locality] [region]</h2>
  <ul class="features">
    [if attribute=\'sqft\']<li><span>[sqft]</span> sqft</li>[/if][if attribute=\'beds\']<li><span>[beds]</span> Bed(s)</li>[/if][if attribute=\'baths\']<li><span>[baths]</span> Bath(s)</li>[/if][if attribute=\'half_baths\']<li><span>[half_baths]</span> Half-Bath(s)</li>[/if]
  </ul>
  <div class="image">[image width=\'640\' height=\'480\']</div>
  <div class="gallery">[gallery]</div>
  <p class="price">[price]</p>
  <p class="mls">MLS#: [mls_id]</p>
  <h3>Description</h3>
  <div class="desc">[desc]</div>
  <div class="subsection">
    <h3>Amenities</h3>
    <div class="amenities">[amenities]</div>
  </div>
  <div class="subsection">
    <h3>Location</h3>
    <div class="location">[map]</div>
  </div>
  <div class="actions">[favorite_link_toggle]</div>
  <div class="compliance">[compliance]</div>
</div>',


'before_widget' => '<!-- Place content here that you want to appear before the listing. May include shortcodes -->
<div class="tpl-clmbs-property-listing">',


'after_widget' => '<!-- Place content here that you want to appear before the listing. May include shortcodes -->
  <div class=\'page-compliance\'>[compliance]</div>
</div>',

);
