<?php
	// $whoami = PL_Helper_User::whoami();

	// $org_zip_exists = ( isset($whoami['location']) && isset($whoami['location']['postal']) && !empty($whoami['location']['postal']) );
	// $user_zip_exists = ( isset($whoami['user']) && isset($whoami['user']['location']) && isset($whoami['user']['location']['postal']) && !empty($whoami['user']['location']['postal']) );

	// $zip = ( $org_zip_exists ? $whoami['location']['postal'] : ( $user_zip_exists ? $whoami['user']['location']['postal'] : '02114' ) );
?>

<div id="demo_data_wizard">
	<p class="message">Display fictional listings <!-- from the <span><?php //echo $zip; ?></span> area --> on your site so that you can immediately test the look-and-feel.</p>	
	<div class="clear"></div>
	<!-- 
	<input type="hidden" id="demo_zip" name="demo_zip" value="<?php //echo $zip; ?>" />
	<div>
		<div id="map_canvas" style="height: 300px; overflow: hidden"></div>
	</div>
	 -->
</div>
