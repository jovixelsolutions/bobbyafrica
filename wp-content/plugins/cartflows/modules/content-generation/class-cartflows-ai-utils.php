<?php
/**
 * AI Initialization.
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * AI Initialization.
 *
 * @since x.x.x
 */
class CartFlows_Ai_Utils {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get Credit System API URL.
	 *
	 * @return string API URL.
	 * @since x.x.x
	 */
	public static function get_credit_system_api_url() {
		if ( ! defined( 'CARTFLOWS_CREDIT_SERVER_API' ) ) {
			define( 'CARTFLOWS_CREDIT_SERVER_API', 'https://credits.startertemplates.com/' );
		}
		return trailingslashit( (string) CARTFLOWS_CREDIT_SERVER_API );
	}

	/**
	 * Get Auth Token.
	 *
	 * @since x.x.x
	 * @return string|WP_Error
	 */
	public function get_auth_token() {
		$auth_token = $this->get_auth_data( 'user_email' );
		$token      = '';

		if ( ! is_wp_error( $auth_token ) && is_string( $auth_token ) ) {
			$token = sanitize_text_field( $auth_token );
		}

		$token = apply_filters( 'cartflows_content_generation_auth_token', $token );

		if ( empty( $token ) || is_wp_error( $token ) ) {
			return new WP_Error( 'no_auth_token', __( 'No authentication token found. Please connect your account.', 'cartflows' ) );
		}

		if ( ! is_string( $token ) ) {
			return new WP_Error( 'invalid_auth_token', __( 'Invalid authentication token format.', 'cartflows' ) );
		}

		return sanitize_text_field( $token );
	}

	/**
	 * Send GET request to service.
	 *
	 * @since x.x.x
	 * @param string $route   API route.
	 * @param int    $timeout Request timeout in seconds.
	 * @return array<string, mixed>|WP_Error API response or WP_Error.
	 */
	public function send_get_request( $route, $timeout = 30 ) {
		$auth_token = $this->get_auth_token();

		if ( empty( $auth_token ) || is_wp_error( $auth_token ) ) {
			return new WP_Error( 'no_auth_token', __( 'No authentication token found. Please connect your account.', 'cartflows' ) );
		}

		$url = $this->build_credit_system_url( $route );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_token = base64_encode( $auth_token );

		$args = array(
			'headers' => array(
				'X-Token'      => $encoded_token,
				'Content-Type' => 'application/json; charset=utf-8',
			),
			'timeout' => $timeout, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
		);

		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			// Signature: vip_safe_wp_remote_get( $url, $fallback_value, $threshold, $timeout, $retry, $args ).
			// $args (with the auth headers) is the 6th argument; the function ignores $args['timeout']
			// and uses the 4th argument. An empty fallback keeps failures returning a WP_Error.
			return vip_safe_wp_remote_get( $url, '', 3, $timeout, 20, $args );
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		return wp_remote_get( $url, $args );
	}

	/**
	 * Send API request to service.
	 *
	 * @since x.x.x
	 * @param array<string, mixed> $request_data Request data to send.
	 * @param string               $route        API route.
	 * @param int                  $timeout      Request timeout in seconds.
	 * @return array<string, mixed>|WP_Error API response or WP_Error.
	 */
	public function send_api_request( $request_data, $route, $timeout = 30 ) {
		$auth_token = $this->get_auth_token();

		if ( empty( $auth_token ) || is_wp_error( $auth_token ) ) {
			return new WP_Error( 'no_auth_token', __( 'No authentication token found. Please connect your account.', 'cartflows' ) );
		}

		$url = $this->build_credit_system_url( $route );

		$body = wp_json_encode( $request_data );

		if ( false === $body ) {
			return new WP_Error( 'json_encode_error', __( 'Failed to encode request data to JSON.', 'cartflows' ) );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_token = base64_encode( $auth_token );

		return wp_remote_post(
			$url,
			array(
				'headers' => array(
					'X-Token'      => $encoded_token,
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => $body,
				'timeout' => $timeout, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			)
		);
	}

	/**
	 * Get Auth Data.
	 *
	 * @since x.x.x
	 * @param string $key Optional. Key to retrieve specific data.
	 * @return array<string, mixed>|string|WP_Error
	 */
	protected function get_auth_data( $key = '' ) {
		$auth_data = get_option( 'cartflows_auth', false );

		if ( empty( $auth_data ) ) {
			return new WP_Error( 'no_auth_data', __( 'No authentication data found.', 'cartflows' ) );
		}

		if ( ! empty( $key ) && is_string( $key ) ) {
			return $auth_data[ $key ] ?? new WP_Error( 'no_key_found', __( 'No data found for the provided key.', 'cartflows' ) );
		}

		return $auth_data;
	}

	/**
	 * Build a normalized credit-system endpoint URL.
	 *
	 * @since 1.7.2
	 * @param string $route API route.
	 * @return string
	 */
	protected function build_credit_system_url( $route ) {
		$base  = self::get_credit_system_api_url();
		$route = ltrim( (string) $route, '/' );

		return $base . $route;
	}
}
