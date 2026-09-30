<?php
/**
 * Advanced Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Advanced' ) ) {

	/**
	 * Advanced settings class.
	 */
	class HT_CTC_Settings_Advanced {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// Animations
			$fields[] = self::card_animations();

			// Notification Badge
			$fields[] = self::card_notification_badge();

			// Custom CSS
			$fields[] = self::card_custom_css();

			// Advanced Settings
			$fields[] = self::card_advanced_settings();

			// Group, Share features
			$fields[] = self::card_group_share_features();

			// Debug, Troubleshoot
			$fields[] = self::card_debug_troubleshoot();

			return $fields;
		}

		/**
		 * Animations Card
		 */
		private static function card_animations() {
			$values = array(
				'field_type'  => 'card',
				'title'       => __( 'Animations', 'click-to-chat-for-whatsapp' ),
				'description' => 'Add animations and effects to your WhatsApp widget',
				'fields'      => array(
					array(
						'field_type'   => 'field_select',
						'id'           => 'an_type',
						'label'        => __( 'Animations', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'options'      => array(
							'no-animation' => '--No-Animation--',
							'bounce'       => 'Bounce',
							'flash'        => 'Flash',
							'pulse'        => 'Pulse',
							'heartBeat'    => 'HeartBeat',
							'flip'         => 'Flip',
						),
					),
					array(
						'field_type'     => 'field_number',
						'id'             => 'an_delay',
						'label'          => __( 'Animation Delay', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'min'            => 0,
						'help'           => __( 'E.g. Add 1 for 1 second delay', 'click-to-chat-for-whatsapp' ),
						'class_pr'       => '',
						'class_field'    => '',
						'data_watch'     => '#an_type',
						'data_hide_when' => 'no-animation',
					),
					array(
						'field_type'     => 'field_number',
						'id'             => 'an_itr',
						'label'          => __( 'Animation Iteration', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'min'            => 1,
						'help'           => __( 'E.g. Add 2 to repeat animation 2 times', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#an_type',
						'data_hide_when' => 'no-animation',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'show_effect',
						'label'        => __( 'Entry Effects', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'options'      => array(
							'no-show-effects' => '--No-Entry-Effects--',
							'center'          => 'Center (zoomIn)',
							'corner'          => 'Corner (corner of icon)',
						),
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/animations/' ),
						'label'      => __( 'Animations', 'click-to-chat-for-whatsapp' ),
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);
			return $values;
		}

		/**
		 * Notification Badge Card
		 */
		private static function card_notification_badge() {
			$greetings_options  = HT_CTC_Utils::get_option( 'ht_ctc_greetings_options' );
			$greetings_settings = HT_CTC_Utils::get_option( 'ht_ctc_greetings_settings' );
			$greetings_options  = is_array( $greetings_options ) ? $greetings_options : array();
			$greetings_settings = is_array( $greetings_settings ) ? $greetings_settings : array();
			$greetings_template = isset( $greetings_options['greetings_template'] ) ? esc_attr( $greetings_options['greetings_template'] ) : '';
			$g_init             = isset( $greetings_settings['g_init'] ) ? esc_attr( $greetings_settings['g_init'] ) : '';
			$show_warning       = ( '' !== $greetings_template && 'no' !== $greetings_template ) && ( 'open' === $g_init || 'default' === $g_init );

			$values = array(
				'field_type'  => 'card',
				'title'       => __( 'Notification Badge', 'click-to-chat-for-whatsapp' ),
				'description' => 'Add a notification badge to attract attention',
				// any field change inside resets n_badge state (see Actions.js)
				'attributes'  => array(
					'data-action-onchange' => 'updateNotificationBadgeLS',
				),
				'fields'      => array(
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'notification_badge',
						'label'        => __( 'Add Notification Badge', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						// 'attributes'   => array( 'data-action-onchange' => 'some_function' ),
					),
					array(
						'field_type'     => 'field_number',
						'id'             => 'notification_count',
						'label'          => __( 'Notification Count', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'min'            => 0,
						'default'        => 1,
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'notification_bg_color',
						'label'          => __( 'Badge Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'default'        => '#ff4c4c',
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'notification_text_color',
						'label'          => __( 'Text Color', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'default'        => '#ffffff',
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'notification_border_color',
						'label'          => __( 'Add border Color', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'field_number',
						'id'             => 'notification_time',
						'label'          => __( 'Badge Time Delay', 'click-to-chat-for-whatsapp' ),
						'option_group'   => 'ht_ctc_othersettings',
						'min'            => 0,
						'help'           => __( 'Time in seconds', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'block_content',
						'id'             => 'notification_badge_greetings_note',
						'content'        => 'Notification badge will display until the first time user clicks to open chat or the greetings dialog.',
						'style'          => 'font-style:italic; margin: 5px 0',
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'block_group',
						'id'             => 'notification_badge_greetings_wrap',
						'class_pr'       => ( $show_warning ) ? '' : 'ctc_init_display_none',
						'data_watch'     => '#notification_badge',
						'data_show_when' => '1',
						'fields'         => array(
							array(
								'field_type'     => 'block_group',
								'id'             => 'notification_badge_greetings_warning_state',
								'data_watch'     => '#greetings_template',
								'data_hide_when' => 'no',
								'fields'         => array(
									array(
										'field_type'     => 'block_content',
										'id'             => 'notification_badge_greetings_warning',
										'content'        => 'If the <a href="#greetings-settings/g_init" class="ctc-shortcut-link">Greetings dialog initial stage is open <span class="dashicons dashicons-arrow-right-alt2"></span></a>, the notification badge cannot be displayed.',
										'style'          => 'color:#ff4c4c; margin: 5px 0',
										'data_watch'     => '#g_init',
										'data_show_when' => 'open,default',
									),
								),
							),
						),
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/notification-badge/' ),
						'label'      => __( 'Notification Badge', 'click-to-chat-for-whatsapp' ),
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);
			return $values;
		}

		/**
		 * Custom CSS Card
		 */
		private static function card_custom_css() {
			$values = array(
				'field_type'  => 'card',
				'title'       => __( 'Custom CSS', 'click-to-chat-for-whatsapp' ),
				'description' => 'Customize the Click to Chat plugin widget by adding custom CSS Code',
				'fields'      => array(
					array(
						'field_type'   => 'field_textarea',
						'id'           => 'custom_css',
						'label'        => __( 'Custom CSS', 'click-to-chat-for-whatsapp' ),
						'rows'         => 8,
						'placeholder'  => __( 'Custom CSS', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_code_blocks',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/custom-css/' ),
						'label'      => 'CSS Code',
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);
			return $values;
		}

		/**
		 * Advanced Settings Card
		 */
		private static function card_advanced_settings() {
			$values = array(
				'field_type'  => 'card',
				'title'       => sprintf( '%1$s %2$s', __( 'Advanced', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
				'description' => __( 'All these below settings are not important to everyone', 'click-to-chat-for-whatsapp' ),
				// 'description' => 'Optional settings for fine-tuning widget behavior and technical integration',
				'fields'      => array(
					array(
						'field_type'   => 'field_number',
						'id'           => 'zindex',
						'label'        => __( 'z-index', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'min'          => 0,
						'default'      => 99999999,
						'help'         => sprintf(
							'%1$s - <br><a target="_blank" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
							__( 'z-index value for the chat widget to ensure proper stacking and visibility', 'click-to-chat-for-whatsapp' ),
							esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/z-index/' ) ),
							__( 'more info', 'click-to-chat-for-whatsapp' )
						),
					),
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'aria',
						'label'        => __( 'Add aria-hidden=true', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'help'         => __( 'hide for Accessibility API (screen readers)', 'click-to-chat-for-whatsapp' ),
					),
				),
			);
			return $values;
		}

		/**
		 * Group, Share Features Card
		 */
		private static function card_group_share_features() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Group, Share features', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Enable additional WhatsApp features',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'enable_group',
						'label'        => __( 'Enable Group Features', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'help'         => array(
							__( 'Adds WhatsApp Icon for Group', 'click-to-chat-for-whatsapp' ),
							'<span data-watch="#enable_group" data-show-when="1"><a href="#group-settings" class="ctc-shortcut-link">' . __( 'Group Settings', 'click-to-chat-for-whatsapp' ) . ' <span class="dashicons dashicons-arrow-right-alt2"></span></a></span>',
						),
					),
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'enable_share',
						'label'        => __( 'Enable Share Features', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_othersettings',
						'help'         => array(
							__( 'Adds WhatsApp Icon for Share', 'click-to-chat-for-whatsapp' ),
							'<span data-watch="#enable_share" data-show-when="1"><a href="#share-settings" class="ctc-shortcut-link">' . sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ) . ' <span class="dashicons dashicons-arrow-right-alt2"></span></a></span>',
						),
					),
				),
			);
			return $values;
		}

		/**
		 * Debug, Troubleshoot Card
		 */
		private static function card_debug_troubleshoot() {
			$fields = array();

			// Note: Tab field definitions are cached in browser localStorage. Enabling or
			// disabling the AMP plugin will only reflect in the admin UI once localStorage is cleared.
			if ( function_exists( 'amp_is_request' ) ) {
				$fields[] = array(
					'field_type'   => 'field_checkbox',
					'id'           => 'amp',
					'label'        => __( 'AMP Compatibility', 'click-to-chat-for-whatsapp' ),
					'option_group' => 'ht_ctc_othersettings',
					'help'         => 'If any issue, uncheck this option and please contact us - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/amp-compatibility/' ) ) . '" class="external-link">more info <span class="dashicons dashicons-external"></span></a>',
				);
			}

			$fields[] = array(
				'field_type'   => 'field_select',
				'id'           => 'chat_load_hook',
				'label'        => __( 'Chat load hook', 'click-to-chat-for-whatsapp' ),
				'option_group' => 'ht_ctc_othersettings',
				'options'      => array(
					'wp_footer'  => 'wp_footer',
					'get_footer' => 'get_footer',
					'wp_head'    => 'wp_head',
				),
				'help'         => 'If the chat widget is not working with the wp_footer hook, change to get_footer or wp_head - <a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/chat-load-hook/' ) ) . '" target="_blank" class="external-link">more info <span class="dashicons dashicons-external"></span></a>',
			);

			$fields[] = array(
				'field_type'   => 'field_select',
				'id'           => 'js_load',
				'label'        => __( 'Load JavaScript', 'click-to-chat-for-whatsapp' ),
				// 'label'        => 'JavaScript Load Method',
				'option_group' => 'ht_ctc_othersettings',
				'options'      => array(
					''      => __( 'Normal', 'click-to-chat-for-whatsapp' ),
					'defer' => 'Defer',
					'async' => 'Async',
				),
				'help'         => 'Async: load js files asynchronously <br> Defer: load asynchronously and execute after the DOM is loaded -<a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/load-javascript-files/' ) ) . '" target="_blank" class="external-link">more info <span class="dashicons dashicons-external"></span></a>.',
			);

			$fields[] = array(
				'field_type'   => 'field_select',
				'id'           => 'chat_wrapper_tag',
				'label'        => 'Click Tracking Compatibility',
				'option_group' => 'ht_ctc_othersettings',
				'options'      => array(
					''       => __( 'Default', 'click-to-chat-for-whatsapp' ),
					'button' => 'Button (recommended for tracking)',
					'a'      => 'Link',
				),
				'help'         => '(Beta Stage)',
				'help_click'   => 'Some click tracking tools only detect clicks on link or button elements. This option provides compatibility with those tools. <br><br> <strong>Default:</strong> The widget works as usual. Recommended if no special click tracking tools are in use. <br> <strong>Button (recommended for tracking):</strong> Renders the click surface as a button element — widely detected by click tracking tools. <br> <strong>Link:</strong> Renders it as a link element. Note: For privacy, the WhatsApp link is created at click time, so the element itself has no URL — tools that expect a URL on the link may not record these clicks. <br><br> Applies to the main click surfaces — the chat widget and the greetings call-to-action. Shortcodes and custom placements keep their own markup. - <a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/analytics/' ) ) . '" target="_blank" class="external-link">more info <span class="dashicons dashicons-external"></span></a>',
			);

			$fields[] = array(
				'field_type'   => 'field_checkbox',
				'id'           => 'disable_page_level_settings',
				'label'        => 'Disable Page level settings',
				'option_group' => 'ht_ctc_othersettings',
				'help'         => 'If checked, this will disable the ability to configure chat settings for individual pages',
			);

			$fields[] = array(
				'field_type'   => 'field_checkbox',
				'id'           => 'no-intl',
				'label'        => 'Disable Initl input library',
				// 'label'        => 'Disable International Input library',
				'option_group' => 'ht_ctc_othersettings',
				'help'         => 'If WhatsApp number is not saved at admin side, disable the initl input library and add WhatsApp number.',
				// 'help'         => 'If the WhatsApp number is not saving correctly on the admin side, disable the international input library and enter the number with country code. <br> <span class="dashicons dashicons-update-alt"></span> After saving, <a href="#" class="ctc-clear-cache-link">Force Refresh / Clear Cache</a> to apply these changes immediately.',
			);

			$fields[] = array(
				'field_type'   => 'field_checkbox',
				'id'           => 'disable_tinymce',
				'label'        => 'Disable TinyMCE Editor',
				'option_group' => 'ht_ctc_othersettings',
				'help'         => 'If checked, the visual editor (TinyMCE) will be disabled, and a plain text area will be used instead.',
			);

			$fields[] = array(
				'field_type'        => 'field_button',
				'id'                => 'clear_plugin_cache',
				'label'             => 'Force Refresh / Clear Cache',
				'button_text'       => 'Force Refresh / Clear Cache',
				// on the <button> itself, not the wrapper. handler: Actions.js ActionRegistry.
				'button_attributes' => array(
					'data-action-onclick' => 'clearPluginFieldsLocalStorage',
				),
				'class_field'       => 'button button-secondary',
				'help'              => 'Clears the locally stored field configurations and refreshes the page to load fresh settings. Use this if you change settings like Disable TinyMCE or Disable Intl Input and the changes are not reflecting.',
			);

			$fields[] = array(
				'field_type'   => 'field_checkbox',
				'id'           => 'delete_options',
				'label'        => __( 'Delete this plugin settings when uninstalls', 'click-to-chat-for-whatsapp' ),
				'option_group' => 'ht_ctc_othersettings',
				// 'help'         => 'Automatically remove all Click to Chat settings and data from the database when the plugin is deleted.',
			);

			$fields[] = array(
				'field_type' => 'block_content',
				'content'    => sprintf(
					'<p>Any issues related to the Click to Chat plugin? Please <br><a href="%1$s" target="_blank" class="external-link">%2$s <span class="dashicons dashicons-external"></span></a></p>',
					esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/support' ) ),
					__( 'Contact Us', 'click-to-chat-for-whatsapp' )
				),
			);

			$values = array(
				'field_type' => 'card',
				'title'      => __( 'Debug, Troubleshoot, ..', 'click-to-chat-for-whatsapp' ),
				// 'title'       => 'Debug & Troubleshooting',
				// 'description' => 'Tools for resolving technical issues and managing compatibility.',
				'id'         => 'debug_troubleshoot',
				'fields'     => $fields,
			);
			return $values;
		}
	}
}
