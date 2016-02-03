// Lead Capture Property Details Overlay

// manually set cookie
// jQuery.cookies.set('lead_capture_visitor');

// manually delete cookie
// jQuery.cookies.del('lead_capture_visitor');

// Search for Cookie ID
var lead_capture_exists = jQuery.cookies.get('lead_capture_visitor');

// If Cookie ID doesn't exist / LEAD HASN'T BEEN CAPTURED, display lead capture form
if( lead_capture_exists != 1) {
  display_email_overlay('#property-details-lead-capture');
}

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
