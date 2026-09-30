<?php
/**
 * REST API endpoints for Click to Chat
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Rest_API' ) ) {

	/**
	 * REST API class.
	 */
	class HT_CTC_Rest_API {

		/**
		 * REST API Version.
		 *
		 * @var string
		 */
		const REST_API_VERSION = 'v1';

		/**
		 * Get REST API Namespace.
		 *
		 * @return string Namespace ("click-to-chat-for-whatsapp/v1").
		 */
		public static function get_namespace() {
			$slug = defined( 'HT_CTC_PLUGIN_SLUG' ) ? HT_CTC_PLUGIN_SLUG : 'click-to-chat-for-whatsapp';
			// "click-to-chat-for-whatsapp/v1".
			return $slug . '/' . self::REST_API_VERSION;
		}

		/**
		 * Build a full REST API route URL.
		 *
		 * @param string $path Path relative to the namespace.
		 * @return string Full REST API URL.
		 */
		public static function route( $path = '' ) {
			return esc_url_raw(
				rest_url( self::get_namespace() . '/' . ltrim( $path, '/' ) )
			);
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->api_hooks();
		}

		/**
		 * Load the REST route dependencies (handlers, validator, permissions, responses).
		 *
		 * Called from register_routes() (rest_api_init) rather than the constructor,
		 * so these files are only parsed on actual REST requests — never on regular
		 * front-end / admin page loads where this class is instantiated.
		 */
		private function load_route_dependencies() {

			// if utils class not available then return. loaded from class-ht-ctc.php
			if ( ! class_exists( 'HT_CTC_Utils' ) ) {
				return;
			}

			// HT_CTC_Utils::load_file( 'new/inc/api/handlers/class-ht-ctc-public-handler.php' );
			HT_CTC_Utils::load_file( 'new/inc/api/utils/class-ht-ctc-api-responses.php' );
			HT_CTC_Utils::load_file( 'new/inc/api/handlers/class-ht-ctc-settings-handler.php' );
			HT_CTC_Utils::load_file( 'new/inc/api/handlers/class-ht-ctc-fields-handler.php' );
			HT_CTC_Utils::load_file( 'new/inc/api/validators/class-ht-ctc-api-validator.php' );
			HT_CTC_Utils::load_file( 'new/inc/api/permissions/class-ht-ctc-api-permissions.php' );
		}

		/**
		 * Register API hooks.
		 */
		public function api_hooks() {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Register routes.
		 */
		public function register_routes() {
			$this->load_route_dependencies();
			$this->register_admin_routes();
			// $this->register_public_routes(); // enable when public endpoints are implemented.
		}

		/**
		 * Register public routes.
		 *
		 * Placeholder — no public endpoints are currently exposed. When implementing:
		 *   1. Add the handler methods on HT_CTC_Public_Handler.
		 *   2. Instantiate the handler here and call register_rest_route() for each endpoint,
		 *      mirroring the pattern in register_admin_routes() above.
		 *   3. Uncomment the call to this method in register_routes().
		 * For prior reference code, see git history of this file.
		 */
		public function register_public_routes() {
		}

		/**
		 * Register admin REST routes.
		 *
		 * Bails early (with a debug log) if any of the four required handler classes
		 * isn't loaded, so a missing/broken handler file degrades the admin REST surface
		 * cleanly instead of fataling on `new` at rest_api_init time.
		 *
		 * Routes registered:
		 *   - POST /save-settings/  → HT_CTC_Settings_Handler::save_settings
		 *       Validates the `settings` arg via HT_CTC_API_Validator::validate_settings_groups
		 *       (checks payload is an array and each top-level key is in the allow list).
		 *       Optional `remove` arg: { option_group => [ "key", "parent[child]", ... ] } —
		 *       explicit deletion paths, validated via HT_CTC_API_Validator::validate_remove_paths.
		 *       Permission: HT_CTC_API_Permissions::check_admin_settings_access.
		 *   - GET  /get-fields/     → HT_CTC_Fields_Handler::get_settings_fields
		 *       Takes `group` (one slug → { fields: [...] }) or `groups` (comma-separated
		 *       → { groups: { slug: [...] } }), both sanitize_text_field then allow-listed
		 *       in the handler. At least one is required.
		 *       Permission: HT_CTC_API_Permissions::check_admin_settings_access.
		 */
		public function register_admin_routes() {

			// check if required classes exist. if not return early to avoid fatal errors when registering routes, and log the issue for debugging.
			$required = array(
				'HT_CTC_API_Permissions',
				'HT_CTC_API_Validator',
				'HT_CTC_Settings_Handler',
				'HT_CTC_Fields_Handler',
			);
			foreach ( $required as $handler_class ) {
				if ( ! class_exists( $handler_class ) ) {
					if ( class_exists( 'HT_CTC_Utils' ) ) {
						HT_CTC_Utils::debug_log(
							'register_admin_routes: skipped, handler class missing',
							array( 'class' => $handler_class )
						);
					}
					return;
				}
			}

			$permissions      = new HT_CTC_API_Permissions();
			$validator        = new HT_CTC_API_Validator();
			$settings_handler = new HT_CTC_Settings_Handler();
			$fields_handler   = new HT_CTC_Fields_Handler();

			register_rest_route(
				// namespace "click-to-chat-for-whatsapp/v1"
				self::get_namespace(),
				'/save-settings/',
				array(
					'methods'             => 'POST',
					'callback'            => array( $settings_handler, 'save_settings' ),
					'permission_callback' => array( $permissions, 'check_admin_settings_access' ), // nonce, current_user_can manage_options
					'args'                => array(
						'settings' => array(
							'required'          => true,
							// 'type'              => 'object',
							'validate_callback' => array( $validator, 'validate_settings_groups' ),
						),
						// Explicit deletion channel: { option_group => [ "key", "parent[child]", ... ] }.
						// Deletion instructions live here, separate from the data payload.
						'remove'   => array(
							'required'          => false,
							'validate_callback' => array( $validator, 'validate_remove_paths' ),
						),
					),
				)
			);

			register_rest_route(
				// namespace "click-to-chat-for-whatsapp/v1"
				self::get_namespace(),
				'/get-fields/',
				// wp-json/click-to-chat-for-whatsapp/v1/get-fields/?group=greetings-settings&v=4.40
				array(
					'methods'             => 'GET',
					'callback'            => array( $fields_handler, 'get_settings_fields' ),
					'permission_callback' => array( $permissions, 'check_admin_settings_access' ),
					'args'                => array(
						'group'  => array(
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						// Comma-separated slugs, for the background preload of the tabs
						// the user has not opened yet. Neither arg is required on its
						// own; the handler rejects a request that carries no group at
						// all, and sanitizes and allow-lists each slug either way.
						'groups' => array(
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				)
			);
		}
	}
}
