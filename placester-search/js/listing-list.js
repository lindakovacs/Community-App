function List () {}

List.prototype.sEcho = 1;

List.prototype.init = function (params) {
	var that = this;
	//list settings
	this.loading_class = params.loading_class || '.dataTables_processing';
	this.dom_id = params.dom_id || '#placester_listings_list';
	this.sort_wrapper = params.sort_wrapper || '.sort_wrapper';
	this.sort_by = params.sort_by || false;
	this.sort_type = params.sort_type || false;
	this.limit_dropdown_class = params.limit_dropdown_class || '.dataTables_length';
	this.pagination_id = params.pagination_id || this.dom_id + '_paginate';
	this.custom_search_results_id = this.custom_search_results_id || '#pls_listings_search_results';
	this.className = params['class'] || false;
	this.table_row_selector = params.table_class || '.placester_properties tr';
	this.context = params.context || false;
	this.total_results_id = params.total_results_id || '#pls_num_results';
	this.limit_default = params.limit_default || 10;
	this.limit_choices = params.limit_choices || [[10, 25, 50], [10, 25, 50]];
	this.settings = params.settings || { 
		"bFilter": false,
		"bProcessing": true,
		"bServerSide": true,
		"sServerMethod": "POST",
		'sPaginationType': 'full_numbers',
		"sAjaxSource": info.ajaxurl,
		'iDisplayLength': this.limit_default,
		'aLengthMenu': this.limit_choices,
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			if ((iDisplayIndex + 1) % 3 == 0) {
				jQuery(nRow).addClass('third');
			};
		},
		'oLanguage': {
			"sProcessing": "Processing...",
			"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
			"sInfoEmpty": "Showing 0 to 0 of 0 entries",
			"sEmptyTable": "No data available in table"
		}
	};

	this.results_as_total = 0;
	this.fnCallback = params.fnCallback || false;
	this.manual_callback = params.manual_callback || false;

	//objects
	this.listings = params.listings || alert('List: You need to include a listings object');
	this.map = params.map || false;

	//empty settings
	this.hide_on_empty = params.hide_on_empty || false;
	this.empty_id = params.empty_id || false;

	this.settings.fnServerData = function (sSource, aoData, fnCallback) {
		if (params.get_listings) {
			params.get_listings( that, sSource, aoData, fnCallback )
		} else {
			that.get_listings( that, sSource, aoData, fnCallback )
		}
	}
}

List.prototype.get_listings = function (self, sSource, aoData, fnCallback) {
	var that = self;
	that.show_loading();
	that.fnCallback = fnCallback;
	that.listings.get();
}

List.prototype.update = function (ajax_response) {
	this.total_results(ajax_response);

	if (this.fnCallback) {
		this.fnCallback(ajax_response);
		// If reg form available or logged in then show add to favorites
		if (jQuery('.pl_lead_register_form').length || jQuery('.pl_add_remove_lead_favorites #pl_add_favorite').length) {
			jQuery('div#pl_add_remove_lead_favorites,.pl_add_remove_lead_favorites').show();
		}
	}

	if (ajax_response.iDisplayStart && ajax_response.iDisplayLength) {
		var dataTable = this.datatable;
		var tSettings = dataTable.fnSettings();
		var curOffset = tSettings._iDisplayStart;
		var curLimit = tSettings._iDisplayLength;
		var newOffset = Number(ajax_response.iDisplayStart);
		var newLimit = Number(ajax_response.iDisplayLength);

		if (newOffset !== curOffset || newLimit !== curLimit) {
			tSettings._iDisplayStart = newOffset;
			tSettings._iDisplayLength = newLimit;
			dataTable._fnCalculateEnd(tSettings);
			dataTable._fnUpdateInfo(tSettings);
			jQuery.fn.dataTableExt.oPagination.full_numbers.fnUpdate(tSettings, function (oSettings) {
				dataTable._fnCalculateEnd(oSettings);
				dataTable._fnDraw(oSettings);
			});
		}
	}

	this.update_favorites_through_cache();
	this.hide_loading();

	if (ajax_response.aaData.length > 0) {
		this.hide_empty();
	} else {
		this.show_empty();
	}
}

List.prototype.total_results = function (ajax_response) {
	this.results_as_total = ajax_response.iTotalDisplayRecords;
	jQuery(this.total_results_id).html(this.results_as_total);
}

List.prototype.update_favorites_through_cache = function () {
	jQuery.post(info.ajaxurl, {action: 'get_favorite_properties'}, function(data, textStatus, xhr) {
		if (data) {
			jQuery('#pl_add_remove_lead_favorites .pl_prop_fav_link').each(function(index) {
				var flag = false;
				for (var i = data.length - 1; i >= 0; i--) {
					//this listing should be a favorite
					if (jQuery(this).attr('href') == ('#' + data[i]) ) {
						if (jQuery(this).attr('id') == 'pl_add_favorite') {
							jQuery(this).hide();
						} else {
							jQuery(this).show();
						}
						flag = true;
					}
				};
				// this listing shouldn't be a favorite...
				if (!flag) {
					if (jQuery(this).attr('id') == 'pl_add_favorite') {
						jQuery(this).show();
					} else {
						jQuery(this).hide();
					}
				}
			});
		}
	}, 'json');
}

List.prototype.row_mouseover = function (listing_id) {
	jQuery(this.table_row_selector).find('[data-listing=' + listing_id + ']').trigger('mouseenter');
}

List.prototype.row_mouseleave = function (listing_id) {
	jQuery(this.table_row_selector).find('[data-listing=' + listing_id + ']').trigger('mouseleave');
}

List.prototype.listeners = function () {
	var that = this;
	if (this.map) {
		jQuery(this.table_row_selector).live({
			mouseenter: function () {
				jQuery(this).addClass('hover');
				that.map.marker_mouseover( jQuery(this).children().children().attr('data-listing') )
			},
			mouseleave: function () {
				jQuery(this).removeClass('hover');
				that.map.marker_mouseout( jQuery(this).children().children().attr('data-listing') )
			}
		});
	}
}

List.prototype.show_loading = function () {
	jQuery(this.loading_class).css('visibility', 'visible');
}

List.prototype.hide_loading = function () {
	jQuery(this.loading_class).css('visibility', 'hidden');
}

List.prototype.show_empty = function () {
	if (this.hide_on_empty) {
		jQuery(this.dom_id).hide();
		jQuery(this.sort_wrapper).hide();
		jQuery(this.limit_dropdown_class).hide();
		jQuery(this.pagination_id).hide();
		jQuery(this.custom_search_results_id).hide();
	}

	if (this.empty_id)
		jQuery(this.empty_id).show();
}

List.prototype.hide_empty = function () {
	if (this.hide_on_empty) {
		jQuery(this.dom_id).show();	
		jQuery(this.sort_wrapper).show();
		jQuery(this.limit_dropdown_class).show();
		jQuery(this.pagination_id).show();
		jQuery(this.custom_search_results_id).show();
	}

	if (this.empty_id)
		jQuery(this.empty_id).hide();
}
