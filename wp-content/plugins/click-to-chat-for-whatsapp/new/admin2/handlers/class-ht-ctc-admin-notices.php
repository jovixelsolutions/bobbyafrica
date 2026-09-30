<?php
/**
 * Admin2 "Missing config" notices.
 *
 * Shows an admin notice when required feature settings are blank:
 *   - WhatsApp number missing
 *   - Group ID missing (when group feature enabled)
 *   - Share text missing (when share feature enabled)
 *
 * Split out of HT_CTC_Admin_Core_Hooks so each notice family lives in one file.
 *
 * @package Click_To_Chat
 * @subpackage Admin2
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Notices' ) ) {

	/**
	 * Admin "missing config" notices.
	 */
	class HT_CTC_Admin_Notices {

		/**
		 * Constructor: register notices that match the current settings state.
		 */
		public function __construct() {
			// Admin screens only. Constructed outside is_admin() (see
			// HT_CTC_Admin_Upgrade_Notice), and register_notices() reads ht_ctc_group /
			// ht_ctc_share — absent on most installs, so a DB query each. It only ever
			// registers admin_notices callbacks, which cannot fire on a front-end view
			// or during ajax.
			if ( ! is_admin() || wp_doing_ajax() ) {
				return;
			}

			$this->register_notices();
		}

		/**
		 * Conditionally hook admin_notices based on which required fields are blank.
		 *
		 * @return void
		 */
		public function register_notices() {

			// Notice: WhatsApp number not configured.
			$ht_ctc_chat_options = HT_CTC_Utils::get_option( 'ht_ctc_chat_options' );
			if ( isset( $ht_ctc_chat_options['number'] ) && '' === $ht_ctc_chat_options['number'] ) {
				add_action( 'admin_notices', array( $this, 'ifnumberblank' ) );
			}

			$ht_ctc_othersettings = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );

			// Notice: Group ID not configured.
			if ( isset( $ht_ctc_othersettings['enable_group'] ) ) {
				$ht_ctc_group = HT_CTC_Utils::get_option( 'ht_ctc_group' );
				if ( isset( $ht_ctc_group['group_id'] ) && '' === $ht_ctc_group['group_id'] ) {
					add_action( 'admin_notices', array( $this, 'ifgroupblank' ) );
				}
			}

			// Notice: Share text not configured.
			if ( isset( $ht_ctc_othersettings['enable_share'] ) ) {
				$ht_ctc_share = HT_CTC_Utils::get_option( 'ht_ctc_share' );
				if ( isset( $ht_ctc_share['share_text'] ) && '' === $ht_ctc_share['share_text'] ) {
					add_action( 'admin_notices', array( $this, 'ifshareblank' ) );
				}
			}

			// Notice: PRO compatibility notice.
			// Note: This check is added in both class-ht-ctc-admin-hooks.php (for 2019 UI) and class-ht-ctc-admin-notices.php (for 2026 UI).
			if ( defined( 'HT_CTC_PRO_VERSION' ) && version_compare( HT_CTC_PRO_VERSION, '2.21', '<' ) ) {
				add_action( 'admin_notices', array( $this, 'show_pro_compatibility_notice' ) );
			}
		}

		/**
		 * Display admin notice if active PRO version is outdated.
		 *
		 * @return void
		 */
		public function show_pro_compatibility_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			?>
		<div class="notice notice-warning is-dismissible ht-ctc-notice">
			<p>
				<strong><?php esc_html_e( 'Click to Chat', 'click-to-chat-for-whatsapp' ); ?>:</strong>
				Please update Click to Chat PRO to v2.21 or higher.
				Your chat widget and current settings will keep working as they are &mdash; this update brings PRO in line with the new admin interface, and it will be needed for settings changes in upcoming versions.
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Update Click to Chat PRO</a>
				&nbsp;&middot;&nbsp;
				If the update is not showing, <a href="<?php echo esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/shop/download-click-to-chat-pro-compatible-version/' ) ); ?>" target="_blank" rel="noopener">download the compatible version</a>.
			</p>
		</div>
			<?php
		}

		/**
		 * Admin notice when WhatsApp number is not configured.
		 *
		 * @return void
		 */
		public function ifnumberblank() {
			?>
		<div class="notice notice-info is-dismissible ht-ctc-notice">
			<p><?php esc_html_e( 'Click to Chat is almost ready', 'click-to-chat-for-whatsapp' ); ?>. <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&tab=general-settings' ) ); ?>"><?php esc_html_e( 'Add WhatsApp Number', 'click-to-chat-for-whatsapp' ); ?></a> <?php esc_html_e( 'and let visitors chat', 'click-to-chat-for-whatsapp' ); ?>.</p>
		</div>
			<?php
		}

		/**
		 * Admin notice when Group ID is not configured.
		 *
		 * @return void
		 */
		public function ifgroupblank() {
			?>
		<div class="notice notice-info is-dismissible ht-ctc-notice">
			<p><?php esc_html_e( 'Click to Chat is almost ready', 'click-to-chat-for-whatsapp' ); ?>. <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&tab=group-settings' ) ); ?>"><?php esc_html_e( 'Add WhatsApp Group ID', 'click-to-chat-for-whatsapp' ); ?></a> <?php esc_html_e( 'to let visitors join in your WhatsApp Group', 'click-to-chat-for-whatsapp' ); ?>.</p>
		</div>
			<?php
		}

		/**
		 * Admin notice when Share Text is not configured.
		 *
		 * @return void
		 */
		public function ifshareblank() {
			?>
		<div class="notice notice-info is-dismissible ht-ctc-notice">
			<p><?php esc_html_e( 'Click to Chat is almost ready', 'click-to-chat-for-whatsapp' ); ?>. <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&tab=share-settings' ) ); ?>"><?php esc_html_e( 'Add Share Text', 'click-to-chat-for-whatsapp' ); ?></a> <?php esc_html_e( 'to let vistiors Share your Webpages', 'click-to-chat-for-whatsapp' ); ?>.</p>
		</div>
			<?php
		}
	}
} // END class_exists check
