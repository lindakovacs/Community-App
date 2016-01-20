jQuery(document).ready(function($) {

	var integration_success_callback = function () {
		jQuery('#integration_wizard').dialog("close");
		prompt_demo_data();
	};

	var integration_buttons = {
		1 : {
				text: "Skip Integration Set Up",
				click: function() {
					 $(this).dialog( "close" );
					 prompt_demo_data();
				}
			},
		2 : {
				text: "Submit",
				id: 'submit_integration_button',
				click: function() {
					 submit_handler(integration_success_callback);
				}
			}
	};

	$('#pls_integration_form').live('submit', function(event) {
		event.preventDefault();

		var refresh_page = function () {
			setTimeout(function () {
				window.location.href = window.location.href;
			}, 500);
		}

		submit_handler(refresh_page);	
	});

	function submit_handler (success_callback) {
		$('#rets_form_message').removeClass('red');
		$('#message.error').remove();

		$.each($('#pls_integration_form .invalid'), function(i, elem) {
			$(elem).removeClass('invalid');
		});

        $('#rets_form_message').html('');
        var errors = [];

        // Error checking...
        if ( $('#mls_id').length != 0 && $('#mls_id').val() == '' ) {
            $('#mls_id').addClass('invalid');
            $('#mls_id').closest('div .row').find('h3').first().addClass('invalid');
            errors.push('Please select an MLS');
        }
        if ( $('#office_name').length != 0 && $('#office_name').val().length < 2 ) {
            $('#office_name').addClass('invalid');
            $('#office_name').closest('div .row').find('h3').first().addClass('invalid');
            errors.push('Please enter a valid office name');
        }
        if ( $('#feed_agent_id').length != 0 && $('#feed_agent_id').val().length < 2 ) {
            $('#feed_agent_id').addClass('invalid');
            $('#feed_agent_id').closest('div .row').find('h3').first().addClass('invalid');
            errors.push('Please enter a valid agent id');
        }
		if ( $('#phone').length != 0 && !validate_phone_number($('#phone').val()) ) {
			$('#phone').addClass('invalid');
			$('#phone').closest('div .row').find('h3').first().addClass('invalid');
            errors.push('Please enter a valid phone number');
		}
        if ( $('#broker_email').length != 0 && !validate_email_address($('#broker_email').val()) ) {
            $('#broker_email').addClass('invalid');
            $('#broker_email').closest('div .row').find('h3').first().addClass('invalid');
            errors.push('Please enter a valid email address');
        }
        if (errors.length) {
            var error = errors.join('<br/>');
            $('#pls_integration_form').prepend('<div id="message" class="error"><h3>'+error+'</h3></div>');
            return;
        }

        $('#rets_form_message').html('Checking Account Status...');

        $.post(ajaxurl, {action: 'subscriptions'}, function(data, textStatus, xhr) {
		  // console.log(data);
		  if (data && data.plan && data.plan == 'pro') {
		  	check_mls_credentials(success_callback);
		  } else {
		  	// console.log('not eligible');
		  	var msg = '<h3>Sorry, your account isn\'t eligible to link with an MLS.</h3>';
		  	msg += '<h3>Please <a href="https://placester.com/subscription">Upgrade Your Account</a> or call us with any questions at (800) 728-8391.</h3>';
		  	$('#rets_form_message').html('');
			$('#pls_integration_form').prepend('<div id="message" class="error">' + msg + '</div>');
		  };
		},'json');	
	}

	function check_mls_credentials (success_callback) {
		$('#rets_form_message').html('Checking RETS information...');
		
		var form_values = {action: 'create_integration'};
		var form_serialized = $('#pls_integration_form').serializeArray();
		
		if (form_serialized.length > 0) {
			$.each(form_serialized, function (i, field) {
	    		form_values[field.name] = field.value;
	        });
		}
		else { // Submitted from customizer...
			$.each($('#pls_integration_form').find('input, select'), function (i, elem) {
				form_values[$(elem).attr('name')] = $(elem).val();
			});		
		}

        // console.log(form_values);

		$.post(ajaxurl, form_values, function(data, textStatus, xhr) {
		  	// console.log(data);
		  	var form = $('#pls_integration_form');
			if (data && data.result) {
				$('#rets_form_message').html(data.message);
				if (success_callback) { success_callback(); }
			} else {
				var item_messages = [];
				for(var key in data['validations']) {
					var item = data['validations'][key];
					if (typeof item == 'object') {
						for( var k in item) {
							if (typeof item[k] == 'string') {
								var message = '<li class="red">' + data['human_names'][key] + ' ' + item[k] + '</li>';
							} else {
								var message = '<li class="red">' + data['human_names'][k] + ' ' + item[k].join(',') + '</li>';
							}
							$("#" + key + '-' + k).prepend(message);
							item_messages.push(message);
						}
					} else {
						var message = '<li class="red">'+item[key].join(',') + '</li>';
						$("#" + key).prepend(message);
						item_messages.push(message);
					}
				} 
				$(form).prepend('<div id="message" class="error"><h3>'+ data['message'] + '</h3><ul>' + item_messages.join(' ') + '</ul></div>');
				$('#rets_form_message').html('');
			};

		}, 'json');
	}

	function display_cancel_message () {
		$('#rets_form_message').html('');
		$('#pls_integration_form').prepend('<div id="message" class="error"><h3>Sorry, this feature requires a premium subscription</h3><p>However, you can test the MLS integration feature for free by creating a website at <a href="https://placester.com" target="_blank">placester.com</a></p></div>');
	}

	function prompt_integration_local () {
		// TODO: Add spinner/loading prompt...
		$.post(ajaxurl, {action:"new_integration_view"}, function (result) {
		  	if (result) {
				// If it doesn't already exist, create container for the wizard dialog...
				if ( $('#integration_wizard').length == 0 ) {
					$('body').append('<div id="integration_wizard"></div>');
				}
				// Render...
				$('#integration_wizard').html(result);
				$( "#integration_wizard" ).dialog({
					autoOpen: true,
					draggable: false,
					modal: true,
					title: 'Set Up an MLS Integration for your Website',
					width: 810,
					minHeight: 500,
					buttons: integration_buttons
				});
		  	}
		});
	}

	// Expose function to global namespace
	prompt_integration = prompt_integration_local;
});