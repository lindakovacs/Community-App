jQuery(document).ready(function($) {

  var map_canvas = $("#location-map-canvas");
  if(map_canvas.length == 0)
    return;


  // hidden form fields
  var latitude_hidden = $("#location-coords-latitude");
  var longitude_hidden = $("#location-coords-longitude");

  // visible form fields, used for geocoding attempts
  var inputs = $("input#location-address, input#location-locality, input#location-region, input#location-postal");
  var select = $("select#location-country");
  var values = {};

  // map components
  var map = new google.maps.Map(document.getElementById("location-map-canvas"),
    { mapTypeId: google.maps.MapTypeId.ROADMAP, streetViewControl: false });

  var geocoder = new google.maps.Geocoder();
  var marker = new google.maps.Marker({ map: map });
  var address_box = null;
  var message_box = null;


  // initialize the map
  (function () {
    inputs.each(function() { values[$(this).attr("id")] = $(this).val(); });
    select.each(function() { values[$(this).attr("id")] = $(this).find("option:selected").text(); });

    var latitude = latitude_hidden.val();
    var longitude = longitude_hidden.val();
    if(latitude && longitude) {
      mark_location(latitude, longitude);
      map.setZoom(15);
    }

    else
      geocode_address();

  })();


  // attempt to geocode the address whenever this is a change to the entered location data
  var timer;

  inputs.on("keyup", function() {
    if($(this).val() == values[$(this).attr("id")]) return;
    values[$(this).attr("id")] = $(this).val();

    if(timer)
      clearTimeout(timer);

    display_address_updating();
    timer = setTimeout(geocode_address, 1500);
  });

  select.on("change", function() {
    values[$(this).attr("id")] = $(this).find("option:selected").text();
    geocode_address();
  });


  // override any previously set location if the map marker is dragged somewhere
  google.maps.event.addListener(marker, 'dragend', function() {
    var latitude = marker.getPosition().lat();
    latitude_hidden.val(latitude);
    var longitude = marker.getPosition().lng();
    longitude_hidden.val(longitude);

    mark_location_manual(latitude, longitude);
  });


  function geocode_address() {
    if(timer)
      clearTimeout(timer);

    var address = values['location-address'];
    address += (address ? ', ' : '') + (values['location-locality']);
    var region = values['location-region'];
    region += (region && values['location-postal']) ? (' ' + values['location-postal']) : '';
    address += (address ? ', ' : '') + region;
    address += (address ? ', ' : '') + values['location-country'];

    geocoder.geocode({address: address}, function(results, status) {
      if( status != "OK" || results.length == 0 ) {
        if(!latitude_hidden.val() || !longitude_hidden.val()) { // if no previous location available
          mark_location("38.822591", "-100.898438");
          map.setZoom(3);
        }
        display_address_invalid(address);
        return;
      }

      results = results[0];
      var latitude = results.geometry.location.lat();
      latitude_hidden.val(latitude);
      var longitude = results.geometry.location.lng();
      longitude_hidden.val(longitude);

      mark_location(latitude, longitude);

      if(results.address_components[0].types[0] != "street_number" && results.address_components[0].types[0] != "route" ) {
        map.fitBounds(results.geometry.viewport);
        display_address_incomplete(results.formatted_address || address);
      }

      else {
        map.setZoom(15);
        display_address_geocoded(results.formatted_address || address, latitude, longitude);
      }
    });
  }


  function mark_location(latitude, longitude) {
    var location = new google.maps.LatLng(latitude, longitude);

    marker.setPosition(location);
    marker.setVisible(true);
    marker.setDraggable(true);

    map.setCenter(location);
  }

  function mark_location_manual(latitude, longitude) {
    console.log('manual');
    clear_address_box();
    set_message_box("<span class='achtung'>You are now using custom location (" + Math.round(latitude * 1000) / 1000 + ", " + Math.round(longitude * 1000) / 1000 + ").</span>");
  }

  function display_address_updating() {
    console.log('updating');
    set_message_box("<span class='message'>Parsing new address...</span>");
  }

  function display_address_geocoded(address, latitude, longitude) {
    console.log('geocoded');
    clear_message_box();
    set_address_box(latitude, longitude, "<span>" + address + "</span>");
  }

  function display_address_invalid(address) {
    console.log('invalid');
    clear_address_box();
    set_message_box("<span class='achtung'>Address could not be found: <em>" + address + "</em>.  You can drag the map marker to specify a location.</span>");
  }

  function display_address_incomplete(address) {
    console.log('incomplete');
    clear_address_box();
    set_message_box("<span class='achtung'>Address is incomplete: <em>" + address + "</em>.  You can drag the map marker to specify a location.</span>");
  }

  function set_address_box(latitude, longitude, text) {
    if(address_box)
      clear_address_box();

    address_box = new TxtOverlay(new google.maps.LatLng(latitude, longitude), text, "location-address-box", map);
  }

  function clear_address_box() {
    if(!address_box)
      return;

    address_box.toggleDOM();
    address_box = null;
  }

  function set_message_box(text) {
    if (message_box)
      clear_message_box();

    message_box = new TxtControl(google.maps.ControlPosition.TOP_CENTER, text, "location-message-box", map);
  }

  function clear_message_box() {
    if(!message_box)
      return;

    message_box.toggleDOM();
    message_box = null;
  }
});
