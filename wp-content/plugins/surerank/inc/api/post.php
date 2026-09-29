<?php
/**
 * Post class
 *
 * Handles post related REST API endpoints for the SureRank plugin.
 *
 * @package SureRank\Inc\API
 */

namespace SureRank\Inc\API;

use SureRank\Inc\Functions\Defaults;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Functions\Helper;
use SureRank\Inc\Functions\Send_Json;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Functions\Update;
use SureRank\Inc\Import_Export\Utils as ImportExportUtils;
use SureRank\Inc\Schema\SchemasApi;
use SureRank\Inc\Traits\Get_Instance;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Post
 *
 * Handles post related REST API endpoints.
 */
class Post extends Api_Base {
	use Get_Instance;

	/**
	 * Route Get Post Seo Data
	 */
	protected const POST_SEO_DATA = '/post/settings';

	/**
	 * Route Get Post Content
	 */
	protected const POST_CONTENT = '/admin/post-content';

	/**
	 * Route Get Posts List
	 */
	protected const POSTS_LIST = '/posts-list';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Register API routes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		$namespace = $this->get_api_namespace();
		$this->register_all_post_routes( $namespace );
	}

	/**
	 * Get post seo data
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @since 1.0.0
	 * @return void
	 */
	public function get_post_seo_data( $request ) {

		$post_id     = $request->get_param( 'post_id' );
		$post_type   = $request->get_param( 'post_type' );
		$is_taxonomy = $request->get_param( 'is_taxonomy' );

		$data        = self::get_post_data_by_id( $post_id, $post_type, $is_taxonomy );
		$decode_data = Utils::decode_html_entities_recursive( $data ) ?? $data;
		Send_Json::success( $decode_data );
	}

	/**
	 * Get post data by id
	 *
	 * @param int    $post_id Post id.
	 * @param string $post_type Post type.
	 * @param bool   $is_taxonomy Is taxonomy.
	 * @return array<string, mixed>
	 */
	public static function get_post_data_by_id( $post_id, $post_type = 'post', $is_taxonomy = false ) {
		$all_options   = Settings::format_array( Defaults::get_instance()->get_post_defaults( false ) );
		$global_values = Settings::get();
		$extended_meta = Utils::get_extended_meta_values( $post_id, $post_type, $is_taxonomy );
		// Merge extended meta templates into global defaults for preview fallback.
		$global_with_emt = array_merge( $global_values, $extended_meta );

		return [
			'data'           => array_intersect_key( Settings::prep_post_meta( $post_id, $post_type, $is_taxonomy ), $all_options ),
			'global_default' => $global_with_emt,
		];
	}

	/**
	 * Update seo data
	 *
	 * REST endpoint handler. Extracts params from the request, delegates to
	 * the transport-free save_post_seo_meta() helper, and emits the result
	 * as JSON. The helper is shared with the AJAX fallback registered in
	 * inc/ajax/save-endpoints.php so both paths produce identical side
	 * effects for identical input.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @since 1.0.0
	 * @return void
	 */
	public function update_post_seo_data( $request ) {

		$post_id = (int) $request->get_param( 'post_id' );
		$data    = (array) $request->get_param( 'metaData' );

		$result = self::save_post_seo_meta( $post_id, $data );

		if ( $result['success'] ) {
			// REST reached and save succeeded — record as evidence for Site Health.
			\SureRank\Inc\Functions\Rest_Observation::mark_reachable();
			Send_Json::success( [ 'message' => $result['message'] ] );
		}

		Send_Json::error( [ 'message' => $result['message'] ] );
	}

	/**
	 * Save post SEO meta — transport-free core logic.
	 *
	 * Called by the REST endpoint handler above and by the AJAX fallback
	 * handler in inc/ajax/save-endpoints.php. Returns a result array rather
	 * than emitting a response, so both callers can transport it in their
	 * native format (wp_send_json / Send_Json).
	 *
	 * Side effects (on success):
	 *   - Downloads external feature-image URLs and updates data in place
	 *   - Writes post meta via update_post_meta_common()
	 *   - Runs the surerank_run_post_seo_checks filter
	 *   - Updates the global and per-post last-optimised timestamps
	 *
	 * Side effects (on failure): no timestamps are written. This matches
	 * the pre-refactor behaviour of update_post_seo_data() and avoids
	 * marking a post as "optimized" when the SEO checks errored.
	 *
	 * @param int                  $post_id Post ID to save meta against.
	 * @param array<string, mixed> $data    Meta payload (already sanitised).
	 * @return array{success: bool, message: string}
	 * @since 1.x.x
	 */
	public static function save_post_seo_meta( int $post_id, array $data ): array {
		self::update_feature_image_data( $post_id, $data );
		self::update_post_meta_common( $post_id, $data );

		$check_result = self::get_instance()->run_checks( $post_id );
		if ( is_wp_error( $check_result ) ) {
			return [
				'success' => false,
				'message' => __( 'Error while running SEO Checks.', 'surerank' ),
			];
		}

		$current_time = time();
		Update::option( 'surerank_last_optimized_on', $current_time ); // Site-wide last optimisation.
		Update::post_meta( $post_id, 'surerank_post_optimized_at', $current_time ); // Per-post optimisation timestamp.

		return [
			'success' => true,
			'message' => __( 'Data updated', 'surerank' ),
		];
	}

	/**
	 * Update feature image data
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $data Data to update (passed by reference to update URLs and IDs).
	 * @return void
	 */
	public static function update_feature_image_data( int $post_id, array &$data ): void {
		$image_fields = [
			'facebook_image_url' => 'facebook_image_id',
			'twitter_image_url'  => 'twitter_image_id',
			'featured_image_url' => 'featured_image_id',
		];

		foreach ( $image_fields as $url_field => $id_field ) {
			if ( ! isset( $data[ $url_field ] ) || empty( $data[ $url_field ] ) ) {
				continue;
			}

			$image_url = $data[ $url_field ];

			if ( strpos( $image_url, home_url() ) !== false ) {
				continue;
			}

			$download_result = self::download_external_image( $image_url, $post_id );

			if ( $download_result['success'] ) {
				$data[ $url_field ] = $download_result['data']['url'];
				$data[ $id_field ]  = $download_result['data']['attachment_id'];

				if ( 'featured_image_url' === $url_field ) {
					set_post_thumbnail( $post_id, $download_result['data']['attachment_id'] );
				}
			}
		}
	}

	/**
	 * Download external image and save to WordPress media library
	 *
	 * Uses the centralized download_and_save_image() utility which includes
	 * SSRF protection and redirect blocking for security.
	 *
	 * @param string $image_url Image URL to download.
	 * @param int    $post_id Post ID to associate the image with (unused, kept for compatibility).
	 * @return array<string, mixed> Result with success status and data.
	 */
	public static function download_external_image( string $image_url, int $post_id ): array {
		$result = ImportExportUtils::download_and_save_image( $image_url );

		if ( ! $result['success'] ) {
			return $result;
		}

		$attachment_id = $result['data']['attachment_id'] ?? 0;

		// Add generated image meta (in addition to _surerank_imported from utils).
		if ( $attachment_id ) {
			add_post_meta( $attachment_id, '_surerank_generated_image', true );
		}

		return $result;
	}

	/**
	 * Update post meta common
	 * This function updates the post meta for a given post ID with the provided data.
	 * It merges existing post meta with the new data, ensuring that all options are updated correctly.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $data Data to update.
	 * @since 1.0.0
	 * @return void
	 */
	public static function update_post_meta_common( int $post_id, array $data ): void {
		$all_options = Defaults::get_instance()->get_post_defaults( false );

		/** Getting post meta if exists, otherwise getting all options(defaults) */
		$post_meta = Get::all_post_meta( $post_id );
		if ( ! empty( $post_meta ) ) {
			$data = array_merge( $post_meta, $data );
		}

		$post_type         = get_post_type( $post_id );
		$post_type         = is_string( $post_type ) ? $post_type : '';
		$processed_options = Utils::process_option_values( $all_options, $data, $post_id, $post_type, false );

		foreach ( $processed_options as $option_name => $new_option_value ) {
			Update::post_meta( $post_id, 'surerank_settings_' . $option_name, $new_option_value );
		}
	}

	/**
	 * Get post types
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @since 1.0.0
	 * @return void
	 */
	public function get_post_type_data( $request ) {
		$data = $this->prepare_post_type_data();
		Send_Json::success( [ 'data' => $data ] );
	}

	/**
	 * Run checks
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Error|array<string, mixed>
	 */
	public function run_checks( $post_id ) {
		if ( ! $post_id ) {
			return new WP_Error( 'no_post_id', __( 'No post ID provided.', 'surerank' ) );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'no_post', __( 'No post found.', 'surerank' ) );
		}

		return apply_filters( 'surerank_run_post_seo_checks', $post_id, $post );
	}

	/**
	 * Get posts list
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @since 1.6.3
	 * @return array<int, array<string, mixed>>
	 */
	public function get_posts_list( $request ) {
		$search    = $request->get_param( 'search' );
		$page      = $request->get_param( 'page' );
		$per_page  = $request->get_param( 'per_page' );
		$post_type = $request->get_param( 'post_type' );
		$exclude   = $request->get_param( 'exclude' );

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
			's'              => $search,
			'fields'         => 'ids',
		];

		if ( ! empty( $exclude ) ) {
			$args['post__not_in'] = $exclude;
		}

		$schemas_api = SchemasApi::get_instance();
		add_filter( 'posts_search', [ $schemas_api, 'search_only_titles' ], 10, 2 );
		$query = new \WP_Query( $args );
		remove_filter( 'posts_search', [ $schemas_api, 'search_only_titles' ], 10 );
		$posts = [];

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post_id ) {
				$posts[] = [
					'label' => get_the_title( $post_id ),
					'value' => $post_id,
				];
			}
		}

		return $posts;
	}

	/**
	 * Register all post routes
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_all_post_routes( $namespace ) {
		$this->register_get_post_seo_data_route( $namespace );
		$this->register_update_post_seo_data_route( $namespace );
		$this->register_post_content_route( $namespace );
		$this->register_posts_list_route( $namespace );
	}

	/**
	 * Register get post SEO data route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_get_post_seo_data_route( $namespace ) {
		register_rest_route(
			$namespace,
			self::POST_SEO_DATA,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_post_seo_data' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_post_seo_data_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register update post SEO data route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_update_post_seo_data_route( $namespace ) {
		register_rest_route(
			$namespace,
			self::POST_SEO_DATA,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_post_seo_data' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_update_post_seo_data_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register post content route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_post_content_route( $namespace ) {
		register_rest_route(
			$namespace,
			self::POST_CONTENT,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_post_type_data' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'role_capability'     => 'global_setting',
			]
		);
	}

	/**
	 * Register posts list route
	 *
	 * @since 1.6.3
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_posts_list_route( $namespace ) {
		register_rest_route(
			$namespace,
			self::POSTS_LIST,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_posts_list' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_posts_list_args(),
			]
		);
	}

	/**
	 * Get posts list arguments
	 *
	 * @since 1.6.3
	 * @return array<string, array<string, mixed>>
	 */
	private function get_posts_list_args() {
		return [
			'search'    => [
				'type'              => 'string',
				'required'          => false,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'page'      => [
				'type'              => 'integer',
				'required'          => false,
				'default'           => 1,
				'sanitize_callback' => 'absint',
			],
			'per_page'  => [
				'type'              => 'integer',
				'required'          => false,
				'default'           => 20,
				'sanitize_callback' => 'absint',
			],
			'post_type' => [
				'type'              => 'string',
				'required'          => false,
				'default'           => 'post',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get post SEO data arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_post_seo_data_args() {
		return [
			'post_id'   => [
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'post_type' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get update post SEO data arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_update_post_seo_data_args() {
		return [
			'post_id'  => [
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'metaData' => [
				'type'              => 'object',
				'required'          => true,
				'sanitize_callback' => [ $this, 'sanitize_array_data' ],
			],
		];
	}

	/**
	 * Prepare post type data
	 *
	 * @return array<string, mixed>
	 */
	private function prepare_post_type_data() {
		$data     = [];
		$settings = Settings::get();

		$data['post_types'] = Helper::get_formatted_post_types();
		$data['taxonomies'] = Helper::get_formatted_taxonomies();
		$data['archives']   = $this->build_archives_list( $settings );
		$data['roles']      = Helper::get_role_names();

		return array_filter( $data );
	}

	/**
	 * Build archives list
	 *
	 * @param array<string, mixed> $settings Settings array.
	 * @return array<string, string>
	 */
	private function build_archives_list( $settings ) {
		$archives = [];

		if ( $settings['author_archive'] ?? false ) {
			$archives['author'] = __( 'Author pages', 'surerank' );
		}

		if ( $settings['date_archive'] ?? false ) {
			$archives['date'] = __( 'Date archives', 'surerank' );
		}

		$archives['search'] = __( 'Search pages', 'surerank' );

		return $archives;
	}
}
