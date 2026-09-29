<?php
/**
 * Style 7 icon with customise padding.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s7_options = get_option( 'ht_ctc_s7' );
$s7_options = apply_filters( 'ht_ctc_fh_s7_options', $s7_options );

$s7_icon_size          = isset( $s7_options['s7_icon_size'] ) ? esc_attr( $s7_options['s7_icon_size'] ) : '20px';
$s7_icon_color         = isset( $s7_options['s7_icon_color'] ) ? esc_attr( $s7_options['s7_icon_color'] ) : '#ffffff';
$s7_icon_color_hover   = isset( $s7_options['s7_icon_color_hover'] ) ? esc_attr( $s7_options['s7_icon_color_hover'] ) : '#f4f4f4';
$s7_border_size        = isset( $s7_options['s7_border_size'] ) ? esc_attr( $s7_options['s7_border_size'] ) : '12px';
$s7_border_color       = isset( $s7_options['s7_border_color'] ) ? esc_attr( $s7_options['s7_border_color'] ) : '#25D366';
$s7_border_color_hover = isset( $s7_options['s7_border_color_hover'] ) ? esc_attr( $s7_options['s7_border_color_hover'] ) : '#25d366';
$s7_border_radius      = isset( $s7_options['s7_border_radius'] ) ? esc_attr( $s7_options['s7_border_radius'] ) : '50%';


// Call to action
$s7_cta_type      = ( isset( $s7_options['cta_type'] ) ) ? esc_attr( $s7_options['cta_type'] ) : 'hover';
$s7_cta_textcolor = ( isset( $s7_options['cta_textcolor'] ) ) ? esc_attr( $s7_options['cta_textcolor'] ) : '';
$s7_cta_bgcolor   = ( isset( $s7_options['cta_bgcolor'] ) ) ? esc_attr( $s7_options['cta_bgcolor'] ) : '#ffffff';

$s7_cta_font_size = ( isset( $s7_options['cta_font_size'] ) ) ? esc_attr( $s7_options['cta_font_size'] ) : '';

$s7_cta_font_size = ( '' !== $s7_cta_font_size ) ? "font-size: $s7_cta_font_size;" : '';

$rtl_css = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';
}

$s7_n1_styles = "display:flex;justify-content:center;align-items:center;$rtl_css ";
$s7_icon_css  = "font-size: $s7_icon_size; color: $s7_icon_color; padding: $s7_border_size; background-color: $s7_border_color; border-radius: $s7_border_radius;";

// Call to action - order
$s7_cta_order = '1';
if ( isset( $side_2 ) && 'right' === $side_2 ) {
	// if side_2 is right then cta is left
	$s7_cta_order = '0';
}


$s7_cta_css   = "padding: 0px 16px; $s7_cta_font_size color: $s7_cta_textcolor; background-color: $s7_cta_bgcolor; border-radius:10px; margin:0 10px; ";
$s7_cta_class = 'ht-ctc-cta ';
$ctc_title    = '';
if ( 'hover' === $s7_cta_type ) {
	$s7_cta_css   .= " display: none; order: $s7_cta_order; ";
	$s7_cta_class .= ' ht-ctc-cta-hover ';
} elseif ( 'show' === $s7_cta_type ) {
	$s7_cta_css .= "order: $s7_cta_order; ";
} elseif ( 'hide' === $s7_cta_type ) {
	$s7_cta_css .= ' display: none; ';
	$ctc_title   = "title = '$call_to_action'";
}

// svg values
$ht_ctc_svg_css = "pointer-events:none; display:block; height:$s7_icon_size; width:$s7_icon_size;";
$s7_svg_attrs   = array(
	'color'          => "$s7_icon_color",
	'icon_size'      => "$s7_icon_size",
	'type'           => "$type",
	'ht_ctc_svg_css' => "$ht_ctc_svg_css",
);

// hover
$s7_hover_icon_styles = ".ht-ctc .ctc_s_7:hover .ctc_s_7_icon_padding, .ht-ctc .ctc_s_7:hover .ctc_cta_stick{background-color:$s7_border_color_hover !important;}.ht-ctc .ctc_s_7:hover svg g path{fill:$s7_icon_color_hover !important;}";

require_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';

if ( isset( $is_same_side ) && 'no' === $is_same_side && isset( $mobile_side ) ) {
	$s7_cta_class .= ( 'left' === $mobile_side ) ? ' ctc_m_cta_order_1 ' : ' ctc_m_cta_order_0 ';
}
?>
<style id="ht-ctc-s7">
<?php echo esc_html( $s7_hover_icon_styles ); ?>
</style>

<div <?php echo esc_attr( $ctc_title ); ?> class="ctc_s_7 ctc-analytics ctc_nb" style="<?php echo esc_attr( $s7_n1_styles ); ?>" data-nb_top="-7.8px" data-nb_right="-7.8px">
	<p class="ctc_s_7_cta ctc_cta ctc_cta_stick ctc-analytics <?php echo esc_attr( $s7_cta_class ); ?>" style="<?php echo esc_attr( $s7_cta_css ); ?>"><?php echo esc_html( $call_to_action ); ?></p>
	<div class="ctc_s_7_icon_padding ctc-analytics " style="<?php echo esc_attr( $s7_icon_css ); ?>">
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_singlecolor().
		echo ht_ctc_singlecolor( $s7_svg_attrs );
		?>
	</div>
</div>
