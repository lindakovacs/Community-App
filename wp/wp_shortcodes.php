<?php


require_once(BUILDER_DIR . 'shortcodes/shortcodes.php');
require_once(BUILDER_DIR . 'shortcodes/shortcode_search_context.php');
require_once(BUILDER_DIR . 'shortcodes/shortcode_search_result.php');
require_once(BUILDER_DIR . 'shortcodes/shortcode_listing.php');
require_once(BUILDER_DIR . 'shortcodes/shortcode_listing_image.php');
require_once(BUILDER_DIR . 'shortcodes/shortcode_page.php');
require_once(BUILDER_DIR . 'shortcodes/template_form.php');


class PL_WP_Shortcode_Global extends PL_Shortcode_Handler {
	protected $sc_connection_context;

	static public function register_shortcodes(PL_Shortcode_Dispatcher $dispatcher) {
		$dispatcher->register_shortcode(__CLASS__, PL_SC_PREFIX . 'connection', 'shortcode_disabled');
	}

	public function __construct($connection_or_context = null) {
		if(is_a($connection_or_context, 'PL_API_Connection'))
			$this->set_connection($connection_or_context);
		else if(is_a($connection_or_context, 'PL_Search_Context'))
			$this->set_search_context($connection_or_context);
		else
			$this->sc_connection_context = null;
	}

	public function set_connection(PL_API_Connection $connection) {
		if($connection) {
			$search_context_handler = new PL_Shortcode_Search_Context(new PL_Search_Context($connection));
			$this->sc_connection_context = new PL_Shortcode_Context($search_context_handler);
		}
		else
			$this->sc_connection_context = null;
	}

	public function set_search_context(PL_Search_Context $search_context) {
		if($search_context) {
			$search_context_handler = new PL_Shortcode_Search_Context($search_context);
			$this->sc_connection_context = new PL_Shortcode_Context($search_context_handler);
		}
		else
			$this->sc_connection_context = null;
	}

	public function connection_shortcode($args, $content, $shortcode) {
		extract(shortcode_atts(array('api_key' => null), $args));
		$this->__construct(null);
		$output = "[$shortcode]";

		if($api_key) {
			$connection = new PL_API_Connection($api_key);
			$connection->enable_attribute(array_keys($connection->get_standard_attributes()));
			$connection->enable_attribute(array_keys($connection->get_custom_attributes()));

			$this->set_connection($connection);
			$output = "[connection api_key=" . $api_key . "]";
		}

		return $output;
	}
}


class PL_WP_Shortcode_System extends PL_Shortcode_System {
	protected $global_handler;

	public function __construct() {
		parent::__construct();

		$this->register_handler_class('PL_WP_Shortcode_Global');

		$this->register_handler_class('PL_Shortcode_Search_Context');
		$this->register_handler_class('PL_Shortcode_Search_Result');
		$this->register_handler_class('PL_Shortcode_Listing');
		$this->register_handler_class('PL_Shortcode_Listing_Image');

		$this->register_handler_class('PL_Shortcode_Page');
		$this->register_handler_class('PL_Template_Form');

		$this->global_handler = new PL_WP_Shortcode_Global();
		$this->attach_handler($this->global_handler);
	}

	public function register_display_shortcodes(PL_Attribute_Map $map) {
		PL_Shortcode_Listing::register_display_shortcodes(self::$dispatcher, $map);
	}

	public function set_connection(PL_API_Connection $connection) {
		$this->global_handler->set_connection($connection);
		$this->register_display_shortcodes($connection);
	}

	public function set_search_context(PL_Search_Context $search_context) {
		$this->global_handler->set_search_context($search_context);
		$this->register_display_shortcodes($search_context->get_connection());
	}
}
