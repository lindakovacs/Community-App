function Neighborhood ( params ) {
	var that = this;
	this.map = params.map || false;
	this.type = params.type || 'neighborhood';
  
	this.strokeColor = params.strokeColor || null;
	this.strokeOpacity  = params.strokeOpacity || null;
	this.strokeWeight  = params.strokeWeight || null;
	this.fillColor  = params.fillColor || null;
	this.fillOpacity  = params.fillOpacity || null;

	this.neighborhood_override = false;

	this.slug = params.slug || false;
  this.name = params.name || false;
	Neighborhood.prototype.polygon_click = params.polygon_click || function ( polygon ) {
		that.map.selected_polygon = polygon;
		that.map.polygons_exclude_center = true;
		that.map.always_center = true;
		that.map.listings.get();
		for (var i = that.map.polygons.length - 1; i >= 0; i--) {
			that.map.polygons[i].setOptions({fillOpacity: "0.4"});
		}
		polygon.setOptions({fillOpacity: "0.6"});
	}
	Neighborhood.prototype.polygon_mouseover = params.polygon_mouseover || function ( polygon ) {
		polygon.setOptions({fillOpacity: "0.9"});
	}
	Neighborhood.prototype.polygon_mouseout = params.polygon_mouseout || function ( polygon ) {
		polygon.setOptions({fillOpacity: "0.4"});
	}
}

Neighborhood.prototype.init = function () {
	var that = this;	
	var filters = {};
	
	if (this.slug) {
		//if slug, only get that one
		filters.action = 'get_polygons_by_slug';
		filters.slug = this.slug;
		filters.type = this.type;
	} else {
		//if no polygon, get all
		filters.action = 'get_polygons_by_type';
		filters.type = this.type;
	}

	jQuery.ajax({
	    "dataType" : 'json',
	    "type" : "POST",
	    "url" : info.ajaxurl,
	    "data" : filters,
	    "success" : function ( neighborhoods ) {
	    	if ( neighborhoods.length > 0) {
	    		var polygon_options, polygon;
	    		for (var i = neighborhoods.length - 1; i >= 0; i--) {
	    			// catch polygons with invalid permalink (missing term) and don't show
	    			if (typeof neighborhoods[i].permalink !== 'string') {
	    				continue;
	    			}
	    			polygon_options = that.process_neighborhood_polygon( neighborhoods[i] );
	    			polygon = that.map.create_polygon( polygon_options );
	    			if ( that.slug ) {
	    				that.map.selected_polygon = polygon;
	    				that.map.filter_by_bounds = true;
						that.map.listings.get();	
	    			}
	    			
	    		};
	    		if( that.map.center_map_on_polygons ) {
	    			that.map.center_on_polygons();
	    		}
	    	} else {
	    		//manually set filters, force the map to update;
	    		that.neighborhood_override = true;
	    		that.map.listings.filter_override = [];
          		that.map.listings.filter_override.push({ "name": "location["+that.type+"]", "value" : that.name });
          		that.map.listings.filter_override.push({ "name": "location["+that.type+"_match]", "value" : 'eq' });

	    		that.map.listings.get();
	    	}
	    }
	});
}

//converts raw neighborhood polygon data into a useable GMaps polygon object
Neighborhood.prototype.process_neighborhood_polygon = function ( neighborhood ) {
	var polygon_options = {};
	polygon_options.paths = [];
	polygon_options.label = neighborhood.name || false;
	polygon_options.tax = neighborhood.tax || false;
	polygon_options.permalink = neighborhood.permalink || false;

	polygon_options.strokeColor = this.strokeColor || neighborhood.settings.border.color;
	polygon_options.strokeOpacity = this.strokeOpacity || neighborhood.settings.border.opacity;
	polygon_options.strokeWeight = this.strokeWeight || neighborhood.settings.border.weight;
	polygon_options.fillColor = this.fillColor || neighborhood.settings.fill.color;
	polygon_options.fillOpacity = this.fillOpacity || neighborhood.settings.fill.opacity;

	if ( neighborhood.vertices.length > 0 ) {
		var bounds = new google.maps.LatLngBounds();
		for (var i = neighborhood.vertices.length - 1; i >= 0; i--) {
			var point = neighborhood.vertices[i];
			var gpoint = new google.maps.LatLng( point['lat'], point['lng'] );
			polygon_options.paths.push( gpoint );	
			//store the verticies directly so we can center the map without relooping the the polygons
			this.map.polygons_verticies.push( gpoint );
			bounds.extend( gpoint );
		}
		polygon_options.label_center = bounds.getCenter();
	}
	//so we can attach directly to the polygon object
	polygon_options.vertices = neighborhood.vertices;

	return polygon_options;
}