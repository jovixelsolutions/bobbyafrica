<?php
/**
 * Modern Cart Woo Price
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="moderncart-cart-item-actions--pricing" role="region" aria-label="<?php esc_attr_e( 'Product Price', 'modern-cart' ); ?>">
	<div class="moderncart-price">
		<span class="screen-reader-text"><?php esc_html_e( 'Price:', 'modern-cart' ); ?></span>
		<?php echo wp_kses_post( moderncart_cart_item_price( $product, $cart_item, $cart_item_key ) ); ?>
	</div>
</div>
