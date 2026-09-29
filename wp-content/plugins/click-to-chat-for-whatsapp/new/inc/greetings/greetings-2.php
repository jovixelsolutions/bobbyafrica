<?php
/**
 * Greetings - template - 2
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$g2_options = get_option( 'ht_ctc_greetings_2' );
$g2_options = apply_filters( 'ht_ctc_fh_g2_options', $g2_options );
$greetings  = get_option( 'ht_ctc_greetings_options' );


// $ht_ctc_greetings['main_content'] = apply_filters( 'the_content', $ht_ctc_greetings['main_content'] );
$ht_ctc_greetings['main_content'] = do_shortcode( $ht_ctc_greetings['main_content'] );

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
	<div class="ht_ctc_chat_greetings_box_link ctc-analytics">
	<?php
	if ( is_file( $g_cta_path ) ) {
		include $g_cta_path;
	}
	?>
	</div>
</div>

<?php
if ( '' !== $ht_ctc_greetings['bottom_content'] ) {
	?>
<div class="ctc_g_bottom" style="<?php echo esc_attr( $bottom_css ); ?>">
	<?php echo wp_kses_post( wpautop( $ht_ctc_greetings['bottom_content'] ) ); ?>
</div>
	<?php
}
