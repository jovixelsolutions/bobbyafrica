<?php
/**
 * Starting point for the admin side of this plugin. 2026 interface.
 *
 * Include other file here .. which need in admin side.
 *
 *  In click-to-chat.php this file will be loaded as is_admin
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin' ) ) {

	/**
	 * Admin class for Click to Chat plugin.
	 */
	class HT_CTC_Admin {

		/**
		 * Static instance to prevent multiple initializations.
		 *
		 * @var HT_CTC_Admin|null
		 */
		private static $instance = null;

		/**
		 * Main instance accessor.
		 *
		 * @return HT_CTC_Admin
		 */
		public static function instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 * Protected to enforce singleton usage.
		 */
		protected function __construct() {
			$this->includes();
			$this->init_classes();
			// $this->hooks();
		}

		/**
		 * Include required files
		 */
		public function includes() {

			// formatting - wa_number, is_page_builder_editor
			HT_CTC_Utils::load_file( 'new/inc/commons/class-ht-ctc-formatting.php' );

			// admin hooks: notices, cache clear, DB init, AJAX dismiss.
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-core-hooks.php' );

			// admin scripts
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-page-scripts.php' );

			// admin menu
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-menu.php' );

			// page level settings meta box (admin2 copy; self-instantiates)
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-metabox.php' );

			do_action( 'ht_ctc_ah_admin_includes' );
		}

		/**
		 * Initialize admin classes.
		 */
		public function init_classes() {
			if ( class_exists( 'HT_CTC_Admin_Menu' ) ) {
				new HT_CTC_Admin_Menu();
			}
			if ( class_exists( 'HT_CTC_Admin_Page_Scripts' ) ) {
				new HT_CTC_Admin_Page_Scripts();
			}
			do_action( 'ht_ctc_ah_admin_init_classes' );
		}

		/**
		 * Initialize hooks
		 */
		// public function hooks() {
		// init
		// add_action( 'admin_init', array( $this, 'init' ) );
		// }

		/**
		 * Init
		 *
		 * Todo: add init function to load all the required files
		 */
		// public function init() {

		// $ht_ctc_othersettings = get_option( 'ht_ctc_othersettings' );
		// }
	}

} // END class_exists check
