<?php
/**
 * Cart Abandonment Recovery Follow-up Report AJAX Handler.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ajax_Follow_Up_Report.
 */
class Ajax_Follow_Up_Report extends Ajax_Base {
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
	 * Register ajax events.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_ajax_events(): void {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$ajax_events = [
				'delete_follow_up_reports',
				'unsubscribe_follow_up_reports',
			];
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Delete follow-up reports.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function delete_follow_up_reports(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'wcar_delete_follow_up_reports', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		global $wpdb;
		$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];

		if ( empty( $ids ) ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => __( 'Failed to delete report(s)', 'woo-cart-abandonment-recovery' ),
				]
			);
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN({$placeholders})", ...$ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

		wp_send_json_success(
			[
				'success' => true,
				'message' => __( 'Report(s) deleted successfully.', 'woo-cart-abandonment-recovery' ),
			]
		);
	}

	/**
	 * Unsubscribe follow-up reports.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function unsubscribe_follow_up_reports(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'wcar_unsubscribe_follow_up_reports', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		global $wpdb;
		$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : [];

		if ( empty( $ids ) ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => __( 'Failed to unsubscribe report(s)', 'woo-cart-abandonment-recovery' ),
				]
			);
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET unsubscribed = 1 WHERE id IN({$placeholders})", ...$ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

		wp_send_json_success(
			[
				'success' => true,
				'message' => __( 'Report(s) unsubscribed successfully.', 'woo-cart-abandonment-recovery' ),
			]
		);
	}
}
