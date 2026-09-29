<?php
/**
 * Style - 99 - own image
 * user can add image
 * 
 * @var string $img_css  - adds css styles based on device, given width, height
 * @var string $own_image  - image url
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// $ccw_options_cs = get_option('ccw_options_cs');
$s_99_img_height_desktop = esc_attr( $ccw_options_cs['s99_img_height_desktop'] );
$s_99_img_width_desktop = esc_attr( $ccw_options_cs['s99_img_width_desktop'] );
$s_99_img_height_mobile = esc_attr( $ccw_options_cs['s99_img_height_mobile'] );
$s_99_img_width_mobile = esc_attr( $ccw_options_cs['s99_img_width_mobile'] );

// img url
// image - width, height based on device
$img_css = "";

// output : is in string ony '1'
if( '1' === $is_mobile ) {
    // $own_image = esc_attr( $ccw_options_cs['s99_mobile_img'] );
    $own_image = esc_url( $ccw_options_cs['s99_mobile_img'] );

    if ( '' !== $s_99_img_height_mobile ) {
        $img_css .= "height: $s_99_img_height_mobile; ";
    }
    if ( '' !== $s_99_img_width_mobile ) {
        $img_css .= "width: $s_99_img_width_mobile; ";
    }
} else {
    // $own_image = esc_attr( $ccw_options_cs['s99_desktop_img'] );
    $own_image = isset($ccw_options_cs['s99_desktop_img']) ? esc_url( $ccw_options_cs['s99_desktop_img'] ) : '';

    if ( '' !== $s_99_img_height_desktop ) {
        $img_css .= "height: $s_99_img_height_desktop; ";
    }
    
    if ( '' !== $s_99_img_width_desktop ) {
        $img_css .= "width: $s_99_img_width_desktop; ";
    }
}

if ( '' === $own_image ) {
    $own_image = plugins_url( './new/inc/assets/img/whatsapp-logo.svg', HT_CTC_PLUGIN_FILE );
}

?>

<div class="ccw_plugin chatbot" style="<?php echo esc_attr($p1) ?>; <?php echo esc_attr($p2) ?>;">
    <div class="ccw_style_99 animated <?php echo esc_attr($an_on_load) .' '. esc_attr($an_on_hover) ?>">
        <a target="_blank" href="<?php echo esc_url($redirect_a) ?>" rel="noreferrer" class="img-icon-a nofocus">   
            <img class="own-img ccw-analytics" id="style-9" data-ccw="style-99-own-image" style="<?php echo esc_attr($img_css) ?>" src="<?php echo esc_url($own_image) ?>" alt="WhatsApp chat">
        </a>
    </div>
</div>