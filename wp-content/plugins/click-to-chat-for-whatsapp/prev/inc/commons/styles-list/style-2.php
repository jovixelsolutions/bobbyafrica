<?php
/**
 * Plain link
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s2_text_color = isset($ccw_options_cs['s2_text_color']) ? esc_attr( $ccw_options_cs['s2_text_color'] ) : '#25D366';
$s2_text_color_onhover = isset($ccw_options_cs['s2_text_color_onhover']) ? esc_attr( $ccw_options_cs['s2_text_color_onhover'] ) : '#25D366';
$s2_decoration = isset($ccw_options_cs['s2_decoration']) ? esc_attr( $ccw_options_cs['s2_decoration'] ) : 'none';
$s2_decoration_onhover = isset($ccw_options_cs['s2_decoration_onhover']) ? esc_attr( $ccw_options_cs['s2_decoration_onhover'] ) : 'underline';
?>
<div class="ccw_plugin chatbot" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;">
    <div class="style2 animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?> ">
        <a href="<?php echo esc_url($redirect_a) ?>" rel="noreferrer" 
            style="color: <?php echo esc_attr($s2_text_color) ?>; text-decoration: <?php echo esc_attr($s2_decoration) ?>;"
            onmouseover = "this.style.color = '<?php echo esc_attr($s2_text_color_onhover) ?>', this.style.textDecoration = '<?php echo esc_attr($s2_decoration_onhover) ?>' "
            onmouseout  = "this.style.color = '<?php echo esc_attr($s2_text_color) ?>', this.style.textDecoration = '<?php echo esc_attr($s2_decoration) ?>' "
            target="_blank" class="nofocus ccw-analytics" id="style-2" data-ccw="style-2" ><?php echo esc_html($val) ?></a>
    </div>
</div>