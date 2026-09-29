<?php
/**
 * Post Popup
 *
 * @since 1.0.0
 * @package surerank
 */

namespace SureRank\Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Frontend\Crawl_Optimization;
use SureRank\Inc\Frontend\Image_Seo;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Functions\Update;
use SureRank\Inc\Traits\Enqueue;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Post Popup
 *
 * @method void wp_enqueue_scripts()
 * @since 1.0.0
 */
class Seo_Popup {

	use Enqueue;
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function __construct() {
		if ( ! apply_filters( 'surerank_content_setting_access', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		$this->enqueue_scripts_admin();
		add_action( 'category_term_edit_form_top', [ $this, 'add_meta_box_trigger' ] );
		add_action( 'created_category', [ $this, 'update_category_seo_values' ] );
		add_action( 'edited_category', [ $this, 'update_category_seo_values' ] );
		// For enqueue scripts on the frontend.
		// Uncomment this line when the frontend meta box style issue is resolved.
		// add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue_scripts' ] );.
	}

	/**
	 * Add tags
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function add_meta_box_trigger() {
		echo '<span id="seo-popup" class="surerank-root"></span>';
	}

	/**
	 * Enqueue SEO metabox front-end scripts
	 *
	 * @since 1.6.2
	 * @return void
	 */
	public function frontend_enqueue_scripts() {
		// Check if the user is logged in and has the necessary capabilities.
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// Early return if it's a preview of editor or customizer.
		if ( is_admin() ||
			is_customize_preview() ||
			is_preview() ||
			! is_admin_bar_showing() ) {
			return;
		}

		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 100 );

		do_action( 'surerank_seo_popup_frontend_enqueue_scripts' );

		wp_enqueue_media();
		Dashboard::get_instance()->site_seo_check_enqueue_scripts();

		$context_data = $this->get_frontend_context_data();

		if ( ! $context_data ) {
			return;
		}

		$this->enqueue_assets( 'elementor', $context_data );

		$this->build_assets_operations(
			'front-end-meta-box',
			[
				'hook'        => 'front-end-meta-box',
				'object_name' => 'front_end_meta_box',
				'data'        => [],
			]
		);
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_media();

		$screen      = $this->get_current_screen_safe();
		$editor_type = self::detect_editor_type( $screen );

		if ( ! self::should_enqueue_scripts( $editor_type, $screen ) ) {
			return;
		}

		$context_data = $this->get_context_data( $editor_type, $screen );
		$this->enqueue_assets( $editor_type, $context_data );
	}

	/**
	 * Add admin bar menu
	 *
	 * @since 1.6.2
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 *
	 * @return void
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		if ( ! wp_script_is( $this->enqueue_prefix . '-seo-popup', 'enqueued' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			[
				'id'    => 'surerank-meta-box',
				'title' => '<span class="ab-icon" style="margin-top: 2px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.5537 1.5C17.8453 1.5 21.3251 4.97895 21.3252 9.27051C21.3252 12.347 19.5368 15.0056 16.9434 16.2646H21.3252V22.5H18.0889C14.9086 22.5 12.2861 20.1186 11.9033 17.042H11.9014L11.9033 13.7852C14.8283 13.7661 17.0342 11.3894 17.0342 8.45996V6.0293C14.137 6.02947 11.6948 7.97682 10.9443 10.6338C10.1605 9.53345 8.87383 8.8165 7.41992 8.81641H6.38086V9.85352H6.38379C6.44515 12.0356 8.23375 13.786 10.4307 13.7861H10.7061L10.6934 17.042H10.6865C10.2943 20.1082 7.67678 22.4785 4.50391 22.4785H2.6748V1.5H13.5537Z" fill="currentColor"/></svg></span><span class="ab-label">' . esc_html__( 'SureRank Meta Box', 'surerank' ) . '</span>',
				'href'  => '#',
				'meta'  => [
					'class'   => 'surerank-meta-box-trigger',
					'title'   => esc_html__( 'Open SureRank Meta Box', 'surerank' ),
					'onclick' => 'return false;',
				],
			]
		);
	}

	/**
	 * Update seo values
	 *
	 * @param int $term_id Post ID.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function update_category_seo_values( $term_id ) {
		// Validate post ID.
		if ( empty( $term_id ) || ! is_int( $term_id ) ) {
			return;
		}

		// Update post seo values.
		$result = Update::term_meta( $term_id, [], [] );

		if ( is_wp_error( $result ) ) {
			return;
		}

		do_action( 'surerank_after_update_category_seo_values', $term_id );
	}

	/**
	 * Get keyword checks configuration
	 *
	 * @since 1.0.0
	 * @return array
	 */
	/**
	 * Get keyword checks configuration
	 *
	 * @since 1.0.0
	 * @return array<string>
	 */
	public function keyword_checks() {
		return [
			'keyword_in_title',
			'keyword_in_description',
			'keyword_in_url',
			'keyword_in_content',
		];
	}

	/**
	 * Get page checks configuration
	 *
	 * @since 1.0.0
	 * @return array<string>
	 */
	public function page_checks() {
		return [
			'h2_subheadings',
			'image_alt_text',
			'media_present',
			'links_present',
			'url_length',
			'search_engine_title',
			'search_engine_description',
			'canonical_url',
			'all_links',
			'open_graph_tags',
			'broken_links',
		];
	}

	/**
	 * Detect the current editor type.
	 *
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return string Editor type.
	 */
	public static function detect_editor_type( $screen ): string {
		if ( class_exists( \Elementor\Plugin::class ) && \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			return 'elementor';
		}

		if ( function_exists( 'bricks_is_builder_main' ) && bricks_is_builder_main() ) {
			return 'bricks';
		}

		// Listing pages (post/taxonomy list tables) use a dedicated context.
		if ( $screen && in_array( $screen->base, [ 'edit', 'edit-tags' ], true ) ) {
			return 'listing';
		}

		// Allow integrations (e.g. Divi BFB) to override before the block-editor check.
		$filtered = apply_filters( 'surerank_detect_editor_type', 'classic', $screen );
		if ( 'classic' !== $filtered ) {
			return $filtered;
		}

		if ( $screen && $screen->is_block_editor ) {
			return 'block';
		}

		return 'classic';
	}

	/**
	 * Check if scripts should be enqueued.
	 *
	 * @param string          $editor_type Editor type.
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return bool True if scripts should be enqueued.
	 */
	public static function should_enqueue_scripts( string $editor_type, $screen ): bool {
		if ( $editor_type === 'bricks' ) {
			return true;
		}

		if ( ! $screen || empty( $screen->base ) || ! in_array( $screen->base, [ 'post', 'term', 'edit', 'edit-tags' ], true ) ) {
			return false;
		}

		if ( 'post' === $screen->base && ! empty( $screen->post_type ) ) {
			if ( ! Seo_Bar::display_metabox( $screen->post_type, 'wp_posts' ) ) {
				return false;
			}
		}

		if ( 'term' === $screen->base && ! empty( $screen->taxonomy ) ) {
			if ( ! Seo_Bar::display_metabox( $screen->taxonomy, 'wp_terms' ) ) {
				return false;
			}
		}

		if ( 'edit' === $screen->base && ! empty( $screen->post_type ) ) {
			if ( ! Seo_Bar::display_metabox( $screen->post_type, 'wp_posts' ) ) {
				return false;
			}
		}

		if ( 'edit-tags' === $screen->base && ! empty( $screen->taxonomy ) ) {
			if ( ! Seo_Bar::display_metabox( $screen->taxonomy, 'wp_terms' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get current screen safely.
	 *
	 * @return \WP_Screen|null
	 */
	private function get_current_screen_safe() {
		return function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	}

	/**
	 * Get context data for the current page.
	 *
	 * @param string          $editor_type Editor type.
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return array{post_data: array<string, mixed>, term_data: array<string, mixed>, post_type: string, is_taxonomy: bool, is_frontend?: bool} Context data.
	 */
	private function get_context_data( string $editor_type, $screen ): array {
		$post_data = $this->get_post_data( $editor_type, $screen );
		$term_data = $this->get_term_data( $screen );

		return [
			'post_data'   => $post_data,
			'term_data'   => $term_data,
			'post_type'   => $this->get_post_type( $editor_type, $screen ),
			'is_taxonomy' => $this->is_taxonomy( $editor_type, $screen ),
		];
	}

	/**
	 * Get post data if on post edit screen.
	 *
	 * @param string          $editor_type Editor type.
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return array<string, mixed> Post data.
	 */
	private function get_post_data( string $editor_type, $screen ): array {
		if ( ( $screen && 'post' === $screen->base ) || $editor_type === 'bricks' ) {
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return [];
			}
			return [
				'post_id'     => $post_id,
				'editor_type' => $editor_type,
				'link'        => get_the_permalink( $post_id ),
			];
		}

		return [];
	}

	/**
	 * Get term data if on term edit screen.
	 *
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return array<string, mixed> Term data.
	 */
	private function get_term_data( $screen ): array {
		if ( ! $screen || 'term' !== $screen->base ) {
			return [];
		}

		global $tag_ID;

		$final_link = get_term_link( (int) $tag_ID );
		if ( is_wp_error( $final_link ) ) {
			return [];
		}

		$final_link = $this->process_category_link( $final_link, $tag_ID, $screen );

		return [
			'term_id' => $tag_ID,
			'link'    => $final_link,
		];
	}

	/**
	 * Process category link if needed.
	 *
	 * @param string          $link Term link.
	 * @param int             $tag_ID Term ID.
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return string Processed link.
	 */
	private function process_category_link( string $link, int $tag_ID, $screen ): string {
		if ( $screen && 'category' === $screen->taxonomy && apply_filters( 'surerank_remove_category_base', false ) ) {
			$term = get_term( $tag_ID );
			if ( $term && ! is_wp_error( $term ) ) {
				return Crawl_Optimization::get_instance()->remove_category_base_from_links( $link, $term, $screen->taxonomy );
			}
		}

		return $link;
	}

	/**
	 * Get post type for current context.
	 *
	 * @param string          $editor_type Editor type.
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return string Post type.
	 */
	private function get_post_type( string $editor_type, $screen ): string {
		if ( $editor_type === 'bricks' ) {
			$post_id   = get_the_ID();
			$post_type = $post_id ? get_post_type( $post_id ) : false;
			return $post_type !== false ? $post_type : '';
		}

		if ( ! $screen ) {
			return '';
		}

		return ! empty( $screen->taxonomy ) ? $screen->taxonomy : $screen->post_type;
	}

	/**
	 * Check if current context is taxonomy.
	 *
	 * @param string          $editor_type Editor type.
	 * @param \WP_Screen|null $screen Current screen object.
	 * @return bool True if taxonomy context.
	 */
	private function is_taxonomy( string $editor_type, $screen ): bool {
		if ( $editor_type === 'bricks' ) {
			return false;
		}

		return $screen && ! empty( $screen->taxonomy );
	}

	/**
	 * Enqueue assets for SEO popup.
	 *
	 * @param string                                                                                                                            $editor_type Editor type.
	 * @param array{post_data: array<string, mixed>, term_data: array<string, mixed>, post_type: string, is_taxonomy: bool, is_frontend?: bool} $context_data Context data.
	 * @return void
	 */
	private function enqueue_assets( string $editor_type, array $context_data ): void {
		$this->enqueue_vendor_and_common_assets();

		$this->build_assets_operations(
			'seo-popup',
			[
				'hook'        => 'seo-popup',
				'object_name' => 'seo_popup',
				'data'        => array_merge(
					[
						'admin_assets_url'   => SURERANK_URL . 'inc/admin/assets',
						'site_icon_url'      => get_site_icon_url( 16 ),
						'editor_type'        => $editor_type,
						'post_type'          => $context_data['post_type'],
						'is_taxonomy'        => $context_data['is_taxonomy'],
						'description_length' => Get::description_length(),
						'title_length'       => Get::title_length(),
						'keyword_checks'     => $this->keyword_checks(),
						'page_checks'        => $this->page_checks(),
						'image_seo'          => Image_Seo::get_instance()->status(),
						'is_frontend'        => $context_data['is_frontend'] ?? false,
					],
					$context_data['post_data'],
					$context_data['term_data']
				),
			]
		);
	}

	/**
	 * Get frontend context data.
	 *
	 * @return array{post_data: array<string, mixed>, term_data: array<string, mixed>, post_type: string, is_taxonomy: bool, is_frontend: bool}|false Context data or false if invalid.
	 */
	private function get_frontend_context_data() {
		$post_data   = [];
		$term_data   = [];
		$post_type   = '';
		$is_taxonomy = false;

		if ( is_singular() ) {
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return false;
			}
			$post_type = get_post_type( $post_id );
			if ( ! $post_type ) {
				return false;
			}
			$post_data = [
				'post_id'     => $post_id,
				'editor_type' => apply_filters( 'surerank_frontend_editor_type', 'classic' ),
				'link'        => get_the_permalink( $post_id ),
			];
		} elseif ( is_tax() || is_tag() || is_category() ) {
			$object = get_queried_object();
			if ( ! $object instanceof \WP_Term ) {
				return false;
			}
			$term_link = get_term_link( $object );
			if ( is_wp_error( $term_link ) ) {
				return false;
			}
			$term_data   = [
				'term_id' => $object->term_id,
				'link'    => $term_link,
			];
			$post_type   = $object->taxonomy;
			$is_taxonomy = true;
		} else {
			return false;
		}

		return [
			'post_data'   => $post_data,
			'term_data'   => $term_data,
			'post_type'   => $post_type,
			'is_taxonomy' => $is_taxonomy,
			'is_frontend' => true,
		];
	}
}
