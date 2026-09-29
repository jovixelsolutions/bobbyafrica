<?php
/**
 * Loader.
 *
 * @package surerank
 * @since 0.0.1
 */

namespace SureRank;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Admin\Admin_Notice;
use SureRank\Inc\Admin\Attachment;
use SureRank\Inc\Admin\BulkActions;
use SureRank\Inc\Admin\BulkEdit;
use SureRank\Inc\Admin\Dashboard;
use SureRank\Inc\Admin\Onboarding;
use SureRank\Inc\Admin\Rest_Site_Health;
use SureRank\Inc\Admin\Search_Console_Widget;
use SureRank\Inc\Admin\Seo_Bar;
use SureRank\Inc\Admin\Seo_Popup;
use SureRank\Inc\Admin\Site_Health;
use SureRank\Inc\Admin\Sync;
use SureRank\Inc\Admin\Update_Timestamp;
use SureRank\Inc\Ajax\Ajax;
use SureRank\Inc\Ajax\Save_Endpoints;
use SureRank\Inc\Analytics\Analytics;
use SureRank\Inc\Analyzer\PostAnalyzer;
use SureRank\Inc\Analyzer\TermAnalyzer;
use SureRank\Inc\API\Analyzer;
use SureRank\Inc\API\Api_Init;
use SureRank\Inc\BatchProcess\Process;
use SureRank\Inc\Cli\Cli;
use SureRank\Inc\Frontend\Archives;
use SureRank\Inc\Frontend\Canonical;
use SureRank\Inc\Frontend\Common;
use SureRank\Inc\Frontend\Content_Seo;
use SureRank\Inc\Frontend\Crawl_Optimization;
use SureRank\Inc\Frontend\Facebook;
use SureRank\Inc\Frontend\Feed;
use SureRank\Inc\Frontend\Meta_Data;
use SureRank\Inc\Frontend\Meta_Tag_Injection;
use SureRank\Inc\Frontend\Product;
use SureRank\Inc\Frontend\Robots;
use SureRank\Inc\Frontend\Seo_Popup as Seo_Popup_Frontend;
use SureRank\Inc\Frontend\Single;
use SureRank\Inc\Frontend\Special_Page;
use SureRank\Inc\Frontend\Taxonomy;
use SureRank\Inc\Frontend\Title;
use SureRank\Inc\Frontend\Twitter;
use SureRank\Inc\Functions\Compat;
use SureRank\Inc\Functions\Cron;
use SureRank\Inc\Functions\Defaults;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Functions\Helper;
use SureRank\Inc\Functions\Update;
use SureRank\Inc\GoogleSearchConsole\Auth;
use SureRank\Inc\Lib\Surerank_Nps_Survey;
use SureRank\Inc\Modules\Ai_Auth\Init as Ai_Auth_Init;
use SureRank\Inc\Modules\Content_Generation\Init as Content_Generation_Init;
use SureRank\Inc\Modules\EmailReports\Init as EmailReports_Init;
use SureRank\Inc\Modules\Fix_Seo_Checks\Init as Fix_Seo_Checks_Init;
use SureRank\Inc\Modules\Knowledge_Graph\Init as Knowledge_Graph_Init;
use SureRank\Inc\Modules\Nudges\Init as Nudges_Init;
use SureRank\Inc\Nps_Notice;
use SureRank\Inc\Routes;
use SureRank\Inc\Schema\Schemas;
use SureRank\Inc\Sitemap\Checksum;
use SureRank\Inc\Sitemap\Xml_Sitemap;
use SureRank\Inc\ThirdPartyIntegrations\Init as Integrations_Init;

/**
 * Plugin_Loader
 *
 * @since 1.0.0
 */
class Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );

		add_action( 'plugins_loaded', [ $this, 'load_routes' ], 10 );

		add_action( 'init', [ $this, 'load_textdomain' ], 10 );
		add_action( 'init', [ $this, 'load_nps' ], 99 );
		add_action( 'init', [ $this, 'setup' ], 999 );
		add_action( 'init', [ $this, 'flush_rules' ], 999 );

		register_activation_hook( SURERANK_FILE, [ $this, 'activation' ] );
		register_deactivation_hook( SURERANK_FILE, [ $this, 'deactivation' ] );

		add_filter( 'plugin_row_meta', [ $this, 'add_meta_links' ], 10, 2 );

		add_filter( 'plugin_action_links', [ $this, 'add_settings_link' ], 10, 2 );
		add_filter( 'plugin_action_links_' . SURERANK_BASE, [ $this, 'add_pro_nudge_link' ] );

		add_filter( 'body_class', [ $this, 'add_body_class' ] );

		// Map custom SureRank capabilities to primitive WordPress capabilities.
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );

		// Remove this after the translation error is fixed.
		add_filter( 'doing_it_wrong_trigger_error', [ $this, 'suppress_translation_error' ], 10, 4 );

		// Prevent Query Monitor from collecting the error.
		add_action( 'doing_it_wrong_run', [ $this, 'prevent_qm_collection' ], 5, 3 );
	}

	/**
	 * Enqueue required classes after plugins loaded.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function setup(): void {
		do_action( 'surerank_before_load_components' );

		$this->load_core_components();
		$this->load_environment_components();

		do_action( 'surerank_after_load_components' );
	}

	/**
	 * Prevent Query Monitor from collecting textdomain errors.
	 *
	 * @param string $function_name The function that was called.
	 * @param string $message The error message.
	 * @param string $version The version.
	 * @return void
	 * @since 1.7.3
	 */
	public function prevent_qm_collection( $function_name, $message, $version ): void {
		if ( $function_name === '_load_textdomain_just_in_time' && strpos( $message, 'surerank' ) !== false ) {
			// Remove Query Monitor's action temporarily.
			if ( class_exists( '\QM_Collectors' ) ) {
				$collector = \QM_Collectors::get( 'doing_it_wrong' );
				$callback  = is_object( $collector ) ? [ $collector, 'action_doing_it_wrong_run' ] : null;
				if ( is_callable( $callback ) ) {
					remove_action( 'doing_it_wrong_run', $callback, 10 );

					// Re-add it after this specific error.
					add_action(
						'shutdown',
						static function() use ( $callback ): void {
							if ( ! has_action( 'doing_it_wrong_run', $callback ) ) {
								add_action( 'doing_it_wrong_run', $callback, 10, 3 );
							}
						},
						-1
					);
				}
			}
		}
	}

	/**
	 * Suppress translation error.
	 *
	 * @param bool   $status       Status.
	 * @param string $function_name Function name.
	 * @param string $message      Message.
	 * @param string $version      Version.
	 *
	 * @return bool
	 * @since 1.7.3
	 */
	public function suppress_translation_error( $status, $function_name, $message, $version ) {
		if ( $function_name === '_load_textdomain_just_in_time' && strpos( $message, 'surerank' ) !== false ) {
			return false;
		}
		return $status;
	}

	/**
	 * Load routes.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function load_routes() {
		do_action( 'surerank_before_load_routes' );

		Routes::get_instance();
		Analytics::get_instance();
		Admin_Notice::get_instance();

		do_action( 'surerank_after_load_routes' );
	}

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 * @since 1.0.0
	 * @return void
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$class_to_load = $class;

		$filename = preg_replace(
			[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
			[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
			$class_to_load
		);

		if ( is_string( $filename ) ) {
			$filename = strtolower( $filename );

			$file = SURERANK_DIR . $filename . '.php';

			// if the file readable, include it.
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	}

	/**
	 * Load Plugin Text Domain.
	 * This will load the translation textdomain depending on the file priorities.
	 *      1. Global Languages /wp-content/languages/surerank/ folder
	 *      2. Local directory /wp-content/plugins/surerank/languages/ folder
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		// Default languages directory.
		$lang_dir = SURERANK_DIR . 'languages/';

		/**
		 * Filters the languages directory path to use for plugin.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'surerank_languages_directory', $lang_dir );

		$get_locale = get_user_locale();

		$locale = apply_filters( 'plugin_locale', $get_locale, 'surerank' ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- wordpress hook
		$mofile = sprintf( '%1$s-%2$s.mo', 'surerank', $locale );

		// Setup paths to current locale file.
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
		$mofile_local  = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/surerank/ folder.
			load_textdomain( 'surerank', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/surerank/languages/ folder.
			load_textdomain( 'surerank', $mofile_local );
		}
	}

	/**
	 * Activation Hook
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activation() {
		Update::option( 'surerank_flush_required', 1 );
		Update::option( 'surerank_redirect_on_activation', 'yes' );
		Cron::get_instance()->schedule_sitemap_generation();
	}

	/**
	 * Deactivation Hook
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function deactivation() {
		Update::option( 'surerank_flush_required', 1 );
		Cron::get_instance()->unschedule_sitemap_generation();
		Checksum::get_instance()->clear_checksum();

		delete_option( 'surerank_cron_test_ok' );
	}

	/**
	 * Flush if settings is updated
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function flush_rules() {
		if ( Get::option( 'surerank_flush_required' ) ) {
			Helper::flush();
			delete_option( 'surerank_flush_required' );
		}
	}

	/**
	 * Add meta links to the plugin row (under description).
	 *
	 * @param array<int,string> $links Array of plugin meta links.
	 * @param string            $file Plugin file path.
	 * @return array<int,string> Modified plugin meta links.
	 */
	public function add_meta_links( array $links, string $file ): array {
		if ( SURERANK_BASE === $file ) {
			$stars = '';
			for ( $indx = 0; $indx < 5; $indx++ ) {
				$stars .= '<span class="dashicons dashicons-star-filled" style="color: #ffb900; font-size: 16px; width: 16px; height: 16px; line-height: 1.2;" aria-hidden="true"></span>';
			}
			$links[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s" role="button">%s</a>',
				esc_url( 'https://wordpress.org/support/plugin/surerank/reviews/#new-post' ),
				esc_attr__( 'Rate our plugin', 'surerank' ),
				$stars
			);
		}
		return $links;
	}

	/**
	 * Add Settings link to plugin action links.
	 *
	 * @param array<string,string> $links Array of plugin action links.
	 * @param string               $file Plugin file path.
	 * @return array<string,string> Modified plugin action links.
	 * @since 1.6.3
	 */
	public function add_settings_link( array $links, string $file ): array {
		if ( SURERANK_BASE === $file ) {
			ob_start();
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=surerank' ) ); ?>">
				<?php echo esc_html__( 'Settings', 'surerank' ); ?>
			</a>
			<?php
			$settings_link_html = ob_get_clean();

			$plugin_links = apply_filters(
				'surerank_plugin_action_links',
				[
					'surerank_settings' => $settings_link_html,
				]
			);

			$links = array_merge( $plugin_links, $links );
		}
		return $links;
	}

	/**
	 * Add Pro nudge link to plugin action links.
	 *
	 * @param array<string,string> $links Array of plugin action links.
	 * @return array<string,string> Modified plugin action links.
	 * @since 1.6.3
	 */
	public function add_pro_nudge_link( array $links ): array {
		// Check if Pro plugin is installed using WordPress function.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins   = get_plugins();
		$pro_installed = false;

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			if ( strpos( $plugin_path, '/surerank-pro.php' ) !== false ) {
				$pro_installed = true;
				break;
			}
		}

		if ( ! $pro_installed ) {
			$pricing_url = add_query_arg(
				[
					'utm_source'   => 'surerank-free',
					'utm_medium'   => 'plugin-list',
					'utm_campaign' => 'plugin-screen',
				],
				'https://surerank.com/pricing/'
			);

			ob_start();
			?>
			<a href="<?php echo esc_url( $pricing_url ); ?>" target="_blank" rel="noreferrer" style="color: #4330D2; font-weight: 700;">
				<?php echo esc_html__( 'Get SureRank Pro', 'surerank' ); ?>
			</a>
			<?php
			$link_html = ob_get_clean();
			if ( false !== $link_html ) {
				$links['surerank_pro'] = trim( $link_html );
			}
		}

		return $links;
	}

	/**
	 * Load NPS Survey if conditions are met.
	 */
	public function load_nps(): void {
		if ( $this->should_load_nps_survey() ) {
			Surerank_Nps_Survey::get_instance();
			Nps_Notice::get_instance();
		}
	}

	/**
	 * Add body class - Assign version class for reference.
	 *
	 * @param array<int, string> $classes body classes.
	 * @since 1.6.2
	 * @return array<int, string>
	 */
	public function add_body_class( $classes ) {
		$classes[] = 'surerank-' . SURERANK_VERSION;
		return $classes;
	}

	/**
	 * Map custom SureRank capabilities to primitive WordPress capabilities.
	 *
	 * This maps the custom capabilities used in SureRank to standard WordPress
	 * capabilities so that role management works correctly. The Pro plugin can
	 * override this to provide more granular control.
	 *
	 * @since 1.7.2
	 * @param array<string> $caps    Primitive capabilities required by the user.
	 * @param string        $cap     Capability being checked.
	 * @param int           $user_id User ID.
	 * @param array<int>    $args    Additional arguments.
	 * @return array<string> Mapped capabilities.
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		// Map custom SureRank capabilities to manage_options for free version.
		// Pro plugin can override this filter for proper role management.
		$surerank_caps = [
			'surerank_content_setting',
			'surerank_global_setting',
		];

		if ( in_array( $cap, $surerank_caps, true ) ) {
			// Map to manage_options for the free version.
			return [ 'manage_options' ];
		}

		return $caps;
	}

	/**
	 * Load core components that are always needed.
	 *
	 * @return void
	 */
	private function load_core_components(): void {
		$core_components = [
			Defaults::class,
			Schemas::class,
			Crawl_Optimization::class,
			Api_Init::class,
			Compat::class,
			Cron::class,
			Checksum::class,
			Integrations_Init::class,
			Knowledge_Graph_Init::class,
			Attachment::class,
			Analyzer::class,
			PostAnalyzer::class,
			TermAnalyzer::class,
			Auth::class,
			Sync::class,
			Ai_Auth_Init::class,
			Content_Generation_Init::class,
			EmailReports_Init::class,
			Fix_Seo_Checks_Init::class,
			Knowledge_Graph_Init::class,
			Nudges_Init::class,
			Process::class,
			Cli::class,
		];

		$this->load_components( $core_components );

		// Seo_Bar needed in admin page views and AJAX (for inline-save column rendering).
		if ( is_admin() ) {
			$this->load_components( [ Seo_Bar::class ] );
		}
	}

	/**
	 * Load environment-specific components.
	 *
	 * @return void
	 */
	private function load_environment_components(): void {
		if ( is_admin() ) {
			$this->load_admin_components();
		} else {
			$this->load_frontend_components();
		}
	}

	/**
	 * Load admin-specific components.
	 *
	 * @return void
	 */
	private function load_admin_components(): void {
		/**
		 * Filter the required capability to load SureRank admin components.
		 * Allows Pro plugin to grant access based on role capabilities.
		 *
		 * @since 1.6.4
		 * @param string $capability Required capability. Default: 'manage_options'.
		 */
		$required_capability = apply_filters( 'surerank_admin_components_capability', 'manage_options' );

		if ( ! current_user_can( $required_capability ) ) {
			return;
		}

		$admin_components = [
			Seo_Popup::class,
			Update_Timestamp::class,
			Dashboard::class,
			Onboarding::class,
			BulkActions::class,
			BulkEdit::class,
			Ajax::class,
			Save_Endpoints::class,
			Rest_Site_Health::class,
			Search_Console_Widget::class,
			Site_Health::class,
		];

		$this->load_components( $admin_components );
	}

	/**
	 * Load frontend-specific components.
	 *
	 * @return void
	 */
	private function load_frontend_components(): void {
		$frontend_components = [
			Single::class,
			Product::class,
			Taxonomy::class,
			Title::class,
			Canonical::class,
			Common::class,
			Robots::class,
			Facebook::class,
			Twitter::class,
			Special_Page::class,
			Feed::class,
			Seo_Popup_Frontend::class,
			Meta_Data::class,
			Content_Seo::class,
			Meta_Tag_Injection::class,
			Xml_Sitemap::class,
			Archives::class,
		];

		// Add SEO metabox on the frontend for logged in users.
		if ( is_user_logged_in() ) {
			$frontend_components[] = Seo_Popup::class;
		}

		$this->load_components( $frontend_components );
	}

	/**
	 * Check if NPS Survey should be loaded.
	 *
	 * @return bool True if should load.
	 */
	private function should_load_nps_survey(): bool {
		return class_exists( 'SureRank\Inc\Lib\Surerank_Nps_Survey' ) && ! apply_filters( 'surerank_disable_nps_survey', false );
	}

	/**
	 * Load an array of components.
	 *
	 * @param array<string> $components Component class names.
	 * @return void
	 */
	private function load_components( array $components ): void {
		foreach ( $components as $component ) {
			$component::get_instance();
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Loader::get_instance();
