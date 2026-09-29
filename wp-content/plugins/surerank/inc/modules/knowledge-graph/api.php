<?php
/**
 * Knowledge Graph API class
 *
 * Handles knowledge graph settings related REST API endpoints.
 *
 * @package SureRank\Inc\Modules\Knowledge_Graph
 * @since 1.6.6
 */

namespace SureRank\Inc\Modules\Knowledge_Graph;

use SureRank\Inc\API\Api_Base;
use SureRank\Inc\Functions\Send_Json;
use SureRank\Inc\Traits\Get_Instance;
use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Api
 *
 * Handles knowledge graph settings related REST API endpoints.
 */
class Api extends Api_Base {
	use Get_Instance;

	/**
	 * Route for Knowledge Graph settings
	 */
	protected const KNOWLEDGE_GRAPH = '/knowledge-graph';

	/**
	 * Knowledge Graph Controller instance.
	 *
	 * @var Controller
	 * @since 1.6.6
	 */
	private $controller;

	/**
	 * Constructor
	 *
	 * @since 1.6.6
	 */
	public function __construct() {
		parent::__construct();
		$this->controller = Controller::get_instance();
	}

	/**
	 * Register API routes.
	 *
	 * @since 1.6.6
	 * @return void
	 */
	public function register_routes() {
		$namespace = $this->get_api_namespace();

		// GET route - retrieve Knowledge Graph settings.
		register_rest_route(
			$namespace,
			self::KNOWLEDGE_GRAPH,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_settings' ],
				'permission_callback' => [ $this, 'validate_permission' ],
			]
		);

		// POST route - update Knowledge Graph settings.
		register_rest_route(
			$namespace,
			self::KNOWLEDGE_GRAPH,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_settings' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_update_args(),
			]
		);
	}

	/**
	 * Get Knowledge Graph settings.
	 *
	 * @since 1.6.6
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return void
	 */
	public function get_settings( $request ) {
		$data = $this->controller->get_settings();
		Send_Json::success( [ 'data' => $data ] );
	}

	/**
	 * Update Knowledge Graph settings.
	 *
	 * @since 1.6.6
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return void
	 */
	public function update_settings( $request ) {
		$data = $request->get_params();

		$result = $this->controller->update_settings( $data );

		if ( is_wp_error( $result ) ) {
			Send_Json::error( [ 'message' => $result->get_error_message() ] );
		}

		Send_Json::success( [ 'message' => __( 'Settings updated successfully', 'surerank' ) ] );
	}

	/**
	 * Get update arguments.
	 *
	 * @since 1.6.6
	 * @return array<string, array<string, mixed>>
	 */
	private function get_update_args() {
		return [
			'website_type'         => [
				'type'              => 'string',
				'required'          => false,
				'description'       => __( 'Type of the website.', 'surerank' ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function( $value ) {
					return is_string( $value );
				},
			],
			'website_name'         => [
				'type'              => 'string',
				'required'          => false,
				'description'       => __( 'Name of the website.', 'surerank' ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function( $value ) {
					return is_string( $value );
				},
			],
			'business_description' => [
				'type'              => 'string',
				'required'          => false,
				'description'       => __( 'Business description of the website.', 'surerank' ),
				'sanitize_callback' => 'sanitize_textarea_field',
				'validate_callback' => static function( $value ) {
					return is_string( $value );
				},
			],
			'website_owner_name'   => [
				'type'              => 'string',
				'required'          => false,
				'description'       => __( 'Name of the website owner.', 'surerank' ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function( $value ) {
					return is_string( $value );
				},
			],
			'website_owner_phone'  => [
				'type'              => 'string',
				'required'          => false,
				'description'       => __( 'Phone number of the website owner.', 'surerank' ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function( $value ) {
					return is_string( $value );
				},
			],
			'organization_type'    => [
				'type'              => 'string',
				'required'          => false,
				'description'       => __( 'Type of the organization.', 'surerank' ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => static function( $value ) {
					return is_string( $value );
				},
			],
			'about_page'           => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'absint',
			],
			'contact_page'         => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'absint',
			],
			'website_logo'         => [
				'type'     => 'string',
				'required' => false,
			],
		];
	}
}
