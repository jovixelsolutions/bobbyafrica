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


$order_summary_items_label_value = [
	'shipping_fee' => [
		'label' => __( 'Shipping Fee', 'modern-cart' ),
		'value' => $shipping,
	],
	'est_tax_fees' => [
		'label' => __( 'Est. Tax & Fees', 'modern-cart' ),
		'value' => $tax,
	],
	'subtotal'     => [
		'label' => __( 'Subtotal', 'modern-cart' ),
		'value' => $subtotal,
	],
	'discount'     => [
		'label' => __( 'Discount', 'modern-cart' ),
		'value' => $discount,
	],
];
?>

<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">

	<h3><?php esc_html_e( 'Order Summary', 'modern-cart' ); ?></h3>

	<div class="moderncart-order-summary-items">
		<?php
		do_action( 'moderncart_slide_out_before_order_summary_items', $args );

		foreach ( $order_summary_items_label_value as $item ) {
			if ( empty( $item['value'] ) ) {
				continue;
			}
			?>
			<div class="moderncart-order-summary-item">
				<label><?php echo esc_html( $item['label'] ); ?></label>
				<?php echo wp_kses_post( $item['value'] ); ?>
			</div>
			<?php
		}

		do_action( 'moderncart_slide_out_after_order_summary_items', ! empty( $args['data_args'] ) ? $args['data_args'] : $args );
		?>
	</div>

	<?php do_action( 'moderncart_slide_out_before_checkout_button' ); ?>

	<?php if ( ! is_checkout() ) : ?>
		<div class="wc-proceed-to-checkout">
			<a class="checkout-button wc-forward" href="<?php echo esc_url( $url ); ?>">
				<span class="btn-text">
					<?php echo esc_html( $button_text ); ?>
					<?php echo is_rtl() ? '&#8592;' : '&#8594;'; ?>
				</span>
				<span class="btn-total-price">
					<?php if ( ! empty( $total ) ) : ?>
						<?php echo wp_kses_post( $total ); ?>
					<?php endif; ?>
				</span>
			</a>
		</div>
	<?php endif; ?>

	<?php do_action( 'moderncart_slide_out_after_checkout_button' ); ?>

</div>
