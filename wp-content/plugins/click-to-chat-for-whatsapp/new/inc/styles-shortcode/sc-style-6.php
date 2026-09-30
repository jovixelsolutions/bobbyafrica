<?php
/**
 * Plain text link.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s6_options = HT_CTC_Utils::get_option( 'ht_ctc_s6' );

$s6_txt_color               = esc_attr( $s6_options['s6_txt_color'] );
$s6_txt_color_on_hover      = esc_attr( $s6_options['s6_txt_color_on_hover'] );
$s6_txt_decoration          = esc_attr( $s6_options['s6_txt_decoration'] );
$s6_txt_decoration_on_hover = esc_attr( $s6_options['s6_txt_decoration_on_hover'] );

// JS-context: esc_js() so values cannot break out of the JS string literal.
// esc_attr alone is insufficient because the browser HTML-decodes the onmouseover
// attribute before executing the JS, so &#039; becomes ' again and can break out.
$input_onhover     = "this.style.color='" . esc_js( $s6_txt_color_on_hover ) . "', this.style.textDecoration='" . esc_js( $s6_txt_decoration_on_hover ) . "'";
$input_onhover_out = "this.style.color='" . esc_js( $s6_txt_color ) . "', this.style.textDecoration='" . esc_js( $s6_txt_decoration ) . "'";


$o .= '
    <a class="ctc-analytics ctc_cta" style="color: ' . $s6_txt_color . '; text-decoration: ' . $s6_txt_decoration . ';"
    onmouseover = "' . $input_onhover . '" 
    onmouseout  = "' . $input_onhover_out . '"
    >
    ' . $call_to_action . '
    </a>
';
