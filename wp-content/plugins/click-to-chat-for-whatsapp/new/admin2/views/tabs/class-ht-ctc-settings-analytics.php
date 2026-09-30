<?php
/**
 * Analytics Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Analytics' ) ) {

	/**
	 * Analytics settings class.
	 */
	class HT_CTC_Settings_Analytics {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// Google Analytics
			$fields[] = self::card_google_analytics();

			// Google Tag Manager
			$fields[] = self::card_google_tag_manager();

			// Meta Pixel
			$fields[] = self::card_meta_pixel();

			// Meta Conversions API
			$fields[] = self::card_meta_conversion_api();

			// Google Ads Conversion
			$fields[] = self::card_google_ads_conversion();

			// Webhooks
			$fields[] = self::card_webhooks();

			// Analytics Settings
			$fields[] = self::card_analytics_settings();

			return $fields;
		}

		/**
		 * Google Analytics Card
		 */
		private static function card_google_analytics() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Google Analytics', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Track WhatsApp clicks with Google Analytics',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'g_an',
						'label'        => __( 'Google Analytics', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'If Google Analytics installed creates an Event there', 'click-to-chat-for-whatsapp' ) . ' - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/google-analytics/' ) ) . '">' . __( 'more info', 'click-to-chat-for-whatsapp' ) . '</a>',
						'value'        => 'ga4',
						'option_group' => 'ht_ctc_othersettings',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'g_an_event_name',
						'label'          => __( 'Event Name', 'click-to-chat-for-whatsapp' ),
						'placeholder'    => 'click to chat',
						'default'        => 'click to chat',
						'option_group'   => 'ht_ctc_othersettings',
						'data_watch'     => '#g_an',
						'data_show_when' => 'ga4',
					),
					// array(
					// 'field_type'     => 'block_container',
					// 'class_pr'       => 'ctc-group-sync ctc_key_value ctc_g_an_params ctc_sortable',
					// 'data_remove'    => 'ht_ctc_othersettings[g_an_params]',
					// 'data_watch'     => '#g_an',
					// 'data_show_when' => 'ga4,ga,1',
					// ),
					// array(
					// 'field_type' => 'section_google_analytics_params',
					// 'data_watch'     => '#g_an',
					// 'data_show_when' => 'ga4,ga,1',
					// ),
					array(
						'field_type'              => 'block_button_add',
						'label'                   => 'Add Parameter',
						'button_class'            => 'ctc_add_g_an_param_button',
						'data_callback'           => 'ga',
						'data_callback_container' => '.ctc_g_an_params',
						'container_class'         => 'ctc-group-sync ctc_key_value ctc_g_an_params ctc_sortable',
						'data_remove'             => 'ht_ctc_othersettings[g_an_params]',
						'data_watch'              => '#g_an',
						'data_show_when'          => 'ga4,ga,1',
					),
					array(
						'field_type' => 'section_google_analytics_params',
						// 'data_watch'     => '#g_an',
						// 'data_show_when' => 'ga4,ga,1',
					),
					self::analytics_variables_reference( '#g_an', 'ga4,ga,1' ),
					array(
						'field_type'     => 'block_accordion',
						'title'          => 'PRO: Values from Cookies & URL Parameters',
						'fields'         => self::pro_syntax_fields(),
						'data_watch'     => '#g_an',
						'data_show_when' => 'ga4,ga,1',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_google_analytics', $values );
			return $values;
		}

		/**
		 * Google Tag Manager Card
		 */
		private static function card_google_tag_manager() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Google Tag Manager', 'click-to-chat-for-whatsapp' ),
				'description'    => __( 'Pushes a dataLayer event for GTM triggers.', 'click-to-chat-for-whatsapp' ),
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'gtm',
						'element_id'   => 'google_tag_manager',
						'label'        => __( 'Google Tag Manager', 'click-to-chat-for-whatsapp' ),
						'help'         => vsprintf(
							'%1$s - <a target="_blank" href="%2$s">%3$s</a><br>',
							array(
								__( 'Create Event from Google Tag manager (GTM)', 'click-to-chat-for-whatsapp' ),
								esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/create-event-from-google-tag-manager-using-datalayer-send-to-google-analytics/' ) ),
								__( 'dataLayer', 'click-to-chat-for-whatsapp' ),
							)
						),
						'value'        => '1',
						'option_group' => 'ht_ctc_othersettings',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'gtm_event_name',
						'label'          => __( 'Event Name', 'click-to-chat-for-whatsapp' ),
						'placeholder'    => 'click to chat',
						'default'        => 'Click to Chat',
						'option_group'   => 'ht_ctc_othersettings',
						'data_watch'     => '#gtm',
						'data_show_when' => '1',
					),
					// array(
					// 'field_type'     => 'block_container',
					// 'class_pr'       => 'ctc-group-sync ctc_key_value ctc_gtm_params ctc_sortable',
					// 'data_remove'    => 'ht_ctc_othersettings[gtm_params]',
					// 'data_watch'     => '#gtm',
					// 'data_show_when' => '1',
					// ),
					// array(
					// 'field_type'     => 'section_google_tag_manager_params',
					// 'data_watch'     => '#gtm',
					// 'data_show_when' => '1',
					// ),
					array(
						'field_type'              => 'block_button_add',
						'label'                   => 'Add Parameter',
						'button_class'            => 'ctc_add_gtm_param_button',
						'data_callback'           => 'gtm',
						'data_callback_container' => '.ctc_gtm_params',
						'container_class'         => 'ctc-group-sync ctc_key_value ctc_gtm_params ctc_sortable',
						'data_remove'             => 'ht_ctc_othersettings[gtm_params]',
						'data_watch'              => '#gtm',
						'data_show_when'          => '1',
					),
					array(
						'field_type'     => 'section_google_tag_manager_params',
						'data_watch'     => '#gtm',
						'data_show_when' => '1',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_google_tag_manager', $values );
			return $values;
		}

		/**
		 * Meta Pixel Card
		 */
		private static function card_meta_pixel() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Meta Pixel', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Track WhatsApp clicks with Meta Pixel',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'fb_pixel',
						'label'        => __( 'Meta Pixel', 'click-to-chat-for-whatsapp' ),
						'help'         => vsprintf(
							'%1$s - <a target="_blank" href="%2$s">%3$s</a><br>',
							array(
								__( 'If Meta Pixel installed creates an Event there', 'click-to-chat-for-whatsapp' ),
								esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/facebook-pixel/' ) ),
								__( 'more info', 'click-to-chat-for-whatsapp' ),
							)
						),
						'value'        => '1',
						'option_group' => 'ht_ctc_othersettings',
					),
					array(
						'field_type'     => 'field_select',
						'id'             => 'pixel_event_type',
						'label'          => __( 'Event Type', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'data_watch'     => '#fb_pixel',
						'data_show_when' => '1',
						'options'        => array(
							'trackCustom' => 'Custom Event',
							'track'       => 'Standard',
						),
						'default'        => 'trackCustom',
					),
					array(
						'field_type'     => 'block_group',
						'data_watch'     => '#fb_pixel',
						'data_show_when' => '1',
						'fields'         => array(
							array(
								'field_type'     => 'field_text',
								'id'             => 'pixel_custom_event_name',
								'label'          => __( 'Custom Event Name', 'click-to-chat-for-whatsapp' ),
								'option_group'   => 'ht_ctc_othersettings',
								'placeholder'    => 'Click to Chat by HoliThemes',
								'default'        => 'Click to Chat by HoliThemes',
								'data_watch'     => '#pixel_event_type',
								'data_show_when' => 'trackCustom',
							),
							array(
								'field_type'     => 'field_select',
								'id'             => 'pixel_standard_event_name',
								'label'          => 'Standard Event',
								'option_group'   => 'ht_ctc_othersettings',
								'data_watch'     => '#pixel_event_type',
								'data_show_when' => 'track',
								'options'        => array(
									'Lead'        => 'Lead',
									'Contact'     => 'Contact',
									'Purchase'    => 'Purchase',
									'Schedule'    => 'Schedule',
									'Subscribe'   => 'Subscribe',
									'ViewContent' => 'ViewContent',
								),
								'default'        => 'Contact',
							),
						),
					),

					// Parameters Container
					// array(
					// 'field_type'     => 'block_container',
					// 'class_pr'       => 'ctc-group-sync ctc_key_value ctc_pixel_params ctc_sortable',
					// 'data_remove'    => 'ht_ctc_othersettings[pixel_params]',
					// 'data_watch'     => '#fb_pixel',
					// 'data_show_when' => '1',
					// ),
					// array(
					// 'field_type'     => 'section_pixel_analytics_params',
					// 'data_watch'     => '#fb_pixel',
					// 'data_show_when' => '1',
					// ),
					array(
						'field_type'              => 'block_button_add',
						'label'                   => 'Add Parameter',
						'button_class'            => 'ctc_add_pixel_param_button',
						'data_callback'           => 'pixel',
						'data_callback_container' => '.ctc_pixel_params',
						'container_class'         => 'ctc-group-sync ctc_key_value ctc_pixel_params ctc_sortable',
						'data_remove'             => 'ht_ctc_othersettings[pixel_params]',
						'data_watch'              => '#fb_pixel',
						'data_show_when'          => '1',
					),
					array(
						'field_type'     => 'section_pixel_analytics_params',
						'data_watch'     => '#fb_pixel',
						'data_show_when' => '1',
					),
					self::analytics_variables_reference( '#fb_pixel', '1' ),
					array(
						'field_type'     => 'block_accordion',
						'title'          => 'PRO: Values from Cookies & URL Parameters',
						'fields'         => self::pro_syntax_fields(),
						'data_watch'     => '#fb_pixel',
						'data_show_when' => '1',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_meta_pixel', $values );
			return $values;
		}

		/**
		 * Meta Conversions API Card
		 */
		private static function card_meta_conversion_api() {
			$values = array(
				'field_type'  => 'card',
				'title'       => 'Meta Conversions API',
				'description' => 'Send Widget clicks to Meta from your server, so they still count when the browser pixel is blocked',
				'fields'      => array(),
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$values['fields'][] = array(
					'field_type'  => 'block_pro_feature',
					'icon'        => 'dashicons dashicons-facebook',
					'title'       => 'Meta Conversions API',
					'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
					'description' => 'Send each click to Meta from your own server — pixel ID, access token and a test event code — so ad blockers and cookie limits stop costing you conversions.',
					'button_text' => 'Learn more',
					'url'         => HT_CTC_Utils::pro_url( 'teaser', 'meta_capi' ),
				);
			}
			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_meta_conversion_api', $values );
			return $values;
		}

		/*
		 * Example for Pro Addon:
		 *
		 * public function hooks() {
		 *     add_filter( 'ht_ctc_fh_settings_fields_analytics_meta_conversion_api', array( $this, 'meta_conversion_api' ) );
		 * }
		 *
		 * public function meta_conversion_api( $values ) {
		 *     $values['fields'][] = array(
		 *         'field_type' => 'field_text',
		 *         'label'      => 'Access Token',
		 *     );
		 *     return $values;
		 * }
		 */

		/**
		 * Google Ads Conversion Card
		 */
		private static function card_google_ads_conversion() {
			$values = array(
				'field_type'  => 'card',
				'title'       => __( 'Google Ads Conversion', 'click-to-chat-for-whatsapp' ),
				'description' => 'Track conversions in Google Ads',
				'fields'      => array(),
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$values['fields'][] = array(
					'field_type'  => 'block_pro_feature',
					'icon'        => 'dashicons dashicons-chart-bar',
					'title'       => __( 'Google Ads Conversion', 'click-to-chat-for-whatsapp' ),
					'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
					'description' => 'Send a conversion with your conversion ID and label whenever a visitor clicks to chat.',
					'button_text' => 'Learn more',
					'url'         => HT_CTC_Utils::pro_url( 'teaser', 'google_ads', 'https://holithemes.com/plugins/click-to-chat/google-ads-conversion/' ),
				);
			}

			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_google_ads_conversion', $values );
			return $values;
		}

		/**
		 * Webhooks Card
		 */
		private static function card_webhooks() {
			$values = array(
				'field_type'  => 'card',
				'title'       => __( 'Webhooks', 'click-to-chat-for-whatsapp' ),
				'description' => '',
				'fields'      => array(
					array(
						'field_type' => 'block_content',
						'content'    => vsprintf(
							'<p class="description" style="margin-bottom: 20px;">%1$s %2$s <a target="_blank" href="%3$s">%4$s</a></p>',
							array(
								__( 'Integrate, Automation', 'click-to-chat-for-whatsapp' ),
								__( 'using', 'click-to-chat-for-whatsapp' ),
								esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/webhooks/' ) ),
								__( 'Webhooks', 'click-to-chat-for-whatsapp' ),
							)
						),
					),
					array(
						'field_type' => 'block_content',
						'content'    => '<p class="description" style="margin:10px 0px;">To get the greetings form data, use the <a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/greetings-form#webhooks' ) ) . '" target="_blank">Greetings Form webhook</a> feature.</p>',
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 'hook_url',
						'label'        => __( 'Webhook URL', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'placeholder'  => 'https://example.com/webhook',
						'help'         => __( 'Clicking on the WhatsApp widget triggers this Webhook URL', 'click-to-chat-for-whatsapp' ),
					),
					// array(
					// 'field_type'  => 'block_container',
					// 'class_pr'    => 'ctc-group-sync ctc_value_only ctc_hook_v_params ctc_sortable',
					// 'data_remove' => 'ht_ctc_othersettings[hook_v]',
					// ),
					// array(
					// 'field_type' => 'section_webhooks_params',
					// ),
					array(
						'field_type'              => 'block_button_add',
						'label'                   => __( 'Add Value', 'click-to-chat-for-whatsapp' ),
						'button_class'            => 'ctc_add_hook_v_param_button',
						'data_callback'           => 'hook_v',
						'data_callback_container' => '.ctc_hook_v_params',
						'container_class'         => 'ctc-group-sync ctc_value_only ctc_hook_v_params ctc_sortable',
						'data_remove'             => 'ht_ctc_othersettings[hook_v]',
					),
					array(
						'field_type' => 'section_webhooks_params',
					),
					self::webhooks_variables_reference(),
					array(
						'field_type' => 'block_accordion',
						'title'      => 'PRO: Values from Cookies & URL Parameters',
						'fields'     => self::pro_syntax_fields(),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'webhook_format',
						'label'        => 'Webhook data format',
						'option_group' => 'ht_ctc_othersettings',
						'options'      => array(
							'json'   => 'JSON',
							'string' => 'String (Stringify JSON)',
						),
						'default'      => 'json',
						'help'         => 'JSON works. If any application need to change',
						// 'help'         => 'Select the data format for the webhook payload. Defaults to JSON.',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_webhooks', $values );
			return $values;
		}

		/**
		 * Analytics Settings Card
		 */
		private static function card_analytics_settings() {
			$values = array(
				'field_type' => 'card',
				'title'      => __( 'Analytics', 'click-to-chat-for-whatsapp' ),
				'fields'     => array(
					array(
						'field_type'   => 'field_select',
						'id'           => 'analytics',
						'label'        => 'Analytics Count',
						'option_group' => 'ht_ctc_othersettings',
						'options'      => array(
							'all'     => 'All Clicks',
							'session' => 'One click per session',
						),
						'default'      => 'all',
						'help'         => '<a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/analytics-count/' ) ) . '">Analytics Count</a>',
					),
					array(
						'field_type'  => 'block_feature_box',
						'class_pr'    => 'custom-tracking',
						'icon'        => 'dashicons-chart-line',
						'label'       => 'Custom Click Tracking',
						'badge'       => 'Info',
						'badge_class' => 'interaction',
						'link'        => array(
							'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/analytics/' ),
							'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
						),
						'content'     => '<p class="ctc-feature-text">'
							. sprintf(
								'To track clicks using third-party analytics or custom tracking scripts, target the class name %1$s (added to all click surfaces) or the parent container %2$s.',
								'<code class="ctc-feature-code">ctc-analytics</code>',
								'<code class="ctc-feature-code">ht-ctc-chat</code>'
							)
							. '</p>'
							. '<p class="ctc-feature-text">'
							. sprintf(
								'Some analytics tools record clicks only on link or button elements. If your tool works that way, you can change the element used for the main click surfaces at %1$s.',
								'<a href="#advanced-settings/chat_wrapper_tag" class="ctc-shortcut-link">' . __( 'Advanced', 'click-to-chat-for-whatsapp' ) . ' > Debug, Troubleshoot > <b>Click Tracking Compatibility</b> <span class="dashicons dashicons-arrow-right-alt2"></span></a>'
							)
							. '</p>',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_analytics_settings', $values );
			return $values;
		}

		/**
		 * Free variables reference (GA, Pixel) — click-to-copy tiles.
		 *
		 * Visibility is passed in because the same reference is shown under
		 * different toggles (GA's #g_an, Pixel's #fb_pixel).
		 *
		 * @param string $watch     Selector the field's visibility follows (e.g. '#g_an').
		 * @param string $show_when Value(s) of $watch that reveal the field (e.g. 'ga4,ga,1').
		 * @return array block_variables field definition.
		 */
		private static function analytics_variables_reference( $watch, $show_when ) {
			return array(
				'field_type'     => 'block_variables',
				'title'          => 'Variables',
				'variables'      => array(
					'{title}'  => 'Page title',
					'{url}'    => 'Page URL',
					'{number}' => 'Admin number',
				),
				'data_watch'     => $watch,
				'data_show_when' => $show_when,
			);
		}

		/**
		 * Webhooks dynamic variables reference block.
		 *
		 * Provides the block_variables field definition for webhooks.
		 * Displays PRO teaser variables.
		 *
		 * @return array block_variables field definition.
		 */
		private static function webhooks_variables_reference() {
			$values = array(
				'field_type' => 'block_variables',
				'title'      => 'Variables',
				'variables'  => array(),
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$values['badge']         = __( 'PRO', 'click-to-chat-for-whatsapp' );
				$values['pro_variables'] = array(
					'{number}' => 'Admin number',
					'{url}'    => 'Page URL',
					'{time}'   => 'Click time',
					'{title}'  => 'Page title',
				);
			}

			$values = apply_filters( 'ht_ctc_fh_webhooks_variables_reference', $values );

			return $values;
		}

		/**
		 * PRO accordion body: fill-in-the-blank syntax for pulling values from URL
		 * parameters ([ ]) and cookies ([[ ]]). Shared by GA, Pixel, and Webhooks.
		 *
		 * Rendered as two feature boxes (reusing block_feature_box, like the
		 * "Custom Element" feature) laid out in a responsive grid — each with a
		 * PRO badge, a docs link, a description, and Syntax / e.g. spec rows.
		 *
		 * @return array Nested fields for the accordion to render.
		 */
		private static function pro_syntax_fields() {
			return array(
				array(
					'field_type' => 'block_group',
					'class_pr'   => 'ctc-feature-grid',
					'fields'     => array(
						array(
							'field_type'  => 'block_feature_box',
							'icon'        => 'link',
							'label'       => 'URL Parameter',
							'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
							'badge_class' => 'pro',
							// todo: confirm the docs URL for URL-parameter values.
							'link'        => array(
								'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/analytics/' ),
								'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
							),
							'content'     => '<p class="ctc-feature-text">'
								. 'Reads a value from the page URL\'s query string. Blank if the parameter is missing.'
								. '</p>' . self::feature_meta( '[parameter]', array( '[gclid]', '[utm_source]' ) ),
						),
						array(
							'field_type'  => 'block_feature_box',
							'icon'        => 'cookie',
							'label'       => 'Cookie Value',
							'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
							'badge_class' => 'pro',
							// todo: confirm the docs URL for cookie values.
							'link'        => array(
								'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/analytics/' ),
								'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
							),
							'content'     => '<p class="ctc-feature-text">'
								. 'Reads a value from a browser cookie. Blank if the cookie is missing.'
								. '</p>' . self::feature_meta( '[[cookie]]', array( '[[_ga]]' ) ),
						),
					),
				),
			);
		}

		/**
		 * Build the "Syntax" / "e.g." spec rows for a syntax feature box,
		 * matching the Custom Element feature's meta-row layout.
		 *
		 * @param string $syntax   Schematic syntax, e.g. '[parameter]'.
		 * @param array  $examples Concrete example strings, e.g. array( '[gclid]' ).
		 * @return string HTML for the .ctc-feature-meta block.
		 */
		private static function feature_meta( $syntax, $examples ) {
			$example_codes = '';
			foreach ( $examples as $example ) {
				$example_codes .= '<code class="ctc-feature-code">' . esc_html( $example ) . '</code>';
			}

			return '<div class="ctc-feature-meta">'
				. '<div class="ctc-feature-meta-row"><span class="ctc-feature-meta-key">Syntax</span><code class="ctc-feature-code">' . esc_html( $syntax ) . '</code></div>'
				. '<div class="ctc-feature-meta-row"><span class="ctc-feature-meta-key">' . esc_html__( 'E.g.', 'click-to-chat-for-whatsapp' ) . '</span>' . $example_codes . '</div>'
				. '</div>';
		}
	}
}
