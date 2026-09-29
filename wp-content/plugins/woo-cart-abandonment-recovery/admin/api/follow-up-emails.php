<?php
/**
 * Cart Abandonment Recovery Follow Up Emails API.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FollowUpEmails.
 */
class FollowUpEmails extends ApiBase {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/followup-emails/';

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
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'get_email_templates' ],
					'permission_callback' => [ $this, 'get_permissions_check' ],
					'args'                => [],
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
	 * Get email templates data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_email_templates( $request ) {
		global $wpdb;

		$email_templates_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
		$email_history_table   = $wpdb->prefix . CARTFLOWS_CA_EMAIL_HISTORY_TABLE;

		// Get all email templates.
		$templates = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT * FROM {$email_templates_table} ORDER BY id DESC", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$formatted_templates = [];

		$email_templates = \Cartflows_Ca_Email_Templates::get_instance();
		foreach ( $templates as $template ) {
			// Get email stats.
			$sent_count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$email_history_table} WHERE template_id = %d AND email_sent = 1", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$template['id']
				)
			);

			// Fetch all meta for this template.
			$meta = $email_templates->get_all_email_template_meta( $template['id'] );

			$formatted_templates[] = array_merge(
				[
					'id'                   => $template['id'],
					'template_name'        => $template['template_name'],
					'email_subject'        => $template['email_subject'],
					'email_body'           => wpautop( $template['email_body'] ),
					'is_activated'         => boolval( $template['is_activated'] ) ? 'on' : '',
					'email_frequency'      => $template['frequency'],
					'email_frequency_unit' => $template['frequency_unit'],
					'sent'                 => (int) $sent_count,
					'open_rate'            => 0, // These would need to be calculated from email tracking data.
					'click_rate'           => 0,
					'conversion_rate'      => 0,
					'unsubscribe_rate'     => 0,
				],
				$meta
			);
		}

		// Apply filter to allow PRO plugin to add tracking data.
		if ( function_exists( '_is_wcar_pro' ) && \_is_wcar_pro() ) {
			$formatted_templates = apply_filters( 'wcar_followup_emails_data', $formatted_templates );
		}

		return rest_ensure_response( $formatted_templates );
	}
}
