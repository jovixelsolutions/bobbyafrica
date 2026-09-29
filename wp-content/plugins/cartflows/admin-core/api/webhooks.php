<?php
/**
 * CartFlows Webhooks REST API Controller.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Api\ApiBase;

/**
 * Class Webhooks.
 */
class Webhooks extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/webhooks/';

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register API routes.
	 */
	public function register_routes() {

		$namespace = $this->get_api_namespace();

		// List + Create webhooks.
		register_rest_route(
			$namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_webhooks' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_webhook' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Update + Delete a webhook.
		register_rest_route(
			$namespace,
			$this->rest_base . '(?P<id>[a-zA-Z0-9-]+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_webhook' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_webhook' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Send test webhook.
		register_rest_route(
			$namespace,
			$this->rest_base . '(?P<id>[a-zA-Z0-9-]+)/test',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'test_webhook' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Get available events.
		register_rest_route(
			$namespace,
			$this->rest_base . 'events',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_events' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Permission check.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return bool|\WP_Error
	 */
	public function permissions_check( $request ) {
		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			return new \WP_Error(
				'cartflows_rest_cannot_manage',
				__( 'Sorry, you are not allowed to manage webhooks.', 'cartflows' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

	/**
	 * Get all webhooks.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function get_webhooks( $request ) {
		$manager  = \Cartflows_Webhook_Manager::get_instance();
		$webhooks = $manager->get_webhooks();

		return new \WP_REST_Response(
			array(
				'success'  => true,
				'webhooks' => $webhooks,
			),
			200
		);
	}

	/**
	 * Create a webhook.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function create_webhook( $request ) {
		$manager = \Cartflows_Webhook_Manager::get_instance();
		$data    = $request->get_json_params();

		if ( empty( $data ) ) {
			$data = $request->get_params();
		}

		$webhook = $manager->add_webhook( $data );

		if ( ! $webhook ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Invalid webhook URL.', 'cartflows' ),
				),
				400
			);
		}

		\Cartflows_Helper::set_analytics_flag( 'first_webhook_configured' );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'webhook' => $webhook,
			),
			201
		);
	}

	/**
	 * Update a webhook.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function update_webhook( $request ) {
		$manager = \Cartflows_Webhook_Manager::get_instance();
		$id      = $request->get_param( 'id' );
		$data    = $request->get_json_params();

		if ( empty( $data ) ) {
			$data = $request->get_params();
		}

		if ( isset( $data['url'] ) && ! wp_http_validate_url( esc_url_raw( $data['url'] ) ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Invalid webhook URL.', 'cartflows' ),
				),
				400
			);
		}

		$webhook = $manager->update_webhook( $id, $data );

		if ( ! $webhook ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Webhook not found.', 'cartflows' ),
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'webhook' => $webhook,
			),
			200
		);
	}

	/**
	 * Delete a webhook.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function delete_webhook( $request ) {
		$manager = \Cartflows_Webhook_Manager::get_instance();
		$id      = $request->get_param( 'id' );
		$deleted = $manager->delete_webhook( $id );

		if ( ! $deleted ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Webhook not found.', 'cartflows' ),
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Webhook deleted successfully.', 'cartflows' ),
			),
			200
		);
	}

	/**
	 * Send a test webhook.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function test_webhook( $request ) {
		$id         = $request->get_param( 'id' );
		$dispatcher = \Cartflows_Webhook_Dispatcher::get_instance();
		$result     = $dispatcher->send_test( $id );

		return new \WP_REST_Response(
			array(
				'success'       => $result['success'],
				'response_code' => $result['response_code'],
				'error'         => $result['error'],
			),
			200
		);
	}

	/**
	 * Get available webhook events.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function get_events( $request ) {
		$events = \Cartflows_Webhook_Events::get_available_events();

		return new \WP_REST_Response(
			array(
				'success' => true,
				'events'  => $events,
			),
			200
		);
	}
}
