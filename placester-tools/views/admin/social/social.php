<div id="icon-plugins" class="icon32"></div>

<div class="wrap">
    <h2>Social Publishing</h2>
    <p style="max-width: 600px;">
    	Login to the following social services to enable social publishing. 
    	The post edit page will contain a field for drafting your social media posts,
     	those posts will automatically be submitted the social services whenever the
      	blog post itself is published.
    </p>
    <h3>Twitter</h3>
    <?php do_action( 'pl_twitter_display' ); ?>
    
    <h3>Facebook</h3>
    <?php do_action( 'pl_facebook_display' ); ?>
    
    <h3>Google+</h3>
    <?php do_action( 'pl_googleplus_display' ); ?>
    
    
</div>