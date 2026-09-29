<?php
/**
 * Cart Abandonent Recovery Dashboard.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Dashboard.
 */
class Dashboard extends ApiBase {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/dashboard/';

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 2.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init Hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_routes(): void {

		register_rest_route(
			$this->get_api_namespace(),
			$this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE, // WP_REST_Server::READABLE.
					'callback'            => [ $this, 'get_dashboard_data' ],
					'permission_callback' => [ $this, 'get_permissions_check' ],
					'args'                => [], // get_collection_params may use.
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Check whether a given request has permission to read data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function get_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new \WP_Error( 'woo_cart_abandonment_recovery_rest_cannot_view', __( 'Sorry, you don\'t have the access to perform this action.', 'woo-cart-abandonment-recovery' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	/**
	 * Get dashboard data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_dashboard_data( $request ) {
		global $wpdb;

		$cart_abandonment_table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;
		$email_history_table    = $wpdb->prefix . CARTFLOWS_CA_EMAIL_HISTORY_TABLE;

		// Get date range for filtering.
		$date_range = $request['date_range'] ?? '';

		if ( is_array( $date_range ) && isset( $date_range['from'], $date_range['to'] ) ) {
			$start_date = sanitize_text_field( $date_range['from'] );
			$end_date   = sanitize_text_field( $date_range['to'] );
		} else {
			// Default to last 7 days.
			$end_date   = gmdate( 'Y-m-d' );
			$start_date = gmdate( 'Y-m-d', strtotime( '-6 days' ) );
		}

		// Format dates for SQL query.
		$start_date_obj = new \DateTime( $start_date );
		$start_date_obj->setTime( 0, 0, 0 );
		$start_date_str = $start_date_obj->format( 'Y-m-d H:i:s' );

		$end_date_obj = new \DateTime( $end_date );
		$end_date_obj->setTime( 23, 59, 59 );
		$end_date_str = $end_date_obj->format( 'Y-m-d H:i:s' );

		$helper           = wcf_ca()->helper;
		$abandoned_report = $helper->get_report_by_type( $start_date, $end_date, WCF_CART_ABANDONED_ORDER );
		$recovered_report = $helper->get_report_by_type( $start_date, $end_date, WCF_CART_COMPLETED_ORDER );
		$lost_report      = $helper->get_report_by_type( $start_date, $end_date, WCF_CART_LOST_ORDER );

		$total_orders = $recovered_report['no_of_orders'] + $abandoned_report['no_of_orders'] + $lost_report['no_of_orders'];

		$conversion_rate = $total_orders ? $recovered_report['no_of_orders'] / $total_orders * 100 : 0;
		$conversion_rate = number_format_i18n( $conversion_rate, 2 ) . '%';

		$previous_period_data = $this->get_previous_period_dashboard_data( $start_date_obj, $end_date_obj );
		// Get revenue chart data.
		$revenue_chart_data = $this->get_revenue_chart_data( $start_date_str, $end_date_str );

		// Get recent email logs.
		$recent_email_logs = $this->get_recent_email_logs( 5 );

		$response = [
			'recoverable_orders'       => esc_html( $abandoned_report['no_of_orders'] ),
			'recovered_orders'         => esc_html( $recovered_report['no_of_orders'] ),
			'lost_orders'              => esc_html( $lost_report['no_of_orders'] ),

			'recoverable_revenue'      => wc_price( ! empty( $abandoned_report['revenue'] ) || ! is_null( $abandoned_report['revenue'] ) ? esc_html( floatval( $abandoned_report['revenue'] ) ) : '0.0' ),
			'recovered_revenue'        => wc_price( ! empty( $recovered_report['revenue'] ) || ! is_null( $recovered_report['revenue'] ) ? esc_html( floatval( $recovered_report['revenue'] ) ) : '0.0' ),
			'recovered_revenue_amount' => floatval( $recovered_report['revenue'] ?? 0 ),
			'recovery_rate'            => $conversion_rate,

			'revenue_chart_data'       => $revenue_chart_data,
			'recent_follow_up_reports' => $recent_email_logs,
			'product_report'           => [],
			'previous_period'          => $previous_period_data,
			'duration'                 => [
				'start' => $start_date,
				'end'   => $end_date,
			],
		];

		$response = apply_filters( 'wcar_dashboard_stats_data', $response, $start_date_str, $end_date_str );

		return rest_ensure_response( $response );
	}

	/**
	 * Get dashboard data.
	 *
	 * @param  \DateTime $start_date Start date.
	 * @param  \DateTime $end_date   End date.
	 * @return array
	 */
	public function get_previous_period_dashboard_data( $start_date, $end_date ) {
		$interval = $start_date->diff( $end_date );
		$days     = $interval->days;

		$prev_start_obj = clone $start_date;
		$prev_start_obj->modify( '-' . ( $days + 1 ) . ' days' );
		$prev_end_obj = clone $start_date;
		$prev_end_obj->modify( '-1 days' );

		$prev_start_date = $prev_start_obj->format( 'Y-m-d' );
		$prev_end_date   = $prev_end_obj->format( 'Y-m-d' );

		$helper                = wcf_ca()->helper;
		$prev_abandoned_report = $helper->get_report_by_type( $prev_start_date, $prev_end_date, WCF_CART_ABANDONED_ORDER );
		$prev_recovered_report = $helper->get_report_by_type( $prev_start_date, $prev_end_date, WCF_CART_COMPLETED_ORDER );
		$prev_lost_report      = $helper->get_report_by_type( $prev_start_date, $prev_end_date, WCF_CART_LOST_ORDER );
		$prev_total_orders     = $prev_abandoned_report['no_of_orders'] + $prev_recovered_report['no_of_orders'] + $prev_lost_report['no_of_orders'];
		$prev_conversion_rate  = $prev_total_orders ? $prev_recovered_report['no_of_orders'] / $prev_total_orders * 100 : 0;
		$prev_conversion_rate  = number_format_i18n( $prev_conversion_rate, 2 ) . '%';
		
		return [
			'start_date'               => $prev_start_date,
			'end_date'                 => $prev_end_date,
			'recoverable_orders'       => esc_html( $prev_abandoned_report['no_of_orders'] ),
			'recovered_orders'         => esc_html( $prev_recovered_report['no_of_orders'] ),
			'lost_orders'              => esc_html( $prev_lost_report['no_of_orders'] ),
			'recoverable_revenue'      => wc_price( ! empty( $prev_abandoned_report['revenue'] ) || ! is_null( $prev_abandoned_report['revenue'] ) ? esc_html( floatval( $prev_abandoned_report['revenue'] ) ) : '0.0' ),
			'recovered_revenue'        => wc_price( ! empty( $prev_recovered_report['revenue'] ) || ! is_null( $prev_recovered_report['revenue'] ) ? esc_html( floatval( $prev_recovered_report['revenue'] ) ) : '0.0' ),
			'recovered_revenue_amount' => floatval( $prev_recovered_report['revenue'] ?? 0 ),
			'recovery_rate'            => $prev_conversion_rate,
		];
	}

	/**
	 * Get revenue chart data.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	private function get_revenue_chart_data( $start_date, $end_date ) {
		global $wpdb;

		// Calculate the number of days between start and end date.
		$start_date_obj = new \DateTime( $start_date );
		$end_date_obj   = new \DateTime( $end_date );
		$interval       = $start_date_obj->diff( $end_date_obj );
		$days           = $interval->days + 1; // Include both start and end dates.

		$chart_data = [];

		// Generate data for each day.
		for ( $i = 0; $i < $days; $i++ ) {
			$current_date = clone $start_date_obj;
			$current_date->modify( "+{$i} days" );
			$day_start  = $current_date->format( 'Y-m-d 00:00:00' );
			$day_end    = $current_date->format( 'Y-m-d 23:59:59' );
			$day_number = $current_date->format( 'F j' ); // e.g., June 27.

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			// Get recoverable revenue for the day.
			$recoverable = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(cart_total) FROM {$wpdb->prefix}" . CARTFLOWS_CA_CART_ABANDONMENT_TABLE . ' WHERE order_status = %s AND time >= %s AND time <= %s',
					WCF_CART_ABANDONED_ORDER,
					$day_start,
					$day_end
				)
			); // Direct DB query needed for performance with aggregation.
			$recoverable = floatval( $recoverable );

			// Get recovered revenue for the day.
			$recovered = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(cart_total) FROM {$wpdb->prefix}" . CARTFLOWS_CA_CART_ABANDONMENT_TABLE . ' WHERE order_status = %s AND time >= %s AND time <= %s',
					WCF_CART_COMPLETED_ORDER,
					$day_start,
					$day_end
				)
			); // Direct DB query needed for performance with aggregation.
			// phpcs:enable
			$recovered = floatval( $recovered );

			$chart_data[] = [
				'day'         => $day_number,
				'recoverable' => $recoverable,
				'recovered'   => $recovered,
			];
		}

		return $chart_data;
	}

	/**
	 * Get recent email logs.
	 *
	 * @param int $limit Number of logs to retrieve.
	 * @return array
	 */
	private function get_recent_email_logs( $limit = 5 ) {
		global $wpdb;
		$cart_abandonment_table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		// Fetch the most recent records (limit by $limit).
		$records = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$cart_abandonment_table} ORDER BY id DESC LIMIT %d", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$limit
			)
		);

		$logs = [];
		foreach ( $records as $record ) {
			// Use robust unserialization for other_fields.
			$fields     = wcf_ca()->helper->wcar_safe_unserialize( $record->other_fields );
			$first_name = $fields['wcf_first_name'] ?? ( $fields['wcf_shipping_first_name'] ?? '' );
			$last_name  = $fields['wcf_last_name'] ?? ( $fields['wcf_shipping_last_name'] ?? '' );
			$name       = trim( $first_name . ' ' . $last_name );
			$country    = $fields['wcf_country'] ?? ( $fields['wcf_shipping_country'] ?? '' );

			$date = new \DateTime( $record->time );
			$date = $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

			$logs[] = [
				'id'          => (int) $record->id,
				'userName'    => $name,
				'email'       => $record->email,
				'cartTotal'   => html_entity_decode( wp_strip_all_tags( wc_price( $record->cart_total ) ) ),
				'orderStatus' => $this->get_order_status_label( $record->order_status ),
				'country'     => $country,
				'dateTime'    => $date,
			];
		}

		return $logs;
	}

	/**
	 * Get order status label.
	 *
	 * @param string $status Order status.
	 * @return string
	 */
	private function get_order_status_label( $status ) {
		switch ( $status ) {
			case WCF_CART_ABANDONED_ORDER:
				return 'Abandoned';
			case WCF_CART_COMPLETED_ORDER:
				return 'Successful';
			case WCF_CART_LOST_ORDER:
			case WCF_CART_FAILED_ORDER:
				return 'Failed';
			case WCF_CART_BLACKLISTED_ORDER:
				return 'Blacklisted';
			default:
				return 'Normal';
		}
	}
}
