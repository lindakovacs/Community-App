<?php
/**
 * Update anything that needs to be updated from a previous version.
 * Runs each successive version to bring current version up to date:
 * Add function for the previous version if there has been a change that needs updating.
 * TODO: move the notifications to a common helper.
 */

PL_Updater::init();

class PL_Updater {
	
	private static $opt = 'placester_update_notices';
			
	public static function init() {
		$prev_ver = get_option('pl_plugin_version', PL_PLUGIN_VERSION);
		add_action('admin_notices', array('PL_Updater', 'admin_notices'));

		if ($prev_ver != PL_PLUGIN_VERSION) {
			$updates = get_class_methods('PL_Updater');
			$updates = preg_replace(array('/^_/','/([0-9]+)_/'), array('', '$1.'), $updates);
			usort($updates, 'version_compare');
			$notices = get_option(self::$opt, array());
    		foreach($updates as $update) {
				if (!is_numeric(substr($update,1,2)) || version_compare($prev_ver, $update)>0) continue;
				$func = 'PL_Updater::_'.str_replace('.', '_', $update);
				call_user_func($func);
		    	$notices[]= "Upgraded data to version $update";
    		}
		    update_option(self::$opt, $notices);
		}
	}

	public static function compare($a, $b) {
		return version_compare(substr(str_replace('_', '.', $a),1), substr(str_replace('_', '.', $b),1));
	}
	
	public static function admin_notices() {
		if ($notices = get_option(self::$opt)) {
			$plugin = get_plugin_data(trailingslashit(PLACESTER_PLUGIN_DIR).'placester.php');
			echo '<div class="updated"><p><em>'.$plugin['Name'].'</em> plugin:</p>';
			foreach ($notices as $notice) {
				echo "<p>$notice</p>";
			}
			echo '</div>';
			delete_option(self::$opt);
		}
	}

	private static function _1_1_12() {
		global $wpdb;
		// update old shortcode templates
		$template_opts = array('pls_search_map','pls_search_form','pls_search_listings','pls_pl_neighborhood','pls_listing_slideshow','pls_featured_listings','pls_static_listings');
		foreach($template_opts as $template_opt) {
			$query = "SELECT * FROM ".$wpdb->prefix."options WHERE option_name LIKE '".$template_opt."_%'";
			$results = $wpdb->get_results($query);
			$shortcode = substr($template_opt, 4);
			$fields = array('before_widget'=>'', 'after_widget'=>'', 'snippet_body'=>'', 'widget_css'=>'');
			foreach($results as $result) {
				$matches = array();
				if (preg_match('/^'.$template_opt.'_((?!list$)(.+))$/', $result->option_name, $matches)) {
					$val = get_option($result->option_name, '');
					if (!is_array($val)) {
						$val = array('shortcode'=>$shortcode, "title"=>$matches[1], 'snippet_body'=>$result->option_value);
						$val = array_merge($fields, $val);
						$query = "UPDATE ".$wpdb->prefix."options
							SET option_name='".$template_opt."__".$matches[1]."',
								option_value='".serialize($val)."'
							WHERE option_name='".$result->option_name."'";
						$results = $wpdb->get_results($query);
					}
				}
			}
			PL_Shortcode_CPT::build_tpl_list($shortcode);
		}
		// update shortcodes
		$shortcodes = array('pl_map'=>'search_map','pl_form'=>'search_form','pl_search_listings'=>'search_listings','pl_neighborhood'=>'pl_neighborhood','pl_slideshow'=>'listing_slideshow','featured_listings'=>'featured_listings','static_listings'=>'static_listings');
		$sc_attrs = PL_Shortcode_CPT::get_shortcode_attrs();
		$scs = get_posts(array('post_type'=>'pl_general_widget', 'numberposts'=>-1));
		foreach ($scs as $sc) {
			$postmeta = get_post_meta($sc->ID);
			if (!empty($postmeta['pl_post_type']) && empty($postmeta['shortcode']) && !empty($shortcodes[$postmeta['pl_post_type'][0]])) {
				$shortcode = $shortcodes[$postmeta['pl_post_type'][0]]; 
				update_post_meta($sc->ID, 'shortcode', $shortcode);
				if (($shortcode=='static_listings' || $shortcode=='search_listings') && !empty($postmeta['pl_static_listings_option'])) {
					update_post_meta($sc->ID, 'pl_filters', maybe_unserialize($postmeta['pl_static_listings_option'][0]));
				}
			}
		}
	}
}
