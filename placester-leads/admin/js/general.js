jQuery(document).ready(function($) {

	$('#button_email_forwarding').on('click', function () {

		$('.pls-loading-overlay').show();
		
		var email_addresses = $('#forwarding_email_addresses').val();
		var invalid_address = first_invalid_address(email_addresses);
		var msg;

		if (invalid_address === "") {
			// Setup AJAX call to save addresses...
			var data = {};
			data.action = 'set_forwarding_addresses';
			data.email_addresses = email_addresses;

			$.post(ajaxurl, data, function (response) {
				// Optional stuff to do after success...
				msg = (response && response.success) ? "Forwarding email addresses successfully updated" : "There was an error while trying to save your request -- please wait a moment and try again";
				forwarding_email_forwarding_validation(msg);
			}, 'json');
		} 
		else {
			msg = invalid_address + " is not a valid email -- please correct it and resubmit";
			forwarding_email_forwarding_validation(msg);
		}
	});

	function first_invalid_address (email_addresses) {
		// Tokenize by commas...
		var email_array = email_addresses.split(',');

		if (email_array.length > 0) {
			for (var i = 0; i < email_array.length; i++) {
				var email = $.trim(email_array[i]);
				// If email address fails validation and is NOT blank, then it's invalid...
				if (!validate_email_address(email) && email !== "") {
					return email;
				}
			}
		}

		return "";
	}

	function forwarding_email_forwarding_validation (message, type) {

		$('.pls-loading-overlay').hide();
		
		var color = 'green';
		if (type == 'error') { color = 'red' };

		$('#email_validation').html('')
		$('#email_validation').html(message).css('color', color).show();

		setTimeout(function () {
			$('#email_validation').hide();			
		}, 3000);
	}

});