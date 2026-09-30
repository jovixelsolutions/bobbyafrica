<?php
/**
 * API Responses Helper
 *
 * Centralizes REST response and error helper methods for the Click to Chat API.
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_API_Responses' ) ) {

	/**
	 * Class for standardizing REST API responses.
	 */
	class HT_CTC_API_Responses {

		/**
		 * Standardized success response.
		 *
		 * Array payloads always carry `success: true` at the top level — the admin
		 * SPA branches on it (`result.success`) for every endpoint. Adding it here
		 * (rather than in each handler) keeps the envelope uniform; a handler-set
		 * `success` key is left untouched.
		 *
		 * @param mixed $data   Data to return.
		 * @param int   $status HTTP status code.
		 * @return WP_REST_Response
		 */
		public static function success( $data = array(), $status = 200 ) {
			if ( is_array( $data ) && ! isset( $data['success'] ) ) {
				$data = array( 'success' => true ) + $data;
			}

			$response = rest_ensure_response( $data );
			if ( $response instanceof WP_REST_Response ) {
				$response->set_status( $status );
			}
			return $response;
		}

		/**
		 * Standardized error response.
		 *
		 * @param string $code    Error code.
		 * @param string $message Error message.
		 * @param int    $status  HTTP status code.
		 * @param array  $data    Additional error data.
		 * @return WP_Error
		 */
		public static function error( $code, $message, $status = 400, $data = array() ) {
			$error_data = array_merge( array( 'status' => $status ), $data );
			return new WP_Error( $code, $message, $error_data );
		}
	}

}
