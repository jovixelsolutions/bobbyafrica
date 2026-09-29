<?php
/**
 * CartFlows Admin Notices.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Cartflows_Admin_Notices.
 */
class Cartflows_Admin_Notices {

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
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add the notices script.
		add_action( 'admin_enqueue_scripts', array( $this, 'notices_scripts' ) );

		// Group the admin notices actions.
		$this->register_admin_notices();

		// Group the ajax action callbacks.
		$this->register_ajax_callbacks();
	}

	/**
	 * Registers admin notices for CartFlows.
	 *
	 * Hooks the methods responsible for displaying admin and NPS notices
	 * to appropriate WordPress admin actions.
	 *
	 * @since 2.1.17
	 * @return void
	 */
	public function register_admin_notices() {
		add_action( 'admin_head', array( $this, 'show_admin_notices' ) );

		add_action( 'admin_footer', array( $this, 'show_nps_notice' ), 999 );
	}

	/**
	 * Registers AJAX callbacks for various CartFlows admin notices.
	 *
	 * This method hooks AJAX actions to their corresponding handler functions,
	 * allowing notices (such as Gutenberg, weekly report email, and custom offer notices)
	 * to be dismissed or acknowledged via AJAX requests in the WordPress admin area.
	 *
	 * @since 2.1.17
	 * @return void
	 */
	public function register_ajax_callbacks() {
		add_action( 'wp_ajax_cartflows_ignore_gutenberg_notice', array( $this, 'ignore_gb_notice' ) );
		add_action( 'wp_ajax_cartflows_disable_weekly_report_email_notice', array( $this, 'disable_weekly_report_email_notice' ) );
		add_action( 'wp_ajax_cartflows_snooze_script_migration', array( $this, 'snooze_script_migration_notice' ) );
		add_action( 'wp_ajax_cartflows_dismiss_script_migration_complete_notice', array( $this, 'dismiss_script_migration_complete_notice' ) );
		add_action( 'wp_ajax_cartflows_dismiss_new_ui_notice', array( $this, 'dismiss_new_ui_notice' ) );
	}

	/**
	 * Persist the "Switch to New UI" dismissal for the current user.
	 *
	 * Writes the same `notice-dismissed` user_meta key that Astra_Notices uses
	 * so the WP admin_notices banner and the React-shell banner stay in sync —
	 * dismissing either one hides both on the next load.
	 *
	 * @since 2.2.4
	 * @return void
	 */
	public function dismiss_new_ui_notice() {

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'cartflows' ) ) );
		}

		if ( ! check_ajax_referer( 'cartflows_dismiss_new_ui_notice', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'cartflows' ) ) );
		}

		update_user_meta( get_current_user_id(), 'cartflows-switch-to-new-ui-notice', 'notice-dismissed' );

		wp_send_json_success();
	}

	/**
	 * Show the weekly email Notice
	 *
	 * @return void
	 */
	public function show_weekly_report_email_settings_notice() {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		$is_show_notice = get_option( 'cartflows_show_weekly_report_email_notice', 'no' );

		if ( 'yes' === $is_show_notice && current_user_can( 'manage_options' ) ) {

			$setting_url = admin_url( 'admin.php?page=cartflows&path=settings#other_settings' );

			/* translators: %1$s Software Title, %2$s Plugin, %3$s Anchor opening tag, %4$s Anchor closing tag, %5$s Software Title. */
			$message = sprintf( __( '%1$sCartFlows:%2$s We just introduced an awesome new feature, weekly store revenue reports via email. Now you can see how many revenue we are generating for your store each week, without having to log into your website. You can set the email address for these email from %3$shere.%4$s', 'cartflows' ), '<strong>', '</strong>', '<a class="wcf-redirect-to-settings" target="_blank" href=" ' . esc_url( $setting_url ) . ' ">', '</a>' );
			$output  = '<div class="wcf-notice weekly-report-email-notice wcf-dismissible-notice notice notice-info is-dismissible">';
			$output .= '<p>' . $message . '</p>';
			$output .= '</div>';

			echo wp_kses_post( $output );
		}
	}

	/**
	 * Disable the weekly email Notice
	 *
	 * @return void
	 */
	public function disable_weekly_report_email_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows-disable-weekly-report-email-notice', 'security' );
		delete_option( 'cartflows_show_weekly_report_email_notice' );
		// Track weekly report notice dismissed event.
		Cartflows_Helper::set_analytics_flag( 'weekly_report_notice_dismissed' );
		wp_send_json_success();
	}

	/**
	 * Show the custom script migration notice.
	 *
	 * Renders an admin notice prompting users to migrate custom script data
	 * from the old textarea format to the new CodeMirror editor format.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function show_script_migration_notice() {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! Cartflows_Update::get_instance()->should_show_migration_notice() ) {
			return;
		}

		$output  = '<div class="wcf-notice script-migration-notice wcf-dismissible-notice notice notice-warning">';
		$output .= '<p>';
		$output .= '<strong>' . esc_html__( 'CartFlows: Custom Script Migration', 'cartflows' ) . '</strong><br>';
		$output .= esc_html__( 'CartFlows now has dedicated JavaScript and CSS editors for custom scripts in your funnels and steps. Click "Migrate Data" to move your existing scripts to the new editors, or choose "Remind Me Later" to be notified again in 7 days.', 'cartflows' );
		$output .= '</p>';
		$output .= '<p>';
		$output .= '<button class="button button-primary wcf-migrate-scripts-btn" data-action="migrate_scripts">' . esc_html__( 'Migrate Data', 'cartflows' ) . '</button>';
		$output .= '&nbsp;';
		$output .= '<button class="button button-secondary wcf-snooze-migration-btn" data-action="remind_later">' . esc_html__( 'Remind Me Later', 'cartflows' ) . '</button>';
		$output .= '</p>';
		$output .= '</div>';

		echo wp_kses_post( $output );
	}

	/**
	 * Handle snooze (remind me later) AJAX request for the script migration notice.
	 *
	 * Increments the skip counter and updates the skip timestamp.
	 * After 2 skips, sets the status to 'accepted' to stop showing the notice.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function snooze_script_migration_notice() {

		if ( ! check_ajax_referer( 'cartflows-snooze-script-migration', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'cartflows' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'cartflows' ) ) );
		}

		$update = Cartflows_Update::get_instance();

		if ( $update instanceof Cartflows_Update ) {
			$update->set_script_migration_status( 'skipped' );
			$update->increment_migration_skip_count();

			// After 2 skips, stop showing the notice permanently.
			if ( $update->get_migration_skip_count() >= 2 ) {
				$update->set_script_migration_status( 'accepted' );
			}

			wp_send_json_success( array( 'message' => __( 'You will be reminded in 7 days.', 'cartflows' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Unable to update migration status.', 'cartflows' ) ) );
	}

	/**
	 *  After save of permalinks.
	 */
	public function notices_scripts() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		wp_enqueue_style( 'cartflows-custom-notices', CARTFLOWS_URL . 'admin/assets/css/notices.css', array(), CARTFLOWS_VER );

		wp_enqueue_script( 'cartflows-notices', CARTFLOWS_URL . 'admin/assets/js/ui-notice.js', array( 'jquery' ), CARTFLOWS_VER, true );

		$localize_vars = array(
			'ignore_gb_notice'                   => wp_create_nonce( 'cartflows-ignore-gutenberg-notice' ),
			'dismiss_weekly_report_email_notice' => wp_create_nonce( 'cartflows-disable-weekly-report-email-notice' ),
			'snooze_script_migration'            => wp_create_nonce( 'cartflows-snooze-script-migration' ),
			'migrate_custom_scripts'             => wp_create_nonce( 'cartflows_migrate_custom_scripts' ),
			'dismiss_migration_complete_notice'  => wp_create_nonce( 'cartflows-dismiss-script-migration-complete-notice' ),
		);

		wp_localize_script( 'cartflows-notices', 'cartflows_notices', $localize_vars );
	}

	/**
	 *  After save of permalinks.
	 */
	public function show_admin_notices() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		global  $wp_version;

		if ( version_compare( $wp_version, '5.0', '>=' ) && is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			add_action( 'admin_notices', array( $this, 'gutenberg_plugin_deactivate_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'show_weekly_report_email_settings_notice' ) );
		add_action( 'admin_notices', array( $this, 'show_script_migration_notice' ) );
		add_action( 'admin_notices', array( $this, 'show_script_migration_complete_notice' ) );

		$image_path = esc_url( CARTFLOWS_URL . 'assets/images/cartflows-logo-small.jpg' );
		Astra_Notices::add_notice(
			array(
				'id'                   => 'cartflows-5-start-notice',
				'type'                 => 'info',
				'class'                => 'cartflows-5-star cartflows-admin-notice',
				'show_if'              => true,
				/* translators: %1$s white label plugin name and %2$s deactivation link */
				'message'              => sprintf(
					'<div class="notice-image" style="display: block;">
                        <img src="%1$s" class="custom-logo" alt="CartFlows Icon" itemprop="logo" style="max-width: 90px; border-radius: 50px;"></div>
                        <div class="notice-content">
                            <div class="notice-heading">
                                %2$s
                            </div>
                            <div class="notice-description">
								%3$s
							</div>
                            <div class="astra-review-notice-container">
                                <a href="%4$s" class="astra-notice-close astra-review-notice button-primary" target="_blank">
									<span class="dashicons dashicons-yes"></span>
                                	%5$s
                                </a>

								<a href="#" data-repeat-notice-after="%6$s" class="astra-notice-close astra-review-notice">
									<span class="dashicons dashicons-calendar"></span>
                                	%7$s
                                </a>

                                <a href="#" class="astra-notice-close astra-review-notice">
								    <span class="dashicons dashicons-smiley"></span>
                                	<u>%8$s</u>
                                </a>
                            </div>
                        </div>',
					$image_path,
					__( 'Hi there! You recently used CartFlows to build a sales funnel &mdash; Thanks a ton!', 'cartflows' ),
					__( 'It would be awesome if you could leave us a 5-star review-it helps us grow and guide others in choosing CartFlows!', 'cartflows' ),
					'https://wordpress.org/support/plugin/cartflows/reviews/?filter=5#new-post',
					__( 'Ok, you deserve it', 'cartflows' ),
					MONTH_IN_SECONDS,
					__( 'Nope, maybe later', 'cartflows' ),
					__( 'I already did', 'cartflows' )
				),
				'repeat-notice-after'  => MONTH_IN_SECONDS,
				'display-notice-after' => ( 2 * WEEK_IN_SECONDS ), // Display notice after 2 weeks.
			)
		);

		// CartFlows 3.0 — soft notice inviting legacy users to opt into the new admin UI.
		// Registered through Astra_Notices so it inherits the standard notice
		// design, dismiss handling and per-user storage the rest of CartFlows uses.
		if ( $this->is_new_ui_notice_visible() ) {
			$this->enqueue_new_ui_notice_assets();

			Astra_Notices::add_notice(
				array(
					'id'             => 'cartflows-switch-to-new-ui-notice',
					'type'           => 'info',
					'class'          => 'cartflows-switch-ui-notice cartflows-admin-notice',
					'show_if'        => true,
					'is_dismissible' => true,
					'priority'       => 5,
					'capability'     => 'cartflows_manage_settings',
					'message'        => sprintf(
						'<div class="notice-image" style="display: block;">
							<img src="%1$s" class="custom-logo" alt="CartFlows Icon" itemprop="logo" style="max-width: 90px; border-radius: 50px;"></div>
							<div class="notice-content">
								<div class="notice-heading">%2$s</div>
								<div class="notice-description">%3$s</div>
								<div class="astra-review-notice-container">
									<button type="button" class="astra-review-notice button-primary" data-wcf-action="switch">
										<span class="dashicons dashicons-yes"></span>
										%4$s
									</button>
									<a href="#" class="astra-notice-close astra-review-notice">
										<span class="dashicons dashicons-no-alt"></span>
										%5$s
									</a>
								</div>
							</div>',
						esc_url( CARTFLOWS_URL . 'assets/images/cartflows-logo-small.jpg' ),
						esc_html__( 'Your CartFlows got a fresh new look ✨', 'cartflows' ),
						esc_html__( 'Enjoy a faster, cleaner dashboard designed to help you work smarter. Not ready? You can always switch back from Settings → Advanced.', 'cartflows' ),
						esc_html__( 'Try the New Experience', 'cartflows' ),
						esc_html__( 'Maybe Later', 'cartflows' )
					),
				)
			);
		}
	}

	/**
	 * Render CartFlows NPS Survey Notice.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function show_nps_notice() {

		Nps_Survey::show_nps_notice(
			'nps-survey-cartflows',
			array(
				'show_if'          => $this->should_display_nps_survey_notice(),
				'dismiss_timespan' => 2 * WEEK_IN_SECONDS,
				'display_after'    => 0,
				'plugin_slug'      => 'cartflows',
				'show_on_screens'  => array( 'edit-cartflows_flow', 'toplevel_page_cartflows' ),
				'message'          => array(

					// Step 1 i.e rating input.
					'logo'                  => esc_url( CARTFLOWS_URL . 'admin-core/assets/images/cartflows-icon.svg' ),
					'plugin_name'           => __( 'CartFlows', 'cartflows' ),
					'nps_rating_message'    => __( 'How likely are you to recommend #pluginname to your friends or colleagues?', 'cartflows' ),

					// Step 2A i.e. positive.
					'feedback_content'      => __( 'Could you please do us a favor and give us a 5-star rating on WordPress? It would help others choose CartFlows with confidence. Thank you!', 'cartflows' ),
					'plugin_rating_link'    => esc_url( 'https://wordpress.org/support/plugin/cartflows/reviews/?filter=5#new-post' ),

					// Step 2B i.e. negative.
					'plugin_rating_title'   => __( 'Thank you for your feedback', 'cartflows' ),
					'plugin_rating_content' => __( 'We value your input. How can we improve your experience?', 'cartflows' ),
				),
			)
		);
	}

	/**
	 * Show Deactivate gutenberg plugin notice.
	 *
	 * @since 1.1.19
	 *
	 * @return void
	 */
	public function gutenberg_plugin_deactivate_notice() {

		$ignore_notice = get_option( 'wcf_ignore_gutenberg_notice', false );

		if ( 'yes' !== $ignore_notice ) {
			printf(
				'<div class="notice notice-error wcf_notice_gutenberg_plugin is-dismissible"><p>%s</p>%s</div>',
				wp_kses_post(
					sprintf(
					/* translators: %1$s: HTML, %2$s: HTML */
						__( 'Heads up! The Gutenberg plugin is not recommended on production sites as it may contain non-final features that cause compatibility issues with CartFlows and other plugins. %1$s Please deactivate the Gutenberg plugin %2$s to ensure the proper functioning of your website.', 'cartflows' ),
						'<strong>',
						'</strong>'
					)
				),
				''
			);
		}
	}

	/**
	 * Ignore admin notice.
	 */
	public function ignore_gb_notice() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows-ignore-gutenberg-notice', 'security' );

		update_option( 'wcf_ignore_gutenberg_notice', 'yes' );
	}

	/**
	 * Show the custom script migration complete notice.
	 *
	 * Renders an admin notice informing users that their custom scripts
	 * have been successfully migrated to the new dedicated code editors.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function show_script_migration_complete_notice() {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( 'completed' !== \CartFlows_Helper::get_script_migration_status() ) {
			return;
		}

		if ( 'yes' === get_option( 'cartflows_script_migration_complete_notice_dismissed', 'no' ) ) {
			return;
		}

		$output  = '<div class="wcf-notice script-migration-complete-notice wcf-dismissible-notice notice notice-success is-dismissible">';
		$output .= '<p>';
		$output .= '<strong>' . esc_html__( 'CartFlows: Custom Script Migration Complete', 'cartflows' ) . '</strong><br>';
		$output .= esc_html__( 'Your scripts have been successfully migrated to the dedicated JavaScript and CSS editors.', 'cartflows' );
		$output .= '</p>';
		$output .= '</div>';

		echo wp_kses_post( $output );
	}

	/**
	 * Handle dismiss AJAX request for the script migration complete notice.
	 *
	 * Permanently hides the migration complete notice once the user dismisses it.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function dismiss_script_migration_complete_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'cartflows' ) ) );
		}

		check_ajax_referer( 'cartflows-dismiss-script-migration-complete-notice', 'security' );

		update_option( 'cartflows_script_migration_complete_notice_dismissed', 'yes' );

		wp_send_json_success();
	}

	/**
	 * Check allowed screen for notices.
	 *
	 * @since 1.0.0
	 *
	 * @param array $exclude_page_ids Optional. Array of screen IDs to exclude from displaying notices.
	 * @return bool True if the notice should be displayed, false otherwise.
	 */
	public function allowed_screen_for_notices( $exclude_page_ids = array() ) {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'dashboard',
			'plugins',
		);

		// Exclude any page ids passed in $exclude_page_ids from $allowed_screens.
		if ( ! empty( $exclude_page_ids ) && is_array( $exclude_page_ids ) ) {
			$allowed_screens = array_diff( $allowed_screens, $exclude_page_ids );
		}

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether the "Switch to New UI" promo should be shown to the current user.
	 *
	 * Centralises the gating logic so the WP `admin_notices` render and the
	 * React-shell banner both honour the exact same conditions.
	 *
	 * @since 2.2.4
	 * @return bool
	 */
	public function is_new_ui_notice_visible() {

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			return false;
		}

		// Only relevant when the legacy admin is the active UI. The constant is
		// defined by the loader and accepts the 'enable'/'disable' string forms
		// the toggle stores, so don't read the option directly here.
		if ( ! defined( 'CARTFLOWS_LEGACY_ADMIN' ) || ! CARTFLOWS_LEGACY_ADMIN ) {
			return false;
		}

		// Astra_Notices' own astra-notice-close JS writes 'notice-dismissed' to
		// this user_meta key when the user dismisses the notice.
		if ( 'notice-dismissed' === get_user_meta( get_current_user_id(), 'cartflows-switch-to-new-ui-notice', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue the vanilla JS that wires the Switch button of the "Switch to
	 * New UI" notice. Called from `show_admin_notices()` so the asset only
	 * loads on screens where the Astra notice actually renders.
	 *
	 * @since 2.2.4
	 * @return void
	 */
	public function enqueue_new_ui_notice_assets() {

		$handle = 'wcf-new-ui-notice';

		if ( wp_script_is( $handle, 'enqueued' ) ) {
			return;
		}

		wp_enqueue_script(
			$handle,
			CARTFLOWS_URL . 'admin-legacy-core/assets/js/new-ui-notice.js',
			array(),
			CARTFLOWS_VER,
			true
		);

		wp_localize_script(
			$handle,
			'wcfNewUiNotice',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'switchAction' => 'cartflows_switch_to_new_ui',
				'switchNonce'  => wp_create_nonce( 'cartflows_switch_to_new_ui' ),
			)
		);
	}

	/**
	 * Check if the user has completed the onboarding, skipped the onboarding on ready step, and the store checkout is imported.
	 *
	 * @since 2.1.6
	 * @return bool
	 */
	public function should_display_nps_survey_notice() {

		$is_store_checkout_imported = (bool) get_option( '_cartflows_wizard_store_checkout_set', false );   // Must be true.
		$onboarding_completed       = (bool) get_option( 'wcf_setup_complete', false );                     // Must be true.
		$is_first_funnel_imported   = (bool) get_option( 'wcf_first_flow_imported', false );                // Must be true.
		$total_funnels              = intval( wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->publish );        // Must be greater than or equal to 1.

		/**
		 * Show the notice in two conditions.
		 * 1. If completed the onboarding steps/process of plugin and sets their first store checkout funnel successfully.
		 * 2. If sets up the first funnel manually and makes it live.
		 */
		return ( true === $is_store_checkout_imported && true === $onboarding_completed ) || ( true === $is_first_funnel_imported && ! empty( $total_funnels ) && 1 >= $total_funnels ) || ( ! empty( $total_funnels ) && 1 >= $total_funnels );
	}
}

Cartflows_Admin_Notices::get_instance();
