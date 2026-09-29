<?php
/**
 * Auth ajax actions.
 *
 * @package AiBuilder
 */

namespace AiBuilder\Inc\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AiBuilder\Inc\Classes\Zipwp\Ai_Builder_ZipWP_Api;
use AiBuilder\Inc\Classes\Zipwp\Ai_Builder_ZipWP_Integration;
use AiBuilder\Inc\Traits\Instance;

/**
 * Class Auth.
 */
class Auth extends AjaxBase {
	use Instance;

	/**
	 * Ajax Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.2.73
	 */
	private static $ajax_instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.2.73
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( null === self::$ajax_instance ) {
			self::$ajax_instance = new self();
		}
		return self::$ajax_instance;
	}

	/**
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events() {

		$ajax_events = array(
			'save_auth_token',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Save auth token received from popup authentication and return plan data.
	 *
	 * @since 1.2.73
	 * @return void
	 */
	public function save_auth_token() {

		check_ajax_referer( 'astra-sites', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'data'   => __( 'You do not have permission to do this action.', 'astra-sites' ),
					'status' => false,
				)
			);
		}

		$token        = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
		$credit_token = sanitize_text_field( wp_unslash( $_POST['credit_token'] ?? '' ) );
		$email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

		if ( empty( $token ) || empty( $credit_token ) || empty( $email ) ) {
			wp_send_json_error(
				array(
					'data'   => __( 'Missing required authentication data.', 'astra-sites' ),
					'status' => false,
				)
			);
		}

		$spec_ai_settings = (array) Ai_Builder_ZipWP_Integration::get_setting();

		$spec_ai_settings['auth_token'] = Ai_Builder_ZipWP_Integration::encrypt( $credit_token );
		$spec_ai_settings['zip_token']  = Ai_Builder_ZipWP_Integration::encrypt( $token );
		$spec_ai_settings['email']      = $email;

		update_option( 'zip_ai_settings', $spec_ai_settings );

		// Fetch fresh plan data so the frontend can update immediately.
		$plans     = Ai_Builder_ZipWP_Api::Instance()->get_zip_plans();
		$plan_data = $plans && isset( $plans['data'] ) ? $plans['data'] : array();

		wp_send_json_success(
			array(
				'status'    => true,
				'zip_plans' => $plan_data,
			)
		);
	}
}
