<?php
/**
 * Admin Header View
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Header' ) ) {

	/**
	 * Admin Header Class
	 */
	class HT_CTC_Admin_Header {

		/**
		 * Display the admin header.
		 */
		public static function display() {

			$ht_ctc_admin_settings = HT_CTC_Utils::get_option( 'ht_ctc_admin_settings' );

			// Auto save status (1 or empty).
			$auto_save = isset( $ht_ctc_admin_settings['auto_save'] ) ? $ht_ctc_admin_settings['auto_save'] : '';

			// Current theme (light/dark/system)
			$theme = isset( $ht_ctc_admin_settings['theme'] ) ? $ht_ctc_admin_settings['theme'] : 'light';
			?>
			<!-- Header -->
			<header class="admin-header">

				<div class="header-left">
					<?php
					/*
					 * Icon-only control: the sprite <svg> contributes no text, so without
					 * aria-label it is announced as an unnamed "button". Interface.js keeps
					 * aria-expanded and the tooltip in sync with the sidebar it controls.
					 */
					?>
					<button type="button" id="menu-toggle" class="menu-toggle"
						aria-label="Toggle settings menu"
						aria-controls="sidebar" aria-expanded="false"
						data-tip="Collapse menu" data-tip-pos="bottom">
						<?php HT_CTC_Icons::render( 'menu', 'ctc-icon' ); ?>
					</button>
					<!-- Mobile: current section label -->
					<span id="mobile-section-label" class="mobile-section-label"></span>
					<?php
					// Clickable shortcut back to General — needs role/tabindex to be
					// reachable at all by keyboard (Interface.js binds Enter/Space).
					?>
					<div class="logo" id="logo-home" role="button" tabindex="0"
						aria-label="Go to General settings">
						<div class="logo-icon">
							<span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
						</div>
						<div class="logo-text">Click to Chat
							<?php do_action( 'ht_ctc_admin_header_logo_after' ); ?>
						</div>
					</div>
				</div>


				<div class="header-right">
					<!-- Switch to legacy admin — todo: move to settings dropdown or remove once admin2 is stable -->
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=click-to-chat&admin_ui=2019' ), 'ht_ctc_switch_ui', '_htnonce' ) ); ?>"
						class="switch-interface" data-tip="Switch to previous Admin UI" data-tip-pos="bottom">
						<?php HT_CTC_Icons::render( 'arrow-left-right', 'ctc-icon' ); ?>
						<span class="switch-text">Previous UI</span>
					</a>

					<?php
					/*
					 * Three mutually exclusive states: default, saving, and a short
					 * "Saved" confirmation. They stack in one CSS grid cell, so the
					 * button stays the width of its widest label and the header does
					 * not shift as it swaps. Which one shows is a class on the button
					 * (SettingsManager.setSaveButtonState) — no inline display styles,
					 * so CSS alone decides, including the responsive icon-only mode.
					 *
					 * SettingsManager also rewrites `title` to say whether there are
					 * unsaved changes and what the keyboard shortcut is. `title`, not
					 * the nicer data-tip bubble: the button sets `overflow: hidden` to
					 * clip its hover shine, which would clip the bubble too.
					 */
					?>
					<button type="button" id="save-button" class="save-button">
						<span class="default-state">
							<?php HT_CTC_Icons::render( 'save', 'ctc-icon' ); ?>
							<span class="text-label"><?php esc_html_e( 'Save Changes', 'click-to-chat-for-whatsapp' ); ?></span>
						</span>
						<span class="loading-state">
							<?php HT_CTC_Icons::render( 'loader-2', 'ctc-icon ctc-spin' ); ?>
							<span class="text-label">Saving...</span>
						</span>
						<span class="saved-state">
							<?php HT_CTC_Icons::render( 'check', 'ctc-icon' ); ?>
							<span class="text-label">Saved</span>
						</span>
					</button>
					<!-- Settings Dropdown Button -->
					<!-- todo: name attr.. save .. in db.... -->
					<!-- check.. if better way..  -->
					<div class="settings-dropdown-wrapper">
						<button type="button" id="settings-toggle" class="settings-toggle" aria-label="<?php esc_attr_e( 'Settings', 'click-to-chat-for-whatsapp' ); ?>"
							aria-haspopup="true" aria-expanded="false" aria-controls="settings-dropdown"
							data-tip="Theme &amp; auto-save" data-tip-pos="bottom" data-tip-align="end">
							<?php HT_CTC_Icons::render( 'settings', 'ctc-icon' ); ?>
						</button>
						<div id="settings-dropdown" class="settings-dropdown hidden">
							<div class="dropdown-item theme-selector">
								<fieldset class="theme-fieldset" role="radiogroup" aria-label="Theme Selection">
									<legend class="dropdown-section-title">Theme</legend>
									<div class="theme-options">
										<label class="theme-option">
											<input type="radio" name="ht_ctc_admin_settings[theme]" value="light" <?php checked( $theme, 'light' ); ?> />
											<span class="theme-icon">
												<?php HT_CTC_Icons::render( 'sun', 'ctc-icon' ); ?>
												<!-- <span class="theme-label">Light</span> -->
											</span>
										</label>
										<label class="theme-option">
											<input type="radio" name="ht_ctc_admin_settings[theme]" value="dark" <?php checked( $theme, 'dark' ); ?> />
											<span class="theme-icon">
												<?php HT_CTC_Icons::render( 'moon', 'ctc-icon' ); ?>
												<!-- <span class="theme-label">Dark</span> -->
											</span>
										</label>
										<label class="theme-option">
											<input type="radio" name="ht_ctc_admin_settings[theme]" value="system" <?php checked( $theme, 'system' ); ?> />
											<span class="theme-icon">
												<?php HT_CTC_Icons::render( 'monitor', 'ctc-icon' ); ?>
												<!-- <span class="theme-label">Auto</span> -->
											</span>
										</label>
									</div>
								</fieldset>
							</div>
							<label class="dropdown-item">
								<input name="ht_ctc_admin_settings[auto_save]" <?php checked( $auto_save, '1' ); ?>  value="1" type="checkbox" id="auto-save-toggle"/>
								<span>Auto Save</span>
							</label>
						</div>
					</div>
				</div>

			</header>
			<?php
		}
	}
}
