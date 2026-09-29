<?php
/**
 * Style 1 button template.
 *
 * Theme button variant for Click to Chat.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s1_options = get_option( 'ht_ctc_s1' );
$s1_options = apply_filters( 'ht_ctc_fh_s1_options', $s1_options );

$s1_css        = '';
$s1_css       .= 'cursor:pointer; display:flex; align-items:center; justify-content:center;';
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

if ( is_admin() ) {
	$s1_css .= 'padding:5px 7px;';
}

$s1_style = ( '' !== $s1_css ) ? $s1_css : '';

$s1_fullwidth_css = '';

if ( '' === $call_to_action ) {
	$call_to_action = 'WhatsApp us';
}

if ( isset( $s1_options['s1_m_fullwidth'] ) ) {
	$s1_fullwidth_css = '@media(max-width:1201px){.ht-ctc.style-1{left:unset !important;right:0px !important;}.ht-ctc.style-1,.ht-ctc .s1_btn{width:100%;}}';

	?>
<style id="ht-ctc-s1"><?php echo esc_html( $s1_fullwidth_css ); ?></style>
	<?php
}


$ctc_type = isset( $type ) ? $type : 'chat';

$icon_html = '';
/* Load Icon if enabled */
if ( '' !== $s1_add_icon ) {

	$s1_svg_css = 'margin-right:6px;';

	$s1_svg_attrs = array(
		'color'          => $s1_icon_color,
		'icon_size'      => $s1_icon_size,
		'type'           => $ctc_type,
		'ht_ctc_svg_css' => $s1_svg_css,
	);

	// Load SVG functions
	include_once HT_CTC_PLUGIN_DIR . 'new/inc/assets/img/ht-ctc-svg-images.php';

	$icon_html = ht_ctc_singlecolor( $s1_svg_attrs );
}
?>

<button 
<?php
if ( $s1_style ) {
	printf( 'style="%s"', esc_attr( $s1_style ) ); }
?>
class="ctc-analytics s1_btn ctc_s_1">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is sanitized at source.
	echo $icon_html;
	?>
	<span class="ctc_cta"><?php echo esc_html( $call_to_action ); ?></span>
</button>

<?php
// instead of display message like this.. remove here and focus at customize styles settings.. and at select style..
// admin - add for admin demo
if ( is_admin() ) {
  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of the 'page' slug on admin screens; no state change or privilege escalation.
	$ctc_page = ( isset( $_GET ) && isset( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'click-to-chat' === $ctc_page || 'click-to-chat-customize-styles' === $ctc_page ) {
		?>
	<p class="description s1_admin_demo_note">Front-End: Theme Button</p>
		<?php
	}
}
