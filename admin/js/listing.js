jQuery(document).ready(function($) {

	var listing_type_select = $('#pl-form-listing_type');
	set_property_type();

	//if we're editing, disable listing type.
	if ($('#hidden-property-id').val()) {
		listing_type_select.prop('disabled', true);
	} else {
		listing_type_select.bind('change', function () {
			set_property_type();
		});
	}

	$('.show_advanced').live('click', function () {
		$(this).removeClass('show_advanced');
		$(this).addClass('hide_advanced');
		$(this).text('Hide Advanced');
		var id = '#' + $(this).attr('id') + '_details_admin_ui_advanced';
		$(id).show();
	});

	$('.hide_advanced').live('click', function () {
		$(this).removeClass('hide_advanced');
		$(this).addClass('show_advanced');
		$(this).text('Show Advanced');
		var id = '#' + $(this).attr('id') + '_details_admin_ui_advanced';
		$(id).hide();
	});

	function set_property_type() {
		$('div.basic-attributes, div.extended-attributes').hide().find('.pl-form-value').prop('disabled', true);
		$('div.' + listing_type_select.val() + '-attributes').show().find('.pl-form-value').prop('disabled', false);
	}

	// Initialize the jQuery File Upload widget:
	$('div.fileupload-buttonbar').fileupload({
		formData: { action: 'upload_image' },
		sequentialUploads: true,
		dataType: 'json',

		submit: function (e, data) {
			$.each(data.files, function (index, file) {
				var id = file.name.replace(/( )|(\.)|(\))|(\()/g,'');
				$('#fileupload-holder-message').append(
					'<li class="image_container"><div class="image_upload_bg">' +
						'<div class="plspinner" id="' + id + '"></div><a id="remove_image">Loading...</a>' +
					'</div></li>');
			});
		},

		done: function (e, data) {
			var message = '';
			$.each(data.result, function (index, file) {

				if (!file.url) {
					if (file.message)
						message += file.message;
					else
						alert('Error - Upload Failed. Your image needs to be smaller then 1MB and gif, jpg, or png.');

					if (file.orig_name) {
						var id = '#' + file.orig_name.replace(/( )|(\.)|(\))|(\()/g,'');
						$(id).parentsUntil('#fileupload-holder-message').remove();
					}

					return false;
				}

				else {
					var id = '#' + file.orig_name.replace(/( )|(\.)|(\))|(\()/g,'');
					$(id).parentsUntil('#fileupload-holder-message').remove();
					$('#fileupload-holder-message').append(
						'<li class="image_container"><div>' +
							'<img width="100px" height="100px" src="' + file.url + '" ><a id="remove_image">Remove</a>' +
							'<input id="hidden_images" type="hidden" name="images[]" value="filename=' + file.name+'">' +
						'</div></li>');
				}
			});

			if (message) {
				alert(message);
				return false;
			}
		},

		fail: function (e, data) {
			alert('error');
		}
	});

	// drag and drop image ordering
	$('#fileupload-holder-message').sortable();

	$('#remove_image').live('click', function (event) {
		event.preventDefault();
		$(this).closest('.image_container').remove();
	});


	$("input#metadata-avail_on_picker").datepicker({
		showOtherMonths: true,
		numberOfMonths: 2,
		selectOtherMonths: true,
		dateFormat: "yy-mm-dd"
	});

	// create listing
	$('#add_listing_publish').live('click', function(event) {
		$('#loading_overlay').show();
	});
});
