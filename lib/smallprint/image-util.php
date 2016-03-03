<?php 

/*
Based heavily on Wes Edling's chaching/scaling script, modified to work properly in our context.
Modifications include:
	 - fixing the way urls are handled to remove get vars in the image name
	 - rewrote to use GD for image manipulation rather then ImageMagic

TODO: 
	- break this out into reusable functions so the logic is more obvious
	- performance testing / optimization.

Here's Wes' requested attribution for the modified "resize" function:

function by Wes Edling .. http://joedesigns.com
feel free to use this in any project, i just ask for a credit in the source code.
a link back to my site would be nice too.

** Wes' resizing was removed because WP Theme Submission didn't allow file_get_contents();

*/

// PLS_Image::init();
class PLS_Image {

	public static function init() {

		if (!is_admin()) {
			add_action('init', array(__CLASS__,'enqueue'));
		}
	}

    public static function enqueue() {
		wp_enqueue_script('jquery-fancybox');
		wp_enqueue_script('jquery-fancybox-settings');
		wp_enqueue_style('jquery-fancybox');
	}

	public static function load ($old_image = '', $args = null) {
		$new_image = false;

		if (isset($args['fancybox']) && $args['fancybox']) {
			unset($args['fancybox']);
		}

	    $args = self::process_defaults($args);
	    // doesn't really disable dragonfly, but disables cropping -- for mls compliance
	    $disable_dragonfly = pls_get_option('pls-disable-dragonfly');
	    
	    // use standard default image
		if ( $old_image === '' || empty($old_image)) {
			if ( !empty($args['null_image']) ) {
				$old_image = $args['null_image'];
			} 
			else {
				$old_image = PLS_IMG_URL . "/null/listing-1200x720.jpg";
			}
		} 
		elseif ( $args['allow_resize'] && $args['resize']['w'] && $args['resize']['h'] && get_theme_support('pls-dragonfly')) {
			
			$img_args = array(
				'resize' => $args['resize'],
				'nocrop' => $disable_dragonfly,
				'old_image' => $old_image
			);

			$new_image = PLS_Plugin_API::resize_image($img_args);
		}

		if ( $args['fancybox'] || $args['as_html']) {
			if ($new_image) {
				$new_image = self::as_html($old_image, $new_image, $args);
			} else {
				$new_image = self::as_html($old_image, null, $args);
			}
		}

		// return the new image if we've managed to create one
		if ($new_image) {
			return $new_image;
		} 
		else {
			return $old_image;
		}

	}
	
	private static function as_html ($old_image, $new_image = false, $args ) {
		extract( $args, EXTR_SKIP );
		// echo 'here in html';
		// pls_dump($html);
		if ($fancybox && !$as_html) {
			// echo 'fancybox';
			ob_start();
			// our basic fancybox html
			?>
				<a ref="#" rel="<?php echo @$html['rel']; ?>" class="<?php echo isset( $fancybox['trigger_class'] ) ? $fancybox['trigger_class'] : '' . ' ' . ( isset( $html['classes'] ) ? $html['classes'] : '' )  ?>" href="<?php echo @$old_image; ?>" >
					<img alt="<?php echo @$html['alt']; ?>" title="<?php echo @$html['title'] ? $html['title'] : ''; ?>" class="<?php echo @$html['img_classes']; ?>" style="width: <?php echo @$resize['w']; ?>px; height: <?php echo @$resize['h']; ?>px; overflow: hidden;" src="<?php echo $new_image ? $new_image : $old_image; ?>" />
				</a>
			<?php
			
			return trim( ob_get_clean() );
			
			
		} else {
			ob_start();
			?>
			<img class="<?php echo @$html['img_classes']; ?>" style="width: <?php echo @$resize['w']; ?>px; height: <?php echo @$resize['h']; ?>px; overflow: hidden;" src="<?php echo $new_image ? $new_image : $old_image; ?>" alt="<?php echo @$html['alt']; ?>" title="<?php echo $html['title'] ?>" itemprop="image" />
			<?php
		
			return trim(ob_get_clean());
		}
	}
	

	private static function process_defaults ($args) {
		// Define the default argument array
		$defaults = array(
			'resize' => array(
				'w' => false,
				'h' => false
			),
			'allow_resize' => true,
			'html' => array(
				'ref' => '',
				'rel' => 'gallery',
				'a_classes' => '',
				'img_classes' => '',
				'alt' => '',
				'title' => '',
				'itemprop' => ''
			),
			'as_html' => false,
			'as_url' => true,
			'fancybox' => array(
				'trigger_class' => 'pls_use_fancy',
				'classes' => false,
				'null_image' => false,
			)
		);

        /** Merge the arguments with the defaults. */
        $args = wp_parse_args( $args, $defaults );
        $args['resize'] = wp_parse_args( $args['resize'], $defaults['resize']);
        $args['html'] = wp_parse_args( $args['html'], $defaults['html']);

        return $args;	
	}
}
