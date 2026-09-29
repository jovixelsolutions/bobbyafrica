<?php
/**
 * Ajax Setting handler.
 *
 * @package WooCommerceCartAbandonmentRecovery
 */

namespace WCAR\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Ajax_Setting
 */
class Ajax_Setting extends Ajax_Base {
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
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events(): void {

		if ( current_user_can( 'manage_woocommerce' ) ) {

			$ajax_events = [
				'save_setting',
			];
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Save the setting value.
	 */
	public function save_setting(): void {

		$response_data = [ 'messsage' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'messsage' => __( 'No post data found!', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'wcar_save_setting', 'security', false ) ) {
			$response_data = [ 'messsage' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		$option = isset( $_POST['option_name'] ) ? sanitize_text_field( wp_unslash( $_POST['option_name'] ) ) : '';
		$value  = isset( $_POST['value'] ) ? wp_unslash( $_POST['value'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in save_meta_fields function.

		if ( empty( $option ) ) {
			$response_data = [ 'messsage' => $this->get_error_msg( 'option' ) ];
			wp_send_json_error( $response_data );
		}

		// Add special case for wcar_usage_optin.
		if ( 'wcar_usage_optin' === $option && 'on' === $value ) {
			$value = 'yes'; // We have to change the value of Analytics toggle to Yes or blank as per the library requirement.
		}

		$success = wcf_ca()->helper->save_meta_fields( $option, $value );

		if ( $success ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to save option', 'woo-cart-abandonment-recovery' ) ] );
		}
	}

}
