function parse_validation (response) {
	$ = jQuery;
	if (response && response['validations']) {
		var item_messages = [];
		for(var key in response['validations']) {
			var item = response['validations'][key];
			if (typeof item == 'object') {
				for( var k in item) {
					if (typeof item[k] == 'string') {
						var message = '<span class="red">' + response['human_names'][key] + ' ' + item[k] + '</span>';
					} else {
						var message = '<span class="red">' + response['human_names'][k] + ' ' + item[k].join(',') + '</span>';
					}
					item_messages.push(message);
				}
			} else {
				var message = '<span class="red">'+item[key].join(',') + '</span>';
				item_messages.push(message);
			}
		}
		return item_messages;	
	}
}

function check_api_key (api_key) {
	$ = jQuery;
	$('#api_key_message').hide();

	var data = {action : "set_placester_api_key",api_key: api_key};
	$('#api_key_message').removeClass('red');
	$('#api_key_message').html('Checking....').show().addClass('green');

	$.post(ajaxurl, data, function (response) {
		if (response && response.message) {
			if (response.result) {
				$('#api_key_message').html("You've successfully changed your Placester API Key.").show().removeClass('red').addClass('green');
				$('#api-key-message-icon').show().addClass('green');
				$('#api_key_form #existing_placester_modal_api_key').addClass('green');
				setTimeout(function () { window.location.reload(true); }, 1000);
			} 
			else {
				$('#api_key_message').html(response.message).show().removeClass('green').addClass('red');
				$('#api-key-message-icon').show().removeClass('green').addClass('red');
				$('#existing_placester_modal_api_key').removeClass('green').addClass('red');
			}
		}
	}, 'json');
}

function new_sign_up (success_callback) {
	$ = jQuery;
	var email = $('input#email').val();
	
	$('#loading_gif').show();
	$('#api_key_success').html('Checking...').show();
	$('#api_key_validation').html('');
  	$('input#email').removeClass('green').removeClass('red');

	$.post(ajaxurl, {action: 'create_account', email: email}, function (response) {
		if (response) {
			console.log(response);
			if (response.validations) {
				// Instrument...
				pls_track_event("SignUp: Validation issue on signup");
				
				// Display validation message
				var message = parse_validation(response);
				
				$('#api_key_success').html('');
				$('#api_key_validation').html(message.join(', ')).show();
				$('input#email').removeClass('green').addClass('red');
				$('#loading_gif').hide();
			} 
			else if (response.api_key) {
				$('#api_key_success').html('Success! Setting up plugin.');
				$('input#email').removeClass('red').addClass('green');

        		// Instrument...
				pls_track_event("Registration - Account Created");
        		
        		$.post(ajaxurl, {action: 'set_placester_api_key', api_key: response.api_key}, function (response) {
		          	if (response.result) {
		          		// Display success message
			            var msg = (response['message']) ? response['message'] : '';
			            $('#api_key_success').html(msg).show();
            		
            			// Instrument...
	            		pls_track_event("SignUp: API key installed");
	            		
	           			// API key was successfully created AND set, ok to move-on to the integration dialog...
	           			if (success_callback) { success_callback(); }
         			}
         			else {
         				$('#api_key_success').html('');
         				var msg = (response['message']).replace('API Key', 'Account');
						$('#api_key_validation').html(msg).show();
						$('input#email').removeClass('green').addClass('red');
						$('#loading_gif').hide();
         			}
        		}, 'json');	
			}
		}
	}, 'json');
}

/*
 * Checks if input is a valid North American or internationally formatted phone number
 */
function validate_phone_number (phone) {

    // North American Regex
    var regex_NA = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;

    // International Regex
    var regex_int = /^\+(?:[0-9] ?){6,14}[0-9]$/;

    var valid = ( (regex_NA.test(phone) || regex_int.test(phone)) ? true : false );
    return valid;
}

function validate_email_address (email) {

    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    return re.test(email);
} 

/*
 * Helper for mixpanel events
 */
function pls_track_event (msg, data) {
	if (window.mixpanel) {
		mixpanel.track(msg, data);
	}
}
