<?php
/**
 * Switcher between the new and previous user interfaces.
 *
 * New users default to the new interface. Previous users retain the legacy
 * interface unless they explicitly opt in to the new experience.
 *
 * @package ClickToChat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Switch' ) ) {

	/**
	 * Handles switching between new and legacy plugin interfaces.
	 */
	class HT_CTC_Switch {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->to_switch();
		}

		/**
		 * Define plugin-wide constants.
		 *
		 * @return void
		 */
		private function define_constants() {

			$this->define( 'HT_CTC_WP_MIN_VERSION', '4.6' );
			$this->define( 'HT_CTC_PLUGIN_DIR_URL', plugin_dir_url( HT_CTC_PLUGIN_FILE ) );
			$this->define( 'HT_CTC_PLUGIN_BASENAME', plugin_basename( HT_CTC_PLUGIN_FILE ) );
			$this->define( 'HT_CTC_BLOG_NAME', get_bloginfo( 'name' ) );
			// $this->define( 'HT_CTC_SITE_URL', get_site_url() );
			// $this->define( 'HT_CTC_HOME_URL', home_url('/') );
			// $this->define( 'HT_CTC_HOME_URL', get_bloginfo('url') );

			$this->define( 'HT_CTC_PLUGIN_SLUG', 'click-to-chat-for-whatsapp' );

			do_action( 'ht_ctc_ah_define_constants' );
		}

		/**
		 * Include required files.
		 *
		 * @return void
		 */
		private function includes() {
			// Load utils for request handling.
			if ( defined( 'HT_CTC_PLUGIN_DIR' ) ) {
				$utils_path = HT_CTC_PLUGIN_DIR . 'new/inc/utils/class-ht-ctc-utils.php';
				if ( file_exists( $utils_path ) ) {
					require_once $utils_path;
				}
			}
		}

		/**
		 * Define a constant if it does not already exist.
		 *
		 * @param string $name  Constant name.
		 * @param mixed  $value Constant value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Check if the active PRO version is compatible with the new 2026 dashboard.
		 *
		 * Compatibility:
		 * - Free version: Always compatible.
		 * - PRO version: Requires 2.21 or higher.
		 *
		 * @return boolean True if compatible, False if PRO is outdated.
		 */
		private function is_pro_compatible() {
			// 1. If PRO constant is already defined, it reflects the live active PRO
			// version - trust it directly and don't fall through to the (possibly
			// stale) DB metadata, which can lag the constant right after a PRO update.
			if ( defined( 'HT_CTC_PRO_VERSION' ) ) {
				return version_compare( HT_CTC_PRO_VERSION, '2.21', '>=' );
			}

			// 2. Fallback: If PRO is not loaded yet (e.g. Free loads first), check active plugins.
			$active_plugins = (array) get_option( 'active_plugins', array() );

			// Support for Multisite network-activated plugins.
			// if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// $network_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );
			// $active_plugins  = array_merge( $active_plugins, $network_plugins );
			// }

			$is_pro_active = false;
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, 'click-to-chat-pro.php' ) ) {
					$is_pro_active = true;
					break;
				}
			}

			// 3. If PRO is active, verify its version from the stored metadata.
			if ( $is_pro_active ) {
				$pro_details = get_option( 'ht_ctc_pro_plugin_details' );
				if ( isset( $pro_details['version'] ) ) {
					// if pro version is greater than or equal to 2.21 return true else false
					return version_compare( $pro_details['version'], '2.21', '>=' );
				}
				// If version metadata is missing, we assume incompatible to be safe.
				return false;
			}

			// 4. No PRO version detected, so it's the Free version (compatible).
			return true;
		}

		/**
		 * Determine whether to load the new or legacy interface.
		 */
		public function to_switch() {

			// Exit early stage. if utils is not loaded.
			if ( ! class_exists( 'HT_CTC_Utils' ) ) {
				return;
			}

			// Integrity check. HT_CTC_Utils is the shared helper used across boot and
			// the entire render path. A stale or partial deploy (or a stale OpCache copy)
			// can leave the class present but missing a method the plugin calls, which
			// would fatal with "call to undefined method" mid-render. If any required
			// method is absent, bail so the site renders normally without the widget.
			foreach ( array( 'get_request_var', 'get_option', 'load_file', 'load_class', 'debug_log', 'pro_url', 'doc_url' ) as $required_util_method ) {
				if ( ! method_exists( 'HT_CTC_Utils', $required_util_method ) ) {
					return;
				}
			}

			// New interface yes/no.
			$is_new = '';

			// User new/prev.
			$user = '';

				/**
				 * For first-time users or those who switched to the new interface set $is_new to yes.
				 *
				 * Users who switch back to the previous interface or upgrade from an older
				 * version will keep $is_new set to no.
				 */
			$ccw_options = get_option( 'ccw_options' );

			if ( isset( $ccw_options['number'] ) ) {
				$user   = 'prev';
				$is_new = 'no';
			} else {
				// new user - new interface
				$user   = 'new';
				$is_new = 'yes';
			}

			// Previous user and if switched.
			if ( 'prev' === $user ) {

				$ht_ctc_switch = get_option( 'ht_ctc_switch' );

				if ( isset( $ht_ctc_switch['interface'] ) && 'yes' === $ht_ctc_switch['interface'] ) {
					$is_new = 'yes';
				}
			}

			// (for testing) forces the new interface. Keep it commented -
			// if you uncomment the line below, add a 'todo(release):' so it cannot ship enabled.
			// $is_new = 'yes';

			// Define HT_CTC_IS_NEW.
			if ( ! defined( 'HT_CTC_IS_NEW' ) ) {
				define( 'HT_CTC_IS_NEW', $is_new );
			}

			// Include related files.
			if ( 'yes' === HT_CTC_IS_NEW ) {
				// New interface. 2019/2026

				$admin_settings = get_option( 'ht_ctc_admin_settings', array() );
				$admin_settings = is_array( $admin_settings ) ? $admin_settings : array();

				// Default admin UI version (2019)
				$admin_ui = isset( $admin_settings['admin_ui'] ) ? sanitize_text_field( $admin_settings['admin_ui'] ) : '2019';

				if ( ! in_array( $admin_ui, array( '2019', '2026' ), true ) ) {
					$admin_ui = '2019';
				}

				// URL-based switching (?page=click-to-chat&admin_ui=2026 or 2019).
				if ( is_admin() ) {
					$get_page = HT_CTC_Utils::get_request_var( 'page' );

					if ( false !== strpos( $get_page, 'click-to-chat' ) ) {
						$get_admin_ui = HT_CTC_Utils::get_request_var( 'admin_ui' );

						if ( in_array( $get_admin_ui, array( '2019', '2026' ), true ) ) {
							$admin_ui = $get_admin_ui;

							if ( '2026' === $get_admin_ui && ! $this->is_pro_compatible() ) {
								add_action(
									'admin_notices',
									function () {
										echo '<div class="notice notice-error is-dismissible">
											<p><strong>Click to Chat:</strong> New Admin UI requires PRO version 2.21+.</p>
										</div>';
									}
								);
							}

							// Hook to save preference safely.
							// 'admin_init' is used because user capabilities (current_user_can)
							// may not yet available this early in the plugin load sequence.
							add_action( 'admin_init', array( $this, 'save_admin_ui_preference' ) );
						}
					}
				}

				if ( '2026' === $admin_ui && ! $this->is_pro_compatible() ) {
					$admin_ui = '2019';
				}

				if ( ! defined( 'HT_CTC_ADMIN_UI' ) ) {
					define( 'HT_CTC_ADMIN_UI', $admin_ui );
				}

				// Register hooks.
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/class-ht-ctc-register.php';
				register_activation_hook( HT_CTC_PLUGIN_FILE, array( 'HT_CTC_Register', 'activate' ) );
				register_deactivation_hook( HT_CTC_PLUGIN_FILE, array( 'HT_CTC_Register', 'deactivate' ) );
				register_uninstall_hook( HT_CTC_PLUGIN_FILE, array( 'HT_CTC_Register', 'uninstall' ) );

				// Include main file - new.
				include_once HT_CTC_PLUGIN_DIR . 'new/class-ht-ctc.php';

				/**
				 * Retrieve the singleton instance of the Click to Chat core class.
				 *
				 * @return HT_CTC
				 */
				function ht_ctc() {
					return HT_CTC::instance();
				}

				// Boot only if the core class actually loaded. A corrupt or partial
					// deploy (core file missing) then degrades to "no widget" instead of a
					// fatal error on every page load.
				if ( class_exists( 'HT_CTC' ) ) {
					ht_ctc();
				}
			} else {
				// Previous interface. 2017

				// Include main file - previous.
				include_once HT_CTC_PLUGIN_DIR . 'prev/inc/class-ht-ccw.php';

				/**
				 * Retrieve the singleton instance of the legacy Click to Chat class.
				 *
				 * @return HT_CCW
				 */
				function ht_ccw() {
					return HT_CCW::instance();
				}

				// Boot only if the legacy core class actually loaded (see note above).
				if ( class_exists( 'HT_CCW' ) ) {
					ht_ccw();
				}
			}
		}

		/**
		 * Save Admin UI preference.
		 *
		 * Hooked to admin_init to ensure current_user_can works.
		 */
		public function save_admin_ui_preference() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$get_page     = HT_CTC_Utils::get_request_var( 'page' );
			$get_admin_ui = HT_CTC_Utils::get_request_var( 'admin_ui' );

			if ( '' === $get_admin_ui || false === strpos( $get_page, 'click-to-chat' ) ) {
				return;
			}

			$nonce = HT_CTC_Utils::get_request_var( '_htnonce' );

			if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'ht_ctc_switch_ui' ) ) {
				return;
			}

			$requested_ui   = $get_admin_ui;
			$admin_settings = get_option( 'ht_ctc_admin_settings', array() );
			$admin_settings = is_array( $admin_settings ) ? $admin_settings : array();

			// PRO Check again for safety
			$is_pro_compatible = $this->is_pro_compatible();

			if ( '2026' === $requested_ui ) {
				if ( $is_pro_compatible ) {
					$admin_settings['admin_ui'] = '2026';
					update_option( 'ht_ctc_admin_settings', $admin_settings );
				}
			} elseif ( '2019' === $requested_ui ) {
				$admin_settings['admin_ui'] = '2019';
				update_option( 'ht_ctc_admin_settings', $admin_settings );
			}

			/**
			 * Action-Redirect-Get (PRG) Pattern:
			 * Redirect immediately to remove 'admin_ui' and '_htnonce' from the URL.
			 * Because 'admin_init' fires before any headers or HTML output are sent,
			 * It prevents "The link you followed has expired" errors on later page refreshes.
			 */
			wp_safe_redirect( admin_url( 'admin.php?page=' . sanitize_text_field( $get_page ) ) );
			exit;
		}
	}

	new HT_CTC_Switch();

} // END class_exists check
