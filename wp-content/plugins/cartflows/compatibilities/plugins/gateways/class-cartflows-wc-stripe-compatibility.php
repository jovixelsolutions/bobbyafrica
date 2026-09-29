<?php
/**
 * WooCommerce Stripe Gateway Compatibility.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Wc_Stripe_Compatibility' ) ) :

	/**
	 * Class for Beaver Builder page builder compatibility
	 */
	class Cartflows_Wc_Stripe_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var Object Cartflows_Wc_Stripe_Compatibility The Object of Cartflows_Wc_Stripe_Compatibility
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 2.2.0
		 * @return self
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 2.2.0
		 */
		public function __construct() {

			add_filter( 'cartflows_update_product_custom_price', array( $this, 'update_custom_price_for_express_checkout' ) );
		}

		/**
		 * Function to check if the current page is a checkout page and it is doing the API call.
		 *
		 * @since 2.2.0
		 * @param bool $should_update The default value for should to update the custom price or not.
		 * @return bool $should_update Should the custom price be updated.
		 */
		public function update_custom_price_for_express_checkout( $should_update ) {

			// Check if the Stripe's Express Checkout Helper Class is exists.
			if ( class_exists( 'WC_Stripe' ) && class_exists( 'WC_Stripe_Express_Checkout_Helper' ) ) {

				$stripe_gateway = WC_Stripe::get_instance()->get_main_stripe_gateway();

				// Get the object of the Express Checkout Helper class.
				$express_checkout_helper = new WC_Stripe_Express_Checkout_Helper( $stripe_gateway );

				// Check weather the express checkout feature is enabled or not.
				$express_checkout_enabled = ! empty( $express_checkout_helper ) ? $express_checkout_helper->is_express_checkout_enabled() : false;

				/**
				 * Update the custom product that is discount added on the CartFlows Checkout page for Product.
				 * If: Update the custom price if express checkout is enabled and may be doing ajax or WooCommerce API Request.
				 * Else: Return the original value as it is.
				 */
				$should_update = ! empty( $express_checkout_enabled ) && ( wp_doing_ajax() || WC()->is_rest_api_request() ) ? true : $should_update;
			}

			// Return the value.
			return $should_update;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Wc_Stripe_Compatibility::get_instance();

endif;
