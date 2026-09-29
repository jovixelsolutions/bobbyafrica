<?php
/**
 * WooCommerce cart override
 * Original template: woocommerce/cart/cart.php
 * WooCommerce version: 10.7.0
 * Reason: preserve WooCommerce cart logic while styling the cart in the marketplace design.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

do_action( 'woocommerce_before_cart' );
?>

<main class="marketplace-page">
	<div class="container marketplace-page-shell">
		<h1><?php esc_html_e( 'Your cart', 'bobbyafrica-marketplace-child' ); ?></h1>
		<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php do_action( 'woocommerce_before_cart_table' ); ?>
			<?php woocommerce_cart_table(); ?>
			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>

		<div class="cart-collaterals">
			<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
			<?php woocommerce_cart_totals(); ?>
			<?php do_action( 'woocommerce_after_cart_collaterals' ); ?>
		</div>
	</div>
</main>

<?php do_action( 'woocommerce_after_cart' ); ?>
