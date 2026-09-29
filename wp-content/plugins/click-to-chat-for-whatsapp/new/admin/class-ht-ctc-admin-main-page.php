<?php
/**
 * Main settings page - admin.
 *
 * This main settings page contains configuration for the Click to Chat plugin.
 *
 * Enable options such as chat defaults, group, share, and WooCommerce settings.
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Main_Page' ) ) {

	/**
	 * Handles rendering and registration for the main admin settings page.
	 */
	class HT_CTC_Admin_Main_Page {

		/**
		 * Register the main Click to Chat admin menu.
		 *
		 * @return void
		 */
		public function menu() {

			$icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iI2ZmZiIgY2xhc3M9ImJpIGJpLXdoYXRzYXBwIiB2aWV3Qm94PSIwIDAgMTYgMTYiPjxwYXRoIGQ9Ik0xMy42MDEgMi4zMjZBNy44NTQgNy44NTQgMCAwIDAgNy45OTQgMEMzLjYyNyAwIC4wNjggMy41NTguMDY0IDcuOTI2YzAgMS4zOTkuMzY2IDIuNzYgMS4wNTcgMy45NjVMMCAxNmw0LjIwNC0xLjEwMmE3LjkzMyA3LjkzMyAwIDAgMCAzLjc5Ljk2NWguMDA0YzQuMzY4IDAgNy45MjYtMy41NTggNy45My03LjkzQTcuODk4IDcuODk4IDAgMCAwIDEzLjYgMi4zMjZ6TTcuOTk0IDE0LjUyMWE2LjU3MyA2LjU3MyAwIDAgMS0zLjM1Ni0uOTJsLS4yNC0uMTQ0LTIuNDk0LjY1NC42NjYtMi40MzMtLjE1Ni0uMjUxYTYuNTYgNi41NiAwIDAgMS0xLjAwNy0zLjUwNWMwLTMuNjI2IDIuOTU3LTYuNTg0IDYuNTkxLTYuNTg0YTYuNTYgNi41NiAwIDAgMSA0LjY2IDEuOTMxIDYuNTU3IDYuNTU3IDAgMCAxIDEuOTI4IDQuNjZjLS4wMDQgMy42MzktMi45NjEgNi41OTItNi41OTIgNi41OTJ6bTMuNjE1LTQuOTM0Yy0uMTk3LS4wOTktMS4xNy0uNTc4LTEuMzUzLS42NDYtLjE4Mi0uMDY1LS4zMTUtLjA5OS0uNDQ1LjA5OS0uMTMzLjE5Ny0uNTEzLjY0Ni0uNjI3Ljc3NS0uMTE0LjEzMy0uMjMyLjE0OC0uNDMuMDUtLjE5Ny0uMS0uODM2LS4zMDgtMS41OTItLjk4NS0uNTktLjUyNS0uOTg1LTEuMTc1LTEuMTAzLTEuMzcyLS4xMTQtLjE5OC0uMDExLS4zMDQuMDg4LS40MDMuMDg3LS4wODguMTk3LS4yMzIuMjk2LS4zNDYuMS0uMTE0LjEzMy0uMTk4LjE5OC0uMzMuMDY1LS4xMzQuMDM0LS4yNDgtLjAxNS0uMzQ3LS4wNS0uMDk5LS40NDUtMS4wNzYtLjYxMi0xLjQ3LS4xNi0uMzg5LS4zMjMtLjMzNS0uNDQ1LS4zNC0uMTE0LS4wMDctLjI0Ny0uMDA3LS4zOC0uMDA3YS43MjkuNzI5IDAgMCAwLS41MjkuMjQ3Yy0uMTgyLjE5OC0uNjkxLjY3Ny0uNjkxIDEuNjU0IDAgLjk3Ny43MSAxLjkxNi44MSAyLjA0OS4wOTguMTMzIDEuMzk0IDIuMTMyIDMuMzgzIDIuOTkyLjQ3LjIwNS44NC4zMjYgMS4xMjkuNDE4LjQ3NS4xNTIuOTA0LjEyOSAxLjI0Ni4wOC4zOC0uMDU4IDEuMTcxLS40OCAxLjMzOC0uOTQzLjE2NC0uNDY0LjE2NC0uODYuMTE0LS45NDMtLjA0OS0uMDg0LS4xODItLjEzMy0uMzgtLjIzMnoiLz48L3N2Zz4=';

			add_menu_page(
				'Click to Chat ',
				'Click to Chat',
				'manage_options',
				'click-to-chat',
				array( $this, 'settings_page' ),
				$icon
			);
		}

		/**
		 * Render the main settings page markup.
		 *
		 * @return void
		 */
		public function settings_page() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			?>

		<div class="wrap ctc-admin-main-page">

			<?php settings_errors(); ?>

			<!-- full row -->
			<div class="row" style="display:flex; flex-wrap:wrap;" >

				<div class="col s12 m12 xl8 options">
					<form action="options.php" method="post" class="ht-ctc-admin-main-page-form">
						<?php settings_fields( 'ht_ctc_main_page_settings_fields' ); ?>
						<?php do_settings_sections( 'ht_ctc_main_page_settings_sections_do' ); ?>
						<?php submit_button(); ?>
					</form>
				</div>

				<!-- sidebar content -->
				<div class="col s12 m9 l7 xl4 ht-ctc-admin-sidebar sticky-sidebar ctc_scrollbar">
					<div class="ctc_scrollbar_2">
						<?php include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/admin-sidebar-content.php'; ?>
					</div>
				</div>
				
			</div>

			<!-- new row - After settings page  -->
			<div class="row">
			</div>

		</div>

			<?php
		}


		/**
		 * Register settings, sections and fields for the main page.
		 *
		 * @return void
		 */
		public function settings() {

			// chat feautes
			register_setting( 'ht_ctc_main_page_settings_fields', 'ht_ctc_chat_options', array( $this, 'options_sanitize' ) );

			add_settings_section( 'ht_ctc_chat_page_settings_sections_add', '', array( $this, 'chat_settings_section_cb' ), 'ht_ctc_main_page_settings_sections_do' );

			add_settings_field( 'number', __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ), array( $this, 'number_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );
			add_settings_field( 'prefilled', __( 'Pre-Filled Message', 'click-to-chat-for-whatsapp' ), array( $this, 'prefilled_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );
			add_settings_field( 'cta', __( 'Call to Action', 'click-to-chat-for-whatsapp' ), array( $this, 'cta_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );
			add_settings_field( 'ctc_desktop', __( 'Style, Position', 'click-to-chat-for-whatsapp' ), array( $this, 'ctc_device_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );
			// @since 3.23 URL Structure field moved to ctc main settings from other settings
			add_settings_field( 'ctc_url_strucutre', __( 'URL Structure', 'click-to-chat-for-whatsapp' ), array( $this, 'ctc_url_strucutre_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );
			add_settings_field( 'ctc_show_hide', __( 'Display Settings', 'click-to-chat-for-whatsapp' ), array( $this, 'ctc_show_hide_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );

			add_settings_field( 'options', '', array( $this, 'options_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );

			add_settings_field( 'ctc_notes', '', array( $this, 'ctc_notes_cb' ), 'ht_ctc_main_page_settings_sections_do', 'ht_ctc_chat_page_settings_sections_add' );
		}


		/**
		 * Output section header for chat settings.
		 *
		 * @return void
		 */
		public function chat_settings_section_cb() {
			?>
		<h1 id="chat_settings">Click to Chat – Settings </h1>
		
			<?php
			do_action( 'ht_ctc_ah_admin' );
		}


		/**
		 * Render WhatsApp number input field.
		 *
		 * @since 3.2.7 - $cc, $num - updated user interface
		 * @return void
		 */
		public function number_cb() {
			$options = get_option( 'ht_ctc_chat_options' );
			$os      = get_option( 'ht_ctc_othersettings' );
			$cc      = ( isset( $options['cc'] ) ) ? esc_attr( $options['cc'] ) : '';
			$num     = ( isset( $options['num'] ) ) ? esc_attr( $options['num'] ) : '';
			$number  = ( isset( $options['number'] ) ) ? esc_attr( $options['number'] ) : '';

			if ( '' === $num && '' === $cc ) {
				$num = $number;
			}

			// this if is safe side check only to prevent admin side issue if file not found.. not required.
			if ( class_exists( 'HT_CTC_Formatting' ) && method_exists( 'HT_CTC_Formatting', 'wa_number' ) ) {
				$number = HT_CTC_Formatting::wa_number( $number );
			}

			/**
			 * 1: no intl-tel-input
			 *      if number set and not intl
			 *
			 * 2: intl-tel-input
			 *      if number blank or isset intl(i.e. number set by intl input)
			 */
			$intl = '1';

			if ( isset( $options['intl'] ) || '' === $number ) {
				$intl = '2';
			}

			// if no-intl is enabled then load 1
			if ( isset( $os['no-intl'] ) ) {
				$intl = '1';
			}

			// if _get have number-field 1 then load 1 else if 2 then load 2 ( &number-field=1 )
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of 'number-field', no action or data modification.
			$get_number_field = ( isset( $_GET ) && isset( $_GET['number-field'] ) ) ? sanitize_text_field( wp_unslash( $_GET['number-field'] ) ) : '';

			// output : if number-field is set in url its throughing "1"
			if ( '1' === $get_number_field ) {
				$intl = '1';
			} elseif ( '2' === $get_number_field ) {
				$intl = '2';
			}

			?>

		<style>
		.ctc_num_field {
			padding-left: 0px !important;
		}
		.ctc_num_field input {
			border: 1px solid #9e9e9e !important;
			padding-left: 15px !important;
		}
		.ctc_num_field input#whatsapp_cc {
			border-right: none !important;
		}
		</style>

			<?php
			if ( '2' === $intl ) {
				/**
				 * Interface 2: intl-tel-input.
				 *
				 * The $intl option controls whether the international phone input displays.
				 * Value 2 enables the intl-tel-input version of the field.
				 * Value 1 keeps the plain input field.
				 */
				if ( '' !== $number && substr( $number, 0, 1 ) !== '+' ) {
					$number = "+$number";
				}
				?>
		<div class="row row_number" id="row_number">
			<div class="col s12">
				<input type="text" name="ht_ctc_chat_options[number]" data-name="ht_ctc_chat_options[number]" class="intl_number browser-default main_wa_number" value="<?php echo esc_attr( $number ); ?>">
				<input name="ht_ctc_chat_options[intl]" style="display: none;" value="1" type="hidden">
				<p class="description"><?php esc_html_e( 'WhatsApp or WhatsApp business number', 'click-to-chat-for-whatsapp' ); ?></p>
				<?php
				// display plain input number filed link.. if number filed is null/blank.
				// - ..
				$ht_ctc_admin_pages = get_option( 'ht_ctc_admin_pages' );
				$save_count         = ( isset( $ht_ctc_admin_pages['count'] ) ) ? $ht_ctc_admin_pages['count'] : 0;

				// if number is empty OR the 'number' option is not set.
				if ( '' === $number || ! isset( $options['number'] ) ) {
					// if save_count is greater than 5 OR again the 'number' option is missing.
					if ( $save_count > 5 || ! isset( $options['number'] ) ) {
						?>
					<p class="description">If WhatsApp number is not saving? load plain <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&number-field=1' ) ); ?>">input field</a></p>
						<?php
					}
				}
				?>

			</div>
		</div>


		<div class="intl_error" style="display:none;">
			<p class="description ht_ctc_error_message">If the WhatsApp number field is not working, <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&number-field=1' ) ); ?>">click here</a> to load the plain input field instead of the INTL library.</p>
		</div>
				<?php
			} else {
				/**
				 * Interface 1: plain (no intl-tel-input).
				 *
				 * Option ht_ctc_chat_options[cc]: Country code. id: whatsapp_cc.
				 * Option ht_ctc_chat_options[num]: Number (without country code). id: whatsapp_number.
				 * Option ht_ctc_chat_options[number]: Full number [cc + num]; updated by JS. id: ctc_whatsapp_number.
				 */
				?>
			<!-- Full WhatsApp Number Card -->
				<div class="row" id="row_number">
					<div class="col s12 m8">
						<p class="description card-panel grey lighten-3" style="padding: 5px 24px; display: inline-block;"><?php esc_html_e( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ); ?>: <span class="ht_ctc_wn"><?php echo esc_html( $number ); ?></span> </p>
					</div>
				</div>

				<div class="row">
					<div class="col s12">

						<!-- country code -->
						<div class="input-field col s3 m3 ctc_num_field">
							<input name="ht_ctc_chat_options[cc]" value="<?php echo esc_attr( $cc ); ?>" id="whatsapp_cc" type="text" placeholder="+1 " class="input-margin tooltipped ctc_no_demo" data-position="left" data-tooltip="Country Code">
							<label for="whatsapp_cc"><?php esc_html_e( 'Country Code', 'click-to-chat-for-whatsapp' ); ?></label>
						</div>

						<!-- number -->
						<div class="input-field col s9 m7 ctc_num_field">
							<input name="ht_ctc_chat_options[num]" value="<?php echo esc_attr( $num ); ?>" id="whatsapp_number" placeholder="23456789" type="text" class="input-margin tooltipped ctc_no_demo" data-position="right" data-tooltip="Number">
							<label for="whatsapp_number"><?php esc_html_e( 'Number', 'click-to-chat-for-whatsapp' ); ?></label>
							<span class="helper-text ctc_wn_initial_zero" style="display: none;">zero may not needed to add before the number</span>
						</div>

						<!-- full number - hidden field -->
						<input name="ht_ctc_chat_options[number]" style="display: none;" hidden value="<?php echo esc_attr( $number ); ?>" id="ctc_whatsapp_number" type="text">

					</div>

					<p class="description"><?php esc_html_e( 'WhatsApp or WhatsApp business number with ', 'click-to-chat-for-whatsapp' ); ?> <a target="_blank" href="https://holithemes.com/blog/country-codes/"><?php esc_html_e( 'country code', 'click-to-chat-for-whatsapp' ); ?></a> </p>
					<p class="description"><?php esc_html_e( '( E.g. 916123456789 - herein e.g. 91 is country code, 6123456789 is the mobile number )', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/whatsapp-number/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>

					<p class="description">Display WhatsApp number input field using: <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&number-field=2' ) ); ?>">Intl input library</a></p>
					

				</div>

				<?php
			}

			do_action( 'ht_ctc_ah_admin_chat_number' );

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				?>
			<p class="description greetings_links">Greetings dialog(message window) at <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-greetings' ) ); ?>" target="_blank">Greetings</a> page</p>
			<p class="description greetings_links">PRO: <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/multi-agent/">Multi Agent</a> | <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/docs/random-number/">Random Number</a></p>
				<?php
			}
		}

			/**
			 * Render the pre-filled message field.
			 *
			 * @return void
			 */
		public function prefilled_cb() {
			$options     = get_option( 'ht_ctc_chat_options' );
			$value       = ( isset( $options['pre_filled'] ) ) ? esc_attr( $options['pre_filled'] ) : '';
			$blogname    = HT_CTC_BLOG_NAME;
			$placeholder = "Hello {site} \nLike to know more information about {title}, {url}";
			?>
		<div class="row">
			<div class="input-field col s12">
				<textarea style="min-height: 64px;" placeholder="<?php echo esc_attr( $placeholder ); ?>" name="ht_ctc_chat_options[pre_filled]" id="pre_filled" data-var="pre_filled" class="materialize-textarea input-margin ctc_ad_main_page_on_change_input_update_var"><?php echo esc_textarea( $value ); ?></textarea>
				<label for="pre_filled"><?php esc_html_e( 'Pre-filled message', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'Text that is pre-filled in WhatsApp Chat window. Add variables {site}, {title}, {url}, [url] to replace with the site name, post title, current webpage URL and full URL including query parameters', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/docs/pre-filled-message/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
			</div>
		</div>
			<?php
		}

			/**
			 * Render the call to action field.
			 *
			 * @return void
			 */
		public function cta_cb() {
			$options = get_option( 'ht_ctc_chat_options' );
			$value   = ( isset( $options['call_to_action'] ) ) ? esc_attr( $options['call_to_action'] ) : '';
			?>
		<div class="row" id="row_call_to_action">
			<div class="input-field col s12">
				<input name="ht_ctc_chat_options[call_to_action]" value="<?php echo esc_attr( $value ); ?>" id="call_to_action" type="text" class="input-margin call_to_action ctc_ad_main_page_on_change_input">
				<label for="call_to_action"><?php esc_html_e( 'Call to Action', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'Text that appears along with WhatsApp icon/button', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/call-to-action/">more info</a> </p>
			<?php
			if ( class_exists( 'WooCommerce' ) ) {
				$woo_link = admin_url( 'admin.php?page=click-to-chat-woocommerce' );
				?>
				<p class= "description">To Change Pre-filled Message, Call to action for WooCommerce Single Product Pages <a target="_blank" href="<?php echo esc_url( $woo_link ); ?>">( Click to Chat -> WooCommerce )</a></p>
				<?php
			}
			?>
			</p>
			</div>
		</div>
			<?php
		}


			/**
			 * Render device-based style and position settings.
			 *
			 * @return void
			 */
		public function ctc_device_cb() {
			$options = get_option( 'ht_ctc_chat_options' );
			$dbrow   = 'ht_ctc_chat_options';
			$type    = 'chat';

			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/admin-device-settings.php';
		}

			/**
			 * URL structure configuration.
			 *
			 * @since 3.23 (moved from other settings to main settings)
			 * Initially started as web WhatsApp here. From version 3.12 moved to other settings as URL structure and now again moved to main settings.
			 * @return void
			 */
		public function ctc_url_strucutre_cb() {
			$options = get_option( 'ht_ctc_chat_options' );
			$dbrow   = 'ht_ctc_chat_options';
			$type    = 'chat';

			// url structure
			$url_target_d    = ( isset( $options['url_target_d'] ) ) ? esc_attr( $options['url_target_d'] ) : '_blank';
			$url_structure_d = ( isset( $options['url_structure_d'] ) ) ? esc_attr( $options['url_structure_d'] ) : '';
			$url_structure_m = ( isset( $options['url_structure_m'] ) ) ? esc_attr( $options['url_structure_m'] ) : '';
			$custom_url_d    = ( isset( $options['custom_url_d'] ) ) ? esc_attr( $options['custom_url_d'] ) : '';
			$custom_url_m    = ( isset( $options['custom_url_m'] ) ) ? esc_attr( $options['custom_url_m'] ) : '';

			$url_structure_d_list = array(
				'default'    => '(' . __( 'Default', 'click-to-chat-for-whatsapp' ) . ') wa.me',
				'web'        => __( 'Web WhatsApp', 'click-to-chat-for-whatsapp' ),
				'custom_url' => __( 'Custom URL', 'click-to-chat-for-whatsapp' ),
			);

			$url_structure_m_list = array(
				'default'    => '(' . __( 'Default', 'click-to-chat-for-whatsapp' ) . ') wa.me',
				'wa_colon'   => 'WhatsApp://',
				'custom_url' => __( 'Custom URL', 'click-to-chat-for-whatsapp' ),
			);

			// $url_structure_d_list = apply_filters( 'ht_ctc_fh_url_structure_d_list', $url_structure_d_list );
			// $url_structure_m_list = apply_filters( 'ht_ctc_fh_url_structure_m_list', $url_structure_m_list );
			?>

		<ul class="collapsible url_structure" id="url_structure">
		<li class="">
		<div class="collapsible-header"><?php esc_html_e( 'URL Structure', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<p class="description" style="margin: 0 0 20px 0;"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/url-structure/"><?php esc_html_e( 'URL Structure', 'click-to-chat-for-whatsapp' ); ?></a> </p>

		<p class="description ht_ctc_subtitle" style="margin-bottom: 11px;"><?php esc_html_e( 'Desktop', 'click-to-chat-for-whatsapp' ); ?>:</p>
		<div class="row url_structure_row ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Open links in', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[url_target_d]" data-var="url_target_d" class="url_target_d ctc_ad_main_page_on_change_input_update_var">
					<option value="_blank" <?php echo ( '_blank' === $url_target_d ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'New Tab', 'click-to-chat-for-whatsapp' ); ?></option>
					<option value="popup" <?php echo ( 'popup' === $url_target_d ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Pop-up', 'click-to-chat-for-whatsapp' ); ?></option>
					<option value="_self" <?php echo ( '_self' === $url_target_d ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Same Tab', 'click-to-chat-for-whatsapp' ); ?></option>
				</select>
				<label><?php esc_html_e( 'Open links in', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<div class="row url_structure_row ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Desktop', 'click-to-chat-for-whatsapp' ); ?>: <?php esc_html_e( 'URL Structure', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[url_structure_d]" data-var="url_structure_d" class="url_structure_d ctc_ad_main_page_on_change_input_update_var">
					<?php
					foreach ( $url_structure_d_list as $key => $value ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $url_structure_d === $key ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
				<label><?php esc_html_e( 'Desktop', 'click-to-chat-for-whatsapp' ); ?>: <?php esc_html_e( 'URL Structure', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description" style="font-size: 11px;">
					<span style="font-weight: 500;">Wa.me</span>: To open WhatsApp Desktop app <br>
					<span style="font-weight: 500;">Web WhatsApp</span>: Opens web.whatsapp.com<br>
					<!-- todo: admin demo - custom url. -->
					<span style="font-weight: 500;">Custom URL</span>: Add any URL (e.g., WhatsApp channel URL).
				</p>
				<div class="custom_url_desktop ctc_init_display_none" style="margin-top:10px;">
					<input type="text" name="<?php echo esc_attr( $dbrow ); ?>[custom_url_d]" value="<?php echo esc_attr( $custom_url_d ); ?>" placeholder="https://www.whatsapp.com/channel" data-var="custom_url_d" class="input-margin ctc_ad_main_page_on_change_input_update_var" style="width:100%;">
					<label style="font-size:11px; color:#888;">Custom URL (Desktop)</label>
				</div>
			</div>
		</div>

		<p class="description ht_ctc_subtitle" style="margin-bottom: 11px;"><?php esc_html_e( 'Mobile', 'click-to-chat-for-whatsapp' ); ?>:</p>
		<div class="url_structure_row ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Mobile', 'click-to-chat-for-whatsapp' ); ?>: <?php esc_html_e( 'URL Structure', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[url_structure_m]" data-var="url_structure_m" class="url_structure_m ctc_ad_main_page_on_change_input_update_var">
					<?php
					foreach ( $url_structure_m_list as $key => $value ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $url_structure_m === $key ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
				<label><?php esc_html_e( 'Mobile', 'click-to-chat-for-whatsapp' ); ?>: <?php esc_html_e( 'URL Structure', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description" style="font-size: 11px;">
					<span style="font-weight: 500;">Wa.me</span>: Opens WhatsApp Mobile app <br>
					<span style="font-weight: 500;">WhatsApp://</span>: Opens WhatsApp Mobile app directly<br>
					<span style="font-weight: 500;">Custom URL</span>: Add any URL (e.g., WhatsApp channel URL).
				</p>
				<div class="custom_url_mobile ctc_init_display_none" style="margin-top:10px;">
					<input type="text" name="<?php echo esc_attr( $dbrow ); ?>[custom_url_m]" value="<?php echo esc_attr( $custom_url_m ); ?>" placeholder="https://www.whatsapp.com/channel" data-var="custom_url_m" class="input-margin ctc_ad_main_page_on_change_input_update_var" style="width:100%;">
					<label style="font-size:11px; color:#888;">Custom URL (Mobile)</label>
				</div>
			</div>
		</div>

		<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/docs/custom-url/"><?php esc_html_e( 'Custom URL', 'click-to-chat-for-whatsapp' ); ?></a> </p>


		</div>
		</li>
		</ul>
		<br>
			<?php
		}

			/**
			 * Render show/hide configuration.
			 *
			 * @return void
			 */
		public function ctc_show_hide_cb() {
			$options = get_option( 'ht_ctc_chat_options' );
			$dbrow   = 'ht_ctc_chat_options';
			$type    = 'chat';

			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/admin-show-hide.php';
		}


			/**
			 * Output additional options hooks for add-on plugins.
			 *
			 * @return void
			 */
		public function options_cb() {
			do_action( 'ht_ctc_ah_admin_chat_more_options' );
		}

			/**
			 * Render notes, feature links, and support references for the admin page.
			 *
			 * @return void
			 */
		public function ctc_notes_cb() {

			$woo_link = 'https://holithemes.com/plugins/click-to-chat/woocommerce/';
			$woo_text = '(Add, Overwrite settings for WooCommerce pages)';

			if ( class_exists( 'WooCommerce' ) ) {
				$woo_link = admin_url( 'admin.php?page=click-to-chat-woocommerce' );
			} else {
				$woo_text = '(Only if WooCommerce plugin is Active)';
			}

			?>
		<p class="description">Menu:</p>
		<p class="description">👋 <a target="_blank" class="em_1_1" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-greetings' ) ); ?>">Greetings</a>: Greetings-1, Greetings-2, Form filling(PRO), Multi Agent(PRO)</p>
		<p class="description">🎨 <a target="_blank" class="em_1_1" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-customize-styles' ) ); ?>">Customize Styles</a>: (Customize style to match your website design - color, size, call to action hover effects, ...)</p>
		<p class="description">⚙️ <a target="_blank" class="em_1_1" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-other-settings' ) ); ?>">Other Settings</a>: (Analytics, Animations, Notification Badge, Webhooks, ...)</p>
		<p class="description">🛒 <a target="_blank" class="em_1_1" href="<?php echo esc_url( $woo_link ); ?>">WooCommerce</a>: <?php echo esc_html( $woo_text ); ?></p>
		<br>
		<p class="description">Features:</p>
		<p class="description">🧩 <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/custom-element">Custom Element: </a>Class name: ctc_chat  |  Href/Link: #ctc_chat</p>
		<p class="description">🔤 <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/shortcodes-chat">Shortcodes for Chat: </a>[ht-ctc-chat]</p>
		<br>
			<?php
			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				?>
				<!-- <p class="description">PRO:  -->
				<!-- <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pro/">Click to Chat PRO</a>  -->
				<!-- - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pro-features/">Features</a> |  -->
				<!-- <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pricing/">Pricing</a></p> -->
				<!-- <br> -->
				<?php
			}
			?>
		<p class="description">Support:</p>
		<p class="description">📚 <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/faq">Frequently Asked Questions (FAQ)</a></p>

			<?php
			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				// WordPress Forum
				?>
			<p class="description">📝 <a target="_blank" href="https://wordpress.org/support/plugin/click-to-chat-for-whatsapp/#new-topic-0">Contact Us</a></p>
			<!-- <p class="description">📧 <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/support/">Contact Us</a></p> -->
				<?php
			} else {
				?>
			<p class="description">📧 <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/support/">Contact Us</a></p>
				<?php
			}

			// clear cache hover text
			$clear_cache_text = 'ctc_no_hover_text';

			if ( function_exists( 'wp_cache_clear_cache' ) || function_exists( 'w3tc_pgcache_flush' ) || function_exists( 'wpfc_clear_all_cache' ) || function_exists( 'rocket_clean_domain' ) || function_exists( 'sg_cachepress_purge_cache' ) || function_exists( 'wpo_cache_flush' ) ) {
				$clear_cache_text = 'ctc_save_changes_hover_text';
			}

			if ( class_exists( 'autoptimizeCache' ) || class_exists( 'WpeCommon' ) || class_exists( 'WpeCommon' ) || class_exists( 'LiteSpeed_Cache_API' ) || class_exists( 'Cache_Enabler' ) || class_exists( 'PagelyCachePurge' ) || class_exists( 'comet_cache' ) || class_exists( '\Hummingbird\WP_Hummingbird' ) ) {
				$clear_cache_text = 'ctc_save_changes_hover_text';
			}

			?>
		<!-- hover content for submit button -->
		<span style="display: none;" id="<?php echo esc_attr( $clear_cache_text ); ?>"><?php esc_html_e( 'Please clear the cache after save changes', 'click-to-chat-for-whatsapp' ); ?></span>
			<?php

			// if multilingual plugin is active then display a message to 'After saving the settings, clear/update the translation'
			// pll_count_posts
			if ( function_exists( 'icl_register_string' ) || function_exists( 'pll_register_string' ) ) {
				?>
			<p class="description" style="margin-top: 24px;">Multilingual: </p>
			<p class="description">🚩 If multilingual plugins are installed, After saving the changes, clear/update the string translations</p>
				<?php
			}
		}



		/**
		 * Sanitize settings input.
		 *
		 * @param array $input Settings fields to sanitize.
		 * @return array Sanitized input array.
		 */
		public function options_sanitize( $input ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'not allowed to modify - please contact admin ' );
			}

			// formatting api - emoji ..
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/ht-ctc-admin-formatting.php';

			$new_input = array();

			foreach ( $input as $key => $value ) {
				if ( isset( $input[ $key ] ) ) {

					if ( is_array( $input[ $key ] ) ) {
						// key: display, r_nums
						// $new_input[$key] = array_map( 'sanitize_text_field', $input[$key] );
						if ( function_exists( 'sanitize_textarea_field' ) ) {
							$new_input[ $key ] = map_deep( $input[ $key ], 'sanitize_textarea_field' );
						} else {
							$new_input[ $key ] = map_deep( $input[ $key ], 'sanitize_text_field' );
						}
					} elseif ( 'pre_filled' === $key || 'woo_pre_filled' === $key ) {
						if ( function_exists( 'ht_ctc_wp_encode_emoji' ) ) {
							$input[ $key ] = ht_ctc_wp_encode_emoji( $input[ $key ] );
						}
						if ( function_exists( 'sanitize_textarea_field' ) ) {
							$new_input[ $key ] = sanitize_textarea_field( $input[ $key ] );
						} else {
							$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
						}
					} elseif ( 'call_to_action' === $key ) {
						if ( function_exists( 'ht_ctc_wp_encode_emoji' ) ) {
							$input[ $key ] = ht_ctc_wp_encode_emoji( $input[ $key ] );
						}
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
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
				}
			}

			// l10n
			foreach ( $input as $key => $value ) {
				if ( 'number' === $key || 'pre_filled' === $key || 'call_to_action' === $key || 'woo_pre_filled' === $key || 'woo_call_to_action' === $key ) {
					do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key, $input[ $key ] );
				}
			}

			do_action( 'ht_ctc_ah_admin_after_sanitize' );

			return $new_input;
		}
	}

		$ht_ctc_admin_main_page = new HT_CTC_Admin_Main_Page();

		add_action( 'admin_menu', array( $ht_ctc_admin_main_page, 'menu' ) );
		add_action( 'admin_init', array( $ht_ctc_admin_main_page, 'settings' ) );

}
