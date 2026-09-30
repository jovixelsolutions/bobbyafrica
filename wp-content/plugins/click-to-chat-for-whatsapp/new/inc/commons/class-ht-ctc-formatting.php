<?php
/**
 * Formating
 * add static methods to make things easy
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Formatting' ) ) {

	/**
	 * Formatting utility class with static methods.
	 */
	class HT_CTC_Formatting {


		/**
		 * Format WhatsApp number according to country-specific rules.
		 *
		 * @param string $number Phone number to format.
		 * @return string Formatted phone number.
		 */
		public static function wa_number( $number ) {

			// remove all expect digits
			$number = preg_replace( '/\D/', '', $number );
			// remove initial 0s
			$number = ltrim( $number, '0' );

			// // Country-specific rules (like Mexico +52 1 and Argentina +54 9) are applied only on the frontend.
			// // In admin, clean digits are returned so intl-tel-input can format the input field correctly.
			// if ( function_exists( 'is_admin' ) && is_admin() ) {
			// return $number;
			// }

			// https://faq.whatsapp.com/640432094208718

			// All phone numbers in Argentina (country code "54") should have a "9" between the country code and area code.
			$number = preg_replace( '/^54(0|1|2|3|4|5|6|7|8)/', '549$1', $number );
			// The prefix "15" must be removed so the final number will have 13 digits total (not needed)

			// Mexico (country code "52") requires "1" after "+52" per WhatsApp documentation:
			// Even though domestic telecom dialing changed in Mexico, WhatsApp internal routing and universal links (wa.me) still require "+52 1" for mobile accounts to ensure reliable routing across all devices.
			$number = preg_replace( '/^52(0|2|3|4|5|6|7|8|9)/', '521$1', $number );

			return $number;
		}

		/**
		 * Check if current page is being viewed in a page builder editor.
		 *
		 * Detects various page builders like Elementor, Divi, etc.
		 *
		 * @return string 'y' if in editor mode, 'n' otherwise.
		 */
		public static function is_page_builder_editor() {

			// $is_editor = false;
			$is_editor = 'n';

			$editor_params = array( 'elementor-preview', 'et_fb', 'is-editor-iframe', 'fl_builder', 'siteorigin_panels_live_editor', 'pagelayer-iframe', 'vcv-editable' );
			// $editor_params = apply_filters( 'ht_ctc_fh_editor_params', $editor_params );

			// Check for page builder editor parameters
			foreach ( $editor_params as $param ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: checking existence of query keys; no state change performed.
				if ( isset( $_GET ) && isset( $_GET[ $param ] ) ) {
					$is_editor = 'y';
					break;
				}
			}

			$is_editor = apply_filters( 'ht_ctc_fh_is_page_builder_editor', $is_editor );

			return $is_editor;
		}
	}
} // END class_exists check
