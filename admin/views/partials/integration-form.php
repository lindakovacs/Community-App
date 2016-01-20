<?php 
	$mls_list = PL_Integration_Helper::mls_list(); 
	$whoami = PL_Helper_User::whoami();
	// error_log(serialize($whoami));

	$phone = !empty($whoami['phone']) ? $whoami['phone'] : (!empty($whoami['user']['phone']) ? $whoami['user']['phone'] : '');
	$email = !empty($whoami['email']) ? $whoami['email'] : (!empty($whoami['user']['email']) ? $whoami['user']['email'] : '');
?>

<?php if ( isset($wizard) && $wizard ): ?>
  <p class="message">Pick your MLS, then enter your office name and agent id to get started. Typically integrations take around 3 to 5 business takes, but can vary. If you need help, or have questions, please contact support via <a href="mailto:support@placester.com">support@placester.com</a> or at (800) 728-8391.</p>	
  <div class="clear"></div>
<?php endif; ?>

<div class="ajax_message" id="rets_form_message"></div>

<div class="rets_form" <?php if ( isset($no_form) && $no_form ) { echo 'id="pls_integration_form"'; } ?>>

<?php if (!isset($no_form)): ?>
  <form id="pls_integration_form" <?php if ( isset($wizard) && $wizard ) { echo 'class="wizard-mls"'; } ?>>
<?php endif; ?>

  	<div class="row">
	  <div class="info">
		<h3>MLS Provider</h3>
		<p>Pick which MLS provides your listing data.</p>
	  </div>
	  <div class="elements">
	  	<p>
		  <strong>Email us at <a href="mailto:support@placester.com">support@placester.com</a> if you don't see your MLS listed.</strong>
		</p>

		<select id="mls_id" name="mls_id">
		  <option value=""> --- </option>
		  <?php if (is_array($mls_list)): ?>
  		  <?php foreach ($mls_list as $mls_group => $mls_arr): ?>
  		    <optgroup label="<?php echo $mls_group; ?>">
  		      <?php foreach ($mls_arr as $mls_pair): ?>
  		      	<option value="<?php echo $mls_pair[1]; ?>"><?php echo $mls_pair[0]; ?></option>
  		      <?php endforeach; ?>
  		    </optgroup>
  		  <?php endforeach; ?>
		  <?php endif ?>
		</select>
	  </div>	
	</div>

	<div class="row">
	  <div class="info">
	    <h3>Office Name</h3>
	    <p>The name of your office or brokerage.</p>
	  </div>
	  <div class="elements">
	    <input id="office_name" name="office_name" size="30" type="text" />
	  </div>
	</div>
  
	<div class="row">
	  <div class="info">
	    <h3>Agent ID</h3>
	    <p>Unique ID used to login to your MLS.</p>
	  </div>
	  <div class="elements">
	    <input id="feed_agent_id" name="feed_agent_id" size="30" type="text" />
	  </div>
	</div>

  	<div class="row">
	  <div class="info">
	    <h3>Phone Number</h3>
		<p>This will help us provide prompt support to get your integration setup.</p>
	  </div>
	  <div class="elements">
		<input id="phone" name="phone" size="30" type="text" value="<?php echo $phone ?>">
	  </div>
    </div>

	<div class="row">
	  <div class="info">
	    <h3>Email Address</h3>
	    <p>This will help us provide prompt support to get your integration setup.</p>
	  </div>
	  <div class="elements">
	    <input id="broker_email" name="broker_email" size="30" type="text" value="<?php echo $email ?>">
      </div>
	</div>

  <?php if ( isset($submit) && $submit ): ?>
    <div class="row">
      <input type="submit" class="button-primary" />
    </div>
  <?php endif; ?>

<?php if (!isset($no_form)): ?>
  </form>
<?php endif; ?>

</div>