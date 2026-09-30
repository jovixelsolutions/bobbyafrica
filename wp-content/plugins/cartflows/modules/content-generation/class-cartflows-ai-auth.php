<?php
/**
 * AI Authentication.
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * AI Authentication.
 *
 * @since x.x.x
 */
class Cartflows_Ai_Auth {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Encryption key.
	 *
	 * @since x.x.x
	 * @var string
	 */
	public $key;

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
	 *  Constructor
	 */
	public function __construct() {}

	/**
	 * Get Auth URL.
	 *
	 * @since x.x.x
	 * @param string $redirect_back Absolute URL the auth flow should return to. Falls back to the CartFlows dashboard.
	 * @return string|WP_Error
	 */
	public function get_auth_url( $redirect_back = '' ) {
		// Generate a random key of 16 characters.
		$this->key = wp_generate_password( 16, false );

		$default_redirect = admin_url( 'admin.php?page=cartflows' );

		// Only allow same-site URLs — the portal appends the access key here, so an external URL would leak it.
		$redirect_back = ! empty( $redirect_back ) ? wp_validate_redirect( $redirect_back, $default_redirect ) : $default_redirect;

		// Prepare the token data.
		$token_data = array(
			'redirect-back' => $this->sanitize_redirect_back( $redirect_back ),
			'key'           => $this->key,
			'site-url'      => site_url(),
			'nonce'         => wp_create_nonce( 'cartflows_ai_auth_nonce' ),
		);

		$encoded_token_data = wp_json_encode( $token_data );

		if ( empty( $encoded_token_data ) ) {
			return new WP_Error( 'failed_to_encode_token_data', __( 'Failed to encode the token data.', 'cartflows' ) );
		}

		return CARTFLOWS_SERVER_URL . 'auth/?token=' . base64_encode( $encoded_token_data );
	}

	/**
	 * Get Auth status.
	 *
	 * @since x.x.x
	 * @return bool
	 */
	public function get_auth_status() {
		$auth_status = get_option( 'cartflows_auth', false );
		return ! empty( $auth_status );
	}

	/**
	 * Save Auth.
	 *
	 * @since x.x.x
	 * @param string $data Data to save.
	 * @param string $key Key to use for encryption.
	 * @param string $method Encryption method. Default is AES-256-CBC.
	 * @return bool|WP_Error
	 */
	public function save_auth( $data, $key, $method = 'AES-256-CBC' ) {

		// Decode the data and split IV and encrypted data.
		$decoded_data = base64_decode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		// if the data is not base64 encoded then return false.
		if ( empty( $decoded_data ) ) {
			return new WP_Error( 'failed_to_decode', __( 'Failed to decode the access key.', 'cartflows' ) );
		}

		// split the key and encrypted data.
		list( $key, $encrypted ) = explode( '::', $decoded_data, 2 );

		// Decrypt the data using the key.
		$decrypted = openssl_decrypt( $encrypted, $method, $key, 0, $key );

		// if the decryption returns false then send error.
		if ( empty( $decrypted ) ) {
			return new WP_Error( 'failed_to_decrypt', __( 'Failed to decrypt the access key.', 'cartflows' ) );
		}

		// json decode the decrypted data.
		$decrypted_data_array = json_decode( $decrypted, true );

		if ( ! is_array( $decrypted_data_array ) || empty( $decrypted_data_array ) ) {
			return new WP_Error( 'failed_to_json_decode', __( 'Failed to json decode the decrypted data.', 'cartflows' ) );
		}

		// The nonce is mandatory — the key travels in the payload, so it is the only proof this site started the flow.
		if ( empty( $decrypted_data_array['nonce'] ) || ! wp_verify_nonce( $decrypted_data_array['nonce'], 'cartflows_ai_auth_nonce' ) ) {
			return new WP_Error( 'nonce_verification_failed', __( 'Nonce verification failed.', 'cartflows' ) );
		}

		// check if the user email is present in the decrypted data.
		if ( empty( $decrypted_data_array['user_email'] ) ) {
			return new WP_Error( 'no_user_email', __( 'No user email found in the decrypted data.', 'cartflows' ) );
		}

		// Extract is_subscribed value if present.
		$is_subscribed = false;
		if ( isset( $decrypted_data_array['is_subscribed'] ) ) {
			// Convert string 'true'/'false' to boolean if needed.
			if ( is_string( $decrypted_data_array['is_subscribed'] ) ) {
				$is_subscribed = 'true' === $decrypted_data_array['is_subscribed'];
			} else {
				$is_subscribed = (bool) $decrypted_data_array['is_subscribed'];
			}

			// Update the analytics option based on the preference.
			// Set 'yes' if opted in, empty string if not.
			$enable_contribution = $is_subscribed ? 'yes' : '';
			update_option( 'cartflows_usage_optin', $enable_contribution );

			// Remove is_subscribed from the decrypted data.
			unset( $decrypted_data_array['is_subscribed'] );
		}

		// remove the nonce from the decrypted data before saving it to the options.
		unset( $decrypted_data_array['nonce'] );

		// save the user email to the options.
		update_option( 'cartflows_auth', $decrypted_data_array );

		// One-shot transient so the next admin page load can show a success banner.
		set_transient( 'cartflows_auth_connected_notice', 1, MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Restrict the redirect-back URL to the current admin host so a hijacked
	 * request can't bounce users to an arbitrary site after auth completes.
	 *
	 * @since x.x.x
	 * @param string $url Candidate redirect URL.
	 * @return string
	 */
	private function sanitize_redirect_back( $url ) {
		$default = admin_url( 'admin.php?page=cartflows' );

		if ( empty( $url ) || ! is_string( $url ) ) {
			return $default;
		}

		$candidate  = esc_url_raw( $url );
		$admin_host = wp_parse_url( admin_url(), PHP_URL_HOST );
		$url_host   = wp_parse_url( $candidate, PHP_URL_HOST );

		if ( '' === $candidate || $admin_host !== $url_host ) {
			return $default;
		}

		return $candidate;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Ai_Auth::get_instance();
