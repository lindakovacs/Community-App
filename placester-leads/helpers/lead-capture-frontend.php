<?php

PLS_Lead_Capture::init();
class PLS_Lead_Capture {

    public static function init () {
        // Add shortcode for template...
        add_shortcode('pl_lead_capture_form', array(__CLASS__, 'lead_capture_shortcode'));
    }

    public static function get_contact_form () {

        // Get args passed to shortcode
        $form_args = func_get_args();
        extract($form_args[0], EXTR_SKIP);

        ob_start();
        ?>
            <div class="lead-capture-wrapper">
              
              <?php if ($title): ?>
                <p class="lc-title"><?php echo $title; ?></p>
              <?php endif ?>
              
              <!-- Lead Capture Description Text -->
              <?php if ($description): ?>
                <div class="lc-description">
                  <p><?php echo $description; ?></p>
                </div>
              <?php endif; ?>
              
              <!-- Contact Form -->
              <div class="lc-contact-form">
                <?php if (class_exists('Placester_Contact_Widget')) {
                    // Lead Capture Form
                    $instance = array(
                        'title' => '',
                        'number' => 9,
                        'phone_number' => true,
                        'name_value' => "Full Name",

                        'name_required' => $name_required,
                        'email_required' => $email_required,
                        'phone_required' => $phone_required,
                        'question_required' => $question_required,

                        'button' => $button,
                        'success_message' => $success_message,
                        'back_on_lc_cancel' => $back_on_cancel == "true" ? true : false
                    );

                    $args = array("id" => 99);  //giving high tab index numbers as to not collide with sidebar widgets

                    $sb = new Placester_Contact_Widget();
                    $sb->number = $instance['number'];
                    $sb->widget($args, $instance);
                } ?>
              </div>
            </div>
        <?php

        $form = ob_get_clean();
        return $form;
    }

    // Lead Capture
    public static function lead_capture_shortcode($args) {
        if(is_user_logged_in()) return;

        $args_with_overrides = shortcode_atts(
            array(
                // Contact Form
                'title' => pls_get_option('pd-lc-form-title'),
                'description' => pls_get_option('pd-lc-form-description'),
                'success_message' => htmlspecialchars(pls_get_option('pd-lc-form-message')),
                'back_on_cancel' => pls_get_option('pd-lc-force-back') ? "true" : "false",

                'name_required' => "true",
                'email_required' => "true",
                'phone_required' => "false",
                'question_required' => "false",
                'button' => "Submit"
            ),
            $args
        );

        echo '<div style="display:none;" href="#" id="property-details-lead-capture">';
        echo self::get_contact_form($args_with_overrides);
        echo '</div>';
    }
} // end class
