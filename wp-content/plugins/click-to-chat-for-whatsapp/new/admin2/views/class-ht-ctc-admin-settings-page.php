<?php
/**
 * Admin Main Page View
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Settings_Page' ) ) {

	/**
	 * Admin Settings Page Class
	 */
	class HT_CTC_Admin_Settings_Page {

		/**
		 * Display the main settings page.
		 */
		public static function display() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Load and Render Icons Sprite Sheet
			if ( class_exists( 'HT_CTC_Utils' ) ) {
				HT_CTC_Utils::load_file( 'new/inc/commons/class-ht-ctc-icons.php' );
			}
			if ( class_exists( 'HT_CTC_Icons' ) ) {
				HT_CTC_Icons::render_sprites();
			}

			// wrap left like to this for admin notices, ..
			?>
			<div class="wrap">
			</div>

			<div class="ctc-admin-main-page">

				<div class="ctc-admin-dashboard">

					<!-- action="options.php" method="post" -->
					<!--
						method="post" is defensive: saves go through the REST API (handleSaveClick), not
						a native form submit. If anything ever does trigger a native submit, POST keeps
						settings (WhatsApp numbers, custom CSS, etc.) out of the URL / browser history /
						server access logs. A submit-event preventDefault listener in SettingsManager.js
						provides the second layer.
					-->
					<form id="ctc-settings-form" method="post" class="">
						<?php
						// settings_fields( 'ht_ctc_main_page_settings_fields' );
						// do_settings_sections( 'ht_ctc_main_page_settings_sections_do' );
						// submit_button();
						?>

						<!-- will do it after initial release as planing to add dynamic way of identifying issues 
						may be at dashboard.php ..  <main class="main-content"> or a notification icon like in header with notification count and message.
						-->
						<div class="ctc-admin-notices"></div>

						<?php
						// Header
						HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-header.php' );
						if ( class_exists( 'HT_CTC_Admin_Header' ) && method_exists( 'HT_CTC_Admin_Header', 'display' ) ) {
							HT_CTC_Admin_Header::display();
						}
						?>

						<!-- main -->
						<div class="dashboard-content">
							<?php
							// Dashboard Panels
							HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-dashboard.php' );
							if ( class_exists( 'HT_CTC_Admin_Dashboard' ) ) {
								HT_CTC_Admin_Dashboard::display();
							}
							?>
						</div>

					</form>

				</div>

				<?php
				/*
				 * Toast notification.
				 *
				 * The dismiss button and the hover/focus pause (UIManager) exist
				 * because the toast is not always throwaway: save errors carry a
				 * copyable technical detail and PRO toasts carry an action link,
				 * and both used to slide away mid-read or mid-reach on a fixed timer.
				 */
				?>
				<div id="toast" class="toast" aria-live="polite" role="status">
					<div class="toast-content">
						<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
						<div class="toast-message">
							<span class="toast-title">Settings saved</span>
							<span class="toast-description">Your changes have been successfully saved.</span>
							<a class="toast-action" href="#" target="_blank" rel="noopener" style="display: none;"><span class="toast-action-text"></span><span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span></a>
						</div>
						<button type="button" class="toast-close" aria-label="Dismiss notification">
							<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
						</button>
					</div>
					<div class="toast-progress"></div>
				</div>

				

			</div>
			<?php
		}
	}

}
