function ask_user_about_dummy_data () {
    jQuery("#theme_change_dummy_data_box").dialog("open");
}

function theme_switch_notifier () {
    jQuery("#theme_change_dialog_box").dialog("open");
}

// Launch dialog on page load...
jQuery(window).load(function () { ask_user_about_dummy_data(); });

jQuery(document).ready(function($) {

    $("#theme_change_dummy_data_box").dialog({
        dialogClass: "theme_change_dummy_data_box",
        autoOpen: false,
        title: "Would you like demo data?",
        resizable: false,
        // height: 250,
        width: 500,
        modal: true,
        closeText: false,
        zIndex: 999999,
        buttons: [
            {
                text: "No thanks",
                class: "gray-btn",
                click: function() {
                    $(this).dialog("close");
                }
            },
            {
                text: "Set up demo data",
                class: "green-btn right-btn",
                click: function() {
                    $(".theme_change_dummy_data_box .ui-dialog-buttonpane .green-btn").before('<div id="loading-spinner" style="z-index:9999999;margin:-2px 0 0 265px;"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div></div>');
                    $.post(ajaxurl, {action: "add_dummy_data"}, function (result) {
                        $("#theme_change_dummy_data_box").dialog("close");
                        theme_switch_notifier();
                    }, "json");
                }
            }
        ]
    });
  
    $("#theme_change_dialog_box").dialog({
        dialogClass: "theme_change_dialog_box",
        title: "You've added demo data!",
        autoOpen: false,
        resizable: false,
        // height: 250,
        width: 500,
        modal: true,
        zIndex: 999999,
        buttons: [
            {
                text: "Change Menus",
                class: "gray-btn",
                click: function() {
                    $(this).dialog("close");
                    window.open( window.location.protocol + "//" + window.location.host + "/wp-admin/nav-menus.php" );
                }
            },
            {
                text: "Change Pages",
                class: "gray-btn",
                click: function() {
                    $(this).dialog("close");
                    window.open( window.location.protocol + "//" + window.location.host + "/wp-admin/edit.php?post_type=page" );
                }
            },
            {
                text: "Thanks! All set.",
                class: "green-btn right-btn",
                click: function() {
                    $(this).dialog("close");
                }
            }  
        ]
    });

    // Bind both dialogs with the same close handler...
    $("#theme_change_dialog_box").on("dialogclose", function (event) {
        // Check if externally defined handler exists...
        if (typeof(dummy_data_close_handler) == typeof(Function)) {
            dummy_data_close_handler();
        }
    });
  
});