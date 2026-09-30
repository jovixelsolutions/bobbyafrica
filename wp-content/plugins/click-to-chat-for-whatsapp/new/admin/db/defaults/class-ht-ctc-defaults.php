<?php
/**
 * Default values..
 *
 * @since 3.9
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Defaults' ) ) {

	/**
	 * Provides default values for plugin settings.
	 */
	class HT_CTC_Defaults {

		/**
		 * Initialize defaults.
		 */
		public function __construct() {
		}

		/**
		 * Get chat options default values.
		 * ht_ctc_chat_options
		 */
		public function ht_ctc_chat_options() {
			$values = array(
				'cc'                  => '',
				'num'                 => '',
				'number'              => '',
				'pre_filled'          => '',
				'call_to_action'      => 'WhatsApp us',
				'style_desktop'       => '2',
				'style_mobile'        => '2',
				'side_1'              => 'bottom',
				'side_1_value'        => '15px',
				'side_2'              => 'right',
				'side_2_value'        => '15px',
				'mobile_side_1'       => 'bottom',
				'mobile_side_1_value' => '10px',
				'mobile_side_2'       => 'right',
				'mobile_side_2_value' => '10px',
				'list_hideon_pages'   => '',
				'list_hideon_cat'     => '',
				'list_showon_pages'   => '',
				'list_showon_cat'     => '',
				'same_settings'       => '1',
				'display_desktop'     => 'show',
				'display_mobile'      => 'show',
				'display'             => array(
					'global_display' => 'show',
				),
			);
			return $values;
		}

		/**
		 * Get other settings default values.
		 * ht_ctc_othersettings
		 *
		 * Analytics param families (g_an_params, gtm_params, pixel_params) are stored as
		 * keyed rows: the array key is the row index and is persisted in the db. Defaults
		 * use 0, 1, 2 .. in order. Rows added later by the user use a time-based index
		 * (e.g. Date.now()) so each row keeps a stable identifier.
		 */
		public function ht_ctc_othersettings() {
			$values = array(
				'an_type'                   => 'no-animation',
				'an_delay'                  => '0',
				'an_itr'                    => '1',
				'show_effect'               => 'corner',
				'amp'                       => '1',
				'g_an'                      => 'ga4',
				'g_an_event_name'           => 'click to chat',
				'g_an_params'               => array(
					0 => array(
						'key'   => 'number',
						'value' => '{number}',
					),
					1 => array(
						'key'   => 'title',
						'value' => '{title}',
					),
					2 => array(
						'key'   => 'url',
						'value' => '{url}',
					),
				),
				'gtm'                       => '1',
				'gtm_event_name'            => 'Click to Chat',
				'gtm_params'                => array(
					0 => array(
						'key'   => 'type',
						'value' => 'chat',
					),
					1 => array(
						'key'   => 'number',
						'value' => '{number}',
					),
					2 => array(
						'key'   => 'title',
						'value' => '{title}',
					),
					3 => array(
						'key'   => 'url',
						'value' => '{url}',
					),
					4 => array(
						'key'   => 'ref',
						'value' => 'dataLayer push',
					),
				),
				'fb_pixel'                  => '1',
				'pixel_event_type'          => 'trackCustom',
				'pixel_custom_event_name'   => 'Click to Chat by HoliThemes',
				'pixel_standard_event_name' => 'Lead',
				'pixel_params'              => array(
					0 => array(
						'key'   => 'Category',
						'value' => 'Click to Chat for WhatsApp',
					),
					1 => array(
						'key'   => 'ID',
						'value' => '{number}',
					),
					2 => array(
						'key'   => 'Title',
						'value' => '{title}',
					),
					3 => array(
						'key'   => 'URL',
						'value' => '{url}',
					),
				),
			);
			return $values;
		}

		/**
		 * Get greetings options default values.
		 * ht_ctc_greetings_options
		 */
		public function ht_ctc_greetings_options() {
			$values = array();
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/db/defaults/class-ht-ctc-defaults-greetings.php';
			if ( class_exists( 'HT_CTC_Defaults_Greetings' ) ) {
				$dg     = new HT_CTC_Defaults_Greetings();
				$values = $dg->greetings();
			}
			return $values;
		}

		/**
		 * Get greetings settings default values.
		 * ht_ctc_greetings_settings
		 */
		public function ht_ctc_greetings_settings() {
			$values = array();
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/db/defaults/class-ht-ctc-defaults-greetings.php';
			if ( class_exists( 'HT_CTC_Defaults_Greetings' ) ) {
				$dg     = new HT_CTC_Defaults_Greetings();
				$values = $dg->g_settings();
			}
			return $values;
		}

		/**
		 * Get greetings 1 default values.
		 * ht_ctc_greetings_1
		 */
		public function ht_ctc_greetings_1() {
			$values = array();
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/db/defaults/class-ht-ctc-defaults-greetings.php';
			if ( class_exists( 'HT_CTC_Defaults_Greetings' ) ) {
				$dg     = new HT_CTC_Defaults_Greetings();
				$values = $dg->g_1();
			}
			return $values;
		}

		/**
		 * Get greetings 2 default values.
		 * ht_ctc_greetings_2
		 */
		public function ht_ctc_greetings_2() {
			$values = array();
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/db/defaults/class-ht-ctc-defaults-greetings.php';
			if ( class_exists( 'HT_CTC_Defaults_Greetings' ) ) {
				$dg     = new HT_CTC_Defaults_Greetings();
				$values = $dg->g_2();
			}
			return $values;
		}

		/**
		 * Get style 1 default values.
		 * ht_ctc_s1
		 */
		public function ht_ctc_s1() {
			$values = array(
				's1_text_color' => '',
				's1_bg_color'   => '',
				's1_icon_color' => '#25d366',
				's1_icon_size'  => '16px',
				's1_add_icon'   => '1',
			);
			return $values;
		}

		/**
		 * Get style 2 default values.
		 * ht_ctc_s2
		 */
		public function ht_ctc_s2() {
			$values = array(
				's2_img_size'   => '50px',
				'cta_textcolor' => '#ffffff',
				'cta_bgcolor'   => '#25D366',
				'cta_type'      => 'hover',
				'cta_font_size' => '15px',
			);
			return $values;
		}

		/**
		 * Get style 3 default values.
		 * ht_ctc_s3
		 */
		public function ht_ctc_s3() {
			$values = array(
				's3_img_size'   => '50px',
				'cta_textcolor' => '#ffffff',
				'cta_bgcolor'   => '#25d366',
				'cta_type'      => 'hover',
				'cta_font_size' => '13px',
			);
			return $values;
		}

		/**
		 * Get style 3.1 default values.
		 * ht_ctc_s3_1
		 */
		public function ht_ctc_s3_1() {
			$values = array(
				's3_img_size'         => '36px',
				's3_bg_color'         => '#25D366',
				's3_bg_color_hover'   => '#25D366',
				's3_padding'          => '16px',
				's3_box_shadow'       => '1',
				's3_box_shadow_hover' => '1',
				'cta_type'            => 'hover',
				'cta_textcolor'       => '#ffffff',
				'cta_bgcolor'         => '#25d366',
				'cta_font_size'       => '15px',
			);
			return $values;
		}

		/**
		 * Get style 4 default values.
		 * ht_ctc_s4
		 */
		public function ht_ctc_s4() {
			$values = array(
				's4_text_color'   => '#7f7d7d',
				's4_bg_color'     => '#e4e4e4',
				's4_img_url'      => '',
				's4_img_position' => 'left',
				's4_img_size'     => '32px',
			);
			return $values;
		}

		/**
		 * Get style 5 default values.
		 * ht_ctc_s5
		 */
		public function ht_ctc_s5() {
			$values = array(
				's5_line_1'           => '',
				's5_line_2'           => 'We will respond as soon as possible',
				's5_line_1_color'     => '#000000',
				's5_line_2_color'     => '#000000',
				's5_background_color' => '#ffffff',
				's5_border_color'     => '#dddddd',
				's5_img'              => '',
				's5_img_height'       => '70px',
				's5_img_width'        => '70px',
				's5_content_height'   => '70px',
				's5_content_width'    => '270px',
				's5_img_position'     => 'right',
			);
			return $values;
		}

		/**
		 * Get style 6 default values.
		 * ht_ctc_s6
		 */
		public function ht_ctc_s6() {
			$values = array(
				's6_txt_color'               => '',
				's6_txt_color_on_hover'      => '',
				's6_txt_decoration'          => '',
				's6_txt_decoration_on_hover' => '',
			);
			return $values;
		}

		/**
		 * Get style 7 default values.
		 * ht_ctc_s7
		 */
		public function ht_ctc_s7() {
			$values = array(
				's7_icon_size'          => '20px',
				's7_icon_color'         => '#ffffff',
				's7_icon_color_hover'   => '#f4f4f4',
				's7_border_size'        => '12px',
				's7_border_color'       => '#25D366',
				's7_border_color_hover' => '#25d366',
				's7_border_radius'      => '50%',
				'cta_type'              => 'hover',
				'cta_textcolor'         => '#ffffff',
				'cta_bgcolor'           => '#25d366',
			);
			return $values;
		}

		/**
		 * Get style 7.1 default values.
		 * ht_ctc_s7_1
		 */
		public function ht_ctc_s7_1() {
			$values = array(
				's7_icon_size'        => '20px',
				's7_icon_color'       => '#ffffff',
				's7_icon_color_hover' => '#f4f4f4',
				's7_border_size'      => '12px',
				's7_bgcolor'          => '#25D366',
				's7_bgcolor_hover'    => '#00d34d',
				'cta_type'            => 'hover',
			);
			return $values;
		}

		/**
		 * Get style 8 default values.
		 * ht_ctc_s8
		 */
		public function ht_ctc_s8() {
			$values = array(
				's8_txt_color'           => '#ffffff',
				's8_txt_color_on_hover'  => '#ffffff',
				's8_bg_color'            => '#26a69a',
				's8_bg_color_on_hover'   => '#26a69a',
				's8_icon_color'          => '#ffffff',
				's8_icon_color_on_hover' => '#ffffff',
				's8_icon_position'       => 'left',
				's8_text_size'           => '16px',
				's8_icon_size'           => '16px',
				's8_btn_size'            => 'btn',
			);
			return $values;
		}

		/**
		 * Get style 99 default values.
		 * ht_ctc_s99
		 */
		public function ht_ctc_s99() {
			$values = array(
				's99_dekstop_img_url'    => '',
				's99_mobile_img_url'     => '',
				's99_desktop_img_height' => '50px',
				's99_desktop_img_width'  => '',
				's99_mobile_img_height'  => '50px',
				's99_mobile_img_width'   => '',
			);
			return $values;
		}

		/**
		 * Get admin settings default values.
		 * ht_ctc_admin_settings
		 */
		public function ht_ctc_admin_settings() {
			$values = array(
				'theme' => 'light',
			);
			return $values;
		}

		/**
		 * Get code blocks default values.
		 * ht_ctc_code_blocks
		 */
		public function ht_ctc_code_blocks() {
			$values = array();
			return $values;
		}

		/**
		 * Get customize settings default values.
		 * ht_ctc_cs_options
		 */
		public function ht_ctc_cs_options() {
			$values = array(
				'count'             => '1',
				'display_allstyles' => '',
			);
			return $values;
		}

		/**
		 * Get woo options default values.
		 * ht_ctc_woo_options
		 */
		public function ht_ctc_woo_options() {
			$values = array(
				'woo_pre_filled'     => '',
				'woo_call_to_action' => '',
			);
			return $values;
		}

		/**
		 * Get group default values.
		 * ht_ctc_group
		 */
		public function ht_ctc_group() {
			$values = array(
				'group_id'            => '',
				'call_to_action'      => 'WhatsApp Group',
				'style_desktop'       => '4',
				'style_mobile'        => '2',
				'side_1'              => 'bottom',
				'side_1_value'        => '10px',
				'side_2'              => 'left',
				'side_2_value'        => '10px',
				'mobile_side_1'       => 'bottom',
				'mobile_side_1_value' => '10px',
				'mobile_side_2'       => 'left',
				'mobile_side_2_value' => '10px',
				'same_settings'       => '1',
				'display_desktop'     => 'show',
				'display_mobile'      => 'show',
				'display'             => array( 'global_display' => 'show' ),
			);
			return $values;
		}

		/**
		 * Get share default values.
		 * ht_ctc_share
		 */
		public function ht_ctc_share() {
			$values = array(
				'share_text'          => 'Checkout this Awesome page {{url}}',
				'call_to_action'      => 'WhatsApp Share',
				'style_desktop'       => '1',
				'style_mobile'        => '2',
				'side_1'              => 'top',
				'side_1_value'        => '10px',
				'side_2'              => 'right',
				'side_2_value'        => '10px',
				'mobile_side_1'       => 'top',
				'mobile_side_1_value' => '10px',
				'mobile_side_2'       => 'right',
				'mobile_side_2_value' => '10px',
				'same_settings'       => '1',
				'display_desktop'     => 'show',
				'display_mobile'      => 'show',
				'display'             => array( 'global_display' => 'show' ),
			);
			return $values;
		}
	}

	new HT_CTC_Defaults();

} // END class_exists check
