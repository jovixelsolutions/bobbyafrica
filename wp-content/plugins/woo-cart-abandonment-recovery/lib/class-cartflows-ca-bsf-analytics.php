<?php
/**
 * WooCommerce Cart Abandonment NPS Loader.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Ca_Bsf_Analytics' ) ) :

	/**
	 * WooCommerce Cart Abandonment NPS Nps Survey Loader
	 *
	 * @since 2.0.0
	 */
	class Cartflows_Ca_Bsf_Analytics {
		/**
		 * Instance
		 *
		 * @since 2.0.0
		 * @var (Object) Cartflows_Ca_Bsf_Analytics
		 */
		private static $instance = null;

		/**
		 * Get Instance
		 *
		 * @since 2.0.0
		 *
		 * @return object Class object.
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
		 * @since 2.0.0
		 */
		private function __construct() {
			add_filter( 'bsf_core_stats', array( $this, 'get_specific_stats' ) );
		}

		/**
		 * Pass addon specific stats to analytics.
		 *
		 * @since 2.0.0
		 * @param array $stats_data Default stats array.
		 * @return array $stats_data Default stats with addon specific stats array.
		 */
		public function get_specific_stats( $stats_data ) {

			if ( apply_filters( 'cartflows_ca_enable_non_sensitive_data_tracking', get_option( 'wcar_usage_optin', false ) ) ) {

				// Prepare default data to be tracked.
				$stats_data['plugin_data']['cart_abandonment']                   = $this->get_default_stats();
				$stats_data['plugin_data']['cart_abandonment']['kpi_records']    = $this->get_kpi_tracking_data();
				$stats_data['plugin_data']['cart_abandonment']['numeric_values'] = $this->get_numeric_data_stats();
				$stats_data['plugin_data']['cart_abandonment']['boolean_values'] = $this->get_boolean_data_stats();

				// Filter to add more options if any.
				$stats_data = apply_filters( 'cartflows_ca_get_specific_stats', $stats_data );
			}

			return $stats_data;

		}

		/**
		 * Retrieves default statistics for CartFlows CA.
		 *
		 * This function collects and returns default statistics for Cart Abandonment Recovery, including the website domain, site language, plugin version, WooCommerce version, active theme, active gateways, store location, and other KPI data.
		 *
		 * @since 2.0.0
		 * @param array $theme_data Array containing active theme information.
		 * @return array $default_data An array of default statistics for Cart Abandonment Recovery.
		 */
		public function get_default_stats() {
			$store_location   = $this->get_store_location();
			$version_numbers  = $this->get_version_numbers();
			$nps_parameters   = $this->get_nps_parameters();

			$default_data = array(
				'website-domain'        => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
				'plugin-version'        => $version_numbers['plugin_version'],
				'wcar-pro-version'      => $version_numbers['wcar_pro_version'],
				'woocommerce-version'   => $version_numbers['wc_version'],
				'wp-version'            => $version_numbers['wp_version'],
				'php-version'           => $version_numbers['php_version'],
				'store-country'         => $store_location['country'],
				'nps-survey-status'     => $nps_parameters,
				'site_language'         => $this->get_site_language(),
				'internal_referer'      => $this->get_internal_referer(),
			);

			return $default_data;
		}

		/**
		 * Get KPI tracking data for the last 2 days (excluding today).
		 *
		 * @since x.x.x
		 * @return array KPI data organized by date
		 */
		private function get_kpi_tracking_data() {
			$kpi_data = array();
			$today    = current_time( 'Y-m-d' );

			// Get data for yesterday and day before yesterday.
			for ( $i = 1; $i <= 2; $i++ ) {
				$date        = gmdate( 'Y-m-d', strtotime( $today . ' -' . $i . ' days' ) );
				$order_count = $this->get_daily_orders_count( $date );

				// Always include data, even if submissions is 0.
				$kpi_data[ $date ] = array( 
					'numeric_values' => array(
						'recovered_order_count' => $order_count,
					),
				);
			}

			return $kpi_data;
		}

		/**
		 * Get daily submissions count for a specific date.
		 *
		 * @param string $date Date in Y-m-d format.
		 * @since x.x.x
		 * @return int Daily submissions count
		 */
		private function get_daily_orders_count( $date ) {
			global $wpdb;

			$cart_abandonment_table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;
			$start_date 			= $date . ' 00:00:00';
			$end_date   			= $date . ' 23:59:59';

			$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$cart_abandonment_table} WHERE `order_status` = %s AND `time` >= %s AND `time` <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					WCF_CART_COMPLETED_ORDER,
					$start_date,
					$end_date
				)
			);

			return absint( $count );
		}

		/**
		 * Retrieves numeric data statistics for Cart Abandonment Recovery.
		 *
		 * This function collects and returns numeric data statistics for Cart Abandonment Recovery, including total users, active users, and follow-up email counts.
		 *
		 * @since 2.0.0
		 * @return array An array of numeric data statistics for Cart Abandonment Recovery.
		 */
		public function get_numeric_data_stats() {
			// Return the prepared data.
			return array(
				'total_followup_emails'      => strval( $this->get_total_followup_emails_count() ),
				'email_tmpl_with_coupon'     => strval( $this->get_email_template_meta_count( 'override_global_coupon' ) ),
				'email_tmpl_with_conditions' => strval( $this->get_email_template_meta_count( 'enable_sms_rule_engine' ) ),
				'email_tmpl_with_exclusions' => strval( $this->get_email_template_meta_count( 'exclude_product_ids' ) ),
			);
		}

		/**
		 * Retrieve all global features tracking data.
		 *
		 * This function retrieves and returns all global features tracking data including cartflows stats report emails, plugin data deletion, store checkout settings, override global checkout, disallow indexing, PayPal reference transactions, and pre-checkout offer settings.
		 *
		 * @since 2.0.0
		 * @param array $theme_data Array containing active theme information.
		 * @return array Array of all global features tracking data.
		 */
		public function get_boolean_data_stats() {
			return array(
				'weekly_report_email'    => $this->get_weekly_report_email_status(),
				'webhook_enabled'        => $this->get_webhook_enabled_status(),
				'using_woo_template'     => $this->get_woo_template_usage_status(),
				'using_new_ui' 			 => $this->get_ui_enabled_status(),
				'is_email_gdpr_enable'	 => $this->get_email_gdpr_enabled_status(),
				'suretriggers_active'    => is_plugin_active( 'suretriggers/suretriggers.php' ),
			);
		}

		/**
		 * Get NPS parameters.
		 *
		 * @since 2.0.0
		 * @return array NPS survey data.
		 */
		public function get_nps_parameters() {
			$nps_data = get_option( 'nps-survey-cartflows-ca', array() );

			return array(
				'nps_score'        => isset( $nps_data['score'] ) ? intval( $nps_data['score'] ) : 0,
				'nps_feedback'     => isset( $nps_data['feedback'] ) ? sanitize_text_field( $nps_data['feedback'] ) : '',
				'nps_submitted'    => isset( $nps_data['submitted'] ) ? (bool) $nps_data['submitted'] : false,
				'nps_dismissed'    => isset( $nps_data['dismissed'] ) ? (bool) $nps_data['dismissed'] : false,
				'nps_first_shown'  => isset( $nps_data['first_shown'] ) ? sanitize_text_field( $nps_data['first_shown'] ) : '',
				'nps_dismiss_step' => isset( $nps_data['dismiss_step'] ) ? sanitize_text_field( $nps_data['dismiss_step'] ) : '',		
			);
		}

		/**
		 * Get GDPR Info.
		 *
		 * @since x.x.x
		 * @return bool True if GDPR is enabled, false otherwise.
		 */
		public function get_email_gdpr_enabled_status(){
			return 'on' === wcf_ca()->utils->wcar_get_option( 'wcf_ca_gdpr_status' ) ? true : false;
		}

		/**
		 * Get plugin version numbers.
		 *
		 * @since 2.0.0
		 * @return array Version information.
		 */
		public function get_version_numbers() {
			return array(
				'plugin_version' => CARTFLOWS_CA_VER,
				'wcar_pro_version' => defined( 'WCAR_PRO_VER' ) ? WCAR_PRO_VER : '',
				'wp_version'     => get_bloginfo( 'version' ),
				'wc_version'     => defined( 'WC_VERSION' ) ? WC_VERSION : '',
				'php_version'    => function_exists( 'phpversion' ) ? phpversion() : '',
			);
		}

		/**
		 * Get site language.
		 *
		 * @since 2.0.0
		 * @return string Site language locale.
		 */
		public function get_site_language() {
			return get_locale();
		}

		/**
		 * Get store location from WooCommerce.
		 *
		 * @since 2.0.0
		 * @return array Store location data.
		 */
		public function get_store_location() {
			if ( ! function_exists( 'wc_get_base_location' ) ) {
				return array(
					'country' => '',
					'state'   => '',
				);
			}

			$location = wc_get_base_location();

			return array(
				'country' => isset( $location['country'] ) ? $location['country'] : '',
				'state'   => isset( $location['state'] ) ? $location['state'] : '',
			);
		}

		/**
		 * Get internal referer data.
		 *
		 * @since 2.0.0
		 * @return string Internal referer information.
		 */
		public function get_internal_referer() {
			$bsf_internal_referer = get_option( 'bsf_product_referers', array() );

			return ! empty( $bsf_internal_referer['woo-cart-abandonment-recovery'] ) ? $bsf_internal_referer['woo-cart-abandonment-recovery'] : '';
		}

		/**
		 * Get weekly report email status.
		 *
		 * @since 2.0.0
		 * @return bool True if weekly report emails are enabled.
		 */
		public function get_weekly_report_email_status() {
			$email_admin_on_recovery = get_option( 'wcf_ca_send_recovery_report_emails_to_admin', 'on' );

			return 'on' === $email_admin_on_recovery;
		}

		/**
		 * Get total number of follow-up emails created.
		 *
		 * @since 2.0.0
		 * @return int Total follow-up emails count.
		 */
		public function get_total_followup_emails_count() {
			global $wpdb;

			$email_template_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;

			// Get the count of templates where the templates are activated (is_activated = 1)
			$total_templates = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$email_template_table} WHERE is_activated = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					1
				)
			);

			return intval( $total_templates );
		}

		/**
		 * Get count of email templates where a given meta key has a non-empty value.
		 *
		 * @since x.x.x
		 * @param string $meta_key The meta key to filter by.
		 * @return int Count of matching email templates.
		 */
		private function get_email_template_meta_count( $meta_key ) {
			global $wpdb;

			$email_template_meta_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;

			$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$email_template_meta_table} WHERE meta_key = %s AND meta_value != %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$meta_key,
					''
				)
			);

			return intval( $count );
		}

		/**
		 * Get webhook enabled status.
		 *
		 * @since 2.0.0
		 * @return bool True if webhook is enabled.
		 */
		public function get_webhook_enabled_status() {
			$zapier_status = get_option( 'wcf_ca_zapier_tracking_status' );
			$webhook_url   = get_option( 'wcf_ca_zapier_cart_abandoned_webhook' );

			return 'on' === $zapier_status && ! empty( $webhook_url );
		}

		/**
		 * Get WooCommerce template usage status.
		 *
		 * @since 2.0.0
		 * @return bool True if using WooCommerce templates.
		 */
		public function get_woo_template_usage_status() {
			// Check if any email templates are using WooCommerce styling.
			global $wpdb;

			$email_template_meta_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;

			$woo_template_usage = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$email_template_meta_table} WHERE meta_key = %s AND meta_value = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					'wc_email_template',
					'yes'
				)
			);

			return intval( $woo_template_usage ) > 0;
		}

		/**
		 * Get new UI enabled status.
		 *
		 * @since 2.0.6
		 * @return bool True if new UI is enabled.
		 */
		public function get_ui_enabled_status() {
			$ui_status = get_option( 'cartflows_ca_use_new_ui', '' );
			$allowed = ['on', '1', 'yes'];
			$value = (string) $ui_status;

			return in_array($value, $allowed, true);
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Ca_Bsf_Analytics::get_instance();

endif;
