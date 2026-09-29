<?php
/**
 * Plugin Loader.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart;

use ModernCart\Admin_Core\Admin_Menu;
use ModernCart\Inc\Abilities\Register_Abilities;
use ModernCart\Inc\Admin_Notices;
use ModernCart\Inc\Floating;
use ModernCart\Inc\Floating_Ajax;
use ModernCart\Inc\Helper;
use ModernCart\Inc\Scripts;
use ModernCart\Inc\Order_Tracking;
use ModernCart\Inc\Slide_Out;
use ModernCart\Inc\Slide_Out_Ajax;

/**
 * Plugin_Loader
 *
 * @since 0.0.1
 */
class Plugin_Loader {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 0.0.1
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );

		register_activation_hook( __DIR__ . '/modern-cart.php', [ $this, 'activate' ] );
		add_action( 'plugins_loaded', [ $this, 'load_classes' ] );
		add_action( 'init', [ $this, 'save_version_info' ] );
		add_action( 'init', [ $this, 'register_bsf_analytics_entity' ], 20 );
		add_action( 'admin_init', [ $this, 'redirect_to_onboarding' ] );
		add_filter( 'plugin_action_links_' . MODERNCART_BASE, [ $this, 'action_links' ] );

		do_action( 'moderncart_loaded' );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since 1.0.9
	 * @return void
	 */
	public function load_textdomain(): void {
		$site_locale = apply_filters( 'plugin_locale', get_locale(), 'modern-cart' );

		// Global translations directory takes priority (Loco Translate / manual uploads).
		$mofile = WP_LANG_DIR . '/plugins/modern-cart-' . $site_locale . '.mo';

		if ( ! file_exists( $mofile ) ) {
			// Fall back to bundled translations shipped with the plugin.
			$mofile = MODERNCART_DIR . 'languages/modern-cart-' . $site_locale . '.mo';
		}

		if ( ! file_exists( $mofile ) ) {
			return;
		}

		// Load translations for the site locale.
		load_textdomain( 'modern-cart', $mofile, $site_locale );

		// In AJAX context (admin-ajax.php), WP_Translation_Controller calls
		// determine_locale() at translate-time, which returns the logged-in user's
		// profile language rather than the site language. Loading the site translations
		// under the request locale too ensures the controller finds them regardless
		// of which locale it looks up — without modifying any global WordPress filters.
		$request_locale = determine_locale();
		if ( $request_locale !== $site_locale ) {
			load_textdomain( 'modern-cart', $mofile, $request_locale );
		}
	}

	/**
	 * Activate plugin and set onboarding redirect.
	 */
	public function activate(): void {
		// Record install timestamp for days_since_install analytics metric.
		if ( ! get_option( 'mcw_usage_installed_time', false ) ) {
			update_option( 'mcw_usage_installed_time', time(), false );
		}

		$is_onboarding_complete = get_option( 'moderncart_is_onboarding_complete', 'no' );

		if ( 'yes' === $is_onboarding_complete ) {
			return;
		}

		set_transient( 'moderncart_redirect_to_onboarding', 'yes' );
	}

	/**
	 * Redirect to onboarding page.
	 */
	public function redirect_to_onboarding(): void {
		if ( ! get_transient( 'moderncart_redirect_to_onboarding' ) ) {
			return;
		}

		// Avoid redirection in case of ajax calls.
		if ( wp_doing_ajax() ) {
			return;
		}

		$url = add_query_arg(
			[
				'page'       => 'moderncart_settings',
				'nonce'      => wp_create_nonce( 'moderncart_onboarding_nonce' ),
				'onboarding' => 1,
			],
			admin_url( 'admin.php' )
		);

		delete_transient( 'moderncart_redirect_to_onboarding' );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Save version information.
	 */
	public function save_version_info(): void {
		$version = get_option( 'moderncart_version' );

		if ( is_array( $version ) && isset( $version['current'] ) && MODERNCART_VER === $version['current'] ) {
			// Already updated.
			return;
		}

		// Backfill install time for sites that activated before this tracking was added.
		if ( ! get_option( 'mcw_usage_installed_time', false ) ) {
			update_option( 'mcw_usage_installed_time', time(), false );
		}

		$version = [
			'current'  => MODERNCART_VER,
			'previous' => ( is_array( $version ) && isset( $version['current'] ) ) ? $version['current'] : '',
		];

		update_option( 'moderncart_version', $version );
	}

	/**
	 * Initiator
	 *
	 * @since 0.0.1
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
	 */
	public function autoload( $class ): void {
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

			$file = MODERNCART_DIR . $filename . '.php';

			// if the file redable, include it.
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	}

	/**
	 *  Declare the woo HPOS compatibility.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function declare_woo_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MODERNCART_FILE, true );
		}
	}

	/**
	 * Loads plugin classes as per requirement.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function load_classes(): void {
		$this->load_textdomain();
		$this->load_bsf_analytics_loader();

		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		// Let WooCommerce know, CartFlows is compatible with HPOS.
		add_action( 'before_woocommerce_init', array( $this, 'declare_woo_hpos_compatibility' ) );

		if ( is_admin() ) {
			Admin_Menu::get_instance();
			$this->load_astra_notices();
			Admin_Notices::get_instance();
		}

		$this->load_integration_classes();

		Scripts::get_instance();
		Floating::get_instance();
		Slide_Out::get_instance();
		Slide_Out_Ajax::get_instance();
		Floating_Ajax::get_instance();
		Register_Abilities::get_instance();
		Order_Tracking::get_instance();
	}

	/**
	 * Adds links in Plugins page.
	 *
	 * @param array<string> $links Existing links.
	 * @return array<string> Filtered links with settings added.
	 * @since 0.0.1
	 */
	public function action_links( $links ) {
		$plugin_links = apply_filters(
			'moderncart_cpsw_plugin_action_links',
			[
				'moderncart_settings' => '<a href="' . admin_url( 'admin.php?page=moderncart_settings' ) . '">' . __( 'Settings', 'modern-cart' ) . '</a>',
			]
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Load the Astra Notices library.
	 *
	 * @since 1.0.9
	 * @return void
	 */
	public function load_astra_notices() {
		if ( ! class_exists( 'Astra_Notices' ) ) {
			require_once MODERNCART_DIR . 'inc/libraries/astra-notices/class-astra-notices.php';
		}
	}

	/**
	 * Require the BSF Analytics Loader and trigger its constructor.
	 *
	 * Runs on plugins_loaded so the BSF_Analytics_Loader class is available
	 * for all plugins before init fires. Entity registration with translatable
	 * strings is intentionally deferred to register_bsf_analytics_entity()
	 * which runs on init after the textdomain has been loaded.
	 *
	 * @since 1.0.8
	 * @return void
	 */
	public function load_bsf_analytics_loader() {
		if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
			require_once MODERNCART_DIR . 'inc/libraries/bsf-analytics/class-bsf-analytics-loader.php';
		}
	}

	/**
	 * Register the Modern Cart entity with BSF Analytics.
	 *
	 * Runs on init:10, registered in the constructor so it queues before
	 * BSF_Analytics_Loader::load_analytics (registered during plugins_loaded).
	 * Translatable strings are safe here because the textdomain is loaded by init.
	 *
	 * Also performs a one-time migration of the legacy cf_analytics_optin option
	 * to mcw_usage_optin before the analytics class reads it.
	 *
	 * Backward compatibility matrix (keyed on MODERNCART_VER / MODERNCART_PRO_VER):
	 *  - Latest Free + Latest Pro  (Free ≥ 1.0.8, Pro ≥ 1.2.4): Free registers entity;
	 *    Pro defers and sends data via filter.  ← this method runs fully.
	 *  - Latest Free + Older Pro   (Free ≥ 1.0.8, Pro < 1.2.4): Old Pro registers its
	 *    own entity and does not defer.  Free must not register a duplicate.  ← early return.
	 *  - Older Free + Latest Pro   (Free < 1.0.8, Pro ≥ 1.2.4): Free does not have this
	 *    method; Pro handles analytics via its own fallback.  No conflict.
	 *  - Older Free + Older Pro    (Free < 1.0.8, Pro < 1.2.4): Neither plugin runs this
	 *    code; no change in behaviour.
	 *
	 * @since 1.0.8
	 * @return void
	 */
	public function register_bsf_analytics_entity() {
		if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
			return;
		}

		$this->maybe_migrate_analytics_optin();

		$plugin_slug   = 'modern-cart';
		$bsf_analytics = \BSF_Analytics_Loader::get_instance();

		$bsf_analytics->set_entity(
			array(
				'mcw' => array(
					'hide_optin_checkbox' => true,
					'product_name'        => 'Modern Cart',
					'usage_doc_link'      => 'https://my.cartflows.com/usage-tracking/',
					'path'                => MODERNCART_DIR . 'inc/libraries/bsf-analytics',
					'author'              => 'CartFlows',
					'deactivation_survey' => apply_filters(
						'moderncart_bsf_analytics_deactivation_survey_data',
						array(
							array(
								'id'                => 'deactivation-survey-' . $plugin_slug,
								'popup_logo'        => MODERNCART_URL . 'admin-core/assets/images/logo.svg',
								'plugin_slug'       => $plugin_slug,
								'plugin_version'    => MODERNCART_VER,
								'popup_title'       => __( 'Quick Feedback', 'modern-cart' ),
								'support_url'       => 'https://cartflows.com/contact/',
								'popup_description' => __( 'If you have a moment, please share why you are deactivating Modern Cart Starter:', 'modern-cart' ),
								'show_on_screens'   => array( 'plugins' ),
							),
						)
					),
				),
			)
		);

		// Ensure BSF_Analytics_Events is loaded before instantiating Modern_Cart_Analytics.
		// class-bsf-analytics-events.php is normally loaded inside BSF_Analytics::includes(),
		// which runs on init at priority 10 via BSF_Analytics_Loader::load_analytics(). Since
		// register_bsf_analytics_entity() also runs on init (priority 20), BSF_Analytics_Loader
		// should have already run — but we require it explicitly here as a safety guarantee so
		// detect_state_events() always finds BSF_Analytics_Events available.
		if ( ! class_exists( 'BSF_Analytics_Events' ) ) {
			require_once MODERNCART_DIR . 'inc/libraries/bsf-analytics/class-bsf-analytics-events.php';
		}

		require_once MODERNCART_DIR . 'inc/libraries/class-modern-cart-analytics.php';
	}

	/**
	 * Migrate legacy cf_analytics_optin to mcw_usage_optin.
	 *
	 * Sites that previously ran a CartFlows-era version of this plugin may
	 * have the analytics opt-in stored under cf_analytics_optin. This copies
	 * the value to the current key and removes the legacy option.
	 *
	 * Skips silently if cf_analytics_optin does not exist, or if
	 * mcw_usage_optin already has a value.
	 *
	 * @since 1.0.8
	 * @return void
	 */
	private function maybe_migrate_analytics_optin() {
		$legacy_value = get_option( 'cf_analytics_optin' );

		if ( false === $legacy_value ) {
			// Legacy option absent — nothing to migrate.
			return;
		}

		if ( false !== get_option( 'mcw_usage_optin' ) ) {
			// New option already set — just clean up the legacy key.
			return;
		}

		update_option( 'mcw_usage_optin', $legacy_value );
	}

	/**
	 * Loads integration classes like mcw-zipwp-helper.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function load_integration_classes() {
		require_once MODERNCART_DIR . 'inc/integrations/mcw-zipwp-helper.php';
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Plugin_Loader::get_instance();
