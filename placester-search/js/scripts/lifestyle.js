function Lifestyle ( params ) {
	this.map = params.map || alert('You need to give the lifestyle object a map');
};

Lifestyle.prototype.init = function () {
	var that = this;

	//set the initial state of the polygon menu
	if (this.status_display) {
		google.maps.event.addDomListenerOnce(this.map, 'idle', function() {
			var content = '<div id="polygon_display_wrapper">';
			content += '<h5>Point of Interest</h5>';
			content += '<p id="start_warning">Use controls below to select what points of interests you\'d like to see on the map</p>';
			content += '</div>';
			jQuery('#' + that.status_display.dom_id).append(content);
		});
	}
	
	jQuery('#lifestyle_form_wrapper form, .location_select_wrapper').live('change', function(event) {
      	event.preventDefault();
      	that.map.clear();
      	that.search_places();
      });

}

Lifestyle.prototype.search_places = function () {
	var that = this;
	var request = this.get_lifestyle_form();

	var service = new google.maps.places.PlacesService(this.map.map);

    service.search(request, function ( results, status ) {
		var points = [];
		if (status == google.maps.places.PlacesServiceStatus.OK) {
			that.map.hide_empty();
			for (var i = 0; i < results.length; i++) {	
				that.map.create_marker({position:new google.maps.LatLng(results[i].geometry.location.lat(), results[i].geometry.location.lng()), content:results[i].name, icon: 'https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.3|0|FF8429|13|b', listing:results[i] });
		  	}
		}	
		if (that.map.markers.length > 0 )
			that.map.center_on_markers();
    })
}
				        
Lifestyle.prototype.service_callback = function (results, status) {
	console.log('service_callback');
	var points = [];
	if (status == google.maps.places.PlacesServiceStatus.OK) {
		for (var i = 0; i < results.length; i++) {						           
			points.push({lat: results[i].geometry.location.lat(), lng: results[i].geometry.location.lng()});
			that.map.create_marker({latlng:results[i].geometry.location, content:results[i].name, icon: 'https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.3|0|FF8429|13|b' });
	  	}
	}
}

Lifestyle.prototype.get_lifestyle_form = function () {
	var response = {location: new google.maps.LatLng(this.map.lat, this.map.lng) , radius: 5000, types: ['']};
	var form_values = [];
	jQuery.each(jQuery('#lifestyle_form_wrapper form').serializeArray(), function(i, field) {
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