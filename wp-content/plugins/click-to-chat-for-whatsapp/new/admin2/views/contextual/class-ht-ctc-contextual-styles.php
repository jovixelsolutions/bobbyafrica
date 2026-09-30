<?php
/**
 * Contextual Styles Settings Provider
 *
 * Single source of truth for all style contextual field definitions.
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.43
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Contextual_Styles' ) ) {

	/**
	 * Contextual Styles class.
	 */
	class HT_CTC_Contextual_Styles {

		/**
		 * Shared CTA actions options
		 *
		 * @return array
		 */
		private static function cta_actions() {
			return array(
				'hover' => 'On Hover',
				'show'  => __( 'Show', 'click-to-chat-for-whatsapp' ),
				'hide'  => __( 'Hide', 'click-to-chat-for-whatsapp' ),
			);
		}

		/**
		 * Get all style contextual fields.
		 *
		 * @return array<string, array> Keyed by style ID
		 */
		public static function fields() {
			$fields = array();

			$fields['style_1']   = self::style_1();
			$fields['style_2']   = self::style_2();
			$fields['style_3']   = self::style_3();
			$fields['style_3_1'] = self::style_3_1();
			$fields['style_4']   = self::style_4();
			$fields['style_5']   = self::style_5();
			$fields['style_6']   = self::style_6();
			$fields['style_7']   = self::style_7();
			$fields['style_7_1'] = self::style_7_1();
			$fields['style_8']   = self::style_8();
			$fields['style_99']  = self::style_99();

			/**
			 * Filter the whole set of contextual styles.
			 *
			 * Adds or removes styles - PRO registers its own here.
			 *
			 * A style added here also needs its tile to point at it
			 * (data-contextual-group / data-contextual-id, via the option's 'attributes'),
			 * and its option group registered in the settings schema - a key the schema
			 * does not allow is dropped on save without an error.
			 *
			 * @since 4.43
			 * @param array $fields Style definitions keyed by contextual id.
			 */
			// $fields = apply_filters( 'ht_ctc_fh_contextual_fields_styles_fields', $fields );

			/*
			 * Every style here is site-wide - one `ht_ctc_s*` group behind it, whichever picker
			 * opened the panel. And the panel opens AT the picker, which is exactly what makes
			 * the settings look like that picker's own: Desktop and Mobile offer the same eleven
			 * styles, WooCommerce and Greetings offer most of them again, so editing Style 1 from
			 * the Mobile grid silently changes Desktop too. Said before the edit, in the card's
			 * own header, rather than discovered after it.
			 *
			 * No exact place list ("Desktop, Mobile, WooCommerce, Greetings") - those pickers do
			 * not all offer every style, and which of them exist depends on WooCommerce and PRO
			 * being active.
			 *
			 * Applied to the whole group here rather than declared on each style: it is true of
			 * the group, not of any one style, so a style added later cannot forget it.
			 */
			foreach ( $fields as $id => $config ) {
				$fields[ $id ]['note'] = sprintf(
					'These settings belong to %1$s itself. Wherever %1$s is selected - desktop, mobile, or another tab - it uses these same values.',
					$config['title']
				);
			}

			return $fields;
		}

		/**
		 * Style 1
		 *
		 * @return array
		 */
		private static function style_1() {
			return array(
				'title'  => __( 'Style 1', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'A ' . __( 'button that appears like themes button', 'click-to-chat-for-whatsapp' ),
				'fields' => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 's1_text_color',
						'option_group' => 'ht_ctc_s1',
						'label'        => __( 'Text Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's1_bg_color',
						'option_group' => 'ht_ctc_s1',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 's1_add_icon',
						'option_group' => 'ht_ctc_s1',
						'label'        => __( 'Add Icon', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 's1_icon_color',
						'option_group'   => 'ht_ctc_s1',
						'label'          => __( 'Icon Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#25D366',
						'data_watch'     => '#s1_add_icon',
						'data_show_when' => '1',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 's1_icon_size',
						'option_group'   => 'ht_ctc_s1',
						'label'          => __( 'Icon Size', 'click-to-chat-for-whatsapp' ),
						'help'           => __( 'Icon Size  -  E.g. 16px', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#s1_add_icon',
						'data_show_when' => '1',
					),
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 's1_m_fullwidth',
						'option_group' => 'ht_ctc_s1',
						'label'        => __( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'     => 'block_content',
						'id'             => '',
						'label'          => '',
						'content'        => sprintf( 'Set position at <a href="#general-settings/tab-btn-mobile-style" class="ctc-shortcut-link">"%1$s → %2$s"</a>', 'Click to Chat', __( 'Position to Place (Mobile)', 'click-to-chat-for-whatsapp' ) ),
						'class_pr'       => 'description',
						'data_watch'     => '#s1_m_fullwidth',
						'data_show_when' => '1',
					),
				),
			);
		}

		/**
		 * Style 2
		 *
		 * @return array
		 */
		private static function style_2() {
			return array(
				'title'  => __( 'Style 2', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'WhatsApp iOS-style icon',
				'fields' => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 's2_img_size',
						'option_group' => 'ht_ctc_s2',
						'label'        => __( 'Image Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s (e.g. 50px)', __( 'Image Size', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'cta_type',
						'option_group' => 'ht_ctc_s2',
						'label'        => __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ),
						'options'      => self::cta_actions(),
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_textcolor',
						'option_group'   => 'ht_ctc_s2',
						'label'          => __( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#ffffff',
						'data_watch'     => '#style_2 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_bgcolor',
						'option_group'   => 'ht_ctc_s2',
						'label'          => __( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#25D366',
						'data_watch'     => '#style_2 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'cta_font_size',
						'option_group'   => 'ht_ctc_s2',
						'label'          => __( 'Font Size', 'click-to-chat-for-whatsapp' ),
						'help'           => __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#style_2 #cta_type',
						'data_hide_when' => 'hide',
					),
				),
			);
		}

		/**
		 * Style 3
		 *
		 * @return array
		 */
		private static function style_3() {
			return array(
				'title'  => __( 'Style 3', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'WhatsApp Android-style icon',
				'fields' => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 's3_img_size',
						'option_group' => 'ht_ctc_s3',
						'label'        => __( 'Image Size', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'Image Size (Default: 50px )', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'cta_type',
						'option_group' => 'ht_ctc_s3',
						'label'        => __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ),
						'options'      => self::cta_actions(),
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_textcolor',
						'option_group'   => 'ht_ctc_s3',
						'label'          => __( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#ffffff',
						'data_watch'     => '#style_3 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_bgcolor',
						'option_group'   => 'ht_ctc_s3',
						'label'          => __( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#25D366',
						'data_watch'     => '#style_3 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'cta_font_size',
						'option_group'   => 'ht_ctc_s3',
						'label'          => __( 'Font Size', 'click-to-chat-for-whatsapp' ),
						'help'           => __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#style_3 #cta_type',
						'data_hide_when' => 'hide',
					),
				),
			);
		}

		/**
		 * Style 3 Extend
		 *
		 * @return array
		 */
		private static function style_3_1() {
			return array(
				'title'  => __( 'Style 3 Extend', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'WhatsApp Android-style icon',
				'fields' => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 's3_img_size',
						'option_group' => 'ht_ctc_s3_1',
						'label'        => __( 'Image Size', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'Image Size (Default: 40px )', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's3_padding',
						'option_group' => 'ht_ctc_s3_1',
						'label'        => __( 'Padding', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'Padding (Default: 20px )', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's3_bg_color',
						'option_group' => 'ht_ctc_s3_1',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#25D366',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's3_bg_color_hover',
						'option_group' => 'ht_ctc_s3_1',
						'label'        => __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#25D366',
					),
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 's3_box_shadow',
						'option_group' => 'ht_ctc_s3_1',
						'label'        => __( 'Shadow', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'     => 'field_checkbox',
						'id'             => 's3_box_shadow_hover',
						'option_group'   => 'ht_ctc_s3_1',
						'label'          => __( 'Shadow on Hover only', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#s3_box_shadow',
						'data_hide_when' => '1',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'cta_type',
						'option_group' => 'ht_ctc_s3_1',
						'label'        => __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ),
						'options'      => self::cta_actions(),
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_textcolor',
						'option_group'   => 'ht_ctc_s3_1',
						'label'          => __( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#ffffff',
						'data_watch'     => '#style_3_1 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_bgcolor',
						'option_group'   => 'ht_ctc_s3_1',
						'label'          => __( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#25D366',
						'data_watch'     => '#style_3_1 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'cta_font_size',
						'option_group'   => 'ht_ctc_s3_1',
						'label'          => __( 'Font Size', 'click-to-chat-for-whatsapp' ),
						'help'           => __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ),
						'data_watch'     => '#style_3_1 #cta_type',
						'data_hide_when' => 'hide',
					),
				),
			);
		}

		/**
		 * Style 4
		 *
		 * @return array
		 */
		private static function style_4() {
			return array(
				'title'  => 'Style 4',
				'desc'   => 'Chip',
				'fields' => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 's4_text_color',
						'option_group' => 'ht_ctc_s4',
						'label'        => __( 'Text Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#7f7d7d',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's4_bg_color',
						'option_group' => 'ht_ctc_s4',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#e4e4e4',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 's4_img_position',
						'option_group' => 'ht_ctc_s4',
						'label'        => __( 'Image Position', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
							'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
						),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's4_img_url',
						'option_group' => 'ht_ctc_s4',
						'label'        => __( 'Image URL', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'Image URL(leave blank for default image)', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's4_img_size',
						'option_group' => 'ht_ctc_s4',
						'label'        => __( 'Image Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s %2$s', __( 'Image Size (default 32px)', 'click-to-chat-for-whatsapp' ), __( '(possible, keep the value less then or equal to 32px)', 'click-to-chat-for-whatsapp' ) ),
					),
				),
			);
		}

		/**
		 * Style 5
		 *
		 * @return array
		 */
		private static function style_5() {
			return array(
				'title'  => __( 'Style 5', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'Chip with image and content',
				'fields' => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_line_1',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Line 1', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_line_2',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Line 2', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's5_line_1_color',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Line 1 - Text Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#000000',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's5_line_2_color',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Line 2 - Text Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#000000',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's5_background_color',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Content Box Background Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's5_border_color',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Content Box Border Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#dddddd',
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_img',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Image URL', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'Image URL(leave blank for default image)', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_img_height',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Image Height', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s 70px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_img_width',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Image Width', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s 70px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_content_height',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Content Box Height', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s 70px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's5_content_width',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Content Box Width', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'E.g. ', 'click-to-chat-for-whatsapp' ) . '270px, 100%',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 's5_img_position',
						'option_group' => 'ht_ctc_s5',
						'label'        => __( 'Image Position', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
							'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
						),
						'help'         => __( 'If style position/located: Right to screen then select Right, if Left to screen then select Left', 'click-to-chat-for-whatsapp' ),
					),
				),
			);
		}

		/**
		 * Style 6
		 *
		 * @return array
		 */
		private static function style_6() {
			return array(
				'title'  => 'Style 6',
				'desc'   => 'Plain link',
				'fields' => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 's6_txt_color',
						'option_group' => 'ht_ctc_s6',
						'label'        => __( 'Text Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's6_txt_color_on_hover',
						'option_group' => 'ht_ctc_s6',
						'label'        => __( 'Text Color on Hover', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 's6_txt_decoration',
						'option_group' => 'ht_ctc_s6',
						'label'        => __( 'Text Decoration', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'initial'      => 'initial',
							'underline'    => 'underline',
							'overline'     => 'overline',
							'line-through' => 'line-through',
							'inherit'      => 'inherit',
						),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 's6_txt_decoration_on_hover',
						'option_group' => 'ht_ctc_s6',
						'label'        => __( 'Text Decoration when Hover', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'initial'      => 'initial',
							'underline'    => 'underline',
							'overline'     => 'overline',
							'line-through' => 'line-through',
							'inherit'      => 'inherit',
						),
					),
				),
			);
		}

		/**
		 * Style 7
		 *
		 * @return array
		 */
		private static function style_7() {
			return array(
				'title'  => __( 'Style 7', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'Plain link variation',
				'fields' => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 's7_icon_size',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Icon Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s 20px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_icon_color',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Icon Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_icon_color_hover',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Icon Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's7_border_size',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Border Padding Size', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'E.g. 12px', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_border_color',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#25d366',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_border_color_hover',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#25d366',
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's7_border_radius',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Border radius', 'click-to-chat-for-whatsapp' ),
						'help'         => __( 'E.g. 10px, 50% ( for round border add 50% )', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 'cta_type',
						'option_group' => 'ht_ctc_s7',
						'label'        => __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ),
						'options'      => self::cta_actions(),
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_textcolor',
						'option_group'   => 'ht_ctc_s7',
						'label'          => __( 'Text Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#ffffff',
						'data_watch'     => '#style_7 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_color',
						'id'             => 'cta_bgcolor',
						'option_group'   => 'ht_ctc_s7',
						'label'          => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'default'        => '#25d366',
						'data_watch'     => '#style_7 #cta_type',
						'data_hide_when' => 'hide',
					),
					array(
						'field_type'     => 'field_text',
						'id'             => 'cta_font_size',
						'option_group'   => 'ht_ctc_s7',
						'label'          => __( 'Font Size', 'click-to-chat-for-whatsapp' ),
						'help'           => sprintf( '%1$s %2$s', __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ),
						'data_watch'     => '#style_7 #cta_type',
						'data_hide_when' => 'hide',
					),
				),
			);
		}

		/**
		 * Style 7 Extend
		 *
		 * @return array
		 */
		private static function style_7_1() {
			return array(
				'title'  => __( 'Style 7 Extend', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'Extended plain link variation',
				'fields' => array(
					array(
						'field_type'   => 'field_select',
						'id'           => 'cta_type',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'hover' => 'On Hover',
							'show'  => __( 'Show', 'click-to-chat-for-whatsapp' ),
						),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's7_icon_size',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Icon Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s 20px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's7_border_size',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Icon Border Padding Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s 12px', __( 'E.g. ', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_icon_color',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Icon,Text Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_icon_color_hover',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Icon,Text Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#f4f4f4',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_bgcolor',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#25d366',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's7_bgcolor_hover',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#25d366',
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 'cta_font_size',
						'option_group' => 'ht_ctc_s7_1',
						'label'        => __( 'Font Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s %2$s', __( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ),
					),
				),
			);
		}

		/**
		 * Style 8
		 *
		 * @return array
		 */
		private static function style_8() {
			return array(
				'title'  => __( 'Style 8', 'click-to-chat-for-whatsapp' ),
				'desc'   => 'Button with icon',
				'fields' => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 's8_txt_color',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Text Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's8_txt_color_on_hover',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Text Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's8_bg_color',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#26a69a',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's8_bg_color_on_hover',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#26a69a',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's8_icon_color',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Icon Color', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 's8_icon_color_on_hover',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Icon Color on Hover', 'click-to-chat-for-whatsapp' ),
						'default'      => '#ffffff',
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 's8_icon_position',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Icon Position', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
							'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
							'hide'  => __( 'Hide', 'click-to-chat-for-whatsapp' ),
						),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's8_text_size',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Text Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s %2$s', __( 'Text Size  -  E.g. 12px', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's8_icon_size',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Icon Size', 'click-to-chat-for-whatsapp' ),
						'help'         => sprintf( '%1$s %2$s', __( 'Icon Size  -  E.g. 16px', 'click-to-chat-for-whatsapp' ), __( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ) ),
					),
					array(
						'field_type'   => 'field_select',
						'id'           => 's8_btn_size',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Button Size', 'click-to-chat-for-whatsapp' ),
						'options'      => array(
							'btn'       => __( 'Normal', 'click-to-chat-for-whatsapp' ),
							'btn-large' => __( 'Large', 'click-to-chat-for-whatsapp' ),
						),
					),
					array(
						'field_type'   => 'field_checkbox',
						'id'           => 's8_m_fullwidth',
						'option_group' => 'ht_ctc_s8',
						'label'        => __( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'     => 'block_content',
						'id'             => '',
						'label'          => '',
						'content'        => sprintf( 'Set position at <a href="#general-settings/tab-btn-mobile-style" class="ctc-shortcut-link">"%1$s → %2$s"</a>', 'Click to Chat', __( 'Position to Place (Mobile)', 'click-to-chat-for-whatsapp' ) ),
						'class_pr'       => 'description',
						'data_watch'     => '#s8_m_fullwidth',
						'data_show_when' => '1',
					),
				),
			);
		}

		/**
		 * Style 99
		 *
		 * @return array
		 */
		private static function style_99() {
			return array(
				'title'  => 'Style 99',
				'desc'   => __( 'Add your own image / GIF (Style-99)', 'click-to-chat-for-whatsapp' ),
				'fields' => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 's99_dekstop_img_url',
						'option_group' => 'ht_ctc_s99',
						'label'        => __( 'Image URL - Desktop', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's99_mobile_img_url',
						'option_group' => 'ht_ctc_s99',
						'label'        => __( 'Image URL - Mobile', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's99_desktop_img_height',
						'option_group' => 'ht_ctc_s99',
						'label'        => __( 'Desktop - Image Height', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's99_desktop_img_width',
						'option_group' => 'ht_ctc_s99',
						'label'        => __( 'Desktop - Image Width', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's99_mobile_img_height',
						'option_group' => 'ht_ctc_s99',
						'label'        => __( 'Mobile - Image Height', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 's99_mobile_img_width',
						'option_group' => 'ht_ctc_s99',
						'label'        => __( 'Mobile - Image Width', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type' => 'block_external_link',
						'id'         => 's99_doc',
						'label'      => 'Style 99 - own image / GIF',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/style-99/' ),
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);
		}
	}
}
