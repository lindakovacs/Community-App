<?php

class PLS_Widget_Testimonials extends WP_Widget {

	/**
	 * Widget Constuctor
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'pls_testimonials_widget', 'description' => 'Featured Testimonials Widget' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 350 );

		/* Create the widget. */
		$this->WP_Widget( 'PLS_Widget_Testimonials', 'Placester: Testimonials Widget', $widget_ops, $control_ops );
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
    $number_of_posts = isset($instance['number_of_posts']) ? $instance['number_of_posts'] : 5;
    $widget_id = isset($instance['widget_id']) ? $instance['widget_id'] : 1;

    /** Define the default argument array. */
    $defaults = array(
      'before_widget' => '<section class="testimonials-widget widget">',
      'after_widget' => '</section>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
    );

    /** Merge the arguments with the defaults. */
    $args = wp_parse_args( $args, $defaults );

    extract($args, EXTR_SKIP);


    if ( !post_type_exists('testimonial')) {
      return false;
    }

    // Testimonials start
    $args = array( 'numberposts' => $number_of_posts, 'post_type' => 'testimonial' );
    $testimonials = get_posts( $args );

    $widget_body = '<section class="featured-testimonials-widget">';

      foreach( $testimonials as $testimonial ) : setup_postdata($testimonial);
      
        $post_item = array(
          'url' => post_permalink($testimonial->ID),
          'content' => $testimonial->post_content,
          'title' => $testimonial->post_title,
          'image' => '',
          'id' => $testimonial->ID,
          'testimonial_giver' => get_post_meta( $testimonial->ID, 'testimonial_giver', true),
          'testimonial_giver_from' => get_post_meta( $testimonial->ID, 'testimonial_giver_from', true)
        );

        if (has_post_thumbnail( $testimonial->ID ) ) {
          $post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $testimonial->ID ) );
          $post_item['image'] = '<img src="'.$post_image[0].'" style="width:100%;" />';
        }
        
        ob_start();
        ?>
        <article class="featured-testimonial" itemscope itemtype="http://schema.org/Review">
          <a href="<?php echo $post_item['url']; ?>" itemprop="url"><h4 itemprop="name"><?php echo $post_item['title']; ?></h4></a>
          <blockquote itemprop="review">
            <p><?php echo PLS_Format::shorten_text($post_item['content'], 130); ?></p>
          </blockquote>
          <?php
          if( $post_item['testimonial_giver'] )
            echo '<p><cite>' . $post_item['testimonial_giver'] . '</cite></p>';
          ?>
          <p><a href="<?php echo $post_item['url']; ?>" itemprop="url">Read More</a></p>
        </article>
        <?php 
        
        $single_testimonial = ob_get_clean();
        
        /** Wrap the post in an article element and filter its contents. */
        $single_testimonial = apply_filters( 'pls_widget_testimonials_post_inner', $single_testimonial, $post_item, $instance, $widget_id );

        /** Append the filtered post to the post list. */
        $widget_body .= apply_filters( 'pls_widget_testiomonials_post_outer', $single_testimonial, $post_item, $instance, $widget_id );
        
        
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
			<label for="<?php echo $this->get_field_id('number_of_posts'); ?>">Number of Testimonials to display:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" type="text" value="<?php echo $number_of_posts; ?>" />
		</p>

		<?php
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("PLS_Widget_Testimonials");' ) );
