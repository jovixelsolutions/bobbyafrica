<?php
/**
 * Order Tracking.
 *
 * Sets a WooCommerce session flag whenever a customer interacts with any
 * Modern Cart AJAX handler, then stamps `_moderncart_source` order meta
 * when WooCommerce creates the order. This is the foundation for North-Star
 * KPI counts (Active Store: 3+ orders / Super Store: 10+ orders in 30 days).
 *
 * @package modern-cart
 * @since   1.0.8
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Order_Tracking
 *
 * @since 1.0.8
 */
class Order_Tracking {
	use Get_Instance;

	/**
	 * WooCommerce session key used to flag Modern Cart interaction.
	 *
	 * @since 1.0.8
	 * @var string
	 */
	const SESSION_KEY = 'moderncart_checkout_source';

	/**
	 * Order meta key written when an order is attributed to Modern Cart.
	 *
	 * @since 1.0.8
	 * @var string
	 */
	const ORDER_META_KEY = '_moderncart_source';

	/**
	 * Constructor.
	 *
	 * @since 1.0.8
	 */
	public function __construct() {
		// Stamp order meta on new orders — classic shortcode checkout.
		add_action( 'woocommerce_checkout_order_created', array( $this, 'stamp_order_meta' ) );

		// Stamp order meta on new orders — WooCommerce Blocks (Store API) checkout.
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'stamp_order_meta' ) );
	}

	/**
	 * Set the WooCommerce session flag marking this session as a Modern Cart session.
	 *
	 * Called directly from each Slide_Out_Ajax handler after nonce verification,
	 * so the flag is only set for verified Modern Cart AJAX interactions.
	 *
	 * @since  1.0.8
	 * @return void
	 */
	public static function flag_session(): void {
		// @phpstan-ignore-next-line - WC() or session may be unavailable in edge cases.
		if ( WC()->session ) {
			WC()->session->set( self::SESSION_KEY, true );
		}
	}

	/**
	 * Stamp `_moderncart_source` on a newly created order.
	 *
	 * Only stamps when the WC session flag is present (meaning the customer
	 * used Modern Cart's UI during this session). Uses the HPOS-safe
	 * WC_Abstract_Order API so the meta is written to the correct storage
	 * backend regardless of whether HPOS is active.
	 *
	 * @since  1.0.8
	 *
	 * @param \WC_Order $order Newly created WooCommerce order.
	 * @return void
	 */
	public function stamp_order_meta( \WC_Order $order ): void {
		// @phpstan-ignore-next-line - WC() or session may be unavailable in edge cases.
		if ( ! WC()->session || ! WC()->session->get( self::SESSION_KEY ) ) {
			return;
		}

		$order->update_meta_data( self::ORDER_META_KEY, '1' );
		$order->save_meta_data();

		// Set flag for first_order_via_modern_cart analytics event.
		if ( ! get_option( 'mcw_first_order_tracked', false ) ) {
			update_option( 'mcw_first_order_tracked', true, false );
		}
	}
}
