<?php
/**
 * Follow Up Reports API.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FollowUp.
 */
class FollowUp extends ApiBase {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/follow-up/';

	/**
	 * Instance of the class.
	 *
	 * @var FollowUp
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @return FollowUp
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register API routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->get_api_namespace(),
			$this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'get_records' ],
					'permission_callback' => [ $this, 'get_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function get_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new \WP_Error( 'woo_cart_abandonment_recovery_rest_cannot_view', __( 'Sorry, you don\'t have the access to perform this action.', 'woo-cart-abandonment-recovery' ), [ 'status' => rest_authorization_required_code() ] );
		}
		return true;
	}

	/**
	 * Get follow up records.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_records( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$records = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$total = count( $records );

		$items = array_map( [ $this, 'prepare_record' ], $records );

		return rest_ensure_response(
			[
				'items'       => $items,
				'total_items' => (int) $total,
			]
		);
	}

	/**
	 * Format order status label.
	 *
	 * @param string $status Status code.
	 * @return string
	 */
	private function get_status_label( $status ) {
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

	/**
	 * Convert DB record to response format.
	 *
	 * @param object $record DB record.
	 * @return array
	 */
	private function prepare_record( $record ) {
		$fields     = maybe_unserialize( $record->other_fields );
		$first_name = $fields['wcf_first_name'] ?? ( $fields['wcf_shipping_first_name'] ?? '' );
		$last_name  = $fields['wcf_last_name'] ?? ( $fields['wcf_shipping_last_name'] ?? '' );
		$name       = trim( $first_name . ' ' . $last_name );
		$country    = $fields['wcf_country'] ?? ( $fields['wcf_shipping_country'] ?? '' );

		$date = new \DateTime( $record->time );
		$date = $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		return [
			'id'           => (int) $record->id,
			'userName'     => $name,
			'email'        => $record->email,
			'cartTotal'    => html_entity_decode( wp_strip_all_tags( wc_price( $record->cart_total ) ) ),
			'orderStatus'  => $this->get_status_label( $record->order_status ),
			'country'      => $country,
			'dateTime'     => $date,
			'unsubscribed' => (int) $record->unsubscribed,
		];
	}
}
