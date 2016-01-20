<?php
/**
 * Post type/Shortcode to generate a list of featured listings
 *
 */
include_once('search_listings.php');

class PL_Featured_Listings_CPT extends PL_Search_Listing_CPT {

	protected $pl_post_type = 'featured_listings';

	protected $shortcode = 'featured_listings';

	protected $title = 'Featured Listings';

	protected $help =
		'<p>
		</p>';

	protected $options = array(
		'context'			=> array( 'type' => 'select', 'label' => 'Template', 'default' => '' ),
		'width'				=> array( 'type' => 'int', 'label' => 'Width', 'default' => 250, 'description' => '(px)' ),
		'height'			=> array( 'type' => 'int', 'label' => 'Height', 'default' => 250, 'description' => '(px)' ),
		'pl_featured_listing_meta' => array( 'type' => 'featured_listing_meta', 'default' => '' ),
	);

	// Use the same subcodes, template as search listings shortcode
	// protected $subcodes = array();
	// protected $template = array();

	protected $template = array(
		'snippet_body' => array(
			'type' => 'textarea',
			'label' => 'HTML to format each individual listing',
			'css' => 'mime_html',
			'default' => '
<!-- Listing -->
<div class="sc-listing sc-listing-620-max row-fluid">
	<div class="sc-listing-thumb span6">
		<a href="[url]">
			[image width=300]
		</a>
		<p class="sc-price">[price]</p>
	</div>
	<div class="sc-listing-info span6">
		<p>
			<a href="[url]" class="sc-listing-address">[address] [locality], [region]</a>
		</p>
		<p class="sc-basic-details">
			<span class="sc-beds hidden-phone">Beds: <strong>[beds]</strong></span>
			<span class="sc-baths hidden-phone">Baths: <strong>[baths]</strong></span>
			<span class="sc-mls hidden-phone">MLS #: <strong>[mls_id]</strong></span>
		</p>
	</div>
</div>
',
			'description' => 'You can use any valid HTML in this field to format the template tags.' 
		),

		'css' => array(
			'type' => 'textarea',
			'label' => 'CSS',
			'css' => 'mime_css',
			'default' => '
.clearfix { *zoom: 1; }
.clearfix:before { display: table; line-height: 0; content: ""; }
.clearfix:after { display: table; line-height: 0; content: ""; clear: both; }

[class*="span"] { float: left; min-height: 1px; margin-left: 20px; }

.span12 { width: 940px; }

.span11 { width: 860px; }

.span10 { width: 780px; }

.span9 { width: 700px; }

.span8 { width: 620px; }

.span7 { width: 540px; }

.span6 { width: 460px; }

.span5 { width: 380px; }

.span4 { width: 300px; }

.span3 { width: 220px; }

.span2 { width: 140px; }

.span1 { width: 60px; }

.row-fluid { width: 100%; *zoom: 1; }
.row-fluid:before { display: table; line-height: 0; content: ""; }
.row-fluid:after { display: table; line-height: 0; content: ""; clear: both; }
.row-fluid [class*="span"] { display: block; float: left; width: 100%; min-height: 30px; margin-left: 2.12766%; *margin-left: 2.07447%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }
.row-fluid [class*="span"]:first-child { margin-left: 0; }
.row-fluid .controls-row [class*="span"] + [class*="span"] { margin-left: 2.12766%; }
.row-fluid .span12 { width: 100%; *width: 99.94681%; }
.row-fluid .span11 { width: 91.48936%; *width: 91.43617%; }
.row-fluid .span10 { width: 82.97872%; *width: 82.92553%; }
.row-fluid .span9 { width: 74.46809%; *width: 74.41489%; }
.row-fluid .span8 { width: 65.95745%; *width: 65.90426%; }
.row-fluid .span7 { width: 57.44681%; *width: 57.39362%; }
.row-fluid .span6 { width: 48.93617%; *width: 48.88298%; }
.row-fluid .span5 { width: 40.42553%; *width: 40.37234%; }
.row-fluid .span4 { width: 31.91489%; *width: 31.8617%; }
.row-fluid .span3 { width: 23.40426%; *width: 23.35106%; }
.row-fluid .span2 { width: 14.89362%; *width: 14.84043%; }
.row-fluid .span1 { width: 6.38298%; *width: 6.32979%; }

[class*="span"].hide, .row-fluid [class*="span"].hide { display: none; }

[class*="span"].pull-right, .row-fluid [class*="span"].pull-right { float: right; }

.boot-container { margin-right: auto; margin-left: auto; *zoom: 1; }
.boot-container:before { display: table; line-height: 0; content: ""; }
.boot-container:after { display: table; line-height: 0; content: ""; clear: both; }

.boot-container-fluid { padding-right: 20px; padding-left: 20px; *zoom: 1; }
.boot-container-fluid:before { display: table; line-height: 0; content: ""; }
.boot-container-fluid:after { display: table; line-height: 0; content: ""; clear: both; }

/* SC Listing Styles */
.sc-listing { font-family: Arial; margin-bottom: 20px; }
.sc-listing .sc-listing-thumb img { float: left; }
.sc-listing .sc-listing-thumb .sc-price { color: white; text-decoration: none; font-size: 0.9em; padding: 10px 15px; margin: -36px 0 0 0; float: left; background: black; background: rgba(0, 0, 0, 0.8); }
.sc-listing .sc-listing-info { float: left; }
.sc-listing .sc-listing-info a.sc-listing-address { color: #4b4b4b; text-decoration: none; font-size: 1.1em; }
.sc-listing .sc-listing-info .sc-basic-details { font-size: 0.8em; color: #b7b7b7; float: left; width: 100%; margin-top: 0; }
.sc-listing .sc-listing-info .sc-basic-details span { margin-right: 10px; }

/* Mobile Boot Container */
@media (min-width: 768px) and (max-width: 979px) { .boot-container { width: 100%; }
  .sc-listing-third { width: 47%; max-width: 500px !important; } }
@media (max-width: 767px) { .boot-container { width: 100%; }
  .boot-container .sc-listing { width: 47%; float: left; margin-right: 1%; }
  .boot-container .sc-listing .sc-listing-thumb img { width: 100%; }
  .boot-container .span6 { width: 100% !important; margin-left: 0 !important; } }
@media (max-width: 420px) { .boot-container .sc-listing { clear: both; width: 98%; float: left; margin-right: 1%; }
  .boot-container .sc-listing .sc-listing-thumb img { width: 100%; }
  .boot-container .span6 { width: 100% !important; margin-left: 0 !important; } }
/* Mobile */
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

/* Print */
@media print { .visible-print { display: inherit !important; }
  .hidden-print { display: none !important; } }
			',
			'description' => 'You can use any valid CSS in this field to customize the listings, which will also inherit the CSS from the theme.' 
		),

		'before_widget'	=> array(
			'type' => 'textarea',
			'label' => 'Add content before the listings',
			'css' => 'mime_html',
			'default' => '<div class="non-row-wrapper">',
			'description' => 'You can use any valid HTML in this field and it will appear before the listings. For example, you can wrap the whole list with a <div> element to apply borders, etc, by placing the opening <div> tag in this field and the closing </div> tag in the following field.'
		),

		'after_widget' => array(
			'type' => 'textarea',
			'label' => 'Add content after the listings',
			'css' => 'mime_html',
			'default' => '</div>',
			'description' => 'You can use any valid HTML in this field and it will appear after the listings.'
		),
	);




	public static function init() {
		parent::_init(__CLASS__);
	}

	/**
	 * No additional options
	 */
	public function get_options_list($with_choices = false) {
		return $this->options;
	}

	/**
	 * No filters
	 */
	public function get_filters_list($with_choices = false) {
		return array();
	}
}

PL_Featured_Listings_CPT::init();
