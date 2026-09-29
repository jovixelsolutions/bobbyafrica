<?php
/**
 * Style - 3_1 - s3 extend
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s3_1_options = get_option( 'ht_ctc_s3_1' );
$s3_1_options = apply_filters( 'ht_ctc_fh_s3_1_options', $s3_1_options );

$img_size = esc_attr( $s3_1_options['s3_img_size'] );
if ( '' === $img_size ) {
	$img_size = '40px';
}

// Call to action
$s3_1_cta_type = ( isset( $s3_1_options['cta_type'] ) ) ? esc_attr( $s3_1_options['cta_type'] ) : 'hover';

$s3_1_cta_textcolor = ( isset( $s3_1_options['cta_textcolor'] ) ) ? esc_attr( $s3_1_options['cta_textcolor'] ) : '';
$s3_1_cta_bgcolor   = ( isset( $s3_1_options['cta_bgcolor'] ) ) ? esc_attr( $s3_1_options['cta_bgcolor'] ) : '#ffffff';
$s3_1_cta_font_size = ( isset( $s3_1_options['cta_font_size'] ) ) ? esc_attr( $s3_1_options['cta_font_size'] ) : '';

$s3_1_cta_textcolor = ( '' !== $s3_1_cta_textcolor ) ? "color: $s3_1_cta_textcolor" : '';
$s3_1_cta_bgcolor   = ( '' !== $s3_1_cta_bgcolor ) ? "background-color: $s3_1_cta_bgcolor" : '';
$s3_1_cta_font_size = ( '' !== $s3_1_cta_font_size ) ? "font-size: $s3_1_cta_font_size" : '';

$s3_1_cta_css   = "padding: 0px 16px; line-height: 1.6; $s3_1_cta_font_size; $s3_1_cta_bgcolor; $s3_1_cta_textcolor; border-radius:10px; margin:0 10px; ";
$s3_1_cta_class = 'ht-ctc-cta ';

$ht_ctc_svg_css = "pointer-events:none; display:block; height:$img_size; width:$img_size;";

require_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';


$rtl_css = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';
}

$s3_1_css = "display:inline-flex;justify-content:center;align-items:center;$rtl_css ";

// extend
$s3_1_padding        = ( isset( $s3_1_options['s3_padding'] ) ) ? esc_attr( $s3_1_options['s3_padding'] ) : '';
$s3_1_bg_color       = ( isset( $s3_1_options['s3_bg_color'] ) ) ? esc_attr( $s3_1_options['s3_bg_color'] ) : '#25D366';
$s3_1_bg_color_hover = ( isset( $s3_1_options['s3_bg_color_hover'] ) ) ? esc_attr( $s3_1_options['s3_bg_color_hover'] ) : '#25D366';

$s3_1_box_shadow = '';
if ( isset( $s3_1_options['s3_box_shadow'] ) ) {
	$s3_1_box_shadow = 'box-shadow: 0px 0px 11px rgba(0,0,0,.5);';
}
$s3_1_extend_css = "background-color: $s3_1_bg_color; padding: $s3_1_padding; border-radius: 50%; $s3_1_box_shadow";

$s3_1_box_shadow_hover = '';
if ( isset( $s3_1_options['s3_box_shadow_hover'] ) ) {
	$s3_1_box_shadow_hover = 'box-shadow:0px 0px 11px rgba(0,0,0,.5);';
}
// hover css
$s3_1_hover_css = "background-color:$s3_1_bg_color_hover !important;$s3_1_box_shadow_hover";

$others = array(
	'bg_color' => "$s3_1_bg_color",
);

?>
<style id="ht-ctc-s3">
.ht-ctc .ctc_s_3_1:hover svg stop{stop-color:<?php echo esc_attr( $s3_1_bg_color_hover ); ?>;}.ht-ctc .ctc_s_3_1:hover .ht_ctc_padding,.ht-ctc .ctc_s_3_1:hover .ctc_cta_stick{<?php echo esc_attr( $s3_1_hover_css ); ?>}
</style>

<div title="<?php echo esc_attr( $call_to_action ); ?>" style="<?php echo esc_attr( $s3_1_css ); ?>" class="ctc_s_3_1">
	<div class="ctc-analytics ht_ctc_padding" style="<?php echo esc_attr( $s3_1_extend_css ); ?>">
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_style_3_1_svg().
		echo ht_ctc_style_3_1_svg( $img_size, $type, $ht_ctc_svg_css, $others );
		?>
	</div>
</div>
