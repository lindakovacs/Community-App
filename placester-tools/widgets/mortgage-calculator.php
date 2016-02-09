<?php

class PLS_Widget_Mortgage_Calculator extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 
			'classname' => 'pls-mortgage-calculator-widget',
			'description' => ''
		);

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 290 );

		/* Create the widget. */
		$this->WP_Widget( 'PLS_Widget_Mortgage_Calculator', 'Placester: Mortgage Calculator', $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {
		// Widget output
		
		add_action('wp_footer', array($this, 'footer_scripts'), 99);

		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$home_price = empty($instance['home_price']) ? ' ' : apply_filters('home_price', $instance['home_price']);
		
		/** Define the default argument array. */
		$defaults = array(
			'before_widget' => '<section id="pls_mortgage_calc" class="pls_mortgage_calc_wrapper widget">',
			'after_widget' => '</section>',
			'title' => '',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		);

		/** Merge the arguments with the defaults. */
		$args = wp_parse_args( $args, $defaults );

		extract($args, EXTR_SKIP);
		
		echo $before_widget; ?>

			<div class="widget-inner pls_mortgage_calc_widget_inner">

				<?php echo $before_title . $title . $after_title; ?>
		
				<form method="post" action="" id="pls_mortgage_calc_form">

					<div class="calc_input_wrapper">
						<label>Price of Home ($)</label>
						<input type="text" name="price" id="pls_Price" class="mortgageField" tabindex=15 value="<?php echo $home_price; ?>" />
					</div>
					<div class="calc_input_wrapper">
						<label>Down Payment (%)</label>
						<input type="text" name="down" id="pls_Down" class="mortgageField" tabindex=16 value="20" />
					</div>
					<div class="calc_input_wrapper">
						<label>Interest Rate (%)</label>
						<input type="text" name="rate" id="pls_Rate" class="mortgageField" tabindex=17 value="5" />
					</div>
					<div class="calc_input_wrapper">
						<label>Loan Term (years)</label>
						<input type="text" name="term" id="pls_Term" class="mortgageField" tabindex=18 value="30" />
					</div>

					<input type="submit" id="mCalc" onclick="return false" tabindex=19 class="button-primary" value="Calculate">
			
					<div id="pls_Payment">
						<span>$</span><input type="text" name="payment" id="calc-submit-total" />
					</div>
		
				</form>

			</div>

		<?php echo $after_widget; ?>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['home_price'] = strip_tags($new_instance['home_price']);
		
		return $instance;
	}

	public function form( $instance ) {
		// Output admin widget options form
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Mortgage Calculator', 'home_price' => '250,000' ) );
		$title = strip_tags($instance['title']);
		$home_price = strip_tags($instance['home_price']);
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title' ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('home_price'); ?>"><?php echo "Calculator's starting home price" ?>: <input class="widefat" id="<?php echo $this->get_field_id('home_price'); ?>" name="<?php echo $this->get_field_name('home_price'); ?>" type="text" value="<?php echo esc_attr($home_price); ?>" /></label></p>
		<?php
	}

	/**
	 * Print js in footer 
	 */
	public function footer_scripts() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
			
				$("#mCalc").live("click", function(){ 
					var data = {};
					var M,P,n,r,dp;
					
					$.each($(this).closest('form').serializeArray(), function(i, field) {
							 data[field.name] = field.value;
					});
					// remove commas from price
					data.price = data.price.replace(/,/g, "");
					M = parseInt(data.price) ? parseInt(data.price) : function () { alert('You must enter a number for the price (rather then a word)'); return false; }()
					n = parseInt( data.term ) * 12;
					r = parseFloat( data.rate ) / 1200;
					dp = 1 - parseFloat( data.down ) / 100;
					M = M * dp;
					
					P = ( M*( r*Math.pow(1+r,n) ) ) / ( Math.pow( 1+r,n )-1 ); 
					
					if(!isNaN(P)) {
						$("#pls_Payment input, #pls_payment_price span").val(P.toFixed(2));
					} else {
						alert('There was an error, please check your values.');
					}
					return false;
				});
				
			});
		</script>
		<?php
	}

}