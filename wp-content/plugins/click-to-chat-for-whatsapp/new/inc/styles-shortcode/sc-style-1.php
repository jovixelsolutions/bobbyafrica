<?php
/**
 * Style 1 button shortcode.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $s1_options = get_option( 'ht_ctc_s1' );
// $s1_img_size = esc_attr( $s1_options['s1_img_size'] );


$s1_options = get_option( 'ht_ctc_s1' );

$s1_css        = '';
$s1_text_color = isset( $s1_options['s1_text_color'] ) ? esc_attr( $s1_options['s1_text_color'] ) : '';
$s1_css       .= ( '' !== $s1_text_color ) ? "color:$s1_text_color;" : '';
$s1_bg_color   = isset( $s1_options['s1_bg_color'] ) ? esc_attr( $s1_options['s1_bg_color'] ) : '';
$s1_css       .= ( '' !== $s1_bg_color ) ? "background-color:$s1_bg_color;" : '';

$s1_style = ( '' !== $s1_css ) ? "style='$s1_css'" : '';

// ICON SETTINGS (Added)
$s1_add_icon   = isset( $s1_options['s1_add_icon'] ) ? esc_attr( $s1_options['s1_add_icon'] ) : '';
$s1_icon_color = isset( $s1_options['s1_icon_color'] ) ? esc_attr( $s1_options['s1_icon_color'] ) : '';
$s1_icon_size  = isset( $s1_options['s1_icon_size'] ) ? esc_attr( $s1_options['s1_icon_size'] ) : '';

if ( '' === $s1_icon_size ) {
	$s1_icon_size = '15';
}

if ( '' === $s1_icon_color ) {
	$s1_icon_color = '#ffffff';
}

if ( '' === $call_to_action ) {
	$call_to_action = 'WhatsApp us';
}

$ctc_type = isset( $type ) ? $type : 'chat';

$icon_html = '';

/* Load Icon if enabled */
if ( '' !== $s1_add_icon ) {

	$s1_svg_css = 'margin-right:6px;';

	$s1_svg_attrs = array(
		'color'          => $s1_icon_color,
		'icon_size'      => $s1_icon_size,
		'type'           => $ctc_type,
		'ht_ctc_svg_css' => $s1_svg_css,
	);

	// Load SVG functions
	include_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';

	// Generate the icon SVG
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - SVG is escaped in its function.
	$icon_html = ht_ctc_singlecolor( $s1_svg_attrs );
}



$o .= '
    <button ' . $s1_style . ' class="ctc-analytics" style="display:flex; align-items:center;">
        ' . $icon_html . '
        <span class="ctc_cta">' . esc_html( $call_to_action ) . '</span>
    </button>
';
