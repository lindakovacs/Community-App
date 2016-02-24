<?php 

PL_Global_Filters::init();

class PL_Global_Filters {
	
	public static function init () {
		add_action('wp_ajax_user_save_global_filters', array(__CLASS__, 'set_global_filters'));
		add_action('wp_ajax_user_remove_all_global_filters', array(__CLASS__, 'remove_all_global_filters'));
		add_action('wp_ajax_filter_options', array(__CLASS__, 'filter_options'));
	}

	/*
	 * Parses and merged incoming args with the current global filters
	 */
	public static function merge_global_filters ($args = array()) {
		// Comes back as an associative array -- false if empty.
		$global_filters = self::get_global_filters();

		// No point in proceeding if $global_filters is not an array...
		if (!is_array($global_filters)) { return $args; }

		// This whole thing basically traverses down the arrays for global filters...
		foreach ($global_filters as $attribute => $value) {
			if ($attribute == 'custom') {
				$attribute = 'metadata';
			}
			if (is_array($value)) {
				// Used to determine whether or not $value is array of values representing filter type $attribute, or a subfilter
				// (i.e, location => array("state" => "AZ", "postal" => "85215") -- location contains subfilters, not values)
				$key_is_list = count(array_filter(array_keys($value), 'is_string')) == 0;

				if ($key_is_list) {
					// Respect existing value if it is already set...
					if (empty($args[$attribute])) {
						foreach ($value as $k => $v) {
							$args[$attribute][] = is_string($v) ? self::translate_string($v) : $v;
						}
						if (count($value) && count($value) > 0) {
							$args["{$attribute}_match"] = "in";
						}
					}
				}
				else {
					// subkeys present
					foreach ($value as $k => $v) {
						// Respect existing value if it is already set...
						if (empty($args[$attribute][$k])) {
							$args[$attribute][$k] = is_string($v) ? self::translate_string($v) : $v;
							if (is_array($v) && count($v) > 0) {
								$args[$attribute]["{$k}_match"] = "in";
							}
						}
					}
				}
			}
			// Respect existing value if it is already set...
			elseif (empty($args[$attribute])) {
				$args[$attribute] = is_string($value) ? self::translate_string($value) : $value;
			}
		}

		return $args;
	}

	/* Updates strings that represent boolean values to the correct format for API calls */
	private static function translate_string ($value) {
		$val = $value;

		switch ($value) {
			case "true": 
				$val = 1;
				break;
			case "false": 
				$val = 0;
				break;
		}
		
		return $val;
	}

	private static function render_active_filter ($name, $value) {
		ob_start();
		?>
			<span id="active_filter_item">
				<a href="#"  class="remove_filter"></a>
				<span class="global_dark_label"><?php echo str_replace("_", " ", $name); ?></span> : <?php echo str_replace("_", " ", $value); ?>
				<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">	
			</span>
		<?php
		return ob_get_clean();
	}

	public static function display_global_filters () {
		$filters = self::get_global_filters();
		if (empty($filters))
			return;

		$html = '';
		foreach ($filters as $key => $filter) {
			if (is_array($filter)) {
				reset($filter);

				if(key($filter) === 0) {
					foreach ($filter as $v) {
						$html .= self::render_active_filter($key, $v);
					}
				}
				else {
					foreach ($filter as $subkey => $item) {
						if (is_array($item)) {
							foreach ($item as $v) {
								$html .= self::render_active_filter("{$key}[{$subkey}]", $v);
							}
						}
						else {
							$html .= self::render_active_filter("{$key}[{$subkey}]", $item);
						}
					}
				}
			}
			else {
				$html .= self::render_active_filter($key, $filter);
			}
		}

		echo $html;
	}

	public static function filter_options () {
		$option_name = 'pl_my_listings_filters';
		$options = get_option($option_name);
		if (isset($_POST['filter']) && isset($_POST['value']) && $options) {
			$options[$_POST['filter']] = $_POST['value'];
			update_option($option_name, $options);
		} 
		elseif (isset($_POST['filter']) && isset($_POST['value']) && !$options) {
			$options = array($_POST['filter'] => $_POST['value']);
			add_option($option_name, $options);
		}
		echo json_encode($options);
		die();
	}

	public static function get_listing_attributes () {
		$options = array();
		$attributes = PL_Config::PL_API_LISTINGS('get', 'args');

		$form_types = PL_Config::PL_API_CUST_ATTR('get');
		$form_types = $form_types['args']['attr_type']['options'];

		if (isset($attributes['custom']) && is_array($attributes['custom'])) {
			$custom_attributes = call_user_func( array($attributes['custom']['bound']['class'], $attributes['custom']['bound']['method'] ) );
							
			foreach ($custom_attributes as $key => $option) {
				$attributes[$option['cat']][$option['key']] = array('label' => $option['name'], 'type' => $form_types[$option['attr_type']] );
			} 

			unset($attributes['custom']);
		}
		
		foreach ($attributes as $key => $attribute) {
			if ( isset($attribute['label']) ) {
				$options['basic'][$key] = $attribute['label'];
			} 
			else {
				foreach ($attribute as $k => $v) {
					if (isset($v['label'])) {
						if (is_int($k)) {
							$options[$key][self::generate_global_filter_key_from_value($v['label'])] = $v['label'];
						} 
						else {
							$options[$key][$k] = $v['label'];
						}
						
					}
				}
			}
		}
		
		$option_html = '';
		foreach ($options as $group => $value) {
			ob_start();
			?>
				<optgroup label="<?php echo ucwords($group) ?>">
					<?php foreach ($value as $value => $label): ?>
						<option value="<?php echo $value ?>"><?php echo $label ?></option>
					<?php endforeach ?>
				</optgroup>
			<?php
			$option_html .= ob_get_clean();
		}

		$option_html = '<select id="selected_global_filter">' . $option_html . '</select>';
		echo $option_html;
	}

	/*
	 * Functionality for Global Filters
	 */

	public static function remove_all_global_filters () {
		$filters_deleted = PL_Option_Helper::set_global_filters(array('filters' => array()));
		$response = $filters_deleted
					? array('result' => true, 'message' => 'You successfully removed all global search filters')
					: array('result' => false, 'message' => 'Change not saved or no change detected. Please try again.');
		
		// Send response...
		echo json_encode($response);
		
		die();
	}

	public static function get_global_filters () {
		$response = PL_Option_Helper::get_global_filters();
		return $response;
	}

	public static function set_global_filters ($args = array()) {
		// error_log(var_export($_POST, true));
		if (empty($args) ) {
			unset($_POST['action']);
			$args = $_POST;
		}
		
		// Validate...
		$global_search_filters = PL_Validate::request($args, PL_Config::PL_API_LISTINGS('get', 'args'));

		// Try to save the new batch global filters...
		$filters_saved = PL_Option_Helper::set_global_filters(array('filters' => $global_search_filters));
		$response = $filters_saved // i.e., if filters were successfully saved...
					? array('result' => true, 'message' => 'You successfully updated the global search filters')
					: array('result' => false, 'message' => 'Change not saved or no change detected -- Please try again.');
		
		// Send response...
		echo json_encode($response);
		die();
	}

	private static function generate_global_filter_key_from_value ($value) {
		$value = str_replace(' ', '_', $value);
		$value = str_replace('.', '', $value);
		$value = str_replace('-', '', $value);
		$value = strtolower($value);
		return $value;
	}
}
?>