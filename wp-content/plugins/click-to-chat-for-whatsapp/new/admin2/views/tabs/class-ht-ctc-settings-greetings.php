<?php
/**
 * Greetings Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Greetings' ) ) {

	/**
	 * Greetings settings class.
	 */
	class HT_CTC_Settings_Greetings {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// Select greetings template.
			$fields[] = self::card_select_greetings_template();

			// Handle Filtered Fields.
			// Placed right after the template selector so the cards revealed by a
			// template choice appear immediately where the user is looking (instead
			// of below the Content/Dialog cards). Cards not matching the selected
			// template stay hidden, so this is a no-op for the built-in templates.
			$additional_fields = apply_filters( 'ht_ctc_fh_settings_fields_greetings_fields_after_template', array() );

			if ( ! empty( $additional_fields ) ) {
				$fields = array_merge( $fields, $additional_fields );
			}

			// Content: header, main, bottom, call to action
			$fields[] = self::card_greetings_content();

			// Greetings Dialog - 1
			// $fields[] = self::card_greetings_style_1();

			// Greetings Dialog - 2
			// $fields[] = self::card_greetings_style_2();

			// Opt-in Settings
			$fields[] = self::card_opt_in_settings();

			// Additional Settings
			$fields[] = self::card_additional_settings();

			// Actions Card
			$fields[] = self::card_actions();

			// PRO teaser — only when PRO is not active. Placed last so it never
			// interrupts the Free setup flow (balanced, non-blocking upsell).
			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$fields[] = self::card_pro_greetings();
			}

			$fields = apply_filters( 'ht_ctc_fh_settings_fields_greetings_fields', $fields );

			return $fields;
		}

		/**
		 * PRO teaser card (Free only): surfaces the greetings-related PRO
		 * features contextually, right where the admin is editing greetings.
		 * Marketing copy only — no PRO option keys are referenced.
		 */
		private static function card_pro_greetings() {
			$pricing_url = HT_CTC_Utils::pro_url( 'teaser', 'greetings' );

			return array(
				'field_type'     => 'card',
				'title'          => 'Do more with Greetings — PRO',
				'description'    => 'Capture leads and route chats right from the greeting dialog.',
				'class_pr'       => 'ctc-pro-teaser-card',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-forms',
						'title'       => 'Form Filling',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Collect name, email, phone and more before the chat opens — 8 field types including date and international number.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'greetings_form', 'https://holithemes.com/plugins/click-to-chat/greetings-form/' ),
					),
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-groups',
						'title'       => 'Multi-Agent Greetings',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Let visitors pick an agent — each with a photo, a role, their own number and their own weekly hours.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'multi_agent', 'https://holithemes.com/plugins/click-to-chat/multi-agent/' ),
					),
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-controls-play',
						'title'       => 'Auto-Open Triggers',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Open the greeting after a set number of seconds, or at a scroll percentage — or when a section you mark with a CSS class scrolls into view.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'greetings_actions', 'https://holithemes.com/plugins/click-to-chat/greetings-actions/' ),
					),
					array(
						'field_type' => 'block_raw_html',
						'content'    => '<a href="' . esc_url( $pricing_url ) . '" target="_blank" rel="noopener" class="ctc-pro-btn ctc-pro-btn-primary ctc-pro-teaser-cta">Upgrade to PRO <span class="dashicons dashicons-external"></span><span class="screen-reader-text">(opens in a new tab)</span></a>',
					),
				),
			);
		}

		/**
		 * Select Greetings Template Card
		 */
		private static function card_select_greetings_template() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Add Greetings Dialog', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Add interactive greetings to engage visitors before they start chatting',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					array(
						'field_type'   => 'block_grid_select',
						'id'           => 'greetings_template',
						'label'        => 'Greetings Dialog',
						'option_group' => 'ht_ctc_greetings_options',
						'class_pr'     => 'ctc-greetings-grid',
						'options'      => self::greetings_templates(),
						'help'         => '<span class="ctc-help-links-group"><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings/' ) ) . '" target="_blank" class="external-link">Greetings <span class="dashicons dashicons-external"></span></a><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-1/' ) ) . '" target="_blank" class="external-link">Greetings-1 <span class="dashicons dashicons-external"></span></a><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-2/' ) ) . '" target="_blank" class="external-link">Greetings-2 <span class="dashicons dashicons-external"></span></a><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-form/' ) ) . '" target="_blank" class="external-link">Form Filling <span class="dashicons dashicons-external"></span></a><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/multi-agent/' ) ) . '" target="_blank" class="external-link">Multi Agent <span class="dashicons dashicons-external"></span></a></span>',
					),
				),
			);
			return $values;
		}

		/**
		 * Greetings Content Card
		 */
		private static function card_greetings_content() {
			$os              = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );
			$os              = is_array( $os ) ? $os : array();
			$disable_tinymce = isset( $os['disable_tinymce'] );
			$editor_type     = ( $disable_tinymce ) ? 'field_textarea' : 'block_editor_tinymce';
			$tinymce_help    = ( $disable_tinymce ) ? '<br> Enable TinyMCE rich text editor at <a href="#advanced-settings/disable_tinymce" class="ctc-shortcut-link">Advanced <span class="dashicons dashicons-arrow-right-alt2"></span></a> tab.' : '';

			$badge_colors_fields = array(
				array(
					array(
						'field_type'     => 'field_color',
						'id'             => 'g_header_online_status_color',
						'label'          => 'Online Status Badge Color',
						'option_group'   => 'ht_ctc_greetings_options',
						'default'        => '#06e376',
						'help'           => 'Color of the online status badge shown on the header image',
						'data_watch'     => '#g_header_online_status',
						'data_show_when' => '1',
					),
				),
			);
			$badge_colors_fields = apply_filters( 'ht_ctc_fh_settings_fields_greetings_content_offline_color', $badge_colors_fields );

			$fields_before = array(
				array(
					'field_type'     => $editor_type,
					'id'             => 'header_content',
					'label'          => __( 'Header Content', 'click-to-chat-for-whatsapp' ),
					'option_group'   => 'ht_ctc_greetings_options',
					'help'           => 'Customize the font to improve the appearance (font size, color, etc.)' . $tinymce_help,
					// 'help'           => 'Style the header content to match your brand (e.g. font size, color, and spacing).' . $tinymce_help,
					'data_watch'     => '#greetings_template',
					'data_show_when' => 'greetings-1,greetings-pro-1,greetings-pro-2,greetings-pro-3',
				),
				array(
					'field_type'     => 'block_group',
					'data_watch'     => '#greetings_template',
					'data_show_when' => 'greetings-1,greetings-pro-1,greetings-pro-2,greetings-pro-3',
					'fields'         => array(
						array(
							'field_type'       => 'block_upload_image',
							'id'               => 'g_header_image',
							'label'            => 'Header Image',
							'option_group'     => 'ht_ctc_greetings_options',
							'thumbnail_height' => '50px',
							// circle/square
							'thumbnail_shape'  => 'circle',
							'help'             => 'Profile picture shown in the greetings header',
						),
						// Badge Status Checkbox:
						array(
							'field_type'     => 'field_checkbox',
							'id'             => 'g_header_online_status',
							'label'          => 'Online Status Badge',
							'option_group'   => 'ht_ctc_greetings_options',
							'help'           => 'Show an online status dot on the header profile picture.',
							'data_watch'     => '#g_header_image',
							'data_show_when' => '!empty',
						),
						// Badge Color Pickers:
						array(
							'field_type'     => 'block_rows',
							'data_watch'     => '#g_header_image',
							'data_show_when' => '!empty',
							'fields'         => $badge_colors_fields,
						),
					),
				),
			);

			// Fields rendered after the badge color row.
			$fields_after = array(
				array(
					'field_type'   => $editor_type,
					'id'           => 'main_content',
					'label'        => __( 'Main Content', 'click-to-chat-for-whatsapp' ),
					'option_group' => 'ht_ctc_greetings_options',
					'help'         => 'Variables: {site}, {title}, {url}' . $tinymce_help,
				),
				array(
					'field_type'   => $editor_type,
					'id'           => 'bottom_content',
					'label'        => __( 'Bottom Content', 'click-to-chat-for-whatsapp' ),
					'option_group' => 'ht_ctc_greetings_options',
					'help'         => '&#128994; <a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/symbols/' ) ) . '" target="_blank" class="external-link">Symbols <span class="dashicons dashicons-external"></span></a>' . $tinymce_help,
				),
				array(
					'field_type'   => 'field_text',
					'id'           => 'call_to_action',
					'label'        => __( 'Call to Action', 'click-to-chat-for-whatsapp' ),
					'option_group' => 'ht_ctc_greetings_options',
					'help'         => __( 'Call to Action (Button/Link Text)', 'click-to-chat-for-whatsapp' ),
				),
			);

			$values = array(
				'field_type'     => 'card',
				'title'          => 'Content',
				'description'    => 'Header, Main, Bottom, Call to Action',
				'class_pr'       => 'ctc_greetings_settings',
				'data_watch'     => '#greetings_template',
				'data_show_when' => 'greetings-1,greetings-2,greetings-pro-1,greetings-pro-2,greetings-pro-3',
				'fields'         => array_merge( $fields_before, $fields_after ),
			);

			$values = apply_filters( 'ht_ctc_fh_settings_fields_greetings_greetings_content', $values );
			return $values;
		}

		/**
		 * Greetings Style 1 Card
		 *
		 * @deprecated 4.43 Superseded by HT_CTC_Contextual_Greetings::fields()['greetings_1'],
		 *                 which renders the same fields in the contextual panel beneath the
		 *                 Greetings-1 tile.
		 *
		 *                 This method is commented out in the fields() method and is no longer
		 *                 served to the Fields_Handler. It is retained here for reference.
		 */
		private static function card_greetings_style_1() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Greetings Dialog - 1', 'click-to-chat-for-whatsapp' ),
				'description'    => __( 'Greetings-1 - Customizable Design', 'click-to-chat-for-whatsapp' ),
				'class_pr'       => 'ctc_greetings_settings',
				'data_watch'     => '#greetings_template',
				'data_show_when' => 'greetings-1',
				'fields'         => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 'header_bg_color',
						'label'        => __( 'Header - Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_1',
						'default'      => '#075e54',
						'help'         => __( 'Header - Background Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 'main_bg_color',
						'label'        => __( 'Main Content - Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_1',
						'default'      => '#ece5dd',
						'help'         => __( 'Main Content - Background Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 'message_box_bg_color',
						'label'        => __( 'Message Box - Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_1',
						'default'      => '#dcf8c6',
						'help'         => 'Main Content as a Message Box with Background Color',
					),
					array(
						'field_type' => 'block_content_details',
						'title'      => 'Background image, Call to Action - button type',
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'main_bg_image',
								'label'        => __( 'Background image', 'click-to-chat-for-whatsapp' ),
								'option_group' => 'ht_ctc_greetings_1',
								'help'         => 'Add a WhatsApp-like background image to the main content.',
							),
							array(
								'field_type'   => 'field_select',
								'id'           => 'cta_style',
								'label'        => __( 'Call to Action - button type', 'click-to-chat-for-whatsapp' ),
								'option_group' => 'ht_ctc_greetings_1',
								'options'      => array(

									/*
									 * Each option names the style it stands for, so the
									 * Customize trigger below opens the right one without
									 * deriving an id from the stored value.
									 */
									'1'   => array(
										'label'      => 'Style 1: Theme Button',
										'attributes' => array( 'data-contextual-id' => 'style_1' ),
									),
									'7_1' => array(
										'label'      => 'Style 7 Extend: Rounded Button',
										'attributes' => array( 'data-contextual-id' => 'style_7_1' ),
									),
								),
							),

							/*
							 * Opens the picked button style's settings inline, instead of
							 * sending the user to Click to Chat -> Customize. It follows the
							 * select above, opening whichever style the chosen option names.
							 */
							array(
								'field_type'       => 'block_contextual_trigger',
								'label'            => 'Customize',
								'contextual_group' => 'contextual_styles',
								'contextual_watch' => '#cta_style',
							),
						),
						'style'      => 'margin: 1px 0px 5px 0px;',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/greetings-1/' ),
						'label'      => 'Greetings-1',
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_greetings_greetings_style_1', $values );
			return $values;
		}

		/**
		 * Greetings Style 2 Card
		 *
		 * @deprecated 4.43 Superseded by HT_CTC_Contextual_Greetings::fields()['greetings_2'],
		 *                 which renders the same fields in the contextual panel beneath the
		 *                 Greetings-2 tile.
		 *
		 *                 This method is commented out in the fields() method and is no longer
		 *                 served to the Fields_Handler. It is retained here for reference.
		 */
		private static function card_greetings_style_2() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Greetings Dialog - 2', 'click-to-chat-for-whatsapp' ),
				'description'    => __( 'Greetings-2 - Content Specific', 'click-to-chat-for-whatsapp' ),
				'class_pr'       => 'ctc_greetings_settings',
				'data_watch'     => '#greetings_template',
				'data_show_when' => 'greetings-2',
				'fields'         => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 'bg_color',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_2',
						'default'      => '#ffffff',
						'help'         => 'Greetings Dialog Background Color',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/greetings-2/' ),
						'label'      => 'Greetings-2',
						'icon'       => 'dashicons dashicons-external',
					),
					array(
						'field_type' => 'block_content',
						'content'    => "Customize 'Call to Action' button from 'Click to Chat' -> Customize - Style-1",
						'style'      => 'margin: 5px 0px;',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_greetings_greetings_style_2', $values );
			return $values;
		}

		/**
		 * Opt-in Settings Card
		 */
		private static function card_opt_in_settings() {
			$os              = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );
			$os              = is_array( $os ) ? $os : array();
			$disable_tinymce = isset( $os['disable_tinymce'] );
			$editor_type     = ( $disable_tinymce ) ? 'field_textarea' : 'block_editor_tinymce';
			$tinymce_help    = ( $disable_tinymce ) ? '<br> Enable TinyMCE rich text editor at <a href="#advanced-settings" class="ctc-shortcut-link">Advanced <span class="dashicons dashicons-arrow-right-alt2"></span></a> tab.' : '';

			$values = array(
				'field_type'     => 'card',
				'title'          => 'Opt-in Settings',
				'description'    => 'Get visitor consent before initiating chat',
				'class_pr'       => 'ctc_greetings_settings',
				'data_watch'     => '#greetings_template',
				'data_show_when' => 'greetings-1,greetings-2,greetings-pro-1,greetings-pro-2,greetings-pro-3',
				'fields'         => array(
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 'is_opt_in',
						'label'        => 'Enable Opt-in',
						'option_group' => 'ht_ctc_greetings_settings',
						'help'         => 'Get website visitors\' consent before initiating the chat.<br><strong>Once the website visitor opts in, the consent prompt will not reappear</strong>.<br><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/opt-in/' ) ) . '" target="_blank" class="external-link">Opt-in <span class="dashicons dashicons-external"></span></a>',
					),
					array(
						'field_type'     => $editor_type,
						// full (default) | lite
						'editor_type'    => 'lite',
						'id'             => 'opt_in',
						'label'          => 'Opt-in Message',
						'option_group'   => 'ht_ctc_greetings_settings',
						'help'           => 'Customize the consent message shown to visitors' . $tinymce_help,
						'data_watch'     => '#is_opt_in',
						'data_show_when' => '1',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_greetings_opt_in_settings', $values );
			return $values;
		}

		/**
		 * Additional Settings Card
		 */
		private static function card_additional_settings() {
			$values = array(
				'field_type'     => 'card',
				'title'          => 'Additional Settings',
				'class_pr'       => 'ctc_greetings_settings',
				'data_watch'     => '#greetings_template',
				'data_show_when' => 'greetings-1,greetings-2,greetings-pro-1,greetings-pro-2,greetings-pro-3',
				'fields'         => array(
					array(
						'field_type'   => 'field_select',
						'id'           => 'g_device',
						'label'        => __( 'Display', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_settings',
						'options'      => array(
							'all'     => __( 'Desktop and Mobile', 'click-to-chat-for-whatsapp' ),
							'desktop' => __( 'Desktop Only', 'click-to-chat-for-whatsapp' ),
							'mobile'  => __( 'Mobile Only', 'click-to-chat-for-whatsapp' ),
						),
						'help_click'   => __( 'Display Greetings Dialog based on device', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'g_position',
						'label'        => 'Greetings dialog Position',
						'option_group' => 'ht_ctc_greetings_settings',
						'options'      => array(
							'next'  => 'Next to the Chat Widget',
							'modal' => 'Modal Dialog (Centered)',
						),
						'help_click'   => '<strong>Next to the Chat Widget</strong>: Default - positions the greetings near the chat icon<br><strong>Modal Dialog</strong>: Displays at the center of the screen with a dimmed background.<br> <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-position' ) ) . '" class="external-link">Learn more <span class="dashicons dashicons-external"></span></a>',
						// 'help'         => '<strong>Next to the Chat Button</strong>: Default - positions the greetings near the chat icon<br><strong>Modal Dialog</strong>: Displays at the center of the screen with a dimmed background.<br><em>Note:</em> <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-position" class="external-link">Learn more <span class="dashicons dashicons-external"></span></a>',
						// 'help'         => '<strong>Next to the Chat Button:</strong> Positions the greetings dialog relative to the floating chat button. <br><strong>Modal Dialog:</strong> Displays at the center of the screen with a dimmed background overlay.<br><em>Note:</em> <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-position" class="external-link">Learn more <span class="dashicons dashicons-external"></span></a>',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'g_size',
						'label'        => __( 'Greetings dialog Size', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_settings',
						'options'      => array(
							's' => 'Small',
							'm' => 'Desktop: Medium, Mobile: Full width',
							'l' => 'Desktop: Large, Mobile: Full width',
						),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'g_init',
						'label'        => 'Greetings Initial Stage',
						'option_group' => 'ht_ctc_greetings_settings',
						'options'      => array(
							'default' => 'Preset',
							'open'    => 'Open',
							'close'   => 'Close',
						),
						'help_click'   => '<strong>Preset:</strong> Recommended - On first visit, opens automatically on desktop and stays closed on mobile — further behavior is based on user interaction. <br> <strong>Open:</strong> Displays the greetings dialog on page load or when triggered by actions. If the user closes the dialog, it remains closed and will not reopen automatically unless triggered again. <br> <strong>Close:</strong> Hidden until the user initiates the chat or triggers greeting actions - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-initial-stage' ) ) . '" class="external-link">' . __( 'more info', 'click-to-chat-for-whatsapp' ) . ' <span class="dashicons dashicons-external"></span></a>',
						// 'help'         => '<strong>Preset:</strong> Recommended. Automatically opens on desktop for first-time visitors and stays closed on mobile. <br> <strong>Open:</strong> Always opens on page load. Once closed by a user, it remains closed for that session. <br> <strong>Close:</strong> Remains hidden until the user clicks the chat button or a trigger action occurrs. - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/greetings-initial-stage" class="external-link">more info <span class="dashicons dashicons-external"></span></a>',
					),
				),
			);
			$values = apply_filters( 'ht_ctc_fh_settings_fields_greetings_additional_settings', $values );
			return $values;
		}

		/**
		 * Get greetings template options.
		 *
		 * @return array
		 */
		private static function greetings_templates() {

			$options = array(
				array(
					'value'    => 'no',
					'name'     => 'Disable',
					'icon'     => 'dashicons dashicons-dismiss',
					// 'description' => 'No greetings dialog — chat widget only',
					'sub_text' => 'No greetings dialog — chat widget only',
					// 'sub_text' => 'Displays only the chat widget without a greetings dialog.',
					// this is the image placeholder for grid select
					// 'image'    => 'https://example.com/200x140?text=Disabled',
				),
				array(
					'value'      => 'greetings-1',
					'name'       => 'Greetings-1',
					'icon'       => 'dashicons dashicons-format-chat',
					// 'description' => 'WhatsApp-style chat popup with header, message, and call to action',
					'sub_text'   => 'WhatsApp-style chat popup with header, message, and call to action',
					// 'image'    => '',

					/*
					 * The stored value is hyphenated and the contextual id is not, so the tile
					 * names the item outright rather than either side deriving it.
					 */
					'attributes' => array(
						'data-contextual-group' => 'contextual_greetings',
						'data-contextual-id'    => 'greetings_1',
					),
				),
				array(
					'value'      => 'greetings-2',
					'name'       => 'Greetings-2',
					'icon'       => 'dashicons dashicons-admin-comments',
					'sub_text'   => 'Minimal content-specific dialog — great for targeted page messages',
					// 'image'    => '',
					'attributes' => array(
						'data-contextual-group' => 'contextual_greetings',
						'data-contextual-id'    => 'greetings_2',
					),
				),
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$options[] = array(
					'name'     => 'Greetings Form',
					'icon'     => 'dashicons dashicons-clipboard',
					'pro'      => true,
					'sub_text' => 'Collect visitor info via a form before starting the chat',
					// 'image'    => '',
				);
				$options[] = array(
					'name'     => 'Multi Agent',
					'icon'     => 'dashicons dashicons-groups',
					'pro'      => true,
					'sub_text' => 'Let visitors choose from multiple team members to chat with',
					// 'image'    => '',
				);
				$options[] = array(
					'name'     => 'Form + Multi Agent',
					'icon'     => 'dashicons dashicons-networking',
					'pro'      => true,
					'sub_text' => 'Combine form filling with multi-agent selection',
					// 'image'    => '',
				);
			}

			// When PRO is installed, this filter provides the real selectable options.
			$additional_options = apply_filters( 'ht_ctc_fh_settings_fields_greetings_templates', array() );
			$options            = array_merge( $options, $additional_options );

			return $options;
		}

		/**
		 * Actions Card (for Free triggers / PRO editable settings)
		 *
		 * @return array
		 */
		private static function card_actions() {
			$fields = array(
				array(
					'field_type' => 'block_content',
					'class_pr'   => 'ctc-actions-intro',
					'content'    => '<p class="description"><a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-actions/' ) ) . '" target="_blank">Greetings Actions:</a> Open the greetings dialog automatically — after a time delay or page scroll, or when a visitor interacts with the page.</p>',
				),
				array(
					'field_type'  => 'block_feature_box',
					'class_pr'    => 'click-trigger',
					'icon'        => 'click',
					'label'       => 'Click',
					'badge'       => 'Interaction',
					'badge_class' => 'interaction',
					'link'        => array(
						'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-actions/#click' ),
						'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
					),
					'content'     => 'Opens the greetings dialog when a visitor clicks any element with class name <code class="ctc-feature-code">ctc_greetings</code>.',
				),
				array(
					'field_type'  => 'block_feature_box',
					'class_pr'    => 'viewport-trigger',
					'icon'        => 'eye',
					'label'       => 'Viewport',
					'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
					'badge_class' => 'pro',
					'link'        => array(
						'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-actions/#viewport' ),
						'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
					),
					'content'     => 'Opens the greetings dialog when an element with class name <code class="ctc-feature-code">ctc_greetings_now</code> scrolls into view (25% margin).',
				),
			);

			// Time Delay & Scroll Depth: informational trigger cards shown only for free users.
			// For PRO, the editable Time / Scroll fields are appended via the
			// 'ht_ctc_fh_settings_fields_greetings_actions' filter, so these descriptive
			// cards would be redundant and are omitted.
			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$fields[] = array(
					'field_type'  => 'block_feature_box',
					'class_pr'    => 'time-trigger',
					'icon'        => 'clock',
					'label'       => 'Time Delay',
					'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
					'badge_class' => 'pro',
					'link'        => array(
						'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-actions/#time-delay' ),
						'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
					),
					'content'     => 'Automatically opens the dialog after a configurable time delay on the page.',
				);
				$fields[] = array(
					'field_type'  => 'block_feature_box',
					'class_pr'    => 'scroll-trigger',
					'icon'        => 'arrow-down',
					'label'       => 'Scroll Depth',
					'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
					'badge_class' => 'pro',
					'link'        => array(
						'url'   => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/greetings-actions/#scroll-depth' ),
						'label' => __( 'more info', 'click-to-chat-for-whatsapp' ),
					),
					'content'     => 'Automatically opens the dialog when the visitor scrolls a percentage of the page.',
				);
			}

			$values = array(
				'field_type'     => 'card',
				'title'          => 'Actions',
				'class_pr'       => 'greetings_actions ctc_greetings_settings ctc_g_1 ctc_g_2',
				'data_watch'     => '#greetings_template',
				'data_hide_when' => 'no',
				'fields'         => $fields,
			);

			$values = apply_filters( 'ht_ctc_fh_settings_fields_greetings_actions', $values );

			return $values;
		}
	}
}
