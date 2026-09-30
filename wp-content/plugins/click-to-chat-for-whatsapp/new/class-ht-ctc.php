<?php
/**
 * New interface starter.
 *
 * Include files for admin and front-end contexts.
 * Add hooks.
 *
 * @package Click_To_Chat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC' ) ) {

	/**
	 * Core Click to Chat bootstrapper.
	 *
	 * Sets up shared dependencies and registers hooks.
	 */
	class HT_CTC {

		/**
		 * Singleton instance.
		 *
		 * @var HT_CTC
		 */
		private static $instance = null;

		/**
		 * Device type helper.
		 *
		 * @var HT_CTC_IsMobile Mobile, tablet, or desktop detection utility.
		 */
		public $device_type;

		/**
		 * Instance of HT_CTC_Values.
		 *
		 * Database values, plugin options, and defaults.
		 *
		 * @var HT_CTC_Values
		 */
		public $values = null;

		/**
		 * Main instance accessor.
		 *
		 * @return HT_CTC Instance.
		 * @since 1.0
		 */
		public static function instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Block cloning of the singleton instance.
		 */
		public function __clone() {
			_doing_it_wrong(
				__FUNCTION__,
				esc_html__( 'Cheatin&#8217; huh?', 'click-to-chat-for-whatsapp' ),
				'1.0'
			);
		}

		/**
		 * Block unserializing of the singleton instance.
		 */
		public function __wakeup() {
			_doing_it_wrong(
				__FUNCTION__,
				esc_html__( 'Cheatin&#8217; huh?', 'click-to-chat-for-whatsapp' ),
				'1.0'
			);
		}

		/**
		 * Constructor.
		 *
		 * Perform basic setup, include dependencies, and register hooks.
		 * Protected to enforce singleton usage.
		 */
		protected function __construct() {
			$this->includes();

			// Foundational guard. HT_CTC_Utils is the shared helper used across the
			// entire render path (chat, group, share, greetings, styles, scripts).
			// If it failed to load (corrupt / partial deploy), abort all wiring so the
			// site renders normally without the widget instead of throwing a fatal.
			if ( ! class_exists( 'HT_CTC_Utils' ) ) {
				return;
			}

			$this->basic();
			// $this->includes();
			$this->hooks();
		}

		/**
		 * Load core files.
		 */
		private function includes() {
			// Load Admin Utils
			if ( file_exists( HT_CTC_PLUGIN_DIR . 'new/inc/utils/class-ht-ctc-utils.php' ) ) {
				require_once HT_CTC_PLUGIN_DIR . 'new/inc/utils/class-ht-ctc-utils.php';
			}
		}

		/**
		 * Prepare core dependencies.
		 *
		 * Called before including other classes so shared state is ready.
		 * Includes and initializes collaborators required prior to init.
		 * Useful for bootstrapping device-specific or user-specific components.
		 */
		private function basic() {
			// Safe loader: silently skips a missing/unreadable file (no PHP warning,
			// no fatal). Consumers are gated on class_exists before use.
			HT_CTC_Utils::load_file( 'new/inc/commons/class-ht-ctc-ismobile.php' );
			HT_CTC_Utils::load_file( 'new/inc/commons/class-ht-ctc-values.php' );
		}

		/**
		 * Register plugin hooks.
		 *
		 * Handles lifecycle events and sets up runtime integrations.
		 * The commented deactivation and uninstall hooks are not needed currently.
		 *
		 * @note The `plugins_loaded` hook checks version differences when the plugin updates.
		 */
		private function hooks() {

			// Init.
			add_action( 'init', array( $this, 'init' ), 0 );

			// Enable shortcodes in widget area.
			add_filter( 'widget_text', 'do_shortcode' );

			// Settings page link.
			add_filter( 'plugin_action_links_' . HT_CTC_PLUGIN_BASENAME, array( 'HT_CTC_Register', 'plugin_action_links' ) );

			// Fresh activation - open the settings page once.
			add_action( 'admin_init', array( 'HT_CTC_Register', 'activation_redirect' ) );

			// When plugin updated - check version diff.
			add_action( 'plugins_loaded', array( 'HT_CTC_Register', 'version_check' ) );
		}

		/**
		 * Initialize runtime components.
		 *
		 * Includes required files and wires dependencies after WordPress is ready.
		 * Call `basic()` first if a component needs to run before init.
		 *
		 * @uses HT_CTC::hooks() Uses the init hook with priority 0.
		 */
		public function init() {

			do_action( 'ht_ctc_ah_init_before' );

			// Core runtime dependencies. Without them the widget cannot render and
			// every consumer (`ht_ctc()->values`, device detection) would fatal, so
			// bail gracefully — the site stays intact, only the widget is absent.
			if ( ! class_exists( 'HT_CTC_Values' ) || ! class_exists( 'HT_CTC_IsMobile' ) ) {
				return;
			}

			$this->values      = new HT_CTC_Values();
			$this->device_type = new HT_CTC_IsMobile();

			if ( defined( 'HT_CTC_ADMIN_UI' ) && '2026' === HT_CTC_ADMIN_UI ) {
				// Initialize Core Hooks once for both Admin and API
				HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-core-hooks.php' );
				if ( class_exists( 'HT_CTC_Admin_Core_Hooks' ) ) {
					new HT_CTC_Admin_Core_Hooks();
				}

				// REST API - init - handles both public and admin endpoints
				HT_CTC_Utils::load_file( 'new/inc/api/class-ht-ctc-rest-api.php' );
				if ( class_exists( 'HT_CTC_Rest_API' ) ) {
					new HT_CTC_Rest_API();
				}
			}

			// hooks
			HT_CTC_Utils::load_file( 'new/inc/commons/class-ht-ctc-hooks.php' );
			// WooCommerce init.
			HT_CTC_Utils::load_file( 'new/tools/woo/ht-ctc-woo.php' );
			// Phone field (intl-tel-input) locator. Defines nothing but constants and
			// static methods — no hooks, no assets — so it is safe to load always.
			// PRO reads it on the front end; both admin UIs read it in wp-admin.
			HT_CTC_Utils::load_file( 'new/tools/phone-field/class-ht-ctc-phone-field.php' );

			// Is admin? Include file to admin area : include files to non-admin area.
			if ( is_admin() ) {
				// Admin main file.
				$this->load_admin();
			} else {
				// Front - main file - Enable - Chat, Group, Share.
				HT_CTC_Utils::load_file( 'new/inc/class-ht-ctc-main.php' );
				// Scripts.
				HT_CTC_Utils::load_file( 'new/inc/commons/class-ht-ctc-scripts.php' );
			}

			do_action( 'ht_ctc_ah_init_after' );
		}



		/**
		 * Load admin interface based on compatibility and settings.
		 */
		private function load_admin() {

			if ( defined( 'HT_CTC_ADMIN_UI' ) && '2026' === HT_CTC_ADMIN_UI ) {
				HT_CTC_Utils::load_file( 'new/admin2/class-ht-ctc-admin.php' );
				if ( class_exists( 'HT_CTC_Admin' ) ) {
					HT_CTC_Admin::instance();
				}
			} else {
				HT_CTC_Utils::load_file( 'new/admin/admin.php' );
			}
		}
	}

} // END class_exists check
