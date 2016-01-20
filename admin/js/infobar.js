jQuery(document).ready(function($) {

	$('#toggle_demo').live('click', function(event) {
		event.preventDefault();
		// console.log("in here...");
	  	$.post(info.ajaxurl, {action: 'demo_data_off'}, function(response) {
            // Now that demo data has been turned off, re-direct the user back the home page to reflect the change.
            //
          	// Note that "parent" is used, in order to allow refreshing from inside iframes--this
          	// still works when in the main window, as its parent property is self-referential.
            var homepage = ( window.location.origin ) ? window.location.origin : ( window.location.protocol + "//" + window.location.host );
            window.location.href = homepage;
        },'json');
	});

	$('#infobar .msg .close').live('click', function() {
		$('#infobar, #infobar-buffer').css('display', 'none');
	});

});