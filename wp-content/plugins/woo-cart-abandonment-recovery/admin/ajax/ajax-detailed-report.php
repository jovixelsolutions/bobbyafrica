<?php
/**
 * Cart Abandonment Recovery Detailed Report AJAX Handler.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ajax_Detailed_Report.
 */
class Ajax_Detailed_Report extends Ajax_Base {
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
				'reschedule_emails',
			];
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Reschedule emails.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function reschedule_emails(): void {
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
		if ( ! check_ajax_referer( 'wcar_reschedule_emails', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

		if ( empty( $session_id ) ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => __( 'Failed to reschedule emails', 'woo-cart-abandonment-recovery' ),
				]
			);
		}

		\Cartflows_Ca_Email_Schedule::get_instance()->schedule_emails( $session_id, true );
		$scheduled_emails = wcf_ca()->helper->fetch_scheduled_emails( $session_id );

		wp_send_json_success(
			[
				'success'          => true,
				'scheduled_emails' => $scheduled_emails,
			]
		);
	}
}
