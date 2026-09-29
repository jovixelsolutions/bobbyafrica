<?php
/**
 * CartFlows Webhook Module Loader.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Webhook_Loader' ) ) {

	/**
	 * Class Cartflows_Webhook_Loader.
	 */
	class Cartflows_Webhook_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Initiator
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->define_constants();
			$this->load_files();
		}

		/**
		 * Define webhook module constants.
		 */
		private function define_constants() {
			define( 'CARTFLOWS_WEBHOOK_DIR', plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Load module files.
		 */
		private function load_files() {
			include_once CARTFLOWS_WEBHOOK_DIR . 'classes/class-cartflows-webhook-manager.php';
			include_once CARTFLOWS_WEBHOOK_DIR . 'classes/class-cartflows-webhook-payload.php';
			include_once CARTFLOWS_WEBHOOK_DIR . 'classes/class-cartflows-webhook-dispatcher.php';
			include_once CARTFLOWS_WEBHOOK_DIR . 'classes/class-cartflows-webhook-events.php';

			// Instantiate dispatcher so AS hook is always registered.
			Cartflows_Webhook_Dispatcher::get_instance();
		}
	}

	Cartflows_Webhook_Loader::get_instance();
}
