/* 
 * Script for handling activation of premium themes from themes admin panel (on hosted env)
 */

jQuery(document).ready(function($) {

	function isPremiumTheme(template) {
		isPrem = ( pl_premThemes && pl_premThemes.indexOf(template) != -1 );
		return isPrem;
	}

	// Intercept any theme activation link click to handle premium theme logic... 
	$('a.activatelink').on('click', function (event) {
		event.preventDefault();

		// Remove any latent error messages...
		$('#message.error').remove();

		// Get params from activation link's href...
		var actHref = this.href; 
		var queryStr = actHref.slice( actHref.indexOf('?') + 1 );
		var rawArgs = queryStr.split('&');

		var argMap = [];
		for (var i = 0; i < rawArgs.length; ++i) {
			var param = rawArgs[i].split('=');
			argMap[param[0]] = param[1];
		}

		if ( argMap['template'] && isPremiumTheme(argMap['template']) ) {
			// console.log('Trying to activate premium theme...');
			var success_callback = function () { window.location.href = actHref; }
			var failure_callback = function () {
				// Construct error message...
				var msg = '<h3>Sorry, your account isn\'t eligible to use Premium themes.</h3>';
			  	msg += '<h3>Please <a href="https://placester.com/subscription">Upgrade Your Account</a> or call us with any questions at (800) 728-8391.</h3>';

				$('#current-theme').after('<div id="message" class="error">' + msg + '</div>');
			}

			// Check user's subscription status and act accordingly...
			$.post(ajaxurl, {action: 'subscriptions'}, function (response) {
				// console.log(response);
				if (response && response.plan && response.plan == 'pro') {
					success_callback();
				} 
				else {
					failure_callback();
				};
			},'json');	
		}
		else {
			// Follow the activation link as was originally intended...
			window.location.href = actHref;
		}
	});

});