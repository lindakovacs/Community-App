<?php

add_action('init', array('PDX_Agent_CPT', 'register_post_type'));

class PDX_Agent_CPT {

	static public function register_post_type() {
		$labels = array(
				'name' => _x('Agents', 'post type general name'),
				'singular_name' => _x('Agent', 'post type singular name'),
				'add_new_item' => __('Add New Agent'),
				'edit_item' => __('Edit Agent'),
				'new_item' => __('New Agent'),
				'view_item' => __('View Agent'),
				'search_items' => __('Search Agents'),
				'not_found' =>  __('No agents found'),
				'not_found_in_trash' => __('Nothing agents found in Trash'),
				'parent_item_colon' => ''
		);

		$args = array(
				'labels' => $labels,
				'description' => 'Biographical information about a real estate agent.',

				'public' => true,
				'menu_position' => 20,

				'capability_type' => 'page',
				'supports' => array('title','excerpt','thumbnail')
		);

		register_post_type( 'agent' , $args );
		add_action( 'add_meta_boxes_agent', array(__CLASS__, 'add_meta_boxes'));
		add_action( 'save_post', array(__CLASS__, 'save_post'));
	}

	static public function add_meta_boxes() {
		add_meta_box( 'agent-information-meta-box', 'Agent Information', array(__CLASS__, 'render_meta_box'), 'agent', 'normal', 'high' );
	}

	static public function render_meta_box( $post ) {

		$values = get_post_custom( $post->ID );
		$agent_phone = isset( $values['agent_phone'] ) ? esc_attr( $values['agent_phone'][0] ) : '';
		$agent_location = isset( $values['agent_location'] ) ? esc_attr( $values['agent_location'][0] ) : '';
		$agent_mobile = isset( $values['agent_mobile'] ) ? esc_attr( $values['agent_mobile'][0] ) : '';
		$agent_email = isset( $values['agent_email'] ) ? esc_attr( $values['agent_email'][0] ) : '';
		$agent_title = isset( $values['agent_title'] ) ? esc_attr( $values['agent_title'][0] ) : '';
		$agent_linkedin = isset( $values['agent_linkedin'] ) ? esc_attr( $values['agent_linkedin'][0] ) : '';
		$agent_youtube = isset( $values['agent_youtube'] ) ? esc_attr( $values['agent_youtube'][0] ) : '';
		$agent_facebook = isset( $values['agent_facebook'] ) ? esc_attr( $values['agent_facebook'][0] ) : '';
		$agent_twitter = isset( $values['agent_twitter'] ) ? esc_attr( $values['agent_twitter'][0] ) : '';
		$agent_googleplus = isset( $values['agent_googleplus'] ) ? esc_attr( $values['agent_googleplus'][0] ) : '';
		$agent_pinterest = isset( $values['agent_pinterest'] ) ? esc_attr( $values['agent_pinterest'][0] ) : '';
		$agent_instagram = isset( $values['agent_instagram'] ) ? esc_attr( $values['agent_instagram'][0] ) : '';
		$agent_flickr = isset( $values['agent_flickr'] ) ? esc_attr( $values['agent_flickr'][0] ) : '';
		$agent_featured = isset( $values['agent_featured'] ) ? esc_attr( $values['agent_featured'][0] ) : '';

		wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );

		?>
		<p>
			<label for="agent_phone">Agent Office Location</label>
			<input type="text" name="agent_location" id="agent_location" value="<?php echo $agent_location; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_phone">Agent Office Phone</label>
			<input type="text" name="agent_phone" id="agent_phone" value="<?php echo $agent_phone; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_mobile">Agent Mobile</label>
			<input type="text" name="agent_mobile" id="agent_mobile" value="<?php echo $agent_mobile; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_email">Agent Email</label>
			<input type="text" name="agent_email" id="agent_email" value="<?php echo $agent_email; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_title">Agent Title</label>
			<input type="text" name="agent_title" id="agent_title" value="<?php echo $agent_title; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_linkedin">Agent LinkedIn</label>
			<input type="text" name="agent_linkedin" id="agent_linkedin" value="<?php echo $agent_linkedin; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_youtube">Agent YouTube</label>
			<input type="text" name="agent_youtube" id="agent_youtube" value="<?php echo $agent_youtube; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_facebook">Agent Facebook</label>
			<input type="text" name="agent_facebook" id="agent_facebook" value="<?php echo $agent_facebook; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_twitter">Agent Twitter Username</label>
			<input type="text" name="agent_twitter" id="agent_twitter" value="<?php echo $agent_twitter; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_googleplus">Agent Google+</label>
			<input type="text" name="agent_googleplus" id="agent_googleplus" value="<?php echo $agent_googleplus; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_pinterest">Agent Pinterest</label>
			<input type="text" name="agent_pinterest" id="agent_pinterest" value="<?php echo $agent_pinterest; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_instagram">Agent Instagram</label>
			<input type="text" name="agent_instagram" id="agent_instagram" value="<?php echo $agent_instagram; ?>" style="width:250px;" />
		</p>
		<p>
			<label for="agent_flickr">Agent Flickr</label>
			<input type="text" name="agent_flickr" id="agent_flickr" value="<?php echo $agent_flickr; ?>" style="width:250px;" />
		</p>
		<p>
			<input type="checkbox" id="agent_featured" name="agent_featured" <?php checked( $agent_featured, 'on' ); ?> />
			<label for="agent_featured">Should this agent be featured?</label>
		</p>
		<?php
	}

	static public function save_post( $post_id ) {

		if( isset( $_POST['agent_location'] ) ) {
			update_post_meta( $post_id, 'agent_location', wp_kses( $_POST['agent_location'], $allowed ) );
		}
		if( isset( $_POST['agent_phone'] ) ) {
			update_post_meta( $post_id, 'agent_phone', wp_kses( $_POST['agent_phone'], $allowed ) );
		}
		if( isset( $_POST['agent_mobile'] ) ) {
			update_post_meta( $post_id, 'agent_mobile', wp_kses( $_POST['agent_mobile'], $allowed ) );
		}
		if( isset( $_POST['agent_email'] ) ) {
			update_post_meta( $post_id, 'agent_email', wp_kses( $_POST['agent_email'], $allowed ) );
		}
		if( isset( $_POST['agent_title'] ) ) {
			update_post_meta( $post_id, 'agent_title', wp_kses( $_POST['agent_title'], $allowed ) );
		}
		if( isset( $_POST['agent_linkedin'] ) ) {
			update_post_meta( $post_id, 'agent_linkedin', esc_url( $_POST['agent_linkedin'] ) );
		}
		if( isset( $_POST['agent_youtube'] ) ) {
			update_post_meta( $post_id, 'agent_youtube', esc_url( $_POST['agent_youtube'] ) );
		}
		if( isset( $_POST['agent_facebook'] ) ) {
			update_post_meta( $post_id, 'agent_facebook', esc_url( $_POST['agent_facebook'] ) );
		}
		if( isset( $_POST['agent_twitter'] ) ) {
			update_post_meta( $post_id, 'agent_twitter', esc_url( $_POST['agent_twitter'] ) );
		}
		if( isset( $_POST['agent_googleplus'] ) ) {
			update_post_meta( $post_id, 'agent_googleplus', esc_url( $_POST['agent_googleplus'] ) );
		}
		if( isset( $_POST['agent_pinterest'] ) ) {
			update_post_meta( $post_id, 'agent_pinterest', esc_url( $_POST['agent_pinterest'] ) );
		}
		if( isset( $_POST['agent_instagram'] ) ) {
			update_post_meta( $post_id, 'agent_instagram', esc_url( $_POST['agent_instagram'] ) );
		}
		if( isset( $_POST['agent_flickr'] ) ) {
			update_post_meta( $post_id, 'agent_flickr', esc_url( $_POST['agent_flickr'] ) );
		}

		$agent_featured = ( isset( $_POST['agent_featured'] ) && $_POST['agent_featured'] ) ? 'on' : 'off';
		update_post_meta( $post_id, 'agent_featured', $agent_featured );
	}
}
