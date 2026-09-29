<?php
/**
 * WooCommerce Smart Coupons compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for WooCommerce Smart Coupons compatibility
 */
class Cartflows_Smart_Coupons_Compatibility {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'handle_coupon_action_from_smart_coupons' ), 20 );
	}

	/**
	 * Handle coupon actions from Smart Coupons.
	 *
	 * Triggers Smart Coupons product addition when coupons are applied on CartFlows checkout.
	 *
	 * @param WC_Cart $cart The cart object.
	 * @return void
	 */
	public function handle_coupon_action_from_smart_coupons( $cart ) {
		if ( ! _is_wcf_checkout_type() ) {
			return;
		}
		if ( empty( $cart ) || ! $cart instanceof WC_Cart ) {
			return;
		}

		// Remove this hook to prevent infinite loop.
		remove_action( 'woocommerce_after_calculate_totals', array( $this, 'handle_coupon_action_from_smart_coupons' ), 20 );

		foreach ( $cart->get_coupons() as $code => $coupon ) {
			if ( class_exists( 'WC_SC_Coupon_Actions' ) ) {
				WC_SC_Coupon_Actions::get_instance()->coupon_action( $code );
			}
		}

		// Re-add the hook after processing.
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'handle_coupon_action_from_smart_coupons' ), 20 );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Smart_Coupons_Compatibility::get_instance();
