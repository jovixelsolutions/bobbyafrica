<?php
/**
 * Style - 4
 *
 * Chip
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s4_options = get_option( 'ht_ctc_s4' );
$s4_options = apply_filters( 'ht_ctc_fh_s4_options', $s4_options );

$s4_text_color = isset( $s4_options['s4_text_color'] ) ? esc_attr( $s4_options['s4_text_color'] ) : '#000000';

$s4_bg_color     = isset( $s4_options['s4_bg_color'] ) ? esc_attr( $s4_options['s4_bg_color'] ) : '#e4e4e4';
$s4_img_url      = isset( $s4_options['s4_img_url'] ) ? esc_attr( $s4_options['s4_img_url'] ) : '';
$s4_img_position = ( isset( $s4_options['s4_img_position'] ) ) ? esc_attr( $s4_options['s4_img_position'] ) : 'left';
$s4_img_size     = ( isset( $s4_options['s4_img_size'] ) ) ? esc_attr( $s4_options['s4_img_size'] ) : '';
if ( '' === $s4_img_size ) {
	$s4_img_size = '32px';
}

if ( '' === $call_to_action ) {
	$call_to_action = 'WhatsApp us';
}

if ( 'left' === $s4_img_position ) {
	$s4_margin = '0 8px 0 -12px;';
	// $s4_margin = "0 8px 0 -13px;";
	$s4_order = '0';
} else {
	$s4_margin = '0 -12px 0 8px;';
	$s4_order  = '1';
}

$rtl_css = '';
if ( is_rtl() ) {
	$rtl_css = 'flex-direction:row-reverse;';
}

// 3.30 class name chip is replaced as ctc_chip (to avoid conflict with other styles)

$s4_chip_css     = "display:flex;justify-content: center;align-items: center;background-color:$s4_bg_color;color:$s4_text_color;padding:0 12px;border-radius:25px;font-size:13px;line-height:32px;$rtl_css ";
$s4_chip_svg_css = "margin:$s4_margin;order:$s4_order;";
$s4_chip_img_css = "margin:$s4_margin;order:$s4_order;height:$s4_img_size;width:$s4_img_size;border-radius:50%";
$ht_ctc_svg_css  = "pointer-events:none; display: block; height:$s4_img_size; width:$s4_img_size;";
?>

<div class="ctc_chip ctc-analytics ctc_s_4 ctc_nb" style="<?php echo esc_attr( $s4_chip_css ); ?>" data-nb_top="-10px" data-nb_right="-10px">
	<?php
	if ( '' === $s4_img_url ) {
		include_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';
		$ctc_type = "$type-s4";
		?>
		<span class="s4_img" style="<?php echo esc_attr( $s4_chip_svg_css ); ?>">
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup is escaped in ht_ctc_style_3_svg().
		echo ht_ctc_style_3_svg( $s4_img_size, $ctc_type, $ht_ctc_svg_css );
		?>
		</span>
		<?php
	} else {
		// if user changed the image
		?>
		<img class="s4_img" style="<?php echo esc_attr( $s4_chip_img_css ); ?>" src="<?php echo esc_url( $s4_img_url ); ?>" alt="<?php echo esc_attr( $call_to_action ); ?>">
		<?php
	}
	?>
	<span class="ctc_cta"><?php echo esc_html( $call_to_action ); ?></span>
</div>
