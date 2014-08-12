<?php 

PL_Js_Helper::init();

class PL_Js_Helper {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin') );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend') );
		add_action( 'admin_head', array(__CLASS__, 'admin_menu_url') );
		add_action( 'customize_controls_enqueue_scripts', array(__CLASS__, 'customizer') );
	}

	public static function admin ($hook) {
		// Inject premium themes logic into the themes admin page when visiting from any site on the hosted env...
		if ($hook == 'themes.php' && defined('HOSTED_PLUGIN_KEY')) {
			self::register_enqueue_if_not('premium', trailingslashit(PL_JS_ADMIN_URL) . 'premium.js', array('jquery'));

			// Print global JS var containing premium theme list...
			global $PL_CUSTOMIZER_THEMES;
			ob_start();
			?>
			<script type="text/javascript"> 
				var pl_premThemes = [];
				<?php foreach ( $PL_CUSTOMIZER_THEMES['Premium'] as $name => $template ): ?>
					pl_premThemes.push("<?php echo $template; ?>");
				<?php endforeach; ?>
			</script>
			<?php
			echo ob_get_clean();
			
			// Launch dialog after theme is switched...
			if ( isset($_GET['activated']) && $_GET['activated'] == 'true' ) {
				PL_Bootup::theme_switch_user_prompt();
				self::register_enqueue_if_not('theme-switch', trailingslashit(PL_JS_ADMIN_URL) . 'theme-switch.js', array('jquery-ui-core', 'jquery-ui-dialog'));
	    	}

			// Don't load any other scripts...
			return;
		}

		// NOTE:  This ensures that pages with the proper hook prefix make it past this point... (i.e., only plugin admin pages)
		if (strpos($hook, 'placester_page_placester_') === false) { return; }

		// Load JS available to all of the plugin's pages...
		self::register_enqueue_if_not('global', trailingslashit(PL_JS_ADMIN_URL) . 'global.js', array('jquery'));
		// Try to make old IE look decent
		self::register_enqueue_if_not('modernizr', trailingslashit( PLS_JS_URL ) . 'libs/modernizr/modernizr.min.js' , array(), '2.6.1');
		
		
		if ($hook == 'placester_page_placester_properties') {
			self::register_enqueue_if_not('datatables', trailingslashit(PL_JS_LIB_URL) . 'datatables/jquery.dataTables.js', array('jquery'));
			self::register_enqueue_if_not('my-listings', trailingslashit(PL_JS_ADMIN_URL) . 'my-listings.js', array('jquery-ui-dialog', 'jquery-ui-datepicker'));
		}

		if ($hook == 'placester_page_placester_property_add') {
			// need ui-widget that is compatable w/ fileupload
			wp_deregister_script('jquery-ui-widget');
			wp_deregister_script('jquery-ui-mouse');
			wp_enqueue_script('jquery-ui-widget', trailingslashit(PL_JS_LIB_URL) . 'blueimp/js/vendor/jquery.ui.widget.js', array('jquery'), '1.10.3');
			self::register_enqueue_if_not('blueimp-iframe', trailingslashit(PL_JS_LIB_URL) . 'blueimp/js/jquery.iframe-transport.js', array('jquery'));
			self::register_enqueue_if_not('blueimp-file-upload', trailingslashit(PL_JS_LIB_URL) . 'blueimp/js/jquery.fileupload.js', array('jquery-ui-widget'));
			self::register_enqueue_if_not('add-listing', trailingslashit(PL_JS_ADMIN_URL) . 'add-listing.js', array('jquery-ui-datepicker'));
		}

		if ($hook == 'placester_page_placester_theme_gallery') {
			self::register_enqueue_if_not('theme-gallery', trailingslashit(PL_JS_ADMIN_URL) . 'theme-gallery.js', array('jquery'));
		}

		if ($hook == 'placester_page_placester_integrations') {
			self::register_enqueue_if_not('integration', trailingslashit(PL_JS_ADMIN_URL) . 'integration.js', array('jquery'));
		}

		if ($hook == 'placester_page_placester_settings_lead_capture') {
			self::register_enqueue_if_not('lead-capture', trailingslashit(PL_JS_ADMIN_URL) . 'lead-capture/general.js', array('jquery-ui-core', 'jquery-ui-dialog'));
		}

		if ($hook == 'placester_page_placester_settings') {
			self::register_enqueue_if_not('settings', trailingslashit(PL_JS_ADMIN_URL) . 'settings/general.js', array('jquery-ui-core', 'jquery-ui-dialog'));
		}

		if ($hook == 'placester_page_placester_settings_polygons') {
			self::register_enqueue_if_not('settings', trailingslashit(PL_JS_ADMIN_URL) . 'settings/polygon.js', array('jquery'));
			self::register_enqueue_if_not('new-colorpicker', trailingslashit(PL_JS_LIB_URL) . 'colorpicker/js/colorpicker.js', array('jquery'));
			self::register_enqueue_if_not('google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false', array('jquery'));
			self::register_enqueue_if_not('text-overlay', trailingslashit(PL_JS_LIB_URL) . 'google-maps/text-overlay.js', array('jquery'));
			self::register_enqueue_if_not('datatables', trailingslashit(PL_JS_LIB_URL) . 'datatables/jquery.dataTables.js', array('jquery'));
		}

		if ($hook == 'placester_page_placester_settings_international') {
			self::register_enqueue_if_not('settings', trailingslashit(PL_JS_ADMIN_URL) . 'settings/international.js', array('jquery'));	
		}

		if ($hook == 'placester_page_placester_settings_filtering') {
			self::register_enqueue_if_not('settings', trailingslashit(PL_JS_ADMIN_URL) . 'settings/filtering.js', array('jquery'));
		}

		if ($hook == 'placester_page_placester_settings_client') {
			self::register_enqueue_if_not('settings-client', trailingslashit(PL_JS_ADMIN_URL) . 'settings/client.js', array('jquery'));
		}

		// Shortcodes and Shortcode Templates
		if ($hook == 'placester_page_placester_shortcodes_shortcode_edit') {
			self::register_enqueue_if_not('shortcodes-admin', trailingslashit(PL_JS_ADMIN_URL) . 'shortcodes/all.js', array('jquery-ui-dialog','jquery-ui-datepicker'));
			self::register_enqueue_if_not('datatable', trailingslashit(PLS_JS_URL) . 'libs/datatables/jquery.dataTables.js' , array('jquery'), NULL, true);
			self::register_enqueue_if_not('featured-listing', trailingslashit(PLS_OPTRM_URL) . 'js/featured-listing.js', array('jquery'));
			
			wp_localize_script('shortcodes-admin', 'autosaveL10n', array(
				'saveAlert' => __('The changes you made will be lost if you navigate away from this page.')
			));
		}
		if ($hook == 'placester_page_placester_shortcodes_template_edit') {
			self::register_enqueue_if_not('shortcodes-admin', trailingslashit(PL_JS_ADMIN_URL) . 'shortcodes/all.js', array('jquery-ui-dialog'));
			self::register_enqueue_if_not('codemirror', trailingslashit(PL_JS_LIB_URL) . 'codemirror/codemirror.js');
			self::register_enqueue_if_not('codemirror-foldcode', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/foldcode.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-foldgutter', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/foldgutter.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-brace-fold', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/brace-fold.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-xml-fold', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/xml-fold.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-xml', trailingslashit(PL_JS_LIB_URL) . 'codemirror/mode/xml/xml.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-css', trailingslashit(PL_JS_LIB_URL) . 'codemirror/mode/css/css.js', array('codemirror'));
				
			wp_localize_script('shortcodes-admin', 'autosaveL10n', array(
				'saveAlert' => __('The changes you made will be lost if you navigate away from this page.')
			));
		}
		
		// Listing customizer
		if ($hook == 'placester_page_placester_shortcodes_listing_template_edit') {
			self::register_enqueue_if_not('listing-customizer', trailingslashit(PL_JS_ADMIN_URL) . 'listing-customizer.js', array('jquery-ui-dialog'));
			self::register_enqueue_if_not('codemirror', trailingslashit(PL_JS_LIB_URL) . 'codemirror/codemirror.js');
			self::register_enqueue_if_not('codemirror-foldcode', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/foldcode.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-foldgutter', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/foldgutter.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-brace-fold', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/brace-fold.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-xml-fold', trailingslashit(PL_JS_LIB_URL) . 'codemirror/addon/fold/xml-fold.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-xml', trailingslashit(PL_JS_LIB_URL) . 'codemirror/mode/xml/xml.js', array('codemirror'));
			self::register_enqueue_if_not('codemirror-css', trailingslashit(PL_JS_LIB_URL) . 'codemirror/mode/css/css.js', array('codemirror'));
		
			wp_localize_script('listing-customizer', 'autosaveL10n', array(
				'saveAlert' => __('The changes you made will be lost if you navigate away from this page.')
			));
		}
		
		if ($hook == 'placester_page_placester_settings_crm') {
			self::register_enqueue_if_not('crm', trailingslashit(PL_JS_ADMIN_URL) . 'crm.js', array('jquery'));
			self::register_enqueue_if_not('datatables', trailingslashit(PL_JS_LIB_URL) . 'datatables/jquery.dataTables.js', array('jquery'));	
		}
		
		// If no API key is set or somehow we have an invalid one, load the following JS files for use by the wizard on ANY plugin settings page...
		$pls_whoami = PL_Helper_User::whoami();
		if (!PL_Option_Helper::api_key() || empty($pls_whoami)) {
			global $i_am_a_placester_theme;
			self::register_enqueue_if_not('sign-up', trailingslashit(PL_JS_ADMIN_URL) . 'sign-up.js', array('jquery-ui-core', 'jquery-ui-dialog'));
			wp_localize_script('sign-up', 'pl_signup_data', array('placester_theme' => $i_am_a_placester_theme, 'mls_int' => false));
		}
	}

	public static function admin_menu_url () {
		?>
			<script type="text/javascript">
				var adminurl = '<?php echo ADMIN_MENU_URL; ?>';
				var siteurl = '<?php echo site_url(); ?>';
			</script>
		<?php
	}

	public static function frontend () {
		self::register_enqueue_if_not('general', trailingslashit(PL_JS_PUB_URL) . 'general.js', array('jquery'));
		// listings
		self::register_enqueue_if_not('datatables', trailingslashit(PL_JS_LIB_URL) . 'datatables/jquery.dataTables.js', array('jquery'));
		// favorites/contact form
		self::register_enqueue_if_not('membership', trailingslashit(PL_JS_PUB_URL) . 'membership.js', array('jquery'));

		if ( PL_Option_Helper::get_demo_data_flag() && current_user_can('manage_options') ) {
			self::register_enqueue_if_not('infobar', trailingslashit(PL_JS_PUB_URL) . 'infobar.js', array('jquery'));
		}

		if ( defined('PL_ANALYTICS_SCRIPT_URL') && PL_Analytics::can_collect() ) {
			self::register_enqueue_if_not('analytics', PL_ANALYTICS_SCRIPT_URL);
		}
	}

	public static function customizer() {
		self::register_enqueue_if_not('customizer', trailingslashit(PL_JS_ADMIN_URL) . 'customizer/customizer.js', array('jquery'));
		if ( PL_Customizer_Helper::is_onboarding() ) {
			self::register_enqueue_if_not('onboard', trailingslashit(PL_JS_ADMIN_URL) . 'customizer/onboard.js', array('jquery'));
		}

		self::register_enqueue_if_not('global', trailingslashit(PL_JS_ADMIN_URL) . 'global.js', array('jquery-ui-core', 'jquery-ui-dialog'));

		if ( isset($_GET['theme_changed']) && $_GET['theme_changed'] == 'true' && !PL_Customizer_Helper::is_onboarding() ) {
			PL_Bootup::theme_switch_user_prompt();
			self::register_enqueue_if_not('theme-switch', trailingslashit(PL_JS_ADMIN_URL) . 'theme-switch.js', array('jquery-ui-core', 'jquery-ui-dialog'));
		}
	}

	public static function register_enqueue_if_not($name, $path, $dependencies = array(), $version = null, $in_footer = false) {
		if (!wp_script_is($name, 'registered')) {
			if (!$version) {
				$pos = strpos($path, PL_PARENT_URL);
				if ($pos === 0) {
					$fpath = PL_PARENT_DIR . substr($path, strlen(PL_PARENT_URL));
					if (file_exists($fpath)) {
						$version = filemtime($fpath);
					}
				}
			}
			wp_register_script($name, $path, $dependencies, $version, $in_footer);
		}

		if (!wp_script_is($name, 'queue')) {
			wp_enqueue_script($name);
		}
	}

	//end class
}