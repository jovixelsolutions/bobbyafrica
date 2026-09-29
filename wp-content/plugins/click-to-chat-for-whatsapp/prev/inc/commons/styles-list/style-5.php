<?php
/**
 * Plan icon - similar to sytle-3, an icon
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s5_color = isset($ccw_options_cs['s5_color']) ? esc_attr( $ccw_options_cs['s5_color'] ) : '#25D366';
$s5_hover_color = isset($ccw_options_cs['s5_hover_color']) ? esc_attr( $ccw_options_cs['s5_hover_color'] ) : '#25D366';
$s5_icon_size = isset($ccw_options_cs['s5_icon_size']) ? esc_attr( $ccw_options_cs['s5_icon_size'] ) : '50px';
?>
<div class="ccw_plugin">
    <div class="style-5 chatbot nofocus animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?>" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;">
            <a target="_blank" class="nofocus icon icon-whatsapp2 icon-2 ccw-analytics" id="stye-5" data-ccw="style-5" 
                href="<?php echo esc_url($redirect_a) ?>" rel="noreferrer" 
                style = "color: <?php echo esc_attr($s5_color) ?>; font-size: <?php echo esc_attr($s5_icon_size) ?>;"
                onmouseover = "this.style.color = '<?php echo esc_attr($s5_hover_color) ?>' "
                onmouseout  = "this.style.color = '<?php echo esc_attr($s5_color) ?>' " >   
            </a>
    </div>
</div>