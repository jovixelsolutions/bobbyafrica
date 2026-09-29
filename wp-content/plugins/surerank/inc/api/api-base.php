<?php
/**
 * API base.
 *
 * @package SureRank;
 * @since 1.0.0
 */

namespace SureRank\Inc\API;

use SureRank\Inc\Functions\Helper;
use SureRank\Inc\Functions\Sanitize;
use SureRank\Inc\Meta_Variables\Site;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Api_Base
 *
 * @since 1.0.0
 */
abstract class Api_Base extends WP_REST_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'surerank/v1';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}
	/**
	 * Get API namespace.
	 *
	 * @since 1.0.0
	 * @return string
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
				'surerank_nonce_verification_failed',
				__( 'Nonce is missing.', 'surerank' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		// Verify the nonce.
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'surerank_nonce_verification_failed',
				__( 'Nonce is invalid.', 'surerank' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return self::check_permission_for_action( $request );
	}

	/**
	 * Apply SureRank's capability + Pro-filter permission chain for
	 * the given request.
	 *
	 * Extracted from validate_permission() so the AJAX fallback in
	 * inc/ajax/save-endpoints.php can enforce the exact same policy
	 * that Pro plugins layer on top of the REST endpoints via the
	 * `surerank_rest_api_permission` and
	 * `surerank_rest_api_permission_check` filters. AJAX must not be
	 * a back door around a Pro licensing or role policy that blocks
	 * the REST endpoint.
	 *
	 * Callers are responsible for verifying request authenticity
	 * (X-WP-Nonce for REST, `_wpnonce` via check_ajax_referer() for
	 * AJAX) before invoking this helper.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The REST request object (or a synthetic request built by the AJAX handler mirroring the equivalent REST route).
	 * @return bool|WP_Error True on pass, WP_Error on denial.
	 * @since 1.7.2
	 */
	public static function check_permission_for_action( $request ) {
		/**
		 * Filter to allow Pro plugin or extensions to override permission checks.
		 *
		 * @since 1.6.4
		 * @param bool|WP_Error $has_permission True to allow access, WP_Error to deny with custom message, false to use default check.
		 * @param WP_REST_Request $request The REST request object.
		 */
		$has_permission = apply_filters( 'surerank_rest_api_permission', false, $request );

		if ( is_wp_error( $has_permission ) ) {
			return $has_permission;
		}

		// If filter didn't handle permission (Pro not active), fall back to admin capability check.
		if ( true !== $has_permission ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return new WP_Error(
					'surerank_rest_cannot_access',
					__( 'You do not have permission to perform this action.', 'surerank' ),
					[ 'status' => rest_authorization_required_code() ]
				);
			}
		}

		/**
		 * Filter to allow additional permission checks (e.g., license validation).
		 *
		 * This runs AFTER the user capability check has passed.
		 *
		 * @since 1.6.4
		 * @param bool|WP_Error $permission_status True if permission granted, WP_Error to deny.
		 * @param WP_REST_Request $request The REST request object.
		 */
		$permission_status = apply_filters( 'surerank_rest_api_permission_check', true, $request );

		if ( is_wp_error( $permission_status ) ) {
			return $permission_status;
		}

		return true;
	}

	/**
	 * Get favicon image URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_favicon() {
		return esc_url( get_site_icon_url( 16 ) );
	}

	/**
	 * Get site variables
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public function get_site_variables() {
		$site           = Site::get_instance();
		$site_variables = $site->get_all_values();
		$variables      = [];

		// Add favicon icon if variable is available and should be a array.
		if ( ! empty( $site_variables ) && is_array( $site_variables ) ) {

			// Keep in key and array format.
			foreach ( $site_variables as $key => $value ) {
				// Verify that value should be an array.

				if ( ! isset( $value['value'] ) ) {
					continue;
				}
				$variables[ $key ] = $value['value'];
			}

			$variables['favicon']       = $this->get_favicon();
			$variables['title']         = __( 'Sample Post', 'surerank' );
			$variables['current_year']  = gmdate( 'Y' );
			$variables['current_month'] = gmdate( 'F' );
		} else {
			$variables = [];
		}

		$variables['page'] = Helper::format_paged_info( 2, 5 );
		return $variables;
	}

	/**
	 * Sanitize object data
	 *
	 * @since 1.0.0
	 * @param array<string, mixed>|array<int, string> $data Data to sanitize.
	 * @return array<string, mixed>|array<int, string>
	 */
	public function sanitize_array_data( $data ) {
		return Sanitize::array_deep( [ Sanitize::class, 'sanitize_with_placeholders' ], $data );
	}
}
