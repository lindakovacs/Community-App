
// For datatable
jQuery(document).ready(function($) {
    $('section#quick-search .pl-form-value').live('change', function (event) {
        event.preventDefault();
        listings_datatable.fnDraw();
    });

    $('section#basic-search .pl-form-value').live('change', function (event) {
        event.preventDefault();
        listings_datatable.fnDraw();
    });

    $('section#advanced-search .pl-form-value').live('change', function (event) {
        event.preventDefault();
        listings_datatable.fnDraw();
    });

    $('section#search-filters .pl-form-value:not([value="advanced"])').live('change', function (event) {
        event.preventDefault();
        var before = listings_search_params([]);

        if ($(this).is(":checked"))
            $('section#basic-search div#' + $(this).attr('value') + '-parameters').slideDown(500);
        else
            $('section#basic-search > div#' + $(this).attr('value') + '-parameters').slideUp(500);

        possibly_trigger_search(before, 600);
    });

    $('section#search-filters .pl-form-value[value="advanced"]').live('change', function (event) {
        event.preventDefault();
        var before = listings_search_params([]);

        if ($(this).is(":checked")) {
            $('section#advanced-filters').slideDown(500);
            $('section#advanced-search div#extended-parameters').slideDown(500);
        } else {
            $('section#advanced-filters').slideUp(500);
            $('section#advanced-search div#extended-parameters').slideUp(500);
        }

        possibly_trigger_search(before, 600);
    });

    $('section#advanced-filters .pl-form-value').live('change', function (event) {
        event.preventDefault();
        var before = listings_search_params([]);

        if ($(this).is(":checked"))
            $('section#advanced-search div#extended-' + $(this).attr('value') + '-parameters').slideDown(500);
        else
            $('section#advanced-search div#extended-' + $(this).attr('value') + '-parameters').slideUp(500);

        possibly_trigger_search(before, 600);
    });

    // parses search form and adds parameters to aoData
    function listings_search_params (aoData) {
        $.each($('form#pl-admin-listings-form .pl-form-value').filter(':visible').add('section#advanced-filters .pl-form-value').serializeArray(), function(i, field) {
            if(field.value && field.value != 'false') {
                if(field.value == 'true') field.value = 1;
                aoData.push({"name" : field.name, "value" : field.value});
            }
        });

        return aoData;
    }

    // redraw if parameters with values are shown or hidden
    var slide_timer;
    function possibly_trigger_search (before, delay) {
        clearTimeout(slide_timer);
        slide_timer = setTimeout(function () {
            var after = listings_search_params([]);
            if(before.length != after.length)
                listings_datatable.fnDraw();
        }, delay);
    }

    var listings_datatable = $('#placester_listings_list').dataTable({
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
            { sWidth: '120px' },    //listing type
            { sWidth: '120px' },    //property type
            { sWidth: '80px' },     //status
            { sWidth: '60px' },     //beds
            { sWidth: '60px' },     //beds
            { sWidth: '60px' },     //price
            { sWidth: '60px' }      //sqft
        ],
        "aaSorting": [[0, "desc"]],
        "fnServerParams": function ( aoData ) {
            aoData.push( { "name": "action", "value" : "listings_table"} );
            aoData = listings_search_params(aoData);
        }
    });


    //datepicker
    $("input#metadata-max_avail_on_picker, #metadata-min_avail_on_picker").datepicker({
        showOtherMonths: true,
        numberOfMonths: 2,
        selectOtherMonths: true
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
                    $.post(ajaxurl, {action: "delete_listing", id: $('span#delete_listing_address').attr('data-ref')}, function(data, textStatus, xhr) {
                        console.log(data);
                        if (data) {
                            if (data.response) {
                                $('#delete_response_message').html(data.message).removeClass('red').addClass('green');
                                setTimeout(function () {
                                    window.location.href = window.location.href;
                                }, 750);
                            } else {
                                $('#delete_response_message').html(data.message).removeClass('green').addClass('red');
                            }
                        }
                    }, 'json');
                }
            }
        }
    });

    $('#pls_delete_listing').live('click', function(event) {
        event.preventDefault();
        var property_id = $(this).attr('data-ref');
        var address = $(this).parentsUntil('tr').children('.address').text();
        $('span#delete_listing_address').html(address);
        $('span#delete_listing_address').attr('data-ref', property_id);
        delete_listing_confirm.dialog("open");
    });
});



