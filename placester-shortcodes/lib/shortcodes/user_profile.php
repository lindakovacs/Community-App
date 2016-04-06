<?php
/**
 * Post type/Shortcode to generate a property search form
 *
 */

class PL_User_Profile_CPT extends PL_SC_Base {

	protected $pl_post_type = 'pl_user_profile';

	protected $shortcode = 'pl_user_profile';

	protected $title = 'User Profile';

	protected $help =
		'<p>Add the shortcode [pl_user_profile] to a page to display the current user\'s profile.</p>';

	protected $options = array(
		'context'		=> array( 'type' => 'select', 'label' => 'Template', 'default' => '' ),
		'width'			=> array( 'type' => 'int', 'label' => 'Width', 'default' => 250, 'description' => '(px)' ),
		'height'		=> array( 'type' => 'int', 'label' => 'Height', 'default' => 250, 'description' => '(px)' ),
		'widget_class'	=> array( 'type' => 'text', 'label' => 'CSS Class', 'default' => '', 'description' => '(optional)' ),
	);

	//TODO build from the api
	protected $subcodes = array(
		'name'		=> array('help' => 'User name'),
		'email'		=> array('help' => 'Email address'),
		'phone'		=> array('help' => 'Phone'),
		'company'	=> array('help' => 'Company'),
		'address'	=> array('help' => 'Street address'),
		'locality'	=> array('help' => 'City'),
		'region'	=> array('help' => 'State'),
		'postal'	=> array('help' => 'Postal/Zip code'),
		'country'	=> array('help' => 'Country'),
		'if'		=> array('help' => 'Use to conditionally display some content depending on the value of a user attribute.<br />Format is as follows:<br />
<code>[if attribute=\'some_attribute_name\' value=\'some_value\']some HTML that will be displayed if the condition is true[/if]</code><br />
where:<br />
<code>attribute</code> - (required) The user attribute being checked.<br />
<code>value</code> - (optional) By default the condition is true if the attribute has any value other than being empty. If you wish to test if the attribute matches a specific value, then set that value in this parameter.<br />
For example, to only display user name if it has a value:<br />
<code>[if attribute=\'name\']Username: [name] [/if]</code><br />
'),
		'edit'		=> array('help' => 'Link to edit user profile'),
	);

	protected $default_tpls = array('twentyten');

	protected $template = array(
		'not_logged_in'	=> array(
				'type' => 'textarea',
				'label' => 'HTML to display if the user is not logged in',
				'css' => 'mime_html',
				'default' => '<p>Please login to view your profile.</p>',
				'description' => 'You can use any valid HTML in this field.'
		),

		'snippet_body'	=> array(
			'type' => 'textarea',
			'label' => 'HTML',
			'css' => 'mime_html',
			'default' => '',
			'description'	=> 'You can use any valid HTML in this field to format the template tags.'
		),

		'css' => array(
			'type' => 'textarea',
			'label' => 'CSS',
			'css' => 'mime_css',
			'default' => '',
			'description' => 'You can use any valid CSS in this field to customize the form, which will also inherit the CSS from the theme.'
		),

	);

	private static $singleton = null;
	private static $form_data = array();




	public static function init() {
		self::$singleton = parent::_init(__CLASS__);
	}

	public function __construct() {
		parent::__construct(__CLASS__);
		add_shortcode('pl_user_profile', array($this, 'shortcode_handler'));
	}

	/**
	 * Called when a shortcode is found in a post.
	 * @param array $atts
	 * @param string $content
	 */
	public function shortcode_handler($atts) {
		if (!empty($atts['id'])) {
			// if we are a custom shortcode fetch the record so we can display the correct options
			$options = PL_Shortcode_CPT::get_shortcode_options('pl_user_profile', $atts['id']);
			if ($options!==false) {
				$atts = wp_parse_args($atts, $options);
			}
			else {
				unset($atts['id']);
			}
		}
		$template_id = empty($atts['context']) ? 'shortcode' : $atts['context'];
		$template = PL_Shortcode_CPT::load_template($template_id, 'pl_user_profile');
		if (empty($template['snippet_body'])) {
			return '';
		}

		$op = '';
		if (!empty($template['css'])) {
			$op .= '<style type="text/css">'.$template['css'].'</style>';
		}

		self::$form_data = PL_Membership::get_site_user();
		self::$form_data += array('cur_data'=>array(), 'location'=>array());
		if (is_admin()) {
			self::$form_data['cur_data'] += array('name'=>'User Name', 'email'=>'email@domain.com', 'phone'=>'123-456-7890', 'company'=>'Company Name');
			self::$form_data['location'] += array('address'=>'Address', 'locality'=>'City', 'postal'=>'Postal Code', 'region'=>'State', 'country'=>'Country');
		}
		else {
			self::$form_data['cur_data'] += array('name'=>'', 'email'=>'', 'phone'=>'', 'company'=>'');
			self::$form_data['location'] += array('address'=>'', 'locality'=>'', 'postal'=>'', 'region'=>'', 'country'=>'');
		}

		if (!is_user_logged_in())
			return $op.$template['not_logged_in'];
		else
			return $op.self::do_templatetags($template['snippet_body']).self::_get_edit_form();
	}

	public static function do_templatetags($content) {
		return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
	}

	public static function templatetag_callback($m) {
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}

		$tag = $m[2];
		$atts = shortcode_parse_atts($m[3]);
		$content = $m[5];

		if ($tag == 'if') {
			$val = isset($atts['value']) ? $atts['value'] : null;
			if ((!isset(self::$form_data[$atts['attribute']]) && $val==='') ||
				(isset(self::$form_data[$atts['attribute']]) && (self::$form_data[$atts['attribute']]===$val || (is_null($val) && self::$form_data[$atts['attribute']])))) {
				return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
			}
			else if ((!isset(self::$form_data['cur_data'][$atts['attribute']]) && $val==='') ||
				(isset(self::$form_data['cur_data'][$atts['attribute']]) && (self::$form_data['cur_data'][$atts['attribute']]===$val || (is_null($val) && self::$form_data['cur_data'][$atts['attribute']])))) {
				return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
			}
			else if ((!isset(self::$form_data['location'][$atts['attribute']]) && $val==='') ||
				(isset(self::$form_data['location'][$atts['attribute']]) && (self::$form_data['location'][$atts['attribute']]===$val || (is_null($val) && self::$form_data['location'][$atts['attribute']])))) {
				return self::_do_templatetags(__CLASS__, array_keys(self::$singleton->subcodes), $content);
			}
			return '';
		}
		else if ($tag == 'edit') {
			$content = empty($content) ? 'Edit Profile' : $content;
			return '<a href="#" id="edit_profile_button" class="edit_profile_button">'.$content.'</a>';
		}
		else if ( isset( self::$form_data[$tag] ) ) {
			// use form data from partial to construct
			return $m[1] . self::$form_data[$tag] . $m[6];
		}
		else if ( isset( self::$form_data['cur_data'][$tag] ) ) {
			// use form data from partial to construct
			return $m[1] . self::$form_data['cur_data'][$tag] . $m[6];
		}
		else if ( isset( self::$form_data['location'][$tag] ) ) {
			// use form data from partial to construct
			return $m[1] . self::$form_data['location'][$tag] . $m[6];
		}
		return $m[0];
	}

	private static function _get_edit_form() {
		wp_enqueue_script('jquery-ui-dialog');
		//wp_enqueue_style('jquery-ui');

		$form = '
		<div id="edit_profile" style="display:none;">
			<div id="edit_profile_message"></div>
			<form id="edit_profile_form">
				<div>
					<label>Name</label>
					<input type="text" name="name" value="'.self::$form_data['name'].'" >
				</div>
				<div>
					<label>Company</label>
					<input type="text" name="company" value="'.self::$form_data['company'].'">
				</div>
				<div>
					<label>Email</label>
					<input type="text" name="email" value="'.self::$form_data['email'].'"'.(self::$form_data['email'] == self::$form_data['wp_login'] ? ' disabled' : '').'>
				</div>
				<div>
					<label>Phone</label>
					<input type="text" name="phone" value="'.self::$form_data['phone'].'">
				</div>
				<div>
					<label>Street</label>
					<input type="text" name="location[address]" value="'.self::$form_data['location']['address'].'">
				</div>
				<div>
					<label>City</label>
					<input type="text" name="location[locality]" value="'.self::$form_data['location']['locality'].'" >
				</div>
				<div>
					<label>State</label>
					<input type="text" name="location[region]" value="'.self::$form_data['location']['region'].'">
				</div>
			</form>
		</div>';
		return $form;
	}
}

PL_User_Profile_CPT::init();
