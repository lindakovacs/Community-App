<?php 

PL_Css_Helper::init();

class PL_Css_Helper {
	
	public static function init () {		
		// add_action( 'admin_init', array( __CLASS__, 'admin' ));
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin' ) );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'customizer' ) );
	}

	public static function admin ($hook) {
		// Inject premium themes logic into the themes admin page when visiting from any site on the hosted env...
		if ($hook == 'themes.php' && defined('HOSTED_PLUGIN_KEY')) {		
			self::register_enqueue_if_not('global-css', trailingslashit(PL_CSS_URL) . 'global.css');
			self::register_enqueue_if_not('jquery-ui', trailingslashit(PL_JS_LIB_URL) . 'jquery-ui/css/smoothness/jquery-ui-1.8.17.custom.css');
			// self::register_enqueue_if_not('jquery-ui-dialog', OPTIONS_FRAMEWORK_DIRECTORY.'css/jquery-ui-1.8.22.custom.css');
		}
		
		if ( $hook == 'post-new.php' || $hook == 'post.php' || ( $hook == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'pl_general_widget' ) ) {
			self::register_enqueue_if_not('post-screens', trailingslashit(PL_CSS_ADMIN_URL) . 'post-screens.css');
		}

		// NOTE:  This ensures that pages with the proper hook prefix make it past this point... (i.e., only plugin admin pages)
		if (strpos($hook, 'placester_page_placester_') === false && $hook != 'edit.php') { return; }

		//always load these
		self::register_enqueue_if_not('global-css', trailingslashit(PL_CSS_URL) . 'global.css');		
		self::register_enqueue_if_not('jquery-ui', trailingslashit(PL_JS_LIB_URL) . 'jquery-ui/css/smoothness/jquery-ui-1.8.17.custom.css');
		
		// If no API key is set, load the following CSS files for use by the wizard on ANY plugin settings page...
		if (!PL_Option_Helper::api_key()) {
		  self::register_enqueue_if_not('integrations', trailingslashit(PL_CSS_ADMIN_URL) . 'integration.css');		
        }

		if ($hook == 'placester_page_placester_properties') {
			self::register_enqueue_if_not('my-listings', trailingslashit(PL_CSS_ADMIN_URL) . 'my-listings.css');					
		}

		if ($hook == 'placester_page_placester_property_add') {
			self::register_enqueue_if_not('add-listing', trailingslashit(PL_CSS_ADMIN_URL) . 'add-listing.css');			
		}

		if ($hook == 'placester_page_placester_support') {
			self::register_enqueue_if_not('support', trailingslashit(PL_CSS_ADMIN_URL) . 'support.css');			
		}

		if ($hook == 'placester_page_placester_theme_gallery') {
			self::register_enqueue_if_not('support', trailingslashit(PL_CSS_ADMIN_URL) . 'theme-gallery.css');			
		}

		if ($hook == 'placester_page_placester_settings_lead_capture') {
			self::register_enqueue_if_not('settings-all', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/all.css');					
			self::register_enqueue_if_not('integrations', trailingslashit(PL_CSS_ADMIN_URL) . 'lead-capture/general.css');
		}

		if ($hook == 'placester_page_placester_integrations') {
			self::register_enqueue_if_not('integrations', trailingslashit(PL_CSS_ADMIN_URL) . 'integration.css');
		}

		if ($hook == 'placester_page_placester_settings') {
			self::register_enqueue_if_not('settings-all', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/all.css');					
			self::register_enqueue_if_not('settings-general', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/general.css');	
		}

		if ($hook == 'placester_page_placester_settings_polygons') {
			self::register_enqueue_if_not('settings-all', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/all.css');					
			self::register_enqueue_if_not('settings-polygons', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/polygon.css');									
			self::register_enqueue_if_not('colorpicker', trailingslashit(PL_JS_URL) . 'lib/colorpicker/css/colorpicker.css');					
		}

		if ($hook == 'placester_page_placester_settings_international') {
			self::register_enqueue_if_not('settings', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/all.css');					
		}
		
		if ($hook == 'placester_page_placester_settings_client') {
			self::register_enqueue_if_not('settings-all', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/all.css');					
			self::register_enqueue_if_not('settings-filtering', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/client.css');					
		}
		
		if ($hook == 'placester_page_placester_settings_filtering') {
			self::register_enqueue_if_not('settings-all', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/all.css');					
			self::register_enqueue_if_not('settings-filtering', trailingslashit(PL_CSS_ADMIN_URL) . 'settings/filtering.css');					
		}

		if ($hook == 'placester_page_placester_shortcodes_shortcode_edit') {
			self::register_enqueue_if_not('featured-listings', trailingslashit(PLS_OPTRM_URL) . 'css/featured-listings.css');
			self::register_enqueue_if_not('placester-widget', trailingslashit(PL_CSS_ADMIN_URL) . 'shortcodes/all.css');
		}
		
		if ( $hook == 'placester_page_placester_shortcodes_template_edit') {
			self::register_enqueue_if_not('codemirror', trailingslashit(PL_JS_URL) . 'lib/codemirror/codemirror.css');
			self::register_enqueue_if_not('placester-widget', trailingslashit(PL_CSS_ADMIN_URL) . 'shortcodes/all.css');
			self::register_enqueue_if_not('placester-widget-chosen', trailingslashit(PL_JS_URL) . 'lib/chosen/chosen.css');
		}
		
		if ( $hook == 'placester_page_placester_shortcodes_listing_template_edit') {
			self::register_enqueue_if_not('codemirror', trailingslashit(PL_JS_URL) . 'lib/codemirror/codemirror.css');
			self::register_enqueue_if_not('placester-widget', trailingslashit(PL_CSS_ADMIN_URL) . 'shortcodes/all.css');
			self::register_enqueue_if_not('placester-widget-chosen', trailingslashit(PL_JS_URL) . 'lib/chosen/chosen.css');
		}
		
		if ($hook == 'placester_page_placester_settings_crm') {
			self::register_enqueue_if_not('crm', trailingslashit(PL_CSS_ADMIN_URL) . 'crm.css');	
		}
	}

	public static function customizer() {
		self::register_enqueue_if_not('customizer-css', trailingslashit(PL_CSS_URL) . 'customizer.css');
		self::register_enqueue_if_not('onboard-css', trailingslashit(PL_CSS_URL) . 'onboard.css');
		self::register_enqueue_if_not('jquery-ui', trailingslashit(PL_JS_LIB_URL) . 'jquery-ui/css/smoothness/jquery-ui-1.8.17.custom.css');
		self::register_enqueue_if_not('global-css', trailingslashit(PL_CSS_URL) . 'global.css');
	}

	private static function register_enqueue_if_not($name, $path, $dependencies = array()) {
		if (!wp_style_is($name, 'registered')) {
			wp_register_style($name, $path, $dependencies);		
		}

		if ( !wp_style_is($name, 'queue') ) {
			wp_enqueue_style($name);		
		}	
	}

// end of class
}