/* Needs json files for themes in order to work

jQuery(document).ready(function($) {
	var that = {};
	$('a.install_theme').live('click', function (event) {
		event.preventDefault();
		var link = $(this).attr('href');
		that['download_link'] = link;
		$.ajax({
		    url: link,
		    type: 'GET',
		    dataType: 'jsonp',
		    success: function(data) {
		    	console.log(data);
	        	$( "#install_theme_overlay" ).dialog({
					autoOpen: false,
					draggable: false,
					modal: true,
					width: 700,
				});
				if (data && data.type == 'subscribe') {
				} else {
					window.location.href = adminurl + "?page=placester_theme_gallery&theme_url=" + encodeURIComponent(data.url);
				};
		    }
		});
	});
	function premium_theme_success () {
		var link = that.download_link;
		$.ajax({
		    url: link,
		    type: 'GET',
		    dataType: 'jsonp',
		    success: function(data) {
	        	$( "#install_theme_overlay" ).dialog({
					autoOpen: false,
					draggable: false,
					modal: true,
					width: 700,
				});
				if (data && data.type == 'subscribe') {
				} else {
					window.location.href = adminurl + "?page=placester_theme_gallery&theme_url=" + encodeURIComponent(data.url);
				};
		    }
		});
	}

	function premium_theme_cancel () {
		$('#theme-gallery-error-message').html('<div id="message" class="error"><h3>Sorry, this feature requires a premium subscription</h3><p>However, you can test the MLS integration feature for free by creating a website <a href="https://placester.com" target="_blank">placester.com</a></p></div>');
		setTimeout(function () {
			$('#theme-gallery-error-message #message').fadeOut('slow');
		}, 1000)
	}
});
*/
