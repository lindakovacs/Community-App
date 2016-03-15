<?php
/**
 * The Agents widget gives users the ability to add agent information.
 *
 * @package PlacesterBlueprint
 * @subpackage Classes
 */

/**
 * Agent Widget Class
 *
 * @since 0.0.1
 */
register_widget( 'PLS_Widget_Agent' );
class PLS_Widget_Agent extends WP_Widget {

	/**
	 * Textdomain for the widget.
	 * @since 0.0.1
	 */
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 0.0.1
	 */
	public function __construct() {

		/* Set the widget textdomain. */
		$this->textdomain = pls_get_textdomain();

		/* Set up the widget options. */
		$widget_options = array(
			'classname' => 'pls-agent',
			'description' => 'A widget that displays information about the agent.'
		);

		/* Create the widget. */
		parent::__construct( "pls-agent", 'Placester: Agent Widget', $widget_options );
	}


	/**
	 * Outputs and filters the widget.
	 * @since 0.0.1
	 */
	public function widget( $args, $instance ) {

		list($args, $instance) = self::process_defaults($args, $instance);
		extract( $args, EXTR_SKIP );


		$agent_array = array();

		// Get the default agent information from theme options, if available
		if($name_option = pls_get_option('pls-user-name')) {
			$agent_array['name'] = $name_option;
			$agent_array['email'] = pls_get_option('pls-user-email');
			$agent_array['phone'] = pls_get_option('pls-user-phone');
		}

		// Otherwise, try the plugin's registered owner
		else if($whoami = PL_User::whoami()) {
			$agent_array['name'] = trim($whoami['user']['first_name'] . ' ' . $whoami['user']['last_name']);
			$agent_array['email'] = $whoami['user']['email'];
			$agent_array['phone'] = PLS_Format::phone($whoami['user']['phone']);
			$agent_array['photo'] = $whoami['user']['headshot'];
		}

		// Pull this info from options, regardless
		if(($description_option = pls_get_option('pls-user-description-widget')) || ($description_option = pls_get_option('pls-user-description')))
			$agent_array['description'] = $description_option;
		if($photo_option = pls_get_option('pls-user-image'))
			$agent_array['photo'] = $photo_option;


		// Handle widget overrides and flags
		if($instance['name_custom'])
			$agent_array['name'] = $instance['name_custom'];
		else if(!$instance['name'])
			unset($agent_array['name']);

		if($instance['email_custom'])
			$agent_array['email'] = $instance['email_custom'];
		else if(!$instance['email'])
			unset($agent_array['email']);

		if($instance['phone_custom'])
			$agent_array['phone'] = $instance['phone_custom'];
		else if(!$instance['phone'])
			unset($agent_array['phone']);

		if($instance['description_custom'])
			$agent_array['description'] = $instance['description_custom'];
		else if(!$instance['description'])
			unset($agent_array['description']);

		if($instance['image_uri'])
			$agent_array['photo'] = $instance['image_uri'];
		else if(!$instance['photo'])
			unset($agent_array['photo']);


		// Build the html output
		$agent_html = array();
		if($agent_array['name'])
			$agent_html['name'] = pls_h_p($agent_array['name'], array('class' => 'pls-agent-name', 'itemprop' => 'name'));
		if($agent_array['email'])
			$agent_html['email'] = pls_h_p(pls_h_a("mailto:{$agent_array['email']}", $agent_array['email']), array('class' => 'pls-agent-email', 'itemprop' => 'email'));
		if($agent_array['phone'])
			$agent_html['phone'] = pls_h_p($agent_array['phone'], array('class' => 'pls-agent-phone', 'itemprop' => 'phone'));
		if($agent_array['description'])
			$agent_html['description'] = pls_h_p($agent_array['description'], array('class' => 'pls-agent-description', 'itemprop' => 'description'));
		if($agent_array['photo'])
			$agent_html['photo'] = pls_h_img($agent_array['photo'], $agent_array['name'], array('class' => 'pls-agent-photo', 'itemprop' => 'image'));


		// Apply filters
		$widget_title = $instance['title'];
		if($widget_title)
			$widget_title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$widget_body = $agent_html['photo'] . $agent_html['name'] . $agent_html['email'] . $agent_html['phone'] . $agent_html['description'];
		if($widget_body)
			$widget_body = apply_filters('pls_widget_agent_inner', $widget_body, $agent_html, $agent_array, $instance);


		// Output
		echo $before_widget;
		echo apply_filters('pls_widget_agent', $before_title . $widget_title . $after_title . $widget_body, $instance, $this->id_base);
		if ($args['clearfix'])
			echo '<div class="clearfix"></div>';
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 0.0.1
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['name'] = ( isset( $new_instance['name'] ) ? 1 : 0 );
		$instance['name_custom'] = strip_tags( $new_instance['name_custom'] );
		$instance['email'] = ( isset( $new_instance['email'] ) ? 1 : 0 );
		$instance['email_custom'] = strip_tags( $new_instance['email_custom'] );
		$instance['phone'] = ( isset( $new_instance['phone'] ) ? 1 : 0 );
		$instance['phone_custom'] = strip_tags( $new_instance['phone_custom'] );
		$instance['description'] = ( isset( $new_instance['description'] ) ? 1 : 0 );
		$instance['description_custom'] = strip_tags( $new_instance['description_custom'] );
		$instance['photo'] = ( isset( $new_instance['photo'] ) ? 1 : 0 );
		$instance['image_uri'] = strip_tags( $new_instance['image_uri'] );

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 0.0.1
	 */
	public function form( $instance ) {

		/** Set up the default form values. */
		$defaults = array(
			'title' => 'Agent',
			'name' => true,
			'name_custom' => '',
			'email' => true,
			'email_custom' => '',
			'phone' => true,
			'phone_custom' => '',
			'description' => false,
			'description_custom' => '',
			'photo' => true,
			'image_uri' => ''
		);

		/** Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults );

		/** Print the backend widget form. */
		echo pls_h_div(
		/** Print the Title input */
			pls_h_p(
				pls_h_label(
					'Title' . ':' .
					pls_h(
						'input',
						array(
							'type' => 'text',
							'class' => 'widefat',
							'id' => $this->get_field_id( 'title' ),
							'name' => $this->get_field_name( 'title' ),
							'value' => esc_attr( $instance['title'] ),
							'style' => 'font-weight:normal'
						)
					),
					$this->get_field_id( 'title' ),
					array('style' => 'font-weight:bold')
				)
			) .
			// Name checkbox and override input
			pls_h_p(
				pls_h_label(
					pls_h_checkbox(
						checked( $instance['name'], true, false ),
						array(
							'id' => $this->get_field_id( 'name' ),
							'name' => $this->get_field_name( 'name' ),
						)
					) .
					' ' . 'Include Name',
					$this->get_field_id( 'name' )
				)
			) .
			pls_h_p(
				pls_h_label(
					'Override Name:' .
					pls_h(
						'input',
						array(
							'type' => 'text',
							'class' => 'widefat',
							'id' => $this->get_field_id( 'name_custom' ),
							'name' => $this->get_field_name( 'name_custom' ),
							'value' => esc_attr( $instance['name_custom'] ),
							'style' => 'font-weight:normal'
						)
					),
					$this->get_field_id( 'name_custom' ),
					array('style' => 'font-weight:bold')
				)
			) .
			// Email checkbox and override input
			pls_h_p(
				pls_h_label(
					pls_h_checkbox(
						checked( $instance['email'], true, false ),
						array(
							'id' => $this->get_field_id( 'email' ),
							'name' => $this->get_field_name( 'email' ),
						)
					) .
					' ' . 'Include Email',
					$this->get_field_id( 'email' )
				)
			) .
			pls_h_p(
				pls_h_label(
					'Override Email:' .
					pls_h(
						'input',
						array(
							'type' => 'text',
							'class' => 'widefat',
							'id' => $this->get_field_id( 'email_custom' ),
							'name' => $this->get_field_name( 'email_custom' ),
							'value' => esc_attr( $instance['email_custom'] ),
							'style' => 'font-weight:normal'
						)
					),
					$this->get_field_id( 'email_custom' ),
					array('style' => 'font-weight:bold')
				)
			) .
			// Phone checkbox and override input
			pls_h_p(
				pls_h_label(
					pls_h_checkbox(
						checked( $instance['phone'], true, false ),
						array(
							'id' => $this->get_field_id( 'phone' ),
							'name' => $this->get_field_name( 'phone' ),
						)
					) .
					' ' . 'Include Phone',
					$this->get_field_id( 'phone' )
				)
			) .
			pls_h_p(
				pls_h_label(
					'Override Phone:' .
					pls_h(
						'input',
						array(
							'type' => 'text',
							'class' => 'widefat',
							'id' => $this->get_field_id( 'phone_custom' ),
							'name' => $this->get_field_name( 'phone_custom' ),
							'value' => esc_attr( $instance['phone_custom'] ),
							'style' => 'font-weight:normal'
						)
					),
					$this->get_field_id( 'phone_custom' ),
					array('style' => 'font-weight:bold')
				)
			) .
			// Description checkbox and override input
			pls_h_p(
				pls_h_label(
					pls_h_checkbox(
						checked( $instance['description'], true, false ),
						array(
							'id' => $this->get_field_id( 'description' ),
							'name' => $this->get_field_name( 'description' ),
						)
					) .
					' ' . 'Include Description',
					$this->get_field_id( 'description' )
				)
			) .
			pls_h_p(
				pls_h_label(
					'Override Description:' .
					pls_h(
						'input',
						array(
							'type' => 'text',
							'class' => 'widefat',
							'id' => $this->get_field_id( 'description_custom' ),
							'name' => $this->get_field_name( 'description_custom' ),
							'value' => esc_attr( $instance['description_custom'] ),
							'style' => 'font-weight:normal;height:'
						)
					),
					$this->get_field_id( 'description_custom' ),
					array('style' => 'font-weight:bold')
				)
			) .


			/** Print the Photo checkbox */
			pls_h_p(
				pls_h_label(
					pls_h_checkbox(
						checked( $instance['photo'], true, false ),
						array(
							'id' => $this->get_field_id( 'photo' ),
							'name' => $this->get_field_name( 'photo' ),
						)
					) .
					' ' . 'Photo',
					$this->get_field_id( 'photo' )
				)
			) .
			pls_h_p(
				pls_h_label(
					'Image'
				// array(
				//   'for' => $this->get_field_id('image_uri'),
				//   'value' => esc_attr( $instance['image_uri'] ),
				//   'id' => "image_tag-". esc_attr( $this->get_field_id('image_uri') ),
				// )
				) .
				pls_h(
					'input',
					array(
						'name' => $this->get_field_name('image_uri'),
						'type' => 'text',
						'id' => "image-". esc_attr( $this->get_field_id('image_uri') ),
						'value' => $instance['image_uri'],
						'class' => '.agent-widget-img-text-input'
					)
				) .
				pls_h(
					'img',
					array(
						'id' => "image_tag-" . esc_attr( $this->get_field_id("image_uri") ),
						'src' => $instance['image_uri'],
						'class' => '.agent-widget-img-tag'
					)
				) .
				pls_h(
					'input',
					array(
						'type' => 'button',
						'id' => 'select-img-' . $this->get_field_id("image_uri"),
						'value' => 'Select Image',
						'class' => 'agent-widget-img-button'
					)
				)
			)

		);

	}


	private static function process_defaults ($args, $instance) {

		/** Define the default argument array. */
		$arg_defaults = array(
			'title' => 'Have any questions?',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
			'before_widget' => '<section id="pls-agent-3" class="widget pls-agent widget-pls-agent" itemscope="" itemtype="http://schema.org/RealEstateAgent">',
			'after_widget' => '</section>',
			'widget_id' => '',
			'clearfix' => true
		);


		/** Merge the arguments with the defaults. */
		$args = wp_parse_args( $args, $arg_defaults );

		/** Define the default argument array. */
		$instance_defaults = array(
			'name' => true,
			'name_custom' => false,
			'email' => true,
			'email_custom' => false,
			'phone' => true,
			'phone_custom' => false,
			'description' => true,
			'description_custom' => false,
			'photo' => true,
			'image_uri' => false,
			'widget_id' => ''
		);

		/** Merge the arguments with the defaults. */
		$instance = wp_parse_args( $instance, $instance_defaults );

		return array($args, $instance);
	}
}


// queue up the necessary js
add_action('admin_enqueue_scripts', 'upload_js_enqueue');
function upload_js_enqueue() {
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media-upload');
}

add_action('admin_footer-widgets.php', 'agent_image_js', false, true);
function agent_image_js () {
	ob_start();
	?>
	<script>
		var image_field;
		var input_field;
		jQuery(function($){
			$(document).on('click', 'input.agent-widget-img-button', function(evt){
				var image_id = $(this).attr('id');

				var partial_image_id = image_id.substr(image_id.indexOf('widget-pls-agent'));
				input_field = $(this).siblings('#image-' + partial_image_id);
				image_field = $(this).siblings('#image_tag-' + partial_image_id);
				tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
				return false;
			});
			window.send_to_editor = function(html) {
				imgurl = $('img', html).attr('src');
				image_field.attr('src',imgurl);
				input_field.val(imgurl);
				tb_remove();
			}
		});
	</script>
	<?php
	echo ob_get_clean();
}
