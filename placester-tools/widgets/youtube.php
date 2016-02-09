<?php

class PLS_Widget_YouTube extends WP_Widget {

	/**
	 * Widget Constuctor
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'pls_youtube_widget', 'description' => 'YouTube Widget' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 350 );

		/* Create the widget. */
		$this->WP_Widget( 'PLS_Widget_YouTube', 'Placester: YouTube Widget', $widget_ops, $control_ops );
	}

	/**
	 * Widget Output
	 *
	 * @param $args (array)
	 * @param $instance (array) Widget values.
	 */
	public function widget( $args, $instance ) {
		// widget output
		extract($args);

		$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
		$yt_id = empty( $instance['yt_id'] ) ? ' ' : apply_filters( 'yt_id', $instance['yt_id'] );
		$desc = empty( $instance['desc'] ) ? ' ' : apply_filters( 'desc', $instance['desc'] );
		$rm_text = empty( $instance['rm_text'] ) ? ' ' : apply_filters( 'rm_text', $instance['rm_text'] );
		$rm_url = empty( $instance['rm_url'] ) ? ' ' : apply_filters( 'rm_url', $instance['rm_url'] );

		echo $before_widget;

			// Display widget title
			if( $instance['title'] ) {
				echo $before_title . $title . $after_title;
			}

			?>
				<div class="video-widget-inner">
					<iframe width="290" height="163" src="//www.youtube.com/embed/<?php echo $yt_id; ?>" frameborder="0" allowfullscreen></iframe>
					<p><?php echo $desc; ?></p>
					<a href="<?php echo $rm_url; ?>"><?php echo $rm_text; ?></a>
				</div>
			<?php

		echo $after_widget;
	}

	/**
	 * Update Widget
	 *
	 * @param $new_instance (array) New widget values.
	 * @param $old_instance (array) Old widget values.
	 *
	 * @return (array) New values.
	 */
	public function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = $old_instance;

		//Strip tags from title to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['yt_id'] = strip_tags( $new_instance['yt_id'] );
		$instance['desc'] = strip_tags( $new_instance['desc'] );
		$instance['rm_text'] = strip_tags( $new_instance['rm_text'] );
		$instance['rm_url'] = strip_tags( $new_instance['rm_url'] );

		return $instance;
	}

	/**
	 * Widget Options Form
	 *
	 * @param $instance (array) Widget values.
	 */
	public function form( $instance ) {

		// Defaults
		$instance = wp_parse_args( (array) $instance, array( 
			'title' => '',
			'yt_id'=> '',
			'desc'=> '',
			'rm_text'=> '',
			'rm_url'=> ''
		) );

		// Values
		$title = esc_attr( $instance['title'] );
		$yt_id = esc_attr( $instance['yt_id'] );
		$desc = esc_attr( $instance['desc'] );
		$rm_text = esc_attr( $instance['rm_text'] );
		$rm_url = esc_attr( $instance['rm_url'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('yt_id'); ?>">YouTube ID:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'yt_id' ); ?>" name="<?php echo $this->get_field_name( 'yt_id' ); ?>" type="text" value="<?php echo $yt_id; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('desc'); ?>">Description:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" type="text" value="<?php echo $desc; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('rm_text'); ?>">Read More Link Text:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'rm_text' ); ?>" name="<?php echo $this->get_field_name( 'rm_text' ); ?>" type="text" value="<?php echo $rm_text; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('rm_url'); ?>">Read more Link URL:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'rm_url' ); ?>" name="<?php echo $this->get_field_name( 'rm_url' ); ?>" type="text" value="<?php echo $rm_url; ?>" />
		</p>

		<?php
	}
}