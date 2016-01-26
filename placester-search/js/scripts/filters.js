function Filters() {
}

Filters.prototype.init = function (params) {
	this.filters = {};
	this.dom_id = params.dom_id || false;
	this.list = params.list || false;
	this.map = params.map || false;
	this.listings = params.listings || false;
	this.custom_update_callback = params.custom_update_callback || false;
	this.className = params['class'] || '.pls_search_form_listings';

	if (params.listener) {
		this.listener = {};
		this.listener.elements = params.listener.elements || this.className + ', #sort_by, #sort_dir';
		this.listener.events = params.listener.events || 'change submit reset';
	} else {
		this.listener = {};
		this.listener.elements = this.className + ', #sort_by, #sort_dir';
		this.listener.events = 'change submit reset';
	}
}

Filters.prototype.listeners = function (callback) {
	var that = this;
	jQuery(this.listener.elements).on(this.listener.events, function (event) {
		if(event.type == 'submit') {
			event.preventDefault();
			that.listings.get(true);
		}
		else if(event.type == 'reset') {
			// the reset event comes before the form values are actually reset, so fire the listings call after returning
			setTimeout(function () { that.listings.get(true); }, 0)
		}
		else {
			// Check to see if a meaningful change to search criteria (i.e., not sort or pagination) triggered this call...
			var search_criteria_changed = (this.className.split(" ").indexOf(that.className.replace('.', '')) >= 0);
			that.listings.get(search_criteria_changed);
		}
	});
}

Filters.prototype.get_values = function () {
	var result = [];
	jQuery.each(jQuery(this.listener.elements).serializeArray(), function (i, field) {
		if(field.value != '') result.push({'name': field.name, 'value': field.value});
	});

	return result;
}

Filters.prototype.set_values = function (search_id) {
	var that = this;
	jQuery.post(info.ajaxurl, {action: 'get_saved_search_filters', search_id: search_id}, function (data, textStatus, xhr) {
		jQuery(that.listener.elements).find('input, select').add(that.listener.elements).each(function (i) {
			var data_name = this.name;
			var data_value = '';

			if (data_name == 'purchase_types[]')
				data_name = 'purchase_types[0]';
			else if (data_name == 'zoning_types[]')
				data_name = 'zoning_types[0]';
			else if (data_name.indexOf('[]', data_name.length - 2) != -1)
				data_name = data_name.substring(0, data_name.length - 2);

			if (data[data_name] != null)
				data_value = data[data_name];

			if(jQuery(this).is('select')) {
				jQuery(this).val(data_value);
				jQuery(this).trigger("liszt:updated");
			}

			else if(jQuery(this).is('input')) {
				switch(jQuery(this).attr('type')) {
					case 'text':
					case 'password':
						jQuery(this).val(data_value);
						break;

					case 'radio':
					case 'checkbox':
						var this_value = jQuery(this).attr('value');
						if(this_value == data_value || Array.isArray(data_value) && data_value.indexOf(this_value) != -1)
							jQuery(this).attr('checked', true);
						else
							jQuery(this).attr('checked', false);
						break;
				}
			}
		});

		// update "Show n" menu -- page number is handled elsewhere
		if(data['limit']) {
			jQuery('select[name=placester_listings_list_length]').val(data['limit']);
			jQuery('select[name=placester_listings_list_length]').trigger("liszt:updated");
		}

		if (typeof that.custom_update_callback == 'function') {
			that.custom_update_callback(data);
		}
	}, 'json');
}
