<?php
/**
 * CartFlows Loader.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CARTFLOWS_CA_Loader' ) ) {

	/**
	 * Class CARTFLOWS_CA_Loader.
	 */
	final class CARTFLOWS_CA_Loader {
		/**
		 * Member Variable
		 *
		 * @var utils
		 */
		public $utils = null;

		/**
		 * Member Variable
		 *
		 * @var Cartflows_Ca_Helper
		 */
		public $helper = null;

		/**
		 * Member Variable
		 *
		 * @var Cartflows_Ca_Default_Meta
		 */
		public $options = null;
		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->define_constants();

			// Activation hook.
			register_activation_hook( CARTFLOWS_CA_FILE, [ $this, 'activation_reset' ] );

			// deActivation hook.
			register_deactivation_hook( CARTFLOWS_CA_FILE, [ $this, 'deactivation_reset' ] );
			add_action( 'plugins_loaded', [ $this, 'load_abilities' ], 1 );
			add_action( 'plugins_loaded', [ $this, 'load_libraries' ], 99 );
			add_action( 'init', [ $this, 'load_cf_textdomain' ] );
			add_action( 'init', [ $this, 'load_plugin' ], 99 );

			// Let WooCommerce know, Plugin is compatible with HPOS.
			add_action( 'before_woocommerce_init', [ $this, 'declare_woo_hpos_compatibility' ] );
			
			add_action( 'admin_init', [ $this, 'redirect_to_onboarding' ], 20 );
		}

		/**
		 *  Initiator
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

				/**
				 * CartFlows CA loaded.
				 *
				 * Fires when Cartflows CA was fully loaded and instantiated.
				 *
				 * @since 1.0.0
				 */
				do_action( 'cartflows_ca_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		public function define_constants(): void {
			define( 'CARTFLOWS_CA_BASE', plugin_basename( CARTFLOWS_CA_FILE ) );
			define( 'CARTFLOWS_CA_DIR', plugin_dir_path( CARTFLOWS_CA_FILE ) );
			define( 'CARTFLOWS_CA_URL', plugins_url( '/', CARTFLOWS_CA_FILE ) );
			define( 'CARTFLOWS_CA_VER', '2.1.1' );
			define( 'CARTFLOWS_CA_REQ_PRO_VER', '1.2.0' );

			define( 'CARTFLOWS_CA_SLUG', 'cartflows_ca' );

			define( 'CARTFLOWS_CA_CART_ABANDONMENT_TABLE', 'cartflows_ca_cart_abandonment' );
			define( 'CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE', 'cartflows_ca_email_templates' );
			define( 'CARTFLOWS_CA_EMAIL_HISTORY_TABLE', 'cartflows_ca_email_history' );
			define( 'CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE', 'cartflows_ca_email_templates_meta' );

			define( 'CARTFLOWS_CA_DOMAIN_URL', 'https://cartflows.com/' );
			define( 'CARTFLOWS_CA_NPS_WEBHOOK_URL', 'https://webhook.ottokit.com/ottokit/c883bcf8-1f86-4a16-9b81-7fd4cfaa3a49' );
			define( 'WCAR_ONBOARDING_USER_SUB_WORKFLOW_URL', 'https://webhook.ottokit.com/ottokit/9d32a688-8d3f-4329-8f59-261dfc62c938' );

			define( 'WCF_DEFAULT_CART_LOST_TIME', 30 );
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin(): void {

			if ( ! function_exists( 'WC' ) ) {
				add_action( 'admin_notices', [ $this, 'fails_to_load' ] );
				return;
			}

			if ( 'no' === get_option( 'wcf_ca_all_db_tables_created', false ) ) {
				add_action( 'admin_notices', [ $this, 'fails_to_create_table' ] );
				return;
			}

			$this->load_helper_files_components();
			$this->load_core_files();
			$this->load_core_components();

			/**
			 * CartFlows Init.
			 *
			 * Fires when Cartflows is instantiated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cartflows_ca_init' );
		}

		/**
		 * Show error notice when all of the required database tables are not created.
		 *
		 * @since 1.2.15
		 *
		 * @return void
		 */
		public function fails_to_create_table(): void {

			$class = 'notice notice-error';
			/* translators: %s: html tags */
			$message = sprintf( __( 'Required database tables are not created for %1$sCart Abandonment Recovery for WooCommerce%2$s plugin. Please make sure that the database user has the REFERENCES privilege to create tables.', 'woo-cart-abandonment-recovery' ), '<strong>', '</strong>' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
		}

		/**
		 * Fires admin notice when Elementor is not installed and activated.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function fails_to_load(): void {

			$screen = get_current_screen();

			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$class = 'notice notice-error';
			/* translators: %s: html tags */
			$message = sprintf( __( 'The %1$sCart Abandonment Recovery for WooCommerce%2$s plugin requires %1$sWooCommerce%2$s plugin installed & activated.', 'woo-cart-abandonment-recovery' ), '<strong>', '</strong>' );
			$plugin  = 'woocommerce/woocommerce.php';

			if ( $this->is_woo_installed() ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate WooCommerce', 'woo-cart-abandonment-recovery' );

			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
				$button_label = __( 'Install WooCommerce', 'woo-cart-abandonment-recovery' );
			}

			$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
		}

		/**
		 * Is woocommerce plugin installed.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function is_woo_installed() {

			$path    = 'woocommerce/woocommerce.php';
			$plugins = get_plugins();

			return isset( $plugins[ $path ] );
		}

		/**
		 * Create new database tables for plugin updates.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function initialize_cart_abandonment_tables(): void {

			include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/classes/class-cartflows-ca-database.php';
			$db = Cartflows_Ca_Database::get_instance();
			$db->create_tables();
			$db->template_table_seeder();
		}

		/**
		 * Load Helper Files and Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_helper_files_components(): void {

			/* Cart abandonment helper class */
			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-helper.php';
			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-default-meta.php';
			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-utils.php';

			$this->utils  = Cartflows_Ca_Utils::get_instance();
			$this->helper = Cartflows_Ca_Helper::get_instance();

			// Check if the options instance is already set. If not, create a new instance.
			if ( is_null( $this->options ) || empty( $this->options ) ) {
				$this->options = $this->get_options_instance();
			}
		}

		/**
		 * Load core files.
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		public function load_core_files(): void {

			/* Update compatibility. */
			require_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-update.php';

			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-settings.php';

			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-tabs.php';
			
			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-admin-notices.php';

			include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-rollback.php';
			
			if ( ! $this->is_legacy_admin() ) {
				/* New admin loader with namespace */
				include_once CARTFLOWS_CA_DIR . 'class-wcar-admin-loader.php';
			}
		}

		/**
		 * Load Libraries file and classes.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function load_libraries() {

			if ( is_admin() ) {
				require_once CARTFLOWS_CA_DIR . 'lib/astra-notices/class-astra-notices.php';
			}

			if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
				require_once CARTFLOWS_CA_DIR . '/lib/bsf-analytics/class-bsf-analytics-loader.php';
			}

			$bsf_analytics = BSF_Analytics_Loader::get_instance();

			$bsf_analytics->set_entity(
				[
					'wcar' => [
						'hide_optin_checkbox' => true,
						'product_name'        => 'Woocommerce Cart Abandonment Recovery',
						'usage_doc_link'      => 'https://my.cartflows.com/usage-tracking/',
						'path'                => CARTFLOWS_CA_DIR . 'lib/bsf-analytics',
						'author'              => 'CartFlows Inc',
						'deactivation_survey' => apply_filters(
							'wcar_bsf_analytics_deactivation_survey_data',
							array(
								array(
									'id'                => 'deactivation-survey-woo-cart-abandonment-recovery',
									'popup_logo'        => CARTFLOWS_CA_URL . 'admin/assets/images/wcar-icon.svg',
									'plugin_version'    => CARTFLOWS_CA_VER,
									'plugin_slug'       => 'woo-cart-abandonment-recovery',
									'popup_title'       => 'Quick Feedback',
									'support_url'       => 'https://cartflows.com/contact/',
									'popup_description' => 'If you have a moment, please share why you are deactivating Cart Abandonment Recovery:',
									'show_on_screens'   => array( 'plugins' ),
								),
							)
						),
					],
				]
			);

			// Load the NPS Survey library.
			if ( ! class_exists( 'Cartflows_Ca_Nps_Survey' ) ) {
				require_once CARTFLOWS_CA_DIR . 'lib/class-cartflows-ca-nps-survey.php';
			}

			// Load BSF Library to send the data.
			if ( ! class_exists( 'Cartflows_Ca_Bsf_Analytics' ) ) {
				require_once CARTFLOWS_CA_DIR . 'lib/class-cartflows-ca-bsf-analytics.php';
			}
		}

		/**
		 * Load CartFlows Ca Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/%plugin-folder-name%/ folder
		 *      2. Local dorectory /wp-content/plugins/%plugin-folder-name%/languages/ folder
		 *
		 * @since  1.0.3
		 * @return void
		 */
		public function load_cf_textdomain(): void {

			// Default languages directory for CartFlows Ca.
			$lang_dir = CARTFLOWS_CA_DIR . 'languages/';

			/**
			 * Filters the languages directory path to use for CartFlows Ca.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'carflows_ca_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			global $wp_version;

			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			/**
			 * Language Locale for CartFlows Ca
			 *
			 * @var The $get_locale locale to use.
			 * Uses get_user_locale()` in WordPress 4.7 or greater,
			 * otherwise uses `get_locale()`.
			 */
			$locale = apply_filters( 'plugin_locale', $get_locale, 'woo-cart-abandonment-recovery' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'woo-cart-abandonment-recovery', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/%plugin-folder-name%/ folder.
				load_textdomain( 'woo-cart-abandonment-recovery', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/%plugin-folder-name%/languages/ folder.
				load_textdomain( 'woo-cart-abandonment-recovery', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'woo-cart-abandonment-recovery', false, $lang_dir );
			}
		}
		/**
		 * Load Core Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_components(): void {

			/* Cart abandonment module loader class */
			include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/classes/class-cartflows-ca-module-loader.php';

			include_once CARTFLOWS_CA_DIR . 'modules/weekly-email-report/class-cartflows-ca-admin-report-emails.php';
		}

		/**
		 * Load MCP Abilities.
		 *
		 * Includes the abstract ability base, the abilities register singleton,
		 * and all five WCAR ability classes, then bootstraps the register.
		 *
		 * @since 2.1.0
		 * @return void
		 */
		public function load_abilities(): void {

			// Ensure Meta_Options is available (it may not be in legacy-admin mode).
			if ( ! class_exists( 'WCAR\Admin\Inc\Meta_Options' ) ) {
				include_once CARTFLOWS_CA_DIR . 'admin/inc/meta-options.php';
			}

			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-abstract-ability.php';
			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-abilities-register.php';
			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-ability-get-settings.php';
			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-ability-get-setting.php';
			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-ability-update-setting.php';
			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-ability-get-dashboard-stats.php';
			include_once CARTFLOWS_CA_DIR . 'inc/abilities/class-wcar-ability-get-product-stats.php';

			\WCAR\Inc\Abilities\Wcar_Abilities_Register::get_instance();
		}

		/**
		 * Activation Reset
		 */
		public function activation_reset(): void {
			// Set the default options.
			$this->update_default_settings();
			// Create the database tables if they do not exist.
			$this->initialize_cart_abandonment_tables();
			// Onboarding.
			$this->activate();
		}

		/**
		 * Set the default cart abandonment settings.
		 *
		 * Uses the universal plugin options for centralized default value management.
		 *
		 * @since 1.0.0 Original method
		 * @since 1.3.3 Refactored to use universal plugin options
		 */
		public function update_default_settings(): void {

			// Get plugin options configuration.
			$plugin_options = $this->get_options_instance()->get_plugin_options();

			foreach ( $plugin_options as $option_key => $config ) {
				// Only set default if option doesn't exist.
				if ( false === get_option( $option_key, false ) ) {
					// Sanitize the default value before saving.
					$sanitized_value = $this->options->sanitize_setting_value( $option_key, $config['default'] );
					update_option( $option_key, $sanitized_value );
				}
			}
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset(): void {
			wp_clear_scheduled_hook( 'cartflows_ca_update_order_status_action' );
		}

		/**
		 *  Declare the woo HPOS compatibility.
		 */
		public function declare_woo_hpos_compatibility(): void {

			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CARTFLOWS_CA_FILE, true );
			}
		}

		/**
		 * Get the options instance.
		 *
		 * @since 2.0.0
		 * @return Cartflows_Ca_Default_Meta
		 */
		public function get_options_instance() {

			// Load required classes for activation.
			if ( ! class_exists( 'Cartflows_Ca_Default_Meta' ) ) {
				include_once CARTFLOWS_CA_DIR . 'classes/class-cartflows-ca-default-meta.php';
			}

			if ( is_null( $this->options ) || empty( $this->options ) ) {
				$this->options = Cartflows_Ca_Default_Meta::get_instance();
			}

			return $this->options;
		}

		/**
		 * Determine which UI to use based on version and user opt-in.
		 *
		 * @since 1.3.2
		 * @return bool True for new UI, false for legacy UI.
		 */
		public function should_use_new_ui(): bool {
			$saved_version = get_option( 'wcf_ca_version', false );
			$user_opted_in = get_option( 'cartflows_ca_use_new_ui', null );

			// If user has explicitly opted into new UI, use it.
			if ( 'on' === $user_opted_in ) {
				return true;
			} elseif ( empty( $user_opted_in ) ) {
				return false;
			}

			// For versions above 2.0.0, use new UI by default.
			
			if ( ! empty( $saved_version ) && false === stripos( $saved_version, 'RC' ) && version_compare( $saved_version, '2.0.0', '>' ) ) {
				return true;
			}

			// For versions below 2.0.0, use legacy UI (with notice to switch).
			return false;
		}

		/**
		 * Check if we should show the UI switch notice.
		 *
		 * @since 1.3.2
		 * @return bool True if notice should be shown.
		 */
		public function should_show_ui_switch_notice(): bool {
			$user_opted_in = get_option( 'cartflows_ca_use_new_ui', false );

			// Don't show notice if user already opted in.
			if ( ! empty( $user_opted_in ) && boolval( $user_opted_in ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Is legacy admin - backward compatibility wrapper.
		 *
		 * @since 1.0.0
		 * @return bool
		 */
		public function is_legacy_admin(): bool {
			return ! $this->should_use_new_ui();
		}

		/**
		 * Activate plugin and set onboarding redirect.
		 */
		public function activate(): void {

			if ( defined( 'WP_CLI' ) && \WP_CLI ) {
				return;
			}
	
			// Only for users who can configure the plugin.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
	
			$do_redirect = apply_filters(
				'wcar_enable_redirect_activation',
				get_option( 'wcar_do_redirect', true )
			);
	
			// If flag is off, never redirect.
			if ( ! $do_redirect ) {
				return;
			}
	
			// If onboarding is already completed, clear the flag and stop.
			$done_onboarding_setup = get_option( 'wcar_onboarding_completed', false );
			if ( $done_onboarding_setup ) {
				update_option( 'wcar_do_redirect', false );
				return;
			}
	
			// At this point: first‑time activation + onboarding not done.
			set_transient( 'wcar_redirect_to_onboarding', true );
		}

		/**
		 * Redirect to onboarding page.
		 */
		public function redirect_to_onboarding(): void {
			if ( ! get_transient( 'wcar_redirect_to_onboarding' ) ) {
				return;
			}

			// Avoid redirection in case of ajax calls.
			if ( wp_doing_ajax() ) {
				return;
			}

			$url = add_query_arg(
				[
					'page' => 'woo-cart-abandonment-recovery',
					'path' => 'onboarding',
				],
				admin_url( 'admin.php' )
			);

			delete_transient( 'wcar_redirect_to_onboarding' );

			wp_safe_redirect( $url );
			exit;
		}

	}

	/**
	 *  Prepare if class 'CARTFLOWS_CA_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	CARTFLOWS_CA_Loader::get_instance();
}

if ( ! function_exists( 'wcf_ca' ) ) {
	/**
	 * Get global class.
	 *
	 * @return object
	 */
	function wcf_ca() {
		return CARTFLOWS_CA_Loader::get_instance();
	}
}
