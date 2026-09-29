<?php
/**
 * CartFlows Ca Admin Notices.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Cartflows_Ca_Admin_Notices.
 */
class Cartflows_Ca_Admin_Notices {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'show_admin_notices' ] );
		add_action( 'admin_footer', array( $this, 'show_nps_notice' ), 999 );

		add_action( 'admin_notices', [ $this, 'show_weekly_report_email_settings_notice' ] );

		add_action( 'wp_ajax_wcar_disable_weekly_report_email_notice', [ $this, 'disable_weekly_report_email_notice' ] );

		// UI Switch Notice functionality.
		add_action( 'admin_notices', [ $this, 'show_ui_switch_notice' ] );
		add_action( 'wp_ajax_wcar_switch_to_new_ui', [ $this, 'update_ui_switch_option' ] );
	}

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
	 *  Show admin notices.
	 */
	public function show_admin_notices(): void {

		$image_path = esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/wcar-icon.png' );
		$review_url = esc_url( apply_filters( 'woo_ca_plugin_review_url', 'https://wordpress.org/support/plugin/woo-cart-abandonment-recovery/reviews/?filter=5#new-post' ) );

		Astra_Notices::add_notice(
			[
				'id'                   => 'cartflows-ca-5-star-notice',
				'type'                 => 'info',
				'class'                => 'cartflows-ca-5-star',
				'show_if'              => true,
				/* translators: %1$s white label plugin name and %2$s deactivation link */
				'message'              => sprintf(
					'<div class="notice-image" style="display: flex;">
                        <img src="%1$s" class="custom-logo" alt="CartFlows Icon" itemprop="logo" style="max-width: 90px;"></div>
                        <div class="notice-content">
                            <div class="notice-heading">
                                %2$s
                            </div>
                            %3$s<br />
                            <div class="astra-review-notice-container">
                                <a href="%4$s" class="astra-notice-close astra-review-notice button-primary" target="_blank">
                                %5$s
                                </a>
                            <span class="dashicons dashicons-calendar"></span>
                                <a href="#" data-repeat-notice-after="%6$s" class="astra-notice-close astra-review-notice">
                                %7$s
                                </a>
                            <span class="dashicons dashicons-smiley"></span>
                                <a href="#" class="astra-notice-close astra-review-notice">
                                %8$s
                                </a>
                            </div>
                        </div>',
					$image_path,
					__( 'Hello! Seems like you have used Cart Abandonment Recovery for WooCommerce plugin to recover abandoned carts. &mdash; Thanks a ton!', 'woo-cart-abandonment-recovery' ),
					__( 'Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help other users make a comfortable decision while choosing the CartFlows cart abandonment plugin.', 'woo-cart-abandonment-recovery' ),
					$review_url,
					__( 'Ok, you deserve it', 'woo-cart-abandonment-recovery' ),
					MONTH_IN_SECONDS,
					__( 'Nope, maybe later', 'woo-cart-abandonment-recovery' ),
					__( 'I already did', 'woo-cart-abandonment-recovery' )
				),
				'repeat-notice-after'  => MONTH_IN_SECONDS,
				'display-notice-after' => 3 * WEEK_IN_SECONDS, // Display notice after 2 weeks.
			]
		);
	}

	/**
	 * Render CartFlows NPS Survey Notice.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function show_nps_notice() {

		Nps_Survey::show_nps_notice(
			'nps-survey-woo-cart-abandonment-recovery',
			array(
				'show_if'          => $this->should_display_nps_survey_notice(),
				'dismiss_timespan' => 2 * WEEK_IN_SECONDS,
				'display_after'    => 0,
				'plugin_slug'      => 'woo-cart-abandonment-recovery',
				'show_on_screens'  => array( 'woocommerce_page_woo-cart-abandonment-recovery' ),
				'message'          => array(

					// Step 1 i.e rating input.
					'logo'                  => esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/wcar-icon.svg' ),
					'plugin_name'           => __( 'Cart Abandonment Recovery', 'woo-cart-abandonment-recovery' ),
					'nps_rating_message'    => __( 'How likely are you to recommend #pluginname to your friends or colleagues?', 'woo-cart-abandonment-recovery' ),

					// Step 2A i.e. positive.
					'feedback_content'      => __( 'Could you please do us a favor and give us a 5-star rating on WordPress? It would help others choose Cart Abandonment Recovery for WooCommerce with confidence. Thank you!', 'woo-cart-abandonment-recovery' ),
					'plugin_rating_link'    => esc_url( 'https://wordpress.org/support/plugin/cartflows/reviews/?filter=5#new-post' ),

					// Step 2B i.e. negative.
					'plugin_rating_title'   => __( 'Thank you for your feedback', 'woo-cart-abandonment-recovery' ),
					'plugin_rating_content' => __( 'We value your input. How can we improve your experience?', 'woo-cart-abandonment-recovery' ),
				),
				'privacy_policy'   => array(
					'url' => 'https://cartflows.com/privacy-policy/',
				),
			)
		);
	}

	/**
	 * Show the weekly email Notice
	 *
	 * @return void
	 */
	public function show_weekly_report_email_settings_notice(): void {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		$is_show_notice = get_option( 'wcf_ca_show_weekly_report_email_notice', 'no' );

		if ( 'yes' === $is_show_notice && current_user_can( 'manage_options' ) ) {

			$setting_url = admin_url( 'admin.php?page=woo-cart-abandonment-recovery&action=settings#wcf-ca-weekly-report-email-settings' );

			/* translators: %1$s Software Title, %2$s Plugin, %3$s Anchor opening tag, %4$s Anchor closing tag, %5$s Software Title. */
			$message = sprintf( __( '%1$sCart Abandonment Recovery for WooCommerce:%2$s We just introduced an awesome new feature, weekly order recovery reports via email. Now you can see how many orders we are recovering for your store each week, without having to log into your website. You can set the email address for these email from %3$shere.%4$s', 'woo-cart-abandonment-recovery' ), '<strong>', '</strong>', '<a class="wcf-ca-redirect-to-settings" target="_blank" href=" ' . esc_url( $setting_url ) . ' ">', '</a>' );
			$output  = '<div class="weekly-report-email-notice wcar-dismissible-notice notice notice-info is-dismissible">';
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
	public function disable_weekly_report_email_notice(): void {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		check_ajax_referer( 'wcar_disable_weekly_report_email_notice', 'security' );
		delete_option( 'wcf_ca_show_weekly_report_email_notice' );
		wp_send_json_success();
	}

	/**
	 * Check allowed screen for notices.
	 *
	 * @return bool
	 */
	public function allowed_screen_for_notices() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = [
			'woocommerce_page_woo-cart-abandonment-recovery',
			'dashboard',
			'plugins',
		];

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Show UI switch notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function show_ui_switch_notice(): void {

		// Use new notice decision function.
		if ( ! wcf_ca()->should_show_ui_switch_notice() ) {
			return;
		}

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		$image_path = esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/wcar-icon.png' );

		$ajax_nonce = wp_create_nonce( 'wcar_ui_switch_nonce' );
		
		// Return if no notice class found.
		if ( ! class_exists( 'Astra_Notices' ) ) {
			return;
		}

		Astra_Notices::add_notice(
			array(
				'id'                   => 'wcar-ui-switch-notice',
				'type'                 => 'warning',
				'class'                => 'wcar-switch-ui',
				'show_if'              => true,
				/* translators: %1$s white label plugin name and %2$s deactivation link */
				'message'              => sprintf(
					'<div class="notice-image">
                        <img src="%1$s" class="custom-logo" alt="Cart Abandonment Recovery for WooCommerce Icon" itemprop="logo"></div>
                        <div class="notice-content">
                            <div class="notice-heading" style="font-weight: 700;">
                                %2$s
                            </div>
                            <div class="notice-description">
								%3$s
							</div>
                            <div class="astra-review-notice-container">
								<button type="button" class="astra-notice-close astra-review-notice button-primary wcar-switch-ui-btn" data-action="new-ui" data-nonce="%4$s">
									%5$s
								</button>
                            </div>
                        </div>',
					$image_path,
					__( 'Legacy UI Deprecation', 'woo-cart-abandonment-recovery' ),
					__( 'We’re retiring the Legacy UI of Cart Abandonment Recovery to improve performance, ensure better WooCommerce compatibility, and support upcoming features. Please switch to the new UI to maintain uninterrupted cart recovery.', 'woo-cart-abandonment-recovery' ),
					$ajax_nonce,
					__( 'Switch to New UI', 'woo-cart-abandonment-recovery' )
				),
				'repeat-notice-after'  => 1 * WEEK_IN_SECONDS,
				'display-notice-after' => false,
			)
		);
	}

	/**
	 * Update UI switch option via AJAX.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_ui_switch_option(): void {

		// Check user capability.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'woo-cart-abandonment-recovery' ) );
		}

		// Check is the data is available in POST.
		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => __( 'No post data found', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}

		// Nonce verification.
		if ( ! check_ajax_referer( 'wcar_ui_switch_nonce', 'nonce', false ) ) {
			$response_data = [ 'message' => __( 'Invalid Nonce.', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}

		// Find the notice action.
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

		// Button action.
		if ( empty( $action ) ) {
			$response_data = [ 'message' => __( 'Button action not found.', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}

		if ( 'new-ui' === $action ) {
			// User wants to switch to new UI - set the persistent option.
			update_option( 'cartflows_ca_use_new_ui', true );
			wp_send_json_success(
				[
					'message'     => __( 'Switched to new UI', 'woo-cart-abandonment-recovery' ),
					'redirect_to' => admin_url( 'admin.php?page=woo-cart-abandonment-recovery' ),
				] 
			);
		} elseif ( 'dismiss' === $action ) {
			// User dismissed the notice - don't change UI preference, just hide notice.
			// We could add a separate option to track notice dismissal if needed.
			wp_send_json_success(
				[
					'message' => __( 'Notice dismissed', 'woo-cart-abandonment-recovery' ),
					'reload'  => true,
				] 
			);
		}

		wp_send_json_error( [ 'message' => __( 'Invalid action', 'woo-cart-abandonment-recovery' ) ] );
	}

	/**
	 * Check if the user has completed the onboarding, skipped the onboarding on ready step, and the store checkout is imported.
	 * Also checks that the current version is 2.0.0 or higher and that 2 weeks have passed since the plugin was updated to 2.0.0+.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function should_display_nps_survey_notice() {

		global $wpdb;
		$email_templates_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
		$total_email_templates = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$email_templates_table} WHERE is_activated = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				1
			)
		);

		/**
		 * Show the notice in following conditions.
		 * 
		 * If version is 2.0.0 or grater. 
		 * If 2 weeks have passed since the plugin was updated to 2.0.0+.
		 * If total email templates is greater than 0.
		 *
		 * @since 2.0.0
		 */
		return ( 1 <= $total_email_templates );
	}
}

Cartflows_Ca_Admin_Notices::get_instance();
