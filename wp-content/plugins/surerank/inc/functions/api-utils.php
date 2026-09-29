<?php
/**
 * API Utils Base Class.
 *
 * Abstract base class for shared API utility functions across modules.
 *
 * @package SureRank\Inc\Functions
 * @since 1.7.2
 */

namespace SureRank\Inc\Functions;

use SureRank\Inc\Modules\Ai_Auth\Controller as Ai_Auth_Controller;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract API_Utils class
 *
 * Base class for modules that need to interact with the credit system API.
 */
abstract class API_Utils {

	/**
	 * Get Credit System API URL.
	 *
	 * @return string API URL.
	 * @since 1.7.2
	 */
	public static function get_credit_system_api_url() {
		if ( ! defined( 'SURERANK_CREDIT_SERVER_API' ) ) {
			define( 'SURERANK_CREDIT_SERVER_API', 'https://credits.startertemplates.com/' );
		}
		return SURERANK_CREDIT_SERVER_API;
	}

	/**
	 * Get Auth Token.
	 *
	 * @since 1.7.2
	 * @return string|WP_Error
	 */
	public function get_auth_token() {
		$token = apply_filters( 'surerank_content_generation_auth_token', $this->get_auth_data( 'user_email' ) );

		if ( empty( $token ) || is_wp_error( $token ) ) {
			return new WP_Error( 'no_auth_token', __( 'No authentication token found. Please connect your account.', 'surerank' ) );
		}

		return $token;
	}

	/**
	 * Get custom error messages for API responses.
	 *
	 * @since 1.7.2
	 * @return array<string, string> Array of error codes and their custom messages.
	 */
	public static function get_custom_error_messages() {
		return [
			'internal_server_error' => __( 'Something went wrong on our end. Please try again in a moment, or contact support if you need help.', 'surerank' ),
			'require_pro'           => __( 'You\'ve reached your free usage limit. Upgrade to Pro for additional credits.', 'surerank' ),
			'limit_exceeded'        => __( 'You\'ve used all your AI credits for today. Your credits will refresh automatically tomorrow.', 'surerank' ),
		];
	}

	/**
	 * Send GET request to service.
	 *
	 * @since 1.7.2
	 * @param string $route   API route.
	 * @param int    $timeout Request timeout in seconds.
	 * @return array<string, mixed>|WP_Error API response or WP_Error.
	 */
	public function send_get_request( $route, $timeout = 30 ) {
		$auth_token = $this->get_auth_token();

		if ( empty( $auth_token ) || is_wp_error( $auth_token ) ) {
			return new WP_Error( 'no_auth_token', __( 'No authentication token found. Please connect your account.', 'surerank' ) );
		}

		$url = self::get_credit_system_api_url() . $route;

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_token = base64_encode( $auth_token );

		return Requests::get(
			$url,
			[
				'headers' => [
					'X-Token'      => $encoded_token,
					'Content-Type' => 'application/json; charset=utf-8',
				],
				'timeout' => $timeout, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			]
		);
	}

	/**
	 * Send API request to service.
	 *
	 * @since 1.7.2
	 * @param array<string, mixed> $request_data Request data to send.
	 * @param string               $route        API route.
	 * @param int                  $timeout      Request timeout in seconds.
	 * @return array<string, mixed>|WP_Error API response or WP_Error.
	 */
	public function send_api_request( $request_data, $route, $timeout = 30 ) {
		$auth_token = $this->get_auth_token();

		if ( empty( $auth_token ) || is_wp_error( $auth_token ) ) {
			return new WP_Error( 'no_auth_token', __( 'No authentication token found. Please connect your account.', 'surerank' ) );
		}

		$url = self::get_credit_system_api_url() . $route;

		$body = wp_json_encode( $request_data );

		if ( false === $body ) {
			return new WP_Error( 'json_encode_error', __( 'Failed to encode request data to JSON.', 'surerank' ) );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_token = base64_encode( $auth_token );

		return Requests::post(
			$url,
			[
				'headers' => [
					'X-Token'      => $encoded_token,
					'Content-Type' => 'application/json; charset=utf-8',
				],
				'body'    => $body,
				'timeout' => $timeout, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			]
		);
	}

	/**
	 * Get Auth Data.
	 *
	 * @since 1.7.2
	 * @param string $key Optional. Key to retrieve specific data.
	 * @return array<string, mixed>|string|WP_Error
	 */
	protected function get_auth_data( $key = '' ) {
		$auth_data = get_option( Ai_Auth_Controller::SETTINGS_KEY, false );

		if ( empty( $auth_data ) ) {
			return new WP_Error( 'no_auth_data', __( 'No authentication data found.', 'surerank' ) );
		}

		if ( ! empty( $key ) && is_string( $key ) ) {
			return $auth_data[ $key ] ?? new WP_Error( 'no_key_found', __( 'No data found for the provided key.', 'surerank' ) );
		}

		return $auth_data;
	}
}
