<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;
use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Menu.
 */
abstract class ApiBase extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cartflows/v1';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Register API routes.
	 */
	public function get_api_namespace() {

		return $this->namespace;
	}

	/**
	 * Validate the nonce for REST API requests, then apply the
	 * capability + Pro filter chain via check_permission_for_action().
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The REST request object.
	 * @return bool|WP_Error True if valid, WP_Error if invalid.
	 */
	public function validate_permission( $request ) {
		// Retrieve the nonce from the request header.
		$nonce = $request->get_header( 'X-WP-Nonce' );

		// Check if nonce is null or empty.
		if ( empty( $nonce ) || ! is_string( $nonce ) ) {
			return new WP_Error(
				'cartflows_nonce_verification_failed',
				__( 'Nonce is missing.', 'cartflows' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'cartflows_nonce_verification_failed',
				__( 'Nonce is invalid.', 'cartflows' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return self::check_permission_for_action( $request );
	}

	/**
	 * Extracted from validate_permission() so the AJAX fallback in
	 * inc/ajax/save-endpoints.php can enforce the exact same policy
	 * that Pro plugins layer on top of the REST endpoints via the
	 * `cartflows_rest_api_permission` and
	 * `cartflows_rest_api_permission_check` filters. AJAX must not be
	 * a back door around a Pro licensing or role policy that blocks
	 * the REST endpoint.
	 *
	 * Callers are responsible for verifying request authenticity
	 * (X-WP-Nonce for REST, `_wpnonce` via check_ajax_referer() for
	 * AJAX) before invoking this helper.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The REST request object (or a synthetic request built by the AJAX handler mirroring the equivalent REST route).
	 * @return bool|WP_Error True on pass, WP_Error on denial.
	 * @since x.x.x
	 */
	public static function check_permission_for_action( $request ) {
		/**
		 * Filter to allow Pro plugin or extensions to override permission checks.
		 *
		 * @since x.x.x
		 * @param bool|WP_Error $has_permission True to allow access, WP_Error to deny with custom message, false to use default check.
		 * @param WP_REST_Request $request The REST request object.
		 */
		$has_permission = apply_filters( 'cartflows_rest_api_permission', false, $request );

		if ( is_wp_error( $has_permission ) ) {
			return $has_permission;
		}

		// If filter didn't handle permission (Pro not active), fall back to admin capability check.
		if ( true !== $has_permission ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return new WP_Error(
					'cartflows_rest_cannot_access',
					__( 'You do not have permission to perform this action.', 'cartflows' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		/**
		 * Filter to allow additional permission checks (e.g., license validation).
		 *
		 * This runs AFTER the user capability check has passed.
		 *
		 * @since x.x.x
		 * @param bool|WP_Error $permission_status True if permission granted, WP_Error to deny.
		 * @param WP_REST_Request $request The REST request object.
		 */
		$permission_status = apply_filters( 'cartflows_rest_api_permission_check', true, $request );

		if ( is_wp_error( $permission_status ) ) {
			return $permission_status;
		}

		return true;
	}
}
