<?php
/**
 * Greetings call to action - style - 7 Extend
 *
 * <input class="ht_ctc_chat_greetings_box_link" type="submit" style="" value="<?php echo $ht_ctc_greetings['call_to_action'] ?>">
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s7_1_options = get_option( 'ht_ctc_s7_1' );
$s7_1_options = apply_filters( 'ht_ctc_fh_s7_1_options', $s7_1_options );

$s7_icon_size        = ( isset( $s7_1_options['s7_icon_size'] ) ) ? esc_attr( $s7_1_options['s7_icon_size'] ) : '';
$s7_icon_color       = ( isset( $s7_1_options['s7_icon_color'] ) ) ? esc_attr( $s7_1_options['s7_icon_color'] ) : '';
$s7_icon_color_hover = ( isset( $s7_1_options['s7_icon_color_hover'] ) ) ? esc_attr( $s7_1_options['s7_icon_color_hover'] ) : '';
$s7_bgcolor          = ( isset( $s7_1_options['s7_bgcolor'] ) ) ? esc_attr( $s7_1_options['s7_bgcolor'] ) : '';
$s7_bgcolor_hover    = ( isset( $s7_1_options['s7_bgcolor_hover'] ) ) ? esc_attr( $s7_1_options['s7_bgcolor_hover'] ) : '';
$s7_border_size      = ( isset( $s7_1_options['s7_border_size'] ) ) ? esc_attr( $s7_1_options['s7_border_size'] ) : '';

// Call to action
$s7_1_cta_font_size = ( isset( $s7_1_options['cta_font_size'] ) ) ? esc_attr( $s7_1_options['cta_font_size'] ) : '';

$s7_1_cta_font_size = ( '' !== $s7_1_cta_font_size ) ? "font-size: $s7_1_cta_font_size" : '';

// Call to action - Order
$s7_cta_order            = '1';
$s7_show_cta_padding_css = 'padding:5px;';


if ( isset( $side_2 ) && 'right' === $side_2 ) {
	// if side_2 is right then cta is left
	$s7_cta_order = '0';
}

$rtl_css = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';
}

$s7_n1_styles        = "display:flex;justify-content:center;align-items:center;$rtl_css ";
$s7_cta_css          = "$s7_1_cta_font_size; ";
$s7_icon_padding_css = '';
$s7_cta_class        = 'ht-ctc-cta ';
$s7_hover_styles     = '';

$s7_n1_styles        .= "$s7_show_cta_padding_css background-color:$s7_bgcolor;border-radius:25px; cursor: pointer;";
$s7_cta_css          .= "padding:1px 0px; color:$s7_icon_color; border-radius:10px; margin:0 10px; order:$s7_cta_order; ";
$s7_icon_padding_css .= '';
$s7_hover_styles      = ".ht-ctc .g_ctc_s_7_1:hover{background-color:$s7_bgcolor_hover !important;}.ht-ctc .g_ctc_s_7_1:hover .g_ctc_s_7_1_cta{color:$s7_icon_color_hover !important;}.ht-ctc .g_ctc_s_7_1:hover svg g path{fill:$s7_icon_color_hover !important;}";


// svg values
$ht_ctc_svg_css = "pointer-events:none; display:block; height:$s7_icon_size; width:$s7_icon_size;";
$s7_svg_attrs   = array(
	'color'          => "$s7_icon_color",
	'icon_size'      => "$s7_icon_size",
	'type'           => 'greetings_chat',
	'ht_ctc_svg_css' => "$ht_ctc_svg_css",
);


require_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';
?>
<style id="ht-ctc-s7_1">
<?php echo esc_html( $s7_hover_styles ); ?>
</style>

<div class="g_ctc_s_7_1 ctc-analytics" style="<?php echo esc_attr( $s7_n1_styles ); ?>">
	<p class="g_ctc_s_7_1_cta ctc-analytics ctc_cta <?php echo esc_attr( $s7_cta_class ); ?>" style="<?php echo esc_attr( $s7_cta_css ); ?>"><?php echo esc_html( $ht_ctc_greetings['call_to_action'] ); ?></p>
	<div class="g_ctc_s_7_icon_padding ctc-analytics " style="<?php echo esc_attr( $s7_icon_padding_css ); ?>">
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_singlecolor().
		echo ht_ctc_singlecolor( $s7_svg_attrs );
		?>
	</div>
</div>
