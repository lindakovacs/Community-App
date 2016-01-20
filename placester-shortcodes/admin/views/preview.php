<?php
/**
 * Used to preview a shortcode using parameters provided in the url
 * instead of usng a shortcode post object
 */
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


if (class_exists('PLS_Slideshow')) {
	PLS_Slideshow::enqueue();
}
//TODO: check if we need all these
add_action( 'wp_print_scripts', 'pls_print_info_var' );
wp_enqueue_script( 'jquery-placeholder', trailingslashit( PLS_JS_URL ) . 'libs/jquery-placeholder/jquery.placeholder.min.js' , array( 'jquery' ), '1.0.1', true );
wp_enqueue_script( 'listings-object', trailingslashit( PLS_JS_URL ) . 'scripts/listings.js' , array( 'jquery' ), '1.0.1', true );
wp_enqueue_script( 'get-listings-fav-ajax', trailingslashit( PLS_JS_URL ) . 'scripts/get-listings-fav-ajax.js' , array( 'jquery' ), NULL, true );
wp_enqueue_script( 'contact-widget', trailingslashit( PLS_JS_URL ) . 'scripts/contact.widget.ajax.js' , array( 'jquery' ), NULL, true );
wp_enqueue_script( 'client-edit-profile', trailingslashit( PLS_JS_URL ) . 'scripts/client-edit-profile.js' , array( 'jquery' ), NULL, true );
wp_enqueue_script( 'script-history', trailingslashit( PLS_JS_URL ) . 'libs/history/jquery.address.js' , array( 'jquery' ), NULL, true );
wp_enqueue_script( 'search-bootloader', trailingslashit( PLS_JS_URL ) . 'scripts/search-loader.js' , array( 'jquery' ), NULL, true );

add_filter('show_admin_bar', '__return_false');
remove_action('wp_head', 'placester_info_bar');

?><html style="margin-top: 0 !important; overflow: auto;">
	<head>
		<style type="text/css">
			body {
				margin-top: 0px;
				overflow: hidden;
			}
			.pls_embedded_widget_wrapper {
				overflow: hidden;
			}
			#full-search .form-grp:first-child {
				margin-top: 0px;
			}
			.pls_embedded_widget_wrapper .pls_search_form_listings {
				margin-bottom: 0px;
			}
			p {
				margin-top: 0px;
			}
		</style>
		<script type="text/javascript">
			var pl_general_widget = true;
		</script>
		<?php wp_head(); ?>
	</head>
	<body>

		<div class="pls_embedded_widget_wrapper">
			<?php
			echo do_shortcode( html_entity_decode($sc_str) );
			?>
		<div>

		<?php wp_footer();?>
	</body>
</html>
