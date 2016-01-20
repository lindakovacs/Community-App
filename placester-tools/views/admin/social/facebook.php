    <?php 
    // does authentication and authorization and tries to get the FB object with the profile
    if( self::get_facebook_object() ): 
    		$profile = self::get_profile(); ?>
    
    	<p>You are currently logged in with Facebook as <strong><a href="<?php echo $profile['link']?>" title="<?php _e('See your profile in facebook') ?>" target="_blank"><?php echo $profile['name'] ?></a></strong>.</p>
    	<p><a href="<?php echo self::$admin_redirect_uri; ?>&logout_clear=facebook">Logout from Facebook</a></p>
    <?php else: ?>
    <p> <?php self::fb_print_login_url(); ?> </p>
    <?php endif; 