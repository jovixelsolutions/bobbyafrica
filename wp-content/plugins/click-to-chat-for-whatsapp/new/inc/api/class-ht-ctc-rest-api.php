<?php
/**
 * REST API endpoints for Click to Chat.
 *
 * Provides routes to retrieve chat configuration and general settings.
 *
 * @package Click_To_Chat
 * @since 4.23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HT_CTC_Rest_API' ) ) {

	/**
	 * Registers the plugin's public REST endpoints.
	 */
	class HT_CTC_Rest_API {

		// public function __construct() {
		// $this->init();
		// }

		/**
		 * Initialize REST API endpoints.
		 *
		 * Registers WordPress action hooks for REST route registration.
		 *
		 * @return void
		 */
		public function init() {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Register REST routes.
		 *
		 * Defines public API endpoints for retrieving chat configuration.
		 *
		 * @return void
		 */
		public function register_routes() {

			register_rest_route(
				'click-to-chat-for-whatsapp/v1',
				'/get_ht_ctc_chat_var',
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_ht_ctc_chat_var' ),
					'permission_callback' => '__return_true',
				)
			);

			register_rest_route(
				'click-to-chat-for-whatsapp/v1',
				'/get_ht_ctc_variables',
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_ht_ctc_variables' ),
					'permission_callback' => '__return_true',
				)
			);
		}

		/**
		 * Handle /get_ht_ctc_chat_var requests.
		 *
		 * @param WP_REST_Request $request Request instance.
		 * @return WP_REST_Response|array Response payload.
		 */
		public function get_ht_ctc_chat_var( WP_REST_Request $request ) {

			// Load security class only when needed — prevents auto-executing code in files
			require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-security.php';

			// Validate request via referer, nonce, and user-agent checks
			$check = HT_CTC_Security::validate_rest_request( $request );
			if ( true !== $check ) {
				return $check;
			}

			// Load settings data class only after validation
			require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-settings-data.php';
			$data = HT_CTC_Settings_Data::get_ht_ctc_chat_var();

			return rest_ensure_response( $data );
		}

		/**
		 * Handle /get_ht_ctc_variables requests.
		 *
		 * @param WP_REST_Request $request Request instance.
		 * @return WP_REST_Response|array Response payload.
		 */
		public static function get_ht_ctc_variables( WP_REST_Request $request ) {

			// Load security class only when needed — avoids early side effects
			require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-security.php';

			// Validate request via referer, nonce, and user-agent checks
			$check = HT_CTC_Security::validate_rest_request( $request );
			if ( true !== $check ) {
				return $check;
			}

			// Load settings data class only after validation
			require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-settings-data.php';
			$data = HT_CTC_Settings_Data::get_ht_ctc_variables();

			return rest_ensure_response( $data );
		}
	}

	// stub for HT_CTC_Rest_API instance
	// new HT_CTC_Rest_API(); // Optional: You can auto-init elsewhere

} // End class check
