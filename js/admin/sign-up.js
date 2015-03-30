jQuery(document).ready(function($) {

	var new_buttons = {
        1 : {
            text: "Don't activate yet",
            class: "linkify-button",
            click: function() {
				$(this).dialog("close");
            }
        },
        2 : {
            text: "I already have an account",
            class: "linkify-button",
            click: function() {
             	construct_modal(existing_acct_args);
            }
        },
        3 : {
            text: "Confirm Email",
            class: "green-btn right-btn",
            click: function() {
            	// Instrument...
            	signup_progress("Registration - Submitted");

                new_sign_up(function () {
                	construct_modal(idx_args);
                });
            }
        }
    };

	var existing_buttons = {
		1 : {
			text: "Don't Activate Yet",
			class: "linkify-button",
			click: function() {
				$(this).dialog("close");
			}
		},
		2 : {
			text: "Create a New Account",
			class: "linkify-button",
			classes: "left",
			click: function() {
				 construct_modal(new_acct_args);
			}
		},
		3 : {
			text: "Confirm",
			class: "green-btn right-btn",
			click: function() {
				var api_key = $('#existing_placester_modal_api_key').val();
				check_api_key(api_key);
			}
		}
	};

	var idx_buttons = {
        1 : {
            text: "No thanks",
            class: "linkify-button no-thanks-idx-btn",
            click: function() {
				// Remove current dialog area, show final page.
				$('#idx-add-inner').addClass('hide');
				$('#idx-none-inner').removeClass('hide');
				$('#searchpage-inner').removeClass('hide');

				// Hide buttons, show new buttons
				$('.yes-idx-btn, .no-thanks-idx-btn').addClass('hide');
				$('.add-listings-manually-btn').removeClass('hide');
				if (!pl_signup_data.placester_theme) {
					$('.custom-search-page-btn').removeClass('hide');
				}

				// Change title
				$(this).dialog('option', 'title', "Set Up Complete!");

				// Instrumentation
				signup_progress("Registration - Complete");
            }
        },
        2 : {
            text: "Yes",
            class: "yes-idx-btn green-btn right-btn",
            click: function() {
				// Remove current dialog area, add phone # dialog area.
				$('#idx-add-inner').addClass('hide');
				$('#idx-contact-inner').removeClass('hide');

				// Hide buttons, show new buttons
				$('.yes-idx-btn, .no-thanks-idx-btn').addClass('hide');
				$('.i-prefer-email-btn, .call-me-btn').removeClass('hide');

				// Instrument...
				signup_progress("Registration - Integration Requested");
				pl_signup_data.mls_int = true;

				// Start free trial...
				$.post(ajaxurl, {action: "start_subscription_trial", source: "wi"}, function (result) {
					// Instrument...
					pls_track_event("Registration - Trial Started",  {'source' : 'Activation Modal'});
				}, "json");
            }
        },
        4 : {
            text: "I prefer email",
            class: "linkify-button hide i-prefer-email-btn",
            click: function() {
            	// Instrument...
            	signup_progress("Registration - MLS through Email");

				// remove current dialog
				$('#idx-contact-inner').addClass('hide');
				$(this).dialog('option', 'title', "Set Up Complete!");

				// Show email dialog
				$('#idx-success-inner span#action').text("email");
				$('#idx-success-inner').removeClass('hide');

				// Search page and shortcode info
				$('#searchpage-inner').removeClass('hide');

				// Hide buttons, show new buttons
				$('.i-prefer-email-btn, .call-me-btn').addClass('hide');
				$('.request-done-btn').removeClass('hide');
				if (!pl_signup_data.placester_theme) {
					$('.custom-search-page-btn').removeClass('hide');
				}

				// Instrumentation
				signup_progress("Registration - Complete");
            }
        },
        5 : {
            text: "Please Call Me",
            class: "hide call-me-btn green-btn right-btn",
            click: function() {
				// Check if number entered is valid...
				var phone_number = $("#callme-idx-phone").val();
				var valid = validate_phone_number(phone_number);

				if (valid) {
					// Instrument...
					signup_progress("Registration - Phone Number");

					$(this).dialog('option', 'title', "Set Up Complete!");

					// Valid Phone Number
					$('#idx-contact-inner').prepend("YEP!");

					// remove current dialog
					$('#idx-contact-inner').addClass('hide');

					// Show email dialog
					$('#idx-success-inner span#action').text("call");
					$('#idx-success-inner').removeClass('hide');

					// Search page and shortcode info
					$('#searchpage-inner').removeClass('hide');

					// Hide buttons, show new buttons
					$('.i-prefer-email-btn, .call-me-btn').addClass('hide');
					$('.request-done-btn').removeClass('hide');
					$("#phone-validation-message").html('');
					if (!pl_signup_data.placester_theme) {
						$('.custom-search-page-btn').removeClass('hide');
					}

					// Instrumentation
					signup_progress("Registration - Complete");

					// Update user's account with phone number in Rails...
					$.post(ajaxurl, {action: 'update_user', phone: phone_number}, function (result) {
						// console.log(result);
					}, "json");
				}
				else {
					// Invalid Phone Number
					$("#callme-idx-phone").addClass('red');
					$("#phone-validation-message").html("Phone number is not valid");
				}
			}
        },
		7 : {
			text: "Close",
			class: "hide add-listings-manually-btn request-done-btn left-btn",
			click: function() {
				mark_mls_complete();
				signup_progress("Registration - Close button");
				$(this).dialog("close");
				// Reload page to reflect any addition of an API key, etc
				window.location.href = window.location.href;
			}
		},
		8 : {
			text: "View Your Search Page",
			class: "hide custom-search-page-btn green-btn right-btn",
			click: function() {
				mark_mls_complete();
				signup_progress("Registration - View search page button");
				$(this).dialog("close");
				window.location.href = pl_signup_data.search_page;
			}
		},
		9 : {
			text: "Create a Property",
			class: "hide add-listings-manually-btn request-done-btn green-btn right-btn",
			click: function() {
				mark_mls_complete();
				signup_progress("Registration - Create a property button");
				$(this).dialog("close");
				window.location.href = pl_signup_data.listing_page;
			}
		},
	};


	// Dialog config args...
	var new_acct_args = { ajax: 'new_api_key_view', title: 'Activate Your Plugin', buttons: new_buttons, width: 550 };
	var existing_acct_args = { ajax: 'existing_api_key_view', title: 'Use an Existing Placester Account', buttons: existing_buttons, width: 700 };
	var idx_args = { ajax: 'idx_prompt_view', title: 'Add IDX / MLS To My Website', buttons: idx_buttons, width: 550 };

	function construct_modal(args) {
		$.post(ajaxurl, {action: args.ajax}, function (result) {
			if (result) {
				if (typeof(result) === 'object') {
					if (result.hasOwnProperty('data')) {
						$.extend(pl_signup_data, result.data);
					}
					if (result.hasOwnProperty('html')) {
						result = result.html;
					}
				}
				if ($('#signup_wizard').is(':data(dialog)')) $('#signup_wizard').dialog('close');
				$('#signup_wizard').html(result);
				$('#signup_wizard').dialog({
					autoOpen: true,
					draggable: false,
					modal: true,
					title: args.title,
					width: args.width,
					buttons: args.buttons,
					close: function(event,ui) {
						if (event.originalEvent && $(event.originalEvent.target).closest('.ui-dialog-titlebar-close').length) {
							// user clicked close icon
							signup_progress('Registration - Dialog close icon');
						}
					}
				});
				$("#signup_wizard").find('a[data-mixpanel]').click(function() {
					signup_progress($(this).attr('data-mixpanel'));
				});
			}
		});
	}

	function mark_mls_complete() {
		// Marks flag that prevents similar IDX prompts elsewhere in the app from firing in the future...
		if (pl_signup_data.mls_int) {
			$.post(ajaxurl, {action: 'idx_prompt_completed', mark_completed: true}, function (result) { }, "json");
		}
	}

	function signup_progress(string) {
		setTimeout(
			function() {
				try {
					pls_track_event(string);
				}
				catch(err) {
					if (typeof console !== "undefined") {
						console.log('Mixpanel error: '+err.message);
					}					
				}
			}, 500);
	}

	// Create the sign-up wizard dialog container on initial page load...
	$('body').append('<div id="signup_wizard"></div>');
	construct_modal(new_acct_args);

	// Instrument...
	pls_track_event("Registration - Opportunity", {'type' : 'Activation Modal'});

	// Prevent any clicks...
	$('.wrapper').on('click', function() {
		$("#signup_wizard").dialog("open");
	});

});