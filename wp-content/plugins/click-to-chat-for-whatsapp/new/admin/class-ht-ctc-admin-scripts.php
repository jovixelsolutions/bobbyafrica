<?php
/**
 * Register css styles, javascript files at admin side
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Scripts' ) ) {

	/**
	 * Admin scripts class for Click to Chat plugin.
	 */
	class HT_CTC_Admin_Scripts {

		/**
		 * Constructor.
		 *
		 * Initializes admin script registration hooks.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Register WordPress hooks.
		 *
		 * Sets up admin script enqueuing actions.
		 *
		 * @return void
		 */
		public function hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_admin' ) );
		}

		/**
		 * Register CSS styles and JavaScript files for admin pages.
		 *
		 * Only loads assets on Click to Chat admin pages.
		 *
		 * @param string $hook The current admin page hook.
		 * @return void
		 */
		public function register_scripts_admin( $hook ) {

			// whether to load js files true in bottom or false in head.
			$load_js_bottom = apply_filters( 'ht_ctc_fh_load_admin_js_bottom', true );

			$js = 'admin.js';
			// Greetings js. greetings_template, editor related. required in greetings page, woo page.
			$greetings_js = 'greetings.js';

			$css = 'admin.css';

			// if HT_CTC_DEBUG_MODE defined by any other plugin or any logic
			if ( defined( 'HT_CTC_DEBUG_MODE' ) ) {
				$js           = 'dev/admin.dev.js';
				$greetings_js = 'dev/greetings.dev.js';

				$css = 'dev/admin.dev.css';
			}

			$rtl_css = 'admin-rtl.css';
			$md_css  = 'materialize.min.css';

			// hook ..
			if ( 'toplevel_page_click-to-chat' === $hook || 'click-to-chat_page_click-to-chat-chat-feature' === $hook || 'click-to-chat_page_click-to-chat-group-feature' === $hook || 'click-to-chat_page_click-to-chat-share-feature' === $hook || 'click-to-chat_page_click-to-chat-customize-styles' === $hook || 'click-to-chat_page_click-to-chat-other-settings' === $hook || 'click-to-chat_page_click-to-chat-woocommerce' === $hook || 'click-to-chat_page_click-to-chat-greetings' === $hook ) {

				do_action( 'ht_ctc_ah_admin_scripts_start' );

				// default dequeue in ctc woo admin page
				if ( 'click-to-chat_page_click-to-chat-woocommerce' === $hook ) {
					do_action( 'ht_ctc_ah_admin_scripts_start_woo_page' );
				}

				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'ctc_admin_md_css', plugins_url( "new/admin/admin_assets/css/$md_css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );
				wp_enqueue_style( 'ctc_admin_css', plugins_url( "new/admin/admin_assets/css/$css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );

				// intlTelInput. register and enqueue
				wp_register_style( 'ctc_admin_intl_css', plugins_url( 'new/admin/admin_assets/intl/css/intlTelInput.min.css', HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );
				wp_register_script( 'ctc_admin_intl_js', plugins_url( 'new/admin/admin_assets/intl/js/intlTelInput.min.js', HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION, $load_js_bottom );

				wp_enqueue_script( 'ctc_admin_md_js', plugins_url( 'new/admin/admin_assets/js/materialize.min.js', HT_CTC_PLUGIN_FILE ), array( 'jquery' ), HT_CTC_VERSION, $load_js_bottom );

				$ctc_admin_js_dependencies = array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'wp-color-picker', 'ctc_admin_md_js' );

				if ( 'toplevel_page_click-to-chat' === $hook ) {
					wp_enqueue_style( 'ctc_admin_intl_css' );
					wp_enqueue_script( 'ctc_admin_intl_js' );

					$ctc_admin_js_dependencies[] = 'ctc_admin_intl_js';
				}

				wp_enqueue_script( 'ctc_admin_js', plugins_url( "new/admin/admin_assets/js/$js", HT_CTC_PLUGIN_FILE ), $ctc_admin_js_dependencies, HT_CTC_VERSION, $load_js_bottom );

				wp_enqueue_script( 'ctc_admin_greetings_js', plugins_url( "new/admin/admin_assets/js/$greetings_js", HT_CTC_PLUGIN_FILE ), array( 'jquery', 'ctc_admin_js' ), HT_CTC_VERSION, $load_js_bottom );

				// rtl pages
				if ( function_exists( 'is_rtl' ) && is_rtl() ) {
					wp_enqueue_style( 'ctc_admin_rtl_css', plugins_url( "new/admin/admin_assets/css/$rtl_css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );
				}

				do_action( 'ht_ctc_ah_admin_scripts_end' );

			} else {
				return;
			}

			$this->admin_var();
		}

		/**
		 * Localize admin JavaScript variables.
		 *
		 * Provides plugin configuration data to admin scripts.
		 *
		 * @return void
		 */
		public function admin_var() {

			$utils = plugins_url( 'new/admin/admin_assets/intl/js/utils.js', HT_CTC_PLUGIN_FILE );

			$ctc = array(
				'plugin_url' => HT_CTC_PLUGIN_DIR_URL,
				'utils'      => $utils,
				'tz'         => esc_attr( get_option( 'gmt_offset' ) ),
			);

			wp_localize_script( 'ctc_admin_js', 'ht_ctc_admin_var', $ctc );
		}
	}

	new HT_CTC_Admin_Scripts();

} // END class_exists check
