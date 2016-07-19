<?php if (PL_Option_Helper::api_key()): ?>
<p class="message"><b>Note:</b><i> Changing your Placester API Key will completely switch all the listings on your website and delete all your current listing pages. <strong>This might not be what you want.</strong></i></p>
<?php endif ?>
<p>Please enter a valid Placester.com api key. Here's how to find one:</p>
<ol class="existing_placester_steps">
	<li>Login to Placester.com <a href="https://placester.com/user/login" target="pl_help">here</a></li>
	<li>Navigate to your user api keys <a href="https://placester.com/user/apikeys" target="pl_help">here</a></li>
	<li>Copy your api key</li>
	<li>Paste it in the box below</li>
</ol>
<div>
	<div id="api_key_form">
  <div id="api_key_message"></div>
  <label for="api_key">Placester.com API Key</label>
  <input type="text" name="api_key" class="existing_placester_modal_api_key" id="existing_placester_modal_api_key" placeholder="Enter API Key" />
  <div id="api-key-message-icon"></div>
	</div>
</div>