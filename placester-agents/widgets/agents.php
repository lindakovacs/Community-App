<?php

class PLS_Widget_Agents extends WP_Widget {

	/**
	 * Widget Constuctor
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'pls_agents_widget', 'description' => 'Agents List Widget' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 350 );

		/* Create the widget. */
		$this->WP_Widget( 'PLS_Widget_Agents', 'Placester: Agents List', $widget_ops, $control_ops );
	}

	/**
	 * Widget Output
	 *
	 * @param $args (array)
	 * @param $instance (array) Widget values.
	 */
   public function widget( $args, $instance ) {
     // widget output
     extract($args);

     $title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
     $number_of_posts = $instance['number_of_posts'];

     if ( !post_type_exists('agent')) {
       return false;
     }

     // Testimonials start
     $args = array( 'numberposts' => $number_of_posts, 'post_type' => 'agent' );
     $agents = get_posts( $args );

     $widget_body = '<section class="featured-agents-widget-inner">';

       foreach( $agents as $agent ) : setup_postdata($agent);

         $post_item = array(
           'url' => post_permalink($agent->ID),
           'content' => $agent->post_content,
           'title' => $agent->post_title,
           'image' => '',
           'id' => $agent->ID,
           'agent_title' => get_post_meta( $agent->ID, 'agent_title', true),
           'agent_phone' => get_post_meta( $agent->ID, 'agent_phone', true),
           'agent_email' => get_post_meta( $agent->ID, 'agent_email', true),
           'agent_ophone' => get_post_meta( $agent->ID, 'agent_ophone', true)
         );

         if (has_post_thumbnail( $agent->ID ) ) {
           $post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $agent->ID ) );
           $post_item['image'] = '<img src="'.$post_image[0].'" style="width:100%;" />';
         }

         ob_start();
         ?>
         <article class="featured-agent" itemscope itemtype="http://schema.org/Person">
           <div class="agent-image">
             <?php echo $post_item['image']; ?>
           </div>
           <h4 itemprop="name"><a href="<?php echo $post_item['url']; ?>" itemprop="url"><?php echo $post_item['title']; ?></a></h4>
           <p class="agent-phone" itemprop="phone"><?php echo $post_item['agent_phone']; ?></p>
           <p class="agent-email" itemprop="email"><a href="mailto:<?php echo $post_item['agent_email']; ?>"><?php echo $post_item['agent_email']; ?></a></p>
           <p class="agent-ophone"><?php echo $post_item['agent_ophone']; ?></p>
         </article>
         <?php 

         $single_agent = ob_get_clean();
         
         if (!isset($widget_id)) { $widget_id = 1; }
         
         /** Wrap the post in an article element and filter its contents. */
         $single_agent = apply_filters( 'pls_widget_agents_post_inner', $single_agent, $post_item, $instance, $widget_id );
         

         /** Append the filtered post to the post list. */
         $widget_body .= apply_filters( 'pls_widget_agents_post_outer', $single_agent, $post_item, $instance, $widget_id );


       endforeach;

     $widget_body .= '</section>';

     // Display Widget
     echo $before_widget;
       if( $instance['title'] ) {
         echo $before_title . $title . $after_title;
       }
       echo $widget_body;
     echo $after_widget;
   }

	/**
	 * Update Widget
	 *
	 * @param $new_instance (array) New widget values.
	 * @param $old_instance (array) Old widget values.
	 *
	 * @return (array) New values.
	 */
	public function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = $old_instance;

		//Strip tags from title to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		$instance['number_of_posts'] = $new_instance['number_of_posts'];

		return $instance;
	}

	/**
	 * Widget Options Form
	 *
	 * @param $instance (array) Widget values.
	 */
	public function form( $instance ) {

		// Defaults
		$instance = wp_parse_args( (array) $instance, array( 
											'title' => '',
											'number_of_posts' => '1'
		) );

		// Values
		$title = esc_attr( $instance['title'] );
		$number_of_posts = $instance['number_of_posts'];
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number_of_posts'); ?>">Number of Agents to display:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" type="text" value="<?php echo $number_of_posts; ?>" />
		</p>

		<?php
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("PLS_Widget_Agents");' ) );
