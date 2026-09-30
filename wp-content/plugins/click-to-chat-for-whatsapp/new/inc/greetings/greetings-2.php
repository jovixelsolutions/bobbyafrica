<?php
/**
 * Greetings - template - 2
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$g2_options = HT_CTC_Utils::get_option( 'ht_ctc_greetings_2' );
$g2_options = apply_filters( 'ht_ctc_fh_g2_options', $g2_options );
$greetings  = HT_CTC_Utils::get_option( 'ht_ctc_greetings_options' );


// $ht_ctc_greetings['main_content'] = apply_filters( 'the_content', $ht_ctc_greetings['main_content'] );
// main_content shortcodes are processed in HT_CTC_Chat_Greetings (single sink); do not call do_shortcode() here.

// css
$main_css   = 'padding: 18px 20px 15px 20px;';
$send_css   = 'text-align:center; padding: 11px 20px 9px 20px; cursor:pointer;';
$bottom_css = 'padding: 2px 20px 2px 20px;text-align:center; font-size:12px;';

$bg_color = ( isset( $g2_options['bg_color'] ) ) ? esc_attr( $g2_options['bg_color'] ) : '';

if ( '' === $bg_color ) {
	$bg_color = '#ffffff';
}
$main_css   .= "background-color:$bg_color;";
$bottom_css .= "background-color:$bg_color;";
$send_css   .= "background-color:$bg_color;";


// call to action - style
// $cta_style = ( isset($g2_options['cta_style']) ) ? esc_attr( $g2_options['cta_style'] ) : '7_1';
$cta_style    = '1';
$g_cta_path   = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/greetings_styles/g-cta-' . $cta_style . '.php';
$g_optin_path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/greetings_styles/opt-in.php';

?>

<div class="ctc_g_content" style="<?php echo esc_attr( $main_css ); ?>">
	<div class="ctc_g_message_box" style=""><?php echo wp_kses_post( wpautop( $ht_ctc_greetings['main_content'] ) ); ?></div>
</div>

<div class="ctc_g_sentbutton" style="<?php echo esc_attr( $send_css ); ?>">
	<?php
	if ( isset( $ht_ctc_greetings['is_opt_in'] ) && '' !== $ht_ctc_greetings['is_opt_in'] && is_file( $g_optin_path ) ) {
		include $g_optin_path;
	}
	?>
	<?php
	// Match the base widget's "Click Tracking Compatibility" setting so greetings chat clicks
	// are trackable too. CTA style '1' already renders its own <button>, so keep it
	// <div>-wrapped to avoid nesting interactive elements.
	$othersettings = get_option( 'ht_ctc_othersettings', array() );
	$g_link_tag    = ( is_array( $othersettings ) && isset( $othersettings['chat_wrapper_tag'] ) && in_array( $othersettings['chat_wrapper_tag'], array( 'a', 'button' ), true ) ) ? $othersettings['chat_wrapper_tag'] : 'div';
	if ( '1' === (string) $cta_style ) {
		$g_link_tag = 'div';
	}
	$g_link_aria  = ( isset( $ht_ctc_greetings['call_to_action'] ) && '' !== $ht_ctc_greetings['call_to_action'] ) ? $ht_ctc_greetings['call_to_action'] : 'WhatsApp';
	$g_link_reset = 'display:block;text-decoration:none;color:inherit;cursor:pointer;';
	if ( 'button' === $g_link_tag ) {
		$g_link_reset = 'display:block;width:100%;margin:0;padding:0;border:0;background:none;color:inherit;font:inherit;line-height:inherit;text-align:inherit;letter-spacing:inherit;min-width:0;box-shadow:none;-webkit-appearance:none;appearance:none;cursor:pointer;';
	}
	?>
	<?php if ( 'button' === $g_link_tag ) { ?>
	<button type="button" class="ht_ctc_chat_greetings_box_link ctc-analytics" aria-label="<?php echo esc_attr( $g_link_aria ); ?>" style="<?php echo esc_attr( $g_link_reset ); ?>">
	<?php } elseif ( 'a' === $g_link_tag ) { ?>
	<a class="ht_ctc_chat_greetings_box_link ctc-analytics" aria-label="<?php echo esc_attr( $g_link_aria ); ?>" style="<?php echo esc_attr( $g_link_reset ); ?>">
	<?php } else { ?>
	<div class="ht_ctc_chat_greetings_box_link ctc-analytics">
	<?php } ?>
	<?php
	if ( is_file( $g_cta_path ) ) {
		include $g_cta_path;
	}
	?>
	<?php if ( 'button' === $g_link_tag ) { ?>
	</button>
	<?php } elseif ( 'a' === $g_link_tag ) { ?>
	</a>
	<?php } else { ?>
	</div>
	<?php } ?>
</div>

<?php
if ( '' !== $ht_ctc_greetings['bottom_content'] ) {
	?>
<div class="ctc_g_bottom" style="<?php echo esc_attr( $bottom_css ); ?>">
	<?php echo wp_kses_post( wpautop( $ht_ctc_greetings['bottom_content'] ) ); ?>
</div>
	<?php
}
