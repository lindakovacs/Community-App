<?php 

PL_Logging::init();
class PL_Logging {

	private static $hook;
	private static $pages = array();
	private static $is_pl_page = null;

	private static $custom_post_types = array(
		'agent' => 'Agent Post Type',
		'service' => 'Service Post Type',
		'testimonial' => 'Testimonial Post Type',
		'property' => 'Property Post Type',
		'pl_general_widget' => 'Shortcode Widget Post Type' // custom post type that powers the short code functionality
	);

	public static function init () {
	 	$logging_option = PL_Option_Helper::get_log_errors();
	 	if ($logging_option) {
	 		add_action('admin_menu', array(__CLASS__, 'create_page_list'), 50);
	 		add_action('admin_enqueue_scripts', array(__CLASS__, 'record_page'));
			add_action('admin_head', array(__CLASS__, 'start'));
			add_action('admin_footer', array(__CLASS__, 'events'));
			register_activation_hook(PLACESTER_PLUGIN_DIR, 'activation');
		}
	}

	public static function create_page_list () {
		global $submenu;
	 	// error_log(var_export($submenu['placester'], true));
	 	
	 	if (!empty($submenu['placester'])) {
	 		foreach ($submenu['placester'] as $page) {
	 			$hook = "placester_page_{$page[2]}";
	 			self::$pages[$hook] = $page[3];
	 		}
	 	}
	}
	
	public static function record_page ($hook) {
		self::$hook = $hook;
	}

	// Logic to help determine which pages mixpanel is used
	public static function start () {
		// Exclude on non-placester pages...
		if (self::is_placester_page()) {
			echo self::mixpanel_inline_js();	
		}
	}

	public static function events () {
		if (!self::is_placester_page()) { return; }

	 	ob_start();
	 	if (!PL_Option_Helper::api_key()) {
	 		?>
		 		<script type="text/javascript">
		 			jQuery('#signup_wizard').live('dialogopen', function () {
		 				pls_track_event("SignUp: Overlay Opened");			
		 			});
		 			jQuery('#signup_wizard').live('dialogclose', function () {
		 				pls_track_event("SignUp: Overlay Closed");			
		 			});
		 			jQuery('#pls_search_form input#email').live('focus', function() {
		 				pls_track_event("SignUp: Edit Sign Up Email");			
		 			});
		 			jQuery('#confirm_email_button').live('click', function() {
		 				pls_track_event("SignUp: Confirm Email Click");			
		 			});
		 		</script>	
		 	<?php	
	 	} 
	 	else {
	 		$page = self::get_page_tracking_name();

	 		?>
		 		<script type="text/javascript">
		 			//Log page views since wordpress always appears as admin.php :(. 
		 			pls_track_event("<?php echo $page ?>");		
		 		</script>
	 		<?php
	 	}
	 	echo ob_get_clean();
	}

	public static function activation () {
		// Nothing yet..
	}

	private static function is_placester_page () {
		global $typenow;
		
		// Easy way to dump out page names...
		// error_log("Hook: " . self::$hook);
		// error_log("Typenow: {$typenow}");

		// Check to see if the result for this has already been calculated and stored...
		if (is_bool(self::$is_pl_page)) {
			$is_placester_page = self::$is_pl_page;
		}
		else {
			// Custom post type indexs appear as edit.php
			if (self::$hook === 'edit.php' && array_key_exists($typenow, self::$custom_post_types)) {
				$is_placester_page = true;
			} 
			// Custom post type create new pages appear as "post-new.php"
			elseif (self::$hook === 'post-new.php' && array_key_exists($typenow, self::$custom_post_types)) {
				$is_placester_page = true;
			}
			// Custom post type edit pages are on post.php
			elseif (self::$hook === 'post.php' && array_key_exists($typenow, self::$custom_post_types)) {
				$is_placester_page = true;
			} 
			// Otherwise check for a placester value in the hook
			elseif (array_key_exists(self::$hook, self::$pages)) {
				$is_placester_page = true;
			} 
			// So we can catch the activation event
			elseif (self::$hook === 'plugins.php') {
				$is_placester_page = true;
			}
			else {
				$is_placester_page = false;
			}

			// Store result for any subsequent calls...
			self::$is_pl_page = $is_placester_page;
		}

		return $is_placester_page;
	}

	public static function mixpanel_inline_js () {
		// Retrieve user info for instrumentation purposes...
		$whoami = PL_Helper_User::whoami();

		ob_start();
	 	?>
	 		<script type="text/javascript">
			    (function(c,a){window.mixpanel=a;var b,d,h,e;b=c.createElement("script");
			    b.type="text/javascript";b.async=!0;b.src=("https:"===c.location.protocol?"https:":"http:")+
			    '//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';d=c.getElementsByTagName("script")[0];
			    d.parentNode.insertBefore(b,d);a._i=[];a.init=function(b,c,f){function d(a,b){
			    var c=b.split(".");2==c.length&&(a=a[c[0]],b=c[1]);a[b]=function(){a.push([b].concat(
			    Array.prototype.slice.call(arguments,0)))}}var g=a;"undefined"!==typeof f?g=a[f]=[]:
			    f="mixpanel";g.people=g.people||[];h=['disable','track','track_pageview','track_links',
			    'track_forms','register','register_once','unregister','identify','alias','name_tag',
			    'set_config','people.set','people.increment','people.track_charge','people.append'];
			    for(e=0;e<h.length;e++)d(g,h[e]);a._i.push([b,c,f])};a.__SV=1.2;})(document,window.mixpanel||[]);
			    mixpanel.init("9186cdb540264089399036dd672afb10");

				//things that we want to track for every request.
				var core_properties = {
					"first seen": new Date(),
					"$initial referrer": document.referrer,
					"wordpress_location": "<?php echo site_url(); ?>",
					"wordpress_version": "<?php echo get_bloginfo('version'); ?>",
					"wordpress_language": "<?php echo get_bloginfo('language'); ?>",
					"install_type": "<?php echo ( defined('HOSTED_PLUGIN_KEY') ? 'hosted' : 'remote' ); ?>"
				};
				//append them to every request.
				mixpanel.register_once(core_properties);

				//conditionally identify if we actually know who this person is.
				<?php if (is_array($whoami)): ?>
					mixpanel.identify("<?php echo $whoami['user']['email']; ?>");
					mixpanel.name_tag("Registered - <?php echo $whoami['user']['email']; ?>");				
					var user_data = core_properties;
					user_data['$email'] = "<?php echo $whoami['user']['email'] ?>";
					user_data['$first_name'] = "<?php echo $whoami['user']['first_name']; ?>";
					user_data['$last_name'] = "<?php echo $whoami['user']['last_name']; ?>";
					user_data['wordpress_email'] = "<?php echo get_option('admin_email'); ?>";
					mixpanel.people.set(user_data);
				<?php else: ?>
					mixpanel.name_tag("Unregistered - <?php echo get_option('admin_email'); ?>");
				<?php endif; ?>
			</script>
	 	<?php
	 	
	 	return ob_get_clean();
	}

	private static function get_page_tracking_name () {
		global $typenow;
 		$page = 'unknown';

 		if (self::$hook === 'edit.php') {
			$page = 'View - ' . self::$custom_post_types[$typenow];
 		}
 		elseif (self::$hook === 'post-new.php') {
 			$page = 'View - New - ' . self::$custom_post_types[$typenow];
 		} 
 		elseif (self::$hook === 'post.php') {
 			$page = 'View - Edit - ' . self::$custom_post_types[$typenow];
 		}
 		elseif (array_key_exists(self::$hook, self::$pages)) {
 			$page = 'View - ' . self::$pages[self::$hook];
 		} 

 		return $page;
	}
}
