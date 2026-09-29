<?php
/**
 * Style 7 extended icon template.
 *
 * @since 3.30 Ensures $is_ctc_admin works with hover and show effects in the admin demo styles.
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// shadow
// 0px 0px 11px rgba(0,0,0,.5)  /   5px 5px 11px #888888
// $s7_bs = "box-shadow: 5px 5px 11px rgba(0,0,0,.5);";
// $s7_bs = "box-shadow: 2px 2px 6px rgba(0,0,0,.5);";
// $s7_box_shadow = "";
// if ( !isset( $s7_options['s3_box_shadow'])) {
// $s7_box_shadow = "$s7_bs ";
// }
// $s7_box_shadow_hover = "";
// if ( isset( $s7_options['s7_box_shadow_hover'])) {
// $s7_box_shadow_hover = "$s7_bs ";
// }

$s7_1_options = get_option( 'ht_ctc_s7_1' );
$s7_1_options = apply_filters( 'ht_ctc_fh_s7_1_options', $s7_1_options );

$is_ctc_admin = '';

if ( is_admin() ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of the 'page' slug on admin screens; no state change or privilege escalation.
	$ctc_page = ( isset( $_GET ) && isset( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'click-to-chat' === $ctc_page || 'click-to-chat-other-settings' === $ctc_page || 'click-to-chat-customize-styles' === $ctc_page ) {
		$is_ctc_admin = 'yes';
	}
}

$s7_icon_size        = isset( $s7_1_options['s7_icon_size'] ) ? esc_attr( $s7_1_options['s7_icon_size'] ) : '20px';
$s7_icon_color       = isset( $s7_1_options['s7_icon_color'] ) ? esc_attr( $s7_1_options['s7_icon_color'] ) : '#ffffff';
$s7_icon_color_hover = isset( $s7_1_options['s7_icon_color_hover'] ) ? esc_attr( $s7_1_options['s7_icon_color_hover'] ) : '#f4f4f4';
$s7_bgcolor          = isset( $s7_1_options['s7_bgcolor'] ) ? esc_attr( $s7_1_options['s7_bgcolor'] ) : '#25D366';
$s7_bgcolor_hover    = isset( $s7_1_options['s7_bgcolor_hover'] ) ? esc_attr( $s7_1_options['s7_bgcolor_hover'] ) : '#00d34d';
$s7_border_size      = isset( $s7_1_options['s7_border_size'] ) ? esc_attr( $s7_1_options['s7_border_size'] ) : '12px';

// Call to action
$s7_cta_type        = isset( $s7_1_options['cta_type'] ) ? esc_attr( $s7_1_options['cta_type'] ) : 'hover';
$s7_1_cta_font_size = isset( $s7_1_options['cta_font_size'] ) ? esc_attr( $s7_1_options['cta_font_size'] ) : '';
$s7_1_cta_font_size = ( '' !== $s7_1_cta_font_size ) ? "font-size: $s7_1_cta_font_size; " : '';


// Call to action - Order
$s7_cta_order             = '1';
$s7_hover_cta_padding_css = 'padding: 0px 21px 0px 0px;';
$s7_show_cta_padding_css  = '';

/*
 * Admin padding adjustments:
 * - Non-admin pages: $s7_show_cta_padding_css adds padding.
 * - Admin demo pages: keep original padding values.
 */
if ( 'yes' !== $is_ctc_admin ) {
	$s7_show_cta_padding_css = 'padding:5px 5px 5px 20px;';
}


if ( isset( $side_2 ) && 'right' === $side_2 ) {
	// if side_2 is right then cta is left
	$s7_cta_order             = '0';
	$s7_hover_cta_padding_css = 'padding: 0px 0px 0px 21px;';
	if ( 'yes' !== $is_ctc_admin ) {
		$s7_show_cta_padding_css = 'padding:5px 20px 5px 5px;';
	}
}

$rtl_css = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';

	// add only if not admin page.
	if ( 'yes' !== $is_ctc_admin ) {
		if ( isset( $side_2 ) && 'right' === $side_2 ) {
			$s7_show_cta_padding_css = 'padding:5px 5px 5px 20px;';
		} else {
			$s7_show_cta_padding_css = 'padding:5px 20px 5px 5px;';
		}
	}
}

$s7_n1_styles        = "display:flex;justify-content:center;align-items:center;$rtl_css ";
$s7_cta_css          = "$s7_1_cta_font_size";
$s7_icon_padding_css = '';
$s7_cta_class        = 'ht-ctc-cta ';
$s7_hover_styles     = '';
if ( 'hover' === $s7_cta_type ) {
	$s7_n1_styles        .= "background-color: $s7_bgcolor; border-radius:25px;";
	$s7_cta_css          .= " display: none; order: $s7_cta_order; color: $s7_icon_color; $s7_hover_cta_padding_css  margin:0 10px; border-radius: 25px; ";
	$s7_cta_class        .= ' ht-ctc-cta-hover ctc_cta_stick ';
	$s7_icon_padding_css .= "padding: $s7_border_size;background-color: $s7_bgcolor;border-radius: 25px; ";
	$s7_hover_styles      = ".ht-ctc .ctc_s_7_1:hover .ctc_s_7_icon_padding, .ht-ctc .ctc_s_7_1:hover{background-color:$s7_bgcolor_hover !important;border-radius: 25px;}.ht-ctc .ctc_s_7_1:hover .ctc_s_7_1_cta{color:$s7_icon_color_hover !important;}.ht-ctc .ctc_s_7_1:hover svg g path{fill:$s7_icon_color_hover !important;}";
} elseif ( 'show' === $s7_cta_type ) {
	$s7_n1_styles .= "$s7_show_cta_padding_css background-color:$s7_bgcolor;border-radius:25px;";
	$s7_cta_css   .= "color: $s7_icon_color; border-radius:10px; margin:0 10px; order: $s7_cta_order; ";

	if ( 'yes' === $is_ctc_admin ) {
		$s7_icon_padding_css .= 'padding: 12px; border-radius:25px;';
		// $s7_border_size
		// $s7_icon_padding_css .= "padding: $s7_border_size; border-radius:25px;";
		$s7_cta_css .= "$s7_hover_cta_padding_css";
	} else {
		// $s7_icon_padding_css .= "padding: $s7_border_size; border-radius:25px;";
		$s7_cta_css .= 'padding: 1px 16px;';
		// $s7_cta_css          .= "$s7_hover_cta_padding_css";
	}

	$s7_hover_styles = ".ht-ctc .ctc_s_7_1:hover{background-color:$s7_bgcolor_hover !important;}.ht-ctc .ctc_s_7_1:hover .ctc_s_7_1_cta{color:$s7_icon_color_hover !important;}.ht-ctc .ctc_s_7_1:hover svg g path{fill:$s7_icon_color_hover !important;}";
}


// svg values
$ht_ctc_svg_css = "pointer-events:none; display:block; height:$s7_icon_size; width:$s7_icon_size;";
$s7_svg_attrs   = array(
	'color'          => "$s7_icon_color",
	'icon_size'      => "$s7_icon_size",
	'type'           => "$type",
	'ht_ctc_svg_css' => "$ht_ctc_svg_css",
);


require_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';

if ( isset( $is_same_side ) && 'no' === $is_same_side && isset( $mobile_side ) ) {
	$s7_cta_class .= ( 'left' === $mobile_side ) ? ' ctc_m_cta_order_1 ' : ' ctc_m_cta_order_0 ';
}
?>
<style id="ht-ctc-s7_1">
<?php echo esc_html( $s7_hover_styles ); ?>
</style>

<div class="ctc_s_7_1 ctc-analytics ctc_nb" style="<?php echo esc_attr( $s7_n1_styles ); ?>" data-nb_top="-7.8px" data-nb_right="-7.8px">
	<p class="ctc_s_7_1_cta ctc-analytics ctc_cta <?php echo esc_attr( $s7_cta_class ); ?>" style="<?php echo esc_attr( $s7_cta_css ); ?>"><?php echo esc_html( $call_to_action ); ?></p>
	<div class="ctc_s_7_icon_padding ctc-analytics " style="<?php echo esc_attr( $s7_icon_padding_css ); ?>">
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_singlecolor().
		echo ht_ctc_singlecolor( $s7_svg_attrs );
		?>
	</div>
</div>
