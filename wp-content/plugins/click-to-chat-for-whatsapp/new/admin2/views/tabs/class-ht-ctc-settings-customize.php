<?php
/**
 * Customize Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 * @deprecated 4.43 Use Contextual Settings Panel / new fields provider instead.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Deliberately NOT _deprecated_file(). This file is only ever loaded from inside a REST
 * request (get_tab_fields() -> /get-fields/), and _deprecated_file() raises an
 * E_USER_DEPRECATED notice — which, on a site with WP_DEBUG_DISPLAY on, prints ahead of
 * the JSON body and turns a working response into a parse error in the admin SPA.
 *
 * The @deprecated tags above and below are the whole notice: they reach a developer
 * reading the file without breaking a user's admin screen.
 */

if ( ! class_exists( 'HT_CTC_Settings_Customize' ) ) {

	/**
	 * Customize settings class.
	 *
	 * @deprecated 4.43 Use Contextual Settings Panel / new fields provider instead.
	 */
	class HT_CTC_Settings_Customize {

		/**
		 * Get fields
		 *
		 * @deprecated 4.43
		 * @return array
		 */
		public static function fields() {
			$cta_actions = array(
				'hover' => 'On Hover',
				'show'  => __( 'Show', 'click-to-chat-for-whatsapp' ),
				'hide'  => __( 'Hide', 'click-to-chat-for-whatsapp' ),
			);

			$styles = array(
				'style_1'   => array(
					'title'  => __( 'Style 1', 'click-to-chat-for-whatsapp' ),
					// 'desc'   => 'Button that appears like themes button',
					'desc'   => 'A ' . __( 'button that appears like themes button', 'click-to-chat-for-whatsapp' ),
					'opt'    => 'ht_ctc_s1',
					'fields' => array(
						array( 'field_color', 's1_text_color', __( 'Text Color', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_color', 's1_bg_color', __( 'Background Color', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_checkbox', 's1_add_icon', __( 'Add Icon', 'click-to-chat-for-whatsapp' ) ),
						array(
							'field_color',
							's1_icon_color',
							__( 'Icon Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#25D366',
								'data_watch'     => '#s1_add_icon',
								'data_show_when' => '1',
							),
						),
						array(
							'field_text',
							's1_icon_size',
							__( 'Icon Size', 'click-to-chat-for-whatsapp' ),
							array(
								// 'default'        => '16px',
								'help'           => __( 'Icon Size  -  E.g. 16px', 'click-to-chat-for-whatsapp' ),
								'data_watch'     => '#s1_add_icon',
								'data_show_when' => '1',
							),
						),
						array( 'field_checkbox', 's1_m_fullwidth', __( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ) ),
						array(
							'block_content',
							'',
							'',
							array(
								'content'        => sprintf( 'Set position at <a href="#position_to_place" target="_blank" class="ctc-shortcut-link"> "%1$s → %2$s" </a>', 'Click to Chat', __( 'Position to Place (Mobile)', 'click-to-chat-for-whatsapp' ) ),
								'class_pr'       => 'description',
								'data_watch'     => '#s1_m_fullwidth',
								'data_show_when' => '1',
							),
						),
					),
				),
				'style_2'   => array(
					'title'  => __( 'Style 2', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'WhatsApp iOS-style icon',
					'opt'    => 'ht_ctc_s2',
					'fields' => array(
						array( 'field_text', 's2_img_size', __( 'Image Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s (e.g. 50px)', __( 'Image Size', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_select', 'cta_type', __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ), array( 'options' => $cta_actions ) ),
						array(
							'field_color',
							'cta_textcolor',
							__( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#ffffff',
								'data_watch'     => '#style_2 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_color',
							'cta_bgcolor',
							__( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#25D366',
								'data_watch'     => '#style_2 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_text',
							'cta_font_size',
							__( 'Font Size', 'click-to-chat-for-whatsapp' ),
							array(
								'help'           => __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ),
								'data_watch'     => '#style_2 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
					),
				),
				'style_3'   => array(
					'title'  => __( 'Style 3', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'WhatsApp Android-style icon',
					'opt'    => 'ht_ctc_s3',
					'fields' => array(
						array( 'field_text', 's3_img_size', __( 'Image Size', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'Image Size (Default: 50px )', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_select', 'cta_type', __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ), array( 'options' => $cta_actions ) ),
						array(
							'field_color',
							'cta_textcolor',
							__( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#ffffff',
								'data_watch'     => '#style_3 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_color',
							'cta_bgcolor',
							__( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#25D366',
								'data_watch'     => '#style_3 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_text',
							'cta_font_size',
							__( 'Font Size', 'click-to-chat-for-whatsapp' ),
							array(
								'help'           => __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ),
								'data_watch'     => '#style_3 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
					),
				),
				'style_3_1' => array(
					// 'title'  => 'Style 3_1',
					'title'  => __( 'Style 3 Extend', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'WhatsApp Android-style icon',
					'opt'    => 'ht_ctc_s3_1',
					'fields' => array(
						array( 'field_text', 's3_img_size', __( 'Image Size', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'Image Size (Default: 40px )', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_text', 's3_padding', __( 'Padding', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'Padding (Default: 20px )', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_color', 's3_bg_color', __( 'Background Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#25D366' ) ),
						array( 'field_color', 's3_bg_color_hover', __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#25D366' ) ),
						array( 'field_checkbox', 's3_box_shadow', __( 'Shadow', 'click-to-chat-for-whatsapp' ) ),
						array(
							'field_checkbox',
							's3_box_shadow_hover',
							__( 'Shadow on Hover only', 'click-to-chat-for-whatsapp' ),
							array(
								'data_watch'     => '#s3_box_shadow',
								'data_hide_when' => '1',
							),
						),
						array( 'field_select', 'cta_type', __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ), array( 'options' => $cta_actions ) ),
						array(
							'field_color',
							'cta_textcolor',
							__( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#ffffff',
								'data_watch'     => '#style_3_1 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_color',
							'cta_bgcolor',
							__( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#25D366',
								'data_watch'     => '#style_3_1 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_text',
							'cta_font_size',
							__( 'Font Size', 'click-to-chat-for-whatsapp' ),
							array(
								'help'           => __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ),
								'data_watch'     => '#style_3_1 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
					),
				),
				'style_4'   => array(
					'title'  => 'Style 4',
					'desc'   => 'Chip',
					'opt'    => 'ht_ctc_s4',
					'fields' => array(
						array( 'field_color', 's4_text_color', __( 'Text Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#7f7d7d' ) ),
						array( 'field_color', 's4_bg_color', __( 'Background Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#e4e4e4' ) ),
						array(
							'field_select',
							's4_img_position',
							__( 'Image Position', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
									'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
								),
							),
						),
						array( 'field_text', 's4_img_url', __( 'Image URL', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'Image URL(leave blank for default image)', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_text', 's4_img_size', __( 'Image Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s %2$s', __( 'Image Size (default 32px)', 'click-to-chat-for-whatsapp' ), __( '(possible, keep the value less then or equal to 32px)', 'click-to-chat-for-whatsapp' ) ) ) ),
						// array( 'field_text', 's4_img_size', __( 'Image Size', 'click-to-chat-for-whatsapp' ), array( 'help' => 'Image Size (default 32px). It is recommended to keep this value at or below 32px.' ) ) ,
					),
				),
				'style_5'   => array(
					'title'  => __( 'Style 5', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Chip with image and content',
					'opt'    => 'ht_ctc_s5',
					'fields' => array(
						array( 'field_text', 's5_line_1', __( 'Line 1', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_text', 's5_line_2', __( 'Line 2', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_color', 's5_line_1_color', __( 'Line 1 - Text Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#000000' ) ),
						array( 'field_color', 's5_line_2_color', __( 'Line 2 - Text Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#000000' ) ),
						array( 'field_color', 's5_background_color', __( 'Content Box Background Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_color', 's5_border_color', __( 'Content Box Border Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#dddddd' ) ),
						array( 'field_text', 's5_img', __( 'Image URL', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'Image URL(leave blank for default image)', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_text', 's5_img_height', __( 'Image Height', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s 70px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_text', 's5_img_width', __( 'Image Width', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s 70px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_text', 's5_content_height', __( 'Content Box Height', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s 70px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_text', 's5_content_width', __( 'Content Box Width', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'E.g. ', 'click-to-chat-for-whatsapp' ) . '270px, 100%' ) ),
						// array( 'field_select', 's5_img_position', __( 'Image Position', 'click-to-chat-for-whatsapp' ), array( 'options' => array( 'right' => __( 'Right', 'click-to-chat-for-whatsapp' ), 'left' => __( 'Left', 'click-to-chat-for-whatsapp' ) ), 'help' => 'Select the side relative to the edge of the screen where the widget is positioned.' ) ),
						array(
							'field_select',
							's5_img_position',
							__( 'Image Position', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
									'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
								),
								'help'    => __( 'If style position/located: Right to screen then select Right, if Left to screen then select Left', 'click-to-chat-for-whatsapp' ),
							),
						),
					),
				),
				'style_6'   => array(
					'title'  => 'Style 6',
					'desc'   => 'Plain link',
					'opt'    => 'ht_ctc_s6',
					'fields' => array(
						array( 'field_color', 's6_txt_color', __( 'Text Color', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_color', 's6_txt_color_on_hover', __( 'Text Color on Hover', 'click-to-chat-for-whatsapp' ) ),
						array(
							'field_select',
							's6_txt_decoration',
							__( 'Text Decoration', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'initial'      => 'initial',
									'underline'    => 'underline',
									'overline'     => 'overline',
									'line-through' => 'line-through',
									'inherit'      => 'inherit',
								),
							),
						),
						array(
							'field_select',
							's6_txt_decoration_on_hover',
							__( 'Text Decoration when Hover', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'initial'      => 'initial',
									'underline'    => 'underline',
									'overline'     => 'overline',
									'line-through' => 'line-through',
									'inherit'      => 'inherit',
								),
							),
						),
					),
				),
				'style_7'   => array(
					'title'  => __( 'Style 7', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Plain link variation',
					'opt'    => 'ht_ctc_s7',
					'fields' => array(
						array( 'field_text', 's7_icon_size', __( 'Icon Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s 20px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_color', 's7_icon_color', __( 'Icon Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_color', 's7_icon_color_hover', __( 'Icon Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_text', 's7_border_size', __( 'Border Padding Size', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'E.g. 12px', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_color', 's7_border_color', __( 'Background Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#25d366' ) ),
						array( 'field_color', 's7_border_color_hover', __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#25d366' ) ),
						array( 'field_text', 's7_border_radius', __( 'Border radius', 'click-to-chat-for-whatsapp' ), array( 'help' => __( 'E.g. 10px, 50% ( for round border add 50% )', 'click-to-chat-for-whatsapp' ) ) ),
						array( 'field_select', 'cta_type', __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ), array( 'options' => $cta_actions ) ),
						array(
							'field_color',
							'cta_textcolor',
							__( 'Text Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#ffffff',
								'data_watch'     => '#style_7 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_color',
							'cta_bgcolor',
							__( 'Background Color', 'click-to-chat-for-whatsapp' ),
							array(
								'default'        => '#25d366',
								'data_watch'     => '#style_7 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
						array(
							'field_text',
							'cta_font_size',
							__( 'Font Size', 'click-to-chat-for-whatsapp' ),
							array(
								'help'           => sprintf( '%1$s %2$s', __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ),
								'data_watch'     => '#style_7 #cta_type',
								'data_hide_when' => 'hide',
							),
						),
					),
				),
				'style_7_1' => array(
					// 'title'  => 'Style 7_1',
					'title'  => __( 'Style 7 Extend', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Extended plain link variation',
					'opt'    => 'ht_ctc_s7_1',
					'fields' => array(
						array(
							'field_select',
							'cta_type',
							__( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'hover' => 'On Hover',
									'show'  => __( 'Show', 'click-to-chat-for-whatsapp' ),
								),
							),
						),
						array( 'field_text', 's7_icon_size', __( 'Icon Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s 20px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_text', 's7_border_size', __( 'Icon Border Padding Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s 12px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_color', 's7_icon_color', __( 'Icon,Text Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_color', 's7_icon_color_hover', __( 'Icon,Text Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#f4f4f4' ) ),
						array( 'field_color', 's7_bgcolor', __( 'Background Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#25d366' ) ),
						array( 'field_color', 's7_bgcolor_hover', __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#25d366' ) ),
						array( 'field_text', 'cta_font_size', __( 'Font Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s %2$s', __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ) ) ),
					),
				),
				'style_8'   => array(
					'title'  => __( 'Style 8', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Button with icon',
					'opt'    => 'ht_ctc_s8',
					'fields' => array(
						array( 'field_color', 's8_txt_color', __( 'Text Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_color', 's8_txt_color_on_hover', __( 'Text Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_color', 's8_bg_color', __( 'Background Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#26a69a' ) ),
						array( 'field_color', 's8_bg_color_on_hover', __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#26a69a' ) ),
						array( 'field_color', 's8_icon_color', __( 'Icon Color', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array( 'field_color', 's8_icon_color_on_hover', __( 'Icon Color on Hover', 'click-to-chat-for-whatsapp' ), array( 'default' => '#ffffff' ) ),
						array(
							'field_select',
							's8_icon_position',
							__( 'Icon Position', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
									'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
									'hide'  => __( 'Hide', 'click-to-chat-for-whatsapp' ),
								),
							),
						),
						array( 'field_text', 's8_text_size', __( 'Text Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s %2$s', __( 'Text Size  -  E.g. 12px', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ) ) ),
						array( 'field_text', 's8_icon_size', __( 'Icon Size', 'click-to-chat-for-whatsapp' ), array( 'help' => sprintf( '%1$s %2$s', __( 'Icon Size  -  E.g. 16px', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ) ) ),
						array(
							'field_select',
							's8_btn_size',
							__( 'Button Size', 'click-to-chat-for-whatsapp' ),
							array(
								'options' => array(
									'btn'       => __( 'Normal', 'click-to-chat-for-whatsapp' ),
									'btn-large' => __( 'Large', 'click-to-chat-for-whatsapp' ),
								),
							),
						),
						array( 'field_checkbox', 's8_m_fullwidth', __( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ) ),
						array(
							'block_content',
							'',
							'',
							array(
								'content'        => sprintf( 'Set position at <a href="#position_to_place" target="_blank" class="ctc-shortcut-link"> Click to Chat → %1$s</a>', __( 'Position to Place (Mobile)', 'click-to-chat-for-whatsapp' ) ),
								'class_pr'       => 'description',
								'data_watch'     => '#s8_m_fullwidth',
								'data_show_when' => '1',
							),
						),
					),
				),
				'style_99'  => array(
					'title'  => 'Style 99',
					'desc'   => __( 'Add your own image / GIF (Style-99)', 'click-to-chat-for-whatsapp' ),
					'opt'    => 'ht_ctc_s99',
					'fields' => array(
						array( 'field_text', 's99_dekstop_img_url', __( 'Image URL - Desktop', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_text', 's99_mobile_img_url', __( 'Image URL - Mobile', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_text', 's99_desktop_img_height', __( 'Desktop - Image Height', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_text', 's99_desktop_img_width', __( 'Desktop - Image Width', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_text', 's99_mobile_img_height', __( 'Mobile - Image Height', 'click-to-chat-for-whatsapp' ) ),
						array( 'field_text', 's99_mobile_img_width', __( 'Mobile - Image Width', 'click-to-chat-for-whatsapp' ) ),
						array(
							'block_external_link',
							's99_doc',
							'Style 99 - own image / GIF',
							array(
								'url'  => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/style-99/' ),
								'icon' => 'dashicons dashicons-external',
							),
						),
					),
				),
			);

			$results = array();

			foreach ( $styles as $id => $config ) {
				$parsed_fields = array();
				foreach ( $config['fields'] as $f ) {
					$base = array(
						'field_type'   => $f[0],
						'id'           => $f[1],
						'label'        => isset( $f[2] ) ? $f[2] : '',
						'option_group' => $config['opt'],
					);
					if ( isset( $f[3] ) && is_array( $f[3] ) ) {
						$base = array_merge( $base, $f[3] );
					}
					$parsed_fields[] = $base;
				}

				$card_values = array(
					array(
						'field_type'   => 'card',
						'id'           => $id,
						'title'        => $config['title'],
						'description'  => $config['desc'],
						'option_group' => $config['opt'],
						'fields'       => $parsed_fields,
					),
				);

				// Uncomment below if you'd like to apply filters
				// $card_values = apply_filters( "ht_ctc_fh_customize_{$id}", $card_values );

				$results[ $id ] = $card_values;
			}

			return $results;
		}
	}
}
