<?php
/**
Plugin Name: Placester Real Estate API
Description: Quickly create a lead generating real estate website for your real property.
Plugin URI: https://placester.com/
Author: Placester.com
Version: 0.1
Author URI: https://www.placester.com/
 */


require_once(BUILDER_DIR . 'www/form.php');


add_shortcode('img', 'img_shortcode');
add_shortcode('test', 'form_test_shortcode');



function form_test_shortcode($args) {
	extract(shortcode_atts(array('id' => '', 'class' => '', 'style' => ''), $args));

	global $global_connection;
	$global_connection = PL_WP_API_Connection::get_connection();

	$result = "<form id='{$args['id']}'><br>\n";

	$element = new PL_Text_Input('test_text', 'Test text value', 'asdf');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Hidden_Input('test_hidden', 'test hidden value');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Checkbox_Input('test_checkbox', 'test checkbox value', true);
	$label = new HTML_Label('test_checkbox', 'Test Checkbox');
	$result .= $element->html_string() . $label->html_string() . "<br>\n";

	$element = new HTML_Radio_Input('test_radio', 'test radio value', true);
	$label = new HTML_Label('test_radio', 'Test Radio');
	$result .= $element->html_string() . $label->html_string() . "<br>\n";

	$element = new HTML_Button('test button');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_Submit_Button('test submit');
	$result .= $element->html_string() . "<br>\n";

	$element = new HTML_TextArea('test_text_area', 'test text area value');
	$result .= $element->html_string() . "<br>\n";

	$element = new PL_Select('test_select', 'Test Select');
	$element->add_option('test select choice 1', 'Choice 1');
	$element->add_option('test select choice 2', 'Choice 2');
	$element->add_option('test select choice 3', 'Choice 3', true);
	$element->add_option('test select choice 4', 'Choice 4');
	$result .= $element->html_string() . "<br>\n";

	$element = new PL_Select('test_multi_select', 'Test Multi Select', true);
	$element->add_option('test select choice 1', 'Choice 1');
	$element->add_option('test select choice 2', 'Choice 2');
	$element->add_option('test select choice 3', 'Choice 3', true);
	$element->add_option('test select choice 4', 'Choice 4');
	$result .= $element->html_string() . "<br>\n";

	$element = new PL_Checkbox_Select('test_checkbox_select', 'Test Fieldset');
	$element->add_option('test select choice 1', 'Choice 1');
	$element->add_option('test select choice 2', 'Choice 2', true);
	$element->add_option('test select choice 3', 'Choice 3', true);
	$element->add_option('test select choice 4', 'Choice 4');
	$result .= $element->html_string() . "<br>\n";

	$filter = $global_connection->new_search_request(array('locality' => 'Somerville'));
	$builder = new PL_Form_Builder($global_connection, $filter);
	$element = $builder->get_search_filter_menu('neighborhood', array('type' => 'radio'));
	$result .= $element->html_string() . "<br>\n";

	$element = $builder->get_search_filter_entry('neighborhood', array('datalist' => true));
	$result .= $element->html_string() . "<br>\n";

	$result .= "</form>\n";
	return $result;
}
