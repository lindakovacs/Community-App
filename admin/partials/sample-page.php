<?php 
/**
 * Appears in the final screen of the signup dialog
 */
?>
<div id="searchpage-inner" class="hide">
	<?php if ($page):?>
		<p class="major-subtitle">We have created a real estate search page for you. 
		Check it out <a href="<?php echo get_permalink($page->ID)?>" target="_blank" data-mixpanel="Registration - View search page">here</a></p>
		<p class="subtitle">The search is powered by Placester Shortcodes. 
		Placester Shortcodes allow you to customize the way your real estate search looks (or create new search pages). 
		Learn more about shortcodes <a class="link_shortcode_overview" href="https://placester.com/developers/placester-shortcode-overview/" target="_blank" data-mixpanel="Registration - Shortcodes overview">here</a> or to view them, click <a class="link_shortcode_edit" href="<?php echo admin_url('admin.php?page=placester_shortcodes_shortcode_edit') ?>" target="_blank" data-mixpanel="Registration - Shortcode edit">here</a>.</p>
	<?php else: ?>
		<p class="major-subtitle">Your Real Estate website is now set up. 
		Check out the home page <a href="<?php echo get_home_url()?>" target="_blank" data-mixpanel="Registration - View home page">here</a>.</p>
		<p class="subtitle">You can create additional search pages using shortcodes. Placester Shortcodes allow you to customize the way your real estate search looks (or create new search pages).</p> 
		<p class="subtitle">Learn more about shortcodes <a class="link_shortcode_overview" href="https://placester.com/developers/placester-shortcode-overview/" target="_blank" data-mixpanel="Registration - Shortcodes overview">here</a> or to view them, click <a class="link_shortcode_edit" href="<?php echo admin_url('admin.php?page=placester_shortcodes_shortcode_edit') ?>" target="_blank" data-mixpanel="Registration - Shortcode edit">here</a>.</p>
	<?php endif ?>
	<p class="subtitle"><span class="red">Right now we're showing example listings. 
	To turn this off, go <a class="link_demo_setting" href="<?php echo admin_url('admin.php?page=placester_settings') ?>" target="_blank" data-mixpanel="Registration - Turn off demo data">here</a>.</span></p>
</div>
