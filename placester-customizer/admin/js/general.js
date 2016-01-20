/*
 * Allows for site to communicate loading status to customizer interface (or any other equipped top frame)
 * when loading in the theme preview/an iframe.
 */

jQuery(window).load( function () {  
    if ( (top.location != self.location) && top.customizer_global ) {
        top.customizer_global.previewLoaded();

        // Check for onboarding wizard, throw appropriate event..
        if ( top.wizard_global ) {
            top.wizard_global.previewLoaded();
        }
    }
});