jQuery(document).ready(function($) {

	if ( jQuery().dialog ) {
		$('#edit_profile').dialog({
			autoOpen: false,
			draggable: false,
			resizable: false,
			dialogClass: 'edit_profile_dialog',
			modal: true,
			width: 700,
			title: 'Update Contact Info',
			buttons: {
				'Cancel': function() {
					$(this).dialog('close');
				},
				'Update': {
					id: 'update_contact_info',
					text: 'Update',
					click: function() {
						update_contact_info();
					}
				}
			}
		});

		$('#edit_profile_button').live('click', function(event) {
			event.preventDefault();
			$('#edit_profile').dialog('open');
		});
	}

	function update_contact_info () {
		data = {};
		$.each($('#edit_profile_form').serializeArray(), function(i, field) {
			data[field.name] = field.value;
		});
		data['action'] = 'update_person';
		$('#edit_profile_message').html('').removeClass('error');
		$('#edit_profile span.error').remove();

		$.post(info.ajaxurl, data, function(data, textStatus, xhr) {
			if (data) {
				if (data.id) {
					$('#edit_profile_message').html('You successfully updated your profile.');
					$('#edit_profile form').hide();
					$('#edit_profile').siblings('.ui-dialog-buttonpane').hide();
					setTimeout(function () {
						window.location.reload(true);
					}, 700);
				}
				else {
					if (data.message) {
						$('#edit_profile_message').html(data.message).addClass('error');
					}
					if (data.validations) {
						for (group in data.validations) {
							for (key in data.validations[group]) {
								$('#edit_profile input[name="'+group+'['+key+']"]').after('<span class="error">*</span>');
							}
						}
					}
				}
			}
			else {
				$('#edit_profile_message').html('Unable to update profile at this time.').addClass('error');
			};
		}, 'json');
	}
});
