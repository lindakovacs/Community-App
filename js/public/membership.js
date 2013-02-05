jQuery(document).ready(function($) {

    // beat Chrome's HTML5 tooltips for form validation
    $('form#pl_lead_register_form input[type="submit"]').on('mousedown', function() {
      validate_register_form();
    });
    $('form#pl_login_form input[type="submit"]').on('mousedown', function() {
      validate_login_form();
    });
    
    // Catch "Enter" keystroke and block it from submitting, except on Submit button
    $('#pl_lead_register_form').bind("keypress", function(e) {
      var code = e.keyCode || e.which;
      if (code  == 13) {
        validate_register_form();
      }
    });
    $('#pl_login_form').bind("keypress", function(e) {
      var code = e.keyCode || e.which;
      if (code  == 13) {
        validate_login_form();
      }
    });
    
    $('#pl_lead_register_form').bind('submit', function(e) {
        
        // prevent default form submission logic
        e.preventDefault();
        var form = $(this);
        
        if ($('.invalid', this).length) {
          return false;
        };
        
        nonce = $(this).find('#register_nonce_field').val();
        username = $(this).find('#user_email').val();
        email = $(this).find('#user_email').val();
        password = $(this).find('#user_password').val();
        confirm = $(this).find('#user_confirm').val();
        name = $(this).find('#user_fname').val();
        phone = $(this).find('#user_phone').val();

        data = {
            action: 'pl_register_lead',
            username: username,
            email: email,
            nonce: nonce,
            password: password,
            confirm: confirm,
            name: name,
            phone: phone
        };

        return register_user(data);
    });
    
    
    
    // initialize validator and add the custom form submission logic
    $("form#pl_login_form").bind('submit', function(e) {

      // prevent default form submission logic
      e.preventDefault();
      var form = $(this);
       
      if ($('.invalid', this).length) {
        return false;
      };

      username = $(form).find('#user_login').val();
      password = $(form).find('#user_pass').val();
      remember = $(form).find('#rememberme').val();

      return login_user (username, password, remember);
    });
    
    if(typeof $.fancybox == 'function') {
        // Register Form Fancybox
        $(".pl_register_lead_link").fancybox({
            'hideOnContentClick': false,
            'scrolling' : true,
            onClosed : function () {
              $(".register-form-validator-error").remove();
            }
        });
        // Login Form Fancybox
        $(".pl_login_link").fancybox({
            'hideOnContentClick': false,
            'scrolling' : true,
            onClosed : function () {
              $(".login-form-validator-error").remove();
            }
            
        });

        $(document).ajaxStop(function() {
            favorites_link_signup();
        });
    }

    favorites_link_signup();

    function favorites_link_signup () {
        if(typeof $.fancybox == 'function') {
            $('.pl_register_lead_favorites_link').fancybox({
              'hideOnContentClick': false,
              'scrolling' : true
            }); 
        }
    }
    
    function register_user (data) {
      
      // Need to validate here too, just in case someone press enter in the form instead of pressing submit
      validate_register_form();
      
      $.ajax({
          url: info.ajaxurl,
          data: data, 
          async: false,
          type: "POST",
          success: function(response) {
          
            if (response) {
              
                // Error Handling
                var errors = jQuery.parseJSON(response);
                
                // jQuery Tools Validator error handling
                // $('form#pl_lead_register_form').validator();
                
                // take possible errors and create new object with correct ones to pass to validator
                error_array = new Array("user_email", "user_password", "user_confirm");
                new_error_array = new Object();
                $(error_array).each(function(i, v) {
                  if (typeof errors[v] != "undefined") { 
                    new_error_array[v] = errors[v];
                  }
                });
                
                $('form#pl_lead_register_form input').data("validator").invalidate(new_error_array);
               
            } else {
              
                event.preventDefault ? event.preventDefault() : event.returnValue = false;
               
               // remove error messages
               $('.register-form-validator-error').remove();
               
               // Remove form
               $("#pl_lead_register_form_inner_wrapper").slideUp();
               
                 // Show success message
                 setTimeout(function() {
                   $("#pl_lead_register_form .success").show('fast');
                 },500);
               
                 // send window to redirect link
                 setTimeout(function () {
                  window.location.href = window.location.href;
                 }, 1500);
               
                $('#pl_lead_register_form .success').fadeIn('fast');
                setTimeout(function () {
                    window.location.href = window.location.href;
                }, 700);
                return true;
            }
         }

      });
      
    }
    
    function login_user (username, password, remember) {
         
       data = {
           action: 'pl_login',
           username: username,
           password: password,
           remember: remember
       };

       var success = false;

       // Need to validate here too, just in case someone press enter in the form instead of pressing submit
       validate_login_form();

       $.ajax({
           url: info.ajaxurl, 
           data: data, 
           async: false,
           type: "POST",
           success: function(response) {
             // console.log(response);
               // If request successfull empty the form
               if ( response == '"You have successfully logged in."' ) {
                 
                 event.preventDefault ? event.preventDefault() : event.returnValue = false;
                 
                 // remove error messages
                 $('.login-form-validator-error').remove();
                 
                 // Remove form
                 $("#pl_login_form_inner_wrapper").slideUp();
                 
                 // Show success message
                 setTimeout(function() {
                   $("#pl_login_form .success").show('fast');
                 },500);
                 
                 // send window to redirect link
                 setTimeout(function () {
                  window.location.href = window.location.href;
                 }, 1500);
                 
                 success = true;
               } else {
                 // Error Handling
                 var errors = jQuery.parseJSON(response);
                 
                 // jQuery Tools Validator error handling
                 $('form#pl_login_form').validator();
                 
                 // take possible errors and create new object with correct ones to pass to validator
                 error_array = new Array("user_login", "user_pass");
                 new_error_array = new Object();
                 $(error_array).each(function(i, v) {
                   if (typeof errors[v] != "undefined") { 
                     new_error_array[v] = errors[v];
                   }
                 });
                 
                 $('form#pl_login_form input').data("validator").invalidate(new_error_array);
                 
               }
           }
       });

       // allow page redirect of page on success
       if ( ! success ) {
          return false;
        } else {
          return true;
        }
    }

    function validate_register_form () {
      
      var this_form = $('form#pl_lead_register_form');
      
      // get fields that are required from form and execture validator()
      var inputs = $(this_form).find("input[required]").validator({
          messageClass: 'register-form-validator-error', 
          offset: [10,0],
          message: "<div><span></span></div>",
          position: 'top center'
        });
      
      // check required field's validity
      inputs.data("validator").checkValidity();
  }

    function validate_login_form () {
      
      var this_form = $('form#pl_login_form');

      // get fields that are required from form and execture validator()
      var inputs = $(this_form).find("input[required]").validator({
          messageClass: 'login-form-validator-error', 
          offset: [10,0],
          message: "<div><span></span></div>",
          position: 'top center'
        });

      // check required field's validity
      inputs.data("validator").checkValidity();
  }

});