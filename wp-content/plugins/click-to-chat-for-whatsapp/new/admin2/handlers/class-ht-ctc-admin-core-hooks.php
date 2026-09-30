<?php
/**
 * Admin2 Core Hooks orchestrator.
 *
 * Thin orchestrator that wires the Admin2 lifecycle and boots the focused
 * sub-handlers that used to live in this file:
 *
 *  - HT_CTC_Admin_Notices       — "missing config" admin notices
 *  - HT_CTC_Admin_Upgrade_Notice — PRO upsell banner + dismiss AJAX + styles/JS
 *  - HT_CTC_Admin_Cache_Cleaner — cache plugin integrations
 *
 * Adapted from new/admin/admin_commons/class-ht-ctc-admin-hooks.php.
 * Only features relevant to Admin2 are included here:
 *  - Admin notices (number / group / share blank + PRO upsell banner).
 *  - AJAX dismiss-notice handler.
 *  - DB defaults initialization when a CTC admin page loads.
 *  - Cache clearing after settings are saved.
 *
 * Intentionally excluded (old admin only):
 *  - Sub-page load hooks for legacy page slugs (greetings, customize, etc.).
 *  - Script / style dequeue logic (Admin2 manages its own assets).
 *  - ht_ctc_ah_admin_scripts_start* actions.
 *
 * The class is instantiated directly via `new HT_CTC_Admin_Core_Hooks()`
 * during core bootstrapping inside `HT_CTC::init()`.
 *
 * @since 4.41
 * @package Click_To_Chat
 * @subpackage Admin2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Core_Hooks' ) ) {

	/**
	 * Admin Core Hooks Class.
	 */
	class HT_CTC_Admin_Core_Hooks {

		/**
		 * Constructor: wire admin lifecycle hooks and boot sub-handlers.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->admin_hooks();
			$this->boot_sub_handlers();
		}

		/**
		 * Register Admin2 lifecycle hooks.
		 *
		 * @return void
		 */
		public function admin_hooks() {

			// Only run notices and page-specific logic on relevant admin requests.
			// This avoids expensive get_option calls on every admin load (e.g., during heartbeats or unrelated plugin pages).
			// if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! isset( $_POST['action'] ) ) ) {
			// return;
			// }

			// DB defaults init when the main CTC admin page (Admin2 SPA) loads.
			add_action( 'load-toplevel_page_click-to-chat', array( $this, 'load_ctc_admin_page' ) );

			// Bump counter after settings save (used by cache plugins to detect changes).
			// i.e. ht_ctc_admin_pages update_option -> update_option_ht_ctc_admin_pages.
			add_action( 'ht_ctc_ah_admin_after_save_settings', array( $this, 'after_save_settings' ) );
		}

		/**
		 * Load and boot the focused sub-handlers.
		 *
		 * Each sub-handler owns one slice of admin behavior. They self-register
		 * their own WordPress hooks in their constructors, so booting them here
		 * is enough to wire everything up.
		 *
		 * @return void
		 */
		private function boot_sub_handlers() {
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-notices.php' );
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-upgrade-notice.php' );
			HT_CTC_Utils::load_file( 'new/admin2/handlers/class-ht-ctc-admin-cache-cleaner.php' );

			if ( class_exists( 'HT_CTC_Admin_Notices' ) ) {
				new HT_CTC_Admin_Notices();
			}
			if ( class_exists( 'HT_CTC_Admin_Upgrade_Notice' ) ) {
				new HT_CTC_Admin_Upgrade_Notice();
			}
			if ( class_exists( 'HT_CTC_Admin_Cache_Cleaner' ) ) {
				new HT_CTC_Admin_Cache_Cleaner();
			}
		}

		/**
		 * Fires when the Click to Chat Admin2 page loads.
		 *
		 * @return void
		 */
		public function load_ctc_admin_page() {
			do_action( 'ht_ctc_ah_admin_its_ctc_admin_page' );
		}

		/**
		 * After settings save, bump a counter to trigger cache clears.
		 *
		 * @return void
		 */
		public function after_save_settings() {

			$ht_ctc_admin_pages = HT_CTC_Utils::get_option( 'ht_ctc_admin_pages' );

			$count = ( isset( $ht_ctc_admin_pages['count'] ) ) ? absint( $ht_ctc_admin_pages['count'] ) : 1;
			++$count;

			$values = array(
				'count' => $count,
			);

			update_option( 'ht_ctc_admin_pages', $values );
		}
	}

} // END class_exists check
