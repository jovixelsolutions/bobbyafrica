<?php
/**
 * Chip - logo+text
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s4_text_color = isset( $ccw_options_cs['s4_text_color'] ) ? esc_attr( $ccw_options_cs['s4_text_color'] ) : '#ffffff';
$s4_background_color = isset( $ccw_options_cs['s4_background_color'] ) ? esc_attr( $ccw_options_cs['s4_background_color'] ) : '#000000';

?>
<div class="ccw_plugin chatbot" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;">
    <div class="style4 animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?>">
        <a target="_blank" href="<?php echo esc_url($redirect_a) ?>" rel="noreferrer" class="nofocus">
            <div class="chip style-4 ccw-analytics" id="style-4" data-ccw="style-4" style="background-color: <?php echo esc_attr($s4_background_color) ?>; color: <?php echo esc_attr($s4_text_color) ?>">
                <img src="<?php echo esc_url(plugins_url( './new/inc/assets/img/whatsapp-logo-32x32.png', HT_CTC_PLUGIN_FILE )) ?>"  class="ccw-analytics" id="s4-icon" data-ccw="style-4" alt="WhatsApp">
                <?php echo esc_html($val) ?>
            </div>
        </a>
    </div>
</div>