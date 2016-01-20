<?php
/**
 * Displays shortcode when embedded in an iframe using js embed
 */

function pl_template_drop_modernizr() {
	wp_dequeue_script('form');
}

function pl_template_add_modernizr() {
	wp_enqueue_script( 'modernizr', trailingslashit( PLS_JS_URL ) . 'libs/modernizr/modernizr.min.js' , array(), '2.6.1');
}


$preview = ( !empty($_GET['preview']) && $_GET['preview'] == 'true' ) ? true : false;
if( $preview ) {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}

$widget_cache = new PL_Cache("Embeddable_Widget");
global $post;

if( $widget_page = $widget_cache->get( $post->ID ) ) {
	echo $widget_page;
	return;
}

ob_start();

$widget_class = !empty($_GET['widget_class']) ? $_GET['widget_class'] : (!empty($sc_options['widget_class'])?$sc_options['widget_class']:'');

?>
<html style="margin-top: 0 !important; overflow: hidden;" class="<?php echo $widget_class ?>">
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
	<?php
	add_filter('show_admin_bar', '__return_false');
	add_action('wp_enqueue_scripts', isset( $drop_modernizr ) ? 'pl_template_drop_modernizr': 'pl_template_add_modernizr' );

	echo '<div class="pls_embedded_widget_wrapper">';
	echo do_shortcode( isset( $shortcode ) ? $shortcode : $post->post_content );
	echo '<div>';

	wp_footer();
 	?>
</body>
</html>
<?php
$widget_page = ob_get_clean();
$widget_cache->save( $widget_page );

echo $widget_page;