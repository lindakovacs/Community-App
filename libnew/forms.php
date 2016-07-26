<?php


require_once('attributes.php');


class PLX_Form {
	const INPUT = 1;
	const RADIO = 5;
	const CHECKBOX = 6;
	const SELECT = 11;
	const MULTISELECT = 12;
	const RADIO_GROUP = 15;
	const CHECKBOX_GROUP = 16;
	const TEXTAREA = 21;
	const HIDDEN = 31;
	const READONLY = 35;

	public function get_form_item($name, $display, $type, $options, $value = null) {
		if(is_scalar($options)) { // $options is required to be scalar for a radio or checkbox -- it is used as the HTML "value" attribute
			$option = $options;
			$options = array($option => $option);
		}
		else if(is_array($options)) {
			$option = 'Must be scalar';
			if(!empty($options) && array_keys($options) === range(0, count($options) - 1))
				$options = array_combine($options, $options);
		}
		else {
			$option = $name;
			$options = array();
		}

		if(is_scalar($value)) {
			$values = array($value);
		}
		else if(is_array($value)) {
			$values = $value;
			$value = reset($values);
		}
		else {
			$value = null;
			$values = array();
		}

		ob_start();

		if ($type == self::INPUT) {
			?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-input-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-input-label"><?php echo self::esc($display); ?></label>
				<input id="<?php echo "pl-form-$name"; ?>" class="pl-form-value pl-form-input" type="text" name="<?php echo $name; ?>"
					value="<?php echo self::esc($value); ?>"<?php if($options) echo "list=\"pl-form-$name-list\""; ?>>
				<?php if($options) { ?>
					<datalist id="<?php echo "pl-form-$name-list"; ?>">
						<?php foreach($options as $option) { ?><option value="<?php echo $option; ?>"><?php } ?>
					</datalist>
				<?php } ?>
			</div>
			<?php
		}
		else if ($type == self::HIDDEN) {
			?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-hidden-item">
				<input id="<?php echo "pl-form-$name"; ?>" class="pl-form-value pl-form-hidden" type="hidden" name="<?php echo $name; ?>"
					value="<?php echo self::esc($value); ?>">
			</div>
			<?php
		}
		else if ($type == self::READONLY) {
			?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-readonly-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-readonly-label"><?php echo self::esc($display); ?></label>
				<input id="<?php echo "pl-form-$name"; ?>" class="pl-form-value pl-form-readonly" type="text" name="<?php echo $name; ?>"
					value="<?php echo self::esc($value); ?>" readonly="readonly">
			</div>
			<?php
		}
		elseif ($type == self::RADIO) {
			$id_name = "$name-" . $this->idify($option);
		?>
			<div id="<?php echo "pl-form-item-$id_name"; ?>" class="pl-form-item pl-form-radio-item">
				<input id="<?php echo "pl-form-$id_name"; ?>" class="pl-form-value pl-form-radio" type="radio" name="<?php echo $name; ?>"
					value="<?php echo self::esc($option); ?>"<?php if($value === true || $value === $option) echo ' checked="checked"'; ?>>
				<label for="<?php echo "pl-form-$id_name"; ?>" class="pl-form-item-label pl-form-radio-label"><?php echo self::esc($display); ?></label>
			</div>
		<?php
		}
		elseif ($type == self::CHECKBOX) {
			$id_name = "$name-" . $this->idify($option);
		?>
			<div id="<?php echo "pl-form-item-$id_name"; ?>" class="pl-form-item pl-form-checkbox-item">
				<input id="<?php echo "pl-form-$id_name"; ?>" class="pl-form-value pl-form-checkbox" type="checkbox" name="<?php echo $name; ?>"
					value="<?php echo self::esc($option); ?>"<?php if($value === true || $value === $option) echo ' checked="checked"'; ?>>
				<label for="<?php echo "pl-form-$id_name"; ?>" class="pl-form-item-label pl-form-checkbox-label"><?php echo self::esc($display); ?></label>
			</div>
		<?php	
		}
		elseif ($type == self::SELECT) {
		?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-select-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-select-label"><?php echo $display; ?></label>
				<select id="<?php echo "pl-form-$name"; ?>" class="pl-form-value pl-form-select" name="<?php echo $name; ?>">
				<?php foreach($options as $option_name => $option_display) { ?>
					<option class="pl-form-option" value="<?php echo $option_name; ?>"<?php if($option_name === $value) echo ' selected="selected"'; ?>>
						<?php echo self::esc($option_display); ?>
					</option>
				<?php } ?>
				</select>
			</div>
		<?php
		}
		elseif ($type == self::MULTISELECT) {
		?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-multiselect-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-multiselect-label"><?php echo $display; ?></label>
				<select id="<?php echo "pl-form-$name"; ?>" class="pl-form-value pl-form-multiselect" name="<?php echo $name; ?>[]" multiple="multiple">
				<?php foreach($options as $option_name => $option_display) { ?>
					<option class="pl-form-option" value="<?php echo $option_name; ?>"<?php if(in_array($option_name, $values)) echo ' selected="selected"'; ?>>
						<?php echo self::esc($option_display); ?>
					</option>
				<?php } ?>
			</div>
		<?php
		}
		elseif ($type == self::RADIO_GROUP) {
		?>
			<fieldset id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-radio-group">
				<legend class="pl-form-item-legend pl-form-radio-legend"><?php echo $display; ?></legend>
				<?php foreach($options as $option_name => $option_display) {
					echo self::get_form_item($name, $option_display, self::RADIO, $option_name, $option_name === $value ? $option_name : null);
				} ?>
			</fieldset>
		<?php
		}
		elseif ($type == self::CHECKBOX_GROUP) {
		?>
			<fieldset id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-checkbox-group">
				<legend class="pl-form-item-legend pl-form-checkbox-legend"><?php echo $display; ?></legend>
				<?php foreach($options as $option_name => $option_display) {
					echo self::get_form_item($name . '[]', $option_display, self::CHECKBOX, $option_name, in_array($option_name, $values) ? $option_name : null);
				} ?>
			</fieldset>
		<?php
		}
		elseif ($type == self::TEXTAREA) {
		?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-textarea-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-textarea-label"><?php echo self::esc($display); ?></label>
				<textarea id="<?php echo "pl-form-$name"; ?>" class="pl-form-value pl-form-textarea" name="<?php echo $name; ?>">
					<?php echo self::esc($value); ?>
				</textarea>
			</div>
		<?php
		}

		return ob_get_clean();
	}

	private function esc($string) {
		return htmlentities($string);
	}

	private function idify($string) {
		return $string;
	}
}

class PLX_Data_Form extends PLX_Form {
	protected $form_data;

	public function __construct($data = null) {
		$this->set_form_data($data ?: $_POST);

	}

	public function set_form_data($data) {
		$this->form_data = $data;
	}

	public function get_form_item($name, $display, $type, $options, $default = null) {
		return parent::get_form_item($name, $display, $type, $options, $this->get_item_value($name, $default));
	}

	protected function get_item_value($name, $default = null) {
		return isset($this->form_data[$name]) ? $this->form_data[$name] : ($this->form_data ? null : $default);
	}

	// for use by derived classes
	static protected function get_item_type($data_type) {
		switch($data_type) {
			case PLX_Attributes::BOOLEAN:
				return PLX_Form::CHECKBOX;

			case PLX_Attributes::NUMERIC:
			case PLX_Attributes::CURRENCY:
				return PLX_Form::INPUT;

			case PLX_Attributes::DATE_TIME:
			case PLX_Attributes::COORDINATE:
				return PLX_Form::READONLY;

			case PLX_Attributes::TEXT_ID:
				return PLX_Form::INPUT;

			case PLX_Attributes::TEXT_VALUE:
				return PLX_Form::SELECT;

			case PLX_Attributes::SHORT_TEXT:
				return PLX_Form::INPUT;

			case PLX_Attributes::LONG_TEXT:
				return PLX_Form::TEXTAREA;
		}

		return null;
	}
}


class PLX_Attribute_Form extends PLX_Data_Form {
	public function get_form_item($name, $display = null, $type = null, $options = null, $default = null) {
		if(!($attribute = PLX_Attributes::get($name)))
			return null;

		if(is_null($display))
			$display = $attribute['display'];
		if(is_null($type))
			$type = $this->get_default_item_type($attribute);
		if(is_null($options))
			$options = $this->get_default_item_options($attribute);

		return parent::get_form_item($name, $display, $type, $options, $default);
	}

	protected function get_default_item_type($attribute) {
		return $this->get_item_type($attribute['type']);
	}

	protected function get_default_item_options($attribute) {
		return PLX_Attributes::get_attribute_values($attribute['name']);
	}
}


class PLX_Parameter_Form extends PLX_Data_Form {
	public function get_form_item($name, $display = null, $type = null, $options = null, $default = null) {
		if(!($parameter = PLX_Parameters::get($name)))
			return null;

		if(is_null($display))
			$display = $parameter['display'];
		if(is_null($type))
			$type = $this->get_default_item_type($parameter);
		if(is_null($options))
			$options = $this->get_default_item_options($parameter);

		return parent::get_form_item($name, $display, $type, $options, $default);
	}

	protected function get_default_item_type($parameter) {
		return $this->get_item_type($parameter['type']);
	}

	protected function get_default_item_options($parameter) {
		return PLX_Attributes::get_attribute_values($parameter['attribute']);
	}
}


class PLX_Search_Form extends PLX_Parameter_Form {
	protected function get_default_item_type($parameter) {
		switch($parameter['type']) {
			case PLX_Attributes::LONG_TEXT:
				return PLX_Form::INPUT;
		}

		return parent::get_default_item_type($parameter);
	}
}