jQuery(document).ready(function($) {

	function create_existing_dialog() {
		$.post(ajaxurl, {action:"existing_api_key_view"}, function (result) {
			if (result) {
				$('#existing_placester_dialog').html(result);
				$("#existing_placester_dialog").dialog({
					autoOpen: true,
					draggable: false,
					modal: true,
					width: 700,
					title: 'Use an existing Placester account',
					buttons: {
						1: {
						  text: "Close",
						  class: "gray-btn",
						  click: function() {
							  $(this).dialog("close");
						  }
						},
						2: {
						  text: "Switch API Keys",
							id: "switch_placester_api_key",
							class: "green-btn right-btn",
							click: function() {
								 check_api_key($("#existing_placester_modal_api_key").val());
							}
						}
					}
				});
			}
		});		
	}

	// Create the sign-up wizard dialog container on initial page load...
	$('body').append('<div id="existing_placester_dialog"></div>');


	$('#existing_placester').on('click', function() {
		create_existing_dialog();
	});

	$('#new_email').on('click', function () {
		$.post(ajaxurl, {action: 'new_api_key_view'}, function (result) {
  			// Change the dialog's content and display it...
  			$('#existing_placester_dialog').html(result);
  			
			$('#existing_placester_dialog').dialog({
				autoOpen: true,
				draggable: false,
				modal: true,
				width: 500,
				title: 'Create a New Placester Account' ,
				buttons: {
					1:{
						text: "Cancel",
						class: "gray-btn",
						click: function (){
							$(this).dialog("close")
						}
					},
					2:{
						text: "Confirm Email",
						class: "green-btn right-btn",
						click: function () {
							new_sign_up(function() { setTimeout(function () {window.location.reload(true);}, 1000); });
						}
					}
				}
			});
			return false;
		});
		
	});

	$('#error_logging_click').on('click', function() {
		var request = {
			report_errors: $(this).is(':checked'),
			action: 'ajax_log_errors'
		}
		$.post(ajaxurl, request, function(data, textStatus, xhr) {
		  if (data && data.result) {
			$('#error_logging_message').html(data.message);
			$('#error_logging_message').removeClass('red');
			$('#error_logging_message').addClass('green');
		  } else {
		  	$('#error_logging_message').html(data.message);
		  	$('#error_logging_message').removeClass('green');
		  	$('#error_logging_message').addClass('red');
		  }
		}, 'json');
	});
	
	$('#enable_community_pages').on('click', function() {
		var request = {
			enable_pages: $(this).is(':checked'),
			action: 'enable_community_pages'
		}
		$.post(ajaxurl, request, function(data, textStatus, xhr) {
		  if (data && data.result) {
			$('#community_pages_message').html(data.message);
			$('#community_pages_message').removeClass('red');
			$('#community_pages_message').addClass('green');
		  } else {
		  	$('#community_pages_message').html(data.message);
		  	$('#community_pages_message').removeClass('green');
		  	$('#community_pages_message').addClass('red');
		  };
		}, 'json');
	});


	$('#block_address').on('click', function() {
		var request = {
			use_block_address: $(this).is(':checked'),
			action: 'ajax_block_address'
		}
		$.post(ajaxurl, request, function(data, textStatus, xhr) {
		  if (data && data.result) {
			$('#listing_settings_message').html(data.message);
			$('#listing_settings_message').removeClass();
			$('#listing_settings_message').addClass('green');
		  } else {
		  	$('#listing_settings_message').html(data.message);
		  	$('#listing_settings_message').removeClass();
		  	$('#listing_settings_message').addClass('red');
		  };
		}, 'json');
	});

	$('#demo_data').on('click', function() {
		var method = ( $(this).is(':checked') ? 'demo_data_on' : 'demo_data_off' );
		var request = { action : method };

		$.post(ajaxurl, request, function(data, textStatus, xhr) {
		  if (data && data.message) {
			$('#listing_settings_message').html(data.message);
			$('#listing_settings_message').removeClass();
			$('#listing_settings_message').addClass('green');
		  }
		}, 'json');
	});

	$('#google_places_api_button').on('click', function (event) {
		event.preventDefault();
		var request = {};
		request.places_key = $('#google_places_api').val();
		request.action = 'update_google_places'
		$.post(ajaxurl, request, function(data, textStatus, xhr) {
		  	$('#default_googe_places_message').removeClass('red');
			if (data && data.result) {
				$('#default_googe_places_message').addClass('green').html(data.message);
				setTimeout(function () {
					window.location.href = window.location.href;
				}, 700);
			} else {
				$('#default_googe_places_message').addClass('red').html(data.message);
				setTimeout(function () {
					$('#default_googe_places_message').html('');
				}, 700);
			};
		}, 'json');
	});
});