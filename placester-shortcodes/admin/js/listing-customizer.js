/**
 * Used by the admin shortcode settings pages
 */

jQuery(document).ready(function($){

	/**
	 * Put hint text into a text input
	 */
	function wptitlehint(id) {
		id = id || 'title';

		var title = $('#' + id), titleprompt = $('#' + id + '-prompt-text');

		if ( title.val() == '' )
			titleprompt.removeClass('screen-reader-text');

		titleprompt.click(function(){
			$(this).addClass('screen-reader-text');
			title.focus();
		});

		title.blur(function(){
			if ( this.value == '' )
				titleprompt.removeClass('screen-reader-text');
		}).focus(function(){
			titleprompt.addClass('screen-reader-text');
		}).keydown(function(e){
			titleprompt.addClass('screen-reader-text');
			$(this).unbind(e);
		});
	}


	////////////////////////////////////////
	// Template editor
	////////////////////////////////////////

	// setup view based on current shortcode type, etc
	wptitlehint();
	// call the custom autosave for every changed input and select in the template edit view
	$('#pl_sc_tpl_edit').find('input, select, textarea').change(function() {
		_changesMade = true;
	});
	$('#pl_sc_tpl_edit input[type="submit"]').click(function() {
		_changesMade = false;
	});
	$('.subcode').click(function(e) {
		e.preventDefault();
		$(this).next('.subcode-help').toggle();
	});
	// Add CodeMirror support to edit boxes
	$('.pl_template_block textarea').each(function() {
		var cm = CodeMirror.fromTextArea(document.getElementById($(this).attr('id')), {
		    mode: $(this).closest('section').hasClass('mime_css')?"text/css":"text/html",
		    lineNumbers: true,
		    lineWrapping: true,
		    extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
		    foldGutter: true,
		    gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],

		});
		// copy cm changes back to hidden field so the preview will work
		cm.on('change', function(){
			_changesMade = true;
			if (_previewWait) {
				clearTimeout(_previewWait);
			}
			_previewWait = setTimeout(function(){
				cm.save();
			}, 1000);
		});
	});
	// popup with list of listing attributes
	$('.show_listing_attributes').click(function(e){
		e.preventDefault();
		$('#listing_attributes').dialog({modal: true, title: 'Lookup Listing Attribute', width: 'auto', height: 300});
	});


	////////////////////////////////////////
	// All forms - check for unsaved edits
	////////////////////////////////////////
	var _changesMade = false;
	var _previewWait = 0;
	
	$(window).bind('beforeunload', function() {
		if (_changesMade)
			return autosaveL10n.saveAlert;
	});
	
});
