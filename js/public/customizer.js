/*
 * Global Definitions
 */

// Usually defined by WordPress, but not in the customizer...
var ajaxurl = 'http://onboard.placester.local/wp-admin/admin-ajax.php';

// This global variable must be defined in order to conditionally prevent iframes from being
// automatically "busted" when in the hosted environment... (see hosted-modifications plugin)
var customizer_global = {
	refreshing: false,
	previewLoaded: function () {
		// alert('Preview finished loading...');
		// jQuery('#customize-preview').removeClass('preview-load-indicator');
		jQuery('#customize-preview').fadeTo(1000, 1);
		jQuery('#preview_load_spinner').fadeTo(700, 0);

		// Set to let other components know that refresh has been completed...
		this.refreshing = false;
	}
};

jQuery(document).ready(function($) {

	/*
	 * Add custom javascript here that applies/affects the customizer as a whole. 
	 * (Configured to execute on any load of customize.php)
	 */

	// Hide the "You are Previewing" div--no hook to prevent this, so this is the only way to hide w/out altering core...
	$('#customize-info').hide();

	/*
	 * Applies to loading default theme options "pallets"
	 */
	$('#btn_def_opts').live('click', function (event) {
		event.preventDefault();

		if (!confirm('Are you sure you want to overwrite your existing Theme Options?'))
		{ return; }

		var data = { action: 'import_default_options',
					 name: $('#def_theme_opts option:selected').val() }
		// console.log(data);
		
		$.post(ajaxurl, data, function(response) {
		  if (response) {
		  	// console.log(response);
		  }
		  
	      // Refresh theme options to reflect newly imported settings...
	      window.location.reload(true);  
		});
	});

	/*
	 * Handles switching themes in the preview iframe...
	 */
	$('#switch_theme_main #theme_choices').live('change', function (event) {
		// console.log($(this).val());
		var curr_href = window.location.href;
		var new_href = $(this).val()

		// Check to see if the current URL contains a flag for onboarding--if so, replicate it in the new href...
		if ( curr_href.indexOf('onboard=true') != -1 ) {
			new_href += '&onboard=true';
		}  

		window.location.href = new_href;
	});

	// Ensures that saving a new theme in the customizer does NOT cause a redirect...
	if (_wpCustomizeSettings) {
		var boolSuccess = delete _wpCustomizeSettings.url.activated;
		// console.log('redirect deleted: ' + boolSuccess);
	}

	/*
	 * Display loading for input changes...
	 */
	function setPreviewLoading() {
		if ( !customizer_global.refreshing ) {
		  	$('#customize-preview').fadeTo(800, 0.3);
			$('#preview_load_spinner').fadeTo(700, 1);

			customizer_global.refreshing = true;
		}  
	}

	$('.customize-control-text input[type=text]').on('keyup', function (event) { 
		setPreviewLoading();
	});

	$('.customize-control-checkbox input[type=checkbox]').on('change', function (event) {
		setPreviewLoading();
	});

	$('select.of-typography, #theme_choices').on('change', function (event) {
		setPreviewLoading();
	});
});	


