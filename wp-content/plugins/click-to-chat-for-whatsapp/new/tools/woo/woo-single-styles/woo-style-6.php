<?php
/**
 * Woo style 6 template.
 *
 * Woo single styles located in woo-single-styles/woo-style-*.php for single product pages.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s6_options = get_option( 'ht_ctc_s6' );
$s6_options = apply_filters( 'ht_ctc_fh_s6_options', $s6_options );

$s6_txt_color               = esc_attr( $s6_options['s6_txt_color'] );
$s6_txt_color_on_hover      = esc_attr( $s6_options['s6_txt_color_on_hover'] );
$s6_txt_decoration          = esc_attr( $s6_options['s6_txt_decoration'] );
$s6_txt_decoration_on_hover = esc_attr( $s6_options['s6_txt_decoration_on_hover'] );
?>

<a class="ctc-analytics ctc_cta" style="color: <?php echo esc_attr( $s6_txt_color ); ?>; text-decoration: <?php echo esc_attr( $s6_txt_decoration ); ?>;"
	onmouseover = "this.style.color = '<?php echo esc_attr( $s6_txt_color_on_hover ); ?>', this.style.textDecoration = '<?php echo esc_attr( $s6_txt_decoration_on_hover ); ?>' "
	onmouseout  = "this.style.color = '<?php echo esc_attr( $s6_txt_color ); ?>', this.style.textDecoration = '<?php echo esc_attr( $s6_txt_decoration ); ?>' "
	>
	<?php echo esc_html( $call_to_action ); ?>
</a>
