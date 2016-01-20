<?php

global $PL_CUSTOMIZER_THEMES;
$PL_CUSTOMIZER_THEMES = array(
    'Agency' => array(
        'Columbus' => 'columbus',
        'Highland' => 'highland',
        'Manchester' => 'manchester',
        'Tampa' => 'tampa',
        'Ventura' => 'ventura'
    ),
    'Single Property' => array(
		'Bluestone' => 'bluestone',
		'Slate' => 'slate'
	),
	'Premium' => array(
        'Bethesda' => 'bethesda',
        'Charlotte' => 'charlotte',
        'Franklin' => 'franklin',
        'Fremont' => 'fremont',
        'Ontario' => 'ontario',
        'Park City' => 'parkcity',
        'Phoenix' => 'phoenix',
        'Plymouth' => 'plymouth',
        'Toronto' => 'toronto',
        'Sedona' => 'sedona'
	)
);

// Construct a reference to quickly access the list of themes supported by the customizer...
global $PL_CUSTOMIZER_THEME_LIST;
$PL_CUSTOMIZER_THEME_LIST = array();

foreach ($PL_CUSTOMIZER_THEMES as $group => $themes) {
	$PL_CUSTOMIZER_THEME_LIST = array_merge($PL_CUSTOMIZER_THEME_LIST, array_values($themes));
}

global $PL_CUSTOMIZER_THEME_INFO;
$PL_CUSTOMIZER_THEME_INFO = array(
	'bluestone' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => '.agent-email a',
		'pls-user-phone' => '.agent-phone'	
	),
	'slate' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => '.agent-email a',
		'pls-user-phone' => '.agent-phone'	
	),
	'bethesda' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => 'header #header-email a, .widget-pls-agent .email',
		'pls-user-phone' => 'header #header-phone, .widget-pls-agent .phone'
	),
	'charlotte' => array(
		'pls-site-title' => 'header a#site-title',
		'pls-site-subtitle' => '#search-label p',
		'pls-user-email' => '.widget-pls-agent .email a',
		'pls-user-phone' => 'header #header-phone, .widget-pls-agent .phone'
	),
	'columbus' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => 'header .h-email a, footer .f-email a, .widget-pls-agent .email',
		'pls-user-phone' => 'header .h-phone, footer .f-phone, .widget-pls-agent .phone'	
	),
	'highland' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => 'header .phone a, footer .phone a, .widget-pls-agent .email',
		'pls-user-phone' => '.widget-pls-agent .phone' // 'header .phone, footer .phone, .widget-pls-agent .phone'	
	),
	'manchester' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header #slogan',
		'pls-user-email' => 'header .email a, footer .footer-contact a, .widget-pls-agent .nrm-txt a',
		'pls-user-phone' => 'header .phone, .widget-pls-agent .phone'
	),
	'ontario' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => '.widget-pls-agent .email',
		'pls-user-phone' => 'header .user-phone, .widget-pls-agent .phone'
	),
	'tampa' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => 'header .email a, footer .contact a, .widget-pls-agent .agent a',
		'pls-user-phone' => 'header .phone, footer .contact strong, .widget-pls-agent .phone'	
	),
    'toronto' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => '.widget-pls-agent .email',
		'pls-user-phone' => 'header .phone, .widget-pls-agent .phone'
	),
	'ventura' => array(
		'pls-site-title' => 'header h1 a',
		'pls-site-subtitle' => 'header h2',
		'pls-user-email' => 'header .email li a, footer #footer-contact p.info a, .widget-pls-agent .email',
		'pls-user-phone' => 'header .phone li.phone-bg-mid, footer #footer-contact p.info strong, .widget-pls-agent .phone'	
	)
);

?>