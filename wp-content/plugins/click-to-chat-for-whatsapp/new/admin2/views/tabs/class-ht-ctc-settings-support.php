<?php
/**
 * Support Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Support' ) ) {

	/**
	 * Support settings class.
	 */
	class HT_CTC_Settings_Support {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// Basic Troubleshoot
			$fields[] = self::card_troubleshoot();

			// FAQ Section
			$fields[] = self::card_faq();

			// $fields = apply_filters( 'ht_ctc_fh_admin_support_fields', $fields );

			return $fields;
		}

		/**
		 * Troubleshoot Card
		 */
		private static function card_troubleshoot() {
			$values = array(
				'field_type'  => 'card',
				'title'       => 'Basic Troubleshooting',
				'description' => 'Quick solutions to common issues',
				'fields'      => array(
					array(
						'field_type'   => 'block_faq',
						'id'           => 'support_ts_1',
						'option_group' => 'ht_ctc_othersettings',
						'title'        => 'WhatsApp widget is not appearing on the site?',
						'content'      => 'First, confirm if you have entered the correct WhatsApp number in the <strong>General Settings</strong>. Next, check the <strong>Display Settings</strong> to ensure it is not hidden on specific devices or pages. Clear any caching plugins you might be using.',
					),
					array(
						'field_type'   => 'block_faq',
						'id'           => 'support_ts_2',
						'option_group' => 'ht_ctc_othersettings',
						'title'        => 'The chat opens but without pre-filled text?',
						// 'title'        => 'The chat opens but without a pre-filled message',
						'content'      => 'Ensure the pre-filled message is configured under General Settings. Also, check if you have any page-specific settings that override the global configuration.',
					),
					array(
						'field_type'   => 'block_content',
						'id'           => 'support_ts_help_link',
						'option_group' => 'ht_ctc_othersettings',
						// add a dedicated documentation page URL alongside the two guides below
						'content'      => '<div>
							<a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/troubleshoot/' ) ) . '" target="_blank" class="external-link">View Complete Troubleshoot Guide <span class="dashicons dashicons-external"></span></a>
						</div>
						<div style="margin-top: 8px;">
							<a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/installation-of-click-to-chat-pro-plugin/' ) ) . '" target="_blank" class="external-link">Installation Guide <span class="dashicons dashicons-external"></span></a>
						</div>',
					),
				),
			);
			return $values;
		}

		/**
		 * FAQ Card
		 */
		private static function card_faq() {
			$values = array(
				'field_type'  => 'card',
				'title'       => __( 'Frequently Asked Questions', 'click-to-chat-for-whatsapp' ),
				'description' => 'Find answers to the most common questions',
				'fields'      => array(
					array(
						'field_type'   => 'block_faq',
						'id'           => 'support_faq_1',
						'option_group' => 'ht_ctc_othersettings',
						'title'        => 'How to add a WhatsApp Group link?',
						'content'      => vsprintf(
							'Enable "%1$s" under <strong>%2$s %3$s</strong> to display a WhatsApp group link alongside your direct contact button.',
							array(
								__( 'Enable Group Features', 'click-to-chat-for-whatsapp' ),
								__( 'Advanced', 'click-to-chat-for-whatsapp' ),
								__( 'Settings', 'click-to-chat-for-whatsapp' ),
							)
						),
					),
					array(
						'field_type'   => 'block_faq',
						'id'           => 'support_faq_2',
						'option_group' => 'ht_ctc_othersettings',
						'title'        => 'Can I track clicks with Google Analytics?',
						'content'      => 'Yes! Go to the <strong>Analytics</strong> tab and enable Google Analytics tracking. Click events will automatically be logged when users start a chat.',
					),
					array(
						'field_type'   => 'block_content',
						'id'           => 'support_faq_link',
						'option_group' => 'ht_ctc_othersettings',
						'content'      => '<div>
							<a href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/faq/' ) ) . '" target="_blank" class="external-link">View All FAQs <span class="dashicons dashicons-external"></span></a>
						</div>',
					),
				),
			);
			return $values;
		}
	}
}
