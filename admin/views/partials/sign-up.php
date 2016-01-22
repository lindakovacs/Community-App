<!-- 
<p class="message">You need to complete the set up wizard you can start using the Real Estate Website Builder plugin. It takes 2 minutes and ensures your real estate website will work properly.</p>	
<div class="clear"></div>
<p>This plugin turns your WordPress blog into a full featured real estate website. It allows you to create, edit, search, and display real estate listings. To get started, we need to confirm your email address. This email address will be used to save all the properties you enter.</p>
<div id="confirm_email">
	<div id="api_key_validation"></div>
	<div id="api_key_success"></div>
	<?php //PL_Form::generate_form( PL_Config::PL_API_USERS('setup', 'args'), array('method'=>'POST', 'include_submit' => false, 'wrap_form' => true) ); ?>
</div>
 -->
<div id="activate-plugin-dialog" class="">
    <p class="subtitle">To complete plugin activation, please confirm your email address.</p>
	<img id="loading_gif" src="<?php echo PL_ADMIN_CSS_URL . 'spinner.gif'; ?>"/>
    <input id="email" type="email" name="email" value="<?php echo $email; ?>" />
    <div id="api_key_validation"></div>
    <a href="https://placester.com/support/why-do-i-need-to-create-an-account/" target="pl_help" class="learn-more">Learn More</a>
    <div id="api_key_success"></div>
</div>
