<?php
/**
 * Style - 2
 *
 * Android like - WhatsApp icon
 *
 * @included from
 *  class-ht-ctc-chat.php (class-ht-ctc- chat/group/share .php)
 *  class-ht-ctc-woo.php
 *
 * External variable are from included files:
 *  $call_to_action
 *  $type
 *  $side_2 (sub file: position-to-place.php is included in some of the files that included this file )
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s2_options = get_option( 'ht_ctc_s2' );
$s2_options = apply_filters( 'ht_ctc_fh_s2_options', $s2_options );

$s2_img_size = ( isset( $s2_options['s2_img_size'] ) ) ? esc_attr( $s2_options['s2_img_size'] ) : '';
$img_size    = $s2_img_size;
if ( '' === $img_size ) {
	$img_size = '50px';
}

// Call to action
$s2_cta_type = ( isset( $s2_options['cta_type'] ) ) ? esc_attr( $s2_options['cta_type'] ) : 'hover';

$s2_cta_order = '1';
if ( isset( $side_2 ) && 'right' === $side_2 ) {
	// if side_2 is right then cta is left
	$s2_cta_order = '0';
}

$rtl_css = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';
}

$s2_cta_textcolor = ( isset( $s2_options['cta_textcolor'] ) ) ? esc_attr( $s2_options['cta_textcolor'] ) : '';
$s2_cta_bgcolor   = ( isset( $s2_options['cta_bgcolor'] ) ) ? esc_attr( $s2_options['cta_bgcolor'] ) : '#ffffff';
$s2_cta_font_size = ( isset( $s2_options['cta_font_size'] ) ) ? esc_attr( $s2_options['cta_font_size'] ) : '';

$s2_cta_textcolor = ( '' !== $s2_cta_textcolor ) ? "color: $s2_cta_textcolor" : '';
$s2_cta_bgcolor   = ( '' !== $s2_cta_bgcolor ) ? "background-color: $s2_cta_bgcolor" : '';
$s2_cta_font_size = ( '' !== $s2_cta_font_size ) ? "font-size: $s2_cta_font_size" : '';

$s2_css       = "display: flex; justify-content: center; align-items: center; $rtl_css ";
$s2_cta_css   = "padding: 0px 16px; line-height: 1.6; $s2_cta_font_size; $s2_cta_bgcolor; $s2_cta_textcolor; border-radius:10px; margin:0 10px; ";
$s2_cta_class = 'ht-ctc-cta ';
$ctc_title    = '';
if ( 'hover' === $s2_cta_type ) {
	$s2_cta_css   .= " display: none; order: $s2_cta_order; ";
	$s2_cta_class .= ' ht-ctc-cta-hover ';
} elseif ( 'show' === $s2_cta_type ) {
	$s2_cta_css .= "order: $s2_cta_order; ";
} elseif ( 'hide' === $s2_cta_type ) {
	$s2_cta_css .= ' display: none; ';
	$ctc_title   = "title = '$call_to_action'";
}

// default order is based on desktop. if desktop and mobile not same side. then add class name to change order..
if ( isset( $is_same_side ) && 'no' === $is_same_side && isset( $mobile_side ) ) {
	$s2_cta_class .= ( 'left' === $mobile_side ) ? ' ctc_m_cta_order_1 ' : ' ctc_m_cta_order_0 ';
}

$ht_ctc_svg_css = "pointer-events:none; display:block; height:$img_size; width:$img_size;";

require_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';
?>
<div <?php echo esc_attr( $ctc_title ); ?> style="<?php echo esc_attr( $s2_css ); ?>" class="ctc-analytics ctc_s_2">
	<p class="ctc-analytics ctc_cta ctc_cta_stick <?php echo esc_attr( $s2_cta_class ); ?>" style="<?php echo esc_attr( $s2_cta_css ); ?>"><?php echo esc_html( $call_to_action ); ?></p>
	<?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_style_2_svg().
	echo ht_ctc_style_2_svg( $img_size, $type, $ht_ctc_svg_css );
	?>
</div>
