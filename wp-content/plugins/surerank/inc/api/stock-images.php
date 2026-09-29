<?php
/**
 * Stock Images API class
 *
 * Handles stock images related REST API endpoints for the SureRank plugin.
 *
 * @package SureRank\Inc\API
 */

namespace SureRank\Inc\API;

use SureRank\Inc\Functions\Send_Json;
use SureRank\Inc\Traits\Get_Instance;
use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Stock_Images
 *
 * Handles stock images related REST API endpoints.
 */
class Stock_Images extends Api_Base {
	use Get_Instance;

	/**
	 * Route Stock Images
	 */
	protected const STOCK_IMAGES = '/admin/stock-images';

	/**
	 * Constructor
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
	}

	/**
	 * Register API routes.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public function register_routes() {
		$namespace = $this->get_api_namespace();
		$this->register_stock_images_route( $namespace );
	}

	/**
	 * Get stock images from external API
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The REST request object.
	 * @return void
	 */
	public function get_stock_images( $request ) {
		$keywords    = $request->get_param( 'keywords' );
		$page        = $request->get_param( 'page' ) ?? '1';
		$per_page    = $request->get_param( 'per_page' ) ?? '20';
		$filter      = $request->get_param( 'filter' ) ?? 'popular';
		$engine      = $request->get_param( 'engine' ) ?? 'pexels';
		$orientation = $request->get_param( 'orientation' ) ?? 'all';

		// Build request body.
		$body = [
			'keywords'    => $keywords,
			'page'        => $page,
			'per_page'    => $per_page,
			'filter'      => $filter,
			'engine'      => $engine,
			'orientation' => $orientation,
		];

		// Make request to external API.
		$response = wp_remote_post(
			'https://api.zipwp.com/api/images',
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json, */*;q=0.1',
					'Cache-Control' => 'no-cache',
					'Pragma'        => 'no-cache',
				],
				'body'    => (string) wp_json_encode( $body ),
				'timeout' => 30, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			]
		);

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			Send_Json::error(
				[
					'message' => $response->get_error_message(),
				]
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			Send_Json::error(
				[
					'message' => __( 'Failed to fetch images from external API', 'surerank' ),
					'code'    => $response_code,
				]
			);
		}

		$data = json_decode( $response_body, true );

		if ( empty( $data ) ) {
			Send_Json::error(
				[
					'message' => __( 'Invalid response from external API', 'surerank' ),
				]
			);
		}

		Send_Json::success( $data );
	}

	/**
	 * Sanitize keywords parameter
	 *
	 * @param mixed $value The value to sanitize.
	 * @return string|array<string>
	 */
	public function sanitize_keywords( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}
		return sanitize_text_field( $value );
	}

	/**
	 * Register stock images route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_stock_images_route( $namespace ) {
		register_rest_route(
			$namespace,
			self::STOCK_IMAGES,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_stock_images' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_stock_images_args(),
			]
		);
	}

	/**
	 * Get stock images route arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_stock_images_args() {
		return [
			'keywords'    => [
				'type'              => [ 'string', 'array' ],
				'required'          => false,
				'sanitize_callback' => [ $this, 'sanitize_keywords' ],
			],
			'page'        => [
				'type'              => 'string',
				'required'          => false,
				'default'           => '1',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'per_page'    => [
				'type'              => 'string',
				'required'          => false,
				'default'           => '20',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'filter'      => [
				'type'              => 'string',
				'required'          => false,
				'default'           => 'popular',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'engine'      => [
				'type'              => 'string',
				'required'          => false,
				'default'           => 'pexels',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'orientation' => [
				'type'              => 'string',
				'required'          => false,
				'default'           => 'all',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}
