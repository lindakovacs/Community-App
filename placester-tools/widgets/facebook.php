<?php

class PLS_Widget_Facebook extends WP_Widget {

	public function __construct() {
	$widget_ops = array(
		'classname' => 'pls-facebook-widget',
		'description' => 'Change the title of the "Facebook" widget.'
	);

	/* Widget control settings. */
	$control_ops = array( 'width' => 200, 'height' => 350 );

	/* Create the widget. */
	$this->WP_Widget( 'PLS_Widget_Facebook', 'Placester: Facebook Widget', $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {
	// Widget output
	extract($args);
	$title = empty($instance['title']) ? ' ' : apply_filters('title', $instance['title']);
	$facebook_id = empty($instance['facebook_id']) ? ' ' : apply_filters('facebook_id', $instance['facebook_id']);
	$count = empty($instance['count']) ? ' ' : apply_filters('count', $instance['count']);

	if ( !empty($facebook_id) ) {
		$page_id = $facebook_id;
	} else {
		$page_id = @pls_get_option('pls-facebook-id');
	}

	$limit = $count;

	// get Facebook Feed
	$feed = get_facebook_feed( $page_id, $limit, $post_types = array() );

	$widget_body = '';
	
	if(isset($feed['posts']['data']))
	foreach ($feed['posts']['data'] as $post) {
		$fb_post = build_fb_post_html($post);
		
		ob_start();
		?>
		<article class="fb_post <?php echo $fb_post['type']; ?>">
			
			<p class="fb_post_message"><?php echo $fb_post['message']; ?></p>
			
			<?php if (!empty($fb_post['media'])) { ?>
			<div class="fb_post_media">

				<!-- Media -->
				<?php echo $fb_post['media']; ?>
				
				<div class="fb_media_caption_wrapper">

				<?php if (!empty($fb_post['title_caption'])) { ?>
					<!-- Caption Title -->
					<p class="fb_media_title_caption"><a href="<?php echo $fb_post['link']; ?>"><?php echo $fb_post['title_caption']; ?></a></p>
				<?php } ?>

				<?php if (!empty($fb_post['description'])) { ?>
					<!-- description -->
					<p class="fb_media_caption"><?php echo $fb_post['description']; ?></p>
				<?php } ?>
				</div>

			</div>

			<?php } ?>

			<p class="fb_post_date"><span class="fb_month"><?php echo $fb_post['month']; ?></span> <span class="fb_day"><?php echo $fb_post['day']; ?></span>, <span class="fb_year"><?php echo $fb_post['year']; ?></span></p>
		
		</article>
		<?php
		$post_item = ob_get_clean();
		
		if (!isset($widget_id)) {
		$widget_id = 1;
		}
		/** Wrap the post in an article element and filter its contents. */
		$post_item = apply_filters( 'pls_widget_facebook_post_inner', $post_item, $fb_post, $instance, $widget_id );
		
		/** Append the filtered post to the post list. */
		$widget_body .= apply_filters( 'pls_widget_facebook_post_outer', $post_item, $fb_post, $instance, $widget_id );
	}

	/** Define the default argument array. */
	$defaults = array(
		'before_widget' => '<section class="facebook-widget widget">',
		'after_widget' => '</section>',
		'title' => '',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	);

	/** Merge the arguments with the defaults. */
	$args = wp_parse_args( $args, $defaults );

	extract($args, EXTR_SKIP);
	?>

		<?php echo $before_widget; ?>

		<?php echo $before_title . $title . $after_title; ?>

		<style type="text/css" media="screen">
			.fb-play-icon {
			background: url(http://static.ak.fbcdn.net/rsrc.php/v2/yJ/x/Gj2ad6O09TZ.png) no-repeat 0 0;
			height: 26px;
			width: 35px;
			top: 50%;
			left: 50%;
			margin: -13px 0 0 -17px;
			position: absolute;
			}
		</style>

		<section class="facebook-sidebar-widget">
			<?php echo $widget_body; ?>
		</section>

		<?php echo $after_widget; ?>

	<?php

	}

	public function update( $new_instance, $old_instance ) {
	// Save widget options
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['facebook_id'] = strip_tags($new_instance['facebook_id']);
	$instance['count'] = strip_tags($new_instance['count']);
	
	return $instance;
	}

	public function form( $instance ) {
	// Output admin widget options form
	$instance = wp_parse_args( (array) $instance, array( 'title' => 'Facebook', 'facebook_id' => '320744871774', 'count' => '4' ) );
	$title = strip_tags($instance['title']);
	$facebook_id = strip_tags($instance['facebook_id']);
	$count = strip_tags($instance['count']);
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('facebook_id'); ?>">Facebook Page ID (<a href="http://findmyfacebookid.com/">Find my FB ID</a>): <input class="widefat" id="<?php echo $this->get_field_id('facebook_id'); ?>" name="<?php echo $this->get_field_name('facebook_id'); ?>" type="text" value="<?php echo esc_attr($facebook_id); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('count'); ?>">Number of recent posts to show: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label></p>
	<?php
	}
}



function get_facebook_feed ( $page_id, $limit, $post_types = array() ) {

	$cache = new PLS_Cache('fb_feed');
	if ($result = $cache->get($page_id)) {
		return $result;
	}

	// Obtain App Access Token
	$app_id = "440638186000329";
	$app_secret = "4ee7a7894c8aaf811acd3a46f23fe4ac";
	$app_token_url = "https://graph.facebook.com/oauth/access_token?"
		. "client_id=" . $app_id
		. "&client_secret=" . $app_secret 
		. "&grant_type=client_credentials";
	$result = wp_remote_get($app_token_url);
	if (!is_array($result) || !isset($result['body']) || !$result['body']) {
		return;
	}
	$response = $result['body'];

	$params = null;
	parse_str($response, $params);

	// How to get a link to the app
	//$graph_url = "https://graph.facebook.com/app?access_token=" . $params['access_token'];
	//$app_details = json_decode(file_get_contents($graph_url), true);

	// how to get feed from Jeff Lobb's site
	// echo 'https://graph.facebook.com/68088808263/feed?access_token='.$params['access_token'];
	// remove feed to be able to pass params
	// echo 'https://graph.facebook.com/68088808263?access_token='.$params['access_token'].'&fields=posts.limit(2)';
	$limit = $limit + 5;
	$query = 'https://graph.facebook.com/' . $page_id . '?access_token='.$params['access_token'].'&fields=posts.limit(' . $limit . ').fields(message,caption,timeline_visibility,source,picture,description,name,type,link)';

  $content = wp_remote_get($query);
  if (!is_array($content) || !isset($content['body']) || !$content['body']) {
    return;
  }
	$feed = json_decode($content['body'], true);

	if(!isset($feed['posts']['data']))
		return null;

	// remove posts that don't have a message, picture, caption, name, or source
	foreach ($feed['posts']['data'] as $key => $value) {
		if (!isset($feed['posts']['data'][$key]['message']) && !isset($feed['posts']['data'][$key]['picture']) && !isset($feed['posts']['data'][$key]['caption']) && !isset($feed['posts']['data'][$key]['name']) && !isset($feed['posts']['data'][$key]['source'])) {
		unset($feed['posts']['data'][$key]);
		}
	}
	
	// reset posts array keys
	$feed['posts']['data'] = array_values($feed['posts']['data']);
	
	// how many posts are available after unsetting empty ones?
	$count = count($feed['posts']['data']);
	
	// declare new var
	$custom_feed['posts']['data'] = array();

	// reset actual limit set by admin
	$real_limit = $limit - 5;
	
	for ($j=0; $j < $real_limit; $j++) { 
		array_push($custom_feed['posts']['data'], $feed['posts']['data'][$j]);
	}

	$cache->save($custom_feed);
	
	return $custom_feed;

}


// style post HTML
function build_fb_post_html ( $post ) {
	
	$fb_post = array(
	'message' => '',
	'link' => '',
	'caption' => '',
	'media' => '',
	'month' => '',
	'day' => '',
	'year' => '',
	'hour' => '',
	'minute' => '',
	'am_pm' => '',
	'date_string' => '',
	'type' => $post['type']
	);
		
	$args = wp_parse_args( $post, $fb_post );

	extract( $args, EXTR_SKIP );

	if( ! isset( $post['caption'] ) ) $post['caption'] = '';
	
	// determine post type
	switch ($post['type']) {
	// break empties here
	case 'video':
	
		$fb_post['youtube_id'] = linkify_youtube_URLs($post['source']);
		// Handle YouTube
		if ( !empty($fb_post['youtube_id']) ) {
    $result = wp_remote_get("http://youtube.com/get_video_info?video_id=".$fb_post['youtube_id']);
    if (!is_array($result) || !isset($result['body']) || !$result['body']) {
      return;
    }
		$content = $result['body'];

		parse_str($content, $ytarr);
		$title_caption = stripslashes($ytarr['title']);
		
		$media = '<iframe style="width:100%" src="http://www.youtube.com/embed/' . $fb_post['youtube_id'] . '" frameborder="0"	showinfo="0" modestbranding="1" allowfullscreen></iframe>';
		} else {
		// just give image of video and link if not YouTube
		$media = '<a href="' . $post['source'] . '" style="position:relative;float:left;"><i class="fb-play-icon"></i><img src="' . $post['picture'] . '" alt=""></a>';
		}
		$fb_post['message'] = isset($post['message']) ? $post['message'] : '';
		$fb_post['link'] = $post['source'];
		$fb_post['caption'] = isset($caption) ? $caption : '';

		break;
	

	// case 'photo':
	//	 $fb_post['message'] = isset($post['message']) ? $post['message'] : '';
	//	 // $link = $post['link'];
	//	 $fb_post['media'] = '<img src="' . $post['picture'] . '" alt="' . @$post['caption'] . '">';
	//	 $fb_post['caption'] = isset($caption) ? $caption : '';
		
	//	 break;

	// case 'link':
	//	 $fb_post['message'] = $post['message'];
	//	 // $link = $post['link'];
	//	 $fb_post['media'] = '<img src="' . $picture . '" alt="' . $caption . '">';
	//	 $fb_post['caption'] = isset($description) ? $description : '';
		
	//	 break;

	// case 'status':
		
	//	 $fb_post['message'] = isset($post['message']) ? $post['message'] : '';
	//	 // $link = $post['link'];
	//	 $fb_post['media'] = '<img src="' . $post['picture'] . '" alt="' . @$post['caption'] . '">';
	//	 $fb_post['caption'] = isset($caption) ? $caption : '';
		
	//	 break;

	// case 'checkin':
		
	//	 $fb_post['message'] = isset($post['message']) ? $post['message'] : '';
	//	 // $link = $post['link'];
	//	 $fb_post['media'] = '<img src="' . $post['picture'] . '" alt="' . @$post['caption'] . '">';
	//	 $fb_post['caption'] = isset($caption) ? $caption : '';
		
	// break;

	default:
		// other less common post_types
		// music, question, review, swf, offer, note
		$fb_post['message'] = isset($post['message']) ? $post['message'] : '';
		$fb_post['link'] = isset($link) ? $link : '';
		if (!empty($picture)) {
		$fb_post['media'] = '<img src="' . $picture . '" alt="' . $caption . '">';
		}
		$fb_post['caption'] = isset($caption) ? $caption : '';
		$fb_post['description'] = isset($description) ? $description : '';

		break;
		
	}
	
	$dateTime = new DateTime($post['created_time']);
	$fb_post['month'] = $dateTime->format('M');
	$fb_post['day'] = $dateTime->format('j');
	$fb_post['year'] = $dateTime->format('Y');
	$fb_post['hour'] = $dateTime->format('g');
	$fb_post['minute'] = $dateTime->format('i');
	$fb_post['am_pm'] = $dateTime->format('a');
	$fb_post['date_string'] = '<p class="fb_post_date"><span class="fb_month">'.$fb_post['month'].'</span> <span class="fb_day">'.$fb_post['day'].'</span>, <span class="fb_year">'.$fb_post['year'].'</span></p>';
	
	// Add anchors to links in captions and messages
	$fb_post['caption'] = find_links_in_text($fb_post['caption']);
	$fb_post['message'] = find_links_in_text($fb_post['message']);
	
	if ( !empty($fb_post['message']) || !empty($fb_post['media']) ) {
	return $fb_post;
	}

}


// Linkify youtube URLs which are not already links.
function linkify_youtube_URLs($text) {

	// Only for YouTube video
	if (!strpos($text, 'youtube') && !strpos($text, 'youtu.be')) {
		return '';
	}

	$text = preg_replace('~
		# Match non-linked youtube URL in the wild. (Rev:20111012)
		https?://		 # Required scheme. Either http or https.
		(?:[0-9A-Z-]+\.)? # Optional subdomain.
		(?:				 # Group host alternatives.
			youtu\.be/		# Either youtu.be,
		| youtube\.com	# or youtube.com followed by
			\S*			 # Allow anything up to VIDEO_ID,
			[^\w\-\s]		 # but char before ID is non-ID char.
		)				 # End host alternatives.
		([\w\-]{11})		# $1: VIDEO_ID is exactly 11 chars.
		(?=[^\w\-]|$)	 # Assert next char is non-ID or EOS.
		(?!				 # Assert URL is not pre-linked.
			[?=&+%\w]*		# Allow URL (query) remainder.
			(?:			 # Group pre-linked alternatives.
			[\'"][^<>]*>	# Either inside a start tag,
			| </a>			# or inside <a> element text contents.
			)				 # End recognized pre-linked alts.
		)				 # End negative lookahead assertion.
		[?=&+%\w-]*		# Consume any URL (query) remainder.
		~ix', 
		'$1',
		$text);
	return $text;
}


function find_links_in_text ( $text ) {
	
	if ( !empty($text) ) {

	// HTTP / WWW links
		// The Regular Expression filter
		$reg_exUrl = "/((http|https|ftp|ftps)\:\/\/|www\.)[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

		// Check if there is a url in the text
		if(preg_match($reg_exUrl, $text, $url)) {

		// if URL ends in a period, remove it
		$last_char = substr($url[0], -1);
		if ($last_char == '.') {
			$url[0] = substr_replace($url[0] ,"",-1);
		}

		// make the urls hyper links
		$text = preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow" target="_blank">'.$url[0].'</a>', $text);
		} else {
		// if no urls in the text just return the text
		$text = $text;
		}

	// Facebook links
		// remove extra junk to get ID
		$reg_ex_ID = "/@\[[0-9]+:[0-9]+:/";
		if(preg_match($reg_ex_ID, $text, $url)) {
		$junk_ID = $url[0];
		$cleaner_ID = str_replace('@[', '', $url[0]);
		$cleaner_ID = explode(':', $cleaner_ID);
		$clean_ID = $cleaner_ID[0];
		$link_from_ID = 'http://www.facebook.com/' . $clean_ID;
		}

		// replace FB links in captions w/ correct link
		$reg_ex_ID_url = "/@\[[0-9]+:[0-9]+:[a-zA-Z0-9\-\.\s\,]+\]/";
		if(preg_match($reg_ex_ID_url, $text, $url)) {
		$link_text = str_replace($junk_ID, '', $url);
		$link_text = substr($link_text[0], 0, -1);
		$text = preg_replace($reg_ex_ID_url, '<a href="'.$link_from_ID.'" rel="nofollow" target="_blank">'.$link_text.'</a>', $text);
		}
		
		return $text;
		
	}

}