<?php
/**
 * AI Authentication.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Api\ApiBase;
/**
 * Checkout Markup
 *
 * @since x.x.x
 */
class Cartflows_Ai_API extends ApiBase {

	/**
	 * Member Variable
	 *
	 * @var object instance
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
	 * Constructor
	 *
	 * @since x.x.x
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register API routes.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_api_namespace(),
			'/ai/auth',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'verify_auth' ),
				'permission_callback' => array( $this, 'validate_permission' ),
				'args'                => array(
					'accessKey' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			$this->get_api_namespace(),
			'/ai/auth',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_auth_url' ),
				'permission_callback' => array( $this, 'validate_permission' ),
				'args'                => array(
					'redirect_back' => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
					),
				),
			)
		);
	}

	/**
	 * Verify Auth
	 *
	 * @since x.x.x
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return void
	 */
	public function verify_auth( $request ) {
		$access_key = $request->get_param( 'accessKey' );

		if ( ! isset( $access_key ) || empty( $access_key ) ) {
			wp_send_json_error( array( 'message' => __( 'No access key provided.', 'cartflows' ) ) );
		}
		
		$saved = Cartflows_Ai_Auth::get_instance()->save_auth( $access_key, Cartflows_Ai_Auth::get_instance()->key );

		if ( is_wp_error( $saved ) && $saved instanceof WP_Error ) {
			wp_send_json_error( array( 'message' => $saved->get_error_message() ) );
		}

		if ( false === $saved ) {
			wp_send_json_error( array( 'message' => __( 'Failed to save authentication data.', 'cartflows' ) ) );
		}
		
		wp_send_json_success( array( 'message' => __( 'Authentication data saved.', 'cartflows' ) ) );
	}

	/**
	 * Submit URLs to IndexNow API.
	 *
	 * @since x.x.x
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return void
	 */
	public function get_auth_url( $request ) {
		if ( Cartflows_Ai_Auth::get_instance()->get_auth_status() ) {
			wp_send_json_success( array( 'message' => __( 'Authentication is already completed.', 'cartflows' ) ) );
		}

		$redirect_back = $request->get_param( 'redirect_back' );
		$redirect_back = is_string( $redirect_back ) ? $redirect_back : '';
		$auth          = Cartflows_Ai_Auth::get_instance()->get_auth_url( $redirect_back );

		if ( is_wp_error( $auth ) && $auth instanceof WP_Error ) {
			wp_send_json_error( array( 'message' => $auth->get_error_message() ) );
		} else {
			wp_send_json_success( array( 'auth_url' => $auth ) );
		}

	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Ai_API::get_instance();
