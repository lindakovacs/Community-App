/**
 *  Applies Chosen to forms.
 */
// $( document ).ready( function() {
//     $("select").chosen({allow_single_deselect: true});
// });


// For datatable
jQuery(document).ready(function($) {

    // Filter Filters
    get_custom_filter_choices();

    $('#pls_admin_my_listings_filters input').live('change', function (event) {
        event.preventDefault();
        if ($(this).is(":checked")) {
            if ($(this).attr('name') == 'custom')
                $('#pls_admin_my_listings > section').not('#listing_types, #location, #basic, #advanced').slideDown();
            else
                $('#pls_admin_my_listings > section#' + $(this).attr('name')).slideDown();
        } else {
            if ($(this).attr('name') == 'custom')
                $('#pls_admin_my_listings > section').not('#listing_types, #location, #basic, #advanced').hide();
            else
                $('#pls_admin_my_listings > section#' + $(this).attr('name')).hide();
        }
        var params = {action : 'filter_options'};
        params['filter'] = $(this).attr('id');
        params['value'] = $(this).is(":checked");
        setTimeout(function() {set_custom_filter_choices(params);}, 100);
    });
    
    function get_custom_filter_choices(params) {
        var params = {action : 'filter_options', get : true};
        $.post(ajaxurl, params, function(data) {
            if (data) {
                $.each(data, function (index, value) {
                    $('input#' + index).prop("checked", value === 'true');
                    if (value === 'true') {
                        if (index == 'custom')
                            $('#pls_admin_my_listings > section').not('#listing_types, #location, #basic, #advanced').slideDown();
                        else
                            $('#pls_admin_my_listings > section#' + index).slideDown();
                    } else {
                        if (index == 'custom')
                            $('#pls_admin_my_listings > section').not('#listing_types, #location, #basic, #advanced').hide();
                        else
                            $('#pls_admin_my_listings > section#' + index).hide();
                    }
                });
            } else {
                $('#pls_admin_my_listings .form_group').hide();
            };
        }, "json");
    }

    function set_custom_filter_choices(params) {
        $.post(ajaxurl, params, function(data) {}, "json");
    }

    //datepicker
    $("input#metadata-max_avail_on_picker, #metadata-min_avail_on_picker").datepicker({
            showOtherMonths: true,
            numberOfMonths: 2,
            selectOtherMonths: true
    });

    var my_listings_datatable = $('#placester_listings_list').dataTable( {
            "bFilter": false,
            "bProcessing": true,
            "bServerSide": true,
            "sServerMethod": "POST",
            'sPaginationType': 'full_numbers',
            'sDom': '<"dataTables_top"pi>lftpir',
            "sAjaxSource": ajaxurl, //wordpress url thing
            "aoColumns" : [
                { sWidth: '100px' },    //images
                { sWidth: '300px' },    //address
                { sWidth: '70px' },     //zip
                { sWidth: '100px' },     //type
                { sWidth: '100px' },     //property
                { sWidth: '60px' },     //beds
                { sWidth: '60px' },     //baths
                { sWidth: '70px' },     //price
                { sWidth: '100px' },    //sqft
                { sWidth: '100px' }     //available
            ],
            "aaSorting": [[0, "desc"]],
            "fnServerParams": function ( aoData ) {
                aoData.push( { "name": "action", "value" : "datatable_ajax"} );
                aoData.push( { "name": "sSearch", "value" : $('input#address_search').val() })
                aoData = my_listings_search_params(aoData);
            }
        });

    var address_timer;
    $('input#address_search').live('keyup', function () {
        clearTimeout(address_timer);
        address_timer = setTimeout(function () {my_listings_datatable.fnDraw();}, 700);

    });

    // hide/show action links in rows
    $('tr.odd, tr.even').live('mouseover', function(event) {
        $(this).find(".row_actions").css('visibility', 'visible');
    });
    $('tr.odd, tr.even').live('mouseout', function(event) {
        $(this).find(".row_actions").css('visibility', 'hidden');
    });


    var delete_listing_confirm = $("#delete_listing_confirm" )
    delete_listing_confirm.dialog({
        autoOpen:false,
        title: '<h2>Delete Listing</h2> ',
        buttons: {
            1:{
                text: "Cancel",
                click: function (){
                    $(this).dialog("close")
                }
            },
            2:{
                text:"Permanently Delete",
                click: function () {
                    $.post(ajaxurl, {action: "delete_listing", id: $('span#delete_listing_address').attr('ref')}, function(data, textStatus, xhr) {
                        console.log(data);
                        if (data) {
                            if (data.response) {
                                $('#delete_response_message').html(data.message).removeClass('red').addClass('green');
                                setTimeout(function () {
                                    window.location.href = window.location.href;
                                }, 750);
                            } else {
                                $('#delete_response_message').html(data.message).removeClass('green').addClass('red');
                            };
                        };
                    }, 'json');
                }
            }
        }
    });
    $('#pls_delete_listing').live('click', function(event) {
        event.preventDefault();
        var property_id = $(this).attr('ref');
        var address = $(this).parentsUntil('tr').children('.address').text();
        $('span#delete_listing_address').html(address);
        $('span#delete_listing_address').attr('ref',property_id);
        delete_listing_confirm.dialog("open");

    });

    // prevents default on search button
    $('#pls_admin_my_listings').live('change', function(event) {
        event.preventDefault();
        my_listings_datatable.fnDraw();
    });
	// parses search form and adds parameters to aoData
	function my_listings_search_params (aoData) {
		var formAdditions = new Array();
		$.each($('#pls_admin_my_listings:visible').serializeArray(), function(i, field) {

			// only push the criteria we need for the call
			if(field.value && field.value != 'false') {
				if(field.value == 'true') field.value = 1;

				if( field.name == 'zoning_types' || field.name == 'listing_types' || field.name == 'purchase_types' ) {
					// API expects these to be arrays
					aoData.push({"name" : field.name + "[]", "value" : field.value});
				} else {
					aoData.push({"name" : field.name, "value" : field.value});
				}

				// if this is an array then tell the api to look for multiple values
				if( field.name.slice(-2) == '[]' ) {
					if ( $.inArray(field.name, formAdditions) == -1) {
						formAdditions.push(field.name);
						var matchname = field.name.slice(0, -2);
						matchname = matchname.slice(-1) == ']' ? matchname.slice(0,-1)+'_match]' : matchname+'_match';
						aoData.push({name: matchname, value: 'in'});
					}
				}
			}
		});

		return aoData;
	}
});



