<?php
/**
 * Logo
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s9_icon_size = isset($ccw_options_cs['s9_icon_size']) ? esc_attr( $ccw_options_cs['s9_icon_size'] ) : '50px';
?>

<div class="ccw_plugin chatbot" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;">
    <div class="ccw_style9 animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?>">
        <a target="_blank" href="<?php echo esc_url($redirect_a) ?>" rel="noreferrer" class="img-icon-a nofocus">   
            <img class="img-icon ccw-analytics" id="style-9" data-ccw="style-9" style="height: <?php echo esc_attr($s9_icon_size) ?>;" src="<?php echo esc_url(plugins_url( './new/inc/assets/img/whatsapp-icon-square.svg', HT_CTC_PLUGIN_FILE )) ?>" alt="WhatsApp chat">
        </a>
    </div>
</div>