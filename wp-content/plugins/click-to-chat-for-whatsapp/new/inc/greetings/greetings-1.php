<?php
/**
 * Greetings - template - 1
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $greetings_fallback_values, .. is set at class-ht-ctc-admin-greetings-page.php -> settings_values. if loading from other pages, where not set then this will be []
$g1_fallback_values         = isset( $g1_fallback_values ) ? $g1_fallback_values : array();
$greetings_fallback_values  = isset( $greetings_fallback_values ) ? $greetings_fallback_values : array();
$g_settings_fallback_values = isset( $g_settings_fallback_values ) ? $g_settings_fallback_values : array();

// Get options with fallback values
$g1_options         = get_option( 'ht_ctc_greetings_1', $g1_fallback_values );
$g1_options         = apply_filters( 'ht_ctc_fh_g1_options', $g1_options );
$greetings          = get_option( 'ht_ctc_greetings_options', $greetings_fallback_values );
$greetings_settings = get_option( 'ht_ctc_greetings_settings', $g_settings_fallback_values );

$g_header_image_filename = 'header-image';
$is_demo_page            = 'no';

// $ht_ctc_greetings['main_content'] = apply_filters( 'the_content', $ht_ctc_greetings['main_content'] );
$ht_ctc_greetings['main_content'] = do_shortcode( $ht_ctc_greetings['main_content'] );

// css
$header_css = 'display: flex; align-items: center; padding: 12px 25px 12px 25px;';

$main_css = '';

$message_box_css = 'margin: 8px 5px;';
$send_css        = 'text-align:center; padding: 11px 25px 9px 25px; cursor:pointer;background-color:#ffffff;';
$bottom_css      = 'padding: 2px 25px 2px 25px; text-align:center; font-size:12px;background-color:#ffffff;';

$header_bg_color = ( isset( $g1_options['header_bg_color'] ) ) ? esc_attr( $g1_options['header_bg_color'] ) : '';
if ( '' === $header_bg_color ) {
	$header_bg_color = '#ffffff';
}
$main_bg_color = ( isset( $g1_options['main_bg_color'] ) ) ? esc_attr( $g1_options['main_bg_color'] ) : '';
if ( '' === $main_bg_color ) {
	$main_bg_color = '#ffffff';
}
$message_box_bg_color = ( isset( $g1_options['message_box_bg_color'] ) ) ? esc_attr( $g1_options['message_box_bg_color'] ) : '';
$main_bg_image        = ( isset( $g1_options['main_bg_image'] ) ) ? 'yes' : '';

$header_css .= "background-color:$header_bg_color;";
$main_css   .= "background-color:$main_bg_color;";

$rtl_page = '';
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$rtl_page = 'yes';
}

// if $is_demo_page is available this page is loading from demo.php.
if ( isset( $demo_page ) && 'yes' === $demo_page ) {
	$is_demo_page = 'yes';
}

// Since 3.28: Respect greeting size setting when rendering.
$g_size = ( isset( $greetings_settings['g_size'] ) ) ? esc_attr( $greetings_settings['g_size'] ) : 's';

$main_padding_bottom = ( 'yes' === $main_bg_image ) ? '72px' : '40px';

$message_box_minus_width = '20px';

if ( 's' === $g_size ) {
	$message_box_minus_width = '15px';
} elseif ( 'm' === $g_size ) {
	$main_padding_bottom     = '98px';
	$message_box_minus_width = '30px';
} elseif ( 'l' === $g_size ) {
	$main_padding_bottom     = '108px';
	$message_box_minus_width = '40px';
}

$main_css .= ( 'yes' === $rtl_page ) ? "padding: 18px 18px $main_padding_bottom 24px;" : "padding: 18px 24px $main_padding_bottom 18px;";

$g_header_image_css = 'border-radius:50%;height:50px; width:50px;';
if ( 'yes' === $rtl_page ) {
	$g_header_image_css .= 'margin-left:9px;';
} else {
	$g_header_image_css .= 'margin-right:9px;';
}

if ( '' !== $message_box_bg_color ) {
	$message_box_css .= 'padding:6px 8px 8px 9px;';

	// can remove this later.. as added in below style tag. using css variables. as now kept to avoid cache issues.
	if ( ! is_admin() ) {
		// load only if not admin. to make things work for admin demo. (at admin demo on change color update using css variables)
		$message_box_css .= "background-color:$message_box_bg_color;";
	}
}

// call to action - style
$cta_style    = ( isset( $g1_options['cta_style'] ) ) ? esc_attr( $g1_options['cta_style'] ) : '7_1';
$g_cta_path   = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/greetings_styles/g-cta-' . $cta_style . '.php';
$g_optin_path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/greetings_styles/opt-in.php';

$g_header_image = ( isset( $greetings['g_header_image'] ) ) ? esc_attr( $greetings['g_header_image'] ) : '';

if ( '' !== $g_header_image ) {
	$header_css .= 'line-height:1.1;';
} else {
	$header_css .= 'line-height:1.3;';
}

// $is_demo_page - hide the greetings image.. add add based on the demo page.
if ( 'yes' === $is_demo_page && empty( $g_header_image ) ) {
	$g_header_image_css .= 'display:none;';
}

?>
<style>
<?php
if ( 'yes' === $main_bg_image ) {
	$bg_path = plugins_url( './new/inc/assets/img/wa_bg.png', HT_CTC_PLUGIN_FILE );
	?>
.ctc_g_content_for_bg_image:before {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: url('<?php echo esc_url( $bg_path ); ?>');
	opacity: 0.07;
}
	<?php
}
// handle complete message box bg color.. with clip-path at a side.
if ( '' !== $message_box_bg_color ) {
	?>
:root {
	--ctc_g_message_box_bg_color: <?php echo esc_attr( $message_box_bg_color ); ?>;
}
.template-greetings-1 .ctc_g_message_box {
	position: relative;
	max-width: calc(100% - <?php echo esc_attr( $message_box_minus_width ); ?>);
	background-color: var(--ctc_g_message_box_bg_color);
}
.template-greetings-1 .ctc_g_message_box {
	box-shadow: 0 1px 0.5px 0 rgba(0,0,0,.14);
}
.template-greetings-1 .ctc_g_message_box:before {
	content: "";
	position: absolute;
	top: 0px;
	height: 18px;
	width: 9px;
	background-color: var(--ctc_g_message_box_bg_color);
}
	<?php
	if ( 'yes' === $rtl_page ) {
		?>
.ctc_g_message_box {
	border-radius: 7px 0px 7px 7px;
}
.ctc_g_message_box:before {
	left: 100%;
	clip-path: polygon(0% 0%, 0% 50%, 100% 0%);
	-webkit-clip-path: polygon(0% 0%, 0% 50%, 100% 0%);
}
		<?php
	} else {
		?>
.ctc_g_message_box {
	border-radius: 0px 7px 7px 7px;
}
.ctc_g_message_box:before {
	right: 99.7%;
	clip-path: polygon(0% 0%, 100% 0%, 100% 50%);
	-webkit-clip-path: polygon(0% 0%, 100% 0%, 100% 50%);
}
		<?php
	}
}
?>
</style>
<?php

if ( '' !== $ht_ctc_greetings['header_content'] ) {
	?>
	<div class="ctc_g_heading" style="<?php echo esc_attr( $header_css ); ?>">
		<?php
		if ( ! empty( $g_header_image ) || 'yes' === $is_demo_page ) {
			?>
			<div class="greetings_header_image" style="<?php echo esc_attr( $g_header_image_css ); ?>">
				<?php
				try {
					$g_header_image_filename = pathinfo( $g_header_image, PATHINFO_FILENAME );
				} catch ( Exception $e ) {
					$g_header_image_filename = 'header-image';
				}
				?>
				<img style="display:inline-block; border-radius:50%; height:50px; width:50px;" src="<?php echo esc_url( $g_header_image ); ?>" alt="<?php echo esc_attr( $g_header_image_filename ); ?>">
				<?php
				if ( isset( $greetings['g_header_online_status'] ) || 'yes' === $is_demo_page ) {
					$g_header_online_status_color = ( isset( $greetings['g_header_online_status_color'] ) ) ? esc_attr( $greetings['g_header_online_status_color'] ) : '';
					if ( '' === $g_header_online_status_color ) {
						$g_header_online_status_color = '#06e376';
					}
					// adds 'g_header_badge_online' class to the badge from js. to make it work with css.
					?>
					<span class="for_greetings_header_image_badge" style="display:none; border: 2px solid <?php echo esc_attr( $header_bg_color ); ?>; background-color: <?php echo esc_attr( $g_header_online_status_color ); ?>;"></span>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<div class="ctc_g_header_content">
			<?php echo wp_kses_post( wpautop( $ht_ctc_greetings['header_content'] ) ); ?>
		</div>
	</div>
	<?php
}
?>

<?php
// if main content is available
if ( '' !== $ht_ctc_greetings['main_content'] ) {
	if ( 'yes' === $main_bg_image ) {
		// if bg image is added
		?>
		<div class="ctc_g_content" style="<?php echo esc_attr( $main_css ); ?> position:relative;">
			<div class="ctc_g_content_for_bg_image">
				<div class="ctc_g_message_box ctc_g_message_box_width" style="<?php echo esc_attr( $message_box_css ); ?>"><?php echo wp_kses_post( wpautop( $ht_ctc_greetings['main_content'] ) ); ?></div>
			</div>
		</div>
		<?php
	} else {
		// if bg image is not added
		?>
		<div class="ctc_g_content" style="<?php echo esc_attr( $main_css ); ?>">
			<div class="ctc_g_message_box ctc_g_message_box_width" style="<?php echo esc_attr( $message_box_css ); ?>"><?php echo wp_kses_post( wpautop( $ht_ctc_greetings['main_content'] ) ); ?></div>
		</div>
		<?php
	}
}
?>

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
