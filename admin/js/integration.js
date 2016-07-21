jQuery(document).ready(function($) {
	var pls_integration_form = $('#pls_integration_form');
	var rets_form_message = $('#rets_form_message');

	pls_integration_form.live('submit', function(event) {
		event.preventDefault();

		$('#integration_message.error').remove();

		$.each(pls_integration_form.find('.invalid'), function (i, elem) {
			$(elem).removeClass('invalid');
		});

		var errors = [];

		var mls_id = $('#mls_id');
		if (mls_id.length != 0 && mls_id.val() == '') {
			mls_id.addClass('invalid');
			mls_id.closest('div .row').find('h3').first().addClass('invalid');
			errors.push('Please select an MLS');
		}

		var office_name = $('#office_name');
		if (office_name.length != 0 && office_name.val().length < 2) {
			office_name.addClass('invalid');
			office_name.closest('div .row').find('h3').first().addClass('invalid');
			errors.push('Please enter a valid office name');
		}

		var feed_agent_id = $('#feed_agent_id');
		if (feed_agent_id.length != 0 && feed_agent_id.val().length < 2) {
			feed_agent_id.addClass('invalid');
			feed_agent_id.closest('div .row').find('h3').first().addClass('invalid');
			errors.push('Please enter a valid agent id');
		}

		var feed_agent_email = $('#feed_agent_email');
		if (feed_agent_email.length != 0 && !validate_email_address(feed_agent_email.val())) {
			feed_agent_email.addClass('invalid');
			feed_agent_email.closest('div .row').find('h3').first().addClass('invalid');
			errors.push('Please enter a valid email address');
		}

		var broker_email = $('#broker_email');
		if (broker_email.length != 0 && !validate_email_address(broker_email.val())) {
			broker_email.addClass('invalid');
			broker_email.closest('div .row').find('h3').first().addClass('invalid');
			errors.push('Please enter a valid email address');
		}

		var phone = $('#phone');
		if (phone.length != 0 && !validate_phone_number(phone.val())) {
			phone.addClass('invalid');
			phone.closest('div .row').find('h3').first().addClass('invalid');
			errors.push('Please enter a valid phone number');
		}

		if (errors.length) {
			pls_integration_form.prepend('<div id="integration_message" class="error"><h3>' + errors.join('<br/>') + '</h3></div>');
			return;
		}

		rets_form_message.html('Checking Account Status...');

		$.post(ajaxurl, {action: 'check_subscription'}, function (data, textStatus, xhr) {
			if (data && data.plan && data.plan == 'pro') {
				check_mls_credentials();
			}

			else {
				var msg = '<h3>Sorry, your account isn\'t eligible to link with an MLS.</h3>'
					+ '<h3>Please <a href="https://placester.com/subscription">Upgrade Your Account</a>.</h3>';

				rets_form_message.html('');
				pls_integration_form.prepend('<div id="integration_message" class="error">' + msg + '</div>');
			}
		}, 'json');
	});

	function check_mls_credentials () {
		rets_form_message.html('Checking RETS information...');

		var form_values = pls_integration_form.serializeArray();
		form_values.push({ name: 'action', value: 'create_integration' });

		$.post(ajaxurl, form_values, function(data, textStatus, xhr) {

			if (data && data.result) {
				rets_form_message.html(data.message);
				setTimeout(function () {
					window.location.href = window.location.href;
				}, 500);
			}

			else {
				var item_messages = [];
				var message;

				for(var key in data['validations']) {
					var item = data['validations'][key];

					if (typeof item == 'object') {
						for( var k in item) {
							if (typeof item[k] == 'string')
								message = '<li class="red">' + data['human_names'][key] + ' ' + item[k] + '</li>';
							else
								message = '<li class="red">' + data['human_names'][k] + ' ' + item[k].join(',') + '</li>';

							$("#" + key + '-' + k).prepend(message);
							item_messages.push(message);
						}
					}

					else {
						message = '<li class="red">'+item[key].join(',') + '</li>';
						$("#" + key).prepend(message);
						item_messages.push(message);
					}
				}

				$(pls_integration_form).prepend('<div id="integration_message" class="error"><h3>'+ data['message'] + '</h3><ul>' + item_messages.join(' ') + '</ul></div>');
				$(rets_form_message).html('');
			}
		}, 'json');
	}
});