<?php
/**
 * Style-1 - new method. 
 *  default button, looks like theme.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="ccw_plugin chatbot" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;">
    <div class="style1 animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?> ">
        <button onclick="<?php echo esc_attr($redirect) ?>"><?php echo esc_html($val) ?></button>    
    </div>
</div>