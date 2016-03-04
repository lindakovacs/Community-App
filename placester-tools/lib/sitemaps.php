<?php

PL_Sitemaps::init();

class PL_Sitemaps {

	private static $max_prop_entries = 500;
	private static $max_entries = 1000;
	private static $supported_taxonomies = array('state'=>'region', 'city'=>'locality', 'zip'=>'postal', 'neighborhood'=>'neighborhood');
	private static $current_listing = array();

	public static function init() {
		add_action('init', array(__CLASS__, 'register_sitemaps'));
		add_filter('wpseo_sitemap_index', array(__CLASS__, 'append_sub_sitemaps'));
		//add_action('wpseo_xmlsitemaps_config', array(__CLASS__, 'configure_sitemaps'));
	}

	public static function register_sitemaps() {
		global $wpseo_sitemaps;

		if ($wpseo_sitemaps) {
			$seo_options = get_wpseo_options();
			if (!empty($seo_options['entries-per-page'])) {
				self::$max_prop_entries = min(array(self::$max_prop_entries, $seo_options['entries-per-page']));
				self::$max_entries = $seo_options['entries-per-page'];
			}
			if (!isset($seo_options['post_types-property-not_in_sitemap']) || !$seo_options['post_types-property-not_in_sitemap']) {
				$wpseo_sitemaps->register_sitemap('pl-property', array(__CLASS__, 'property_details_sitemap'));
			}
			foreach (self::$supported_taxonomies as $tax=>$loc_type) {
				if (!isset($seo_options["taxonomies-$tax-not_in_sitemap"]) || !$seo_options["taxonomies-$tax-not_in_sitemap"]) {
					$wpseo_sitemaps->register_sitemap('pl-'.$tax, array(__CLASS__, 'taxonomy_sitemap'));
				}
			}
		}
	}

	public static function append_sub_sitemaps($sitemap_list) {
		global $wpseo_sitemaps;
		$seo_options = get_wpseo_options();

		// Make an API call to determine the total number of listings in a feed...
		$response = PL_Listing_Helper::results(array('limit'=>1, 'cachebuster'=>time()));
		$total = isset($response['total']) ? $response['total'] : 0;

		// Try to fetch from cache...
		$cache = new PL_Cache('sitemap_index');
		$sitemap_index = $cache->get($total, $seo_options);

		if (!empty($sitemap_index)) {
			// Cache hit -- return cached HTML...
			return $sitemap_index;
		}

		// Cache miss -- construct sitemap index...
		$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';
		$date = date('c');
		
		if (!empty($total)) {
			if (!isset($seo_options['post_types-property-not_in_sitemap']) || !$seo_options['post_types-property-not_in_sitemap']) {
				// property pages
				$count = $total;
				$n = ( $count > self::$max_prop_entries ) ? (int) ceil( $count / self::$max_prop_entries ) : 1;
				for ( $i = 0; $i < $n; $i++ ) {
					$count = ( $n > 1 ) ? $i + 1 : '';
					$sitemap_list .= self::format_sub_sitemap_entry($base, $date, 'pl-property', $count);
				}
			}
			// location taxonomy
			$response = PL_Listing_Helper::locations_for_options(false, true);
			foreach (self::$supported_taxonomies as $tax=>$loc_type) {
				if (!empty($response[$loc_type]) && (!isset($seo_options["taxonomies-$tax-not_in_sitemap"]) || !$seo_options["taxonomies-$tax-not_in_sitemap"])) {
					$count = count($response[$loc_type]);
					$n = ( $count > self::$max_entries ) ? (int) ceil( $count / self::$max_entries ) : 1;
					for ( $i = 0; $i < $n; $i++ ) {
						$count = ( $n > 1 ) ? $i + 1 : '';
						$sitemap_list .= self::format_sub_sitemap_entry($base, $date, 'pl-'.$tax, $count);
					}
				}
			}
		}

		// Cache constructed sitemap index for 12 hours...
		$cache->save($sitemap_list, PL_Cache::TTL_MID);

		return $sitemap_list;
	}

	private static function format_sub_sitemap_entry($base, $date, $type, $count='') {
		$sitemap_list = '<sitemap>' . "\n";
		$sitemap_list .= '<loc>' . home_url( $base . $type . '-sitemap' . $count . '.xml' ) . '</loc>' . "\n";
		$sitemap_list .= '<lastmod>' . htmlspecialchars( $date ) . '</lastmod>' . "\n";
		$sitemap_list .= '</sitemap>' . "\n";
		return $sitemap_list;
	}

	public static function property_details_sitemap($arg) {
		$sitemap = '';

		$n = (int)get_query_var('sitemap_n');
		$offset = ( $n > 1 ) ? ( $n - 1 ) * self::$max_prop_entries : 0;
		$rem = self::$max_prop_entries;

		// Try to fetch from cache...
		$cache = new PL_Cache('prop_sitemap');
		$sitemap = $cache->get($offset, $rem);

		if (!empty($sitemap)) {
			// Cache hit -- return cached HTML...
			self::finish_sitemap($sitemap);
			return;
		}

		// Cache miss -- construct sitemap for the given offset...
		$url_tmpl = PL_Pages::get_link_template();

		while ($rem > 0) {
			$args = array('offset'=>$offset, 'limit'=>min($rem, self::$max_prop_entries), 'sort_by'=>'access_id', 'sort_type'=>'asc');
			$response = PL_Listing_Helper::results($args);

			foreach($response['listings'] as $listing) {
				self::$current_listing = $listing['location'];
				$url = str_replace('%id%', $listing['id'], $url_tmpl);
				$url = preg_replace_callback('/%([^%]*)%/', array(__CLASS__, '_template_replace'), $url);
				$sitemap .= "\t<url>\n";
				$sitemap .= "\t\t<loc>".$url."</loc>\n";
				$sitemap .= "\t\t<lastmod>" . $listing['updated_at'] . "</lastmod>\n";
				$sitemap .= "\t\t<changefreq>weekly</changefreq>\n";
				$sitemap .= "\t\t<priority>0.6</priority>\n";
				$first = true;
				if (!empty($listing['images'])) {
					uasort($listing['images'], array(__CLASS__, '_image_sort'));
					foreach($listing['images'] as $key => $image) {
						$sitemap .= "\t\t<image:image>\n";
						$sitemap .= "\t\t\t<image:loc>" . esc_html($image['url']) . "</image:loc>\n";
						if ($first) {
							$sitemap .= "\t\t\t<image:geo_location>" . esc_html($listing['location']['address'].', '.$listing['location']['locality'].', '.$listing['location']['region'].', '.$listing['location']['postal']) . "</image:geo_location>\n";
							$first = false;
						}
						$sitemap .= "\t\t</image:image>\n";
					}
				}
				$sitemap .= "\t</url>\n";
			}

			// if we are getting chunks in less than the requested amount then loop till got what we want
			$count = count($response['listings']);
			if ($count == 0) {
				break;
			}
			$offset += $count;
			$rem -= $count;
		}

		// Cache constructed sitemap for 12 hours...
		$cache->save($sitemap, PL_Cache::TTL_MID);

		self::finish_sitemap($sitemap);
	}

	/**
	 * Build url with placeholders for empty fields. Should match the PL_Pages::get_url() functionality
	 */
	public static function _template_replace($arg) {
		return empty(self::$current_listing[$arg[1]]) ? '-' : preg_replace('/[^a-z0-9\-]+/', '-', strtolower(self::$current_listing[$arg[1]]));
	}

	public static function taxonomy_sitemap($arg) {
		$tax = explode('-', current_filter());
		$sitemap = '';
		$tmpls = PL_Taxonomy_Helper::get_permalink_templates();

		if (count($tax) == 2 && ($tax = $tax[1]) && isset(self::$supported_taxonomies[$tax]) && isset($tmpls[$tax])) {
			$loc_type = self::$supported_taxonomies[$tax];
			$url_tmpl = $tmpls[$tax];
			$n = (int)get_query_var('sitemap_n');
			$offset = ( $n > 1 ) ? ( $n - 1 ) * self::$max_entries : 0;
			$rem = self::$max_entries;
			$date = date('c', time() - 24 * 60 * 60);

			$response = PL_Listing_Helper::locations_for_options(false, true);

			if (!empty($response[$loc_type]) && count($response[$loc_type]) > $offset) {
				$rem = min(array($rem, count($response[$loc_type])-$offset));
				for (;$rem-- > 0; $offset++) {
					if (!empty($response[$loc_type][$offset])) {
						$url = str_replace("%$tax%", sanitize_title_with_dashes($response[$loc_type][$offset]), $url_tmpl);
						$sitemap .= "\t<url>\n";
						$sitemap .= "\t\t<loc>".$url."</loc>\n";
						$sitemap .= "\t\t<lastmod>" . $date . "</lastmod>\n";
						$sitemap .= "\t\t<changefreq>weekly</changefreq>\n";
						$sitemap .= "\t\t<priority>0.6</priority>\n";
						$sitemap .= "\t</url>\n";
					}
				}
			}
		}

		self::finish_sitemap($sitemap);
	}

	private static function finish_sitemap($content) {
		global $wpseo_sitemaps;

		$sitemap = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ';
		$sitemap .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ';
		$sitemap .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		$sitemap .= $content;
		$sitemap .= '</urlset>';
		$wpseo_sitemaps->set_sitemap($sitemap);
	}

	public static function configure_sitemaps() {
		global $wpseo_sitemaps, $wpseo_admin_pages;

		$content = '<div id="pl_sitemapinfo">';
		$content .= '<strong>Exclude property search pages</strong><br/>';
		$content .= '<p>' . __( 'Please check the appropriate box below if there\'s a property search type that you do <strong>NOT</strong> want to include in your sitemap:', 'wordpress-seo' ) . '</p>';
		$pt = get_post_type_object('property');
		$content .= $wpseo_admin_pages->checkbox( 'post_types-' . $pt->name . '-not_in_sitemap', 'Individual property pages' );
		foreach ( get_taxonomies(array('object_type'=>array('property')), 'objects' ) as $tax ) {
			$content .= $wpseo_admin_pages->checkbox( 'taxonomies-' . $tax->name . '-not_in_sitemap', 'Search pages for individual ' . $tax->labels->name);
		}
		$content .= '<br class="clear"/>';
		$content .= '</div>';
		ob_start();
		?>
		<script type="text/javascript"> 
		jQuery(document).ready(function () {
			jQuery("#enablexmlsitemap").change(function() {
				if (jQuery("#enablexmlsitemap").is(':checked')) {
					jQuery("#pl_sitemapinfo").css("display","block");
				} else {
					jQuery("#pl_sitemapinfo").css("display","none");
				}
			});
		});
		</script>
		<?php
		$content .= ob_get_clean();

		$wpseo_admin_pages->postbox('pl_xmlsitemaps', '', $content);
	}

	public function _image_sort($a, $b) {
		return $a['order'] > $b['order'];
	}
}