<?php
/**
 * Astra Sites Compatibility for 'Modern Cart'.
 *
 * @package Astra Sites
 * @since 4.4.48
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Astra_Sites_Compatibility_Modern_Cart' ) ) {

	/**
	 * Modern Cart Compatibility.
	 *
	 * @since 4.4.48
	 */
	class Astra_Sites_Compatibility_Modern_Cart {

		/**
		 * Instance
		 *
		 * @access private
		 * @var self Class object.
		 *
		 * @since 4.4.48
		 */
		private static $instance = null;

		/**
		 * Constructor.
		 *
		 * @since 4.4.48
		 */
		public function __construct() {
			add_action( 'astra_sites_after_plugin_activation', array( $this, 'disable_modern_cart_redirection' ) );
		}

		/**
		 * Initiator.
		 *
		 * @since 4.4.48
		 * @return self initialized object of class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Disables Woo Modern Cart redirection during plugin activation.
		 *
		 * @param string $plugin_init The path to the plugin file that was just activated.
		 *
		 * @since 4.4.48
		 * @return void
		 */
		public function disable_modern_cart_redirection( $plugin_init ) {
			if ( 'modern-cart/modern-cart.php' === $plugin_init ) {
				delete_transient( 'moderncart_redirect_to_onboarding' );
			}
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method.
	 */
	Astra_Sites_Compatibility_Modern_Cart::get_instance();
}
