function Status_Window ( params ) {
	var that = this;
	this.params = params;
	if ( typeof params === 'undefined' )
		alert('You need to pass in parameters');
	
	//objects 
	this.listings = params.listings || alert('You must attach a listings object to your status object');
	this.map = this.listings.map || alert('You need to attach a map to the listings object if you want the status object actually work');
	this.filter_position = params.fitler_position || google.maps.ControlPosition.RIGHT_TOP;
	this.className = params['class'] || 'map_filter_area';
	this.dom_id = params.dom_id || 'map_filter_area';

	//functions representing states
	this.on_load = params.on_load || false;
	this.some_results = params.some_results || false;
	this.empty = params.empty || false;
	this.loading = params.loading || false;
	this.update_map_on_drag = false;
	this.prompt_map_on_drag = false;

	//status indicators
	this.title = false;
	this.body = false;
	this.footer = false;
	this.active_title = false;
	this.active_body = false;
	this.active_footer = false;
}

Status_Window.prototype.init = function () {
		
	if ( this.map.type == 'listings' ) {
		this.listings_init();
	} else if ( this.map.type == 'neighborhood' ) {
		this.neighborhood_init();
	} else if ( this.map.type == 'lifestyle') {
		this.lifestyle_init();
	} else if ( this.map.type == 'lifestyle_polygon' ) {
		this.initalize_lifestyle_polygon_init();
	}

}

// TODO: complete this later
Status_Window.prototype.neightborhood_init = function () {}
Status_Window.prototype.lifestyle_init = function () {}
Status_Window.prototype.lifestyle_polygon_init = function () {}

//default initialization states
Status_Window.prototype.listings_init = function () {
	var that = this;
	this.on_load = this.params.on_load || function () {
		that.title = '<h4>First Load</h4>';
		that.body = 'Some text that needs to be long';
		that.footer = that.drag_reload_modal();
		that.update();
	}

	this.some_results = this.params.some_results || function () {
		that.title = '<h4>You have Results!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.empty = this.params.empty || function () {
		that.title = '<h4>Empty!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.loading = this.params.loading || function () {
		that.title = '<h4>Loading</h4>';
		that.body = 'New listings are on the way!';
		that.update();
	}

	this.dragging = this.params.dragging || function () {
		that.title = '<h4>You are dragging</h4>';
		that.body = 'Let go to see new listings';
		that.update();
	}

	this.full = this.params.full || function () {
		that.title = "<h4>Zoom In</h4>"
		that.body = 'Full here! Try zooming in.';
		that.update();
	}

	this.listeners = this.params.listeners || function () {
		jQuery('#polygon_unselect').live('click', function () {
			that.unselect_polygon();
		});
	}();

}

Status_Window.prototype.neighborhood_init = function () {

	var that = this;
	this.on_load = this.params.on_load || function () {
		that.title = '<h4>First Load</h4>';
		that.body = 'Some text that needs to be long';
		that.footer = 'Just another footer';
		that.update();
	}

	this.some_results = this.params.some_results || function () {
		that.title = '<h4>You have Results!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.empty = this.params.empty || function () {
		that.title = '<h4>Empty!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.loading = this.params.loading || function () {
		that.title = '<h4>Loading</h4>';
		that.body = 'New listings are on the way!';
		that.update();
	}

	this.dragging = this.params.dragging || function () {
		that.title = '<h4>You are dragging</h4>';
		that.body = 'Let go to see new listings';
		that.update();
	}

	this.full = this.params.full || function () {
		that.title = "<h4>Zoom In</h4>"
		that.body = 'Full here! Try zooming in.';
		that.update();
	}

	this.listeners = this.params.listeners || function () {
		jQuery('#polygon_unselect').live('click', function () {
			that.unselect_polygon();
		});
	}();
}

Status_Window.prototype.lifestyle = function () {
	
	
	var that = this;
	this.on_load = this.params.on_load || function () {
		that.title = '<h4>First Load</h4>';
		that.body = 'Some text that needs to be long';
		that.footer = 'Just another footer';
		that.update();
	}

	this.some_results = this.params.some_results || function () {
		that.title = '<h4>You have Results!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.empty = this.params.empty || function () {
		that.title = '<h4>Empty!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.loading = this.params.loading || function () {
		that.title = '<h4>Loading</h4>';
		that.body = 'New listings are on the way!';
		that.update();
	}

	this.dragging = this.params.dragging || function () {
		that.title = '<h4>You are dragging</h4>';
		that.body = 'Let go to see new listings';
		that.update();
	}

	this.full = this.params.full || function () {
		that.title = "<h4>Zoom In</h4>"
		that.body = 'Full here! Try zooming in.';
		that.update();
	}

	this.listeners = this.params.listeners || function () {
		jQuery('#polygon_unselect').live('click', function () {
			that.unselect_polygon();
		});
	}();

}

Status_Window.prototype.lifestyle_polygon = function () {
	

	var that = this;
	this.on_load = this.params.on_load || function () {
		that.title = '<h4>First Load</h4>';
		that.body = 'Some text that needs to be long';
		that.footer = 'Just another footer';
		that.update();
	}

	this.some_results = this.params.some_results || function () {
		that.title = '<h4>You have Results!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.empty = this.params.empty || function () {
		that.title = '<h4>Empty!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.loading = this.params.loading || function () {
		that.title = '<h4>Loading</h4>';
		that.body = 'New listings are on the way!';
		that.update();
	}

	this.dragging = this.params.dragging || function () {
		that.title = '<h4>You are dragging</h4>';
		that.body = 'Let go to see new listings';
		that.update();
	}

	this.full = this.params.full || function () {
		that.title = "<h4>Zoom In</h4>"
		that.body = 'Full here! Try zooming in.';
		that.update();
	}

	this.listeners = this.params.listeners || function () {
		jQuery('#polygon_unselect').live('click', function () {
			that.unselect_polygon();
		});
	}();
}


Status_Window.prototype.update = function () {
	
	if ( !this.active_title || this.active_title != this.title ) {
		jQuery('#title_wrapper').html(this.title);
		this.active_title = this.title;
	}

	if ( !this.active_body || this.active_body != this.body ) {
		jQuery('#body_wrapper').html(this.body);
		this.active_body = this.body;
	}

	if ( !this.active_footer || this.active_footer != this.footer ) {
		jQuery('#footer_wrapper').html(this.footer);
		this.active_footer = this.footer;
	}
	

	// switch ( this.map.type ) {
	// 	case 'listings':
	// 		content += '<h5>Listings Search</h5>';
	// 		content += '<p id="start_warning">Drag the map to refine your search</p>';
	// 		break;
	// 	case 'neighborhood':
	// 		content += '<h5>Neighborhood Search</h5>';
	// 		content += '<p id="start_warning">Click on a highlighted area to start searching</p>';
	// 		break;
	// }

	// var content = '<div id="polygon_display_status">';
	// if (this.map.selected_polygon) {
	// 	jQuery('#' + this.dom_id + ' #start_warning').remove();
	// 	content += '<a id="polygon_unselect">Unselect Neighborhood</a>';
	// 	content += '<div>Selected Neighborhood: ' + this.map.selected_polygon.label + '</div>';
	// 	content += '<div>Number of Listings:' + this.map.listings.ajax_response.iTotalRecords + '</div>';
	// }

	// var formatted_filters = this.get_formatted_filters();
	// if ( formatted_filters.length > 0 ) {
	// 	content += '<ul>';
	// 	for (var i = formatted_filters.length - 1; i >= 0; i--) {
	// 		content += '<li>' + formatted_filters[i].name + formatted_filters[i].value + '</li>'
	// 	};
	// 	content += '</ul>';
	// }

	// content += '</div>';
	// jQuery('#' + this.dom_id).append(content);
}

Status_Window.prototype.add_control_container = function ( append ) {
	var that = this;
	var controlDiv = document.createElement('div');
	controlDiv.id = this.dom_id + append;
	controlDiv.className = this.className;
	controlDiv.style.marginTop = '9px';
	controlDiv.style.marginRight = '7px'; 
	controlDiv.style.padding = '5px';
	
	// Set CSS for the control border.
	var wrapper = document.createElement('div');
	wrapper.id = 'map_filter_area_wrapper';

	var title_wrapper = document.createElement('div');
	title_wrapper.id = 'title_wrapper';
	wrapper.appendChild(title_wrapper);

	var body_wrapper = document.createElement('div');
	body_wrapper.id = 'body_wrapper';
	wrapper.appendChild(body_wrapper);

	var footer_wrapper = document.createElement('div');
	footer_wrapper.id = 'footer_wrapper';
	wrapper.appendChild(footer_wrapper);

	controlDiv.appendChild(wrapper);

	that.map.map.controls[ that.filter_position ].push(controlDiv);
}

Status_Window.prototype.unselect_polygon = function () {
	console.log(this);
	this.map.selected_polygon = false;
	this.listings.get();	
	this.map.center_on_polygons();			
	this.on_load();
}

Status_Window.prototype.drag_reload_modal = function () {
	var that = this;
	var append = '_donkey';
	jQuery('#update_map_on_drag').live('click', function (event) {
		// event.preventDefault()

		if ( jQuery(this).attr('checked') ) {
			that.update_map_on_drag = true;
		} else {
			that.update_map_on_drag = false;
		}
		
	});

	google.maps.event.addListenerOnce(that.map.map, 'drag', function() {
		if ( !that.prompt_map_on_drag ) {
			that.prompt_map_on_drag = true;
			that.add_control_container(append);
			jQuery('#' + that.dom_id + append + ' #title_wrapper').html('<h4>Hey Map Searcher</h4>');
			jQuery('#' + that.dom_id + append + ' #body_wrapper').html('<p>Do you want to reload the map after drags?</p><p><a id="reload_map_yes">Yes</a><a id="reload_map_no">No</a></p>');
		} 

		jQuery('#' + that.dom_id + append + ' #body_wrapper #reload_map_no').live('click', function () {
			that.update_map_on_drag = false;
			jQuery('#update_map_on_drag').attr('checked', 0);
			jQuery('#' + that.dom_id + append).remove();
		});

		jQuery('#' + that.dom_id + append + ' #body_wrapper #reload_map_yes').live('click', function () {
			jQuery('#update_map_on_drag').attr('checked', 1);
			that.listings.get();
			that.update_map_on_drag = true;
			jQuery('#' + that.dom_id + append).remove();
		});	
	});
	

	return '<input id="update_map_on_drag" type="checkbox"/><label>Redo search when map is dragged</label>';
}

Status_Window.prototype.get_formatted_filters = function ( ) {
	var filters = this.listings.active_filters;
	var formatted_filters = [];
	for (var i = filters.length - 1; i >= 0; i--) {

		if ( ( jQuery.inArray(filters[i].name, ['metadata[beds]']) === -1 ) || filters[i].value == "")
			continue;
			
		if (this.filter_translation[filters[i].name])
			filters[i].name = this.filter_translation[filters[i].name];

		formatted_filters.push({ name: filters[i].name, value: filters[i].value })
	}
	return formatted_filters;
}