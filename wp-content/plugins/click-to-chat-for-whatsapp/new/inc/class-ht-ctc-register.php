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
		 * @return void
		 */
		public static function activate() {

			if ( version_compare( get_bloginfo( 'version' ), '3.1.0', '<' ) ) {
				wp_die( esc_html__( 'Please update WordPress.', 'click-to-chat-for-whatsapp' ) );
			}

			// add default values to options db
			// class-ht-ctc-db2.php - will call add ctc admin pages.
			include_once HT_CTC_PLUGIN_DIR . '/new/admin/db/class-ht-ctc-db.php';
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

				// deletes custom styles, ht_ctc_share, ht_ctc_switch
				$like_s = $wpdb->esc_like( 'ht_ctc_s' ) . '%';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $like_s ) );

				// greetings
				$like_g = $wpdb->esc_like( 'ht_ctc_g' ) . '%';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $like_g ) );

				// deletes page level settings - postmeta starting with ht_ctc_page*
				$like_page = $wpdb->esc_like( 'ht_ctc_page' ) . '%';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
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
				$links['pro'] = '<a target="_blank" rel="noreferrer noopener" href="https://holithemes.com/plugins/click-to-chat/pricing/"><strong style="display: inline; color:#11a485;">' . __( 'PRO Version', 'click-to-chat-for-whatsapp' ) . '</strong></a>';
			}

			return array_merge( $new_links, $links );
		}
	}

} // END class_exists check
