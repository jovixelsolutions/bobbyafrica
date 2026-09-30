<?php
/**
 * General Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_General' ) ) {

	/**
	 * General settings class.
	 */
	class HT_CTC_Settings_General {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// Connection Type
			// $fields[] = self::card_connection_type();

			// WhatsApp Number
			$fields[] = self::card_whatsapp_number();

			// // Omni Channel Settings
			// $fields[] = self::card_omni_channel_settings();

			// // Cloud Base
			// $fields[] = self::card_cloud_base();

			// Pre-filled Message
			$fields[] = self::card_prefilled_message();

			// Call to Action
			$fields[] = self::card_call_to_action();

			// Widget Style & Position
			$fields[] = self::card_widget_style_position();

			// URL Structure
			$fields[] = self::card_url_structure();

			// Features
			$fields[] = self::card_features();

			// Dynamic Settings (Greetings)
			// $fields[] = array(
			// 'field_type' => 'section_dynamic',
			// 'id'         => 'ctc_load_dynamic_settings',
			// 'group'      => 'greetings_settings',
			// );

			return $fields;
		}

		// -------------------------------------------------------------------------
		// Private helpers
		// -------------------------------------------------------------------------

		/**
		 * Widget style options shared by Desktop and Mobile selectors.
		 *
		 * Centralises the list so desktop and mobile grids stay in sync
		 * and the definition only ever needs to be updated in one place.
		 *
		 * @return array
		 */
		private static function style_options() {
			return array(
				array(
					'value'       => '1',
					'name'        => __( 'Style 1', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Theme Button',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-1',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_1',
					),
				),
				array(
					'value'       => '2',
					'name'        => __( 'Style 2', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Square Icon',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-2',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_2',
					),
				),
				array(
					'value'       => '3',
					'name'        => __( 'Style 3', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Round Icon',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-3',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_3',
					),
				),
				array(
					'value'       => '3_1',
					'name'        => __( 'Style 3 Extend', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Round Icon',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-3-1',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_3_1',
					),
				),
				array(
					'value'       => '4',
					'name'        => 'Style 4',
					'sub_text'    => 'Chip Button',
					'icon'        => 'dashicons dashicons-format-chat',
					'text'        => 'Chat',
					'class_field' => 'style-4',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_4',
					),
				),
				array(
					'value'       => '5',
					'name'        => __( 'Style 5', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Image Slider',
					'icon'        => 'dashicons dashicons-format-chat',
					'text'        => 'Chat',
					'class_field' => 'style-5',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_5',
					),
				),
				array(
					'value'       => '6',
					'name'        => 'Style 6',
					'sub_text'    => 'Text Only',
					'text'        => 'Chat with us',
					'class_field' => 'style-6',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_6',
					),
				),
				array(
					'value'       => '7',
					'name'        => __( 'Style 7', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Rounded Button',
					'text'        => 'WhatsApp Chat',
					'class_field' => 'style-7',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_7',
					),
				),
				array(
					'value'       => '7_1',
					'name'        => __( 'Style 7 Extend', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Rounded Button',
					'text'        => 'WhatsApp Chat',
					'class_field' => 'style-7-1',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_7_1',
					),
				),
				array(
					'value'       => '8',
					'name'        => __( 'Style 8', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Rect Button',
					'text'        => 'WhatsApp Chat',
					'class_field' => 'style-8',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_8',
					),
				),
				array(
					'value'       => '99',
					'name'        => 'Style 99',
					'sub_text'    => 'Custom Image',
					'icon'        => 'dashicons dashicons-format-image',
					'class_field' => 'style-99',
					'attributes'  => array(
						'data-contextual-group' => 'contextual_styles',
						'data-contextual-id'    => 'style_99',
					),
				),
			);
		}

		/**
		 * Connection Type Card
		 */
		private static function card_connection_type() {
			$values = array(
				'field_type'  => 'card',
				'title'       => 'Connection Type',
				'description' => 'Choose how you want to connect with your visitors',
				'fields'      => array(
					array(
						'field_type'   => 'field_select',
						'id'           => 'connection_type',
						'option_group' => 'ht_ctc_chat_options',
						'label'        => 'Select Service',
						'default'      => 'single',
						'options'      => array(
							'single' => 'Single Channel (WhatsApp)',
							'omni'   => 'Omni Channel (Telegram, Instagram ...)',
							'cloud'  => 'Cloud Base (clicktochat.pro)',
						),
					),
				),
			);
			return $values;
		}

		/**
		 * Omni Channel Settings Card
		 */
		private static function card_omni_channel_settings() {
			$values = array(
				'field_type'     => 'card',
				'title'          => 'Omni Channel Settings',
				'description'    => 'Connect through multiple applications. Drag items to arrange order.',
				'class_pr'       => 'is-sortable omni-channel-card',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'omni',
				'fields'         => array(
					// WhatsApp
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-whatsapp',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][whatsapp][enable',
								'label'        => '<span class="dashicons dashicons-format-chat"></span> WhatsApp',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][whatsapp][value',
								'label'          => __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ),
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => '+1234567890',
								'class_field'    => 'intl_number',
								'data_watch'     => '#channels][whatsapp][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Telegram
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-telegram',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][telegram][enable',
								'label'        => '<span class="dashicons dashicons-paperplane"></span> Telegram',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][telegram][value',
								'label'          => 'Telegram Username',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => 'Username',
								'data_watch'     => '#channels][telegram][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Instagram
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-instagram',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][instagram][enable',
								'label'        => '<span class="dashicons dashicons-camera"></span> Instagram',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][instagram][value',
								'label'          => 'Instagram Username',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => 'Username',
								'data_watch'     => '#channels][instagram][enable',
								'data_show_when' => '1',
							),
						),
					),
					// SMS
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-sms',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][sms][enable',
								'label'        => '<span class="dashicons dashicons-email-alt"></span> SMS',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][sms][value',
								'label'          => 'SMS Number',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => '+1234567890',
								'data_watch'     => '#channels][sms][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Call
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-call',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][call][enable',
								'label'        => '<span class="dashicons dashicons-phone"></span> Call',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][call][value',
								'label'          => 'Phone Number',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => '+1234567890',
								'data_watch'     => '#channels][call][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Email
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-email',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][email][enable',
								'label'        => '<span class="dashicons dashicons-email"></span> Email',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][email][value',
								'label'          => 'Email Address',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => 'hello@example.com',
								'data_watch'     => '#channels][email][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Messenger
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-messenger',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][messenger][enable',
								'label'        => '<span class="dashicons dashicons-format-status"></span> Messenger',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][messenger][value',
								'label'          => 'Messenger Username / ID',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => 'Username / ID',
								'data_watch'     => '#channels][messenger][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Viber
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-viber',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][viber][enable',
								'label'        => '<span class="dashicons dashicons-phone"></span> Viber',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][viber][value',
								'label'          => 'Viber Number',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => '+1234567890',
								'data_watch'     => '#channels][viber][enable',
								'data_show_when' => '1',
							),
						),
					),
					// Custom
					array(
						'field_type' => 'block_group',
						'class_pr'   => 'omni-channel-item omni-custom',
						'draggable'  => true,
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'channels][custom][enable',
								'label'        => '<span class="dashicons dashicons-admin-links"></span> Custom Link',
								'option_group' => 'ht_ctc_chat_options',
							),
							array(
								'field_type'     => 'field_text',
								'id'             => 'channels][custom][value',
								'label'          => 'Link / URL',
								'option_group'   => 'ht_ctc_chat_options',
								'placeholder'    => 'https://example.com/contact',
								'data_watch'     => '#channels][custom][enable',
								'data_show_when' => '1',
							),
						),
					),
					array(
						'field_type' => 'block_infobox_alert',
						'type'       => 'info',
						'content'    => 'More channels and advanced customization are available in the Pro version.',
					),
				),
			);
			return $values;
		}

		/**
		 * Cloud Base Card
		 */
		private static function card_cloud_base() {
			$values = array(
				'field_type'     => 'card',
				'title'          => 'Cloud Base (SaaS)',
				'description'    => 'Connect with clicktochat.pro',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'cloud',
				'fields'         => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 'cloud_id',
						'label'        => 'Widget ID',
						'option_group' => 'ht_ctc_chat_options',
						'placeholder'  => 'e.g. 12345',
					),
					array(
						'field_type' => 'block_infobox',
						'content'    => 'Please create an account at <a href="https://clicktochat.pro" target="_blank" class="external-link">clicktochat.pro <span class="dashicons dashicons-external"></span></a>, create a widget, and enter the ID here.',
					),
				),
			);
			return $values;
		}

		/**
		 * WhatsApp Number Card
		 */
		private static function card_whatsapp_number() {

			$os      = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );
			$os      = is_array( $os ) ? $os : array();
			$no_intl = isset( $os['no-intl'] );

			$ht_ctc_chat_options = HT_CTC_Utils::get_option( 'ht_ctc_chat_options' );
			$number              = isset( $ht_ctc_chat_options['number'] ) ? $ht_ctc_chat_options['number'] : '';
			$ht_ctc_admin_pages  = HT_CTC_Utils::get_option( 'ht_ctc_admin_pages' );
			$save_count          = isset( $ht_ctc_admin_pages['count'] ) ? (int) $ht_ctc_admin_pages['count'] : 0;

			if ( $no_intl ) {
				$number_fields = array(
					array(
						'field_type'   => 'field_text',
						'id'           => 'number',
						'label'        => __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_chat_options',
						'class_field'  => 'main_wa_number',
						'placeholder'  => '+1234567890',
						'help'         => vsprintf(
							'%1$s <a target="_blank" href="%2$s">%3$s</a><br>%4$s - <a target="_blank" href="%5$s">%6$s</a>',
							// '%1$s <a target="_blank" href="%2$s">%3$s</a><br>%4$s - <a target="_blank" href="%5$s">%6$s</a><br>Display WhatsApp number input field using: <a href="%7$s">%8$s</a>',
							array(
								__( 'WhatsApp or WhatsApp business number with ', 'click-to-chat-for-whatsapp' ),
								esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/blog/country-codes/' ) ),
								__( 'country code', 'click-to-chat-for-whatsapp' ),
								__( '( E.g. 916123456789 - herein e.g. 91 is country code, 6123456789 is the mobile number )', 'click-to-chat-for-whatsapp' ),
								esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/whatsapp-number/' ) ),
								__( 'more info', 'click-to-chat-for-whatsapp' ),
								// esc_url( admin_url( 'admin.php?page=click-to-chat&number-field=2' ) ),
								// 'Intl input library',
							)
						),
					),
				);
			} else {
				$number_fields = array(
					array(
						'field_type'   => 'section_whatsapp_number',
						'id'           => 'number',
						'label'        => __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_chat_options',
						'class_field'  => 'intl_number main_wa_number',
						// 'placeholder'  => '+1234567890',
						'help'         => __( 'WhatsApp or WhatsApp business number', 'click-to-chat-for-whatsapp' ),
					),
				);
			}

			/*
			 * The one PRO line on this tab, and it sits here rather than in a
			 * teaser card at the bottom: "I need a second number" is the most
			 * common reason someone upgrades, and the moment they wonder is
			 * while they are looking at the single number field. One quiet line
			 * after the field - not a card, which would repeat what the PRO
			 * widget in the sidebar already shows on this tab.
			 * improve the content, links for multi-agent / random numbers note.
			 */
			// if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
			// $number_fields[] = array(
			// 'field_type' => 'block_content',
			// 'class_pr'   => 'ctc-inline-pro-note',
			// BlockContent applies `margin: 0 10px` unless a style is given;
			// the extra top margin separates this from the field's own help
			// text, which it would otherwise read as a continuation of.
			// 'style'      => 'margin: 12px 10px 0;',
			// 'content'    => sprintf(
			// '<p class="description">%1$s <a target="_blank" rel="noopener" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external" aria-hidden="true"></span><span class="screen-reader-text">%4$s</span></a></p>',
			// 'Need more than one number? PRO adds multi-agent, and random or sequential numbers.',
			// esc_url( HT_CTC_Utils::pro_url( 'inline', 'general_number', 'https://holithemes.com/plugins/click-to-chat/multi-agent/' ) ),
			// 'See how it works',
			// '(opens in a new tab)'
			// ),
			// );
			// }

			// not included as now. if any issue related to the intl library reported then will enable this.
			// if ( ! $no_intl && '' === $number && $save_count > 5 ) {
			// $number_fields[] = array(
			// 'field_type' => 'block_infobox_alert',
			// 'type'       => 'info',
			// 'content'    => 'If WhatsApp number is not saved at admin side, please <a href="#advanced-settings/no-intl" class="ctc-shortcut-link">Disable Intl input library <span class="dashicons dashicons-arrow-right-alt2"></span></a> and add WhatsApp number.',
			// 'content'    => 'If the WhatsApp number is not saving correctly on this page, please <a href="#advanced-settings/no-intl" class="ctc-shortcut-link">Disable the International Input library <span class="dashicons dashicons-arrow-right-alt2"></span></a> and then enter the number.',
			// );
			// }

			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Enter the WhatsApp number that will receive messages from your visitors',
				'class_pr'       => '',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => $number_fields,
			);

			$values = apply_filters( 'ht_ctc_fh_settings_fields_general_whatsapp_number', $values );

			return $values;
		}

		/**
		 * Pre-filled Message Card
		 */
		private static function card_prefilled_message() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Pre-filled Message', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Set a pre-filled message that automatically appears in the WhatsApp chat window when a visitor clicks the chat widget.',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'field_textarea',
						'id'           => 'pre_filled',
						'option_group' => 'ht_ctc_chat_options',
						'placeholder'  => "Hello {site} \nLike to know more information about {title}, {url}",
						'rows'         => 4,
					),
					array(
						'field_type' => 'block_infobox',
						// 'content'    => 'You can use variables like <code>{site_url}</code> and <code>{title}</code> in your message.',
						'content'    => __( 'Text that is pre-filled in WhatsApp Chat window. Add variables {site}, {title}, {url}, [url] to replace with the site name, post title, current webpage URL and full URL including query parameters', 'click-to-chat-for-whatsapp' ) . ' - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/pre-filled-message/' ) ) . '" class="external-link">' . __( 'more info', 'click-to-chat-for-whatsapp' ) . ' <span class="dashicons dashicons-external"></span></a>',
						'class_pr'   => '',
					),
				),
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$values['fields'][] = array(
					'field_type' => 'block_infobox',
					'content'    => 'To change Pre-filled Message for WooCommerce Single Product Pages, go to <a href="#woo-overwrite-settings/woo_pre_filled" class="ctc-shortcut-link">WooCommerce Settings <span class="dashicons dashicons-arrow-right-alt2"></span></a>.',
					'class_pr'   => '',
				);
			}

			return $values;
		}

		/**
		 * Call to Action Card
		 */
		private static function card_call_to_action() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Call to Action', 'click-to-chat-for-whatsapp' ),
				'description'    => __( 'Text that appears along with WhatsApp icon/button', 'click-to-chat-for-whatsapp' ),
				// 'description'    => 'The main text displayed next to the WhatsApp icon or button',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 'call_to_action',
						'label'        => __( 'Call to Action', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_chat_options',
						'placeholder'  => 'WhatsApp Us',
						'class_pr'     => 'main-cta',
						'class_field'  => 'cta-input',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/call-to-action/' ),
						'label'      => __( 'Call to Action', 'click-to-chat-for-whatsapp' ),
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);
			if ( class_exists( 'WooCommerce' ) ) {
				$values['fields'][] = array(
					'field_type' => 'block_infobox',
					'content'    => 'To change Call to Action for WooCommerce Single Product Pages, go to <a href="#woo-overwrite-settings/woo_call_to_action" class="ctc-shortcut-link">WooCommerce Settings <span class="dashicons dashicons-arrow-right-alt2"></span></a>.',
					'class_pr'   => '',
				);
			}

			return $values;
		}

		/**
		 * Widget Style & Position Card
		 */
		private static function card_widget_style_position() {
			$position_type_options = array( 'fixed' => 'Fixed' );
			$position_type_options = apply_filters( 'ht_ctc_fh_settings_fields_position_type_options', $position_type_options );
			$style_options         = self::style_options();

			// Shared position-type help text used for both Desktop and Mobile selectors.
			$position_type_help = array(
				__( 'Fixed: Position relative to the screen, stays at the same place even after page scroll', 'click-to-chat-for-whatsapp' ),
				vsprintf(
					'%1$s (PRO)<br><a target="_blank" rel="noopener" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
					array(
						__( 'Absolute: Position relative to the content (body tag) and moves with page scroll', 'click-to-chat-for-whatsapp' ),
						esc_url( HT_CTC_Utils::pro_url( 'inline', 'position_absolute', 'https://holithemes.com/plugins/click-to-chat/position-to-place/#pro_block' ) ),
						__( 'more info', 'click-to-chat-for-whatsapp' ),
					)
				),
			);

			$values = array(
				'field_type'     => 'card',
				'title'          => 'Widget Style & Position',
				'description'    => 'Choose how your chat widget looks and where it appears',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type' => 'tabs',
						'class_pr'   => '',
						'tabs'       => array(
							'desktop-style' => array(
								'label'  => __( 'Desktop', 'click-to-chat-for-whatsapp' ),
								'fields' => array(
									array(
										'field_type'   => 'block_grid_select',
										'label'        => sprintf(
											'%1$s: (%2$s)',
											__( 'Select Style', 'click-to-chat-for-whatsapp' ),
											__( 'Desktop', 'click-to-chat-for-whatsapp' )
										),
										'id'           => 'style_desktop',
										'option_group' => 'ht_ctc_chat_options',
										'class_pr'     => 'style-option-desktop',
										'options'      => $style_options,
										'help'         => sprintf(
											'<a target="_blank" href="%s" class="external-link">%s <span class="dashicons dashicons-external"></span></a>',
											esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/list-of-styles/' ) ),
											__( 'List of Styles', 'click-to-chat-for-whatsapp' )
										),
									),
									array(
										'field_type'   => 'field_select',
										'label'        => sprintf(
											'%1$s: (%2$s)',
											__( 'Position Type', 'click-to-chat-for-whatsapp' ),
											__( 'Desktop', 'click-to-chat-for-whatsapp' )
										),
										'id'           => 'position_type',
										'option_group' => 'ht_ctc_chat_options',
										'options'      => $position_type_options,
										'help_click'   => $position_type_help,
									),
									array(
										'field_type'   => 'block_rows',
										'label'        => sprintf(
											'%1$s: (%2$s)',
											__( 'Position to Place', 'click-to-chat-for-whatsapp' ),
											__( 'Desktop', 'click-to-chat-for-whatsapp' )
										),
										'option_group' => 'ht_ctc_chat_options',
										'fields'       => array(
											// col-1, col-2 are just identifiers, can be named anything.
											'col-1' => array(
												array(
													'field_type' => 'field_select',
													'id' => 'side_1',
													'option_group' => 'ht_ctc_chat_options',
													'options' => array(
														'top'    => __( 'Top', 'click-to-chat-for-whatsapp' ),
														'bottom' => __( 'Bottom', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'bottom',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'side_1_value',
													'option_group' => 'ht_ctc_chat_options',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
											'col-2' => array(
												array(
													'field_type' => 'field_select',
													'id' => 'side_2',
													'option_group' => 'ht_ctc_chat_options',
													'options' => array(
														'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
														'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'right',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'side_2_value',
													'option_group' => 'ht_ctc_chat_options',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
										),
										'help'         => vsprintf(
											'%1$s <a target="_blank" rel="noopener" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
											array(
												__( 'Add css units as suffix - e.g. 10px, 50%', 'click-to-chat-for-whatsapp' ),
												esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/position-to-place/' ) ),
												__( 'more info', 'click-to-chat-for-whatsapp' ),
											)
										),
									),
								),
							),
							'mobile-style'  => array(
								'label'  => __( 'Mobile', 'click-to-chat-for-whatsapp' ),
								'fields' => array(
									array(
										'field_type'   => 'field_checkbox',
										'type'         => 'switch',
										'id'           => 'same_settings',
										'label'        => __( 'Mobile and Desktop same settings', 'click-to-chat-for-whatsapp' ),
										'option_group' => 'ht_ctc_chat_options',
										'value'        => '1',
										'class_pr'     => '',
										'class_field'  => '',
										'help'         => vsprintf(
											'%1$s, %2$s, %3$s',
											array(
												__( 'Select Style', 'click-to-chat-for-whatsapp' ),
												__( 'Position Type', 'click-to-chat-for-whatsapp' ),
												__( 'Position to Place', 'click-to-chat-for-whatsapp' ),
											)
										),

									),
									array(
										'field_type'     => 'block_grid_select',
										'label'          => sprintf(
											'%1$s: (%2$s)',
											__( 'Select Style', 'click-to-chat-for-whatsapp' ),
											__( 'Mobile', 'click-to-chat-for-whatsapp' )
										),
										'id'             => 'style_mobile',
										'option_group'   => 'ht_ctc_chat_options',
										'class_pr'       => 'style-option-mobile',
										'data_watch'     => '#mobile-style-tab #same_settings',
										'data_hide_when' => '1',
										'options'        => $style_options,
									),
									array(
										'field_type'     => 'field_select',
										'label'          => sprintf(
											'%1$s: (%2$s)',
											__( 'Position Type', 'click-to-chat-for-whatsapp' ),
											__( 'Mobile', 'click-to-chat-for-whatsapp' )
										),
										'id'             => 'position_type_mobile',
										'class_pr'       => 'mobile-setting',
										'option_group'   => 'ht_ctc_chat_options',
										'options'        => $position_type_options,
										'help'           => $position_type_help,
										'data_watch'     => '#mobile-style-tab #same_settings',
										'data_hide_when' => '1',
									),
									array(
										'field_type'     => 'block_rows',
										'label'          => sprintf(
											'%1$s: (%2$s)',
											__( 'Position to Place', 'click-to-chat-for-whatsapp' ),
											__( 'Mobile', 'click-to-chat-for-whatsapp' )
										),
										'class_pr'       => 'mobile-setting',
										'option_group'   => 'ht_ctc_chat_options',
										'data_watch'     => '#mobile-style-tab #same_settings',
										'data_hide_when' => '1',
										'fields'         => array(
											'col-1' => array(
												array(
													'field_type' => 'field_select',
													'id' => 'mobile_side_1',
													'option_group' => 'ht_ctc_chat_options',
													'options' => array(
														'top'       => __( 'Top', 'click-to-chat-for-whatsapp' ),
														'bottom'    => __( 'Bottom', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'bottom',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'mobile_side_1_value',
													'option_group' => 'ht_ctc_chat_options',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
											'col-2' => array(
												array(
													'field_type' => 'field_select',
													'id' => 'mobile_side_2',
													'option_group' => 'ht_ctc_chat_options',
													'options' => array(
														'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
														'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'right',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'mobile_side_2_value',
													'option_group' => 'ht_ctc_chat_options',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
										),
										'help'           => vsprintf(
											'%1$s <a target="_blank" rel="noopener" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
											array(
												__( 'Add css units as suffix - e.g. 10px, 50%', 'click-to-chat-for-whatsapp' ),
												esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/position-to-place/#pro_block' ) ),
												__( 'more info', 'click-to-chat-for-whatsapp' ),
											)
										),
									),
									array(
										'field_type'     => 'block_divider',
										'data_watch'     => '#mobile-style-tab #same_settings',
										'data_hide_when' => '1',
									),
									array(
										'field_type'     => 'block_content_details',
										'style'          => 'margin-top: 10px;',
										'data_watch'     => '#mobile-style-tab #same_settings',
										'data_hide_when' => '1',
										'title'          => '<span class="not_samesettings select_styles_issue_description" style="font-size: 0.9em; display: inline;">If Styles for desktop, mobile not selected as expected <span style="color: #039be5; cursor: pointer;">Check this</span>, - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/select-styles/#styles-not-applied' ) ) . '" class="external-link">more info <span class="dashicons dashicons-external"></span></a></span>',
										'fields'         => array(
											array(
												'field_type' => 'field_checkbox',
												'id'    => 'select_styles_issue',
												'value' => '1',
												'option_group' => 'ht_ctc_chat_options',
												'label' => __( 'Check this only, If styles for mobile, desktop not selected as expected(due to cache)', 'click-to-chat-for-whatsapp' ),
											),
										),
									),
								),
							),
						),
					),
				),
			);
			return $values;
		}

		/**
		 * URL Structure Card
		 */
		private static function card_url_structure() {
			$values = array(

				// Collapsed by default — the wa.me default suits nearly all sites,
				// so this stays out of the way of the primary settings.
				// 'field_type'     => 'block_accordion',
				// 'id'             => 'url_structure',

				'field_type'     => 'card',
				'title'          => __( 'URL Structure', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Configure how WhatsApp links are opened on different devices',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/url-structure/' ),
						'label'      => __( 'URL Structure', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'url_target_d',
						'label'        => vsprintf(
							'%1$s: %2$s',
							array(
								__( 'Desktop', 'click-to-chat-for-whatsapp' ),
								__( 'Open links in', 'click-to-chat-for-whatsapp' ),
							)
						),
						'option_group' => 'ht_ctc_chat_options',
						'options'      => array(
							'_blank' => __( 'New Tab', 'click-to-chat-for-whatsapp' ),
							'popup'  => __( 'Pop-up', 'click-to-chat-for-whatsapp' ),
							'_self'  => __( 'Same Tab', 'click-to-chat-for-whatsapp' ),
						),
						'default'      => '_blank',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'url_structure_d',
						'label'        => vsprintf(
							'%1$s: %2$s',
							array(
								__( 'Desktop', 'click-to-chat-for-whatsapp' ),
								__( 'URL Structure', 'click-to-chat-for-whatsapp' ),
							)
						),
						'option_group' => 'ht_ctc_chat_options',
						'options'      => array(
							'default'    => '(Default) wa.me',
							'web'        => __( 'Web WhatsApp', 'click-to-chat-for-whatsapp' ),
							'custom_url' => __( 'Custom URL', 'click-to-chat-for-whatsapp' ),
						),
						'default'      => 'default',
						'help'         => 'Wa.me: Opens WhatsApp Desktop app<br>Web WhatsApp: Opens web.whatsapp.com<br>Custom URL: Add your own (e.g., channel URL)',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'custom_url_d',
						'label'          => sprintf(
							'%1$s (%2$s)',
							__( 'Custom URL', 'click-to-chat-for-whatsapp' ),
							__( 'Desktop', 'click-to-chat-for-whatsapp' )
						),
						'option_group'   => 'ht_ctc_chat_options',
						'placeholder'    => 'https://www.whatsapp.com/channel',
						'data_watch'     => '#url_structure_d',
						'data_show_when' => 'custom_url',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'url_structure_m',
						'label'        => sprintf(
							'%1$s: %2$s',
							__( 'Mobile', 'click-to-chat-for-whatsapp' ),
							__( 'URL Structure', 'click-to-chat-for-whatsapp' )
						),
						'option_group' => 'ht_ctc_chat_options',
						'options'      => array(
							'default'    => '(Default) wa.me',
							'wa_colon'   => 'WhatsApp://',
							'custom_url' => __( 'Custom URL', 'click-to-chat-for-whatsapp' ),
						),
						'default'      => 'default',
						'help'         => 'Wa.me: Universal link<br>WhatsApp://: Tries to open app directly<br>Custom URL: Add your own',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'custom_url_m',
						'label'          => sprintf(
							'%1$s (%2$s)',
							__( 'Custom URL', 'click-to-chat-for-whatsapp' ),
							__( 'Mobile', 'click-to-chat-for-whatsapp' )
						),
						'option_group'   => 'ht_ctc_chat_options',
						'placeholder'    => 'https://www.whatsapp.com/channel',
						'data_watch'     => '#url_structure_m',
						'data_show_when' => 'custom_url',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/custom-url/' ),
						'label'      => __( 'Custom URL', 'click-to-chat-for-whatsapp' ),
					),
				),

			);
			return $values;
		}
		/**
		 * Features Card
		 */
		private static function card_features() {
			$values = array(
				'field_type' => 'card',
				'title'      => 'Features',
				'class_pr'   => 'ctc-features-section',
				'fields'     => array(
					array(
						'field_type' => 'block_feature_box',
						'icon'       => 'pointer-square',
						'label'      => 'Custom Element',
						'link'       => array(
							'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/custom-element/' ),
							'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
						),
						'content'    => sprintf(
							'<p class="ctc-feature-text">%1$s</p>
							<div class="ctc-feature-meta">
								<div class="ctc-feature-meta-row"><span class="ctc-feature-meta-key">%2$s</span><code class="ctc-feature-code">ctc_chat</code></div>
								<div class="ctc-feature-meta-row"><span class="ctc-feature-meta-key">%3$s</span><code class="ctc-feature-code">#ctc_chat</code></div>
							</div>',
							'Turn any button or link on your site into a WhatsApp click — add the class name or href below to your own element.',
							'Class name',
							'Href / Link'
						),
					),
					array(
						'field_type' => 'block_feature_box',
						'icon'       => 'code',
						'label'      => 'Shortcode',
						'link'       => array(
							'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/shortcodes/' ),
							'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
						),
						'content'    => sprintf(
							'<p class="ctc-feature-text">%1$s</p>
							<div class="ctc-feature-meta">
								<div class="ctc-feature-meta-row"><span class="ctc-feature-meta-key">%2$s</span><code class="ctc-feature-code">[ht-ctc-chat]</code></div>
							</div>',
							'Display the chat widget anywhere — paste this shortcode into any post, page, or widget area.',
							'Shortcode'
						),
					),
				),
			);
			return $values;
		}
	}
}
