<?php

define( 'SOCIAL_DEBUGGER', false );
define( 'DX_CRONO_POSTER_PATH', plugin_dir_path( __FILE__ ) );

PL_Social_Networks::init();

class PL_Social_Networks {
	// Twitter related variables
	public static $user_token = NULL;
	public static $user_token_secret = NULL;
	public static $user_meta_key_token = 'pl_twitter_token';
	public static $user_meta_key_token_secret = 'pl_twitter_token_secret';
	public static $logged_user = NULL;
	public static $admin_redirect_uri = NULL;
	
	// Settings API option
	public static $social_setting = NULL;
	public static $social_setting_key = 'pls_social_setting';
	
	// Facebook related variables
	public static $fb_user_meta_key_token = 'fb_token';
	public static $fb_token_name = 'FBLoginToken';
	public static $logged_in = FALSE;
	public static $fb = NULL;
	public static $fb_token = NULL;
	public static $fb_profile = NULL;
	public static $fb_default_proxy_url = 'http://placester.com/bridge/';
	
	public static $fb_list_icon = '';
	public static $twitter_list_icon = '';
	
	public static function init() {
		// init for Twitter
		// add_action( 'admin_init', array( __CLASS__, 'verify_user_logged'), 3 );
		add_action( 'admin_init', array( __CLASS__, 'prevent_headers_already_sent_options' ), 1 );
		
		self::$fb_list_icon = PL_IMG_URL . '/social/pls-fb-icon.png';
		self::$twitter_list_icon = PL_IMG_URL . '/social/pls-twitter-icon.png';
		
		add_action( 'admin_init', array( __CLASS__, 'init_admin_redirect_uri' ), 1 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_post_metaboxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_post_social_messages' ) );
		
		// add_action( 'admin_menu', array( __CLASS__, 'add_social_settings_page' ) );
		
		add_action( 'pls_add_future_post', array( __CLASS__, 'publish_post_scheduled_delay' ), 10, 2 );
		
		// Settings API field init
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'wp_head', array( __CLASS__, 'add_google_authorship' ) );
		
		// Facebook init
		add_action( 'admin_init', array( __CLASS__, 'fb_login_callback' ) );
		add_action( 'pl_twitter_display', array( __CLASS__, 'twitter_handler' ) );
		add_action( 'pl_facebook_display', array( __CLASS__, 'facebook_handler' ) );
		add_action( 'pl_googleplus_display', array( __CLASS__, 'googleplus_handler' ) );
		
		add_filter('manage_posts_columns', array( __CLASS__, 'social_columns_append' ) );
		add_filter('manage_posts_custom_column', array( __CLASS__, 'social_column_behavior' ), 10, 2);
	}
	
	/**
	 * Future hook for publishing to FB and Twitter
	 * @param int $current_user_id user ID
	 * @param int $post_id post ID
	 */
	public static function publish_post_scheduled_delay( $current_user_id, $post_id ) {
		$facebook = self::get_facebook_object( $current_user_id );
		
		pls_log_socials('sn_saver.txt', 'The future has come! ' );
		
		$post = get_post( $post_id );
		
		if( empty( $post ) ) {
			return;
		}
		
		$slug = get_permalink( $post_id );

		// pls_log_socials('sn_saver.txt', 'Facebook Obj:  ' . var_export( $facebook, true ) );
		
		$pl_facebook_message = get_post_meta( $post_id, 'pl_facebook_message', true );
		
		if( ! empty( $facebook ) && ! empty( $pl_facebook_message ) ) {
			pls_log_socials('sn_saver.txt', 'Facebook object not empty. Progress - here we come.' );

			$facebook->api("/me/feed", "post", array(
					'message' => $pl_facebook_message,
					'link' => $slug,
			));
			pls_log_socials('sn_saver.txt', 'FB API seems to post' );
		}
		
		
		$twitter = self::get_twitter_object( $current_user_id );
		
		pls_log_socials('sn_saver.txt', 'Twitter Obj:  ' . var_export( $twitter, true ) );
		
		$pl_twitter_message = get_post_meta( $post_id, 'pl_twitter_message', true );
		
		if( $twitter && ! empty( $pl_twitter_message ) ) {
			$twitter->post('statuses/update', array('status' => $pl_twitter_message . ': ' . $slug ) );
			
			pls_log_socials('sn_saver.txt', 'Twitter API seems to post' );
		}
		
		return;
	}
	
	/**
	 * Verify if the user is logged in based on meta key value
	 * @return boolean logged/not
	 */
	public static function verify_user_logged() {
		if( is_user_logged_in() ) {
			self::$logged_user = wp_get_current_user();
			pls_debug_socials( 'in verify user logged' );
			$current_user_id = self::$logged_user->ID;
		
			if( ! empty( $current_user_id ) ) {
				$user_token = get_user_meta( $current_user_id, self::$user_meta_key_token, true );
				if( ! empty( $user_token ) ) {
					self::$user_token = $user_token;
				}
				$user_token_secret = get_user_meta( $current_user_id, self::$user_meta_key_token_secret, true );
				if( ! empty( $user_token_secret ) ) {
					self::$user_token_secret = $user_token_secret;
				}
				return true;
			}
		}
		return false; 
	}
	
	/**
	 * Init the twitter and facebook redirect URI, unique for a site domain
	 * 
	 * TODO: if it takes too much time, it's used in 3 methods only so clone there
	 */
	public static function init_admin_redirect_uri() {
		$admin_url = admin_url( 'options-general.php?page=placester-social' );
		self::$admin_redirect_uri = $admin_url;
 		define('OAUTH_CALLBACK', $admin_url );
	}
	
	/**
	 * Call settings page callback content for socials
	 */
	public static function add_social_settings_cb() {
		PL_Helper_Header::placester_admin_header();
		if( is_user_logged_in() ) {
			$current_user_id = get_current_user_id();
			
			// Clear database variables based on a GET request
			if( isset( $_GET['logout_clear'] ) && $_GET['logout_clear'] == 'twitter' ) {
 				delete_user_meta( $current_user_id, self::$user_meta_key_token );
 				delete_user_meta( $current_user_id, self::$user_meta_key_token_secret );
 				wp_redirect( self::$admin_redirect_uri );
 				exit;
			}
			if( isset( $_GET['logout_clear'] ) && $_GET['logout_clear'] == 'facebook' ) {
				delete_user_meta( $current_user_id, self::$fb_user_meta_key_token );
				wp_redirect( self::$admin_redirect_uri );
				exit;
			}
			
			// Call the template that loads Twitter and Facebook hooks
			include_once( PL_VIEWS_ADMIN_DIR . 'social/social.php');
		}
	}
	
	/**
	 * Manage all Facebook related work
	 */
	public static function facebook_handler() {
		self::facebook_callback();
	}
	
	/**
	 * Display the Google+ field for adding authorship
	 */
	public static function googleplus_handler() {
		include_once( PL_VIEWS_ADMIN_DIR . 'social/googleplus.php');
	}
	
	/**
	 * Manage steps 1-5 for Twitter authorization
	 */
	public static function twitter_handler() {
		// Mandatory configs
		include_once PL_LIB_DIR . 'twitteroauth/config.php';
		include_once PL_LIB_DIR . 'twitteroauth/twitteroauth/twitteroauth.php';
			
		// Step 5 - we already know the user, he's authorized, we have the data in DB
		if( self::is_twitter_authenticated() ) {
			pls_log_socials( 'logins.txt', 'step5' );
			$connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, self::$user_token, self::$user_token_secret );
		
			if( isset( $_GET['postme'] ) ) {
				$post_msg = urldecode( $_GET['postme'] );
				$connection->post('statuses/update', array('status' => $post_msg));
			}
				
			$user = $connection->get('account/verify_credentials');
			if( is_object( $user ) ) {
				echo "<p>Hello, " . $user->screen_name . "</p>";
			}
			
			echo '<p><a href="' . self::$admin_redirect_uri .'&logout_clear=twitter">Logout from Twitter</a></p>';
			pls_debug_socials( 'Authorized:', 'brown');
			// pls_debug_socials($content, 'brown');
		} else {
			// Steps 1 through 4 for authentication
			if( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) ) {
				pls_log_socials( 'logins.txt', 'step3:' );
				// If session is empty, then it's probably auto flushed after step 3
				if( is_null( $_SESSION ) ) {
					self::step4_login();
				} elseif( ! isset( $_SESSION['first_token'] ) ) {
					$_SESSION['first_token'] = $_GET['oauth_token'];
					self::step3_login();
				} else {
					// Update session due to step 3 if not automatically done
					session_destroy();
					session_start();
					pls_log_socials( 'logins.txt', 'step4' );
					self::step4_login();
				}
			}
			else if( isset( $_GET['social_action'] ) && $_GET['social_action'] === 'twitter-redirect' ) {
				pls_log_socials( 'logins.txt', 'step2' );
				self::step2_login();
			} else {
				pls_log_socials( 'logins.txt', 'step1' );
				self::step1_login();
			}
		}
	}
	
	/**
	 * Step 1 - initial 'Sign in' button
	 */
	public static function step1_login() {
		if (CONSUMER_KEY === '' || CONSUMER_SECRET === '') {
			echo 'You need a consumer key and secret to test the sample code. Get one from <a href="https://twitter.com/apps">https://twitter.com/apps</a>';
			exit;
		}
		
		/* Build an image link to start the redirect process. */
		$content = '<a href="' . self::$admin_redirect_uri . '&social_action=twitter-redirect"><img src="' .  trailingslashit(PL_IMG_URL) .'social/twlogin.png" alt="Sign in with Twitter"/></a>';
			
		echo $content;
		/* Include HTML to display on the page. */
	}
	
	/**
	 * Step 2 - the Redirect to Twitter for auth
	 */
	public static function step2_login() {
		include_once PL_LIB_DIR . 'twitteroauth/redirect.php';
	}
	
	/**
	 * Step 3 - do the twist (i.e. verify that token of yours)
	 */
	public static function step3_login() {
		$connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );
		$url = $connection->getAuthorizeURL( $_GET['oauth_token'], FALSE );
		pls_log_socials( 'logins.txt', 'in step 3: url and connection');
		pls_log_socials( 'logins.txt', $url );
		pls_log_socials( 'logins.txt', var_export( $connection, true) );
		pls_log_socials( 'responses.txt', var_export( $_REQUEST, true ) );
		
		pls_debug_socials('URL to redrect to!', '#FF00AA');
		pls_debug_socials( $url, 'yellow' );
// 		die();
		wp_redirect( $url );
		exit;
	}
	/**
	 * Step 4 - already authorized, use the correct tokens now!
	 */
	public static function step4_login() {
		$connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );
// 		$token_consumer = $connection->getCorrectAccessToken( $_GET['oauth_token'], $_GET['oauth_verifier'], 'POST' );
		$token_consumer = $connection->getAccessToken( $_GET['oauth_verifier'] );

		// check is not processed before that
		if( self::verify_user_logged() ) {
			if( isset( $token_consumer['oauth_token'] ) && isset( $token_consumer['oauth_token_secret'] ) ) {
				update_user_meta( self::$logged_user->ID, self::$user_meta_key_token, $token_consumer['oauth_token']);
				update_user_meta( self::$logged_user->ID, self::$user_meta_key_token_secret, $token_consumer['oauth_token_secret']);
				
				wp_redirect( self::$admin_redirect_uri );
				exit;
			}
			
			pls_debug_socials(' token and verifier - okay ', 'blue');
			pls_debug_socials( $token_consumer, 'red' );
		} else {
			pls_debug_socials(' No user verified ');
		}
	}
 
	/**
	 * Add post metaboxes for posts and pages
	 */
	public static function add_post_metaboxes() {
		add_meta_box(
			'pl_social_box',
			__( 'Social Publishing', 'pls' ),
			array( __CLASS__, 'add_post_metaboxes_callback' ),
			'page', // leave empty quotes as '' if you want it on all custom post add/edit screens
			'normal'
		);
		add_meta_box(
			'pl_social_box',
			__( 'Social Publishing', 'pls' ),
			array( __CLASS__, 'add_post_metaboxes_callback' ),
			'post', // leave empty quotes as '' if you want it on all custom post add/edit screens
			'normal'
		);
	}
	
	/**
	 * Content for metaboxes
	 */
	public static function add_post_metaboxes_callback( $post_id ) {
	?>
		<script type="text/javascript">
		/*
		* Mostly reusing the word-count.dev.js script from wp-admin
		*/
		jQuery(document).ready( function($) {
			wpSocialWordCount = {

				settings : wpWordCount.settings,

				block : wpWordCount.block,

				wc : function(tx, selector) {
					var t = this, tc = 0;
					var w = $(selector);

					var type = wordCountL10n.type;

					if ( t.block )
						return;

					t.block = 1;

					setTimeout( function() {
						if ( tx ) {
							tx = tx.replace( t.settings.strip, ' ' ).replace( /&nbsp;|&#160;/gi, ' ' );
							tx = tx.replace( t.settings.clean, '' );
							tx.replace( t.settings[type], function(){tc++;} );
						}
						w.html(tc.toString());

						setTimeout( function() { t.block = 0; }, 2000 );
					}, 1 );
				}
			}

			$(document).bind( 'plsFbCountWords', function(e) {
				var txt = $('#pl_facebook_message').val();
				wpSocialWordCount.wc(txt, '#pl_facebook_word_count');
			});
			
			$(document).bind( 'plsTwitterCountWords', function(e) {
				var txt = $('#pl_twitter_message').val();
				$('#pl_twitter_word_count').html( txt.length );
				
			});

			$('#pl_facebook_message').on('change', function() {
				$(document).triggerHandler('plsFbCountWords');
			});

			$('#pl_twitter_message').on('change', function() {
				$(document).triggerHandler('plsTwitterCountWords');
			});
		});
		
		</script>
		
		<?php
			$facebook_message = '';
			$twitter_message = '';
			
			// Populate content for future posts (remind authors what is about to get published)
			$current_post = get_post( $post_id );
			if( ! empty( $current_post ) && $current_post->post_status == 'future' ) {
				$post_meta = get_post_custom( $current_post->ID );
				$facebook_message = ! empty( $post_meta['pl_facebook_message'] ) ? $post_meta['pl_facebook_message'][0] : '';
				$twitter_message = ! empty( $post_meta['pl_twitter_message'] ) ? $post_meta['pl_twitter_message'][0] : '';
			}
		?>
		<div class="social-left facebook-block">
			<h4>Facebook</h4>
			<p><textarea id="pl_facebook_message" name="pl_facebook_message" cols="40" rows="5"><?php echo $facebook_message; ?></textarea></p>
			<p><span><?php _e('Words: ', 'pls'); ?></span><span id="pl_facebook_word_count">0</span></p>
		</div>
		<div class="social-right facebook-block">
			<h4>Twitter</h4>
			<p><textarea id="pl_twitter_message" name="pl_twitter_message" cols="40" rows="5"><?php echo $twitter_message; ?></textarea></p>
			<p><span><?php _e('Characters: ', 'pls'); ?></span><span id="pl_twitter_word_count">0</span></p>
		</div>
		<div class="clearblock"></div>
		 <?php wp_nonce_field( 'pls_social_submission', 'pls_social_nonce' ); ?>
	<?php 
	}

	/**
	 * Save hook for post social messages
	 */
	public static function save_post_social_messages( $post_id ) {
		pls_log_socials('sn_saver.txt', 'Top of save_post_social_messages' );
		pls_log_socials('sn_saver.txt', var_export( $_POST, true ) );

		// Avoid autosaves
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		
		// Verify nonces for ineffective calls
 		if( !isset( $_POST['pls_social_nonce'] ) || !wp_verify_nonce( $_POST['pls_social_nonce'], 'pls_social_submission' ) ) return;
		
		// if our current user can't edit this post, bail - notices 
// 		if( !current_user_can( 'edit_post' ) ) return;
		
		$post = get_post( $post_id );
		
		pls_log_socials('sn_saver.txt', 'Post ID, nonce checked: ' . $post_id );
		
		if( empty( $post ) ) {
			return;
		}
		
		// Update database values
		if( ! empty( $_POST['pl_facebook_message'] ) ) {
			$pl_facebook_message = $_POST['pl_facebook_message'];
				
			update_post_meta( $post_id, 'pl_facebook_message', $pl_facebook_message );
		}
		if( ! empty( $_POST['pl_twitter_message'] ) ) {
			$pl_facebook_message = $_POST['pl_twitter_message'];
		
			update_post_meta( $post_id, 'pl_twitter_message', $pl_facebook_message );
		}
		
// 		pls_log_socials('sn_saver.txt', 'Post Object: ' . var_export( $post, true ) );
		
		if( $post->post_status == 'future' ) { 
			pls_log_socials('sn_saver.txt', 'Future post here' );
			$time = strtotime( $post->post_date_gmt . ' GMT' );
			pls_log_socials('sn_saver.txt', 'Scheduled timing: ' . $post->post_date_gmt );

			if ( $time > time() ) { // Uh oh, someone jumped the gun!
				// wp_clear_scheduled_hook( 'publish_future_post', array( $post_id ) ); // clear anything else in the system
				pls_log_socials('sn_saver.txt', 'Right before scheduling' );
				
				$scheduled_cron_arguments = array( get_current_user_id(), $post_id );
				wp_schedule_single_event( $time, 'pls_add_future_post', $scheduled_cron_arguments );
				return;
			}
		}
		
		// Handle Facebook and Twitter messaging
		if ( !wp_is_post_revision( $post_id ) ) {
			$slug = get_permalink( $post_id );
			
			
			if( ! empty( $_POST['pl_facebook_message'] ) ) {
				$facebook = self::get_facebook_object();

				if( $facebook ) {
					$message = $_POST['pl_facebook_message'];
					$facebook->api("/me/feed", "post", array(
									    message => $message,
									    link => $slug,
									));
				}
			}
			
			if( ! empty( $_POST['pl_twitter_message'] ) ) {
				$twitter = self::get_twitter_object();
				if( $twitter ) {
					$message = $_POST['pl_twitter_message'];
					$twitter->post('statuses/update', array('status' => $message . ': ' . $slug ) );
				}	
			}
		}
	}
	
	/**
	 * Stop automatic header sent when loading the admin template (this breaks the redirect to twitter part)
	 */
	public static function prevent_headers_already_sent_options( ) {
		$request_uri = $_SERVER['REQUEST_URI'];
		
		if( false !== strpos( $request_uri, 'page=placester-social&social_action=twitter-redirect' )
		|| ( false !== strpos( $request_uri, 'page=placester-social' ) && false !== strpos( $request_uri, 'oauth_verifier=' ) )
		|| ( false !== strpos( $request_uri, 'page=placester-social' ) && false !== strpos( $request_uri, 'logout_clear=' ) ) ) {
		
			ob_start();
		}
	}
	
	/**
	 * Facebook functions
	 */
	
	/**
	 * Get the login callback for communication with the proxy
	 */
	public static function fb_login_callback() {
		if( ! is_user_logged_in() )
			return;

// 		pls_log_socials( 'logins.txt', var_export( $_GET, true ) );
		if ( isset( $_GET[ self::$fb_token_name ] ) ) {
			include_once PL_LIB_DIR . 'facebook-php-sdk/src/facebook.php';
			
			update_user_meta( get_current_user_id(), self::$fb_user_meta_key_token, $_GET[ self::$fb_token_name ] );

			pls_log_socials( 'logins.txt', 'in token part' );
			
			$request_uri = $_SERVER['REQUEST_URI'];
			if( strpos( $request_uri, '/wp-admin' ) !== false ) {
				$request_uri = substr( $request_uri, strpos( $request_uri, '/wp-admin' ) );
			}
			
			$redirect = self::fb_get_clean_url( get_bloginfo('url') . $request_uri );
	
 			die( '<script type="text/javascript">top.location.href = "' .  $redirect . '";</script>' );
		}
	
		if( ! empty( $_GET['page'] ) && $_GET['page'] === 'placester-social' && 
			! ( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) ) ) {
			include_once PL_LIB_DIR . 'facebook-php-sdk/src/facebook.php';
			pls_log_socials( 'logins.txt', 'below if token part' );
			pls_log_socials( 'logins.txt', var_export( $_GET, true ) );
			self::$logged_in = FALSE;
		
			self::$fb_token = get_user_meta( get_current_user_id(), self::$fb_user_meta_key_token, true );
		
			self::$fb = new Facebook( array( 'appId' => NULL, 'secret' => NULL ) );
			self::$fb->setAccessToken( self::$fb_token );
		
			try {
				self::$fb_profile = self::$fb->api( '/me' );
			}
			catch( FacebookApiException $e ) {
				pls_debug_socials( $e->getMessage() );
				return;
			}
		
			self::$logged_in = TRUE;
		}
	}
	
	/**
	 * Print the login URL for Facebook
	 * 
	 * Part of a function to be reused in other sections if needed
	 * 
	 */
	public static function fb_print_login_url() {
		$proxy_url = get_option( 'fb_proxy_url', self::$fb_default_proxy_url );
		
		echo '<a href="' . trailingslashit( $proxy_url ) . 'login.php"><img src="' . trailingslashit(PL_IMG_URL) . 'social/fblogin.png" /></a>' . "<br />\n\n"; 
		// . print_r($this->_profile, true) . "<br />\n\n";
	}
	
	/**
	 * Manage Facebook arguments and add to temporary array for building proper query to the proxy
	 * @return string url
	 */
	private static function fb_get_clean_url( $url ) {
		$tmp = parse_url( $url );
	
		if ( !is_array( $tmp ) )
			return $url;
	
		$tmp_query = array();
		parse_str( @$tmp['query'], $tmp_query );
	
		unset($tmp_query[self::$fb_token_name]);
	
		$tmp['query'] = http_build_query( $tmp_query );
	
		if ( strlen( $tmp['query'] ) )
			$tmp['query'] = '?' . $tmp['query'];
	
		return $tmp['scheme'] . '://' . $tmp['host'] . $tmp['path'] . $tmp['query'];
	}
	
	/**
	 * Menu page call
	 */
	
	public static function facebook_callback() {
		include_once( PL_VIEWS_ADMIN_DIR . 'social/facebook.php');
	}
	
	/**
	 * Property-alike helper functions for templates
	 */
	public static function get_user_meta_key_name() {
		return self::$fb_user_meta_key_token;
	}
	
	public static function get_token_key_name() {
		return self::$fb_token_name;
	}
	
	public static function is_logged_in() {
		return self::$logged_in;
	}
	
	public static function get_profile() {
		return self::$fb_profile;
	}
	
	public static function add_social_settings_page() {
		add_options_page('Social Networks', 'Social Networks', 'manage_options',
			'placester-social', array( __CLASS__, 'add_social_settings_cb' ) );
	}
	/**
	 * Helpers for checking whether a user is logged in or not
	 */
	
	public static function is_facebook_authenticated( $current_user_id = 0 ) {
		pls_log_socials('fb_author.txt', 'Inside of is_facebook_authenticated() ');

		// When cron request has been issued with the ID already known
		if( empty( $current_user_id ) ) {
			if( ! is_user_logged_in() ) {
				return false;
			}
			
			$current_user_id = get_current_user_id();
		}
		
		pls_log_socials('fb_author.txt', 'Current User ID: ' . $current_user_id );
		
		$user_facebook_token = get_user_meta( $current_user_id, self::$fb_user_meta_key_token, true );
		
		if( empty( $user_facebook_token ) ) {
			return false;
		}
		
		self::$fb_token = $user_facebook_token;
		
		return true;
	}
	
	/**
	 * Is logged in to Twitter
	 * @param int $current_user_id user ID
	 * @return boolean is logged in
	 */
	public static function is_twitter_authenticated( $current_user_id = 0 ) {
		if( empty( $current_user_id ) ) {
			if( ! is_user_logged_in() ) {
				return false;
			}
			$current_user = wp_get_current_user();
			$current_user_id = $current_user->ID;
		}
		
		$user_twitter_token = get_user_meta( $current_user_id, self::$user_meta_key_token, true );
		$user_twitter_token_secret = get_user_meta( $current_user_id, self::$user_meta_key_token_secret, true );
		if( empty( $user_twitter_token ) || empty( $user_twitter_token_secret ) ) {
			return false;
		}
		
		self::$user_token = $user_twitter_token;
		self::$user_token_secret = $user_twitter_token_secret;
		
		return true;
	}
	
	/**
	 * Helper functions for getting the objects for Twitter/Facebook management
	 */
	
	/**
	 * Get Twitter object
	 * @return TwitterOAuth or false
	 */
	public static function get_twitter_object( $current_user_id = 0 ) {
		if( self::is_twitter_authenticated( $current_user_id ) ) {
			include_once PL_LIB_DIR . 'twitteroauth/config.php';
			include_once PL_LIB_DIR . 'twitteroauth/twitteroauth/twitteroauth.php';
			
			$connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, self::$user_token, self::$user_token_secret );
			
			return $connection;
		}
		
		return false;
	}
	
	/**
	 * Get Facebook object
	 * @return Facebook object or false
	 */
	public static function get_facebook_object( $current_user_id = 0 ) {
		pls_log_socials('fb_author.txt', 'Inside of get_facebook_object() ');
		if( self::is_facebook_authenticated( $current_user_id ) ) {
			include_once PL_LIB_DIR . 'facebook-php-sdk/src/facebook.php';

			self::$fb = new Facebook( array( 'appId' => NULL, 'secret' => NULL ) );
			self::$fb->setAccessToken( self::$fb_token );
			
			try {
				pls_debug_socials( 'alabalaportokala' );
				self::$fb_profile = self::$fb->api( '/me' );
				
				return self::$fb;
			}
			catch( FacebookApiException $e ) {
				pls_log_socials( 'fb_author.txt', 'FB API failed: ' . $e->getMessage() );
				pls_debug_socials( $e->getMessage() );
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * Manage post columns to add social one
	 * @param array $columns columns array
	 */
	public static function social_columns_append( $columns ) {
		// need to add the second column
		// array_splice doesn't handle assoc arrays properly
		$new_columns = array();
		$col_index = 0;
		
		foreach( $columns as $key => $value ) {
			$col_index++;
			if( $col_index == 3 ) {
				$new_columns['Social'] = 'Social';
			}
			$new_columns[$key] = $value;
		}
		
		return $new_columns;
	}
	
	/**
	 * Add the social column icons in List/All Posts screen
	 * @param string $column_name
	 * @param int $post_id
	 */
	public static function social_column_behavior( $column_name, $post_id ) {
		$out = '';
		
		if( $column_name === 'Social' ) {
			$out = '<span class="pl_list_social_icons">';			

			$post_meta = get_post_custom( $post_id );
			
			if( ! empty( $post_meta['pl_facebook_message'] ) ) {
				$out .= '<img src="' . self::$fb_list_icon . '" / style="margin-right: 10px;">';
			}
			if( ! empty( $post_meta['pl_twitter_message'] ) ) {
				$out .= '<img src="' . self::$twitter_list_icon . '" />';
			}
			
			$out .= '</span>';
		}
		
		echo $out;
	}
	
	/**
	 * Register social options with the Settings API
	 */
	public static function register_settings() {
		register_setting( self::$social_setting_key, self::$social_setting_key, array( __CLASS__, 'validate_settings' ) );
	
		add_settings_section(
			'pls_socials_section',         
			'',                  		
			array( __CLASS__, 'settings_callback' ), 
			'placester_social'
		);
		
		add_settings_field(
			'pls_googleplus_id',                      
			'Google Plus ID',
			array( __CLASS__, 'pls_googleplus_callback' ), 
			'placester_social',                         
			'pls_socials_section'  
		);
	}
	
	public static function settings_callback( ) {}
	
	/**
	 * Display Google+ Input field with proper data
	 */
	public static function pls_googleplus_callback() {
		$out = '';
		$val = '';
		
		self::$social_setting = get_option( self::$social_setting_key, '' );
		if( ! is_array( self::$social_setting ) || ! isset( self::$social_setting['pls_googleplus_id'] ) ) {
			$val = '';
		} else {
			$val = self::$social_setting['pls_googleplus_id'];
		}
		
		$out = '<input type="text" id="pls_googleplus_id" name="pls_social_setting[pls_googleplus_id]" value="' . $val . '"  />';
		
		echo $out;
	}
	
	/**
	 * Validate settings fields
	 * @param array $input settings array
	 * @return array $input get the array filtered
	 */
	public static function validate_settings( $input ) {
		if( isset( $input['pls_googleplus_id'] ) ) {
			$input['pls_googleplus_id'] = wp_strip_all_tags( $input['pls_googleplus_id'] );
		}
		
		return $input;
	}
	
	public static function add_google_authorship() {
		self::$social_setting = get_option( self::$social_setting_key, array() );
		
		if( ! empty( self::$social_setting ) 
			&& isset( self::$social_setting['pls_googleplus_id'] ) ) {
			
		?><link rel="author" href="https://plus.google.com/<?php echo self::$social_setting['pls_googleplus_id']; ?>/posts/">
		<?php 
		} 
	}
	
}

/**
 * Debugging area
 * 
 * Only if SOCIAL_DEBUGGER constant is true
 */

// Screen debugging
function pls_debug_socials( $arg, $color = 'black' ) {
	if( SOCIAL_DEBUGGER ) {
		echo "<pre style='color: $color'>";
		var_dump( $arg );
		echo "</pre>";
	}
}

// file debugging (note: logs needs to be manually created)
function pls_log_socials( $file, $text ) {
	if( SOCIAL_DEBUGGER ) {
		$content = time() . ': ' . $text . "\n";
		file_put_contents(DX_CRONO_POSTER_PATH  . 'logs/' . $file, $content, FILE_APPEND | LOCK_EX);
	}
}
