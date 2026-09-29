<?php
/**
 * Tool Response Helper
 *
 * All ability handlers should use this class for consistent responses.
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Response Class - Enforces consistent response format for all abilities.
 *
 * Execute_callback must return a plain array on success (matching output_schema),
 * or a WP_Error on failure — consistent with the WordPress Abilities API contract.
 */
class Response {

	/**
	 * Return data directly as the execute_callback result.
	 *
	 * The Abilities API expects execute_callback to return the raw data array,
	 * not a wrapper envelope. Pass the data you want the caller to receive.
	 *
	 * @param array<string, mixed> $data Response data matching the ability's output_schema.
	 * @return array<string, mixed>
	 */
	public static function success( $data = array() ) {
		return $data;
	}

	/**
	 * Create a WP_Error to signal a failed execution.
	 *
	 * The Abilities API treats a WP_Error return from execute_callback as a
	 * failure and converts it to an appropriate error response for the caller.
	 *
	 * @param string $message Human-readable error message.
	 * @param string $code    Machine-readable error code (default: 'moderncart_error').
	 * @return \WP_Error
	 */
	public static function error( $message, $code = 'moderncart_error' ) {
		return new \WP_Error( $code, $message );
	}
}
