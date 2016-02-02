<?php
// Register Testimonials
add_action('init', 'testimonial_register');

function testimonial_register() {
	$labels = array(
			'name' => _x('Testimonials', 'post type general name'),
			'singular_name' => _x('Testimonial Item', 'post type singular name'),
			'add_new' => _x('Add New', 'testimonial'),
			'add_new_item' => __('Add New Testimonial'),
			'edit_item' => __('Edit Testimonial'),
			'new_item' => __('New Testimonial'),
			'view_item' => __('View Testimonial'),
			'search_items' => __('Search Testimonials'),
			'not_found' =>  __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
	);

	$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','thumbnail')
	);

	register_post_type( 'testimonial' , $args );
}

add_action( 'add_meta_boxes', 'pls_cpt_testimonial_add_meta' );
function pls_cpt_testimonial_add_meta() {
	add_meta_box( 'my-meta-box-id', 'Testimonial Information', 'pls_cpt_testimonial_meta_box', 'testimonial', 'side', 'high' );
}

function pls_cpt_testimonial_meta_box( $post ) {
	
	$testimonial_support = get_theme_support( 'pls-post-type-testimonial' );
	
	if( empty( $testimonial_support ) || ! is_array( $testimonial_support ) ) return;
	
	$values = get_post_custom( $post->ID );
	$testimonial_featured = isset( $values['testimonial_featured'] ) ? esc_attr( $values['testimonial_featured'][0] ) : '';
	$testimonial_giver = isset( $values['testimonial_giver'] ) ? esc_attr( $values['testimonial_giver'][0] ) : '';
	$testimonial_from = isset( $values['testimonial_from'] ) ? esc_attr( $values['testimonial_from'][0] ) : '';
	
	do_action( 'pls_cpt_testimonial_before_meta' );
	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
	?>
	
	<?php if( ! empty( $testimonial_support[0]['testimonial_featured'] ) ) { ?>
	<p>
		<input type="checkbox" id="testimonial_featured" name="testimonial_featured" <?php checked( $testimonial_featured, 'on' ); ?> />  
		<label for="testimonial_featured">Should this testimonial be featured?</label>
	</p>
	<?php } ?>
	<?php if( ! empty( $testimonial_support[0]['testimonial_giver'] ) ) { ?>
	<p>
		<label for="testimonial_giver">Testimonial Giver</label>
		<input type="text" name="testimonial_giver" id="testimonial_giver" value="<?php echo $testimonial_giver; ?>" style="width:250px;" />
	</p>
	<?php } ?>
	<?php if( ! empty( $testimonial_support[0]['testimonial_from'] ) ) { ?>
	<p>
		<label for="testimonial_from">Testimonial Giver from? </label>
		<input type="text" name="testimonial_from" id="testimonial_from" value="<?php echo $testimonial_from; ?>" style="width:250px;" />
	</p>
	<?php } ?>
	<?php
	
	do_action( 'pls_cpt_testimonial_after_meta' );
}

add_action( 'save_post', 'pls_testimonials_save_meta_boxes' );
function pls_testimonials_save_meta_boxes( $post_id ) {
	// now we can actually save the data
	$allowed = array(
			'a' => array( // on allow a tags
					'href' => array() // and those anchords can only have href attribute
			)
	);

	$testimonial_support = get_theme_support( 'pls-post-type-testimonial' );
	
	if( ! empty( $testimonial_support[0]['testimonial_featured'] ) ) {
		// This is purely my personal preference for saving checkboxes
		$testimonial_featured = ( isset( $_POST['testimonial_featured'] ) && $_POST['testimonial_featured'] ) ? 'on' : 'off';
		update_post_meta( $post_id, 'testimonial_featured', $testimonial_featured );
	}
	
	if( ! empty( $testimonial_support[0]['testimonial_giver'] ) ) {
		if( isset( $_POST['testimonial_giver'] ) ) {
			update_post_meta( $post_id, 'testimonial_giver', wp_kses( $_POST['testimonial_giver'], $allowed ) );
		}
	}

	if( ! empty( $testimonial_support[0]['testimonial_from'] ) ) {
		if( isset( $_POST['testimonial_from'] ) ) {
			update_post_meta( $post_id, 'testimonial_from', wp_kses( $_POST['testimonial_from'], $allowed ) );
		}
	}
	
	do_action( 'pls_cpt_testimonial_save_action' );

}