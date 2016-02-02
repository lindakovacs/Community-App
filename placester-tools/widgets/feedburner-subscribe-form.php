<?php

class PLS_Widget_Feedburner_Widget extends WP_Widget {

  public function __construct() {
    $widget_ops = array( 
      'classname' => 'pls-feedburner-widget',
      'description' => 'Set your Feedburner URI in theme options.'
    );

    /* Widget control settings. */
    $control_ops = array( 'width' => 200, 'height' => 290 );

    /* Create the widget. */
    $this->WP_Widget( 'PLS_Widget_Feedburner_Widget', 'Placester: Feedburner Subscription', $widget_ops, $control_ops );
  }

  public function widget( $args, $instance ) {
    // Widget output

    /** Define the default argument array. */
    $defaults = array(
      'before_widget' => '<section id="pls_feedburner_widget" class="pls_feedburner_widget_wrapper widget">',
      'after_widget' => '</section>',
      'title' => '',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
    );

    /** Merge the arguments with the defaults. */
    $args = wp_parse_args( $args, $defaults );

    extract($args, EXTR_SKIP);
    
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    $instructions = empty($instance['instructions']) ? ' ' : apply_filters('instructions', $instance['instructions']);
    $email_placeholder = empty($instance['email_placeholder']) ? ' ' : apply_filters('email_placeholder', $instance['email_placeholder']);

    $feedburner_url = pls_get_option('pls-feedburner-uri');
    $feedburner_id = str_replace("http://feeds.feedburner.com/", "", $feedburner_url);
    ?>
   
     <?php echo $before_widget; ?>

      <?php echo $before_title . $title . $after_title; ?>

      <form action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $feedburner_id; ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">
        <p><?php echo $instructions; ?></p>

        <input type="email" name="email" placeholder="<?php echo $email_placeholder; ?>" />

        <input type="hidden" value="<?php echo $feedburner_id; ?>" name="uri"/>
        <input type="hidden" name="loc" value="en_US"/>
        <input type="submit" value="Subscribe" class="button-primary" />

      </form>

      <?php echo $after_widget; ?>

<?php
  }

  public function update( $new_instance, $old_instance ) {
    // Save widget options
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['instructions'] = strip_tags($new_instance['instructions']);
    $instance['email_placeholder'] = strip_tags($new_instance['email_placeholder']);
    return $instance;
  }

  public function form( $instance ) {
    // Output admin widget options form
    $instance = wp_parse_args( (array) $instance, array( 'title' => 'Newsletter Signup', 'instructions' => 'Sign up for our newsletter.', 'email_placeholder' => 'Email Address' ) );
    $title = strip_tags($instance['title']);
    $instructions = strip_tags($instance['instructions']);
    $email_placeholder = strip_tags($instance['email_placeholder']);
    ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title' ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
      <p><label for="<?php echo $this->get_field_id('instructions'); ?>"><?php echo "Form instructions" ?>: <input class="widefat" id="<?php echo $this->get_field_id('instructions'); ?>" name="<?php echo $this->get_field_name('instructions'); ?>" type="text" value="<?php echo esc_attr($instructions); ?>" /></label></p>
      <p><label for="<?php echo $this->get_field_id('email_placeholder'); ?>"><?php echo "Email input placeholder" ?>: <input class="widefat" id="<?php echo $this->get_field_id('email_placeholder'); ?>" name="<?php echo $this->get_field_name('email_placeholder'); ?>" type="text" value="<?php echo esc_attr($email_placeholder); ?>" /></label></p>
    <?php
  }
}