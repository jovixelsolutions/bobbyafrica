<?php
/**
 * WooCommerce form-checkout override
 * Original template: woocommerce/checkout/form-checkout.php
 * WooCommerce template version: 9.4.0 (WooCommerce 10.7.0)
 * Reason: keep WooCommerce checkout hooks and payment processing in a conversion-focused layout.
 * The $checkout object is passed by WooCommerce when loading this template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $checkout ) ) {
	$checkout = WC()->checkout();
}

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>


	<div class="marketplace-page marketplace-checkout">
	<div class="container marketplace-page-shell">
		<h1><?php esc_html_e( 'Secure checkout', 'bobbyafrica-marketplace-child' ); ?></h1>
		<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
			<?php if ( $checkout->get_checkout_fields() ) : ?>
				<div class="checkout-grid">
					<div class="checkout-form-column">
						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
						<div id="customer_details">
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
							<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						</div>
						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
					</div>
					<div class="checkout-summary-column">
						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>
						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
					</div>
				</div>
			<?php endif; ?>
		</form>
	</div>
	</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
