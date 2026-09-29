<?php
/**
 * Modern Cart Woo Cart Totals
 *
 * @package modern-cart
 * @version 1.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">

	<div class="moderncart-order-summary-items">
		<div class="moderncart-order-summary-item">
			<span><?php esc_html_e( 'Shipping Fee', 'modern-cart' ); ?></span>
			<span><?php esc_html_e( 'Free', 'modern-cart' ); ?></span>
		</div>

		<div class="moderncart-order-summary-item">
			<span><?php esc_html_e( 'Est. Tax & Fees', 'modern-cart' ); ?></span>
			<span>-</span>
		</div>

		<div class="moderncart-order-summary-item">
			<span><?php esc_html_e( 'Subtotal', 'modern-cart' ); ?></span>
			<span>Rs 55.00</span>
		</div>

		<div class="moderncart-order-summary-item">
			<span><?php esc_html_e( 'Savings', 'modern-cart' ); ?></span>
			<span>Rs 2.00</span>
		</div>

		<div class="moderncart-apply-coupon-container">
			<button type="button" class="moderncart-have-coupon-code-area-trigger"><?php esc_html_e( 'Apply Coupon Code', 'modern-cart' ); ?></button>
		</div>
	</div>

	<div class="moderncart-cart-line-items">
		<?php if ( ! empty( $total ) ) : ?>
			<?php echo wp_kses_post( $total ); ?>
		<?php endif; ?>
	</div>

	<?php do_action( 'moderncart_slide_out_before_checkout_button' ); ?>

	<?php if ( ! is_checkout() ) : ?>
		<div class="wc-proceed-to-checkout">
			<a class="checkout-button wc-forward" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $button_text ); ?>
				<?php echo is_rtl() ? '&#8592;' : '&#8594;'; ?>
			</a>
		</div>
	<?php endif; ?>

	<?php do_action( 'moderncart_slide_out_after_checkout_button' ); ?>

</div>
