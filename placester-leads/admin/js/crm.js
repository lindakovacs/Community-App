jQuery(document).ready(function($) {

	function call_CRM_AJAX (method, args, callback) {
		// Set response format variable...
		var format = (args && args.response_format) ? args.response_format : 'html';

		// Format data object to be passed...
		data = {};
		data.action = 'crm_ajax_controller';
		data.crm_method = method;
		
		// If a separate method is specified for what is returned, set property and remove from args...
		if (args && args.return_spec) { 
			data.return_spec = args.return_spec;
			delete args.return_spec;
		}

		data.crm_args = args;
		data.response_format = format;

		jQuery.post(ajaxurl, data, callback, format);
	}

	function fetch_api_key (crm_id) {
		var api_key = null;

		// Alphanumeric regex (API keys should pass this...)
		var alnum_regex = /^[a-z0-9]+$/i;

		// Try to fetch API key based on naming convention...
		var input_id = '#' + crm_id + '_api_key';
		var input_elem = jQuery(input_id);
		
		// There should only be one API key input entered for this CRM...
		if (input_elem.length == 1) {
			var input = input_elem.val();

			// If input passes validating regex, set API key to input, otherwise set to null...
			api_key = ( alnum_regex.test(input) ? input : null );
		}

		return api_key;
	}

	function createSpinner () {
		var barCount = 8;

		// Construct spinner components...
		var spinnerElem = '<div id="loading-spinner" class="spinningBars">';
	   	for (var i = 1; i <= barCount; i++) {
	   		spinnerElem += ('<div class="bar' + i + '"></div>');
	   	}
		spinnerElem += '</div>';

		return spinnerElem;				   
	}

	// Pre-load this image initially for quick access at-will...
	var loadingGifSrc = window.location.protocol + '//' + window.location.host + '/wp-admin/images/wpspin_light.gif';
	var loadingGifImg = new Image();
	loadingGifImg.src = loadingGifSrc;

	function showLoading (parentElem) {
		parentElem.append('<img id="loading_img" src="' + loadingGifSrc + '" style="margin: 0px 0px -3px 3px" />');
	}

	function hideLoading (parentElem) {
		parentElem.children('#loading_img').remove();
	}

	// Initialize this variable outside of the setting function below, for closure-wide scope...
	var contacts_datatable;

	// Parses search form and bundles parameters
    function parse_search_params (aoData) {
        $.each($('#contacts_grid_search').serializeArray(), function(i, field) {
            if (field.value !== '') {
            	aoData.push({name: field.name, value: field.value});
        	}
        });
    }

	function intializeContactsGrid () {
		contacts_datatable = $('#contacts_grid').dataTable({
	        bFilter: false,
	        bProcessing: true,
	        bServerSide: true,
	        sServerMethod: 'POST',
	        sPaginationType: 'full_numbers',
	        sDom: '<"dataTables_top"pi>lftpir',
	        sAjaxSource: ajaxurl,
	        aoColumnDefs: [ {bSortable: false, aTargets: ["_all"]} ], 
	        fnServerParams: function (aoData) {
	            aoData.push({name: 'action', value: 'crm_ajax_controller'});
	            aoData.push({name: 'crm_method', value: 'getContactGridData'});
	            aoData.push({name: 'response_format', value: 'JSON'});
	            parse_search_params(aoData);
	        }
	    });
	}

	// Store ref to main container element for use in setting delegated events and altering the view...(see below)
	var view = $('#main-crm-container');

	// Get the main view initially...
	call_CRM_AJAX('getView', {}, function (result) {
		view.html(result);

		// If contacts grid exists, initialize it...
		intializeContactsGrid();
	});

	view.on('click', '.integrate-button', function (event) {
		event.preventDefault();
		showLoading($(this).parent());

		// Extract CRM id from clicked element's actual id...
		var id = $(this).attr('id')
		var CRMid = id.replace('integrate_', '');
		var APIkey = fetch_api_key(CRMid);

		// If API key wasn't entered or is invalid, prompt the user and exit...
		if (APIkey == null) {
			// Prompt of invalid API key entry...
			$('#' + CRMid + '_api_key').addClass('invalid');

			hideLoading($(this).parent());
			return;
		}

		// Specify call to return altered view that results from CRM integration...
		retSpec = {method: 'browseView'};

		call_CRM_AJAX('integrateCRM', {crm_id: CRMid, api_key: APIkey, return_spec: retSpec}, function (result) {
			// Refesh the view so that this CRM can being integrated...
			view.html(result);

			// Activating a CRM will return a grid for the contacts that needs to be initialized...
			intializeContactsGrid();
		});
	});

	view.on('click', '.activate-button', function (event) {
		event.preventDefault();
		showLoading($(this).parent());
		
		// Extract CRM id from clicked element's actual id...
		var id = $(this).attr('id')
		var CRMid = id.replace('activate_', '');

		// Specify call to return altered view that results from CRM activation...
		retSpec = {method: 'browseView'};

		call_CRM_AJAX('setActiveCRM', {crm_id: CRMid, return_spec: retSpec}, function (result) {
			// Refresh view to reflect CRM activation...
			view.html(result);

			// Activating a CRM will return a grid for the contacts that needs to be initialized...
			intializeContactsGrid();
		});
	});

	view.on('click', '.reset-creds-button', function (event) {
		event.preventDefault();
		showLoading($(this).parent());

		// Extract CRM id from clicked element's actual id...
		var id = $(this).attr('id')
		var CRMid = id.replace('reset_', '');

		// Specify call to return the "activate" CRM partial for display purposes...
		retSpec = {method: 'getPartial', args: {partial: 'integrate', partial_args: {id: CRMid}}};

		call_CRM_AJAX('resetCRM', {crm_id: CRMid, return_spec: retSpec}, function (result) {
			// Refesh the view so that this CRM can be activated, now that integration is completed...
			var elem = $('#' + CRMid + '-box .action-box');
			elem.html(result);
		});		
	});

	view.on('click', '.deactivate-button', function (event) {
		event.preventDefault();
		showLoading($(this).parent());

		// Extract CRM id from clicked element's actual id...
		var id = $(this).attr('id')
		var CRMid = id.replace('deactivate_', '');

		// Specify call to return the "activate" CRM partial for display purposes...
		retSpec = {method: 'getPartial', args: {partial: 'activate', partial_args: {id: CRMid}}};

		call_CRM_AJAX('resetActiveCRM', {return_spec: retSpec}, function (result) {
			// Refesh the view so that this CRM can be activated, now that integration is completed...
			var elem = $('#' + CRMid + '-box .action-box');
			elem.html(result);
		});
	});

	view.on('click', '.browse-button', function (event) { 
		event.preventDefault();
		showLoading($(this).parent());

		call_CRM_AJAX('browseView', {}, function (result) {
			view.html(result);

			// If contacts grid exists, initialize it...
			intializeContactsGrid();
		});
	});

	view.on('click', '.settings-button', function (event) {
		event.preventDefault();
		showLoading($(this).parent());

		call_CRM_AJAX('settingsView', {}, function (result) {
			// Refresh view to reflect CRM deactivation...
			view.html(result);
		});
	});

    view.on('change', '#contacts_grid_search', function (event) {
        event.preventDefault();
        contacts_datatable.fnDraw();
    });

    view.on('click', '#contacts_grid tbody tr', function (event) {
    	var userID =  $(this).children('td:first-child').text();

    	// Retrieve details on the clicked contact and display them...
		call_CRM_AJAX('getPartial', {partial: 'details', partial_args: {contact_id: userID}}, function (result) {
			$('body').append(result);
		});
	});

    function removeDetailsOverlay () {
    	$('.contact-details-overlay, .contact-details-pane').remove();
    }

	$('body').on('click', '.contact-details-overlay', function (event) {
		removeDetailsOverlay();
	});

	// Bind esc key to removing the details panel overlay...
	$(document).on('keyup', function (event) {
		if (event.keyCode == 27) { removeDetailsOverlay(); }
	});

});