<?php 

class PL_Form {
	
	private static $args = array();
	private static $textarea_as_text = false;
	
	public static function generate_form ($items, $args, $section_prefix = '') {
		extract(self::process_defaults($args), EXTR_SKIP);
		self::$textarea_as_text = $textarea_as_text;

		$cache = new PL_Cache('form');
		if ($result = $cache->get($items, $args)) {
			if ($echo_form) {
				echo $result;
				$result = false;
			}

			return $result;
		}
		
		$form = '';
		$form_group = array();
		foreach ($items as $key => $attributes) {
			if ( isset($attributes['type']) && isset($attributes['group']) ) {
				$form_group[$attributes['group']][] = self::item($key, $attributes, $method, $parent, $section_prefix);
			}
			elseif ( !isset($attributes['type']) && is_array($attributes) ) {
				if ($parent) {
					$key = $parent.'['.$key.']';
				}

				foreach ($attributes as $child_item => $attribute) {
					if ( isset($attribute['group']) ) {
						if($attribute['attr_type'] == 'int' || $attribute['attr_type'] == 'date')
						{
							$temp_attribute = $attribute;
							$temp_attribute['label'] = $attribute['label_min'] ?: 'Min ' . $attribute['label'];
							$form_group[$attribute['group']][] = self::item($child_item, $temp_attribute, $method, 'min_' . $key, $section_prefix);

							$temp_attribute = $attribute;
							$temp_attribute['label'] = $attribute['label_max'] ?: 'Max ' . $attribute['label'];
							$form_group[$attribute['group']][] = self::item($child_item, $temp_attribute, $method, 'max_' . $key, $section_prefix);
						}
						else
							$form_group[$attribute['group']][] = self::item($child_item, $attribute, $method, $key, $section_prefix);
					}
				}
			}
		}

		$section_index = 1;
		$section_count = count($form_group);
		
		foreach ($form_group as $group => $elements) {
			$section_id = empty($group) ? 'custom' : $group;
			$form = apply_filters('pl_form_section_before', $form, $section_index, $section_count);
			$gid = str_replace(" ","_",$section_id);
			$form .= '<section class="form_group form_group_' . $gid . '" id="' . $gid . '">';
			
			if (!empty($group)) {
				$form .= $title ? "<h3>" . ucwords($group) . "</h3>" : '';
			}

			$form .= implode($elements, '');
			$form .= "</section>";
			$form = apply_filters('pl_form_section_after', $form, $section_index, $section_count);
		}

		$form .= '<section class="clear"></section>';
		
		if ($include_submit) {
			$form .= '<button id="' . $id . '_submit_button" type="submit">Submit</button>';
		}
		
		if ($wrap_form) {
			$form = '<form name="input" method="' . $method . '" url="' . $url . '" class="complex-search" id="' . $id . '">' . $form . '</form>';
		}
		
		$cache->save($form);
		
		if ($echo_form) {
			echo $form;
			$form = '';
		} 
		
		return $form;
	}

	public static function item ($item, $attributes, $method, $parent = false, $section_prefix = '', $echo = false) {
		// Prepare args then extract them...
		extract(self::prepare_item($item, $attributes, $method, $parent), EXTR_SKIP);
		
		if ($type == 'textarea' && self::$textarea_as_text) {
			$type = 'text';
		}
		
		ob_start();

		if ($type == 'checkbox') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>">
					<input id="<?php echo $id ?>" type="<?php echo $type ?>" name="<?php echo $name ?>" value="true" <?php echo $value ? 'checked' : '' ?> />
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
				</section>
			<?php	
		} 
		elseif ($type == 'textarea') {
			$rows = ! empty( $attributes['rows'] ) ? $attributes['rows'] : 2;
			$cols = ! empty( $attributes['cols'] ) ? $attributes['cols'] : 20;
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>">
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
					<textarea id="<?php echo $id ?>" name="<?php echo $name ?>" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>"><?php echo $value ?></textarea>
				</section>
			<?php
		} 
		elseif ($type == 'select') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>" >
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
					<select name="<?php echo $name ?>" id="<?php echo $id ?>" data-multi="<?php echo $multi ?>" >
						<?php foreach ($options as $key => $text): ?>
							<option value="<?php echo htmlentities($key, ENT_QUOTES) ?>" <?php echo ($key === $value || (gettype($key)=='integer' && (string)$key === $value) ? 'selected="selected"' : '' ) ?>><?php echo htmlentities($text, ENT_QUOTES, 'UTF-8', false) ?></option>
						<?php endforeach ?>
					</select>
				</section>
			<?php	
		} 
		elseif ($type == 'multiselect') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>" >
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
					<select name="<?php echo $name ?>[]" id="<?php echo $id ?>" multiple="multiple" data-multi="<?php echo $multi ?>" >
						<?php foreach ($options as $key => $text): ?>
							<option value="<?php echo htmlentities($key, ENT_QUOTES) ?>" <?php echo ((is_array($value) && in_array($key, $value) ) ? 'selected="selected"' : '' ) ?>><?php echo htmlentities($text, ENT_QUOTES, 'UTF-8', false) ?></option>
						<?php endforeach ?>
					</select>
				</section>
			<?php	
		} 
		elseif ($type == 'text' || $type == 'int') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>">
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
					<input id="<?php echo $id ?>" class="form_item_<?php echo $type ?>" type="text" name="<?php echo $name ?>" value="<?php echo htmlentities($value) ?>" data-attr_type="<?php echo $type ?>" />
				</section>
			<?php
		} 
		elseif ($type == 'date') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>">
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
					<input id="<?php echo $id ?>_picker" class="form_item_date" type="text" name="<?php echo $name ?>" <?php echo !empty($value) ? 'value="'.$value.'"' : ''; ?> />
				</section>
			<?php
		} 
		elseif ($type == 'image') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>">
					<label for="<?php echo $id ?>" class="<?php echo $type ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>	
					<input id="fileupload" type="file" name="images" name="<?php echo $name ?>" multiple /> 
				</section>
			<?php
		} 
		elseif ($type == 'bundle') {
			$parent = empty($parent) ? $item : $parent . '['.$item.']';
			$bundle = '';
			foreach (self::prepare_custom_item($options, $method, $parent) as $key => $form_items) {
				$bundle .= '<section class="form_subgroup" id="'.$id.'-'.preg_replace('/[^a-z]/i', '_', $key).'">';
				if (self::$args['title']) {
					$bundle .= "<h3>" . ucwords($key) . "</h3>";
				}
				if (is_array($form_items)) {
					$bundle .= implode($form_items, '');	
				} else {
					$bundle .= $form_items;	
				}
				$bundle .= "</section>";
			}
			echo $bundle;
		} 
		elseif ($type == 'radio') {
			?>
				<section id="<?php echo $section_prefix . $id ?>" class="pls_search_form <?php echo $css ?>" >
					<label for="<?php echo $id ?>"><?php echo $text ?><?php if (!empty($description)) : ?><span class="description"><?php echo htmlentities($description);?></span><?php endif;?></label>
					<?php foreach( $options as $key => $text ): ?>
					<div class="<?php echo $name . '_radios'; ?>">
						<label for="<?php echo $name . '_' . $key; ?>" class="<?php echo $type ?>"><?php echo $text ?></label>
						<input id="<?php echo $name . '_' . $key; ?>" type="radio" value="<?php echo $text; ?>" name="<?php echo $name; ?>" />
					</div>
					<?php endforeach; ?>	
				</section>
			<?php	
		} 
		elseif ($type == 'custom_data') {
			?>
				<section id="<?php echo $id ?>" class="pls_search_form <?php echo $css ?>">
					<label for="">Category Name</label>
					<input type="text" name="custom_attribs[][cat]" />
					<label for="">Label Name</label>
					<input type="text" name="custom_attribs[][type]" />
					<label for="">Information Type</label>
					<input type="text" name="custom_attribs[][name]" />
					<button id="<?php echo $id ?>">Add another</button>
				</section>
			<?php
		}

		$op = trim(ob_get_clean());

		if ($echo) {
			echo $op;
			$op = '';
		}
		
		return $op;
	}

	private static function prepare_item ($item, $attributes, $method, $parent) {
		// Sets text
		$text = $item;
		if (isset($attributes['label'])) { $text = $attributes['label']; }

		// Generates CSS, want it about the name to avoid the explode
		$css = $item;
		if (isset($attributes['css'])) { $css = $attributes['css']; }

		// Properly set the name if an array to handle property type bullshit
		if (strpos($item, '.')) {
			$exploded = explode('-', $item);
			$item = $exploded[0];
		}

		$name = $item;
		$id = $item;

		if ($parent) {
			$name = $parent . '[' . $item . ']';
			$id = str_replace(array('[',']'), array('-',''), $parent) . '-' . $item; // brackets are not valid in the id
		}
		
		// Support description text
		$description = '';
		if (!empty($attributes['description'])) { $description = $attributes['description']; }

		// Get options, if there are any
		if (isset($attributes['bound']) && is_array($attributes['bound'])) {
			// Deal with params...
			$params = ( isset($attributes['bound']['params']) ? $attributes['bound']['params'] : array() ) ;
			// If "params" is a single element, encapsulate in an array...
			if (isset($params) && !is_array($params)) {
				$params = array($params);
			}

			$options = call_user_func_array(array($attributes['bound']['class'], $attributes['bound']['method']), $params);
		} 
		elseif (isset($attributes['options'])) {
			$options = $attributes['options'];
		} 
		else {
			$options = array();
		}

		if ($method == 'GET') {
			$_data = &$_GET;
		} 
		else {
			$_data = &$_POST;
		}

		if ($parent) {
			$chain = explode('[',$parent);
			$value = ''; 
			array_push($chain, $item);
			$i = count($chain);

			foreach($chain as $link) {
				$link = trim($link, ' ]');

				if (!isset($_data[$link])) { break; }
				
				$_data = &$_data[$link];
				$i--;
				
				if (!$i) { $value = $_data; }
			}
		} 
		else {
			$value = isset($_data[$item]) ? $_data[$item] : null;	
		}

		if (!$value && isset($attributes['bound']) && isset($attributes['bound']['default']) ) {
			if (is_array($attributes['bound']['default'])) {
				$value = call_user_func($attributes['bound']['default']);
			} 
			else {
				$value = $attributes['bound']['default'];	
			}
		} 
		
		// Extra check for blank arrays
		$value = ( is_array($value) && empty($value) ) ? null : $value;

		$multi = isset($attributes['multi']) ? $attributes['multi'] : "";

		return array(
			'name' => $name, 
			'value' => $value, 
			'text' => $text, 
			'options' => $options, 
			'id' => $id, 
			'type' => $attributes['type'], 
			'css' => $css, 
			'description' => $description, 
			'multi' => $multi
		);
	}

	public static function prepare_custom_item ($options, $method, $parent) {
		$custom_items = array();
		$form_types = PL_Config::PL_API_CUST_ATTR('get');
		$form_types = $form_types['args']['attr_type']['options'];

		foreach ($options as $key => $option) {
			$attributes = array('label' => $option['name'], 'type' => $form_types[$option['attr_type']]);
			$custom_items[$option['cat']][] = self::item($option['key'], $attributes, $method, $parent);
		}
		
		return $custom_items;
	}

	private static function process_defaults ($args){
		/** Define the default argument array. */
		$defaults = array(
			'url' => false,
			'method' => 'GET',
			'parent' => false,
			'id' => 'pls_search_form',
			'title' => false,
			'include_submit' => true,
			'wrap_form' => true,
			'echo_form' => true,
			'textarea_as_text' => false
		);

		/** Merge the arguments with the defaults. */
		$args = wp_parse_args( $args, $defaults );
		self::$args = $args;

		return $args;
	}

// class end
}