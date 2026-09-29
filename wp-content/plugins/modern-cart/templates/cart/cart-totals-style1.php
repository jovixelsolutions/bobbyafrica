<?php
/**
 * Modern Cart Woo Cart Totals
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">

	<div class="moderncart-cart-line-items">
		<?php if ( ! empty( $subtotal ) ) : ?>
			<?php echo wp_kses_post( $subtotal ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $discount ) ) : ?>
			<?php echo wp_kses_post( $discount ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $tax ) ) : ?>
			<?php echo wp_kses_post( $tax ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $shipping ) ) : ?>
			<?php echo wp_kses_post( $shipping ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $total ) ) : ?>
			<?php echo wp_kses_post( $total ); ?>
		<?php endif; ?>
	</div>

	<?php do_action( 'moderncart_slide_out_before_secondary_checkout_button' ); ?>
		<?php if ( ! empty( $secondary_btn ) ) : ?>
			<?php echo wp_kses_post( $secondary_btn ); ?>
		<?php endif; ?>
	<?php do_action( 'moderncart_slide_out_after_secondary_checkout_button' ); ?>

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
