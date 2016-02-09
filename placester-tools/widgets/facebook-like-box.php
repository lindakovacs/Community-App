<?php

class PLS_Widget_Facebook_Like_Box extends WP_Widget {

  public function __construct() {
    $widget_ops = array(
      'classname' => 'pls-facebook-like-box-widget',
      'description' => 'Change the title of the "Facebook Like Box" widget.'
    );

    /* Widget control settings. */
    $control_ops = array( 'width' => 200, 'height' => 350 );

    /* Create the widget. */
    $this->WP_Widget( 'PLS_Widget_Facebook_Like_Box', 'Placester: Facebook Like Box Widget', $widget_ops, $control_ops );
  }

  public function widget( $args, $instance ) {
    // Widget output
    extract($args);
    
    $title = empty($instance['title']) ? ' ' : apply_filters('title', $instance['title']);
    $fb_page_url = empty($instance['fb_page_url']) ? ' ' : apply_filters('fb_page_url', $instance['fb_page_url']);
    $width = empty($instance['width']) ? ' ' : apply_filters('width', $instance['width']);
    $height = empty($instance['height']) ? ' ' : apply_filters('height', $instance['height']);
    
    $faces = isset($instance['faces']) && $instance['faces'] == "false" ? false : true;
    $stream = isset($instance['stream']) && $instance['stream'] == "false" ? false : true;
    $border = isset($instance['border']) && $instance['border'] == "false" ? false : true;
    $header = isset($instance['header']) && $instance['header'] == "false" ? false : true;

    if ( !empty($fb_page_url) ) {
      $page_id = $fb_page_url;
    } else {
      $page_id = @pls_get_option('pls-facebook-page-url');
    }

    /** Define the default argument array. */
    $defaults = array(
      'before_widget' => '<section class="facebook-like-box-widget widget">',
      'after_widget' => '</section>',
      'title' => '',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
    );

    /** Merge the arguments with the defaults. */
    $args = wp_parse_args( $args, $defaults );

    extract($args, EXTR_SKIP);

    // header: true
    // show_border: true
    // show_faces: true
    // colorscheme: light
    ?>

      <?php echo $before_widget; ?>

        <?php echo $before_title . $title . $after_title; ?>

        <section class="facebook-like-box-sidebar-widget">
          <iframe src="//www.facebook.com/plugins/likebox.php?href=<?php echo $fb_page_url; ?>&amp;width=<?php echo $width; ?>&amp;height=<?php echo $height; ?>&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;show_border=true&amp;header=true&amp;appId=263914027073402" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:<?php echo $width; ?>px; height:<?php echo $height; ?>px;" allowTransparency="true"></iframe>
        </section>

      <?php echo $after_widget; ?>

    <?php

  }

  public function update( $new_instance, $old_instance ) {
    // Save widget options
    $instance = $old_instance;

    $instance['title'] = strip_tags($new_instance['title']);
    $instance['fb_page_url'] = strip_tags($new_instance['fb_page_url']);
    $instance['width'] = strip_tags($new_instance['width']);
    $instance['height'] = strip_tags($new_instance['height']);
    
    return $instance;
  }

  public function form( $instance ) {
    // Output admin widget options form
    
    $defaults = array( 
      'title' => 'Facebook Like Box',
      'fb_page_url' => 'https://www.facebook.com/Placester',
      'width' => '300',
      'height' => '255'
    );
    
    $instance = wp_parse_args( (array) $instance, $defaults );

    $title = strip_tags($instance['title']);
    $fb_page_url = strip_tags($instance['fb_page_url']);
    $width = strip_tags($instance['width']);
    $height = strip_tags($instance['height']);
    ?>

    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('fb_page_url'); ?>">Facebook Page URL: <input class="widefat" id="<?php echo $this->get_field_id('fb_page_url'); ?>" name="<?php echo $this->get_field_name('fb_page_url'); ?>" type="text" value="<?php echo esc_attr($fb_page_url); ?>" /></label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('width'); ?>">Width: <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>" /></label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('height'); ?>">Height: <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr($height); ?>" /></label>
    </p>

   <?php
  }
}