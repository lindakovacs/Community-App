function POI () {};

POI.prototype.init = function ( params ) {
	var that = this;

	this.map = params.map || alert('You need to give the POI object a map');
	this.form_id = params.form_id || alert('You need to provide the id of the form that has the checkbox controls.');
	this.status_window_header = params.status_window_header || '<h5>Point of Interest</h5>';
	this.status_window_content = params.status_window_content || '<p id="start_warning">Use controls below to select what points of interests you\'d like to see on the map</p>';
	this.search_radius = params.search_radius || 5000;
	this.use_google_icons = params.use_google_icons || false;
	this.default_icon = params.default_icon || 'https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.3|0|FF8429|13|b';

	this.markers = [];
	this.infowindows = [];

	this.all_markers = [];

	//set the initial state of the status window to
	//tell users how to use the thing
	if (this.map.status_window) {
		google.maps.event.addDomListenerOnce(this.map.map, 'idle', function() {
			var content = '<div id="polygon_display_wrapper">';
			content += that.status_window_header;
			content += that.status_window_content;
			content += '</div>';
			jQuery('#' + that.map.status_window.dom_id).append(content);
		});
	}

	//do something on form click. 
	jQuery( this.form_id ).live('change', function(event) {
      	event.preventDefault();
      	that.clear_markers();
      	that.search_places();
      });

}

POI.prototype.update = function () {
	this.update_all_marker_list();
	this.center_on_markers();
}

POI.prototype.update_all_marker_list = function () {
	
	 var all_markers = [];

	for (var i = this.map.markers.length - 1; i >= 0; i--) {
		all_markers.push(this.map.markers[i]);
	};

	for (var i = this.markers.length - 1; i >= 0; i--) {
		all_markers.push(this.markers[i]);
	};

	this.all_markers = all_markers;
}

POI.prototype.clear_markers = function () {
	if (this.markers) {
		for (var i = this.markers.length - 1; i >= 0; i--) {
			this.markers[i].setMap( null )
		}
        this.markers = [];
	}
}

POI.prototype.create_marker = function ( marker_options ) {

	var that = this;

	var marker = new google.maps.Marker(marker_options);

	var infowindow = new google.maps.InfoWindow({content: marker_options.content});
	this.infowindows.push(infowindow);

	google.maps.event.addListener(marker, 'click', marker_options.click || function() {
		
		//hide all infowindows
		for (var i = that.infowindows.length - 1; i >= 0; i--) {
			that.infowindows[i].setMap(null)
		}

		infowindow.open( that.map.map, marker );
	});

	
	marker.setMap(this.map.map);

	this.markers.push(marker);
}

POI.prototype.search_places = function () {
	var that = this;
	var request = this.get_POI_form();

	var service = new google.maps.places.PlacesService(this.map.map);

    service.search(request, function ( results, status ) {

		var points = [];

		if (status == google.maps.places.PlacesServiceStatus.OK) {
			for (var i = 0; i < results.length; i++) {	
				
				var marker_options = {position:new google.maps.LatLng(results[i].geometry.location.lat(), results[i].geometry.location.lng()), content:results[i].name, icon: that.default_icon, listing:results[i] }
				if (that.use_google_icons) {
					marker_options.icon = results[i].icon;
				};

				that.create_marker(marker_options);
		  	}
		}	

		that.update_all_marker_list();
		that.center_on_markers();
			
    })
}
				        

POI.prototype.get_POI_form = function () {
	var response = {location: new google.maps.LatLng(this.map.lat, this.map.lng) , radius: this.search_radius, types: ['']};
	var form_values = [];
	jQuery.each(jQuery(this.form_id).serializeArray(), function(i, field) {
		form_values.push(field.name);
	});
	if (form_values.length > 0) {
		response.types = [];
		for (key in form_values) {
			response.types.push(form_values[key]);
		};
	};
	return response;
}

POI.prototype.center_on_markers = function () {
	var bounds = new google.maps.LatLngBounds();
	for (var i = this.all_markers.length - 1; i >= 0; i--) {
		bounds.extend(this.all_markers[i].getPosition());
	}	
	this.map.map.fitBounds(bounds);
}