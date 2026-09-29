<?php
/**
 * Divi Builder Integration
 *
 * @package SureRank\Inc\ThirdPartyIntegrations
 */

namespace SureRank\Inc\ThirdPartyIntegrations;

use SureRank\Inc\Admin\Dashboard;
use SureRank\Inc\Admin\Seo_Popup as Admin_Seo_Popup;
use SureRank\Inc\Frontend\Image_Seo;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Divi Builder Integration Class
 *
 * Handles both Divi 4 (et_pb_* shortcodes) and Divi 5 (WordPress blocks).
 *
 * DIVI 5 — native block rendering:
 *   Divi 5 registers all modules as WordPress block types, and explicitly
 *   enables this registration for REST API requests (see builder-5/server/
 *   bootstrap.php). WordPress core's do_blocks() invokes Divi's own
 *   server-side render callbacks for every module type automatically.
 *
 * DIVI 4 — et_builder_render_layout():
 *   Divi's own render function applies its full filter chain (do_blocks at
 *   priority 9, do_shortcode at priority 11), producing fully rendered HTML
 *   for every Divi 4 module type.
 */
class Divi {

	use Get_Instance;

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( ! $this->is_active() ) {
			return;
		}

		add_filter( 'surerank_post_analyzer_content', [ $this, 'process_divi_content' ], 10, 2 );
		add_filter( 'surerank_meta_variable_post_content', [ $this, 'process_divi_content' ], 10, 2 );

		// Divi Frontend Visual Builder (?et_fb=1) — enqueue popup on the frontend.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_for_visual_builder' ], 101 );

		// Enqueue surerank_globals on FVB frontend (same guard as enqueue_for_visual_builder).
		// Mirrors the Bricks pattern: hooked separately so jquery handle is reliably enqueued.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_globals_for_visual_builder' ], 999 );

		// Add is_divi flag to surerank_globals (mirrors Bricks add_localization_vars).
		add_filter( 'surerank_globals_localization_vars', [ $this, 'add_localization_vars' ] );

		// Admin bar button + click handler for any Divi admin editing context
		// (block editor, classic editor, BFB). Fires on admin_enqueue_scripts so the
		// inline script is appended after seo-popup is already in the queue.
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_add_divi_admin_click_handler' ], 99999 );

		// admin_bar_menu fires on both admin and frontend, so this handles both
		// the FVB top window (frontend) and all admin editor contexts in one hook.
		add_action( 'admin_bar_menu', [ $this, 'maybe_add_divi_admin_bar_menu' ], 100 );

		// Override editor type to 'divi' when Divi Backend Builder (BFB) is active
		// so the JS skips the classic .wrap > h1 button injection.
		add_filter( 'surerank_detect_editor_type', [ $this, 'filter_bfb_editor_type' ], 10, 2 );
	}

	/**
	 * Check if Divi Builder is active.
	 *
	 * ET_BUILDER_VERSION is defined inside et_setup_builder() which is always
	 * called by et-pagebuilder.php via the init hook, regardless of request
	 * context (admin, frontend, REST API, AJAX).
	 *
	 * @since 1.7.0
	 * @return bool
	 */
	public function is_active(): bool {
		return defined( 'ET_BUILDER_VERSION' ) || class_exists( 'ET_Builder_Element' );
	}

	/**
	 * Route content to the correct renderer based on Divi version.
	 *
	 * Detection strategy:
	 *   - Divi 5 pages store content as <!-- wp:divi/... --> block comments.
	 *     We detect this from the content itself because _et_pb_use_builder is
	 *     NOT reliably set for Divi 5 pages (it is only force-set to 'on' during
	 *     the Divi layout-block preview request, not on regular page saves).
	 *   - Divi 4 pages are detected via the _et_pb_use_builder = 'on' meta key,
	 *     which Divi 4 always writes when the builder is active for a post.
	 *
	 * @since 1.7.0
	 * @param string   $content Raw post_content.
	 * @param \WP_Post $post    Post being analyzed.
	 * @return string Rendered HTML for XPath analysis.
	 */
	public function process_divi_content( string $content, $post ): string {
		if ( ! $post instanceof \WP_Post ) {
			return $content;
		}

		// Divi 5 content always contains the wp:divi/ block namespace.
		if ( false !== strpos( $content, '<!-- wp:divi/' ) ) {
			return $this->process_divi5( $content );
		}

		// Divi 4 pages set this meta key when the visual builder is active.
		if ( 'on' !== get_post_meta( $post->ID, '_et_pb_use_builder', true ) ) {
			return $content;
		}

		return $this->process_divi4( $content );
	}

	/**
	 * Enqueue SEO popup for Divi Frontend Visual Builder (frontend ?et_fb=1).
	 *
	 * Fires on wp_enqueue_scripts at priority 101 (after Divi's own scripts).
	 * Calls Enqueue trait methods directly to avoid the is_admin_bar_showing()
	 * guard inside frontend_enqueue_scripts(), which can return false in
	 * certain Divi 5 rendering contexts even when the admin bar is visible.
	 *
	 * Skips the Divi 5 app-window iframe (?et_fb=1&app_window=1) — scripts
	 * should only load once, in the top-level builder window.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function enqueue_for_visual_builder(): void {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}

		// Divi 5 loads the page in two iframes: the outer builder shell (?et_fb=1)
		// and an inner content preview (?et_fb=1&app_window=1). Only enqueue in
		// the outer window to avoid double-loading assets.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['app_window'] ) ) {
			return;
		}

		if ( ! current_user_can( 'surerank_content_setting' ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$seo_popup = Admin_Seo_Popup::get_instance();

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
					'editor_type'        => 'divi',
					'post_type'          => get_post_type( $post_id ) ? get_post_type( $post_id ) : '',
					'is_taxonomy'        => false,
					'description_length' => Get::description_length(),
					'title_length'       => Get::title_length(),
					'keyword_checks'     => $seo_popup->keyword_checks(),
					'page_checks'        => $seo_popup->page_checks(),
					'image_seo'          => Image_Seo::get_instance()->status(),
					'is_frontend'        => true,
					'post_id'            => $post_id,
					'link'               => get_the_permalink( $post_id ),
				],
			]
		);

		$seo_popup->build_assets_operations(
			'front-end-meta-box',
			[
				'hook'        => 'front-end-meta-box',
				'object_name' => 'front_end_meta_box',
				'data'        => [],
			]
		);

		// Admin bar node is registered by maybe_add_divi_admin_bar_menu() which is
		// hooked on admin_bar_menu in the constructor and covers both FVB (frontend)
		// and all admin editing contexts.

		// Enqueue the Divi page bar integration script (button + status indicator + tooltip).
		$this->enqueue_divi_bar_script();
	}

	/**
	 * Enqueue surerank_globals for Divi Frontend Visual Builder.
	 *
	 * Fires on wp_enqueue_scripts at priority 999 (after Divi's own scripts).
	 * Hooked separately from enqueue_for_visual_builder() so that the jquery
	 * handle is reliably in the queue before wp_localize_script() is called —
	 * mirrors the pattern used by the Bricks integration.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function enqueue_globals_for_visual_builder(): void {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['app_window'] ) ) {
			return;
		}

		Dashboard::get_instance()->site_seo_check_enqueue_scripts();
	}

	/**
	 * Add Divi-specific variables to surerank_globals localization.
	 *
	 * Mirrors Bricks::add_localization_vars() so that JS code can detect
	 * the active builder via window.surerank_globals.is_divi.
	 *
	 * @since 1.6.0
	 * @param array<string, mixed> $vars Existing localization variables.
	 * @return array<string, mixed>
	 */
	public function add_localization_vars( array $vars ): array {
		return array_merge( $vars, [ 'is_divi' => true ] );
	}

	/**
	 * Override editor type to 'divi' when Divi Backend Builder is active.
	 *
	 * Hooked into surerank_detect_editor_type, which is applied at the end of
	 * Admin\Seo_Popup::detect_editor_type() so that the JS skips the classic
	 * .wrap > h1 button injection and uses the admin bar button instead.
	 *
	 * @since 1.6.0
	 * @param string          $editor_type Current editor type.
	 * @param \WP_Screen|null $screen      Current screen object.
	 * @return string
	 */
	public function filter_bfb_editor_type( string $editor_type, $screen ): string {
		if ( function_exists( 'et_builder_bfb_enabled' ) && et_builder_bfb_enabled() ) {
			return 'divi';
		}

		return $editor_type;
	}

	/**
	 * Attach an inline admin-bar click handler for any Divi admin editing context.
	 *
	 * Fires late on admin_enqueue_scripts (priority 99999) so surerank-seo-popup
	 * is guaranteed to be enqueued before we append to it. Works for block editor,
	 * classic editor, and BFB — wherever seo-popup scripts are already loaded.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function maybe_add_divi_admin_click_handler(): void {
		if ( ! wp_script_is( 'surerank-seo-popup', 'enqueued' ) ) {
			return;
		}

		wp_add_inline_script(
			'surerank-seo-popup',
			"document.addEventListener('click',function(e){if(e.target.closest('#wp-admin-bar-surerank-meta-box')){e.preventDefault();if(window.wp&&window.wp.data){window.wp.data.dispatch('surerank').updateModalState(true);} } });"
		);
	}

	/**
	 * Render admin bar node for any Divi editing context (admin or FVB frontend).
	 *
	 * Delegates to Admin\Seo_Popup::add_admin_bar_menu(), which self-guards:
	 * it only adds the node when surerank-seo-popup is already enqueued.
	 * This makes it safe to hook unconditionally on admin_bar_menu.
	 *
	 * @since 1.6.0
	 * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 * @return void
	 */
	public function maybe_add_divi_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ): void {
		Admin_Seo_Popup::get_instance()->add_admin_bar_menu( $wp_admin_bar );
	}

	/**
	 * Render Divi 5 content using WordPress core's do_blocks().
	 *
	 * Divi 5 registers ALL its modules as WordPress block types with proper
	 * render_callback functions, and this registration happens for REST API
	 * requests (see includes/builder-5/server/bootstrap.php). Calling
	 * do_blocks() invokes Divi's own server-side render callbacks for every
	 * module type with no custom per-module code required on our side.
	 *
	 * @param string $content Raw post_content with divi/* block comments.
	 * @return string Fully rendered HTML from Divi's own render callbacks.
	 */
	private function process_divi5( string $content ): string {
		if ( ! function_exists( 'do_blocks' ) ) {
			return $content;
		}

		$html = do_blocks( $content );

		return $html ? $html : $content;
	}

	/**
	 * Render Divi 4 content using Divi's own render function.
	 *
	 * The et_builder_render_layout() applies Divi's full render filter chain
	 * (do_blocks at priority 9, do_shortcode at priority 11), producing
	 * fully rendered HTML for every Divi 4 module type.
	 *
	 * @param string $content Raw Divi 4 post_content with et_pb_* shortcodes.
	 * @return string Rendered HTML.
	 */
	private function process_divi4( string $content ): string {
		if ( ! function_exists( 'et_builder_render_layout' ) ) {
			return $content;
		}

		return et_builder_render_layout( $content );
	}

	/**
	 * Enqueue the bundled Divi page bar integration script.
	 *
	 * Registers and enqueues the webpack-built divi module which injects a
	 * SureRank button (with status indicator and tooltip) into the Divi VB
	 * page bar. Uses the auto-generated asset file for dependencies and version.
	 *
	 * @since 1.6.5
	 * @return void
	 */
	private function enqueue_divi_bar_script(): void {
		$asset_path = SURERANK_DIR . 'build/divi/index.asset.php';

		if ( ! file_exists( $asset_path ) ) {
			return;
		}

		$asset_info = include $asset_path;

		// Ensure surerank-seo-popup is a dependency so the store is available.
		$asset_info['dependencies'][] = 'surerank-seo-popup';
		$asset_info['dependencies']   = array_unique( $asset_info['dependencies'] );

		wp_register_script(
			'surerank-divi',
			SURERANK_URL . 'build/divi/index.js',
			$asset_info['dependencies'],
			$asset_info['version'],
			false
		);
		wp_enqueue_style(
			'surerank-divi',
			SURERANK_URL . 'build/divi/style.css',
			[],
			$asset_info['version']
		);
		wp_style_add_data( 'surerank-divi', 'rtl', 'replace' );
		wp_enqueue_script( 'surerank-divi' );
	}
}
