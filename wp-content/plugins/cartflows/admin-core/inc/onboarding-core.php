<?php
/**
 * CartFlows Onboarding Core.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OnboardingCore.
 */
class OnboardingCore {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
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
	 */
	public function __construct() {

		if ( apply_filters( 'cartflows_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {

			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			add_action( 'admin_init', array( $this, 'hide_notices' ) );
			add_action( 'admin_notices', array( $this, 'show_setup_wizard' ) );
			add_action( 'woocommerce_installed', array( $this, 'disable_woo_setup_redirect' ) );
			// We are hiding admin bar intentionally, only on the setup wizard page.
			if ( isset( $_GET['page'] ) && 'cartflows-onboarding' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_filter( 'show_admin_bar', '__return_false', 1 ); //phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected, WordPressVIPMinimum.UserExperience.AdminBarRemoval.HidingDetected
			}

			add_action( 'init', array( $this, 'load_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'load_admin_media_styles' ) );

			add_action( 'admin_init', array( $this, 'redirect_to_onboarding' ) );
			add_filter( 'before_cp_load_popup', array( $this, 'hide_convert_pro_popups' ), 10, 1 ); // Disable the render of Convert Pro popups in onboarding wizard.
			add_filter( 'astra_get_option_scroll-to-top-enable', '__return_false' ); // Remove Astra's Scroll to top arrow.
		}
	}

	/**
	 * Redirect to onboarding if required.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function redirect_to_onboarding() {

		if ( ! get_option( 'wcf_start_onboarding', false ) ) {
			return;
		}

		if ( '1' === get_option( 'wcf_setup_complete', false ) || '1' === get_option( 'wcf_setup_skipped', false ) ) {
			return;
		}

		delete_option( 'wcf_start_onboarding' );
		wp_safe_redirect( esc_url_raw( admin_url( 'index.php?page=cartflows-onboarding' ) ) );
		exit();
	}


	/**
	 * Load styles.
	 */
	public function load_admin_media_styles() {

		$ary_libs               = array(
			'common',
			'forms',
		);
		$admin_media_styles_url = add_query_arg(
			array(
				'c'      => 0,
				'dir'    => ! is_rtl() ? 'ltr' : 'rtl',
				'load[]' => implode( ',', $ary_libs ),
				'ver'    => 'you_wp_version',
			),
			admin_url() . 'load-styles.php'
		);
		echo "<link rel='stylesheet' id='admin_styles_for_media-css' href='" . esc_url( $admin_media_styles_url ) . "' type='text/css' media='all' />";
	}

	/**
	 * Load media.
	 */
	public function load_scripts() {

		add_action( 'wp_enqueue_scripts', array( $this, 'load_media_script' ) );
	}

	/**
	 * Load WP media script on init.
	 */
	public function load_media_script() {
		wp_enqueue_media();
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public function hide_notices() {

		if ( ! isset( $_GET['wcf-hide-notice'] ) ) {
			return;
		}

		$wcf_hide_notice   = isset( $_GET['wcf-hide-notice'] ) ? sanitize_text_field( wp_unslash( $_GET['wcf-hide-notice'] ) ) : '';
		$_wcf_notice_nonce = isset( $_GET['_wcf_notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wcf_notice_nonce'] ) ) : '';

		if ( $wcf_hide_notice && $_wcf_notice_nonce && wp_verify_nonce( sanitize_text_field( wp_unslash( $_wcf_notice_nonce ) ), 'wcf_hide_notices_nonce' ) ) {
			update_option( 'wcf_setup_skipped', true );
		}
	}

	/**
	 *  Disable the woo redirect for new setup.
	 */
	public function disable_woo_setup_redirect() {

		delete_transient( '_wc_activation_redirect' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function show_setup_wizard() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'cartflows_page_cartflows_settings',
			'edit-cartflows_flow',
			'dashboard',
			'plugins',
		);

		if ( ! in_array( $screen_id, $allowed_screens, true ) ) {
			return;
		}

		$status     = get_option( 'wcf_setup_complete', false );
		$skip_setup = get_option( 'wcf_setup_skipped', false );

		if ( false === $status && ! $skip_setup ) { ?>
			<div class="notice notice-info wcf-notice">
				<p><b><?php esc_html_e( 'Thanks for installing and using CartFlows!', 'cartflows' ); ?></b></p>
				<p><?php esc_html_e( 'It is easy to use the CartFlows. Please use the setup wizard to quick start setup.', 'cartflows' ); ?></p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'index.php?page=cartflows-onboarding' ) ); ?>" class="button button-primary"> <?php esc_html_e( 'Start Setup', 'cartflows' ); ?></a>
					<a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcf-hide-notice', 'install' ), 'wcf_hide_notices_nonce', '_wcf_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip Setup', 'cartflows' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		// Ignoring phpcs nonce rule as we are calling this function on admin action.
		if ( empty( $_GET['page'] ) || 'cartflows-onboarding' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		add_dashboard_page( '', '', 'manage_options', 'cartflows-onboarding', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {

		// Ignoring phpcs nonce rule as we are calling this function on admin action.
		if ( empty( $_GET['page'] ) || 'cartflows-onboarding' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$this->load_required_scripts();
		$this->localize_vars();

		// Disable loading of Query Monitor in footer.
		add_filter( 'qm/dispatch/html', '__return_false' );

		ob_start();
		include_once CARTFLOWS_DIR . 'admin-core/views/onboarding-base.php';
	}

	/**
	 * Load scripts.
	 */
	public function load_required_scripts() {
		$handle            = 'wcf-onboarding-app';
		$build_path        = CARTFLOWS_DIR . 'admin-core/assets/build/';
		$build_url         = CARTFLOWS_URL . 'admin-core/assets/build/';
		$script_asset_path = $build_path . 'onboarding-app.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => CARTFLOWS_VER,
			);

		$script_dep = array_merge( $script_info['dependencies'], array( 'updates' ) );

		wp_register_script(
			$handle,
			$build_url . 'onboarding-app.js',
			$script_dep,
			$script_info['version'],
			true
		);

		wp_register_style(
			$handle,
			$build_url . 'onboarding-app.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );
		wp_style_add_data( $handle, 'rtl', 'replace' );

		wp_register_script(
			'wcf-onboarding-helper',
			CARTFLOWS_URL . 'admin-core/assets/js/onboarding-helper.js',
			array( 'jquery', 'wp-util', 'updates', 'media-upload' ),
			CARTFLOWS_VER,
			true
		);
		wp_enqueue_script( 'wcf-onboarding-helper' );
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui' );
	}

	/**
	 * Localize variables in admin.
	 */
	public function localize_vars() {

		$vars = array();

		$plugins = array(
			array(
				'name'   => 'WooCommerce',
				'slug'   => 'woocommerce',
				'status' => $this->get_plugin_status( 'woocommerce/woocommerce.php' ),
			),
			array(
				'name'   => 'Cart Abandonment',
				'slug'   => 'woo-cart-abandonment-recovery',
				'status' => $this->get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' ),
			),
			array(
				'name'   => 'Modern cart',
				'slug'   => 'modern-cart',
				'status' => $this->get_plugin_status( 'modern-cart/modern-cart.php' ),
			),
			array(
				'name'   => 'WooCommerce Payments',
				'slug'   => 'woocommerce-payments',
				'status' => $this->get_plugin_status( 'woocommerce-payments/woocommerce-payments.php' ),
			),
			array(
				'name'   => 'Spectra',
				'slug'   => 'ultimate-addons-for-gutenberg',
				'status' => $this->get_plugin_status( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ),
			),
		);

		$installed_plugins = get_plugins();

		$page_builders = array(
			'elementor'      => array(
				'slug'    => 'elementor',
				'init'    => 'elementor/elementor.php',
				'active'  => is_plugin_active( 'elementor/elementor.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['elementor/elementor.php'] ) ? 'yes' : 'no',
			),
			'beaver-builder' => array(
				'slug'    => 'beaver-builder-lite-version',
				'init'    => 'beaver-builder-lite-version/fl-builder.php',
				'active'  => is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['beaver-builder-lite-version/fl-builder.php'] ) ? 'yes' : 'no',
			),
			'divi'           => array(
				'slug'    => 'divi',
				'init'    => 'divi',
				'active'  => 'yes',
				'install' => 'NA', // Removed the Support of DIVI theme that is why it is set as NA.
			),
			'gutenberg'      => array(
				'slug'    => 'ultimate-addons-for-gutenberg',
				'init'    => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
				'active'  => is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php'] ) ? 'yes' : 'no',
			),
			'bricks-builder' => array(
				'slug'    => 'bricks',
				'init'    => 'bricks',
				'active'  => 'yes',
				'install' => 'NA', // Bricks is an paid theme that is why, the install option is set to NA. As this will be enabled on the user's website before the CartFlows Install.
			),
			// Intentionally installing the GB plugin when the other option is selected.
			'other'          => array(
				'slug'    => 'ultimate-addons-for-gutenberg',
				'init'    => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
				'active'  => is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) ? 'yes' : 'no',
				'install' => isset( $installed_plugins['ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php'] ) ? 'yes' : 'no',
			),
		);

		$current_user = wp_get_current_user();

		$vars = array(
			'current_user_name'      => ! empty( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name,
			'current_user_email'     => ! empty( $current_user->user_email ) ? $current_user->user_email : '',
			'plugins'                => $plugins,
			'page_builders'          => $page_builders,
			'active_page_builder'    => OnboardingHelper::get_active_supported_builder( $page_builders ),
			'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'admin_url'              => admin_url( 'admin.php' ),
			'admin_base_url'         => admin_url(),
			'admin_index_url'        => admin_url( 'index.php' ),
			'default_page_builder'   => \Cartflows_Helper::get_common_settings()['default_page_builder'],
			'site_logo'              => $this->get_site_logo_url(),
			'template_import_errors' => array(
				'api_errors' => array(
					'title' => __( 'Oops!! Unexpected error occoured', 'cartflows' ),
					'msg'   => __( 'Import template API call failed. Please reload the page and try again!', 'cartflows' ),
				),
			),
			'is_pro'                 => _is_cartflows_pro(),
			'cf_pro_type'            => defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) ? CARTFLOWS_PRO_PLUGIN_TYPE : 'free',
			'woocommerce_status'     => $this->get_plugin_status( 'woocommerce/woocommerce.php' ),
		);

		$vars = apply_filters( 'cartflows_onboarding_localized_vars', $vars );

		wp_localize_script( 'wcf-onboarding-app', 'cartflows_onboarding', $vars );
	}

	/**
	 * Get customizer/site logo URL.
	 *
	 * @since 1.10.0
	 *
	 * @return $image_url Site logo URL
	 */
	public function get_site_logo_url() {

		$logo      = get_theme_mod( 'custom_logo' );
		$site_logo = '';

		if ( ! empty( $logo ) ) {

			$image = wp_get_attachment_image_src( $logo, 'full' );

			$site_logo = array(
				'id'     => $logo,
				'url'    => $image[0] ? $image[0] : '',
				'width'  => $image[1] ? $image[1] : '',
				'height' => $image[2] ? $image[2] : '',
			);
		}

		return $site_logo;
	}

	/**
	 * Get plugin status
	 *
	 * @since 1.1.4
	 *
	 * @param  string $plugin_init_file Plguin init file.
	 * @return mixed
	 */
	public function get_plugin_status( $plugin_init_file ) {

		$installed_plugins = get_plugins();

		if ( ! isset( $installed_plugins[ $plugin_init_file ] ) ) {
			return 'not-installed';
		} elseif ( is_plugin_active( $plugin_init_file ) ) {
			return 'active';
		} else {
			return 'inactive';
		}
	}

	/**
	 * Function to hide Convert Pro popups during the onboarding wizard process.
	 *
	 * This function checks if the current page is the CartFlows onboarding page and if so, it sets the load parameter to false, effectively hiding the Convert Pro popups.
	 *
	 * @param bool $load The initial load state of the Convert Pro popups.
	 * @return bool The modified load state of the Convert Pro popups.
	 */
	public function hide_convert_pro_popups( $load ) {

		if ( ! empty( $_GET['page'] ) && 'cartflows-onboarding' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$load = false;
		}

		return $load;
	}
}

