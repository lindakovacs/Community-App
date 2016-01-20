/*
 * Onboarding Wizard global object -- contains each stage's data + state information
 */

var wizard_global = {
  	states: {
  		welcome: {
  			header: 'Welcome!',
  			content: 'Great!  You\'re making all the right moves.  We\'re going to take you into the main admin panel now so you can further customize your web site.<br />'
  					  + '<br />You can always return to this customization wizard by clicking Appearance in the main menu, then clicking "Customize."',
  			link: 'Let\'s Get Started',
  			left: '39%',
  			top: '33%',
  			next_state: 'mls'
  		},
      mls:  {
        header: 'MLS Integration',
        content: '',
        link_text: 'Integrate with your MLS',
        next_state: 'theme'
      },
  		theme: {
  			header: 'Theme Selection',
  			content: '',
  			link_text: 'Select a Theme',
  			next_state: 'colors'
  		},
      colors: {
        header: 'Colors & Style',
        content: '',
        link_text: 'Customize your Theme',
        next_state: 'title' // switch back to 'brand' when finished...
      },
  		title: {
  			header: 'Slogan & Title',
  			content: '',
  			link_text: 'Add a Title',
  			next_state: 'confirm'
  		},
  		confirm: {
  			header: 'Save your Changes',
  			content: 'Alright, all done for now -- you can view these customization options in the future by visiting Appearance -> Customize from the admin panel',
  			link_text: 'View my Site',
  			next_state: ''
  		}
  	},
  	initial_state: 'welcome',
    state_num: 0, 
  	active_state: 'welcome', // Set to initial value...
    top_default: 50,
    left_default: 75,
    top_reload: true,
    previewLoaded: function () {
      // Only perform these actions the FIRST time the preview loads...
      if ( this.top_reload ) 
      {
        if ( window.location.href.indexOf('theme_changed=true') == -1 ) {
          // Kick things off by loading the initial state...
          jQuery('#full-overlay').prepend('<div id="welcome-overlay"></div>');

          wiz = this;
          jQuery('#welcome-overlay').fadeIn(500, function () {
            loadState(wiz.initial_state);
          });

          // Instrument...
          pls_track_event("Customizer - Loaded");
        }
        else {
          this.active_state = 'theme';
          this.state_num = 1; // This is subject to change...

          // Insert menu overlay (to prevent clicking other menu items directly...)
          // Commented out for now to test usability
          // generateMenuOverlay();

          // Tack on tooltip display elements needed going forward...
          var tooltip = jQuery('#tooltip');
          tooltip.addClass('arrow');
          tooltip.find('a.close').show();

          moveToNextState();
          loadState(this.active_state);
        }
      }
      
      // Ensures we only do this once per main page load (i.e., only the first time the preview is loaded)...
      this.top_reload = false;
    }
  }


/*
 * Onboarding global functions
 */

function loadState (state) {
    var tooltip = jQuery('#tooltip');

    // Retrieve associated state object...
    var stateObj = wizard_global.states[state];
  
    // Populate tooltip w/given state's copy... (no need to do this for initial state, rendered in response)
    if ( state != wizard_global.initial_state ) {
        var header = wizard_global.state_num + '. ' + stateObj.header;
        tooltip.find('h4').text(header);
        tooltip.find('p.desc').html(stateObj.content);
        tooltip.find('.link a').text(stateObj.link_text);
    }

    // Position tooltip + make sure it is visible...
    var top = ( 'top' in stateObj ) ? stateObj.top : (wizard_global.top_default * wizard_global.state_num) + 'px';
    tooltip.css('top', top);

    var left = ( 'left' in stateObj ) ? stateObj.left : wizard_global.left_default + 'px';
    tooltip.css('left', left);
  
    tooltip.show();
}

function moveToNextState () {
    var currStateObj = wizard_global.states[wizard_global.active_state];
    wizard_global.active_state = currStateObj.next_state;
    ++wizard_global.state_num;
}

function openStatePane () {
    // Just mimic related menu-item click...
    jQuery('#' + wizard_global.active_state).trigger('click');

    // Set active state to the next state + hide the tooltip...
    moveToNextState();
    jQuery('#tooltip').hide();
}

function generateMenuOverlay () {
    var tooltip = jQuery('#tooltip');

    // Check for existence -- create and bind event if not there...
    if ( jQuery('#menu-overlay').length == 0 ) {
        jQuery('#menu-nav').prepend('<div id="menu-overlay"></div>');
        jQuery('#menu-overlay').on('click', function () { 
            // If a pane is not already open (i.e., tooltip IS visible), move open active state's pane...
            if ( tooltip.css('display') != 'none' ) {
                openStatePane();
            }
            else if ( jQuery('#pane').css('display') != 'none' ) {
                // Make "move to next step" glow/light-up!!!
            }
            else {
                tooltip.show();
            }
        });
    }
}

/*
 * Onboarding Wizard actions + flow
 */

jQuery(document).ready(function($) {
    // Append "next/skip" to existing panes...
    $('#pane').prepend('<a class="wizard-next" href="#">Move to Next Step</a>');

    // Main tooltip element...
    var tooltip = $('#tooltip');

    // Set altered to true as we're guiding them anyways...
    customizer_global.stateAltered = true;
    $('#confirm').fadeTo(100, 1);

	// Bind main action of clicking tooltip link...
	$('#tooltip .link a').on('click', function (event) {
		event.preventDefault();

		// Initial state's link has been clicked...
		if (wizard_global.active_state == wizard_global.initial_state) {
            // Get rid of welcome overlay & hide tooltip
            tooltip.hide();
            $('#welcome-overlay').remove();

            // Insert menu overlay (to prevent clicking other menu items directly...)
            // Commented out for now to test usability
            // generateMenuOverlay();

            // Tack on tooltip display elements needed going forward...
            tooltip.addClass('arrow');
            tooltip.find('a.close').show();

            // Bring the tooltip back into focus with the next state loaded...
    		moveToNextState();
    			
      	     // Added to bring MLS popup directly after "Let's get started"
            if (wizard_global.active_state === 'mls') {
                openStatePane();
            } 
            else {
                loadState(wizard_global.active_state);
            }

            // Instrument...
            pls_track_event("Customizer - Intro Dismissed");
		}
		else {
            // console.log('Here!');
            openStatePane();	
		}
	});

    // Handle close tooltip close...
    $('#tooltip a.close, #navlist li').on('click', function (event) {
        event.preventDefault();
        tooltip.hide();

        // TODO: If pane is opened while tooltip is still visible, alter state accordingly...
    });

    // Load the next state...
    $('a.wizard-next').on('click', function (event) {
        event.preventDefault();

        // Mimic hide-pane functionality...
        $('#logo').trigger('click');

        // Load the activate state (which was bumped to next when the last pane appeared)...
        loadState(wizard_global.active_state);
    });

    // Bind move-to-next-state event to custom buttons...
    $('#customize_integration_no').on('click', function (event) {
        loadState(wizard_global.active_state);
    });

    // Custom additional handler for submit theme that prevents the beforeunload prompt...
    $('#submit_theme').on('click', function (event) {
        customizer_global.stateAltered = false;
    });

});