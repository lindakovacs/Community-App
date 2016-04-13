jQuery(document).ready(function($) {

	var contacts_datatable;

	function intializeContactsGrid() {
		contacts_datatable = $('#contacts_grid').dataTable({
			bFilter: false,
			bProcessing: true,
			bServerSide: true,
			sServerMethod: 'POST',
			sPaginationType: 'full_numbers',
			sDom: '<"dataTables_top"pi>lftpir',
			sAjaxSource: ajaxurl,
			aoColumnDefs: [{bSortable: false, aTargets: ["_all"]}],
			fnServerParams: function (aoData) {
				aoData.push({ name: 'action', value: 'crm_ajax_controller' });
				aoData.push({ name: 'crm_id', value: $('#contacts_grid').attr('data-crm-id') });
				aoData.push({ name: 'crm_method', value: 'getContactGridData' });
				aoData.push({ name: 'response_format', value: 'JSON' });

				// parse and push search form fields
				$.each($('#contacts_grid_search').serializeArray(), function (i, field) {
					if (field.value !== '') {
						aoData.push({name: field.name, value: field.value});
					}
				});
			}
		});
	}

	// set up dataTables, if present
	intializeContactsGrid();

	function call_CRM_AJAX(method, args, callback) {
		format = 'html';

		data = {
			action: 'crm_ajax_controller',
			crm_method: method,
			crm_args: args
		};

		if (args) {
			if (args.return_spec) {
				data.return_spec = args.return_spec;
				delete args.return_spec;
			}
			if (args.response_format) {
				format = data.response_format = args.response_format;
				delete args.response_format;
			}
		}

		data.crm_args = args;
		jQuery.post(ajaxurl, data, callback, format);
	}

	// ref to main container element for use in setting delegated events and altering the view
	var view = $('#main-crm-container');

	view.on('click', '.integrate-button', function (event) {
		event.preventDefault();

		var CRMid = $(this).attr('id').replace('integrate_', '');
		var APIkey = fetch_api_key(CRMid);
		if (!APIkey) {
			$('#' + CRMid + '_api_key').addClass('invalid');
			return;
		}

		// integrate crm and refresh settings view
		showLoading($(this).parent());
		call_CRM_AJAX('integrateCRM', { crm_id: CRMid, api_key: APIkey, return_spec: {method: 'settingsView'} }, function (result) {
			view.html(result);
		});
	});

	view.on('click', '.activate-button', function (event) {
		event.preventDefault();

		var CRMid = $(this).attr('id').replace('activate_', '');

		// activate crm and refresh settings view
		showLoading($(this).parent());
		call_CRM_AJAX('activateCRM', { crm_id: CRMid, return_spec: { method: 'settingsView' } }, function (result) {
			view.html(result);
		});
	});

	view.on('click', '.reset-creds-button', function (event) {
		event.preventDefault();

		var CRMid = $(this).attr('id').replace('reset_', '');

		showLoading($(this).parent());
		var retSpec = { method: 'getPartial', args: { partial: 'integrate', partial_args: { id: CRMid } } };
		call_CRM_AJAX('resetCRM', { crm_id: CRMid, return_spec: retSpec }, function (result) {
			$('#' + CRMid + '-box .action-box').html(result);
		});
	});

	view.on('click', '.deactivate-button', function (event) {
		event.preventDefault();

		var CRMid = $(this).attr('id').replace('deactivate_', '');

		showLoading($(this).parent());
		retSpec = { method: 'getPartial', args: { partial: 'activate', partial_args: { id: CRMid } } };
		call_CRM_AJAX('deactivateCRM', {  crm_id: CRMid, return_spec: retSpec }, function (result) {
			$('#' + CRMid + '-box .action-box').html(result);
		});
	});


	view.on('click', '.browse-button', function (event) {
		event.preventDefault();

		var CRMid = $(this).attr('id').replace('browse_', '');

		showLoading($(this).parent());
		call_CRM_AJAX('browseView', { crm_id: CRMid }, function (result) {
			view.html(result);
			intializeContactsGrid();
		});
	});

	view.on('click', '.settings-button', function (event) {
		event.preventDefault();

		showLoading($(this).parent());
		call_CRM_AJAX('settingsView', {}, function (result) {
			view.html(result);
		});
	});


	view.on('change', '#contacts_grid_search', function (event) {
		event.preventDefault();
		contacts_datatable.fnDraw();
	});

	view.on('click', '#contacts_grid tbody tr', function (event) {
		var crmID = $('#contacts_grid').attr('data-crm-id');
		var userID = $(this).children('td:first-child').text();
		if (userID) {
			call_CRM_AJAX('getPartial', { partial: 'details', partial_args: { crm_id: crmID, contact_id: userID }}, function (result) {
				$('body').append(result);
			});
		}
	});

	$('body').on('click', '.contact-details-overlay', function (event) {
		$('.contact-details-overlay, .contact-details-pane').remove();
	});

	$(document).on('keyup', function (event) {
		if (event.keyCode == 27) $('.contact-details-overlay, .contact-details-pane').remove();
	});


	function showLoading(parentElem) {
		//parentElem.append('<img id="loading_img" src="' + loadingGifSrc + '" style="margin: 0px 0px -3px 3px" />');
	}

	function hideLoading(parentElem) {
		//parentElem.children('#loading_img').remove();
	}

	function fetch_api_key(crm_id) {
		var alnum_regex = /^[a-z0-9]+$/i;
		var input_elem = $('#' + crm_id + '_api_key');

		if (input_elem.length == 1) {
			var input = input_elem.val();
			return alnum_regex.test(input) ? input : null;
		}

		return null;
	}

	// Hook for WordPress Users page
	$('body').on('click', '.lead-detail-link', function (event) {
		event.preventDefault();
		call_CRM_AJAX('getPartial', {
			partial: 'details',
			partial_args: {crm_id: 'internal', contact_id: $(this).attr('data-lead-login')}
		}, function (result) {
			$('body').append(result);
		});
	});
});
