function Lifestyle_Polygon ( params ) {
	this.map = params.map || alert('You need to give the lifestyle object a map');
	this.select_poi_id = params.select_poi_id || '#lifestyle_select_poi';
}

Lifestyle_Polygon.prototype.init = function () {

	console.log('Lifestyle_Polygon Init');

	var that = this;

	if (this.status_display) {
		google.maps.event.addDomListenerOnce(this.map, 'idle', function() {
			var content = '<div id="polygon_display_wrapper">';
			content += '<h5>Point of Interest</h5>';
			content += '<p id="start_warning">Use controls below to select what points of interests you\'d like to see on the map</p>';
			content += '</div>';
			jQuery('#' + that.status_display.dom_id).append(content);
		});
	}
	
	jQuery('#lifestyle_form_wrapper form, .location_select_wrapper, .location_select').live('change', function(event) {
	  	event.preventDefault();
	  	//clear markers
	  	//clear polygons
	  	that.search_places();
	  });

	  jQuery('#lifestyle_form_wrapper select.location').live('change', function(event) {
	  	event.preventDefault();
	  	that.update_lifestyle_location_selects();
	  });

	this.update_lifestyle_location_selects();
}

Lifestyle_Polygon.prototype.search_places = function () {
	var that = this;
	this.map.show_loading();
	var location = this.get_location();
	console.log(location);
	if (location && location.address) {

		this.map.geocode(location.address, function ( results, status ) {
			if (status == google.maps.GeocoderStatus.OK) {
				// we win
				var point = results[0].geometry.location;
				that.map.map.setCenter(point);
				that.search_callback(point );
			} else {
				// we lost
			}
		});

		// this.map.pls_geocode(location.address, <?php echo self::$map_js_var ?>, search_callback, function () {
		// 	search_callback(new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>));
		// });		
	} else {
		this.search_callback(new google.maps.LatLng(this.map.lat, this.map.lng));
	};
}


Lifestyle_Polygon.prototype.search_callback = function (new_point) {
	var that = this;
	request = {};
	request.action = 'lifestyle_polygon';
	request.location = {};
	request.location = '' + new_point.lat() + ',' + new_point.lng();
	request.radius = jQuery('.location_select select.radius').val();
	request.types = this.get_form_items();
	console.log('this is the request object')
	console.log(request);
	if (!request.types || request.types == "") {
		that.show_select_poi();
		that.map.hide_loading();
		return false;
	};
	console.log('here');
	jQuery.post(info.ajaxurl, request, function(data, textStatus, xhr) {
		
		that.map.clear();

		if (data.places.length <= 0) {
			that.map.show_empty();
			return;
		}
		
		if (data.places) {
			for (var i = 0; i < data.places.length; i++) {						           
				that.map.create_marker({position: new google.maps.LatLng(data.places[i].geometry.location.lat, data.places[i].geometry.location.lng), content:data.places[i].name, icon: 'https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.3|0|FF8429|13|b' }, this);
      		}
		}

  //     	if (data.polygon) {
  //     		var polygon_options = {strokeColor: '#55b429',strokeOpacity: 1.0,strokeWeight: 3, fillColor: 'c0ecac', paths : []};
		// 	for (var i = data.polygon.length - 1; i >= 0; i--) {
		// 		var point = data.polygon[i];
		// 		var gpoint = new google.maps.LatLng( point[0], point[1] );
		// 		polygon_options.paths.push( gpoint );	
		// 		//store the verticies directly so we can center the map without relooping the the polygons
		// 		that.map.polygons_verticies.push( gpoint );
		// 	}
		// 	that.map.create_polygon( polygon_options );
		// }

		if (data.listings) {
			for (var i = data.listings.length - 1; i >= 0; i--) {
				that.map.create_listing_marker( data.listings[i] );
			};
			that.map.center_on_markers();
		}

		if (that.map.markers.length > 0 ) {
			that.map.hide_empty();
			that.map.center();
		} else {
			that.map.show_empty();
		}
		that.map.hide_loading();
		
	}, 'json');
}

Lifestyle_Polygon.prototype.get_form_items = function () {
	var response = [];
	var form_values = [];
  	jQuery.each(jQuery('#lifestyle_form_wrapper form').serializeArray(), function(i, field) {
		form_values.push(field.name);
	});
	if (form_values.length > 0) {
		response = [];
		for (key in form_values) {
			response.push(form_values[key]);
		};
	};
	return response.join('|');
}

Lifestyle_Polygon.prototype.get_location = function () {
	var response = false;
	var location_type = jQuery('#lifestyle_form_wrapper select[name="location"]').val();
	console.log(location_type);
	var location_value = jQuery('.location_select_wrapper select.' + location_type).val();
	console.log(location_value);
	if (location_value != 'Any') {
		response = {};
		response.address = location_value;
	} 
	return response
}

Lifestyle_Polygon.prototype.update_lifestyle_location_selects = function () {
	var location_type = jQuery('#lifestyle_form_wrapper select[name="location"]').val();
	jQuery('.location_select_wrapper').hide();
	jQuery('.location_select_wrapper select.' + location_type).parent().show().find('.chzn-container').css('width', '150px');
}

Lifestyle_Polygon.prototype.show_select_poi = function () {
	jQuery(this.select_poi_id).fadeIn();
}

Lifestyle_Polygon.prototype.hide_select_poi = function () {
	jQuery(this.select_poi_id).fadeOut();	
}