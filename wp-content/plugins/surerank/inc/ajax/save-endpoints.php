<?php
/**
 * AJAX fallback handlers for SureRank's save endpoints.
 *
 * When WP Ghost or a WAF rewrites / blocks `/wp-json/`, the REST endpoints
 * in inc/api/post.php, inc/api/term.php, and inc/api/admin.php return 403
 * or non-JSON responses. The JS middleware registered in
 * src/functions/api-fetch-middleware.js detects those failure modes and
 * retries the same payload against the AJAX actions below. Both transports
 * delegate to the same transport-free helpers (Post::save_post_seo_meta,
 * Term::save_term_seo_meta, Admin::save_admin_settings) so they produce
 * identical side effects for identical input. See #2362.
 *
 * Auth parity with REST:
 * - Nonce: `wp_rest` (same as api-fetch's X-WP-Nonce), passed as `_wpnonce`.
 * - Permission chain: Api_Base::check_permission_for_action() — runs the
 *   exact same `surerank_rest_api_permission` +
 *   `surerank_rest_api_permission_check` filters the REST endpoints do, so
 *   a Pro license / role policy that would deny the REST call also denies
 *   the AJAX fallback.
 * - Sanitisation: Sanitize::array_deep + sanitize_with_placeholders (same
 *   sanitiser the REST endpoints apply via their route args).
 *
 * @package SureRank\Inc\Ajax
 * @since 1.7.2
 */

namespace SureRank\Inc\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\API\Admin;
use SureRank\Inc\API\Api_Base;
use SureRank\Inc\API\Post;
use SureRank\Inc\API\Term;
use SureRank\Inc\Functions\Rest_Observation;
use SureRank\Inc\Functions\Sanitize;
use SureRank\Inc\Traits\Get_Instance;
use WP_REST_Request;

/**
 * Save endpoints AJAX fallback class.
 *
 * @since 1.7.2
 */
class Save_Endpoints {

	use Get_Instance;

	/**
	 * Nonce action mirrored from api-fetch (X-WP-Nonce is a `wp_rest`
	 * nonce). Using the same action lets clients reuse the single nonce
	 * already localised for REST without requiring a second round-trip.
	 */
	private const NONCE_ACTION = 'wp_rest';

	/**
	 * Constructor: register the AJAX hooks.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		add_action( 'wp_ajax_surerank_save_post_settings', [ $this, 'save_post_settings' ] );
		add_action( 'wp_ajax_surerank_save_term_settings', [ $this, 'save_term_settings' ] );
		add_action( 'wp_ajax_surerank_save_admin_settings', [ $this, 'save_admin_settings' ] );
	}

	/**
	 * AJAX handler for POST /wp-json/surerank/v1/post/settings parity.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public function save_post_settings(): void {
		if ( ! $this->guard_request( 'POST', '/surerank/v1/post/settings' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in guard_request() above.
		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		if ( $post_id <= 0 ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => __( 'Invalid post id.', 'surerank' ),
				],
				400
			);
		}

		$meta_data = $this->extract_meta_data();

		$result = Post::save_post_seo_meta( $post_id, $meta_data );

		$this->respond_with( $result );
	}

	/**
	 * AJAX handler for POST /wp-json/surerank/v1/term/settings parity.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public function save_term_settings(): void {
		if ( ! $this->guard_request( 'POST', '/surerank/v1/term/settings' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in guard_request() above.
		$term_id = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;
		if ( $term_id <= 0 ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => __( 'Invalid term id.', 'surerank' ),
				],
				400
			);
		}

		$meta_data = $this->extract_meta_data();

		$result = Term::save_term_seo_meta( $term_id, $meta_data );

		$this->respond_with( $result );
	}

	/**
	 * AJAX handler for POST /wp-json/surerank/v1/admin/global-settings parity.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public function save_admin_settings(): void {
		if ( ! $this->guard_request( 'POST', '/surerank/v1/admin/global-settings' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in guard_request() above; raw body is sanitised via Sanitize::array_deep below before use.
		$raw_data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
		$data     = is_string( $raw_data ) ? json_decode( $raw_data, true ) : $raw_data;

		if ( ! is_array( $data ) ) {
			$data = [];
		}

		/**
		 * Sanitized settings payload.
		 *
		 * @var array<string, mixed> $data
		 */
		$data = Sanitize::array_deep( [ Sanitize::class, 'sanitize_with_placeholders' ], $data );

		$result = Admin::save_admin_settings( $data );

		$this->respond_with( $result );
	}

	/**
	 * Verify the request arrived via AJAX with a valid nonce AND
	 * satisfies the exact same permission chain as the mirrored REST
	 * endpoint. On failure, emits a structured JSON error and returns
	 * false; callers must early-return.
	 *
	 * A synthetic WP_REST_Request is built so Pro plugins hooking
	 * `surerank_rest_api_permission` / `surerank_rest_api_permission_check`
	 * get the same route context they see on the REST side.
	 *
	 * @param string $method       HTTP method the mirrored REST route uses (e.g., 'POST').
	 * @param string $rest_route   REST route path, including namespace, e.g. '/surerank/v1/post/settings'.
	 * @since 1.7.2
	 * @return bool True on success, false on failure (with response already sent).
	 */
	private function guard_request( string $method, string $rest_route ): bool {
		// Nonce check — use die=false so nonce failures emit structured
		// JSON instead of `wp_die("-1")`, which the JS middleware cannot
		// parse and would misreport as a firewall block.
		if ( false === check_ajax_referer( self::NONCE_ACTION, '_wpnonce', false ) ) {
			wp_send_json_error(
				[
					'success' => false,
					'code'    => 'nonce_invalid',
					'message' => __( 'Your session has expired. Please refresh and try again.', 'surerank' ),
				],
				401
			);
		}

		$request = new WP_REST_Request( $method, $rest_route );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$request->set_body_params( wp_unslash( $_POST ) );

		$permission = Api_Base::check_permission_for_action( $request );
		if ( is_wp_error( $permission ) ) {
			$error_data = $permission->get_error_data();
			$status     = is_array( $error_data ) && isset( $error_data['status'] ) ? (int) $error_data['status'] : 403;
			wp_send_json_error(
				[
					'success' => false,
					'code'    => $permission->get_error_code(),
					'message' => $permission->get_error_message(),
				],
				$status
			);
		}

		// Reaching this handler proves the JS middleware fell back from
		// REST to AJAX — record that REST is currently blocked so
		// Site Health can surface it.
		Rest_Observation::mark_blocked();

		return true;
	}

	/**
	 * Read and sanitise the `metaData` field from the AJAX request body,
	 * applying the same sanitiser the REST endpoints use.
	 *
	 * API-fetch posts JSON bodies, which the middleware re-encodes as
	 * form data before retrying against admin-ajax.php, so the value
	 * arrives as either a nested array (form-encoded) or a JSON string
	 * depending on the client's retry shape. Handle both.
	 *
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	private function extract_meta_data(): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in guard_request(); raw value is sanitised via Sanitize::array_deep below before use.
		$raw = isset( $_POST['metaData'] ) ? wp_unslash( $_POST['metaData'] ) : [];

		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : [];
		}

		if ( ! is_array( $raw ) ) {
			$raw = [];
		}

		/**
		 * Sanitized meta payload.
		 *
		 * @var array<string, mixed> $sanitised
		 */
		$sanitised = Sanitize::array_deep( [ Sanitize::class, 'sanitize_with_placeholders' ], $raw );
		return $sanitised;
	}

	/**
	 * Emit a JSON response that matches the shape produced by
	 * Send_Json::success / Send_Json::error on the REST side, so the
	 * JS middleware sees identical payloads across transports.
	 *
	 * @param array{success: bool, message: string} $result Result from the shared save helper.
	 * @since 1.7.2
	 * @return void
	 */
	private function respond_with( array $result ): void {
		$payload = [
			'success' => (bool) $result['success'],
			'message' => (string) $result['message'],
		];

		if ( $payload['success'] ) {
			wp_send_json_success( $payload );
		}

		wp_send_json_error( $payload );
	}
}
