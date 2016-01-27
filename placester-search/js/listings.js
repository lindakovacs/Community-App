function Listings (params) {
	this.map = params.map || false;
	this.list = params.list || false;
	this.filter = params.filter || false;
	this.poi = params.poi || false;
	this.hook = params.hook || 'pls_listings_ajax';
	this.sSource = params.sSource || info.ajaxurl;
	this.aoData = params.aoData || [];
	this.active_filters = [];
	this.single_listing = params.single_listing || false;
	this.property_ids = params.property_ids || false;
	this.default_filters = params.default_filters || [];
	this.filter_override = params.filter_override || false;
	this.is_new_search = false;
	this.from_back_button = false;
	this.search_hash = false;
	this.sort_by = params.list ? params.list.sort_by : false;
	this.sort_type = params.list ? params.list.sort_type : false;
	this.disable_saved_search = params.disable_saved_search || false;
}

Listings.prototype.pending = false;

Listings.prototype.init = function () {
	var that = this;

	if (this.filter) {
		this.filter.listeners(function () {
			that.update();
		});
	}

	if (this.map) {
		this.map.listeners();
	}

	if (this.list) {
		this.list.listeners();

		jQuery.address.change(function(event) {
			if (!that.is_new_search) {
				var hash = jQuery.address.value();
				if(hash.charAt(0) == '/') {
					hash = hash.slice(1);
					if (hash == '' || jQuery.isNumeric(hash)) {
						that.from_back_button = true;
						that.get();
					}
				}
			}
		});

		// boot up the datatable
		if (this.map.filter_by_bounds) {
			google.maps.event.addDomListenerOnce(window, 'load', function () {
				google.maps.event.addDomListenerOnce(that.map.map, 'idle', function () {
					that.list.datatable = jQuery(that.list.dom_id).dataTable(that.list.settings);			
				});
			});
		} else {
			this.list.datatable = jQuery(this.list.dom_id).dataTable(this.list.settings);	
		}
		
	}

	if (this.single_listing) {
		this.get();
	}
}

Listings.prototype.get = function (search_criteria_changed) {
	this.is_new_search = true;
	var that = this;

	// if there's a pending request, do nothing.
	if (Listings.prototype.pending) {
		return;
	}

	// If this param exists/was passed and is set to true, it indicates a meaningful change to the search criteria
	// (i.e., not just a change in sort, or data pagination)
	var search_changed = (typeof search_criteria_changed !== "undefined" && search_criteria_changed === true);

	if (search_changed) {
		// Since search criteria changed, set the datatable back to the first page of 
		// results but do NOT trigger a new data fetch as it would interfere with the 
		// one we're currently attempting...
		this.list.datatable.fnPageChange(0, false);
	}

	// if there's a single listing, always return that
	if ( this.map.type == 'single_listing' ) {
		google.maps.event.addDomListenerOnce(window, 'load', function() {
			that.map.update( {'aaData' : [['', that.single_listing]], 'iDisplayLength': 0, 'iDisplayStart': 0, 'sEcho': 0} );	
		});
		return false;
	}

	// or, if we're dealing with a polygon map and there's not a selected polygon, we have zero results
	if ( ( this.map.type == 'neighborhood' && !this.map.selected_polygon && !this.map.neighborhood.neighborhood_override ) ) {

		this.map.update({'aaData': [], 'iDisplayLength': 0, 'iDisplayStart': 0, 'iTotalDisplayRecords': 0, 'iTotalRecords': 0, 'sEcho': this.list.sEcho});
		if (this.list)
			this.list.update({'aaData': [], 'iDisplayLength': 0, 'iDisplayStart': 0, 'iTotalDisplayRecords': 0, 'iTotalRecords': 0, 'sEcho': this.list.sEcho});

		return false;
	}
	this.pending = true;

	var that = this;

	if (that.default_filters.length > 0) {
		that.active_filters = that.default_filters;
	}

	// allows the dev to pass in one or many property ids
	if (this.property_ids) {
		that.active_filters.push({"name": "property_ids", "value":  this.property_ids});	
	}

	// get pagination and sorting information
	if (this.list && this.list.datatable) {
		this.list.show_loading();
		var fnSettings = this.list.datatable.fnSettings();
		that.active_filters.push({"name": "iDisplayLength", "value": fnSettings._iDisplayLength});
		that.active_filters.push({"name": "iDisplayStart", "value": fnSettings._iDisplayStart});
		that.active_filters.push({"name": "sEcho", "value":  ++this.list.sEcho});
		// aoData;
	} else if (this.list) {
		this.list.show_loading();
		that.active_filters.push({"name": "iDisplayLength", "value": this.list.limit_default});
		that.active_filters.push({"name": "iDisplayStart", "value": 0});
		that.active_filters.push({"name": "sEcho", "value": 1});
	}

	if (this.list && this.list.context) {
		that.active_filters.push({"name": "context", "value": this.list.context});
	}

	// Get current state of search filter...
	if (this.filter) {
		that.active_filters = that.active_filters.concat(this.filter.get_values());
	}

	// Sort By if set by list
	if (this.list.sort_by) {
		that.active_filters.push({"name": "sort_by", "value": this.list.sort_by});
	}

	// Sort Type if set by list
	if (this.list.sort_type) {
		that.active_filters.push({"name": "sort_type", "value": this.list.sort_type});
	}

	// Get bounding box or polygon information
	// There should be a map + we either need a type of neighborhood, or filter bounds to be enabled
	if (this.map && (this.map.type == 'neighborhood' || this.map.filter_by_bounds ) ) {
		this.map.show_loading();
		// concat bounds only if bounds exist
		if (this.map.filter_by_bounds) {
			that.active_filters = that.active_filters.concat(this.map.get_bounds());
		}
	}

	if (that.filter_override) {
		for (var i = that.filter_override.length - 1; i >= 0; i--) {
			that.active_filters.push(that.filter_override[i]);
		}
	}
	that.active_filters.push({"name": "action", "value": this.hook});

	// Check submitted filters for multiples of same filter(*) to add "[*_match]=in"
	that.active_filters = that.check_multiple_search_filters(that.active_filters);

	if (that.disable_saved_search === false) {

		// Saved search functionality
		var filters_hash = '/' + that.generate_search_hash();
		var current_hash = jQuery.address.value();

		// Don't display in preview screens, nor in widget pages
		if( window.location.href.indexOf('post_type=pl_general_widget') == -1 
			&& window.location.href.indexOf('post.php?post=') == -1
			&& window.location.href.indexOf('/pl_general_widget/') == -1) {

			// Visiting a permalink, or reloading a previous page
			if ((that.search_hash === false && current_hash != '/') || that.from_back_button) {
				that.active_filters.push( { "name": "saved_search_lookup", "value" : that.search_hash = current_hash } );
				if (that.filter) {
					that.filter.set_values(current_hash);
				}
			}

			// Using POST data (if available) or responding to changes coming from the form
			else if ((that.search_hash === false && current_hash == '/') || search_changed) {
				that.active_filters.push( { "name": "saved_search_hash", "value" : that.search_hash = filters_hash } );
				if(search_changed || filters_hash != '/') {
					jQuery.address.value(that.search_hash);
				}
			}
		}
	}

	jQuery.ajax({
		"dataType": 'json',
		"type": "POST",
		"url": this.sSource,
		"data": that.active_filters,
		"success": function (ajax_response) {
			that.pending = false;
			that.is_new_search = false;
			that.from_back_button = false;
			that.ajax_response = ajax_response;

			// Update the favorite search options if they exist
			if (that.disable_saved_search === false && jQuery.address.value() != '/') {
				jQuery('#pl_favorite_search_links').show();
				if (typeof ajax_response.favorite_search != "undefined")
					if (ajax_response.favorite_search) {
						jQuery('#pl_save_favorite_search').hide();
						jQuery('#pl_clear_favorite_search').show();
						if (ajax_response.favorite_search_email) {
							jQuery('#pl_enable_favorite_search').hide();
							jQuery('#pl_disable_favorite_search').show();
						}
						else {
							jQuery('#pl_enable_favorite_search').show();
							jQuery('#pl_disable_favorite_search').hide();
						}
					}
					else {
						jQuery('#pl_save_favorite_search').show();
						jQuery('#pl_clear_favorite_search').hide();
					}
			}
			else {
				jQuery('#pl_favorite_search_links').hide();
			}

			if (that.map)
				that.map.update( ajax_response );

			if (that.list)
				that.list.update( ajax_response );

			if (that.poi)
				that.poi.update();

			// execute manual_callback function
			if (that.list.manual_callback)
				that.list.manual_callback();

			that.active_filters = [];
		}
	});
}

Listings.prototype.generate_search_hash = function () {
	var joined = ''; var something = false;
	for (var i = this.active_filters.length - 1; i >= 0; i--) {
		if (this.active_filters[i]) {

			if (["action", "context", "saved_search_hash", "saved_search_lookup", "sEcho", "sort_by", "sort_type",
				"iDisplayLength", "iDisplayStart"].indexOf(this.active_filters[i]['name']) >= 0)
				continue;
			joined += this.active_filters[i]['name'];
			joined += this.active_filters[i]['value'];

			if (!this.active_filters[i]['value'] || this.active_filters[i]['name'].search('_match') >= 0)
				continue;
			something = true;
		}
	}

	return something ? this.fast_hasher(joined) : '';
}

Listings.prototype.fast_hasher = function (str) {
    var hash = 0;
    if (str.length == 0) return hash;
    for (i = 0; i < str.length; i++) {
        char = str.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}

Listings.prototype.get_search_count = function () {
  	var that = this;
  	// check search form count on load
  	that.check_search_form_count();
  	jQuery(".pls_search_results_num_form select, .pls_search_results_num_form input[type='text'], .pls_search_results_num_form input[type='hidden']").live("change", function(event) {
    	// check search form count on form changes
    	that.check_search_form_count();
  	});
}

Listings.prototype.check_search_form_count = function () {
	// Add spinner
	jQuery('#pls_num_results_found').before("<div id='pls_search_count_spinner'></div>")

	// Get form data
	var form_data = jQuery('.pls_search_results_num_form').serializeArray();
	// console.log(form_data);

	var data = {};

	form_data = this.check_multiple_search_filters(form_data);

	var multi_value_form_keys = new Array("location[locality][]", "location[neighborhood][]", "location[region][]", "location[postal][]", "metadata[prop_type]");

	for (var i = form_data.length - 1; i >= 0; i--) {
  		if (data[form_data[i].name] && multi_value_form_keys.indexOf(form_data[i].name) != -1) {
	  		var existing_val = data[form_data[i].name];
	  		// if it's already an Array, push values to the array
			if (existing_val instanceof Array) {
				data[form_data[i].name].push(form_data[i].value);
			} else {
				// if it's not already an array, create one and assign the first value
				data[form_data[i].name] = new Array(existing_val, form_data[i].value);
			}
		} else {
			// if the key doesn't exist, give it a value
			data[form_data[i].name] = form_data[i].value;
		}
  }
 
	data.action = 'pls_get_search_count';
	data.iDisplayLength = 1;

	jQuery.ajax({
		"dataType": 'json',
	    "type": "POST",
	    "data": data,
	    "url": info.ajaxurl,
	    "success": function ( response ) {
	        if (response) {
				// add listings count
				jQuery('#pls_num_results_found').html(response.count);

				// Safari fix
				jQuery('#pls_num_results_found').hide();
				jQuery('#pls_num_results_found').get(0).offsetHeight;
				jQuery('#pls_num_results_found').show();			
				
				// remove spinner
				jQuery("#pls_search_count_spinner").remove();
			}
	    }
  	},'json');
}

Listings.prototype.check_multiple_search_filters = function (filters) {
	// Search for multiple cities / neighborhoods / states / property types
	var checkMultiples = new Array("location[locality]", "location[neighborhood]", "location[region]", "location[postal]", "metadata[prop_type]");

	// console.log(checkMultiples);
	for (var i = checkMultiples.length - 1; i >= 0; i--){

		var formAdditions = new Array();

		for (var j = filters.length - 1; j >= 0; j--){

			filter = filters[j];
		  
			// if the filter, minus the "[]" brackets, equals the multi-select location, add it.
			if (filter.name.slice(0,-2) == checkMultiples[i]) {
				// find 1 or more cities entered into form
				formAdditions.push(filter.value);
				delete(filter);
			}
		}

		// Add 'in' match params for multiple cities
		if (formAdditions.length > 1) {

		  	// remove ']' from end of checkMultiples
		  	if (checkMultiples[i].slice(-1) == ']') {
		    	multiFilterMatch = checkMultiples[i].slice(0,-1) + '_match]';
		  	} 
		  	else {
		    	// property_type is the only one that doesn't have brackets from checkMultiples variable.
		    	multiFilterMatch = checkMultiples[i] + '_match';
		  	}
		  
		  	filters.push({name: multiFilterMatch, value: 'in'});
		} 
	}

	return filters;
}
