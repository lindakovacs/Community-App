<?php


class PLX_Form {
	const INPUT = 1;
	const RADIO = 5;
	const CHECKBOX = 6;
	const SELECT = 11;
	const MULTISELECT = 12;
	const RADIO_GROUP = 15;
	const CHECKBOX_GROUP = 16;
	const TEXTAREA = 21;

	public function get_form_item($name, $display, $type, $options, $value = null) {
		if(is_scalar($options)) { // $options is required to be scalar for a radio or checkbox -- it is used as the HTML "value" attribute
			$option = $options;
			$options = array($option => $option);
		}
		else if(is_array($options)) {
			$option = 'Must be scalar';
			if(!empty($options) && array_keys($options) !== range(0, count($options) - 1))
				$options = array_combine($options, $options);
		}
		else {
			$option = 'true';
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
				<input id="<?php echo "pl-form-$name"; ?>" class="pl-form-input" type="text" name="<?php echo $name; ?>"
					value="<?php echo self::esc($value); ?>"<?php if($options) echo "list=\"pl-form-$name-list\""; ?>>
				<?php if($options) { ?>
				<datalist id="<?php echo "pl-form-$name-list"; ?>">
					<?php foreach($options as $option) { ?><option value="<?php echo $option; ?>"><?php } ?>
				</datalist>
				<?php } ?>
			</div>
		<?php
		}
		elseif ($type == self::RADIO) {
			$id_name = "$name-" . $this->idify($option);
		?>
			<div id="<?php echo "pl-form-item-$id_name"; ?>" class="pl-form-item pl-form-radio-item">
				<input id="<?php echo "pl-form-$id_name"; ?>" class="pl-form-radio" type="radio" name="<?php echo $name; ?>"
					value="<?php echo self::esc($option); ?>"<?php if($value === true || $value === $option) echo ' checked="checked"'; ?>>
				<label for="<?php echo "pl-form-$id_name"; ?>" class="pl-form-item-label pl-form-radio-label"><?php echo self::esc($display); ?></label>
			</div>
		<?php
		}
		elseif ($type == self::CHECKBOX) {
			$id_name = "$name-" . $this->idify($option);
		?>
			<div id="<?php echo "pl-form-item-$id_name"; ?>" class="pl-form-item pl-form-checkbox-item">
				<input id="<?php echo "pl-form-$id_name"; ?>" class="pl-form-checkbox" type="checkbox" name="<?php echo $name; ?>"
					value="<?php echo self::esc($option); ?>"<?php if($value === true || $value === $option) echo ' checked="checked"'; ?>>
				<label for="<?php echo "pl-form-$id_name"; ?>" class="pl-form-item-label pl-form-checkbox-label"><?php echo self::esc($display); ?></label>
			</div>
		<?php	
		}
		elseif ($type == self::SELECT) {
		?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-select-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-select-label"><?php echo $display; ?></label>
				<select id="<?php echo "pl-form-$name"; ?>" class="pl-form-select" name="<?php echo $name; ?>">
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
				<select id="<?php echo "pl-form-$name"; ?>" class="pl-form-multiselect" name="<?php echo $name; ?>" multiple="multiple">
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
					echo self::get_form_item($name, $option_display, self::CHECKBOX, $option_name, in_array($option_name, $values) ? $option_name : null);
				} ?>
			</fieldset>
		<?php
		}
		elseif ($type == self::TEXTAREA) {
		?>
			<div id="<?php echo "pl-form-item-$name"; ?>" class="pl-form-item pl-form-textarea-item">
				<label for="<?php echo "pl-form-$name"; ?>" class="pl-form-item-label pl-form-textarea-label"><?php echo self::esc($display); ?></label>
				<textarea id="<?php echo "pl-form-$name"; ?>" class="pl-form-textarea" name="<?php echo $name; ?>">
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

class PLX_Attribute_Form extends PLX_Form {
	protected $form_data;

	public function set_form_data($data) {
		$this->form_data = $data;
	}

	public function get_form_item($name, $display = null, $type = null, $options = null, $default = null) {
		if(is_null($display) && $attribute = PLX_Attributes::get($name))
			$display = $attribute['display'];

		if(is_null($type) && (isset($attribute) || $attribute = PLX_Attributes::get($name)))
			$type = $this->get_default_item_type($attribute);

		if(is_null($options) && (isset($attribute) || $attribute = PLX_Attributes::get($name)))
			$options = $this->get_default_item_options($attribute);

		if(!isset($attribute)) $attribute = PLX_Attributes::get($name);
		$value = $this->get_item_value($attribute, $default);

		return parent::get_form_item($name, $display, $type, $options, $value);
	}

	protected function get_item_value($attribute, $default = null) {
		if(!isset($this->form_data))
			$this->form_data = &$_POST;

		return isset($this->form_data[$attribute['name']]) ?
			($this->form_data[$attribute['name']]) :
			($this->form_data ? null : $default);
	}

	static protected function get_default_item_type($attribute) {
		switch($attribute['type']) {
			case PLX_Attributes::BOOLEAN:
				return PLX_Form::CHECKBOX;

			case PLX_Attributes::NUMERIC:
			case PLX_Attributes::CURRENCY:
				return PLX_Form::INPUT;

			case PLX_Attributes::DATE_TIME:
				return null; // no implementation for these

			case PLX_Attributes::TEXT_ID:
				return PLX_Form::INPUT;

			case PLX_Attributes::TEXT_VALUE:
				$values = PLX_Attribute_Values::get($attribute['name']);
				if($values['fixed'])
					return PLX_Form::SELECT;
				else
					return PLX_Form::INPUT;

			case PLX_Attributes::SHORT_TEXT:
				return PLX_Form::INPUT;

			case PLX_Attributes::LONG_TEXT:
				return PLX_Form::TEXTAREA;
		}

		return null;
	}

	protected function get_default_item_options($attribute) {
		return PLX_Attribute_Values::get_values($attribute['name']);
	}
}

class PLX_Search_Form extends PLX_Attribute_Form {}
