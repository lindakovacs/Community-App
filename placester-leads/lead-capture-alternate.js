// Lead Capture Property Details Overlay

// manually set cookie
// jQuery.cookies.set('lead_capture_visitor');

// manually delete cookie
// jQuery.cookies.del('lead_capture_visitor');

// If we're on the property details page, do lead capture
jQuery(document).ready(function() {
	if(jQuery('#property-details-lead-capture').length) {
		var lead_capture_value = jQuery.cookies.get('lead_capture_visitor');
		if(lead_capture_value <= 0) {

			// prompt every freq PDP views, first = 1 to prompt on first view
			var freq = 5; var first = 0;
			jQuery.cookies.set('lead_capture_visitor', --lead_capture_value);
			if((lead_capture_value + first) % freq == 0) {

				if(jQuery('.pl_register_lead_link').length) {
					jQuery('#pl_lead_register_form').addClass('pl_lead_capture_form');
					jQuery('.pl_register_lead_link').trigger('click');
					jQuery('#fancybox-wrap').addClass('pl_lead_capture_wrap');
				}

				else {
					display_email_overlay('#property-details-lead-capture');
				}
			}
		}
	}
});

function display_email_overlay(element_id){
	jQuery(element_id).dialog({
		modal: true,
		draggable: false,
		resizable: false,
		dialogClass: 'property-details-lead-capture',
		width: 450,
		open: function(event, ui){
			dialog_opened = true;
		},
		close: function (event, ui) {
			jQuery(".contact-form-validator-error").remove();

			var this_form = jQuery(element_id);
			if (jQuery(this_form).find("input[name='form_submitted']").val() == 0) {
				if (jQuery(this_form).find("input[name='back_on_lc_cancel']").val() == 1) {

					// send them back to whatever page they came from
					var oldHash = window.location.hash;
					window.history.back();
					var newHash = window.location.hash;

					// If the hash hasn't changed and the page doesn't have a referrer,
					// assume that there is no previous history entry and send them home
					if (oldHash === newHash && (typeof(document.referrer) !== "string" || document.referrer  === "")) {
						setTimeout(function() { window.location = '/'; }, 100);
					}
				};
			}
		}
	});
}
