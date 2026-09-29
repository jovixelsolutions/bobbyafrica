<?php
/**
 * Astra Sites Utlis
 *
 * @since  1.0.0
 * @package Astra Sites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Astra_Sites_Utils' ) ) :

	/**
	 * Astra_Sites_Utils
	 */
	class Astra_Sites_Utils {

		/**
		 * Instance.
		 *
		 * @since  4.4.48
		 * @var null $instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since  4.4.48
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since  4.4.48
		 */
		private function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $hook Current hook name.
		 * @return void
		 */
		public function admin_enqueue( $hook = '' ) {

			global $wp_version;
			if ( version_compare( $wp_version, '6.3', '>' ) ) {
				wp_enqueue_script( 'astra-sites-command-palette', ASTRA_SITES_URI . 'inc/assets/js/command-palette.js', array( 'wp-data', 'wp-element', 'wp-commands', 'wp-dom-ready' ), ASTRA_SITES_VER, true );
				wp_localize_script(
					'astra-sites-command-palette',
					'stCommandsPalette',
					array(
						'imageDir'       => INTELLIGENT_TEMPLATES_URI . 'assets/images/',
						'onboardingUrl'  => admin_url( '/themes.php' ) . '?page=starter-templates',
						'customCommands' => $this->get_custom_commands(),
					)
				);
			}
		}

		/**
		 * Get custom commands.
		 *
		 * @since  4.4.48
		 *
		 * @return array
		 */
		public function get_custom_commands() {
			$common_search_labels = __( 'Starter, Starter Templates, Templates, Themes, Astra, Onboarding, Import, Export, AI builder', 'astra-sites' );
			$commands = array(
				array(
					'name'        => 'starter-templates',
					'label'       => __( 'Starter Templates', 'astra-sites' ),
					'searchLabel' => $common_search_labels,
					'url'         => admin_url( '/themes.php' ) . '?page=starter-templates',
				),
				array(
					'name'        => 'starter-templates-page-builder',
					'label'       => __( 'Starter Templates: Choose Page Builder', 'astra-sites' ),
					'searchLabel' => $common_search_labels . ', ' . __( 'Page Builder', 'astra-sites' ),
					'url'         => admin_url( '/themes.php' ) . '?page=starter-templates&ci=1',
				),
				array(
					'name'        => 'starter-templates-gutenberg',
					'label'       => __( 'Starter Templates: Gutenberg Templates', 'astra-sites' ),
					'searchLabel' => $common_search_labels . ' ' . __( 'Block, Block Editor, Spectra, Gutenberg', 'astra-sites' ),
					'url'         => admin_url( '/themes.php' ) . '?page=starter-templates&builder=gutenberg',
				),
			);

			if ( Astra_Sites::should_show_ai_builder() ) {
				$commands[] = array(
					'name'        => 'starter-templates-ai-builder',
					'label'       => __( 'Starter Templates: AI Builder', 'astra-sites' ),
					'searchLabel' => $common_search_labels . ', ' . __( 'AI Template', 'astra-sites' ),
					'url'         => admin_url( '/themes.php' ) . '?page=ai-builder',
				);
			}

			$is_beaver_builder_disabled = get_option( 'st-beaver-builder-flag' ) || ! Intelligent_Starter_Templates_Loader::is_legacy_beaver_builder_enabled();
			$is_elementor_disabled      = get_option( 'st-elementor-builder-flag' );

			if ( ! $is_beaver_builder_disabled ) {
				$commands[] = array(
					'name'        => 'starter-templates-beaver-builder',
					'label'       => __( 'Starter Templates: Beaver Builder Templates', 'astra-sites' ),
					'searchLabel' => $common_search_labels . ', ' . __( 'Beaver Builder', 'astra-sites' ),
					'url'         => admin_url( '/themes.php' ) . '?page=starter-templates&builder=beaver-builder',
				);
			}

			if ( ! $is_elementor_disabled ) {
				$commands[] = array(
					'name'        => 'starter-templates-elementor',
					'label'       => __( 'Starter Templates: Elementor Templates', 'astra-sites' ),
					'searchLabel' => $common_search_labels . ', ' . __( 'Elementor', 'astra-sites' ),
					'url'         => admin_url( '/themes.php' ) . '?page=starter-templates&builder=elementor',
				);
			}
			return $commands;
		}

		/**
		 * Third party cache plugin clear cache.
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public static function third_party_cache_plugins_clear_cache() {
			// Clear LiteSpeed cache.
			if ( class_exists( '\LiteSpeed\Purge' ) ) {
				\LiteSpeed\Purge::purge_all();
			}

			// Clear cloudways cache.
			self::clear_cloudways_cache();
		}

		/**
		 * This function helps to purge all cache in clodways envirnoment.
		 * In presence of Breeze plugin (https://wordpress.org/plugins/breeze/)
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public static function clear_cloudways_cache() {
			if ( ! class_exists( 'Breeze_Configuration' ) || ! class_exists( 'Breeze_CloudFlare_Helper' ) || ! class_exists( 'Breeze_Admin' ) ) {
				return;
			}

			// clear varnish cache.
			$admin = new Breeze_Admin();
			$admin->breeze_clear_varnish();

			// clear static cache.
			Breeze_Configuration::breeze_clean_cache();
			Breeze_CloudFlare_Helper::reset_all_cache();
		}
		
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Sites_Utils::get_instance();

endif;
