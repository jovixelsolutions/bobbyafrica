<?php
/**
 * Admin page for configuring share invitations.
 *
 * Provides WordPress settings to control the Share feature.
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Share_Page' ) ) {

	/**
	 * Manages the Share feature settings page.
	 */
	class HT_CTC_Admin_Share_Page {

		/**
		 * Register the Share submenu.
		 *
		 * @return void
		 */
		public function menu() {

			add_submenu_page(
				'click-to-chat',
				'Share Invite',
				'Share',
				'manage_options',
				'click-to-chat-share-feature',
				array( $this, 'settings_page' )
			);
		}

		/**
		 * Render the Share settings page.
		 *
		 * @return void
		 */
		public function settings_page() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			?>

		<div class="wrap">

			<?php settings_errors(); ?>

			<div class="row">
				<div class="col s12 m12 xl8 options">
					<form action="options.php" method="post" class="">
						<?php settings_fields( 'ht_ctc_share_page_settings_fields' ); ?>
						<?php do_settings_sections( 'ht_ctc_share_page_settings_sections_do' ); ?>
						<?php submit_button(); ?>
					</form>
				</div>
				<!-- <div class="col s12 m12 xl6 ht-ctc-admin-sidebar">
				</div> -->
			</div>

		</div>

			<?php
		}


		/**
		 * Register Share settings sections and fields.
		 *
		 * @return void
		 */
		public function settings() {

			// main settings - options enable .. share, share ..
			// chat options
			register_setting( 'ht_ctc_share_page_settings_fields', 'ht_ctc_share', array( $this, 'options_sanitize' ) );

			add_settings_section( 'ht_ctc_main_page_settings_sections_add', '', array( $this, 'main_settings_section_cb' ), 'ht_ctc_share_page_settings_sections_do' );

			add_settings_field( 'share_text', __( 'Share Text', 'click-to-chat-for-whatsapp' ), array( $this, 'share_text_cb' ), 'ht_ctc_share_page_settings_sections_do', 'ht_ctc_main_page_settings_sections_add' );
			add_settings_field( 'share_cta', __( 'Call to Action', 'click-to-chat-for-whatsapp' ), array( $this, 'share_cta_cb' ), 'ht_ctc_share_page_settings_sections_do', 'ht_ctc_main_page_settings_sections_add' );
			add_settings_field( 'share_ctc_webandapi', __( 'Web WhatsApp', 'click-to-chat-for-whatsapp' ), array( $this, 'share_ctc_webandapi_cb' ), 'ht_ctc_share_page_settings_sections_do', 'ht_ctc_main_page_settings_sections_add' );

			add_settings_field( 'share_ctc_desktop', __( 'Style, Position', 'click-to-chat-for-whatsapp' ), array( $this, 'share_ctc_device_cb' ), 'ht_ctc_share_page_settings_sections_do', 'ht_ctc_main_page_settings_sections_add' );

			add_settings_field( 'share_show_hide', __( 'Show/Hide', 'click-to-chat-for-whatsapp' ), array( $this, 'share_show_hide_cb' ), 'ht_ctc_share_page_settings_sections_do', 'ht_ctc_main_page_settings_sections_add' );
			add_settings_field( 'share_shortcode', '', array( $this, 'share_shortcode_cb' ), 'ht_ctc_share_page_settings_sections_do', 'ht_ctc_main_page_settings_sections_add' );
		}

		/**
		 * Output the main settings section heading.
		 *
		 * @return void
		 */
		public function main_settings_section_cb() {
			?>
		<h1><?php esc_html_e( 'Share', 'click-to-chat-for-whatsapp' ); ?></h1>
			<?php
			do_action( 'ht_ctc_ah_admin' );
		}


		// WhatsApp share ID.
		/**
		 * Display the Share text field.
		 *
		 * @return void
		 */
		public function share_text_cb() {
			$options = get_option( 'ht_ctc_share' );
			$value   = ( isset( $options['share_text'] ) ) ? esc_attr( $options['share_text'] ) : '';
			?>
		<div class="row">
			<div class="input-field col s12">
				<input name="ht_ctc_share[share_text]" value="<?php echo esc_attr( $value ); ?>" id="whatsapp_share_text" type="text" class="input-margin">
				<label for="whatsapp_share_text"><?php esc_html_e( 'Share Text', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'Placeholder {{url}} returns current webpage URL', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/share-text/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> ) </p>
			</div>
		</div>
			<?php
		}

		// call to action
		/**
		 * Display the Call to Action field.
		 *
		 * @return void
		 */
		public function share_cta_cb() {
			$options = get_option( 'ht_ctc_share' );
			$value   = ( isset( $options['call_to_action'] ) ) ? esc_attr( $options['call_to_action'] ) : '';
			?>
		<div class="row">
			<div class="input-field col s12">
				<input name="ht_ctc_share[call_to_action]" value="<?php echo esc_attr( $value ); ?>" id="call_to_action" type="text" class="input-margin">
				<label for="call_to_action"><?php esc_html_e( 'Call to Action', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'Text that appears along with WhatsApp icon/button', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/call-to-action/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
			</div>
		</div>
			<?php
		}


		// If checked web / api whatsapp link. If unchecked wa.me links
		/**
		 * Render the Web WhatsApp toggle field.
		 *
		 * @return void
		 */
		public function share_ctc_webandapi_cb() {
			$options = get_option( 'ht_ctc_share' );

			if ( isset( $options['webandapi'] ) ) {
				?>
			<p>
				<label>
					<input name="ht_ctc_share[webandapi]" type="checkbox" value="1" <?php checked( $options['webandapi'], 1 ); ?> id="webandapi"   />
					<span><?php esc_html_e( 'Web WhatsApp on Desktop', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
				<?php
			} else {
				?>
			<p>
				<label>
					<input name="ht_ctc_share[webandapi]" type="checkbox" value="1" id="webandapi"   />
					<span><?php esc_html_e( 'Web WhatsApp on Desktop', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
				<?php
			}
			?>
		<p class="description"><?php esc_html_e( 'If checked opens Web.WhatsApp directly on Desktop and in mobile WhatsApp App', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/web-whatsapp/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
		<br>

			<?php
		}


		// device based settings - style, position
		/**
		 * Render device-specific style and position controls.
		 *
		 * @return void
		 */
		public function share_ctc_device_cb() {
			$options = get_option( 'ht_ctc_share' );
			$dbrow   = 'ht_ctc_share';
			$type    = 'share';

			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/admin-device-settings.php';
		}


		// show/hide
		/**
		 * Render the show/hide controls.
		 *
		 * @return void
		 */
		public function share_show_hide_cb() {
			$options = get_option( 'ht_ctc_share' );
			$dbrow   = 'ht_ctc_share';
			$type    = 'share';

			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/admin-show-hide.php';
		}


		/**
		 * Output the Share shortcode helper.
		 *
		 * @return void
		 */
		public function share_shortcode_cb() {
			?>
		<p class="description"><?php esc_html_e( 'Shortcodes for Share', 'click-to-chat-for-whatsapp' ); ?>: [ht-ctc-share] - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/shortcodes-share/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a></p>
			<?php
		}






		/**
		 * Sanitize each setting field as needed.
		 *
		 * @since 2.0
		 * @param array $input Contains all settings fields as array keys.
		 * @return array Sanitized settings values.
		 */
		public function options_sanitize( $input ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'not allowed to modify - please contact admin ' );
			}

			$new_input = array();

			foreach ( $input as $key => $value ) {

				if ( is_array( $input[ $key ] ) ) {
					// key: display
					if ( function_exists( 'sanitize_textarea_field' ) ) {
						$new_input[ $key ] = map_deep( $input[ $key ], 'sanitize_textarea_field' );
					} else {
						$new_input[ $key ] = map_deep( $input[ $key ], 'sanitize_text_field' );
					}
				} elseif ( 'side_1_value' === $key || 'side_2_value' === $key || 'mobile_side_1_value' === $key || 'mobile_side_2_value' === $key ) {
						$input[ $key ] = str_replace( ' ', '', $input[ $key ] );
					if ( is_numeric( $input[ $key ] ) ) {
						$input[ $key ] = $input[ $key ] . 'px';
					}
					if ( '' === $input[ $key ] ) {
						$input[ $key ] = '0px';
					}
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
				} else {
					$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
				}

				// if( isset( $input[$key] ) ) {
				// $new_input[$key] = sanitize_text_field( $input[$key] );
				// }
			}

			// l10n
			foreach ( $input as $key => $value ) {
				if ( 'share_text' === $key || 'call_to_action' === $key ) {
					do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key . '__share', $input[ $key ] );
				}
			}

			do_action( 'ht_ctc_ah_admin_after_sanitize' );

			return $new_input;
		}
	}

	$ht_ctc_admin_share_page = new HT_CTC_Admin_Share_Page();

	add_action( 'admin_menu', array( $ht_ctc_admin_share_page, 'menu' ) );
	add_action( 'admin_init', array( $ht_ctc_admin_share_page, 'settings' ) );

} // END class_exists check
