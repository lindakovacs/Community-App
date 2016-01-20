<div class="googleplus-wrapper">
	<style type="text/css">
		#pls_googleplus_id { width: 250px; }
	</style>
	<form id="pls_settings_form" action="options.php" method="POST">
			<?php settings_fields( self::$social_setting_key ) ?>
			<?php do_settings_sections( 'placester_social' ) ?>
			
			<input type="submit" value="Save" />
	</form> <!-- end of #dxtemplate-form -->
</div>