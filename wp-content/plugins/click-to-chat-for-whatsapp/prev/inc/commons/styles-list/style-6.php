<?php
/**
 * Button with icon - circle
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s6_color = isset($ccw_options_cs['s6_color']) ? esc_attr( $ccw_options_cs['s6_color'] ) : '#25D366';
$s6_hover_color = isset($ccw_options_cs['s6_hover_color']) ? esc_attr( $ccw_options_cs['s6_hover_color'] ) : '#25D366';
$s6_icon_size = isset($ccw_options_cs['s6_icon_size']) ? esc_attr( $ccw_options_cs['s6_icon_size'] ) : '50px';

$s6_circle_background_color = isset($ccw_options_cs['s6_circle_background_color']) ? esc_attr( $ccw_options_cs['s6_circle_background_color'] ) : '#25D366';
$s6_circle_background_hover_color = isset($ccw_options_cs['s6_circle_background_hover_color']) ? esc_attr( $ccw_options_cs['s6_circle_background_hover_color'] ) : '#25D366';
$s6_circle_height = isset($ccw_options_cs['s6_circle_height']) ? esc_attr( $ccw_options_cs['s6_circle_height'] ) : '50px';
$s6_circle_width = isset($ccw_options_cs['s6_circle_width']) ? esc_attr( $ccw_options_cs['s6_circle_width'] ) : '50px';
$s6_line_height = isset($ccw_options_cs['s6_line_height']) ? esc_attr( $ccw_options_cs['s6_line_height'] ) : '50px';

$s6_css_icon = "color: $s6_color; font-size: $s6_icon_size;";
$s6_css_div = "background-color: $s6_circle_background_color; height: $s6_circle_height; width: $s6_circle_width; line-height: $s6_line_height;  ";
?>

<div class="ccw_plugin">
<div class="chatbot btn_only_style_div_circle pointer ccw-analytics animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?>" id="style-6" data-ccw="style-6"
    style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>; <?php echo esc_attr($s6_css_div) ?>"
    onmouseover = "this.style.backgroundColor = '<?php echo esc_attr($s6_circle_background_hover_color) ?>', document.getElementsByClassName('ccw-s6-icon')[0].style.color = '<?php echo esc_attr($s6_hover_color) ?>' "
    onmouseout  = "this.style.backgroundColor = '<?php echo esc_attr($s6_circle_background_color) ?>', document.getElementsByClassName('ccw-s6-icon')[0].style.color = '<?php echo esc_attr($s6_color) ?>' "
    onclick = "<?php echo esc_attr($redirect) ?>" >
        <span class="icon icon-whatsapp2 ccw-s6-icon nofocus ccw-analytics" id="s6-icon" data-ccw="style-6" style="<?php echo esc_attr($s6_css_icon) ?>"></span>
</div>
</div>