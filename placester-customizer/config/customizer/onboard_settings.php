<?php

global $PL_CUSTOMIZER_ONBOARD_SECTIONS;
$PL_CUSTOMIZER_ONBOARD_SECTIONS = array(
    'logo' => 1,
    'mls' => 10,
    'theme' => 20,
    'colors' => 30,
    'title' => 40,
    'brand' => 50,
    // 'listing' => 60,
    // 'post' => 70,
    // 'invite' => 80,
    // 'analytics' => 90,
    'confirm' => 1000
);

global $PL_CUSTOMIZER_ONBOARD_OPTS;
$PL_CUSTOMIZER_ONBOARD_OPTS = array( 
    'logo' => array(
        'name' => 'Placester Logo',
        'id' => 'logo',
        'type' => 'heading',
        'class' => 'no-pane'
    ),

    'mls' => array(
        'name' => 'Add MLS / IDX',
        'desc' => 'Integrate Your Listing Data',
        'id' => 'mls',
        'type' => 'heading',
        'components' => array(
            array(
                'name' => 'integration',
                'type' => 'custom'
            )
        )
    ),

    'theme' => array(
        'name' => 'Select Theme',
        'desc' => 'Choose the Right Look-and-Feel for your Business',
        'id' => 'theme',
        'type' => 'heading',
        'components' => array(
            array(
                'name' => 'theme-select',
                'type' => 'custom'
            )
        )
    ),

    'colors' => array(
        'name' => 'Select Color Palette',
        'desc' => 'Customize your Theme to your Heart\'s Content',
        'id' => 'colors',
        'type' => 'heading',
        'components' => array(
            array(
                'name' => 'color-scheme',
                'type' => 'custom'
            ),
            array( 
                'name' => 'Custom CSS',
                'desc' => 'Enter custom css styles here. Will override any theme styles as well as any theme options you\'ve already set.',
                'id' => 'pls-custom-css',
                'type' => 'textarea'
            )
        )
    ),

    'title' => array(
        'name' => 'Site Title & Slogan',
        'desc' => 'Name your Website and Add your Contact Info',
        'id' => 'title',
        'type' => 'heading',
        'components' => array(
            array(
                'name' => 'Site Title',
                'desc' => 'Site title in header.',
                'id' => 'pls-site-title',
                'type' => 'text',
                'transport' => 'postMessage'
            ),
            array(
                'name' => 'Slogan',
                'desc' => 'Site subtitle in header.',
                'id' => 'pls-site-subtitle',
                'type' => 'text',
                'transport' => 'postMessage'
            ),
            array(
                'name' => 'Your Email Address',
                'desc' => 'Add the email address you want to display on the site.',
                'id' => 'pls-user-email',
                'type' => 'text',
                'transport' => 'postMessage'
            ),
            array(
                'name' => 'Your Phone Number',
                'desc' => 'Add the phone you want to display on the site.',
                'id' => 'pls-user-phone',
                'type' => 'text',
                'transport' => 'postMessage'
            )
            // array(
            //     'name' => 'First and Last Name',
            //     'desc' => 'Add the name you want to display on the site.',
            //     'id' => 'pls-user-name',
            //     'type' => 'text'
            // ),
            // array(
            //     'name' => 'Upload Logo',
            //     'desc' => 'Display Your Brand',
            //     'id' => 'brand',
            //     'type' => 'heading'
            // ),
            // array(
            //     'name' => 'Site Logo',
            //     'desc' => 'Upload your logo here. It will appear in the header and will override the title you\'ve provided above.',
            //     'id' => 'pls-site-logo',
            //     'type' => 'upload'
            // )
        )
    ),

    // array(
    //     'name' => 'Create a Listing',
    //     'desc' => 'Quickly Add the First Listing to your Site',
    //     'id' => 'listing',
    //     'type' => 'heading',
    //     'components' => array(
    //         array(
    //             'name' => 'post-listing',
    //             'type' => 'custom'
    //         )
    //     )
    // ),

    // array(
    //     'name' => 'Create a Blog Post',
    //     'desc' => 'Show your Expertise by Creating Unique Content',
    //     'id' => 'post',
    //     'type' => 'heading',
    //     'components' => array(
    //         array(
    //             'name' => 'blog-post',
    //             'type' => 'custom'
    //         )
    //     )
    // ),

    // array(
    //     'name' => 'Google Analytics',
    //     'desc' => 'Track your Site\'s Traffic Statistics',
    //     'id' => 'analytics',
    //     'type' => 'heading',
    //     'components' => array(
    //         array(
    //             'name' => 'Google Analytics Tracking Code',
    //             'desc' => 'Add your google analytics tracking ID code here. It looks something like this: UA-XXXXXXX-X',
    //             'id' => 'pls-google-analytics',
    //             'type' => 'text'
    //         )
    //     )
    // ),

    'confirm' => array(
        'name' => 'Save & Continue',
        'id' => 'confirm',
        'type' => 'heading',
        'class' => 'no-pane'
    )
);

?>