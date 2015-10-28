<?php


require_once(BUILDER . 'api/connection.php');
require_once('input.php');


class PL_Page extends HTML_Div {
	protected $template;
	protected $form;

	public function __construct($id, $template = null) {
		parent::__construct($id);
		$this->template = $template;
		$this->form = new PL_Form($id . '_form');
	}

	public function instantiate(PL_API_Connection $connection, PL_Search_Filter $filter = null) {
		$builder = new PL_Form_Builder($connection, $filter);

		$group = new HTML_FieldSet('Location');
		$group->add_content($builder->get_search_filter_menu('locality'));
		$group->add_content($builder->get_search_filter_menu('region'));
		$group->add_content($builder->get_search_filter_menu('postal'));
		$this->add_content($group);

		$group = new HTML_FieldSet('Listing Type');
		$group->add_content($builder->get_search_filter_menu('purchase_type'));
		$group->add_content($builder->get_search_filter_menu('property_type'));
		$group->add_content($builder->get_search_filter_menu('zoning_type'));
		$this->add_content($group);

		$group = new HTML_FieldSet('Price Range');
		$group->add_content($builder->get_search_filter_menu('min_price'));
		$group->add_content($builder->get_search_filter_menu('max_price'));
		$this->add_content($group);

		$group = new HTML_FieldSet('Details');
		$group->add_content($builder->get_search_filter_menu('min_beds'));
		$group->add_content($builder->get_search_filter_menu('min_baths'));
		$this->add_content($group);
	}
}