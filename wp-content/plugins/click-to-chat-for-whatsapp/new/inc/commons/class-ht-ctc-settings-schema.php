<?php
/**
 * Per-group settings schema definitions.
 *
 * One method per option group, named exactly like the option group, so
 * HT_CTC_Settings_Data::get_schema() can dispatch to it by name — mirroring how
 * HT_CTC_Utils::get_option() dispatches to HT_CTC_Defaults.
 *
 * Each method returns a slice describing:
 *   - allowed_keys           (string[]): allow-listed top-level keys for the group.
 *   - allowed_keys_patterns  (string[]): regex patterns for dynamic/repeating top-level keys.
 *   - sanitization_callbacks (array<string,string>): field key => sanitizer callback.
 *
 * Each method assigns its slice to $values and exposes a (currently commented)
 * per-group filter hook — ht_ctc_fh_settings_schema_<group> — for PRO / add-ons to
 * extend at a later stage, then returns $values.
 *
 * Sanitizers are listed per group only for keys present in that group's allowed_keys.
 * Field-name => callback hints that aren't tied to any allow-listed key (e.g. 'r_nums',
 * 'greeting', 'enable_feature') stay in HT_CTC_Settings_Data::get_sanitize_map().
 *
 * @package Click_To_Chat
 * @subpackage commons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Schema' ) ) {

	/**
	 * Provides per-group settings schema definitions.
	 */
	class HT_CTC_Settings_Schema {

		/**
		 * Schema for ht_ctc_chat_options (general).
		 *
		 * @return array
		 */
		public function ht_ctc_chat_options() {
			$values = array(
				'allowed_keys'           => array(
					// top-level
					'cc',
					'num',
					'number',
					'pre_filled',
					'call_to_action',
					'style_desktop',
					'position_type',
					'side_1',
					'side_1_value',
					'side_2',
					'side_2_value',
					'same_settings',
					'style_mobile',
					'position_type_mobile',
					'mobile_side_1',
					'mobile_side_1_value',
					'mobile_side_2',
					'mobile_side_2_value',
					'select_styles_issue',
					'url_target_d',
					'url_structure_d',
					'custom_url_d',
					'url_structure_m',
					'custom_url_m',
					'display_desktop',
					'display_mobile',
					'display',
					// nested: display sub-keys
					'global_display',
					'home',
					'posts',
					'pages',
					'archive',
					'category',
					'page_404',
					'woo_product',
					'woo_shop',
					'woo_cart',
					'woo_checkout',
					'woo_order_received',
					'woo_account',
					'list_hideon_pages',
					'list_hideon_cat',
					'list_showon_pages',
					'list_showon_cat',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'number'              => 'ctc_sanitize_whatsapp_number',
					'call_to_action'      => 'ctc_sanitize_emoji_text',
					'pre_filled'          => 'ctc_sanitize_emoji_textarea',
					'side_1_value'        => 'ctc_sanitize_normalize_css_suffix',
					'side_2_value'        => 'ctc_sanitize_normalize_css_suffix',
					'mobile_side_1_value' => 'ctc_sanitize_normalize_css_suffix',
					'mobile_side_2_value' => 'ctc_sanitize_normalize_css_suffix',
				),
			);

			foreach ( $this->get_display_custom_post_type_keys() as $cpt ) {
				$values['allowed_keys'][] = $cpt;
			}

			return $values;
		}

		/**
		 * Schema for ht_ctc_othersettings (advanced).
		 *
		 * @return array
		 */
		public function ht_ctc_othersettings() {
			$values = array(
				'allowed_keys'           => array(
					'amp',
					'an_delay',
					'an_itr',
					'an_type',
					'analytics',
					'aria',
					'chat_load_hook',
					'chat_wrapper_tag',
					'delete_options',
					'disable_page_level_settings',
					'disable_tinymce',
					'enable_group',
					'enable_share',
					'fb_pixel',
					'g_an',
					'g_an_event_name',
					'g_an_params',
					// // If the custom editor field_select is implemented uncomment this...
					// 'greetings_editor',
					'gtm',
					'gtm_event_name',
					'gtm_params',
					'js_load',
					'no-intl',
					'notification_badge',
					'notification_bg_color',
					'notification_border_color',
					'notification_count',
					'notification_text_color',
					'notification_time',
					'pixel_custom_event_name',
					'pixel_event_type',
					'pixel_params',
					'pixel_standard_event_name',
					'hook_url',
					'hook_v',
					'webhook_format',
					'show_effect',
					'zindex',
					// Nested analytics row keys.
					'key',
					'value',
				),
				'allowed_keys_patterns'  => array(
					'/^\d+$/',
				),
				'sanitization_callbacks' => array(
					// URL fields must use esc_url_raw (via ctc_sanitize_url) to reject
					// javascript:/data: URIs (see the s99 schema note).
					'hook_url' => 'ctc_sanitize_url',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 'notification_bg_color'     => 'ctc_sanitize_color',
					// 'notification_border_color' => 'ctc_sanitize_color',
					// 'notification_text_color'   => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_code_blocks (advanced).
		 *
		 * @return array
		 */
		public function ht_ctc_code_blocks() {
			$values = array(
				'allowed_keys'           => array(
					'custom_css',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'custom_css' => 'ctc_sanitize_custom_css',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_greetings_options.
		 *
		 * @return array
		 */
		public function ht_ctc_greetings_options() {
			$values = array(
				'allowed_keys'           => array(
					'greetings_template',
					'header_content',
					'g_header_image',
					'g_header_online_status_color',
					'g_header_offline_status_color',
					'g_header_online_status',
					'g_device',
					'g_init',
					'main_content',
					'bottom_content',
					'call_to_action',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'header_content' => 'ctc_sanitize_text_editor',
					'main_content'   => 'ctc_sanitize_text_editor',
					'bottom_content' => 'ctc_sanitize_text_editor',
					'call_to_action' => 'ctc_sanitize_emoji_text',
					// URL field (header image) — esc_url_raw via ctc_sanitize_url rejects
					// javascript:/data: URIs (see the s99 schema note).
					'g_header_image' => 'ctc_sanitize_url',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 'g_header_online_status_color'  => 'ctc_sanitize_color',
					// 'g_header_offline_status_color' => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_greetings_1.
		 *
		 * @return array
		 */
		public function ht_ctc_greetings_1() {
			$values = array(
				'allowed_keys'           => array(
					'header_bg_color',
					'main_bg_color',
					'message_box_bg_color',
					'main_bg_image',
					'cta_style',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 'header_bg_color'      => 'ctc_sanitize_color',
					// 'main_bg_color'        => 'ctc_sanitize_color',
					// 'message_box_bg_color' => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_greetings_2.
		 *
		 * @return array
		 */
		public function ht_ctc_greetings_2() {
			$values = array(
				'allowed_keys'           => array(
					'bg_color',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 'bg_color' => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_greetings_settings.
		 *
		 * @return array
		 */
		public function ht_ctc_greetings_settings() {
			$values = array(
				'allowed_keys'           => array(
					'is_opt_in',
					'opt_in',
					'g_device',
					'g_position',
					'g_size',
					'g_init',
					'g_first_setup',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'opt_in' => 'ctc_sanitize_text_editor',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_admin_settings.
		 *
		 * @return array
		 */
		public function ht_ctc_admin_settings() {
			$values = array(
				'allowed_keys'           => array(
					'theme',
					'auto_save',
					'admin_ui',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s1 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s1() {
			$values = array(
				'allowed_keys'           => array(
					's1_text_color',
					's1_bg_color',
					's1_add_icon',
					's1_icon_color',
					's1_icon_size',
					's1_m_fullwidth',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's1_icon_size' => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's1_text_color' => 'ctc_sanitize_color',
					// 's1_bg_color'   => 'ctc_sanitize_color',
					// 's1_icon_color' => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s2 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s2() {
			$values = array(
				'allowed_keys'           => array(
					's2_img_size',
					'cta_type',
					'cta_textcolor',
					'cta_bgcolor',
					'cta_font_size',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's2_img_size'   => 'ctc_sanitize_normalize_css_suffix',
					'cta_font_size' => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 'cta_textcolor' => 'ctc_sanitize_color',
					// 'cta_bgcolor'   => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s3 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s3() {
			$values = array(
				'allowed_keys'           => array(
					's3_img_size',
					'cta_type',
					'cta_textcolor',
					'cta_bgcolor',
					'cta_font_size',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's3_img_size'   => 'ctc_sanitize_normalize_css_suffix',
					'cta_font_size' => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 'cta_textcolor' => 'ctc_sanitize_color',
					// 'cta_bgcolor'   => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s3_1 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s3_1() {
			$values = array(
				'allowed_keys'           => array(
					's3_img_size',
					's3_padding',
					's3_bg_color',
					's3_bg_color_hover',
					's3_box_shadow',
					's3_box_shadow_hover',
					'cta_type',
					'cta_textcolor',
					'cta_bgcolor',
					'cta_font_size',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's3_img_size'   => 'ctc_sanitize_normalize_css_suffix',
					's3_padding'    => 'ctc_sanitize_normalize_css_suffix',
					'cta_font_size' => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's3_bg_color'       => 'ctc_sanitize_color',
					// 's3_bg_color_hover' => 'ctc_sanitize_color',
					// 'cta_textcolor'     => 'ctc_sanitize_color',
					// 'cta_bgcolor'       => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s4 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s4() {
			$values = array(
				'allowed_keys'           => array(
					's4_text_color',
					's4_bg_color',
					's4_img_position',
					's4_img_url',
					's4_img_size',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					// URL fields must use esc_url_raw (via ctc_sanitize_url) to reject
					// javascript:/data: URIs (see the s99 schema note).
					's4_img_url'  => 'ctc_sanitize_url',
					's4_img_size' => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's4_text_color' => 'ctc_sanitize_color',
					// 's4_bg_color'   => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s5 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s5() {
			$values = array(
				'allowed_keys'           => array(
					's5_line_1',
					's5_line_2',
					's5_line_1_color',
					's5_line_2_color',
					's5_background_color',
					's5_border_color',
					's5_img',
					's5_img_height',
					's5_img_width',
					's5_content_height',
					's5_content_width',
					's5_img_position',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					// URL fields must use esc_url_raw (via ctc_sanitize_url) to reject
					// javascript:/data: URIs (see the s99 schema note).
					's5_img'            => 'ctc_sanitize_url',
					's5_img_height'     => 'ctc_sanitize_normalize_css_suffix',
					's5_img_width'      => 'ctc_sanitize_normalize_css_suffix',
					's5_content_height' => 'ctc_sanitize_normalize_css_suffix',
					's5_content_width'  => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's5_line_1_color'     => 'ctc_sanitize_color',
					// 's5_line_2_color'     => 'ctc_sanitize_color',
					// 's5_background_color' => 'ctc_sanitize_color',
					// 's5_border_color'     => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s6 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s6() {
			$values = array(
				'allowed_keys'           => array(
					's6_txt_color',
					's6_txt_color_on_hover',
					's6_txt_decoration',
					's6_txt_decoration_on_hover',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's6_txt_color'          => 'ctc_sanitize_color',
					// 's6_txt_color_on_hover' => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s7 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s7() {
			$values = array(
				'allowed_keys'           => array(
					's7_icon_size',
					's7_icon_color',
					's7_icon_color_hover',
					's7_border_size',
					's7_border_color',
					's7_border_color_hover',
					's7_border_radius',
					'cta_type',
					'cta_textcolor',
					'cta_bgcolor',
					'cta_font_size',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's7_icon_size'     => 'ctc_sanitize_normalize_css_suffix',
					's7_border_size'   => 'ctc_sanitize_normalize_css_suffix',
					's7_border_radius' => 'ctc_sanitize_normalize_css_suffix',
					'cta_font_size'    => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's7_icon_color'         => 'ctc_sanitize_color',
					// 's7_icon_color_hover'   => 'ctc_sanitize_color',
					// 's7_border_color'       => 'ctc_sanitize_color',
					// 's7_border_color_hover' => 'ctc_sanitize_color',
					// 'cta_textcolor'         => 'ctc_sanitize_color',
					// 'cta_bgcolor'           => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s7_1 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s7_1() {
			$values = array(
				'allowed_keys'           => array(
					's7_icon_size',
					's7_icon_color',
					's7_icon_color_hover',
					's7_border_size',
					's7_bgcolor',
					's7_bgcolor_hover',
					'cta_type',
					'cta_font_size',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's7_icon_size'   => 'ctc_sanitize_normalize_css_suffix',
					's7_border_size' => 'ctc_sanitize_normalize_css_suffix',
					'cta_font_size'  => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's7_icon_color'       => 'ctc_sanitize_color',
					// 's7_icon_color_hover' => 'ctc_sanitize_color',
					// 's7_bgcolor'          => 'ctc_sanitize_color',
					// 's7_bgcolor_hover'    => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s8 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s8() {
			$values = array(
				'allowed_keys'           => array(
					's8_txt_color',
					's8_txt_color_on_hover',
					's8_bg_color',
					's8_bg_color_on_hover',
					's8_icon_color',
					's8_icon_color_on_hover',
					's8_icon_position',
					's8_text_size',
					's8_icon_size',
					's8_btn_size',
					's8_m_fullwidth',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					's8_text_size' => 'ctc_sanitize_normalize_css_suffix',
					's8_icon_size' => 'ctc_sanitize_normalize_css_suffix',
					// Color fields — wiring deferred until the gradient color picker lands.
					// Enable with ctc_sanitize_color (see HT_CTC_Sanitizer).
					// 's8_txt_color'           => 'ctc_sanitize_color',
					// 's8_txt_color_on_hover'  => 'ctc_sanitize_color',
					// 's8_bg_color'            => 'ctc_sanitize_color',
					// 's8_bg_color_on_hover'   => 'ctc_sanitize_color',
					// 's8_icon_color'          => 'ctc_sanitize_color',
					// 's8_icon_color_on_hover' => 'ctc_sanitize_color',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_s99 (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_s99() {
			$values = array(
				'allowed_keys'           => array(
					's99_dekstop_img_url',
					's99_mobile_img_url',
					's99_desktop_img_height',
					's99_desktop_img_width',
					's99_mobile_img_height',
					's99_mobile_img_width',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					// URL fields must use esc_url_raw (via ctc_sanitize_url) to reject
					// javascript:/data: URIs; otherwise they would fall through to
					// sanitize_text_field and allow stored XSS on style-99 output.
					's99_dekstop_img_url'    => 'ctc_sanitize_url',
					's99_mobile_img_url'     => 'ctc_sanitize_url',
					's99_desktop_img_height' => 'ctc_sanitize_normalize_css_suffix',
					's99_desktop_img_width'  => 'ctc_sanitize_normalize_css_suffix',
					's99_mobile_img_height'  => 'ctc_sanitize_normalize_css_suffix',
					's99_mobile_img_width'   => 'ctc_sanitize_normalize_css_suffix',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_cs_options (styles / customize).
		 *
		 * @return array
		 */
		public function ht_ctc_cs_options() {
			$values = array(
				'allowed_keys'           => array(
					'count',
					'display_allstyles',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_woo_options (woocommerce).
		 *
		 * @return array
		 */
		public function ht_ctc_woo_options() {
			$values = array(
				'allowed_keys'           => array(
					'woo_pre_filled',
					'woo_call_to_action',
					'woo_position',
					'woo_style',
					'woo_single_layout_cart_btn',
					'woo_single_position_center',
					'woo_single_block_type',
					'woo_single_margin_top',
					'woo_single_margin_bottom',
					'woo_single_margin_left',
					'woo_single_margin_right',
					'woo_shop_add_whatsapp',
					'woo_shop_pre_filled',
					'woo_shop_call_to_action',
					'woo_shop_style',
					'woo_shop_layout_cart_btn',
					'woo_shop_position_center',
					'woo_shop_margin_top',
					'woo_shop_margin_bottom',
					'woo_shop_margin_left',
					'woo_shop_margin_right',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'woo_call_to_action'       => 'sanitize_text_field',
					'woo_shop_call_to_action'  => 'sanitize_text_field',
					'woo_pre_filled'           => 'sanitize_textarea_field',
					'woo_shop_pre_filled'      => 'sanitize_textarea_field',
					'woo_single_margin_top'    => 'ctc_sanitize_normalize_css_suffix',
					'woo_single_margin_bottom' => 'ctc_sanitize_normalize_css_suffix',
					'woo_single_margin_left'   => 'ctc_sanitize_normalize_css_suffix',
					'woo_single_margin_right'  => 'ctc_sanitize_normalize_css_suffix',
					'woo_shop_margin_top'      => 'ctc_sanitize_normalize_css_suffix',
					'woo_shop_margin_bottom'   => 'ctc_sanitize_normalize_css_suffix',
					'woo_shop_margin_left'     => 'ctc_sanitize_normalize_css_suffix',
					'woo_shop_margin_right'    => 'ctc_sanitize_normalize_css_suffix',
				),
			);

			return $values;
		}

		/**
		 * Schema for ht_ctc_group.
		 *
		 * @return array
		 */
		public function ht_ctc_group() {
			$values = array(
				'allowed_keys'           => array(
					'group_id',
					'call_to_action',
					'style_desktop',
					'position_type',
					'side_1',
					'side_1_value',
					'side_2',
					'side_2_value',
					'same_settings',
					'style_mobile',
					'position_type_mobile',
					'mobile_side_1',
					'mobile_side_1_value',
					'mobile_side_2',
					'mobile_side_2_value',
					'select_styles_issue',
					'display_desktop',
					'display_mobile',
					'display',
					// nested: display sub-keys
					'global_display',
					'home',
					'posts',
					'pages',
					'archive',
					'category',
					'page_404',
					'woo_product',
					'woo_shop',
					'woo_cart',
					'woo_checkout',
					'woo_order_received',
					'woo_account',
					'list_hideon_pages',
					'list_hideon_cat',
					'list_showon_pages',
					'list_showon_cat',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'group_id'            => 'sanitize_text_field',
					'call_to_action'      => 'ctc_sanitize_emoji_text',
					'side_1_value'        => 'ctc_sanitize_normalize_css_suffix',
					'side_2_value'        => 'ctc_sanitize_normalize_css_suffix',
					'mobile_side_1_value' => 'ctc_sanitize_normalize_css_suffix',
					'mobile_side_2_value' => 'ctc_sanitize_normalize_css_suffix',
				),
			);

			foreach ( $this->get_display_custom_post_type_keys() as $cpt ) {
				$values['allowed_keys'][] = $cpt;
			}

			return $values;
		}

		/**
		 * Schema for ht_ctc_share.
		 *
		 * @return array
		 */
		public function ht_ctc_share() {
			$values = array(
				'allowed_keys'           => array(
					'share_text',
					'call_to_action',
					'webandapi',
					'style_desktop',
					'position_type',
					'side_1',
					'side_1_value',
					'side_2',
					'side_2_value',
					'same_settings',
					'style_mobile',
					'position_type_mobile',
					'mobile_side_1',
					'mobile_side_1_value',
					'mobile_side_2',
					'mobile_side_2_value',
					'select_styles_issue',
					'display_desktop',
					'display_mobile',
					'display',
					// nested: display sub-keys
					'global_display',
					'home',
					'posts',
					'pages',
					'archive',
					'category',
					'page_404',
					'woo_product',
					'woo_shop',
					'woo_cart',
					'woo_checkout',
					'woo_order_received',
					'woo_account',
					'list_hideon_pages',
					'list_hideon_cat',
					'list_showon_pages',
					'list_showon_cat',
				),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(
					'call_to_action'      => 'ctc_sanitize_emoji_text',
					'side_1_value'        => 'ctc_sanitize_normalize_css_suffix',
					'side_2_value'        => 'ctc_sanitize_normalize_css_suffix',
					'mobile_side_1_value' => 'ctc_sanitize_normalize_css_suffix',
					'mobile_side_2_value' => 'ctc_sanitize_normalize_css_suffix',
				),
			);

			foreach ( $this->get_display_custom_post_type_keys() as $cpt ) {
				$values['allowed_keys'][] = $cpt;
			}

			return $values;
		}

		/**
		 * Helper: Get custom post type slugs used as display setting keys.
		 *
		 * @return array
		 */
		private function get_display_custom_post_type_keys() {
			if ( ! function_exists( 'get_post_types' ) ) {
				return array();
			}

			$custom_post_types = get_post_types(
				array(
					'public'   => true,
					'_builtin' => false,
				)
			);

			if ( is_array( $custom_post_types ) ) {
				unset( $custom_post_types['product'] );
				return array_values( $custom_post_types );
			}

			return array();
		}
	}

} // END class_exists check
