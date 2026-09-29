<?php
/**
 * CartFlows Analytics — single entry point for all BSF Analytics tracking.
 *
 * Owns the complete CartFlows stats payload (default stats, numeric_values,
 * boolean_values, kpi_records) and one-time milestone events via
 * BSF_Analytics_Events. Registered on a single bsf_core_stats filter.
 *
 * @package CartFlows
 * @since   2.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cartflows_Analytics.
 */
class Cartflows_Analytics {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.2.4
	 */
	private static $instance;

	/**
	 * Shared BSF_Analytics_Events instance.
	 *
	 * @var BSF_Analytics_Events|null
	 */
	private static $events = null;

	/**
	 * Initiator
	 *
	 * @since 2.2.4
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
	 * Registers the bsf_core_stats filter on every request (frontend and admin)
	 * so events_record rides the BSF_Analytics::send() POST regardless of which
	 * request triggers transmission. State detection runs lazily inside the
	 * filter callback (see get_stats) — same pattern as Astra.
	 *
	 * @since 2.2.4
	 */
	public function __construct() {
		// Single bsf_core_stats filter — owns the complete CartFlows payload.
		add_filter( 'bsf_core_stats', array( $this, 'get_stats' ) );

		// Hook-based event: first flow published.
		add_action( 'transition_post_status', array( $this, 'track_first_flow_published' ), 10, 3 );
	}

	/**
	 * Get shared BSF_Analytics_Events instance.
	 *
	 * Returns null if BSF_Analytics_Events is not yet loaded — callers must guard.
	 *
	 * @since 2.2.4
	 * @return BSF_Analytics_Events|null
	 */
	public static function events() {
		if ( null === self::$events ) {
			if ( ! class_exists( 'BSF_Analytics_Events' ) ) {
				return null;
			}
			self::$events = new BSF_Analytics_Events( 'cf' );
		}
		return self::$events;
	}

	// -------------------------------------------------------------------------
	// BSF Analytics stats payload
	// -------------------------------------------------------------------------

	/**
	 * Build and append the complete CartFlows analytics payload.
	 *
	 * Single bsf_core_stats filter callback — replaces the former
	 * Cartflows_Admin::get_specific_stats() handler.
	 *
	 * @since 2.2.4
	 * @param array $stats_data Existing analytics payload.
	 * @return array
	 */
	public function get_stats( $stats_data ) {
		// Use get_site_option so multisite network opt-in flows correctly — matches
		// BSF_Analytics::is_tracking_enabled() which also reads the site option.
		if ( ! apply_filters( 'cartflows_enable_non_sensitive_data_tracking', get_site_option( 'cf_usage_optin', false ) ) ) {
			return $stats_data;
		}

		$theme_data = $this->get_active_theme();

		$stats_data['plugin_data']['cartflows']                   = $this->get_default_stats( $theme_data );
		$stats_data['plugin_data']['cartflows']['kpi_records']    = $this->get_kpi_tracking_data();
		$stats_data['plugin_data']['cartflows']['numeric_values'] = $this->get_numeric_data_stats();
		$stats_data['plugin_data']['cartflows']['boolean_values'] = $this->get_boolean_data_stats( $theme_data );

		// Detect state-based events lazily inside the filter so detection runs
		// in the same request as transmission (BSF_Analytics_Events dedup makes
		// repeated calls safe). Throttled by a 24h transient.
		if ( false === get_transient( 'cf_state_events_checked' ) ) {
			$this->detect_state_events();
		}

		// Append pending milestone events.
		$events = self::events();
		if ( null !== $events ) {
			$stats_data['plugin_data']['cartflows']['events_record'] = $events->flush_pending();
		}

		// Allow Pro or other extensions to append additional data.
		$stats_data = apply_filters( 'cartflows_get_specific_stats', $stats_data );

		return $stats_data;
	}

	/**
	 * Retrieve default statistics for CartFlows.
	 *
	 * @since 2.2.4
	 * @param array $theme_data Array containing active theme information.
	 * @return array
	 */
	private function get_default_stats( $theme_data ) {
		$bsf_internal_referer = get_option( 'bsf_product_referers', array() );
		$store_location       = '';
		$woo_version          = '';

		if ( wcf()->is_woo_active ) {
			$store_location = wc_get_base_location();
			$woo_version    = WC()->version;
		}

		// Sanitize knowledge base search terms before sending.
		$raw_kb_searches = get_option( 'cartflows_kb_searches', array() );
		$kb_searches     = array_map( 'sanitize_text_field', is_array( $raw_kb_searches ) ? $raw_kb_searches : array() );

		return array(
			'website-domain'             => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
			'site_language'              => get_locale(),
			'cartflows-lite-version'     => CARTFLOWS_VER,
			'cartflows-pro-version'      => _is_cartflows_pro() ? CARTFLOWS_PRO_VER : '',
			'woocommerce-version'        => $woo_version,
			'default-page-builder'       => Cartflows_Helper::get_common_setting( 'default_page_builder' ),
			'active-theme'               => $theme_data['parent_theme'],
			'active-gateway-count'       => wcf()->is_woo_active ? count( (array) $this->get_active_gateways() ) : 0,
			'social-tracking'            => $this->get_social_tracking_flags(),
			'store-country'              => ! empty( $store_location['country'] ) ? $store_location['country'] : '',
			'documentation-search-terms' => $kb_searches,
			'internal_referer'           => ! empty( $bsf_internal_referer['cartflows'] ) ? $bsf_internal_referer['cartflows'] : '',
			// Simplified: boolean flag only — no raw feedback object.
			'nps_survey_submitted'       => ! empty( get_option( 'nps-survey-cartflows', array() ) ),
			'pro_license_key_exists'     => $this->check_pro_license_key_exists() ? true : false,
			// Simplified: count only — no module ID list.
			'learn_modules_completed'    => count( (array) get_option( 'wcf_learn_data', array() ) ),
		);
	}

	/**
	 * Get KPI tracking data for the last 2 days (excluding today).
	 *
	 * @since 2.2.4
	 * @return array KPI records keyed by date.
	 */
	private function get_kpi_tracking_data() {
		$kpi_data = array();

		for ( $i = 1; $i <= 2; $i++ ) {
			$date              = wp_date( 'Y-m-d', strtotime( "-{$i} days" ) );
			$kpi_data[ $date ] = array(
				'numeric_values' => array(
					'order_count'  => $this->get_daily_orders_count( $date ),
					'offer_orders' => $this->get_daily_offer_orders_count( $date ),
				),
			);
		}

		return $kpi_data;
	}

	/**
	 * Get daily CartFlows order count for a specific date.
	 *
	 * Counts completed/processing orders associated with a CartFlows flow.
	 * Supports both classic post-table and HPOS order storage.
	 *
	 * @since 2.2.4
	 * @param string $date Date in Y-m-d format.
	 * @return int
	 */
	private function get_daily_orders_count( $date ) {
		global $wpdb;

		$start_date = $date . ' 00:00:00';
		$end_date   = $date . ' 23:59:59';

		if ( wcf()->utils->is_hpos_enabled() ) {
			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';
			$order_type_key   = 'type';
			$order_table_id   = 'id';
		} else {
			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';
			$order_type_key   = 'post_type';
			$order_table_id   = 'ID';
		}

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM $order_table o
				INNER JOIN $order_meta_table om ON o.$order_table_id = om.$order_id_key AND om.meta_key IN ('_wcf_flow_id', '_cartflows_parent_flow_id')
				WHERE o.$order_type_key = 'shop_order'
					AND o.$order_status_key IN ('wc-completed', 'wc-processing')
					AND o.$order_date_key >= %s
					AND o.$order_date_key <= %s",
				$start_date,
				$end_date
			)
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return absint( $count );
	}

	/**
	 * Get daily offer (upsell/downsell) order count for a specific date.
	 *
	 * Counts orders with _cartflows_offer meta set to 'yes'.
	 * Supports both classic post-table and HPOS order storage.
	 *
	 * @since 2.2.4
	 * @param string $date Date in Y-m-d format.
	 * @return int
	 */
	private function get_daily_offer_orders_count( $date ) {
		global $wpdb;

		$start_date = $date . ' 00:00:00';
		$end_date   = $date . ' 23:59:59';

		if ( wcf()->utils->is_hpos_enabled() ) {
			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';
			$order_type_key   = 'type';
			$order_table_id   = 'id';
		} else {
			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';
			$order_type_key   = 'post_type';
			$order_table_id   = 'ID';
		}

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM $order_table o
				INNER JOIN $order_meta_table om ON o.$order_table_id = om.$order_id_key AND om.meta_key = '_cartflows_offer' AND om.meta_value = 'yes'
				WHERE o.$order_type_key = 'shop_order'
					AND o.$order_status_key IN ('wc-completed', 'wc-processing')
					AND o.$order_date_key >= %s
					AND o.$order_date_key <= %s",
				$start_date,
				$end_date
			)
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return absint( $count );
	}

	/**
	 * Retrieve numeric data statistics for CartFlows.
	 *
	 * @since 2.2.4
	 * @return array
	 */
	private function get_numeric_data_stats() {
		$steps_counts                = $this->get_all_steps_count();
		$flows_with_instant_checkout = $this->get_funnels_with_instant_layout();
		$custom_fields_data          = $this->get_custom_fields_enabled_data();

		$funnel_building_behavior = get_option(
			'cartflows_funnel_creation_method',
			array(
				'scratch'             => 0,
				'ready_made_template' => 0,
			)
		);

		return array(
			'total_flows'            => wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->publish,
			'total_ic_funnels'       => strval( $flows_with_instant_checkout ),
			'optin_step_count'       => strval( $steps_counts['optin'] ),
			'landing_step_count'     => strval( $steps_counts['landing'] ),
			'checkout_step_count'    => strval( $steps_counts['checkout'] ),
			'upsell_step_count'      => strval( $steps_counts['upsell'] ),
			'downsell_step_count'    => strval( $steps_counts['downsell'] ),
			'thankyou_step_count'    => strval( $steps_counts['thankyou'] ),
			'optin_custom_fields'    => strval( $custom_fields_data['optin'] ),
			'checkout_custom_fields' => strval( $custom_fields_data['checkout'] ),
			'funnels_from_scratch'   => strval( $funnel_building_behavior['scratch'] ),
			'funnels_from_template'  => strval( $funnel_building_behavior['ready_made_template'] ),
		);
	}

	/**
	 * Retrieve boolean data statistics for CartFlows.
	 *
	 * @since 2.2.4
	 * @param array $theme_data Array containing active theme information.
	 * @return array
	 */
	private function get_boolean_data_stats( $theme_data ) {
		$common_settings = Cartflows_Helper::get_common_settings();

		return array(
			'override-global-checkout'      => ! empty( $common_settings['override_global_checkout'] ) && 'enable' === $common_settings['override_global_checkout'] ? true : false,
			'disallow-indexing'             => ! empty( $common_settings['disallow_indexing'] ) && 'enable' === $common_settings['disallow_indexing'] ? true : false,
			'paypal-reference-transactions' => ! empty( $common_settings['paypal_reference_transactions'] ) && 'enable' === $common_settings['paypal_reference_transactions'],
			'cartflows-stats-report-emails' => 'enable' === get_option( 'cartflows_stats_report_emails', 'enable' ),
			'cartflows-delete-plugin-data'  => 'enable' === get_option( 'cartflows_delete_plugin_data' ),
			'pre-checkout-offer'            => 'enable' === $common_settings['pre_checkout_offer'],
			'store-checkout-set'            => ! empty( intval( Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) ) ),
			'is-child-theme'                => $theme_data['child_theme'],
			'suretriggers_active'           => is_plugin_active( 'suretriggers/suretriggers.php' ),
		);
	}

	// -------------------------------------------------------------------------
	// Helper methods (stats)
	// -------------------------------------------------------------------------

	/**
	 * Retrieve the active theme name.
	 *
	 * @since 2.2.4
	 * @return array {parent_theme: string, child_theme: bool}
	 */
	private function get_active_theme() {
		$theme             = wp_get_theme();
		$parent_theme_name = '';
		$child_theme_name  = '';

		if ( isset( $theme->parent_theme ) && ! empty( $theme->parent_theme ) ) {
			$parent_theme_name = $theme->parent_theme;
			$child_theme_name  = $theme->name ? $theme->name : '';
		} else {
			$parent_theme_name = $theme->name;
		}

		return array(
			'parent_theme' => ! empty( $child_theme_name ) ? $child_theme_name : $parent_theme_name,
			'child_theme'  => ! empty( $child_theme_name ) ? true : false,
		);
	}

	/**
	 * Get the count of each step type.
	 *
	 * @since 2.2.4
	 * @return array Counts keyed by step type slug.
	 */
	private function get_all_steps_count() {
		$default_step_counts = array(
			'optin'    => 0,
			'landing'  => 0,
			'checkout' => 0,
			'upsell'   => 0,
			'downsell' => 0,
			'thankyou' => 0,
		);

		global $wpdb;

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$step_counts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT JSON_OBJECTAGG(meta_value, count) as counts
				FROM (
					SELECT pm.meta_value, COUNT(*) as count
					FROM $wpdb->posts p
					INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
					WHERE p.post_type = %s
					AND p.post_status = %s
					AND pm.meta_key = %s
					AND pm.meta_value IN ('optin', 'landing', 'checkout', 'upsell', 'downsell', 'thankyou')
					GROUP BY pm.meta_value
				) as subquery",
				CARTFLOWS_STEP_POST_TYPE,
				'publish',
				'wcf-step-type'
			),
			ARRAY_A
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $step_counts ) || empty( $step_counts[0]['counts'] ) ) {
			return $default_step_counts;
		}

		return wp_parse_args( json_decode( $step_counts[0]['counts'], true ), $default_step_counts );
	}

	/**
	 * Retrieve the count of funnels with instant layout enabled.
	 *
	 * @since 2.2.4
	 * @return int
	 */
	private function get_funnels_with_instant_layout() {
		$args = array(
			'post_type'      => CARTFLOWS_FLOW_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'no_found_rows'  => false,
			'fields'         => 'ids',
			'meta_query'     => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Meta query required to count instant layout funnels.
				array(
					'key'     => 'instant-layout-style',
					'value'   => 'yes',
					'compare' => 'LIKE',
				),
			),
		);

		$query       = new WP_Query( $args );
		$posts_count = isset( $query->found_posts ) ? $query->found_posts : 0;

		wp_reset_postdata();

		return $posts_count;
	}

	/**
	 * Retrieve the count of steps with custom fields enabled, by type.
	 *
	 * @since 2.2.4
	 * @return array {optin: int, checkout: int}
	 */
	private function get_custom_fields_enabled_data() {
		$default_step_counts = array(
			'optin'    => 0,
			'checkout' => 0,
		);

		global $wpdb;

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$step_counts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT JSON_OBJECTAGG(subquery.step_type, subquery.count) AS counts
				FROM ( SELECT step.meta_value AS step_type,
						COUNT(DISTINCT p.ID) AS count
					FROM $wpdb->posts p
					INNER JOIN $wpdb->postmeta step
						ON p.ID = step.post_id
						AND step.meta_key = %s
						AND step.meta_value IN ('optin', 'checkout')
					INNER JOIN $wpdb->postmeta custom
						ON p.ID = custom.post_id
						AND (
							(step.meta_value = 'optin' AND custom.meta_key = %s)
							OR
							(step.meta_value = 'checkout' AND custom.meta_key = %s)
						)
						AND custom.meta_value = %s
					WHERE p.post_type = %s
					AND p.post_status = %s
					GROUP BY step.meta_value
				) AS subquery",
				'wcf-step-type',
				'wcf-optin-enable-custom-fields',
				'wcf-custom-checkout-fields',
				'yes',
				CARTFLOWS_STEP_POST_TYPE,
				'publish'
			),
			ARRAY_A
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $step_counts ) || empty( $step_counts[0]['counts'] ) ) {
			return $default_step_counts;
		}

		return wp_parse_args( json_decode( $step_counts[0]['counts'], true ), $default_step_counts );
	}

	/**
	 * Get the list of enabled payment gateway slugs.
	 *
	 * @since 2.2.4
	 * @return array
	 */
	private function get_active_gateways() {
		if ( ! wcf()->is_woo_active ) {
			return array();
		}

		$gateways         = WC()->payment_gateways->get_available_payment_gateways();
		$enabled_gateways = array();

		if ( is_array( $gateways ) ) {
			foreach ( $gateways as $gateway ) {
				if ( 'yes' === $gateway->enabled ) {
					$gateway_key                      = strtolower( str_replace( array( ' ', '(', ')' ), '_', $gateway->get_title() ) );
					$enabled_gateways[ $gateway_key ] = $gateway->get_title();
				}
			}
		}

		return $enabled_gateways;
	}

	/**
	 * Get social tracking enabled/disabled boolean flags for each platform.
	 *
	 * @since 2.2.4
	 * @return array
	 */
	private function get_social_tracking_flags() {
		$fb_setting         = Cartflows_Helper::get_facebook_settings();
		$ga_setting         = Cartflows_Helper::get_google_analytics_settings();
		$tik_pixel_settings = Cartflows_Helper::get_tiktok_settings();
		$pinterest_settings = Cartflows_Helper::get_pinterest_settings();
		$gads_settings      = Cartflows_Helper::get_google_ads_settings();
		$snapchat_settings  = Cartflows_Helper::get_snapchat_settings();

		return array(
			'fb_pixel_enabled'  => ! empty( $fb_setting['facebook_pixel_id'] ),
			'ga_enabled'        => ! empty( $ga_setting['google_analytics_id'] ),
			'tiktok_enabled'    => ! empty( $tik_pixel_settings['tiktok_pixel_id'] ),
			'pinterest_enabled' => ! empty( $pinterest_settings['pinterest_tag_id'] ),
			'gads_enabled'      => ! empty( $gads_settings['google_ads_id'] ),
			'snapchat_enabled'  => ! empty( $snapchat_settings['snapchat_pixel_id'] ),
		);
	}

	/**
	 * Check if CartFlows Pro license key exists.
	 *
	 * @since 2.2.4
	 * @return bool
	 */
	private function check_pro_license_key_exists() {
		if ( _is_cartflows_pro() && class_exists( 'CartFlows_Pro_Licence' ) ) {
			$license_data = get_option( 'wc_am_client_' . \CartFlows_Pro_Licence::get_instance()->product_id . '_api_key', array() );
			return ! empty( $license_data['api_key'] );
		}
		return false;
	}

	// -------------------------------------------------------------------------
	// Milestone event tracking
	// -------------------------------------------------------------------------

	/**
	 * Detect and queue state-based one-time events.
	 *
	 * Called once per day (throttled by transient). BSF_Analytics_Events dedup
	 * prevents duplicate tracking across days.
	 *
	 * @since 2.2.4
	 * @return void
	 */
	private function detect_state_events() {
		$events = self::events();
		if ( null === $events ) {
			// BSF_Analytics_Events not yet loaded — do NOT set transient so we retry
			// on the next admin page load.
			return;
		}

		// Class is available — throttle for 24 hours.
		set_transient( 'cf_state_events_checked', 1, DAY_IN_SECONDS );

		// plugin_activated — fires once on first admin load after activation.
		$bsf_referrers = get_option( 'bsf_product_referers', array() );
		$source        = ! empty( $bsf_referrers['cartflows'] ) ? sanitize_text_field( $bsf_referrers['cartflows'] ) : 'self';
		$events->track( 'plugin_activated', CARTFLOWS_VER, array( 'source' => $source ) );

		// plugin_updated — fires when version changes; re-tracks on each upgrade.
		$stored_version = Cartflows_Helper::get_analytics_flag( 'tracked_version', '' );
		if ( CARTFLOWS_VER !== $stored_version ) {
			if ( ! empty( $stored_version ) ) {
				$events->flush_pushed( array( 'plugin_updated' ) );
				$events->track(
					'plugin_updated',
					CARTFLOWS_VER,
					array( 'from_version' => $stored_version )
				);
			}
			Cartflows_Helper::set_analytics_flag( 'tracked_version', CARTFLOWS_VER, true );
		}

		// onboarding_completed.
		if ( get_option( 'wcf_setup_complete', false ) ) {
			$events->track( 'onboarding_completed' );
		}

		// onboarding_skipped.
		if ( get_option( 'wcf_setup_skipped', false ) ) {
			$exit_step = get_option( 'wcf_exit_setup_step', '' );
			$events->track(
				'onboarding_skipped',
				'',
				array( 'exit_step' => sanitize_text_field( (string) $exit_step ) )
			);
		}

		// pro_license_activated.
		if ( _is_cartflows_pro() ) {
			$events->track( 'pro_license_activated' );
		}

		// ---- Feature usage events ----

		// first_checkout_configured (flag set in admin-core/inc/meta-ops.php).
		$checkout_step_id = Cartflows_Helper::get_analytics_flag( 'first_checkout_configured' );
		if ( $checkout_step_id ) {
			$layout = get_post_meta( intval( $checkout_step_id ), 'wcf-checkout-layout', true );
			$events->track(
				'first_checkout_configured',
				strval( $checkout_step_id ),
				array( 'layout' => $layout ? sanitize_text_field( $layout ) : 'default' )
			);
		}

		// first_template_imported (flag set in admin-core/ajax/importer.php).
		if ( Cartflows_Helper::get_analytics_flag( 'first_template_imported' ) ) {
			$page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );
			$events->track(
				'first_template_imported',
				'',
				array( 'page_builder' => $page_builder ? sanitize_text_field( $page_builder ) : 'default' )
			);
		}

		// ---- Pro feature events (flags set in cartflows-pro) ----

		if ( Cartflows_Helper::get_analytics_flag( 'first_order_bump_created' ) ) {
			$events->track( 'first_order_bump_created' );
		}

		if ( Cartflows_Helper::get_analytics_flag( 'first_upsell_accepted' ) ) {
			$events->track( 'first_upsell_accepted' );
		}

		if ( Cartflows_Helper::get_analytics_flag( 'first_ab_test_started' ) ) {
			$events->track( 'first_ab_test_started' );
		}

		if ( Cartflows_Helper::get_analytics_flag( 'first_ab_test_winner' ) ) {
			$events->track( 'first_ab_test_winner_declared' );
		}

		// ---- Integration events (state-based, no flag-setters needed) ----

		$tracking_flags = $this->get_social_tracking_flags();

		if ( ! empty( $tracking_flags['fb_pixel_enabled'] ) ) {
			$events->track( 'fb_pixel_connected' );
		}

		if ( ! empty( $tracking_flags['ga_enabled'] ) ) {
			$events->track( 'ga_connected' );
		}

		if ( wcf()->is_woo_active ) {
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			foreach ( $gateways as $gateway_id => $gateway ) {
				if ( 'yes' !== $gateway->enabled ) {
					continue;
				}
				if ( false !== strpos( $gateway_id, 'stripe' ) ) {
					$events->track( 'stripe_gateway_enabled' );
				}
				if ( false !== strpos( $gateway_id, 'paypal' ) ) {
					$events->track( 'paypal_gateway_enabled' );
				}
			}
		}

		if ( ! empty( $tracking_flags['tiktok_enabled'] ) ) {
			$events->track( 'tiktok_connected' );
		}

		if ( ! empty( $tracking_flags['pinterest_enabled'] ) ) {
			$events->track( 'pinterest_connected' );
		}

		if ( ! empty( $tracking_flags['gads_enabled'] ) ) {
			$events->track( 'gads_connected' );
		}

		if ( ! empty( $tracking_flags['snapchat_enabled'] ) ) {
			$events->track( 'snapchat_connected' );
		}

		// ---- Pro feature state events ----

		// first_downsell_accepted (flag set in cartflows-pro modules/downsell/classes/class-cartflows-downsell-markup.php).
		if ( Cartflows_Helper::get_analytics_flag( 'first_downsell_accepted' ) ) {
			$events->track( 'first_downsell_accepted' );
		}

		// first_instant_layout_enabled (flag set in admin-core/ajax/flows.php and admin-core/ajax/importer.php).
		if ( Cartflows_Helper::get_analytics_flag( 'first_instant_layout_enabled' ) ) {
			$events->track( 'first_instant_layout_enabled' );
		}

		// first_store_checkout_set — detected directly from the store checkout option.
		$store_checkout_setting = Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' );
		if ( is_scalar( $store_checkout_setting ) && intval( $store_checkout_setting ) > 0 ) {
			$events->track( 'first_store_checkout_set' );
		}

		// first_webhook_configured (flag set in admin-core/api/webhooks.php::create_webhook()).
		if ( Cartflows_Helper::get_analytics_flag( 'first_webhook_configured' ) ) {
			$events->track( 'first_webhook_configured' );
		}

		// ---- Engagement events ----

		$pointer_data = get_option( 'cartflows_pointer_data', array() );

		if ( ! empty( $pointer_data['accepted'] ) ) {
			$events->track( 'pointer_accepted' );
		}

		if ( ! empty( $pointer_data['dismissed'] ) ) {
			$events->track( 'pointer_dismissed' );
		}

		// weekly_report_notice_dismissed (flag set in class-cartflows-admin-notices.php).
		if ( Cartflows_Helper::get_analytics_flag( 'weekly_report_notice_dismissed' ) ) {
			$events->track( 'weekly_report_notice_dismissed' );
		}

		// instant_checkout_notice_dismissed (option set in admin-core/ajax/flows.php).
		if ( 'yes' === get_option( 'wcf-instant-checkout-notice-skipped' ) ) {
			$events->track( 'instant_checkout_notice_dismissed' );
		}
	}

	/**
	 * Track first flow published.
	 *
	 * Fires on transition_post_status when a cartflows_flow moves to 'publish'.
	 * BSF_Analytics_Events dedup ensures this fires only once per site lifecycle.
	 *
	 * @since 2.2.4
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public function track_first_flow_published( $new_status, $old_status, $post ) {
		if ( CARTFLOWS_FLOW_POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		$events = self::events();
		if ( null === $events ) {
			return;
		}

		// days_since_install for time-to-value analysis.
		$install_time = (int) Cartflows_Helper::get_analytics_flag( 'usage_installed_time', 0 );
		$days         = 0;
		if ( $install_time > 0 ) {
			$days = (int) floor( ( time() - $install_time ) / DAY_IN_SECONDS );
		}

		// Flow source: template or scratch.
		// Read per-flow post meta written at creation time so each flow reports its own source.
		$creation_method = get_post_meta( $post->ID, 'wcf-flow-creation-method', true );
		$source          = ( 'ready_made_template' === $creation_method ) ? 'template' : 'scratch';

		// Step count for this flow.
		$steps      = get_post_meta( $post->ID, 'wcf-steps', true );
		$step_count = is_array( $steps ) ? count( $steps ) : 0;

		$events->track(
			'first_flow_published',
			strval( $post->ID ),
			array(
				'days_since_install' => (string) $days,
				'source'             => $source,
				'step_count'         => (string) $step_count,
			)
		);
	}
}

Cartflows_Analytics::get_instance();
