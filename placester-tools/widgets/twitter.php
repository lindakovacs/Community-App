<?php

class PLS_Widget_Twitter extends WP_Widget {

  public function __construct() {
    $widget_ops = array(
      'classname' => 'pls-twitter-widget',
      'description' => 'Change the title of the "Twitter" widget.'
    );

    /* Widget control settings. */
    $control_ops = array( 'width' => 200, 'height' => 350 );

    /* Create the widget. */
    $this->WP_Widget( 'PLS_Widget_Twitter', 'Placester: Twitter Widget', $widget_ops, $control_ops );
  }

  public function widget( $args, $instance ) {
    // Widget output
    
    /** Define the default argument array. */
    $defaults = array(
      'before_widget' => '<section class="twitter-widget widget">',
      'after_widget' => '</section>',
      'title' => '',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
    );

    /** Merge the arguments with the defaults. */
    $args = wp_parse_args( $args, $defaults );

    extract($args, EXTR_SKIP);
    
    $title = empty($instance['title']) ? ' ' : apply_filters('title', $instance['title']);
    $twitter_username = empty($instance['username']) ? ' ' : apply_filters('username', $instance['username']);
    $count = empty($instance['count']) ? ' ' : apply_filters('count', $instance['count']);

    if ( !empty($twitter_username) ) {
      $username = $twitter_username;
    } else {
      $username = @pls_get_option('pls-twitter-username');
    }
    ?>

      <?php echo $before_widget; ?>
      
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        
        <?php echo $before_title . $title . $after_title; ?>
        
        <p class="twitter-handle"><a href="https://twitter.com/<?php echo $username; ?>">@<?php echo $username ?></a></p>
        
        <section id="twitter-sidebar-widget">
          <?php echo get_twitter_feed($username, $count); ?>
        </section>
    
      <?php echo $after_widget; ?>
    
    <?php

  }

  public function update( $new_instance, $old_instance ) {
    // Save widget options
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['username'] = strip_tags($new_instance['username']);
    $instance['count'] = strip_tags($new_instance['count']);
    
    return $instance;
  }

  public function form( $instance ) {
    // Output admin widget options form
    $instance = wp_parse_args( (array) $instance, array( 'title' => 'Twitter', 'username' => 'Placester', 'count' => '4' ) );
    $title = strip_tags($instance['title']);
    $username = strip_tags($instance['username']);
    $count = strip_tags($instance['count']);
    ?>
      <p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
      <p><label for="<?php echo $this->get_field_id('username'); ?>">Twitter Username: <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo esc_attr($username); ?>" /></label></p>
      <p><label for="<?php echo $this->get_field_id('count'); ?>">Number of recent Tweets to show: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label></p>
    <?php
  }
}



// Widget output
function get_twitter_feed($username, $count) {

  $cache = new PLS_Cache('fb_feed');
  if ($result = $cache->get($username)) {
    return $result;
  }
  
  if (empty($username)) {
    return;
  }
  
  $name_count = strlen($username);
  $twitter_feed = 'https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=' . $username;
  $feed = fetch_feed($twitter_feed);

  if (!is_wp_error( $feed ) ) { // Checks that the object is created correctly 
  
    if ($feed->get_items() != null) {
      $items = $feed->get_items();
    } else {
      break;
    }

    $twitter_feed_html = "";

    foreach ( $feed->get_items() as $key => $item ) {

      // Get title value hash
      $full_title = $item->get_title();

      $date = $item->get_date('Y-m-d');
      $date_object = new DateTime($date);

      $month = $date_object->format('M');
      $day = $date_object->format('j');
      $year = $date_object->format('Y');
      // $hour = date('g', $date);
      // $minute = date('i', $date);
      // $am_pm = date('a', $date);

      $date_string = '<p class="tweet-date"><span class="tweet-month">'.$month.'</span> <span class="tweet-day">'.$day.'</span> <span class="tweet-day">'.$year.'</span></p>';
        // <p class="tweet-time"><span class="tweet-hour">'.$hour.'</span>:<span class="tweet-minute">'.$minute.'</span> <span class="tweet-am-pm">'.$am_pm.'</span></p>';

      // Remove "gvinter" from the twitter feed
      $title = substr($full_title, $name_count + 2);

      // The Regular Expression filter
      $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

      // Check if there is a url in the text
      if(preg_match($reg_exUrl, $title, $url)) {
        // make the urls hyper links
        $filtered_title = preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow" target="_blank">'.$url[0].'</a>', $title);
      } else {
        // if no urls in the text just return the text
        $filtered_title = $title;
      }

      $twitter_feed_html = $twitter_feed_html . '<div class="single-tweet"><p class="tweet-content">' . $filtered_title . '</p>' . '<div class="tweet-date-wrapper">' . $date_string . '</div></div>';

      if( $key >= ($count - 1) ) { break; }

    } //endforeach

    return $twitter_feed_html;
  }
}
