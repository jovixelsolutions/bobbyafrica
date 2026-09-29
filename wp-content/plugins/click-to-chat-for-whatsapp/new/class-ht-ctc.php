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
			if ( is_null( self::$instance ) ) {
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
		 */
		public function __construct() {
			$this->basic();
			// $this->includes();
			$this->hooks();
		}

		/**
		 * Prepare core dependencies.
		 *
		 * Called before including other classes so shared state is ready.
		 * Includes and initializes collaborators required prior to init.
		 * Useful for bootstrapping device-specific or user-specific components.
		 */
		private function basic() {

			include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-ismobile.php';
			include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-values.php';
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

			$this->values      = new HT_CTC_Values();
			$this->device_type = new HT_CTC_IsMobile();

			// Stub
			// Rest api - init
			// include_once HT_CTC_PLUGIN_DIR .'new/inc/api/class-ht-ctc-rest-api.php';

			// hooks
			include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-hooks.php';
			// WooCommerce init.
			include_once HT_CTC_PLUGIN_DIR . 'new/tools/woo/ht-ctc-woo.php';

			// Is admin? Include file to admin area : include files to non-admin area.
			if ( is_admin() ) {
				// Admin main file.
				include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin.php';
			} else {
				// Front - main file - Enable - Chat, Group, Share.
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/class-ht-ctc-main.php';
				// Scripts.
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-scripts.php';
			}

			do_action( 'ht_ctc_ah_init_after' );
		}
	}

} // END class_exists check
