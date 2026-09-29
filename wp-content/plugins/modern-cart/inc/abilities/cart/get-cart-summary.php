<?php
/**
 * Get Cart Summary Ability
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities\Cart;

use ModernCart\Inc\Abilities\Abstract_Ability;
use ModernCart\Inc\Abilities\Response;
use ModernCart\Inc\Cart;
use ModernCart\Inc\Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Get_Cart_Summary
 *
 * Returns the current WooCommerce session cart state including totals and
 * free shipping progress.
 */
class Get_Cart_Summary extends Abstract_Ability {

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id           = 'moderncart/get-cart-summary';
		$this->category     = 'moderncart';
		$this->label        = __( 'Get Cart Summary', 'modern-cart' );
		$this->description  = __( 'Returns the current WooCommerce session cart state: item count, is_empty flag, cart totals (subtotal, discount, tax, shipping, grand total), applied coupons list, and free shipping bar progress (threshold, cart_total, remaining amount, percent complete, is_achieved). Most useful for verifying free shipping bar configuration — add a test product to your cart, then call this to confirm the threshold and progress values.', 'modern-cart' );
		$this->capability   = 'manage_options';
		$this->instructions = __( 'Reflects the admin user\'s session, not a customer session. If checking free shipping progress, add a product to the cart first so the progress values are populated. Requires WooCommerce to be active and a session to be initialized.', 'modern-cart' );
	}

	/**
	 * Get annotations override.
	 *
	 * Cart summary is a read-only operation — it never modifies server state.
	 * idempotentHint is true because repeated calls have no effect on state
	 * (even though the response content varies with cart contents).
	 *
	 * @return array<string, bool|float>
	 */
	public function get_annotations() {
		return array(
			'priority'        => 3.0,
			'readOnlyHint'    => true,
			'destructiveHint' => false,
			'idempotentHint'  => true,
			'openWorldHint'   => false,
		);
	}

	/**
	 * Get input schema.
	 *
	 * @return array<string, mixed>
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
			'required'   => array(),
		);
	}

	/**
	 * Execute the ability.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $args ) {
		if ( ! function_exists( 'WC' ) || null === WC()->cart ) {
			return Response::error(
				__( 'WooCommerce cart is not available. Ensure WooCommerce is active and a session is initialized.', 'modern-cart' ),
				'moderncart_cart_unavailable'
			);
		}

		$wc_cart  = WC()->cart;
		$is_empty = Helper::is_cart_empty();

		// Totals (all as raw floats for easy computation).
		$subtotal       = (float) $wc_cart->get_displayed_subtotal();
		$discount_total = (float) $wc_cart->get_discount_total();
		$shipping_total = (float) $wc_cart->get_shipping_total();
		$tax_total      = (float) $wc_cart->get_taxes_total( false, false );
		$grand_total    = (float) $wc_cart->get_total( 'number' );

		// Applied coupons.
		$applied_coupons = $wc_cart->get_applied_coupons();

		// Free shipping progress.
		$cart_instance           = Cart::get_instance();
		$free_shipping_threshold = (float) $cart_instance->get_free_shipping_amount();

		$free_shipping = array(
			'is_enabled'  => $this->get_option( 'enable_free_shipping_bar', MODERNCART_MAIN_SETTINGS, false ),
			'threshold'   => $free_shipping_threshold,
			'cart_total'  => 0.0,
			'remaining'   => 0.0,
			'percent'     => 0,
			'is_achieved' => false,
		);

		if ( $free_shipping_threshold > 0 ) {
			$cart_total_for_shipping = $subtotal - $discount_total;
			if ( $cart_total_for_shipping < 0 ) {
				$cart_total_for_shipping = 0.0;
			}

			$remaining   = max( 0, $free_shipping_threshold - $cart_total_for_shipping );
			$percent     = (int) min( 100, round( ( $cart_total_for_shipping / $free_shipping_threshold ) * 100 ) );
			$is_achieved = $cart_total_for_shipping >= $free_shipping_threshold;

			$free_shipping['cart_total']  = round( $cart_total_for_shipping, 2 );
			$free_shipping['remaining']   = round( $remaining, 2 );
			$free_shipping['percent']     = $percent;
			$free_shipping['is_achieved'] = $is_achieved;
		}

		return Response::success(
			array(
				'is_empty'        => $is_empty,
				'item_count'      => Helper::get_cart_count(),
				'totals'          => array(
					'subtotal'        => round( $subtotal, 2 ),
					'discount_total'  => round( $discount_total, 2 ),
					'shipping_total'  => round( $shipping_total, 2 ),
					'tax_total'       => round( $tax_total, 2 ),
					'grand_total'     => round( $grand_total, 2 ),
					'currency'        => get_woocommerce_currency(),
					'currency_symbol' => get_woocommerce_currency_symbol(),
				),
				'applied_coupons' => array_values( $applied_coupons ),
				'free_shipping'   => $free_shipping,
			)
		);
	}

	/**
	 * Retrieve a setting option value.
	 *
	 * @param string $option  Option key.
	 * @param string $section Option group constant.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	private function get_option( $option, $section, $default = '' ) {
		$helper  = Helper::get_instance();
		$options = $helper->get_option( $section );

		if ( is_array( $options ) && isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}
}
