<?php
/**
 * WooCommerce DPD baltic shipping plugin Compatibility.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Dpd_Baltic_Compatibility' ) ) :

	/**
	 * Class for DPD Baltic shipping plugin compatibility.
	 */
	class Cartflows_Dpd_Baltic_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var Object Cartflows_Dpd_Baltic_Compatibility The object of Cartflows_Dpd_Baltic_Compatibility
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
			// Action hook to handle DPD Baltic shipping options review during checkout.
			add_action( 'woocommerce_review_order_after_shipping', array( $this, 'dpd_baltic_review_order_after_shipping' ) );
		}

		/**
		 * Handles the review order after shipping process for DPD Baltic compatibility.
		 *
		 * @since 2.2.0
		 * @return void
		 */
		public function dpd_baltic_review_order_after_shipping() {
			// Check if DPD_Parcels class exists.
			if ( class_exists( 'DPD_Parcels' ) ) {
				// Create a new instance of DPD_Parcels.
				$dpd_instance = new DPD_Parcels();

				// Check if the method review_order_after_shipping exists in the DPD_Parcels instance.
				if ( method_exists( $dpd_instance, 'review_order_after_shipping' ) ) {
					// Set the global variable $is_hook_executed to false.
					global $is_hook_executed;
					$is_hook_executed = false;

					// Call the review_order_after_shipping method of the DPD_Parcels instance.
					echo '<div class="wcf-dpd-baltic-wrap">';
					$dpd_instance->review_order_after_shipping();
					echo '</div>';
				}
			}
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Dpd_Baltic_Compatibility::get_instance();

endif;
