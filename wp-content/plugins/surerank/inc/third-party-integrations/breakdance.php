<?php
/**
 * Third Party Plugins class - Breakdance
 *
 * Handles Breakdance page builder compatibility.
 *
 * @package SureRank\Inc\ThirdPartyIntegrations
 */

namespace SureRank\Inc\ThirdPartyIntegrations;

use SureRank\Inc\Admin\Dashboard;
use SureRank\Inc\Admin\Seo_Popup;
use SureRank\Inc\Frontend\Image_Seo;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Breakdance
 *
 * Handles Breakdance page builder related compatibility.
 *
 * Architecture note:
 * The Breakdance editor is a standalone Vue.js SPA that bypasses WordPress's
 * script enqueue system entirely (no wp_head / wp_footer). Scripts are injected
 * via two mechanisms:
 *
 * 1. Editor toolbar button: `registerBuilderPlugin()` injects an inline JS
 *    string into the builder SPA context (no wp.data / React available).
 *
 * 2. SEO popup: Rendered in the editor page via the Breakdance footer hook.
 *    We manually call wp_print_scripts() / wp_print_styles() to output assets
 *    since the editor template does not call wp_head() / wp_footer().
 *    The toolbar button communicates with the popup via window.postMessage().
 */
class Breakdance {

	use Get_Instance;

	/**
	 * Tracks whether the builder plugin JS has been registered to prevent
	 * double-registration if breakdance_loaded fires after our constructor.
	 *
	 * @var bool
	 */
	private bool $builder_plugin_registered = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! defined( '__BREAKDANCE_VERSION' ) ) {
			return;
		}

		// Content processing runs in all contexts (admin, REST, AJAX, frontend).
		// Registered directly — no action hook needed.
		add_filter( 'surerank_post_analyzer_content', [ $this, 'process_breakdance_content' ], 10, 2 );
		add_filter( 'surerank_meta_variable_post_content', [ $this, 'process_breakdance_content' ], 10, 2 );

		// Register the editor toolbar button JS after Breakdance is fully loaded.
		// breakdance_loaded fires on plugins_loaded — use did_action() as a guard
		// in case Breakdance loaded before us and the action already fired.
		add_action( 'breakdance_loaded', [ $this, 'maybe_register_builder_plugin' ] );

		if ( did_action( 'breakdance_loaded' ) > 0 ) {
			$this->maybe_register_builder_plugin();
		}

		// Render the SEO popup in the Breakdance editor page.
		// The editor template fires this hook in its footer — it is the only
		// extension point since the editor does not call wp_head() / wp_footer().
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		add_action( 'unofficial_i_am_kevin_geary_master_of_all_things_css_and_html', [ $this, 'render_editor_popup' ] );
	}

	/**
	 * Replace empty post_content with HTML rendered from Breakdance element data.
	 *
	 * Breakdance stores page content as a JSON element tree in post meta.
	 * WordPress post_content is typically empty for Breakdance-built pages,
	 * so the SEO analyzer must read and render the meta instead.
	 *
	 * @param string   $content Post content (usually empty for Breakdance pages).
	 * @param \WP_Post $post    Post object.
	 * @return string Rendered HTML from Breakdance elements, or the original $content.
	 */
	public function process_breakdance_content( string $content, \WP_Post $post ): string {
		if ( ! function_exists( '\Breakdance\Data\get_tree_as_html' ) ||
			! function_exists( '\Breakdance\Data\get_tree' ) ) {
			return $content;
		}

		// Quick check: does this post have a Breakdance element tree?
		// get_tree() reads the meta and decodes JSON — cheaper than full rendering.
		if ( ! \Breakdance\Data\get_tree( $post->ID ) ) {
			return $content;
		}

		$html = \Breakdance\Data\get_tree_as_html( $post->ID );

		return $html ? $html : $content;
	}

	/**
	 * Register the SureRank toolbar button JS with Breakdance's Plugin API.
	 *
	 * Called on breakdance_loaded (or immediately if that action has already fired).
	 * The JS string is executed in the builder SPA context — no WordPress libraries
	 * (wp.data, React, jQuery) are available there.
	 *
	 * @return void
	 */
	public function maybe_register_builder_plugin(): void {
		if ( $this->builder_plugin_registered ) {
			return;
		}

		$this->builder_plugin_registered = true;

		if ( ! function_exists( '\Breakdance\PluginsAPI\registerBuilderPlugin' ) ) {
			return;
		}

		$js = $this->get_builder_plugin_js();
		if ( $js ) {
			\Breakdance\PluginsAPI\registerBuilderPlugin( $js );
		}
	}

	/**
	 * Render the SureRank SEO popup in the Breakdance editor page.
	 *
	 * Hooked on the Breakdance editor footer action. Since the editor template
	 * does not call wp_head() / wp_footer(), we register assets and then
	 * manually call wp_print_styles() / wp_print_scripts() to output them.
	 *
	 * Page checks use the REST API (/surerank/v1/checks/page) with server-side
	 * content extraction, so iframe DOM access is not required.
	 *
	 * @return void
	 */
	public function render_editor_popup(): void {
		if ( ! current_user_can( 'surerank_content_setting' ) ) {
			return;
		}

		// Get post ID from Breakdance editor URL (?breakdance=builder&id=POST_ID).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! $post_id ) {
			return;
		}

		$seo_popup = Seo_Popup::get_instance();

		wp_enqueue_media();
		$seo_popup->enqueue_vendor_and_common_assets();

		$seo_popup->build_assets_operations(
			'seo-popup',
			[
				'hook'        => 'seo-popup',
				'object_name' => 'seo_popup',
				'data'        => [
					'admin_assets_url'   => SURERANK_URL . 'inc/admin/assets',
					'site_icon_url'      => get_site_icon_url( 16 ),
					'editor_type'        => 'breakdance',
					'post_type'          => get_post_type( $post_id ) ? get_post_type( $post_id ) : '',
					'is_taxonomy'        => false,
					'description_length' => Get::description_length(),
					'title_length'       => Get::title_length(),
					'keyword_checks'     => $seo_popup->keyword_checks(),
					'page_checks'        => $seo_popup->page_checks(),
					'image_seo'          => Image_Seo::get_instance()->status(),
					'post_id'            => $post_id,
					'link'               => get_the_permalink( $post_id ),
				],
			]
		);

		// Register the is_breakdance flag only in the Breakdance editor context
		// (not globally) so isBreakdanceBuilder() doesn't return true in Gutenberg.
		add_filter( 'surerank_globals_localization_vars', [ $this, 'add_localization_vars' ] );

		// Enqueue surerank_globals (provides is_breakdance flag for JS).
		Dashboard::get_instance()->site_seo_check_enqueue_scripts();

		// Enqueue the Breakdance editor integration script.
		$this->enqueue_breakdance_editor_script();

		/**
		 * Fires after SureRank assets are enqueued in the Breakdance editor,
		 * before wp_print_scripts(). Allows Pro plugin to enqueue additional assets.
		 *
		 * @param int $post_id The current post ID.
		 */
		do_action( 'surerank_breakdance_editor_enqueue', $post_id );

		// Mount point for the React popup.
		echo '<div id="surerank-root" class="surerank-root"></div>';

		// Output all registered styles and scripts.
		// WordPress resolves dependencies automatically (react, wp-data, etc.).
		wp_print_styles();
		wp_print_scripts();
	}

	/**
	 * Add is_breakdance flag to surerank_globals localization.
	 *
	 * Mirrors Bricks::add_localization_vars() so that JS code can detect
	 * the active builder via window.surerank_globals.is_breakdance.
	 *
	 * @param array<string,mixed> $vars Existing localization variables.
	 * @return array<string,mixed> Updated localization variables.
	 */
	public function add_localization_vars( array $vars ): array {
		return array_merge( $vars, [ 'is_breakdance' => true ] );
	}

	/**
	 * Enqueue the bundled Breakdance editor integration script.
	 *
	 * This webpack-built script runs in the Breakdance editor page. It waits
	 * for the SureRank store to initialize, triggers page checks via REST API,
	 * and bridges status updates to the toolbar button via window.postMessage().
	 *
	 * @return void
	 */
	private function enqueue_breakdance_editor_script(): void {
		$asset_path = SURERANK_DIR . 'build/breakdance/index.asset.php';

		if ( ! file_exists( $asset_path ) ) {
			return;
		}

		$asset_info = include $asset_path;

		// Ensure surerank-seo-popup is a dependency so the store is available.
		$asset_info['dependencies'][] = 'surerank-seo-popup';
		$asset_info['dependencies']   = array_unique( $asset_info['dependencies'] );

		wp_register_script(
			'surerank-breakdance',
			SURERANK_URL . 'build/breakdance/index.js',
			$asset_info['dependencies'],
			$asset_info['version'],
			false
		);

		wp_enqueue_script( 'surerank-breakdance' );
	}

	/**
	 * Load the inline JS string for the Breakdance editor toolbar button.
	 *
	 * Reads the JS from a separate file for maintainability. The content is
	 * passed as a raw string to registerBuilderPlugin() — it executes inside
	 * the Breakdance Vue.js SPA where no WordPress JS libraries are available.
	 *
	 * @return string|false JS content, or false if the file is missing.
	 */
	private function get_builder_plugin_js() {
		$js_file = SURERANK_DIR . 'src/apps/breakdance/breakdance-builder-plugin.js';

		if ( ! file_exists( $js_file ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		return file_get_contents( $js_file );
	}
}
