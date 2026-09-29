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
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s2_options = get_option( 'ht_ctc_s2' );
$s2_options = apply_filters( 'ht_ctc_fh_s2_options', $s2_options );

$s2_img_size = esc_attr( $s2_options['s2_img_size'] );
$img_size    = esc_attr( $s2_options['s2_img_size'] );
if ( '' === $img_size ) {
	$img_size = '50px';
}

$rtl_css = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';
}

$s2_css = "display:inline-flex; justify-content: center; align-items: center; $rtl_css ";

$ht_ctc_svg_css = "pointer-events:none; display:block; height:$img_size; width:$img_size;";

require_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';
?>
<div title="<?php echo esc_attr( $call_to_action ); ?>" style="<?php echo esc_attr( $s2_css ); ?>" class="ctc-analytics">
	<?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_style_2_svg().
	echo ht_ctc_style_2_svg( $img_size, $type, $ht_ctc_svg_css );
	?>
</div>
