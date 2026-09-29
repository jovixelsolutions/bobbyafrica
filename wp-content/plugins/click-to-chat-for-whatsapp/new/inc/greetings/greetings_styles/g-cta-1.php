<?php
/**
 * Greetings call to action - style - 1
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s1_options = get_option( 'ht_ctc_s1' );

$s1_css        = 'padding:9px;width:100%;cursor:pointer; display:flex; align-items:center; justify-content:center;';
$s1_text_color = ( isset( $s1_options['s1_text_color'] ) ) ? esc_attr( $s1_options['s1_text_color'] ) : '';
$s1_css       .= ( '' !== $s1_text_color ) ? "color:$s1_text_color;" : '';
$s1_bg_color   = ( isset( $s1_options['s1_bg_color'] ) ) ? esc_attr( $s1_options['s1_bg_color'] ) : '';
$s1_css       .= ( '' !== $s1_bg_color ) ? "background-color:$s1_bg_color;" : '';

$s1_add_icon   = ( isset( $s1_options['s1_add_icon'] ) ) ? esc_attr( $s1_options['s1_add_icon'] ) : '';
$s1_icon_color = ( isset( $s1_options['s1_icon_color'] ) ) ? esc_attr( $s1_options['s1_icon_color'] ) : '';
$s1_icon_size  = ( isset( $s1_options['s1_icon_size'] ) ) ? esc_attr( $s1_options['s1_icon_size'] ) : '';

if ( '' === $s1_icon_size ) {
	$s1_icon_size = '15';
}

if ( '' === $s1_icon_color ) {
	$s1_icon_color = '#ffffff';
}

$s1_style = ( '' !== $s1_css ) ? $s1_css : '';
?>
<button 
<?php
if ( $s1_style ) {
	printf( 'style="%s"', esc_attr( $s1_style ) ); }
?>
class="ctc-analytics g_s1_cta_btn ctc_cta">
<?php
if ( '' !== $s1_add_icon ) {

	$s1_svg_css = 'margin-right:6px;';

	$s1_svg_attrs = array(
		'color'          => "$s1_icon_color",
		'icon_size'      => "$s1_icon_size",
		'type'           => 'greetings_chat',
		'ht_ctc_svg_css' => "$s1_svg_css",
	);
	include_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';
  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_singlecolor().
	echo ht_ctc_singlecolor( $s1_svg_attrs );
}
?>
<?php echo esc_html( $ht_ctc_greetings['call_to_action'] ); ?>
</button>
