<?php
/**
 * Activate
 * deactivate (no custom post types or so.. to flush rewrite rules)
 * uninstall ( delete if set )
 *
 * @package Click_To_Chat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Register' ) ) {

	/**
	 * Plugin registration and lifecycle management.
	 */
	class HT_CTC_Register {

		/**
		 * Handle plugin activation.
		 *
		 * Checks WordPress version compatibility and initializes default options.
		 *
		 * @param bool $network_wide Whether the plugin is being network-activated (multisite).
		 * @return void
		 */
		public static function activate( $network_wide = false ) {

			if ( version_compare( get_bloginfo( 'version' ), '3.1.0', '<' ) ) {
				wp_die( esc_html__( 'Please update WordPress.', 'click-to-chat-for-whatsapp' ) );
			}

			// Read before the db include below, which writes the version.
			$plugin_details = get_option( 'ht_ctc_plugin_details' );
			$is_fresh       = ! isset( $plugin_details['version'] );

			// add default values to options db
			// class-ht-ctc-db2.php - will call add ctc admin pages.
			include_once HT_CTC_PLUGIN_DIR . '/new/admin/db/class-ht-ctc-db.php';

			// Optional one-time redirect to the settings page (see activation_redirect()): runs after
			// the defaults and inside a safety net, so it can never fail the activation. Only a fresh
			// install activated on its own from the Plugins screen - not bulk, network, WP-CLI or AJAX.
			global $pagenow;
			try {
				$is_single = 'plugins.php' === $pagenow && 'activate' === HT_CTC_Utils::get_request_var( 'action' );

				if ( $is_fresh && $is_single && ! $network_wide ) {
					set_transient( 'ht_ctc_activation_redirect', get_current_user_id(), MINUTE_IN_SECONDS );
				}
			} catch ( Throwable $e ) {
				// Throwable is PHP 7+; on older PHP the catch simply never matches.
				if ( class_exists( 'HT_CTC_Utils' ) ) {
					HT_CTC_Utils::debug_log( 'activation redirect flag skipped', array( 'error' => $e->getMessage() ) );
				}
			}
		}

		/**
		 * Handle plugin version changes.
		 *
		 * Updates database schema and options when plugin version changes.
		 *
		 * @return void
		 */
		public static function version_changed() {

			// add default values to options db
			include_once HT_CTC_PLUGIN_DIR . '/new/admin/db/class-ht-ctc-db.php';
			include_once HT_CTC_PLUGIN_DIR . '/new/admin/db/class-ht-ctc-db2.php';
		}

		/**
		 * Handle plugin deactivation.
		 *
		 * Currently performs no cleanup actions.
		 *
		 * @return void
		 */
		public static function deactivate() {
		}

		/**
		 * Handle plugin uninstallation.
		 *
		 * Removes all plugin data if deletion option is enabled.
		 *
		 * @return void
		 */
		public static function uninstall() {

			$options = get_option( 'ht_ctc_othersettings' );

			if ( isset( $options['delete_options'] ) ) {

				global $wpdb;

				// $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'ht\_ctc\_%';" );
				delete_option( 'ht_ctc_chat_options' );
				delete_option( 'ht_ctc_plugin_details' );
				delete_option( 'ht_ctc_group' );
				delete_option( 'ht_ctc_one_time' );
				delete_option( 'ht_ctc_othersettings' );

				delete_option( 'ccw_options' );
				delete_option( 'ccw_options_cs' );
				delete_option( 'ht_ccw_ga' );
				delete_option( 'ht_ccw_fb' );
				delete_option( 'ht_ctc_admin_pages' );
				delete_option( 'ht_ctc_cs_options' );
				delete_option( 'ht_ctc_code_blocks' );
				delete_option( 'ht_ctc_woo_options' );
				delete_option( 'ht_ctc_admin_settings' );
				delete_option( 'ht_ctc_notices' );

				// deletes custom styles, ht_ctc_share, ht_ctc_switch
				$like_s = $wpdb->esc_like( 'ht_ctc_s' ) . '%';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct wildcard cleanup of custom style options during reset/uninstall.
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $like_s ) );

				// greetings
				$like_g = $wpdb->esc_like( 'ht_ctc_g' ) . '%';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct wildcard cleanup of greeting options during reset/uninstall.
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $like_g ) );

				// deletes page level settings - postmeta starting with ht_ctc_page*
				$like_page = $wpdb->esc_like( 'ht_ctc_page' ) . '%';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct wildcard cleanup of page-level postmeta during reset/uninstall.
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", $like_page ) );

			}

			// If these options are autoloaded, consider refreshing the options cache after bulk deletes:
			// $alloptions = wp_cache_get( 'alloptions', 'options' );
			// if ( function_exists('wp_cache_delete') ) {
			// wp_cache_delete('alloptions', 'options');
			// }

			// clear cache
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
			}
		}

		/**
		 * Check for plugin version changes.
		 *
		 * Runs on plugins_loaded to detect version updates.
		 *
		 * @return void
		 */
		public static function version_check() {

			$ht_ctc_plugin_details = get_option( 'ht_ctc_plugin_details' );

			if ( ! isset( $ht_ctc_plugin_details['version'] ) || HT_CTC_VERSION !== $ht_ctc_plugin_details['version'] ) {
				// to update the plugin - just like activate plugin
				// self::activate();
				self::version_changed();

			}
		}

		/**
		 * Open the settings page once, right after a fresh single activation.
		 *
		 * Only the Plugins screen reads the flag, and its first view consumes it. Redirects
		 * only on the ?activate=true landing, and only for the admin who activated. Any
		 * failure falls back to the normal Plugins page.
		 *
		 * @return void
		 */
		public static function activation_redirect() {
			global $pagenow;

			try {
				if ( 'plugins.php' !== $pagenow || is_network_admin() ) {
					return;
				}

				$user_id = get_transient( 'ht_ctc_activation_redirect' );
				if ( false === $user_id ) {
					return;
				}
				delete_transient( 'ht_ctc_activation_redirect' );

				if ( '' === HT_CTC_Utils::get_request_var( 'activate' ) || get_current_user_id() !== (int) $user_id || ! current_user_can( 'manage_options' ) ) {
					return;
				}

				// Something already printed output (another plugin or theme): the redirect header
				// would fail and exit would leave a blank page.
				if ( headers_sent() ) {
					return;
				}

				// Exit only when the redirect was really sent - a wp_redirect filter can cancel it.
				if ( wp_safe_redirect( admin_url( 'admin.php?page=click-to-chat' ) ) ) {
					exit;
				}
			} catch ( Throwable $e ) {
				// Throwable is PHP 7+; on older PHP the catch simply never matches.
				if ( class_exists( 'HT_CTC_Utils' ) ) {
					HT_CTC_Utils::debug_log( 'activation redirect skipped', array( 'error' => $e->getMessage() ) );
				}
			}
		}

		/**
		 * Add settings page links in plugins page - at plugin.
		 *
		 * @param array $links Plugin action links.
		 * @return array Modified links.
		 */
		public static function plugin_action_links( $links ) {
			$new_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=click-to-chat' ) . '">' . __( 'Settings', 'click-to-chat-for-whatsapp' ) . '</a>',
			);

			// WordPress forum link
			// $links['support'] = '<a target="_blank" href="https://holithemes.com/plugins/click-to-chat/support/">' . __( 'Support' , 'click-to-chat-for-whatsapp' ) . '</a>';
			$links['support'] = '<a target="_blank" href="https://wordpress.org/support/plugin/click-to-chat-for-whatsapp/#new-topic-0">' . __( 'Support', 'click-to-chat-for-whatsapp' ) . '</a>';

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$links['pro'] = '<a target="_blank" rel="noopener" href="' . esc_url( HT_CTC_Utils::pro_url( 'plugins_page' ) ) . '"><strong style="display: inline; color:#11a485;">' . __( 'PRO Version', 'click-to-chat-for-whatsapp' ) . '</strong></a>';
			}

			return array_merge( $new_links, $links );
		}
	}

} // END class_exists check
