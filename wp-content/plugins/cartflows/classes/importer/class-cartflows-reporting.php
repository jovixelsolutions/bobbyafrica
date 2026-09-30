<?php
/**
 * Reporting error
 *
 * @since 3.1.3
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reporting error
 */
class CartFlows_Reporting {

	/**
	 * Instance
	 *
	 * @since 3.1.3
	 * @access private
	 * @var self Class object.
	 */
	private static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 3.1.3
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 3.1.3
	 */
	public function __construct() {
		add_action( 'cartflows_generate_analytics_lead', array( $this, 'schedule_reporting_event' ), 10, 2 );
		add_action( 'cartflows_track_template_import_lead', array( $this, 'send_analytics_lead' ), 10, 2 );
	}

	/**
	 * Schedule the reporting of Error.
	 *
	 * @param array<string, mixed> $flow Flow data.
	 * @param bool                 $is_error Is error.
	 * @since 3.1.3
	 * @return void
	 */
	public function schedule_reporting_event( $flow, $is_error ) {
		$has_sent_error_report = get_option( 'cartflows_has_sent_error_report', 'no' );
		if ( 'no' === $has_sent_error_report ) {
			// Schedule and event in next 30 secs to send error report.
			wp_schedule_single_event( time() + 30, 'cartflows_track_template_import_lead', array( $flow, $is_error ) );
		}
	}

	/**
	 * Send Error.
	 *
	 * @param array<string, mixed> $flow Flow data.
	 * @param bool                 $status Status of the import.
	 * @since 3.1.3
	 * @return void
	 */
	public function send_analytics_lead( $flow, $status ) {

		if ( ! is_array( $flow ) ) {
			return;
		}

		$remote_flow_id = ( isset( $flow['ID'] ) && is_scalar( $flow['ID'] ) ) ? (string) $flow['ID'] : '';

		// Nothing to attribute the import to without the remote template ID.
		if ( empty( $remote_flow_id ) ) {
			return;
		}

		$default_builder = \Cartflows_Helper::get_common_setting( 'default_page_builder' );
		$page_builder    = ( ! empty( $flow['page_builder'] ) && is_string( $flow['page_builder'] ) ) ? $flow['page_builder'] : ( is_string( $default_builder ) ? $default_builder : 'gutenberg' );

		$report_data = array(
			'id'            => $remote_flow_id,
			'page_builder'  => $page_builder,
			'import_status' => $status,
			'version'       => CARTFLOWS_VER,
		);

		$this->report( $report_data );

		delete_option( 'cartflows_has_sent_error_report' );
	}

	/**
	 * Report Error.
	 * 
	 * @param array<string, mixed> $data Error data.
	 * @since 3.1.3
	 * 
	 * @return array<string, mixed>
	 */
	public function report( $data ) {
		$id            = isset( $data['id'] ) ? $data['id'] : 0;
		$import_status = isset( $data['import_status'] ) ? sanitize_text_field( $data['import_status'] ) : 'true';
		$type          = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'astra-sites';
		$page_builder  = isset( $data['page_builder'] ) ? sanitize_text_field( $data['page_builder'] ) : 'gutenberg';
		$error_text    = isset( $data['error_text'] ) ? sanitize_text_field( $data['error_text'] ) : '';

		$api_args = array(
			'timeout'  => 60, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			'blocking' => true,
			'body'     => array(
				'url'           => esc_url( site_url() ),
				'import_status' => $import_status,
				'id'            => $id,
				'version'       => CARTFLOWS_VER,
				'type'          => $type,
				'builder'       => $page_builder,
				'error_text'    => $error_text,
			),
		);

		$import_analytics_url = CARTFLOWS_TEMPLATES_URL . 'wp-json/cartflows-server/v1/import';

		$request = wp_safe_remote_post( $import_analytics_url, $api_args );

		/**
		 * Action fired after analytics reporting.
		 *
		 * @param array<string, mixed>      $data    The data sent for reporting.
		 * @param WP_Error|array<string,mixed> $request The response from the reporting request.
		 *
		 * @since 3.1.3
		 */
		do_action( 'cartflows_after_analytics_reporting', $data, $request );

		if ( is_wp_error( $request ) ) {
			return array(
				'status' => false,
				'data'   => $request,
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $request );
		$data = json_decode( wp_remote_retrieve_body( $request ), true );

		if ( 200 === $code ) {
			return array(
				'status' => true,
				'data'   => $data,
			);
		}
		return array(
			'status' => false,
			'data'   => $data,
		);
	}
}

CartFlows_Reporting::get_instance();
