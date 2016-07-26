
// For datatable
jQuery(document).ready(function($) {
    $('section#quick-search .pl-form-value').live('change', function (event) {
        event.preventDefault();
        trigger_search();    });

    $('section#basic-search .pl-form-value').live('change', function (event) {
        event.preventDefault();
        trigger_search();    });

    $('section#advanced-search .pl-form-value').live('change', function (event) {
        event.preventDefault();
        trigger_search();    });

    $('div#basic-filters .pl-form-value:not([value="advanced"])').live('change', function (event) {
        event.preventDefault();
        var before = listings_search_params([]);

        if ($(this).is(":checked"))
            $('section#basic-search div#' + $(this).attr('value') + '-parameters').slideDown(500);
        else
            $('section#basic-search > div#' + $(this).attr('value') + '-parameters').slideUp(500);

        possibly_trigger_search(before, 600);
    });

    $('div#basic-filters .pl-form-value[value="advanced"]').live('change', function (event) {
        event.preventDefault();
        var before = listings_search_params([]);

        if ($(this).is(":checked")) {
            $('div#advanced-filters').slideDown(500);
            $('section#advanced-search').slideDown(500);
        } else {
            $('div#advanced-filters').slideUp(500);
            $('section#advanced-search').slideUp(500);
        }

        possibly_trigger_search(before, 600);
    });

    $('div#advanced-filters .pl-form-value').live('change', function (event) {
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
        $.each($('form#pl-admin-listings-form .pl-form-value').filter(':visible').add('section#search-filters .pl-form-value').serializeArray(), function(i, field) {
            if(field.value && field.value != 'false') {
                if(field.value == 'true') field.value = 1;
                aoData.push({"name" : field.name, "value" : field.value});
            }
        });

        return aoData;
    }

    function trigger_search() {
        $('#placester_listings_list_processing').css('display', 'block'); // "Processing..." stays hidden on initial page load
        listings_datatable.fnDraw();
    }

    // redraw if parameters with values are shown or hidden
    function possibly_trigger_search (before, delay) {
        if(arguments.callee.timer)
            clearTimeout(arguments.callee.timer);

        arguments.callee.timer = setTimeout(function () {
            var after = listings_search_params([]);
            if(before.length != after.length)
                trigger_search();
        }, delay);
    }

    var listings_datatable = $('#placester_listings_list').dataTable({
        "bFilter": false,
        "bProcessing": true,
        "bServerSide": true,
        "sServerMethod": "POST",
        'sPaginationType': 'full_numbers',
        'sDom': '<"dataTables_top"p>lftir',
        "sAjaxSource": ajaxurl, //wordpress url thing
        "aoColumns" : [
            { sWidth: '60px' },     //images
            { sWidth: '300px' },    //address
            { sWidth: '60px' },     //zip
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
            position_processing_message();
            aoData.push( { "name": "action", "value" : "listings_table"} );
            aoData = listings_search_params(aoData);
        }
    });

    function position_processing_message() {
        var tbody = $('#placester_listings_list').find('tbody');
        var pos = tbody.position();
        var size = tbody.css(['width', 'height']);

        $('#placester_listings_list_processing').css({
            'left': pos.left, 'top': pos.top,
            'width': size.width, 'height': size.height
        });
    }

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



