jQuery(document).ready(function($) {

  var demo_buttons = {
		1 : {
			text: "No, Thanks",
			click: function() {
				 $( this ).dialog( "close" );
			}
		},
		2 : {
			text: "Confirm",
			id: 'confirm_demo_button',
			click: function() {
				$.post(ajaxurl, {action: 'demo_data_on'}, function(response) {
          // console.log(response);
          $('#demo_data_wizard').dialog("close");
        },'json');
			}
		}
	}

	$( "#demo_data_wizard" ).dialog({
		autoOpen: false,
		draggable: false,
		modal: true,
		title: 'Test-drive your Site with Demo Listings',
		width: 500,
		buttons: demo_buttons
	});

  initializeMap = function () {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-34.397, 150.644);
    var mapOptions = {
      zoom: 8,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    // When accessing the DOM element via jQuery, the '[0]' is necessary...
    map = new google.maps.Map($('#map_canvas')[0], mapOptions);

    var address = $('#demo_zip').val();

    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            map: map,
            position: results[0].geometry.location
        });
      } else {
        alert('Geocode was not successful for the following reason: ' + status);
      }
    });
  }

  function prompt_demo_data_local () {
    $('#demo_data_wizard').dialog('open');  

    // Load Google Maps API
    // var googleMapsURL = 'https://maps.googleapis.com/maps/api/js?sensor=false&callback=initializeMap';
    // $.getScript(googleMapsURL);
  }

  // Expose function to global namespace
  prompt_demo_data = prompt_demo_data_local;
});

// These are global vars...
var geocoder;
var map;

