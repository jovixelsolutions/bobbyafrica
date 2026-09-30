<?php
/**
 * API Permissions Handler
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_API_Permissions' ) ) {

	/**
	 * API permissions class.
	 */
	class HT_CTC_API_Permissions {

		/**
		 * Permission & nonce check.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return true|WP_Error
		 */
		public function check_admin_settings_access( $request ) {

			// Only allow users with 'manage_options' capability to access these endpoints.
			if ( ! current_user_can( 'manage_options' ) ) {
				return HT_CTC_API_Responses::error( 'forbidden', 'You do not have permission.', 403 );
			}

			$nonce = $request->get_header( 'X-WP-Nonce' );
			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				return HT_CTC_API_Responses::error( 'invalid_nonce', 'Invalid or missing nonce.', 403 );
			}

			return true;
		}


		/**
		 * Allow public access — NO authentication required.
		 *
		 * Use this ONLY for routes that are intentionally unauthenticated (e.g. public read endpoints).
		 * Do NOT use this as the permission_callback for any admin or settings route.
		 *
		 * @return true
		 */
		// public function allow_public_access() {
		// return true;
		// }
	}

}
