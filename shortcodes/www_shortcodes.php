<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once('api_shortcodes.php');
require_once(BUILDER . 'www/input.php');
require_once(BUILDER . 'www/image.php');


add_shortcode('img', 'img_shortcode');
add_shortcode('test', 'form_test_shortcode');



function form_test_shortcode($args) {
	extract(shortcode_atts(array('id' => '', 'class' => '', 'style' => ''), $args));
	global $global_connection;

	$result = "<form id='{$args['id']}'><br>\n";

	$element = new HTML_Text_Input('test_text', 'test text value');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Hidden_Input('test_hidden', 'test hidden value');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Checkbox_Input('test_checkbox', 'test checkbox value', true);
	$label = new HTML_Label('test_checkbox', 'Test Checkbox');
	$result .= $element->html_string() . $label->html_string() . "<br>\n";

	$element = new HTML_Radio_Input('test_radio', 'test radio value', true);
	$label = new HTML_Label('test_radio', 'Test Radio');
	$result .= $element->html_string() . $label->html_string() . "<br>\n";

	$element = new HTML_Button_Input('test_button', 'test button value');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Submit_Input('test_submit', 'test submit value');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_TextArea('test_text_area', 'test text area value');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Select('test_select');
	$element->add_option(new HTML_Option('test select choice 1', 'Choice 1'));
	$element->add_option(new HTML_Option('test select choice 2', 'Choice 2'));
	$element->add_option(new HTML_Option('test select choice 3', 'Choice 3', true));
	$element->add_option(new HTML_Option('test select choice 4', 'Choice 4'));
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Select('test_multi_select', true);
	$element->add_option(new HTML_Option('test multi choice 1', 'Multi Choice 1'));
	$element->add_option(new HTML_Option('test multi choice 2', 'Multi Choice 2', true));
	$element->add_option(new HTML_Option('test multi choice 3', 'Multi Choice 3', true));
	$element->add_option(new HTML_Option('test multi choice 4', 'Multi Choice 4'));
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_FieldSet('Test Fieldset');
	$element->add_field(new HTML_Checkbox_Input('test_fieldset', 'Fieldset Choice 1'));
	$element->add_field(new HTML_Checkbox_Input('test_fieldset', 'Fieldset Choice 2', true));
	$element->add_field(new HTML_Checkbox_Input('test_fieldset', 'Fieldset Choice 3', true));
	$element->add_field(new HTML_Checkbox_Input('test_fieldset', 'Fieldset Choice 4'));
	$result .= $element->html_string() . "<br>\n";

	return $result;
}

function img_shortcode($args) {
	if(!($url = image_shortcode(shortcode_atts(array('index' => null, 'next' => null), $args), '[data attribute=url]')))
		return;

	$html = new HTML_Image($url, shortcode_atts(array('id' => '', 'class' => '', 'style' => ''), $args));
	return $html->html_string();
}