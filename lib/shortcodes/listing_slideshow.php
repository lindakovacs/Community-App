<?php
/**
 * Post type/Shortcode for displaying the slideshow
 *
 */

class PL_Listing_Slideshow_CPT extends PL_SC_Base {

	protected static $pl_post_type = 'pl_slideshow';

	protected static $shortcode = 'listing_slideshow';

	protected static $title = 'Slideshow';

	protected static $help = 
		'<p>
        You can create a slideshow for your Featured Listings by using the 
        [listing_slideshow post_id="<em>slideshowid</em>"] shortcode. 
		</p>';

	protected static $options = array(
		'pl_cpt_template'	=> array( 'type' => 'select', 'label' => 'Template', 'default' => ''),
		'width'				=> array( 'type' => 'numeric', 'label' => 'Width(px)', 'default' => 250 ),
		'height'			=> array( 'type' => 'numeric', 'label' => 'Height(px)', 'default' => 250 ),
		'widget_class'		=> array( 'type' => 'text', 'label' => 'Widget Class', 'default' => '' ),
		'animation' 		=> array( 'type' => 'select', 'label' => 'Animation', 'options' => array(
				'fade' => 'fade',
				'horizontal-slide' => 'horizontal-slide',
				'vertical-slide' => 'vertical-slide',
				'horizontal-push' => 'horizontal-push',
			),
			'default' => 'fade' ),
		'animationSpeed'	=> array( 'type' => 'numeric', 'label' => 'Animation Speed', 'default' => 4000 ),
		'timer'				=> array( 'type' => 'checkbox', 'label' => 'Timer', 'default' => true),
		'pauseOnHover'		=> array( 'type' => 'checkbox', 'label' => 'Pause on hover', 'default' => true ),
		'pl_featured_listing_meta' => array( 'type' => 'featured_listing_meta', 'default' => '' ),
	);

	protected static $subcodes = array(
		'ls_index',
		'ls_url',
		'ls_address',
		'ls_beds',
		'ls_baths',
	);

	protected static $template = array(
		'snippet_body'	=> array( 'type' => 'textarea', 'label' => 'HTML', 'default' => 'Put subcodes here to customize your slideshow...' ),
		'css'			=> array( 'type' => 'textarea', 'label' => 'CSS', 'default' => '' ),
		'before_widget'	=> array( 'type' => 'textarea', 'label' => 'Add content before the template', 'default' => '' ),
		'after_widget'	=> array( 'type' => 'textarea', 'label' => 'Add content after the template', 'default' => '' ),
	);
}

PL_Listing_Slideshow_CPT::init();
