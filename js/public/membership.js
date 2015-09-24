jQuery(document).ready(function($) {

	// Beat Chrome's HTML5 tooltips for form validation
	$('form.pl_lead_register_form').on('mousedown', 'input[type="submit"]', function() {
		validate_register_form(this);
	});
	$('form#pl_login_form').on('mousedown', 'input[type="submit"]', function() {
		validate_login_form(this);
	});
	$('.pl_lead_register_form').bind('keypress', function(e) {
		var code = e.keyCode || e.which;
		if (code == 13) {
			validate_register_form(this);
		}
	});
	$('#pl_login_form').bind('keypress', function(e) {
		var code = e.keyCode || e.which;
		if (code == 13) {
			validate_login_form(this);
		}
	});

	// Actual form submission - validate and submit
	$('.pl_lead_register_form').bind('submit', function (event) {     
		// Prevent default form submission logic
		event.preventDefault();
		if (validate_register_form(this)) {
			register_user(this);
		}
	});
	$('form#pl_login_form').bind('submit', function (event) {
		event.preventDefault();
		if (validate_login_form(this)) {
			login_user(this);
		}
	});

	// Bind link in registration form that allows user to switch to the login form..
	$('form.pl_lead_register_form').on('click', '#switch_to_login', function (event) {
		event.preventDefault();

		// Simulate login link click to switch to login form...
		$('.pl_login_link').trigger('click');
	});

	if (typeof $.fancybox == "function") {
		// If reg form available or logged in then show add to favorites 
		if ($('.pl_lead_register_form').length || $('.pl_add_remove_lead_favorites #pl_add_favorite').length) {
			$('div#pl_add_remove_lead_favorites,.pl_add_remove_lead_favorites').show();    	
		}

		// These allow Login/Register links to be manually added to WP nav menus
		$('li.pl_register_lead_link > a').addClass('pl_register_lead_link');
		$('li.pl_register_lead_link').removeClass('pl_register_lead_link');
		$('li.pl_login_link > a').addClass('pl_login_link');
		$('li.pl_login_link').removeClass('pl_login_link');

		// Register Form Fancybox
		stash_fancy('.pl_register_lead_link');
		$('.pl_register_lead_link').fancybox({
			"hideOnContentClick": false,
			"scrolling": true,
			onCleanup: function () {
				reset_fancy('.pl_register_lead_link');
			}
		});

		// Login Form Fancybox
		stash_fancy('.pl_login_link');
		$('.pl_login_link').fancybox({
			"hideOnContentClick": false,
			"scrolling": true,
			onCleanup: function () {
				reset_fancy('.pl_login_link');
			}
		});

		$(document).ajaxStop(function() {
			favorites_link_signup();
		});
	}

	favorites_link_signup();

	function favorites_link_signup () {
		if (typeof $.fancybox == 'function') {
			$('.pl_register_lead_favorites_link').fancybox({
				"hideOnContentClick": false,
				"scrolling": true,
				onCleanup: function () {
					reset_fancy('.pl_register_lead_link');
				}
			}); 
		}
	}

	// Called with form data after validation 
	function register_user (form_el) {
		var $form = $(form_el).closest('form');

		data = {
				action: "pl_register_site_user",
				username: $form.find('#reg_user_email').val(),
				email: $form.find('#reg_user_email').val(),
				nonce: $form.find('#register_nonce_field').val(),
				password: $form.find('#reg_user_password').val(),
				confirm: $form.find('#reg_user_confirm').val()
		};

		$.post(info.ajaxurl, data, function (response) {
			if (response && response.success) {
				// Remove error messages
				$('.register-form-validator-error').remove();

				// Remove form
				$("#pl_lead_register_form_inner_wrapper").slideUp();

				// Show success message
				$("#pl_lead_register_form .success").show('fast');

				// Write lead capture cookie
				jQuery.cookies.set('lead_capture_visitor', 1); // no expiration set

				// Reload window so it shows new login status
				setTimeout(function () { window.location.reload(true); }, 1000);
			}
			else if (typeof $.fn.validator == "function") {
				// Error Handling
				var errors = (response && response.errors) ? response.errors : {};

				// jQuery Tools Validator error handling
				$form.validator();

				// Take possible errors and create new object with correct ones to pass to validator
				error_keys = new Array("user_email", "user_password", "user_confirm");
				error_obj = new Object();

				for (key in errors) {
					if (error_keys.indexOf(key) != -1) {
						error_obj[key] = errors[key];
					}
				}

				$form.find('input').data("validator").invalidate(error_obj);
			}
		}, 'json');
	}

	// Called with form data after validation 
	function login_user (form_el) {
		var $form = $(form_el).closest('form');

		data = {
				action: "pl_login_site_user",
				username: $form.find('#user_login').val(),
				password: $form.find('#user_pass').val(),
				remember: $form.find('#rememberme').val()
		};

		$.post(info.ajaxurl, data, function (response) {
			// If request successfull empty the form...
			if (response && response.success) {
				// Remove error messages...
				$('.login-form-validator-error').remove();

				// Hide form...
				// $("#pl_login_form_inner_wrapper").slideUp();
				$.fancybox.close();

				// Show success message
				// setTimeout(function() { $('#pl_login_form .success').show('fast'); }, 500);

				// Write lead capture cookie
				jQuery.cookies.set('lead_capture_visitor', 1); // no expiration set

				// Reload window so it shows new login status
				window.location.reload(true);
			} 
			else if (typeof $.fn.validator == "function") {
				// Error Handling
				var errors = (response && response.errors) ? response.errors : {};

				// jQuery Tools Validator error handling
				$form.validator();

				// Take possible errors and create new object with correct ones to pass to validator
				error_keys = new Array("user_login", "user_pass");
				error_obj = new Object();

				for (key in errors) {
					if (error_keys.indexOf(key) != -1) {
						error_obj[key] = errors[key];
					}
				}

				$form.find('input').data("validator").invalidate(error_obj);
			}
		}, 'json');
	}

	function validate_register_form (form_el) {
		var $form = $(form_el).closest('form');

		if (typeof $.fn.validator == "function") {
			// get fields that are required from form and execute validator()
			var inputs = $form.find("input[required]").validator({
				messageClass: "register-form-validator-error", 
				offset: [10,0],
				message: "<div><span></span></div>",
				position: "top center"
			});

			return inputs.data("validator").checkValidity();
		} else {
			return true;
		}
	}

	function validate_login_form (form_el) {
		var $form = $(form_el).closest('form');

		if (typeof $.fn.validator == "function") {
			// get fields that are required from form and execute validator()
			var inputs = $form.find("input[required]").validator({
				messageClass: "login-form-validator-error", 
				offset: [10,0],
				message: "<div><span></span></div>",
				position: "top center"
			});
			return inputs.data("validator").checkValidity();
		} else {
			return true;
		}
	}

	function stash_fancy (selector) {
		var form = $($(selector).attr('href'));
		form.after(form.clone(true).attr('id', 'cloned_' + form.attr('id')));
	}

	function reset_fancy (selector) {
		var form = $('#cloned_' + $(selector).attr('href').substring(1));
		form.before(form.clone(true).attr('id', form.attr('id').substring(7)));

		// remove any styling class attached to the fancybox-wrap (in lead-capture.js)
		$('#fancybox-wrap').removeClass();
	}

	/*
	 * Property/Listing "favorites" functionality...
	 */

	// Don't ajaxify the add to favorites link for guests
	$('#pl_add_favorite:not(.guest)').live('click', function (event) {
		event.preventDefault();

		var spinner = $(this).parent().find(".pl_favorite_property_spinner");
		spinner.show();

		property_id = $(this).attr('href');

		data = {
			action: 'add_favorite_property',
			property_id: property_id.substr(1)
		};

		var that = this;
		$.post(info.ajaxurl, data, function (response) {
			spinner.hide();

			if (response && response.id) {
				$(that).parent().find('#pl_add_favorite').hide();
				$(that).parent().find('#pl_remove_favorite').show();
			}
			else {
				console.log("Error adding favorite...");
			}
		}, 'json');
	});

	$('#pl_remove_favorite').live('click',function (event) {
		event.preventDefault();

		var spinner = $(this).parent().find(".pl_favorite_property_spinner");
		spinner.show();

		property_id = $(this).attr('href');

		data = {
			action: 'remove_favorite_property',
			property_id: property_id.substr(1)
		};

		var that = this;
		$.post(info.ajaxurl, data, function (response) {
			spinner.hide();

			if (response != 'errors') {
				$(that).parent().find('#pl_remove_favorite').hide();
				$(that).parent().find('#pl_add_favorite').show();
			}
		}, 'json');
	});

/*
 * My Saved Search functionality...
 */

	$('#pl_save_favorite_search:not(.guest) > a').live('click', function (event) {
		event.preventDefault();

		var spinner = $(this).parent().parent().find(".pl_favorite_search_spinner");
		spinner.css('visibility', 'visible');

		data = {
			action: 'save_favorite_search',
			search_url: $(this).parent().parent().find("#pl_favorite_search_link").attr('href') || window.location.href
		};

		var that = this;
		$.post(info.ajaxurl, data, function (response) {
			spinner.css('visibility', 'hidden');

			if (response && response.hash) {
				$(that).parent().parent().find('#pl_save_favorite_search').hide();
				$(that).parent().parent().find('#pl_clear_favorite_search').show();
				if (response.timeout) {
					$(that).parent().parent().find('#pl_enable_favorite_search').hide();
					$(that).parent().parent().find('#pl_disable_favorite_search').show();
				}
				else {
					$(that).parent().parent().find('#pl_enable_favorite_search').show();
					$(that).parent().parent().find('#pl_disable_favorite_search').hide();
				}
			}
			else {
				console.log("Error saving search..." + (response && response.message ? ' ' + response.message : ''));
			}
		}, 'json');
	});

	$('#pl_clear_favorite_search > a').live('click', function (event) {
		event.preventDefault();

		var spinner = $(this).parent().parent().find(".pl_favorite_search_spinner");
		spinner.css('visibility', 'visible');

		data = {
			action: 'clear_favorite_search',
			search_hash: $(this).parent().parent().find("#pl_favorite_search_link").attr('href') || window.location.hash
		};

		var that = this;
		$.post(info.ajaxurl, data, function (response) {
			spinner.css('visibility', 'hidden');

			if (response && response.hash) {
				$(that).parent().parent().find('#pl_clear_favorite_search').hide();
				$(that).parent().parent().find('#pl_save_favorite_search').show();
			}
			else {
				console.log("Error clearing search..." + (response && response.message ? ' ' + response.message : ''));
			}
		}, 'json');
	});

	$('#pl_enable_favorite_search > a').live('click', function (event) {
		event.preventDefault();

		var spinner = $(this).parent().parent().parent().find(".pl_favorite_search_spinner");
		spinner.css('visibility', 'visible');

		data = {
			action: 'enable_favorite_search',
			search_hash: $(this).parent().parent().parent().find("#pl_favorite_search_link").attr('href') || window.location.hash,
			search_enable: 1
		};

		var that = this;
		$.post(info.ajaxurl, data, function (response) {
			spinner.css('visibility', 'hidden');

			if (response && response.hash) {
				$(that).parent().parent().find('#pl_enable_favorite_search').hide();
				$(that).parent().parent().find('#pl_disable_favorite_search').show();
			}
			else {
				console.log("Error enabling search..." + (response && response.message ? ' ' + response.message : ''));
			}
		}, 'json');
	});

	$('#pl_disable_favorite_search > a').live('click', function (event) {
		event.preventDefault();

		var spinner = $(this).parent().parent().parent().find(".pl_favorite_search_spinner");
		spinner.css('visibility', 'visible');

		data = {
			action: 'enable_favorite_search',
			search_hash: $(this).parent().parent().parent().find("#pl_favorite_search_link").attr('href') || window.location.hash,
			search_enable: 0
		};

		var that = this;
		$.post(info.ajaxurl, data, function (response) {
			spinner.css('visibility', 'hidden');

			if (response && response.hash) {
				$(that).parent().parent().find('#pl_disable_favorite_search').hide();
				$(that).parent().parent().find('#pl_enable_favorite_search').show();
			}
			else {
				console.log("Error disabling search..." + (response && response.message ? ' ' + response.message : ''));
			}
		}, 'json');
	});

});
