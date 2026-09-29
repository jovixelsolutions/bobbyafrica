<?php
/**
 * Logo
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s3_icon_size = esc_attr( $ccw_options_cs['s3_icon_size'] );

?>
<div class="ccw_plugin chatbot" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;" >
    <div class="ccw_style3 animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?> ">
        <a target="_blank" href="<?php echo esc_url($redirect_a) ?>" rel="noreferrer" class="img-icon-a nofocus">   
            <img class="img-icon ccw-analytics" id="style-3" data-ccw="style-3" style="height: <?php echo esc_attr($s3_icon_size) ?>;" src="<?php echo esc_url(plugins_url( "./new/inc/assets/img/whatsapp-logo.svg", HT_CTC_PLUGIN_FILE )) ?>" alt="WhatsApp chat">
        </a>
    </div>
</div>