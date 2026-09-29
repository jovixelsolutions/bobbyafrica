<?php
/**
 * Template Name: Instant checkout
 *
 * @package CartFlows
 */

	// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wc_cart_url = Cartflows_Instant_Checkout::get_instance()->get_instant_checkout_cart_url();
$flow_id     = wcf()->utils->get_flow_id();
$site_title  = get_bloginfo( 'name' );

$logo_width      = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-instant-checkout-header-logo-width', '130' );
$logo_height     = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-instant-checkout-header-logo-height', '40' );
$header_bg_color = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-instant-checkout-header-color' );
$custom_logo_src = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-instant-checkout-header-logo' );

$custom_logo_styles = 'width: auto; height: ' . $logo_height . 'px;';
$custom_logo        = ! empty( $custom_logo_src ) ? "<img src='" . esc_url( $custom_logo_src ) . "' class='custom-logo' alt='" . esc_attr( $site_title ) . "' style='" . $custom_logo_styles . "' />" : '';

$site_logo = apply_filters( 'cartflows_instant_checkout_header_logo', ! empty( $custom_logo ) ? $custom_logo : get_custom_logo(), $site_title );

// Don't add the header if the Site Title and Site Logo is empty.
if ( empty( $site_logo ) && empty( $site_title ) ) {
	return;
}

$header_css = '';

if ( ! empty( $header_bg_color ) ) {
	$header_css = 'style="background-color: ' . $header_bg_color . '"';
}
?>
<header class="main-header--wrapper" <?php echo wp_kses_post( $header_css ); ?> >
	<div class="main-header--content">
		<?php if ( ! empty( $site_logo ) ) { ?>
			<span class="main-header--site-logo" style="<?php echo ! empty( $logo_width ) ? 'width:' . intval( $logo_width ) . 'px' : ''; ?>; <?php echo ! empty( $logo_height ) ? 'height:' . intval( $logo_height ) . 'px' : ''; ?>;">
				<?php echo wp_kses_post( $site_logo ); ?>
			</span>
		<?php } else { ?>
			<span class="main-header--site-title">
				<?php echo wp_kses_post( $site_title ); ?>
			</span>
		<?php } ?>
		<?php if ( ! empty( $wc_cart_url ) ) { ?>
			<span class="main-header--action">
				<a href="<?php echo esc_url( $wc_cart_url ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="main-header--action__cart-button" width="24" height="24">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
					</svg>
				</a>
			</span>
		<?php } ?>
	</div>
</header>
