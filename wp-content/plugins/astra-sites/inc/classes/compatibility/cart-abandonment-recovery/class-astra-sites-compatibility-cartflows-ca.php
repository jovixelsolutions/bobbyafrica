<?php
/**
 * Astra Sites Compatibility for 'Cart Abandonment Recovery'.
 *
 * @package Astra Sites
 * @since 4.4.27
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Astra_Sites_Compatibility_Cartflows_CA' ) ) {

	/**
	 * Cart Abandonment Recovery Compatibility.
	 *
	 * @since 4.4.27
	 */
	class Astra_Sites_Compatibility_Cartflows_CA {

		/**
		 * Instance
		 *
		 * @access private
		 * @var self Class object.
		 *
		 * @since 4.4.27
		 */
		private static $instance = null;

		/**
		 * Constructor.
		 *
		 * @since 4.4.27
		 */
		public function __construct() {
			add_action( 'astra_sites_after_plugin_activation', array( $this, 'disable_wcar_redirection' ) );
			add_filter( 'cartflows_ca_skip_default_email_templates', array( $this, 'skip_default_email_templates' ), 10, 2 );
		}

		/**
		 * Initiator.
		 *
		 * @since 4.4.27
		 * @return self initialized object of class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Disables Woo Cart Abandonment Recovery redirection during plugin activation.
		 *
		 * @param string $plugin_init The path to the plugin file that was just activated.
		 *
		 * @since 4.4.48
		 * @return void
		 */
		public function disable_wcar_redirection( $plugin_init ) {
			if ( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' === $plugin_init ) {
				update_option( 'wcar_do_redirect', false );
				delete_transient( 'wcar_redirect_to_onboarding' );
			}
		}

		/**
		 * Skip default email templates.
		 *
		 * @param bool $skip Skip flag.
		 * @param bool $force_restore Force restore flag.
		 *
		 * @since 4.4.27
		 * @return bool
		 */
		public function skip_default_email_templates( $skip, $force_restore ) {
			// If the import has already started, skip the default email templates.
			if ( astra_sites_has_import_started() && ! $force_restore ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method.
	 */
	Astra_Sites_Compatibility_Cartflows_CA::get_instance();
}
