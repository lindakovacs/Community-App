<?php


PL_Admin_International::init();

class PL_Admin_International extends PL_Admin_Page {
	public function __construct() {
		parent::__construct('placester_settings', 180, 'placester_international', 'International', 'International', null);
		$this->require_script('placester-international', PL_ADMIN_JS_URL . 'international.js');
		$this->require_style('placester-settings');
	}

	public function render_admin_content() {
		$default_country = PL_Option_Helper::get_default_country();
		?>

		<div class="settings_option_wrapper">
			<div class="header-wrapper">
				<h2>Set Default Country</h2>
			</div>

			<div class="wrapper">
				<select name="" class="set_default_country" id="set_default_country_select">
					<?php foreach (PLX_Attributes::get_attribute_values('country') as $key => $value): ?>
						<?php if ($key === $default_country): ?>
							<option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option>
						<?php else: ?>
							<option value="<?php echo $key ?>"><?php echo $value ?></option>
						<?php endif ?>

					<?php endforeach ?>
				</select>
				<a class="button-secondary" id="set_default_country" >Set Default</a>
				<div id="default_country_message"></div>
			</div>
			<p>Setting the default country will change the default option in the country selector everywhere in the plugin. This is most convenient when creating a website with listings in a specific country.</p>
		</div>

		<?php
	}


	public static function init() {
		$pl_admin_page = new self();
		add_action( 'wp_ajax_set_default_country', array(__CLASS__, 'set_default_country') );
	}

	public static function set_default_country() {
		if (!empty($_POST['country'])) {
			$result = PL_Option_Helper::set_default_country($_POST['country']);
			$message = ($result ? 'You successfully saved the default country' : 'That\'s already your default country');
		}
		else {
			$result = null;
			$message = 'There was an error -- country was not provided';
		}

		echo json_encode(array('result' => $result, 'message' => $message));
		die();
	}
}

