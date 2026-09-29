<?php
/**
 * Admin page for managing additional settings.
 *
 * Includes analytics, show/hide controls, and other miscellaneous options.
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Other_Settings' ) ) {

	/**
	 * Handles other settings administration.
	 */
	class HT_CTC_Admin_Other_Settings {

		/**
		 * Register the Other Settings submenu pages.
		 *
		 * @return void
		 */
		public function menu() {

			add_submenu_page(
				'click-to-chat',
				'Other-Settings',
				'Other Settings',
				'manage_options',
				'click-to-chat-other-settings',
				array( $this, 'settings_page' )
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				add_submenu_page(
					'click-to-chat',
					__( 'Go Premium', 'click-to-chat-for-whatsapp' ),
					'<span class="dashicons dashicons-star-filled" style="color: #ff8c00"></span><span id="ht-ctc-go-pro-link" style="color: #ff8c00;font-weight: 500;display: inline-block;margin-left: 5px;margin-top: 2px;">' . __( 'Go Premium', 'click-to-chat-for-whatsapp' ) . '</span>',
					'manage_options',
					'https://holithemes.com/plugins/click-to-chat/pricing/'
				);
			}
		}

		/**
		 * Render the Other Settings page markup.
		 *
		 * @return void
		 */
		public function settings_page() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			?>

		<div class="wrap ctc-admin-other-settings">

			<?php settings_errors(); ?>

			<div class="row" style="display:flex; flex-wrap:wrap;">
				<div class="col s12 m12 xl8 options">
					<form action="options.php" method="post" class="">
						<?php settings_fields( 'ht_ctc_os_page_settings_fields' ); ?>
						<?php do_settings_sections( 'ht_ctc_os_page_settings_sections_do' ); ?>
						<?php submit_button(); ?>
					</form>
				</div>
				<div class="col s12 m12 xl4 ht-ctc-admin-sidebar">
				</div>
			</div>

			<!-- new row - After settings page  -->
			<div class="row">
				
				<!-- after settings page -->
				<?php // include_once HT_CTC_PLUGIN_DIR .'new/admin/admin_commons/admin-after-settings-page.php'; ?>
					
			</div>


		</div>

			<?php
		}

		/**
		 * Register settings, sections and fields for Other Settings.
		 *
		 * @return void
		 */
		public function settings() {

			register_setting( 'ht_ctc_os_page_settings_fields', 'ht_ctc_othersettings', array( $this, 'options_sanitize' ) );
			register_setting( 'ht_ctc_os_page_settings_fields', 'ht_ctc_code_blocks', array( $this, 'options_sanitize' ) );

			add_settings_section( 'ht_ctc_os_settings_sections_add', '', array( $this, 'main_settings_section_cb' ), 'ht_ctc_os_page_settings_sections_do' );

			add_settings_field( 'ht_ctc_animations', 'Animations', array( $this, 'ht_ctc_animations_cb' ), 'ht_ctc_os_page_settings_sections_do', 'ht_ctc_os_settings_sections_add' );
			add_settings_field( 'ht_ctc_analytics', 'Analytics', array( $this, 'ht_ctc_analytics_cb' ), 'ht_ctc_os_page_settings_sections_do', 'ht_ctc_os_settings_sections_add' );
			add_settings_field( 'ht_ctc_webhooks', 'Webhooks', array( $this, 'ht_ctc_webhooks_cb' ), 'ht_ctc_os_page_settings_sections_do', 'ht_ctc_os_settings_sections_add' );
			add_settings_field( 'ht_ctc_custom_css', 'Custom CSS', array( $this, 'ht_ctc_custom_css_cb' ), 'ht_ctc_os_page_settings_sections_do', 'ht_ctc_os_settings_sections_add' );
			add_settings_field( 'ht_ctc_othersettings', 'Advanced Settings', array( $this, 'ht_ctc_othersettings_cb' ), 'ht_ctc_os_page_settings_sections_do', 'ht_ctc_os_settings_sections_add' );
		}

		/**
		 * Output section header for Other Settings.
		 *
		 * @return void
		 */
		public function main_settings_section_cb() {
			?>
		<h1>Other Settings</h1>
		<div class="ctc_admin_top_menu" style="float:right; margin:0px 18px;">
			<a href="#ht_ctc_analytics">Analytics</a> | <a href="#ht_ctc_webhooks">Webhooks</a>
		</div>
			<?php
			do_action( 'ht_ctc_ah_admin' );
		}

		/**
		 * Analytics callback function.
		 *
		 * @return void
		 */
		public function ht_ctc_analytics_cb() {

			$options = get_option( 'ht_ctc_othersettings' );
			$dbrow   = 'ht_ctc_othersettings';
			?>
		<ul class="collapsible" data-collapsible="accordion" id="ht_ctc_analytics">
		<li class="active have-sub-collapsible">
		<div class="collapsible-header"><?php esc_html_e( 'Google Analytics, Meta Pixel, Google Ads Conversion', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
			<?php
			// Google Analytics
			$g_an_value = ( isset( $options['g_an'] ) ) ? esc_attr( $options['g_an'] ) : 'ga4';

			$google_analytics_checkbox = ( isset( $options['g_an'] ) ) ? 1 : '';
			// $google_analytics_checkbox = ( isset( $options['g_an']) ) ? esc_attr( $options['g_an'] ) : '';

			$g_an_gtm_value                = ( isset( $options['g_an_gtm'] ) ) ? esc_attr( $options['g_an_gtm'] ) : '1';
			$google_analytics_gtm_checkbox = ( isset( $options['g_an_gtm'] ) ) ? 1 : '';

			?>
		<ul class="collapsible col_google_analytics coll_active" data-coll_active="col_google_analytics" id="col_google_analytics">
		<li class="">
		<div class="collapsible-header">
			<span><?php esc_html_e( 'Google Analytics', 'click-to-chat-for-whatsapp' ); ?></span>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
		<p>
		<p class="description"><?php esc_html_e( 'If Google Analytics installed creates an Event there', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/google-analytics/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
			<br>
			<label class="ctc_checkbox_label">
				<input name="<?php echo esc_attr( $dbrow ); ?>[g_an]" type="checkbox" value="<?php echo esc_attr( $g_an_value ); ?>" <?php checked( $google_analytics_checkbox, 1 ); ?> id="google_analytics" />
				<span><?php esc_html_e( 'Google Analytics', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
			<?php
			$g_an_event_name = ( isset( $options['g_an_event_name'] ) ) ? esc_attr( $options['g_an_event_name'] ) : 'click to chat';
			// list of all g_an params..

			$g_an_params = ( isset( $options['g_an_params'] ) && is_array( $options['g_an_params'] ) ) ? array_map( 'esc_attr', $options['g_an_params'] ) : '';

			// count of g_an params.. used for adding new params.. always raises..
			$g_an_param_order = ( isset( $options['g_an_param_order'] ) ) ? esc_attr( $options['g_an_param_order'] ) : 5;
			$key_gen          = 1;

			?>


		<div class="row ctc_ga_values ctc_init_display_none">

			<div style="display:flex; justify-content:center; gap:5px;">
				<div class="input-field">
					<p class="description"><?php esc_html_e( 'Event Name', 'click-to-chat-for-whatsapp' ); ?></p>
					<input style="visibility:hidden;" type="text" class="input-margin">
				</div>
				<div class="input-field" style="">
					<input name="<?php echo esc_attr( $dbrow ); ?>[g_an_event_name]" value="<?php echo esc_attr( $g_an_event_name ); ?>" placeholder="click to chat" id="g_an_event_name" type="text" class="input-margin">
					<label for="g_an_event_name"><?php esc_html_e( 'Event Name', 'click-to-chat-for-whatsapp' ); ?></label>
				</div>
				<div class="input-field">
					<span style="visibility:hidden;" class="dashicons dashicons-no-alt" title="Remove Parameter"></span>
				</div>
			</div>
			
			<div class="ctc_an_params ctc_g_an_params ctc_sortable">
				<?php

				$num = '';

				if ( is_array( $g_an_params ) && isset( $g_an_params[0] ) ) {

					foreach ( $g_an_params as $param ) {

						$param_options = ( isset( $options[ $param ] ) && is_array( $options[ $param ] ) ) ? map_deep( $options[ $param ], 'esc_attr' ) : '';

						$key   = ( isset( $param_options['key'] ) ) ? esc_attr( $param_options['key'] ) : '';
						$value = ( isset( $param_options['value'] ) ) ? esc_attr( $param_options['value'] ) : '';

						// if key and value not empty..
						if ( ! empty( $key ) && ! empty( $value ) ) {
							?>
							<div class="ctc_an_param g_an_param row" style="margin-bottom:5px; display:flex; gap:5px; justify-content:center;">

								<input style="display: none;" name="ht_ctc_othersettings[g_an_params][]" type="text" class="g_an_param_order_ref_number" value="<?php echo esc_attr( $param ); ?>">

								<div class="input-field">
									<input name="ht_ctc_othersettings[<?php echo esc_attr( $param ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $param . '_key' ); ?>" type="text" class="ht_ctc_g_an_param_key input-margin">
									<label for="<?php echo esc_attr( $param . '_key' ); ?>"><?php esc_html_e( 'Event Parameter', 'click-to-chat-for-whatsapp' ); ?></label>
								</div>

								<div class="input-field">
									<input name="ht_ctc_othersettings[<?php echo esc_attr( $param ); ?>][value]" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr( $param ); ?>" type="text" class="ht_ctc_g_an_param_value input-margin">
									<label for="<?php echo esc_attr( $param ); ?>"><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label>
								</div>

								<div class="input-field">
									<span style="color:#ddd; margin-left:auto; cursor:pointer;" class="an_param_remove dashicons dashicons-no-alt" title="Remove Parameter"></span>
								</div>


							</div>
							<?php
						}

						++$key_gen;
					}
				}

				?>
				<!-- new fileds - for adding -->
				<div class="ctc_new_g_an_param">
				</div>


				<!-- Add parameter - button -->
				<div style="text-align:center;">
					<div class="ctc_add_g_an_param_button" style="display:inline-flex; margin: 10px 0px; cursor:pointer; font-size:16px; font-weight:500; padding: 8px; justify-content:center;">
						<span style="color: #039be5;" class="dashicons dashicons-plus-alt2" ></span>
						<span style="color: #039be5;">Add Parameter</span>
					</div>
				</div>


				<!-- snippets -->
				<div class="ctc_g_an_param_snippets" style="display: none;">

					<!-- g_an_param order. next key. (uses from js, saves in db) -->
					<input type="text" name="ht_ctc_othersettings[g_an_param_order]" class="g_an_param_order" value="<?php echo esc_attr( $g_an_param_order ); ?>">

					
					<!-- snippet: add g_an_param -->
					<div class="ctc_an_param g_an_param ht_ctc_g_an_add_param">

						<div class="row" style="display:flex; gap:5px; justify-content:center;">

							<input style="display: none;" type="text" class="g_an_param_order_ref_number" value="<?php echo esc_attr( $g_an_param_order ); ?>">

							<div class="input-field">
								<input type="text" placeholder="click" class="ht_ctc_g_an_add_param_key input-margin">
								<label><?php esc_html_e( 'Event Parameter', 'click-to-chat-for-whatsapp' ); ?></label>
							</div>

							<div class="input-field">
								<input type="text" placeholder="chat" class="ht_ctc_g_an_add_param_value input-margin">
								<label><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label>
							</div>

							<div class="input-field">
								<span style="color:#ddd; margin-left:auto; cursor:pointer;" class="an_param_remove dashicons dashicons-no-alt" title="Remove Parameter"></span>
							</div>
							
						</div>

					</div>
					
				</div>
				
				
			</div>
					
			<p class="description" style="margin:0px 10px;">Variables: {title}, {url}, and {number} replace the page's title, url, and number that were assigned to the widget.</p>

			<details class="ctc_details" style="margin:7px 10px;">
				<summary>PRO: Get Values from Cookies [[ ]] and URL Parameters [ ]</summary>
				<p class="description" style="margin:8px 10px 0px 10px;">
					<span>
						<strong>Fetch URL Parameter Values:</strong> To retrieve values from URL parameters, enclose the parameter name in a single square bracket <code>[]</code>. If the parameter doesn't exist, return blank. <br>
						Example: <code>[gclid]</code>, <code>[utm_source]</code> 
						<br>
						<strong>Fetch Cookie Values:</strong> To retrieve values from cookies, enclose the cookie name in double square brackets <code>[[]]</code>. If the cookie doesn't exist, return blank.
						<br> Example: <code>[[_ga]]</code>
					</span>
				</p> 
		</details>
			
		</div>



		</div>
		</li>
		</ul>
		
			<?php
			// Google Tag Manager
			$gtm_value                   = ( isset( $options['gtm'] ) ) ? esc_attr( $options['gtm'] ) : 'gtm';
			$google_tag_manager_checkbox = ( isset( $options['gtm'] ) ) ? 1 : '';
			?>
		<ul class="collapsible col_google_tag_manager coll_active" data-coll_active="col_google_tag_manager" id="col_google_tag_manager">
		<li class="">
		<div class="collapsible-header">
			<span><?php esc_html_e( 'Google Tag Manager', 'click-to-chat-for-whatsapp' ); ?></span>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
		<p>
		<p class="description"><?php esc_html_e( 'Pushes a dataLayer event for GTM triggers.', 'click-to-chat-for-whatsapp' ); ?></p>
			<br>
			<label class="ctc_checkbox_label">
				<input name="<?php echo esc_attr( $dbrow ); ?>[gtm]" type="checkbox" value="<?php echo esc_attr( $gtm_value ); ?>" <?php checked( $google_tag_manager_checkbox, 1 ); ?> id="google_tag_manager" />
				<span><?php esc_html_e( 'Google Tag Manager', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
			<?php
			$gtm_event_name  = ( isset( $options['gtm_event_name'] ) ) ? esc_attr( $options['gtm_event_name'] ) : 'Click to Chat';
			$gtm_params      = ( isset( $options['gtm_params'] ) && is_array( $options['gtm_params'] ) ) ? array_map( 'esc_attr', $options['gtm_params'] ) : '';
			$gtm_param_order = ( isset( $options['gtm_param_order'] ) ) ? esc_attr( $options['gtm_param_order'] ) : 10;
			$key_gen         = 1;
			?>

		<div class="row ctc_gtm_values">
			<div style="display:flex; justify-content:center; gap:5px;">
				<div class="input-field">
					<p class="description"><?php esc_html_e( 'Event Name', 'click-to-chat-for-whatsapp' ); ?></p>
					<input style="visibility:hidden;" type="text" class="input-margin">
				</div>
				<div class="input-field" style="">
					<input name="<?php echo esc_attr( $dbrow ); ?>[gtm_event_name]" value="<?php echo esc_attr( $gtm_event_name ); ?>" placeholder="click to chat" id="gtm_event_name" type="text" class="input-margin">
					<label for="gtm_event_name"><?php esc_html_e( 'Event Name', 'click-to-chat-for-whatsapp' ); ?></label>
				</div>
				<div class="input-field">
					<span style="visibility:hidden;" class="dashicons dashicons-no-alt" title="Remove Parameter"></span>
				</div>
			</div>
			
			<div class="ctc_an_params ctc_gtm_params ctc_sortable">
				<?php
				$num = '';
				if ( is_array( $gtm_params ) && isset( $gtm_params[0] ) ) {
					foreach ( $gtm_params as $param ) {
						$param_options = ( isset( $options[ $param ] ) && is_array( $options[ $param ] ) ) ? map_deep( $options[ $param ], 'esc_attr' ) : '';
						$key           = ( isset( $param_options['key'] ) ) ? esc_attr( $param_options['key'] ) : '';
						$value         = ( isset( $param_options['value'] ) ) ? esc_attr( $param_options['value'] ) : '';
						if ( ! empty( $key ) && ! empty( $value ) ) {
							?>
							<div class="ctc_an_param gtm_param row" style="margin-bottom:5px; display:flex; gap:5px; justify-content:center;">
								<input style="display: none;" name="ht_ctc_othersettings[gtm_params][]" type="text" class="gtm_param_order_ref_number" value="<?php echo esc_attr( $param ); ?>">
								<div class="input-field">
									<input name="ht_ctc_othersettings[<?php echo esc_attr( $param ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $param . '_key' ); ?>" type="text" class="ht_ctc_gtm_param_key input-margin">
									<label for="<?php echo esc_attr( $param . '_key' ); ?>"><?php esc_html_e( 'Event Parameter', 'click-to-chat-for-whatsapp' ); ?></label>
								</div>
								<div class="input-field">
									<input name="ht_ctc_othersettings[<?php echo esc_attr( $param ); ?>][value]" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr( $param ); ?>" type="text" class="ht_ctc_gtm_param_value input-margin">
									<label for="<?php echo esc_attr( $param ); ?>"><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label>
								</div>
								<div class="input-field">
									<span style="color:#ddd; margin-left:auto; cursor:pointer;" class="an_param_remove dashicons dashicons-no-alt" title="Remove Parameter"></span>
								</div>
							</div>
							<?php
						}
						++$key_gen;
					}
				}
				?>
				<div class="ctc_new_gtm_param">
				</div>
				<div style="text-align:center;">
					<div class="ctc_add_gtm_param_button" style="display:inline-flex; margin: 10px 0px; cursor:pointer; font-size:16px; font-weight:500; padding: 8px; justify-content:center;">
						<span style="color: #039be5;" class="dashicons dashicons-plus-alt2" ></span>
						<span style="color: #039be5;">Add Parameter</span>
					</div>
				</div>
				<div class="ctc_gtm_param_snippets" style="display: none;">
					<input type="text" name="ht_ctc_othersettings[gtm_param_order]" class="gtm_param_order" value="<?php echo esc_attr( $gtm_param_order ); ?>">
					<div class="ctc_an_param gtm_param ht_ctc_gtm_add_param">
						<div class="row" style="display:flex; gap:5px; justify-content:center;">
							<input style="display: none;" type="text" class="gtm_param_order_ref_number" value="<?php echo esc_attr( $gtm_param_order ); ?>">
							<div class="input-field">
								<input type="text" placeholder="click" class="ht_ctc_gtm_add_param_key input-margin">
								<label><?php esc_html_e( 'Event Parameter', 'click-to-chat-for-whatsapp' ); ?></label>
							</div>
							<div class="input-field">
								<input type="text" placeholder="chat" class="ht_ctc_gtm_add_param_value input-margin">
								<label><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label>
							</div>
							<div class="input-field">
								<span style="color:#ddd; margin-left:auto; cursor:pointer;" class="an_param_remove dashicons dashicons-no-alt" title="Remove Parameter"></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<p class="description"><?php esc_html_e( 'Create Event from Google Tag manager (GTM)', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/create-event-from-google-tag-manager-using-datalayer-send-to-google-analytics/"><?php esc_html_e( 'dataLayer', 'click-to-chat-for-whatsapp' ); ?></a> </p>

		
		<details class="ctc_details" style="margin:15px 10px;">
			<summary style="font-size:12px;"><strong>Deprecated — Use the GTM Settings above instead</strong></summary>
			<div style="margin:8px 10px 0px 10px;">
				<label class="ctc_checkbox_label" style="font-size:11px;">
					<input name="<?php echo esc_attr( $dbrow ); ?>[g_an_gtm]" type="checkbox" value="<?php echo esc_attr( $g_an_gtm_value ); ?>" <?php checked( $google_analytics_gtm_checkbox, 1 ); ?> id="google_analytics_gtm" />
					<span>Push datalayer object to GTM using above Google Analyatics event name, parameters.</span>
				</label>
				<p style="margin:8px 0 0; color:#d00; font-weight:600; font-size:11px;">This feature is deprecated. Please use the GTM settings in this section.</p>
			</div>
		</details>
		
		<br>
		</div>
		</li>
		</ul>

			<?php
			$fb_pixel_checkbox = ( isset( $options['fb_pixel'] ) ) ? esc_attr( $options['fb_pixel'] ) : '';
			?>

		<ul class="collapsible col_pixel coll_active" data-coll_active="col_pixel" id="col_pixel">
		<li class="">
		<div class="collapsible-header">
			<span><?php esc_html_e( 'Meta Pixel', 'click-to-chat-for-whatsapp' ); ?></span>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
		<p class="description" style="margin-bottom: 15px;"><?php esc_html_e( 'If Meta Pixel installed creates an Event there', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/facebook-pixel/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
 
		<p>
			<label class="ctc_checkbox_label">
				<input name="<?php echo esc_attr( $dbrow ); ?>[fb_pixel]" type="checkbox" value="1" <?php checked( $fb_pixel_checkbox, 1 ); ?> id="fb_pixel" />
				<span><?php esc_html_e( 'Meta Pixel', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
			<?php
			$pixel_event_type          = ( isset( $options['pixel_event_type'] ) ) ? esc_attr( $options['pixel_event_type'] ) : 'trackCustom';
			$pixel_custom_event_name   = ( isset( $options['pixel_custom_event_name'] ) ) ? esc_attr( $options['pixel_custom_event_name'] ) : 'Click to Chat by HoliThemes';
			$pixel_standard_event_name = ( isset( $options['pixel_standard_event_name'] ) ) ? esc_attr( $options['pixel_standard_event_name'] ) : 'Lead';

			$pixel_params = ( isset( $options['pixel_params'] ) ) ? array_map( 'esc_attr', $options['pixel_params'] ) : '';

			// count of pixel params.. used for adding new params.. always raises..
			$pixel_param_order = ( isset( $options['pixel_param_order'] ) ) ? esc_attr( $options['pixel_param_order'] ) : 5;
			$key_gen           = 1;

			// https://developers.facebook.com/docs/meta-pixel/implementation/conversion-tracking, https://developers.facebook.com/docs/meta-pixel/reference/
			?>
		<div class="row ctc_pixel_values ctc_init_display_none">

			<div style="display:flex; justify-content:center; gap:5px;">
				<div class="input-field">
					<p class="description"><?php esc_html_e( 'Event Type', 'click-to-chat-for-whatsapp' ); ?></p>
					<input style="visibility:hidden;" type="text" class="input-margin">
				</div>
				<div class="" style="">
					<select class="pixel_event_type" name="<?php echo esc_attr( $dbrow ); ?>[pixel_event_type]">
						<option value="trackCustom" <?php echo ( 'trackCustom' === $pixel_event_type ) ? 'SELECTED' : ''; ?> >Custom Event</option>
						<option value="track" <?php echo ( 'track' === $pixel_event_type ) ? 'SELECTED' : ''; ?> >Standard</option>
					</select>
				</div>
				<div class="input-field">
					<span style="visibility:hidden;" class="dashicons dashicons-no-alt" title="Remove Parameter"></span>
				</div>
			</div>

			<div class="pixel_custom_event ctc_init_display_none">
				<div style="display:flex; justify-content:center; gap:5px;">
					<div class="input-field">
						<p class="description"><?php esc_html_e( 'Event Name', 'click-to-chat-for-whatsapp' ); ?></p>
						<input style="visibility:hidden;" type="text" class="input-margin">
					</div>
					<div class="input-field" style="">
						<input name="<?php echo esc_attr( $dbrow ); ?>[pixel_custom_event_name]" value="<?php echo esc_attr( $pixel_custom_event_name ); ?>" placeholder="click to chat" id="pixel_custom_event_name" type="text" class="input-margin">
						<label for="pixel_custom_event_name"><?php esc_html_e( 'Custom Event Name', 'click-to-chat-for-whatsapp' ); ?></label>
					</div>
					<div class="input-field">
						<span style="visibility:hidden;" class="dashicons dashicons-no-alt" title="Remove Parameter"></span>
					</div>
				</div>
			</div>

			<div class="pixel_standard_event ctc_init_display_none">
				<div style="display:flex; justify-content:center; gap:5px;">
					<div class="input-field">
						<p class="description"><?php esc_html_e( 'Event Name', 'click-to-chat-for-whatsapp' ); ?></p>
						<input style="visibility:hidden;" type="text" class="input-margin">
					</div>
					<div class="input-field" style="">
						<select class="pixel_standard_event_name" name="<?php echo esc_attr( $dbrow ); ?>[pixel_standard_event_name]">
							<option value="Lead" <?php echo ( 'Lead' === $pixel_standard_event_name ) ? 'SELECTED' : ''; ?> >Lead</option>
							<option value="Contact" <?php echo ( 'Contact' === $pixel_standard_event_name ) ? 'SELECTED' : ''; ?> >Contact</option>
							<option value="Purchase" <?php echo ( 'Purchase' === $pixel_standard_event_name ) ? 'SELECTED' : ''; ?> >Purchase</option>
							<option value="Schedule" <?php echo ( 'Schedule' === $pixel_standard_event_name ) ? 'SELECTED' : ''; ?> >Schedule</option>
							<option value="Subscribe" <?php echo ( 'Subscribe' === $pixel_standard_event_name ) ? 'SELECTED' : ''; ?> >Subscribe</option>
							<option value="ViewContent" <?php echo ( 'ViewContent' === $pixel_standard_event_name ) ? 'SELECTED' : ''; ?> >ViewContent</option>
						</select>
					</div>
					<div class="input-field">
						<span style="visibility:hidden;" class="dashicons dashicons-no-alt" title="Remove Parameter"></span>
					</div>
				</div>
			</div>
			
			<div class="ctc_an_params ctc_pixel_params ctc_sortable">
				<?php

				$num = '';

				if ( is_array( $pixel_params ) && isset( $pixel_params[0] ) ) {

					foreach ( $pixel_params as $param ) {

						$param_options = ( isset( $options[ $param ] ) && is_array( $options[ $param ] ) ) ? map_deep( $options[ $param ], 'esc_attr' ) : '';

						$key   = ( isset( $param_options['key'] ) ) ? esc_attr( $param_options['key'] ) : '';
						$value = ( isset( $param_options['value'] ) ) ? esc_attr( $param_options['value'] ) : '';

						if ( ! empty( $key ) && ! empty( $value ) ) {
							?>
							<div class="ctc_an_param pixel_param row" style="margin-bottom:5px; display:flex; gap:5px; justify-content:center;">

								<input style="display: none;" name="ht_ctc_othersettings[pixel_params][]" type="text" class="pixel_param_order_ref_number" value="<?php echo esc_attr( $param ); ?>">

								<div class="input-field">
									<input name="ht_ctc_othersettings[<?php echo esc_attr( $param ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $param . '_key' ); ?>" type="text" class="ht_ctc_g_an_param_key input-margin">
									<label for="<?php echo esc_attr( $param . '_key' ); ?>"><?php esc_html_e( 'Event Parameter', 'click-to-chat-for-whatsapp' ); ?></label>
								</div>

								<div class="input-field">
									<input name="ht_ctc_othersettings[<?php echo esc_attr( $param ); ?>][value]" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr( $param ); ?>" type="text" class="ht_ctc_g_an_param_value input-margin">
									<label for="<?php echo esc_attr( $param ); ?>"><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label>
								</div>

								<div class="input-field">
									<span style="color:#ddd; margin-left:auto; cursor:pointer;" class="an_param_remove dashicons dashicons-no-alt" title="Remove Parameter"></span>
								</div>


							</div>
							<?php
						}

						++$key_gen;
					}
				}

				?>
				<!-- new fileds - for adding -->
				<div class="ctc_new_pixel_param">
				</div>


				<!-- Add parameter - button -->
				<div style="text-align:center;">
					<div class="ctc_add_pixel_param_button" style="display:inline-flex; margin: 10px 0px; cursor:pointer; font-size:16px; font-weight:500; padding: 8px; justify-content:center;">
						<span style="color: #039be5;" class="dashicons dashicons-plus-alt2" ></span>
						<span style="color: #039be5;">Add Parameter</span>
					</div>
				</div>


				<!-- snippets -->
				<div class="ctc_pixel_param_snippets" style="display: none;">

					<!-- pixel_param order. next key. (uses from js, saves in db) -->
					<input type="text" name="ht_ctc_othersettings[pixel_param_order]" class="pixel_param_order" value="<?php echo esc_attr( $pixel_param_order ); ?>">

					
					<!-- snippet: add pixel_param -->
					<div class="ctc_an_param pixel_param ht_ctc_pixel_add_param">

						<div class="row" style="display:flex; gap:5px; justify-content:center;">

							<input style="display: none;" type="text" class="pixel_param_order_ref_number" value="<?php echo esc_attr( $pixel_param_order ); ?>">

							<div class="input-field">
								<input type="text" placeholder="click" class="ht_ctc_pixel_add_param_key input-margin">
								<label><?php esc_html_e( 'Event Parameter', 'click-to-chat-for-whatsapp' ); ?></label>
							</div>

							<div class="input-field">
								<input type="text" placeholder="chat" class="ht_ctc_pixel_add_param_value input-margin">
								<label><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label>
							</div>

							<div class="input-field">
								<span style="color:#ddd; margin-left:auto; cursor:pointer;" class="an_param_remove dashicons dashicons-no-alt" title="Remove Parameter"></span>
							</div>
							
						</div>

					</div>
					
				</div>
				
				
			</div>


			<p class="description" style="margin:0px 10px;">Variables: {title}, {url}, {number} replace page title, url, and number that are assigned to the widget.</p>

			<details class="ctc_details" style="margin:7px 10px;">
				<summary>PRO: Get Values from Cookies [[ ]] and URL Parameters [ ]</summary>
				<p class="description" style="margin:8px 10px 0px 10px;">
					<span>
						<strong>Fetch URL Parameter Values:</strong> To retrieve values from URL parameters, enclose the parameter name in a single square bracket <code>[]</code>. If the parameter doesn't exist, return blank. <br>
						Example: <code>[gclid]</code>, <code>[utm_source]</code> 
						<br>
						<strong>Fetch Cookie Values:</strong> To retrieve values from cookies, enclose the cookie name in double square brackets <code>[[]]</code>. If the cookie doesn't exist, return blank.
						<br> Example: <code>[[_ga]]</code> 
					</span>
				</p> 
		</details>

		</div>
		
			<?php
			do_action( 'ht_ctc_ah_admin_after_fb_pixel' );
			?>

		<br>

		</div>
		</li>
		</ul>

		<ul class="collapsible col_g_ads coll_active" data-coll_active="col_g_ads" id="col_g_ads">
		<li class="">
		<div class="collapsible-header">
			<span><?php esc_html_e( 'Google Ads Conversion', 'click-to-chat-for-whatsapp' ); ?></span>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

			<?php
			// Google Ads gtag_report_conversion
			$ga_ads_checkbox = ( isset( $options['ga_ads'] ) ) ? esc_attr( $options['ga_ads'] ) : '';

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				?>
				<p class="description ht_ctc_subtitle"><?php esc_html_e( 'Google Ads Conversion', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/google-ads-conversion/">PRO</a></p>
				<?php
			}

			// enable, conversion id, label
			do_action( 'ht_ctc_ah_admin_google_ads' );

			?>
		</div>
		</li>
		</ul>

			<?php

			$analytics      = ( isset( $options['analytics'] ) ) ? esc_attr( $options['analytics'] ) : 'all';
			$analytics_list = array(
				'all'     => 'All Clicks',
				'session' => 'One click per session',
			);

			$analytics_message = 'All Clicks';
			if ( isset( $analytics_list[ "$analytics" ] ) ) {
				$analytics_message = $analytics_list[ "$analytics" ];
			}

			?>

		<br>
		<div class="analytics_count">
			<p class="description analytics_count_message" style="display:flex;"><?php esc_html_e( 'Analytics', 'click-to-chat-for-whatsapp' ); ?>: <span class="" style="cursor:pointer; border-bottom: 1px dotted;"><?php echo esc_html( $analytics_message ); ?></span></p>
			<div class="analytics_count_select ctc_init_display_none">
				<select name="ht_ctc_othersettings[analytics]" class="select_analytics" style="border:unset; background-color:inherit;">
					<?php
					foreach ( $analytics_list as $key => $value ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $analytics ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
				<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/analytics-count/">Analytics Count</a></p>
			</div>
		</div>
		
			<?php

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				?>
			<p class="description"><span class="ga_ads_display" style="font-size: 0.7em;"><span style="cursor:pointer; border-bottom: 1px dotted;">gtag_report_conversion</span></span></p>
			<div class="ga_ads_checkbox" style="display:none; margin: 20px 0px 0px 20px;">
				<p class="description">This feature requires to add JavaScript code on your website i.e. add gtag_report_conversion function</p>
				<p>
					<label>
						<input name="<?php echo esc_attr( $dbrow ); ?>[ga_ads]" type="checkbox" value="1" <?php checked( $ga_ads_checkbox, 1 ); ?> id="ga_ads" />
						<span><?php esc_html_e( 'call gtag_report_conversion function', 'click-to-chat-for-whatsapp' ); ?></span>
					</label>
				</p>
				<p class="description"><?php esc_html_e( 'call gtag_report_conversion function, when user clicks', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/call-gtag_report_conversion-function/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
				<br>
				<p class="description"><a href="https://holithemes.com/plugins/click-to-chat/google-ads-conversion/"><strong>PRO</strong></a>: Add Conversion ID, Conversion label direclty (no need to setup gtag_report_conversion function)</p>
			</div>
				<?php
			}
			?>

		</div>
		</li>
		</ul>
			<?php
		}

		/**
		 * Webhook callback function.
		 *
		 * @return void
		 */
		public function ht_ctc_webhooks_cb() {

			$options = get_option( 'ht_ctc_othersettings' );
			$dbrow   = 'ht_ctc_othersettings';

			$hook_url = isset( $options['hook_url'] ) ? esc_attr( $options['hook_url'] ) : '';

			?>
		<ul class="collapsible ht_ctc_webhooks" data-collapsible="accordion" id="ht_ctc_webhooks">
		<li class="">
		<div class="collapsible-header"><?php esc_html_e( 'Webhooks', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
		
		<p class="description" style="margin-bottom: 40px;"><?php esc_html_e( 'Integrate, Automation', 'click-to-chat-for-whatsapp' ); ?> <?php esc_html_e( 'using', 'click-to-chat-for-whatsapp' ); ?> <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/webhooks/"><?php esc_html_e( 'Webhooks', 'click-to-chat-for-whatsapp' ); ?></a></p>
		<p class="description" style="margin-top:10px;">To get the greetings form data, use the <a href="https://holithemes.com/plugins/click-to-chat/docs/greetings-form#webhooks" target="_blank">Greetings Form webhook</a> feature.</p>

		<!-- Webhook URL -->
		<div class="row">
			<div class="input-field col s12">
				<input name="<?php echo esc_attr( $dbrow ); ?>[hook_url]" value="<?php echo esc_attr( $hook_url ); ?>" id="hook_url" type="text" class="input-margin">
				<label for="hook_url"><?php esc_html_e( 'Webhook URL', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'Clicking on the WhatsApp widget triggers this Webhook URL', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>

		<div class="row">
		
			<br>
			<div class="ctc_hook_value ctc_sortable">
				<?php

				// hook values
				$hook_v = ( isset( $options['hook_v'] ) ) ? $options['hook_v'] : '';
				$count  = 1;
				$num    = '';

				if ( is_array( $hook_v ) ) {
					$hook_v = array_filter( $hook_v );
					$hook_v = array_values( $hook_v );
					$hook_v = array_map( 'esc_attr', $hook_v );
					$count  = count( $hook_v );

					// hook values
					if ( isset( $hook_v[0] ) ) {
						for ( $i = 0; $i < $count; $i++ ) {
							$dbrow = "ht_ctc_othersettings[hook_v][$i]";
							$num   = $hook_v[ $i ];
							?>
							<div class="additional-value row" style="margin-bottom: 15px;">
								<div class="col s3">
									<p class="description handle">Value<?php echo esc_html( $i + 1 ); ?></p>
								</div>
								<div class="col s9 m6">
									<p style="display: flex;">
										<input name="<?php echo esc_attr( $dbrow ); ?>" value="<?php echo esc_attr( $num ); ?>" type="text"/>
										<span style="color:lightgrey; cursor:pointer;" class="hook_remove_value dashicons dashicons-no-alt"></span>
									</p>
								</div>
							</div>
							<?php
						}
					}
				}

				?>
			</div>
					
			<span style="color:#039be5; cursor:pointer; font-size:16px;" 
			class="add_hook_value dashicons dashicons-plus-alt2 col s12" 
			data-html='<div class="row additional-value"><div class="col s3"><p class="description"><?php esc_html_e( 'Add Value', 'click-to-chat-for-whatsapp' ); ?></p></div><div class="input-field col s9 m6" style="display: flex;"><input name="ht_ctc_othersettings[hook_v][]" value="" id="hook_v" type="text" class="input-margin"><label for="hook_v"><?php esc_html_e( 'Value', 'click-to-chat-for-whatsapp' ); ?></label><span style="color:lightgrey; cursor:pointer;" class="hook_remove_value dashicons dashicons-no-alt"></span></div></div>' 
			><?php esc_html_e( 'Add Value', 'click-to-chat-for-whatsapp' ); ?></span>
			
		</div>
		<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pricing/">PRO</a>: Dynamic Variables - {number}, {url}, {time}, {title} </p>
		<!-- <p class="description">{number}: Number that is assigned to the widget</p> -->
		<details class="ctc_details" style="margin:7px 0px;">
		<summary>PRO: Get Values from Cookies [[ ]] and URL Parameters [ ]</summary>
				<p class="description" style="margin:8px 10px 0px 10px;">
					<span>
						<strong>Fetch URL Parameter Values:</strong> To retrieve values from URL parameters, enclose the parameter name in a single square bracket <code>[]</code>. If the parameter doesn't exist, return blank. <br>
						Example: <code>[gclid]</code>, <code>[utm_source]</code> 
						<br>
						<strong>Fetch Cookie Values:</strong> To retrieve values from cookies, enclose the cookie name in double square brackets <code>[[]]</code>. If the cookie doesn't exist, return blank.
						<br> Example: <code>[[_ga]]</code> 
					</span>
				</p> 
		</details>
		<a class="description" target="_blank" href="https://holithemes.com/plugins/click-to-chat/webhooks/#pro">Webhooks</a>
		</div>
		</li>
		</ul>
			<?php
		}

		/**
		 * Custom CSS callback function.
		 */
		public function ht_ctc_custom_css_cb() {

			$options = get_option( 'ht_ctc_code_blocks' );
			$dbrow   = 'ht_ctc_code_blocks';

			$custom_css = ( isset( $options['custom_css'] ) ) ? esc_attr( $options['custom_css'] ) : '';

			if ( ! empty( $custom_css ) ) {
				// $custom_css = stripslashes($custom_css);
				$allowed_html = wp_kses_allowed_html( 'post' );
				$custom_css   = wp_kses( $custom_css, $allowed_html );
			}

			?>
		<ul class="collapsible ht_ctc_custom_css" data-collapsible="accordion" id="ht_ctc_custom_css">
		<li class="">
		<div class="collapsible-header"><?php esc_html_e( 'Custom CSS', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<p class="description">Customize the Click to Chat plugin widget by adding custom <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/custom-css/">CSS Code</a></p>

		<!-- Custom CSS -->
		<div class="row">
			<div class="input-field col s12">
				<textarea name="<?php echo esc_attr( $dbrow ); ?>[custom_css]" id="custom_css" class=""  placeholder="Custom CSS" style="padding:12px; height:160px;" ><?php echo esc_textarea( $custom_css ); ?></textarea>
			</div>
		</div>

		</div>
		</li>
		</ul>
			<?php
		}

		/**
		 * Animations callback function.
		 *
		 * @return void
		 */
		public function ht_ctc_animations_cb() {

			$options = get_option( 'ht_ctc_othersettings' );
			$dbrow   = 'ht_ctc_othersettings';

			$greetings          = get_option( 'ht_ctc_greetings_options' );
			$greetings_settings = get_option( 'ht_ctc_greetings_settings' );

			$show_effect = ( isset( $options['show_effect'] ) ) ? esc_attr( $options['show_effect'] ) : 'no-show-effects';
			$an_delay    = ( isset( $options['an_delay'] ) ) ? esc_attr( $options['an_delay'] ) : '';
			$an_itr      = ( isset( $options['an_itr'] ) ) ? esc_attr( $options['an_itr'] ) : '';

			$entry_effect_list = array(
				'no-show-effects' => '--No-Entry-Effects--',
				'From Center'     => 'Center (zoomIn)',
				'From Corner'     => 'Corner (corner of icon)', // js
			// // new
			// 'bounceIn' => 'bounceIn',
			// 'bounceInDown' => 'bounceInDown',
			// 'bounceInUP' => 'bounceInUP',
			// 'bounceInLeft' => 'bounceInLeft',
			// 'bounceInRight' => 'bounceInRight',
			// // 'bottomRight' => 'bottomRight', //add bounce effect
			);

			$an_type = ( isset( $options['an_type'] ) ) ? esc_attr( $options['an_type'] ) : '';

			$an_list = array(
				'no-animation' => '--No-Animation--',
				'bounce'       => 'Bounce',
				'flash'        => 'Flash',
				'pulse'        => 'Pulse',
				'heartBeat'    => 'HeartBeat',
				'flip'         => 'Flip',
			);

			$an_demo_class = ( '' === $an_type || 'no-animation' === $an_type ) ? 'ctc_init_display_none' : '';
			$ee_demo_class = ( '' === $show_effect || 'no-show-effects' === $show_effect ) ? 'ctc_init_display_none' : '';

			?>
		<ul class="collapsible ht_ctc_animations" data-collapsible="accordion" id="ht_ctc_animations">
		<li class="">
		<div class="collapsible-header"><?php esc_html_e( 'Animations', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<p class="description" style="margin-bottom:25px;"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/animations/"><?php esc_html_e( 'Animations', 'click-to-chat-for-whatsapp' ); ?></a></p>

		<!-- animation on load -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Animations', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_othersettings[an_type]" class="select_an_type">
				<?php

				foreach ( $an_list as $key => $value ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $an_type ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
					<?php
				}

				?>
				</select>
				<label><?php esc_html_e( 'Animations', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description ctc_an_demo_btn ctc_run_demo_btn <?php echo esc_attr( $an_demo_class ); ?>">Demo: Animate</p>
			</div>
		</div>

		<!-- animation delay -->
		<div class="row an_delay">
			<div class="col s6">
				<p><?php esc_html_e( 'Animation Delay', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[an_delay]" value="<?php echo esc_attr( $an_delay ); ?>" id="an_delay" type="number" min="0" class="" >
				<label for="an_delay"><?php esc_html_e( 'Animation Delay', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g. Add 1 for 1 second delay', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>

		<!-- animation iteration -->
		<div class="row an_itr">
			<div class="col s6">
				<p><?php esc_html_e( 'Animation Iteration', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[an_itr]" value="<?php echo esc_attr( $an_itr ); ?>" id="an_itr" type="number" min="1" class="" >
				<label for="an_itr"><?php esc_html_e( 'Animation Iteration', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g. Add 2 to repeat animation 2 times', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>

		<hr style="width: 50%;">
		<br><br>

		<!-- Show effect -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Entry Effects', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_othersettings[show_effect]" class="show_effect">
				<?php
				foreach ( $entry_effect_list as $key => $value ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $show_effect ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
					<?php
				}

				?>
				</select>
				<label><?php esc_html_e( 'Entrance Effects', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description ctc_ee_demo_btn ctc_run_demo_btn <?php echo esc_attr( $ee_demo_class ); ?>">Demo: Entry effect</p>
			</div>
		</div>

		</div>
		</li>
		</ul>


			<?php
			// notification Badge

			$notification_badge        = ( isset( $options['notification_badge'] ) ) ? 1 : '';
			$notification_count        = ( isset( $options['notification_count'] ) ) ? esc_attr( $options['notification_count'] ) : '1';
			$notification_bg_color     = ( isset( $options['notification_bg_color'] ) ) ? esc_attr( $options['notification_bg_color'] ) : '#ff4c4c';
			$notification_text_color   = ( isset( $options['notification_text_color'] ) ) ? esc_attr( $options['notification_text_color'] ) : '#ffffff';
			$notification_border_color = ( isset( $options['notification_border_color'] ) ) ? esc_attr( $options['notification_border_color'] ) : '';
			$notification_time         = ( isset( $options['notification_time'] ) ) ? esc_attr( $options['notification_time'] ) : '';
			?>

		<ul class="collapsible ht_ctc_notification" data-collapsible="accordion" id="ht_ctc_notification" style="margin-top: 2rem;">
		<li class="">
		<div class="collapsible-header"><?php esc_html_e( 'Notification Badge', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
		<p class="description" style="margin-bottom:25px;"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/notification-badge/"><?php esc_html_e( 'Notification Badge', 'click-to-chat-for-whatsapp' ); ?></a></p>

		<!-- notification_badge -->
		<div class="row ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Add Notification Badge', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="col s6">
				<label>
					<input class="notification_field notification_badge" name="<?php echo esc_attr( $dbrow ); ?>[notification_badge]" type="checkbox" value="1" <?php checked( $notification_badge, 1 ); ?> id="notification_badge" />
					<span><?php esc_html_e( 'Add Notification Badge', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
				<br>
			</div>
		</div>

		<!-- notification_count -->
		<div class="row notification_settings notification_count ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Notification Count', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[notification_count]" value="<?php echo esc_attr( $notification_count ); ?>" id="notification_count" type="number" min="0" class="notification_field field_notification_count" >
				<label for="notification_count"><?php esc_html_e( 'Notification Count', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- notification_bg_color -->
		<div class="row notification_settings notification_bg_color ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Badge Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color field_notification_bg_color" name="<?php echo esc_attr( $dbrow ); ?>[notification_bg_color]" data-default-color="#ff4c4c" value="<?php echo esc_attr( $notification_bg_color ); ?>" type="text" data-update-type='background-color' data-update-selector='.ctc_ad_badge'>
			</div>
		</div>

		<!-- notification_text_color -->
		<div class="row notification_settings notification_text_color ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color field_notification_text_color" name="<?php echo esc_attr( $dbrow ); ?>[notification_text_color]" data-default-color="#ffffff" value="<?php echo esc_attr( $notification_text_color ); ?>" type="text" data-update-type='color' data-update-selector='.ctc_ad_badge'>
			</div>
		</div>

		<!-- notification_border_color -->
		<div class="row notification_settings notification_border_color ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Add border Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6 notification_border_color_field">
				<input class="ht-ctc-color field_notification_border_color" name="<?php echo esc_attr( $dbrow ); ?>[notification_border_color]" value="<?php echo esc_attr( $notification_border_color ); ?>" type="text" data-update-type='border-color' data-update-selector='.ctc_ad_badge'>
			</div>
		</div>

		<!-- notification_time -->
		<div class="row notification_settings notification_time ctc_side_by_side">
			<div class="col s6">
				<p><?php esc_html_e( 'Badge Time Delay', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[notification_time]" value="<?php echo esc_attr( $notification_time ); ?>" id="notification_time" type="number" min="0" class="notification_field field_notification_time" >
				<label for="notification_time"><?php esc_html_e( 'Time in seconds', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<div class="row notification_settings">
			<p class="description" style="font-style:italic;">Notification badge will display until the first time user clicks to open chat or the greetings dialog.</p>
			<?php
			$greetings_template = ( isset( $greetings['greetings_template'] ) ) ? esc_attr( $greetings['greetings_template'] ) : '';
			$g_init             = isset( $greetings_settings['g_init'] ) ? esc_attr( $greetings_settings['g_init'] ) : '';
			if ( ( '' !== $greetings_template || 'no' !== $greetings_template ) && 'open' === $g_init ) {
				$greetings_page_url = admin_url( 'admin.php?page=click-to-chat-greetings' );
				?>
				<p class="description" style="color:#ff4c4c;">If the <a href="<?php echo esc_url( $greetings_page_url . '#g_init:~:text=initial%20stage' ); ?>" target="_blank">Greetings dialog initial stage is open</a>, the notification badge cannot be displayed.</p>
				<?php
			}
			?>
		</div>

		</div>
		</li>
		</ul>

			<?php
		}

		/**
		 * Other settings
		 *  detect device
		 */
		public function ht_ctc_othersettings_cb() {

			$options      = get_option( 'ht_ctc_othersettings' );
			$chat_options = get_option( 'ht_ctc_chat_options' );
			$dbrow        = 'ht_ctc_othersettings';

			$aria   = ( isset( $options['aria'] ) ) ? 1 : '';
			$zindex = ( isset( $options['zindex'] ) ) ? esc_attr( $options['zindex'] ) : '99999999';

			// start other settings
			do_action( 'ht_ctc_ah_admin_start_os' );

			$li_active_gr_sh = ( isset( $options['enable_group'] ) || isset( $options['enable_share'] ) ) ? "class='active'" : '';

			?>


		<p class="description"><?php esc_html_e( 'All these below settings are not important to everyone', 'click-to-chat-for-whatsapp' ); ?></p>
		<ul class="collapsible ht_ctc_other_settings" data-collapsible="accordion" id="ht_ctc_othersettings">
		<li class="">
		<div class="collapsible-header">Advanced Settings
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- z-index -->
		<div class="row ctc_side_by_side">
			<div class="col s6">
				<p class="description"><?php esc_html_e( 'z-index', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[zindex]" value="<?php echo esc_attr( $zindex ); ?>" min="0" id="zindex" type="number">
				<label for="zindex"><?php esc_html_e( 'z-index', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'z-index value for the chat widget to ensure proper stacking and visibility', 'click-to-chat-for-whatsapp' ); ?><a href="https://holithemes.com/plugins/click-to-chat/z-index/" target="_blank"> - more info</a></p>
			</div>
		</div>        

		<!-- aria -->
		<div class="row ctc_side_by_side">
			<div class="col s6">
				<p class="description"><?php esc_html_e( 'Add aria-hidden=true', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="col s6">
				<label class="ctc_checkbox_label">
					<input name="<?php echo esc_attr( $dbrow ); ?>[aria]" type="checkbox" value="1" <?php checked( $aria, 1 ); ?> id="aria" />
					<span><?php esc_html_e( 'Add aria-hidden=true', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
				<p class="description"><?php esc_html_e( 'hide for Accessibility API (screen readers)', 'click-to-chat-for-whatsapp' ); ?></p>
				<br>
			</div>
		</div>


			<?php
			// webhook data Format
			$webhook_format_list = array(
				'string' => 'String (Stringify JSON)',
				'json'   => 'JSON',
			);

			$webhook_format = ( isset( $options['webhook_format'] ) ) ? esc_attr( $options['webhook_format'] ) : 'json';
			?>

		<div class="row ctc_side_by_side">
			<div class="col s6">
				<p class="description">Webhook data format</p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_othersettings[webhook_format]" class="select_webhook_format" style="border:unset; background-color:inherit;">
					<?php
					foreach ( $webhook_format_list as $key => $value ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $webhook_format ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
				<label>Webhook data format</label>
				<p class="description">JSON works. If any application need to change - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/webhook-data-format/">more info</a></p>
			</div>
		</div>


			<?php
			// hook
			// in other settings
			do_action( 'ht_ctc_ah_admin_in_os' );
			?>
		</div>
		</li>
		</ul>
		<br>

		<!-- enable group, share features -->
		<ul class="collapsible ht_ctc_enable_share_group" data-collapsible="accordion" id="ht_ctc_enable_share_group">
		<li <?php echo esc_attr( $li_active_gr_sh ); ?>>
		<div class="collapsible-header"><?php esc_html_e( 'Group, Share features', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
		
			<?php

			// enable group
			if ( isset( $options['enable_group'] ) ) {
				?>
		<p>
			<label class="ctc_checkbox_label">
				<input name="ht_ctc_othersettings[enable_group]" type="checkbox" value="1" <?php checked( $options['enable_group'], 1 ); ?> id="enable_group" />
				<span><?php esc_html_e( 'Enable Group Features', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
			<p class="description"> <?php esc_html_e( 'Adds WhatsApp Icon for Group', 'click-to-chat-for-whatsapp' ); ?> - <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-group-feature' ) ); ?>"><?php esc_html_e( 'Group Settings page', 'click-to-chat-for-whatsapp' ); ?></a> </p>
		</p>
				<?php
			} else {
				?>
			<p>
				<label class="ctc_checkbox_label"  >
					<input name="ht_ctc_othersettings[enable_group]" type="checkbox" value="1" id="enable_group" />
					<span><?php esc_html_e( 'Enable Group Features', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
			<p class="description"> <?php esc_html_e( 'Adds WhatsApp Icon for Group', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/enable-group-feature/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
				<?php
			}
			?>
		<br>
			<?php

			// enable share
			if ( isset( $options['enable_share'] ) ) {
				?>
		<p>
			<label class="ctc_checkbox_label">
				<input name="ht_ctc_othersettings[enable_share]" type="checkbox" value="1" <?php checked( $options['enable_share'], 1 ); ?> id="enable_share" />
				<span><?php esc_html_e( 'Enable Share Features', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
			<p class="description"> <?php esc_html_e( 'Adds WhatsApp Icon for Share', 'click-to-chat-for-whatsapp' ); ?> - <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-share-feature' ) ); ?>"><?php esc_html_e( 'Share Settings page', 'click-to-chat-for-whatsapp' ); ?></a> </p>
		</p>
				<?php
			} else {
				?>
			<p>
				<label class="ctc_checkbox_label">
					<input name="ht_ctc_othersettings[enable_share]" type="checkbox" value="1" id="enable_share" />
					<span><?php esc_html_e( 'Enable Share Features', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
			<p class="description"> <?php esc_html_e( 'Adds WhatsApp Icon for Share', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/enable-share-feature/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
				<?php
			}
			?>
		<br>
		
		<!-- chat -->
		<p class="description"><?php esc_html_e( 'Chat settings are enabled by default. If like to hide chat on all pages', 'click-to-chat-for-whatsapp' ); ?></p>
		<p class="description"><?php esc_html_e( "'Click to Chat' - 'Display Settings' - 'Global' - check ", 'click-to-chat-for-whatsapp' ); ?> <a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat#showhide_settings' ) ); ?>"><?php esc_html_e( 'Hide on all pages', 'click-to-chat-for-whatsapp' ); ?></a> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/enable-chat"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>
		<br>


		</div>
		</li>
		</ul>

		<br>

		<!-- Troubleshoot, Debug, ..  -->
		<ul class="collapsible ht_ctc_debug" data-collapsible="accordion" id="ht_ctc_debug">
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Debug, Troubleshoot, ..', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">
			<?php

			/**
			 * AMP Compatibility - enabled by default.  (if an issue uncheck this..)
			 * later version remove this option and make enable by default..
			 * if amp related issue, uncheck this option
			 */

			$amp_checkbox = ( isset( $options['amp'] ) ) ? esc_attr( $options['amp'] ) : '';

			if ( function_exists( 'amp_is_request' ) ) {
				?>
			<p id="amp_compatibility">
				<label>
					<input name="<?php echo esc_attr( $dbrow ); ?>[amp]" type="checkbox" value="1" <?php checked( $amp_checkbox, 1 ); ?> id="amp" />
					<span><?php esc_html_e( 'AMP Compatibility', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
			<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/amp-compatibility/"><?php esc_html_e( 'AMP Compatibility', 'click-to-chat-for-whatsapp' ); ?></a> If any issue, uncheck this option and please contact us</p>
			<br>
				<?php
			} else {
				// if amp is activated after this settings.
				?>
			<label style="display: none;">
				<input name="<?php echo esc_attr( $dbrow ); ?>[amp]" type="checkbox" value="1" <?php checked( $amp_checkbox, 1 ); ?> id="amp" />
				<span><?php esc_html_e( 'AMP Compatibility', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
				<?php
			}

			$chat_load_hook = ( isset( $options['chat_load_hook'] ) ) ? esc_attr( $options['chat_load_hook'] ) : '';
			$js_load        = ( isset( $options['js_load'] ) ) ? esc_attr( $options['js_load'] ) : 'defer';
			?>

		<p class="description">
			<ol style="list-style-type: disc;">
				<li class="ctc_debug_list_item">Basic Troubleshoot
					<ol style="list-style-type: none;">
						<li class="ctc_debug_list_item">Clear Cache: Cache plugins, Server side, CDN cache (if available)</li>
						<li class="ctc_debug_list_item">Check display settings</li>
					</ol>
				</li>
					<li class="ctc_debug_list_item"><p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/faq"><?php esc_html_e( 'FAQ', 'click-to-chat-for-whatsapp' ); ?> (<?php esc_html_e( 'Frequently Asked Questions', 'click-to-chat-for-whatsapp' ); ?>)</a></p></li>
				</li>
			</ol>
		</p>
		<!-- <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/link/">Basic Troubleshooting</a></p> -->
		<br>
		<hr>
		<details class="ctc_details">
			<summary style="cursor:pointer;">Chat load hook</summary>
			<div class="m_side_15 m_top_5">
				<!-- chat load hook -->
				<div class="row ctc_side_by_side">
					<div class="col s6">
					<p class="description"><?php esc_html_e( 'Chat load hook', 'click-to-chat-for-whatsapp' ); ?></p>
					</div>
					<div class="input-field col s6">
						<select name="<?php echo esc_attr( $dbrow ); ?>[chat_load_hook]" class="chat_load_hook">
								<option value="wp_footer" <?php echo ( 'wp_footer' === $chat_load_hook ) ? 'SELECTED' : ''; ?> >wp_footer</option>
								<option value="get_footer" <?php echo ( 'get_footer' === $chat_load_hook ) ? 'SELECTED' : ''; ?> >get_footer</option>
								<option value="wp_head" <?php echo ( 'wp_head' === $chat_load_hook ) ? 'SELECTED' : ''; ?> >wp_head</option>
						</select>
						<label>Chat load hook</label>
						<p class="description">If the chat widget is not working with the wp_footer hook, change to get_footer or wp_head - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/chat-load-hook/">more info</a></p>
					</div>
				</div>
			</div>
		</details>

		<details class="ctc_details">
			<summary style="cursor:pointer;">JavaScript</summary>
			<div class="m_side_15 m_top_5">
				<!-- JavaScript load -->
				<div class="row ctc_side_by_side">
					<div class="col s6">
					<p class="description"><?php esc_html_e( 'Load JavaScript', 'click-to-chat-for-whatsapp' ); ?></p>
					</div>
					<div class="input-field col s6">
						<select name="<?php echo esc_attr( $dbrow ); ?>[js_load]" class="js_load">
								<option value="" <?php echo ( 'normal' === $js_load ) ? 'SELECTED' : ''; ?> >Normal</option>
								<option value="async" <?php echo ( 'async' === $js_load ) ? 'SELECTED' : ''; ?> >Async</option>
								<option value="defer" <?php echo ( 'defer' === $js_load ) ? 'SELECTED' : ''; ?> >Defer</option>
						</select>
						<label>JavaScript</label>
						<p class="description">Async: load js files asynchronously, Defer: load asynchronously and execute after the DOM is loaded - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/docs/load-javascript-files/">more info</a></p>
					</div>
				</div>
			</div>
		</details>

			<?php
			// disable page level settings
			$disable_page_level_settings_checkbox = ( isset( $options['disable_page_level_settings'] ) ) ? esc_attr( $options['disable_page_level_settings'] ) : '';
			?>

		<details class="ctc_details">
			<summary style="cursor:pointer;">Disable Page level settings</summary>
			<div class="m_side_15">
				<p class="description">If checked, this will disable the ability to configure chat settings for individual pages.</p>
				<p style="margin-bottom:12px;">
					<label>
						<input name="ht_ctc_othersettings[disable_page_level_settings]" type="checkbox" value="1" <?php checked( $disable_page_level_settings_checkbox, 1 ); ?> id="disable_page_level_settings" />
						<span>Disable Page level settings</span>
					</label>
				</p>
			</div>
		</details>

			<?php
			$no_intl_checkbox = ( isset( $options['no-intl'] ) ) ? esc_attr( $options['no-intl'] ) : '';
			?>
		<details class="ctc_details">
			<summary style="cursor:pointer;">WhatsApp number not saving</summary>
			<div class="m_side_15">
				<p class="description">If WhatsApp number is not saved at admin side, disable the initl input library and add WhatsApp number</p>
				<p style="margin-bottom:12px;">
					<label>
						<input name="<?php echo esc_attr( $dbrow ); ?>[no-intl]" type="checkbox" value="1" <?php checked( $no_intl_checkbox, 1 ); ?> id="no-intl" />
						<span>Disable Initl input library</span>
					</label>
				</p>
			</div>
		</details>

		<details class="ctc_details">
			<summary style="cursor:pointer;">Delete settings</summary>
			<div class="m_side_15">
				<?php
				// delete options
				if ( isset( $options['delete_options'] ) ) {
					?>
					<p>
						<label>
							<input name="ht_ctc_othersettings[delete_options]" type="checkbox" value="1" <?php checked( $options['delete_options'], 1 ); ?> id="delete_options"   />
							<span><?php esc_html_e( 'Delete this plugin settings when uninstalls', 'click-to-chat-for-whatsapp' ); ?></span>
						</label>
					</p>
					<?php
				} else {
					?>
					<p>
						<label>
							<input name="ht_ctc_othersettings[delete_options]" type="checkbox" value="1" id="delete_options"   />
							<span><?php esc_html_e( 'Delete this plugin settings when uninstalls', 'click-to-chat-for-whatsapp' ); ?></span>
						</label>
					</p>
					<?php
				}
				?>
			</div>
		</details>

		<br>
		<p class="description">Any issues related to the Click to Chat plugin? Please <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/support">contact us</a>.</p>

		</div>
		</li>
		</ul>

		

			<?php
		}


		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys.
		 */
		public function options_sanitize( $input ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'not allowed to modify - please contact admin ' );
			}

			// to sanitize the input. custom css, ..
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/ht-ctc-admin-formatting.php';

			$new_input = array();

			foreach ( $input as $key => $value ) {

				if ( is_array( $input[ $key ] ) ) {
					if ( function_exists( 'sanitize_textarea_field' ) ) {
						$new_input[ $key ] = map_deep( $input[ $key ], 'sanitize_textarea_field' );
					} else {
						$new_input[ $key ] = map_deep( $input[ $key ], 'sanitize_text_field' );
					}
				} elseif ( 'placeholder' === $key ) {

					if ( function_exists( 'sanitize_textarea_field' ) ) {
						$new_input[ $key ] = sanitize_textarea_field( $input[ $key ] );
					} else {
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
					}
				} elseif ( 'custom_css' === $key ) {
					if ( function_exists( 'ht_ctc_sanitize_custom_css_code' ) ) {
						$new_input[ $key ] = ht_ctc_sanitize_custom_css_code( $input[ $key ] );
					}
				} elseif ( isset( $input[ $key ] ) ) {
					// $new_input[$key] = sanitize_text_field( $input[$key] );
					if ( function_exists( 'sanitize_textarea_field' ) ) {
						$new_input[ $key ] = sanitize_textarea_field( $input[ $key ] );
					} else {
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
					}
				}
			}

			do_action( 'ht_ctc_ah_admin_after_sanitize' );

			return $new_input;
		}
	}

	$ht_ctc_admin_other_settings = new HT_CTC_Admin_Other_Settings();

	add_action( 'admin_menu', array( $ht_ctc_admin_other_settings, 'menu' ) );
	add_action( 'admin_init', array( $ht_ctc_admin_other_settings, 'settings' ) );

} // END class_exists check
