<?php
/**
 * Style - 99
 * Own image / GIF.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s_99_options = get_option( 'ht_ctc_s99' );
$s_99_options = apply_filters( 'ht_ctc_fh_s99_options', $s_99_options );

// Ensure options are set or use fallback values
$s_99_desktop_img_height = isset( $s_99_options['s99_desktop_img_height'] ) ? esc_attr( $s_99_options['s99_desktop_img_height'] ) : '50px';
$s_99_desktop_img_width  = isset( $s_99_options['s99_desktop_img_width'] ) ? esc_attr( $s_99_options['s99_desktop_img_width'] ) : '50px';
$s_99_mobile_img_height  = isset( $s_99_options['s99_mobile_img_height'] ) ? esc_attr( $s_99_options['s99_mobile_img_height'] ) : '40px';
$s_99_mobile_img_width   = isset( $s_99_options['s99_mobile_img_width'] ) ? esc_attr( $s_99_options['s99_mobile_img_width'] ) : '40px';

$filename = $call_to_action;

// img - url, width, height based on device
$s_99_img_css = '';

if ( isset( $is_mobile ) && 'yes' === $is_mobile ) {
	$s_99_own_image = isset( $s_99_options['s99_mobile_img_url'] ) ? esc_html( $s_99_options['s99_mobile_img_url'] ) : '';
	$s_99_img_css  .= ( '' !== $s_99_mobile_img_height ) ? "height: $s_99_mobile_img_height; " : 'height: 40px; ';

	if ( '' !== $s_99_mobile_img_width ) {
		$s_99_img_css .= "width: $s_99_mobile_img_width; ";
	}
} else {
	$s_99_own_image = isset( $s_99_options['s99_dekstop_img_url'] ) ? esc_html( $s_99_options['s99_dekstop_img_url'] ) : '';
	$s_99_img_css  .= ( '' !== $s_99_desktop_img_height ) ? "height: $s_99_desktop_img_height; " : 'height: 50px; ';

	if ( '' !== $s_99_desktop_img_width ) {
		$s_99_img_css .= "width: $s_99_desktop_img_width; ";
	}
}

// fallback image
if ( '' === $s_99_own_image ) {
	$s_99_own_image = plugins_url( './new/inc/assets/img/whatsapp-logo.svg', HT_CTC_PLUGIN_FILE );
}


try {
	$filename = pathinfo( $s_99_own_image, PATHINFO_FILENAME );
} catch ( Exception $e ) {
	$filename = $call_to_action;
}

?>

<img class="own-img ctc-analytics ctc_s_99 ctc_cta" title="<?php echo esc_attr( $call_to_action ); ?>" id="style-99" src="<?php echo esc_url( $s_99_own_image ); ?>" style="<?php echo esc_attr( $s_99_img_css ); ?>" alt="<?php echo esc_attr( $filename ); ?>">
