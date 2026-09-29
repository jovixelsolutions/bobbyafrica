<?php
/**
 * Third Party Plugins class - Bricks
 *
 * Handles Bricks Plugin related compatibility.
 *
 * @package SureRank\Inc\ThirdPartyIntegrations
 */

namespace SureRank\Inc\ThirdPartyIntegrations;

use SureRank\Inc\Admin\Dashboard;
use SureRank\Inc\Admin\Seo_Popup;
use SureRank\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Bricks
 *
 * Handles Bricks Plugin related compatibility.
 */
class Bricks {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		if ( ! defined( 'BRICKS_VERSION' ) ) {
			return;
		}

		// Runs in admin/save context too — not limited to the visual builder.
		add_filter( 'surerank_post_analyzer_content', [ $this, 'process_bricks_content' ], 10, 2 );
		add_filter( 'surerank_meta_variable_post_content', [ $this, 'process_bricks_content' ], 10, 2 );

		if ( ! function_exists( 'bricks_is_builder_main' ) || ! bricks_is_builder_main() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', [ $this, 'register_script' ], 9999 );
		add_action( 'wp_enqueue_scripts', [ Dashboard::get_instance(), 'site_seo_check_enqueue_scripts' ], 999 );
		add_filter( 'surerank_globals_localization_vars', [ $this, 'add_localization_vars' ] );
	}

	/**
	 * Replace empty post_content with HTML extracted from Bricks element data.
	 *
	 * Bricks stores page content in the `_bricks_page_content_2` post meta as a flat
	 * array of element objects. WordPress's `post_content` is typically empty for
	 * Bricks-built posts, so the SEO analyzer must read the meta instead.
	 *
	 * Bricks saves its meta before calling wp_update_post(), so by the time
	 * wp_after_insert_post fires the data is already persisted and readable here.
	 *
	 * @param string   $content Post content (usually empty for Bricks posts).
	 * @param \WP_Post $post    Post object.
	 * @return string HTML extracted from Bricks elements, or the original $content.
	 * @since 1.7.0
	 */
	public function process_bricks_content( string $content, \WP_Post $post ): string {
		// Skip posts that have been switched back to the WordPress block editor.
		$editor_mode = get_post_meta( $post->ID, BRICKS_DB_EDITOR_MODE, true );
		if ( 'WordPress' === $editor_mode ) {
			return $content;
		}

		$elements = get_post_meta( $post->ID, BRICKS_DB_PAGE_CONTENT, true );
		if ( ! is_array( $elements ) || empty( $elements ) ) {
			return $content;
		}

		return $this->extract_bricks_html( $elements );
	}

	/**
	 * Add localization variables for Bricks.
	 *
	 * @param array<string,mixed> $vars Localization variables.
	 * @return array<string,mixed> Updated localization variables.
	 * @since 1.1.0
	 */
	public function add_localization_vars( array $vars ) {
		return array_merge(
			$vars,
			[
				'is_bricks' => true,
			]
		);
	}

	/**
	 * Register Script
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function register_script() {
		Seo_Popup::get_instance()->admin_enqueue_scripts();

		$asset_path = SURERANK_DIR . 'build/bricks/index.asset.php';
		$asset_info = file_exists( $asset_path ) ? include $asset_path : [
			'dependencies' => [ 'jquery', 'wp-data' ],
			'version'      => SURERANK_VERSION,
		];

		wp_register_script( 'surerank-bricks', SURERANK_URL . 'build/bricks/index.js', $asset_info['dependencies'], $asset_info['version'], false );
		wp_enqueue_script( 'surerank-bricks' );
	}

	/**
	 * Render Bricks elements to HTML using Bricks' own rendering pipeline.
	 *
	 * Frontend::render_data() returns a string (it does not echo), so no output
	 * buffering is needed. Database::$page_data defaults to ['preview_or_post_id' => 0],
	 * so calling this in admin/save context is safe — no fatal errors occur.
	 *
	 * @param array<int, array<string, mixed>> $elements Flat Bricks elements array.
	 * @return string Rendered HTML string.
	 * @since 1.7.0
	 */
	private function extract_bricks_html( array $elements ): string {
		if ( ! class_exists( '\Bricks\Frontend' ) || ! method_exists( '\Bricks\Frontend', 'render_data' ) ) {
			return '';
		}
		return \Bricks\Frontend::render_data( $elements, 'content' ) ?? '';
	}
}
