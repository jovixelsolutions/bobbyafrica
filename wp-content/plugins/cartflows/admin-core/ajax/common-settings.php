<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsAdmin\AdminCore\Inc\AdminHelper;

/**
 * Class Flows.
 */
class CommonSettings extends AjaxBase {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * URL the client should hard-navigate to after a successful save.
	 *
	 * Set by save_other_settings() when the legacy-admin toggle is freshly
	 * enabled so the dashboard reloads through the legacy loader. Read by
	 * save_global_settings() and merged into the final response payload so
	 * do_action() hooks and other side-effects still run before the redirect.
	 *
	 * @var string
	 */
	private $pending_redirect = '';

	/**
	 * Initiator
	 *
	 * @since 1.0.0
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
	public function register_ajax_events() {

		if ( current_user_can( 'cartflows_manage_settings' ) ) {

			$ajax_events = array(
				'save_global_settings',
				'regenerate_css_for_steps',
				'track_kb_search',
				'migrate_custom_scripts',
			);
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Delete the post meta key for dynamic css to regenerate the it.
	 */
	public function regenerate_css_for_steps() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_regenerate_css_for_steps', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		// Update cartflows asset version to regenerate the dynamic css. We are using the time() function to add the random number.
		update_option( 'cartflows-assets-version', time() );

		$response_data = array(
			'messsage' => __( 'Successfully deleted the dynamic CSS keys!', 'cartflows' ),
		);
		wp_send_json_success( $response_data );
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_global_settings() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}


		$setting_tab = isset( $_POST['setting_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_tab'] ) ) : '';

		switch ( $setting_tab ) {

			case 'general':
				$this->save_general_settings();
				break;

			case 'permalink':
				$this->save_permalink_settings();
				break;

			case 'other':
				$this->save_other_settings();
				break;

			case 'user_role_manager':
				$this->save_user_roles_management_settings();
				break;

			case 'integrations':
				$this->save_integration_settings();
				break;

			case 'global_scripts':
				$this->save_global_scripts_settings();
				break;

			default:
				$this->save_general_settings();

		}

		do_action( 'cartflows_admin_save_global_settings', $setting_tab, 'cartflows_save_global_settings' );



		$response_data = array(
			'messsage' => __( 'Successfully saved data!', 'cartflows' ),
		);

		// If a tab handler queued a redirect (e.g. legacy-admin toggle was just
		// enabled), pass it through so the client hard-navigates after save.
		if ( ! empty( $this->pending_redirect ) ) {
			$response_data['redirect_to'] = $this->pending_redirect;
			$response_data['message']     = __( 'Switching to the legacy CartFlows UI…', 'cartflows' );
		}

		wp_send_json_success( $response_data );
	}

	/**
	 * Save integration tab settings. Such as Facebook & Google Analytics and Google AutoAddress.
	 *
	 * Note: Called from save_global_settings function.
	 *
	 * @return void
	 */
	public function save_integration_settings() {

		$new_settings = array();

		if ( isset( $_POST['_cartflows_facebook'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_facebook'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_facebook', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_google_analytics'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_analytics'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_google_analytics', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_google_auto_address'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_auto_address'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_google_auto_address', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_tiktok'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_tiktok'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_tiktok', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_pinterest'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_pinterest'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_pinterest', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_google_ads'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_google_ads'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_google_ads', $new_settings, false );
		}

		if ( isset( $_POST['_cartflows_snapchat'] ) ) { //phpcs:ignore
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_snapchat'] ) ); //phpcs:ignore
			AdminHelper::update_admin_settings_option( '_cartflows_snapchat', $new_settings, false );
		}
	}

	/**
	 * Save global CSS & Scripts settings.
	 *
	 * Note: Called from save_global_settings function.
	 *
	 * Note: Global scripts are stored in the '_cartflows_global_scripts' option and are
	 * completely independent of the per-flow/per-step custom script migration. They always
	 * use CodeMirror code editor fields regardless of the migration status
	 * ('cartflows_script_migration_status'). No migration is needed for global scripts.
	 *
	 * @return void
	 */
	public function save_global_scripts_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'message' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		// Global CSS/JS are output raw on every CartFlows page. Restrict authoring to users
		// with `unfiltered_html` so per-plugin caps cannot grant script write access to lower roles.
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			return;
		}

		if ( isset( $_POST['_cartflows_global_scripts'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$raw_settings = wp_unslash( $_POST['_cartflows_global_scripts'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$new_settings = array();

			if ( isset( $raw_settings['global_css'] ) ) {
				// Decode first to prevent double-encoding on repeated saves, then encode once.
				$new_settings['global_css'] = htmlentities( html_entity_decode( $raw_settings['global_css'], ENT_QUOTES, 'UTF-8' ), ENT_QUOTES, 'UTF-8' );
			}

			if ( isset( $raw_settings['global_js'] ) ) {
				// Decode first to prevent double-encoding on repeated saves, then encode once.
				$new_settings['global_js'] = htmlentities( html_entity_decode( $raw_settings['global_js'], ENT_QUOTES, 'UTF-8' ), ENT_QUOTES, 'UTF-8' );
			}

			AdminHelper::update_admin_settings_option( '_cartflows_global_scripts', $new_settings, false );
		}
	}

	/**
	 * Save other tab settings.
	 *
	 * Note: Called from save_global_settings function.
	 *
	 * @return void
	 */
	public function save_other_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$delete_plugin_data = '';

		if ( isset( $_POST['cartflows_delete_plugin_data'] ) ) {
			$delete_plugin_data = sanitize_text_field( $_POST['cartflows_delete_plugin_data'] );
		}

		AdminHelper::update_admin_settings_option( 'cartflows_delete_plugin_data', $delete_plugin_data, false );

		if ( _is_cartflows_pro() ) {
			AdminHelper::update_admin_settings_option( 'cartflows_pro_delete_plugin_data', $delete_plugin_data, false );
		}

		if ( isset( $_POST['cartflows_stats_report_emails'] ) ) {
			$enable_report_emails = sanitize_text_field( $_POST['cartflows_stats_report_emails'] );
			AdminHelper::update_admin_settings_option( 'cartflows_stats_report_emails', $enable_report_emails, false );
		}

		if ( isset( $_POST['cartflows_stats_report_email_ids'] ) ) {

			if ( ! empty( $_POST['cartflows_stats_report_email_ids'] ) ) {
				// Ignoring the sanitization rule as we are sanitizing each email before saving it.
				$emails           = preg_split( "/[\f\r\n]+/", $_POST['cartflows_stats_report_email_ids'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$validated_emails = array();

				foreach ( $emails as $email_id ) {

					if ( is_email( $email_id ) ) {
						array_push( $validated_emails, sanitize_email( $email_id ) );
					}
				}
				$validated_emails = implode( "\n", $validated_emails );
				AdminHelper::update_admin_settings_option( 'cartflows_stats_report_email_ids', $validated_emails, false );
			} else {
				AdminHelper::update_admin_settings_option( 'cartflows_stats_report_email_ids', '', false );
			}
		}

		// Update the option to enable/disable the non sensitive data tracking.
		if ( isset( $_POST['cf_usage_optin'] ) ) {
			$enable_non_sensative_data_tracking = sanitize_text_field( $_POST['cf_usage_optin'] );
			AdminHelper::update_admin_settings_option( 'cf_usage_optin', $enable_non_sensative_data_tracking, false );
		}

		// Persist the legacy-admin toggle. When it transitions from any non-enabled
		// value to 'enable', queue a redirect so the dashboard hard-reloads through
		// the legacy loader on the next request. The redirect is merged into the
		// final response by save_global_settings() AFTER do_action() and any other
		// side-effects fire, so we don't bypass third-party hooks. The legacy value
		// can be stored as boolean true (1.6.0 migration) — treat any truthy form as
		// "previously enabled" so re-saving doesn't re-trigger the redirect.
		if ( isset( $_POST['cartflows-legacy-admin'] ) ) {
			$prev_raw         = get_option( 'cartflows-legacy-admin', '' );
			$prev_was_enabled = in_array( $prev_raw, array( true, 1, '1', 'enable', 'yes' ), true );
			$legacy_admin     = in_array( $_POST['cartflows-legacy-admin'], array( 'enable', 'disable' ), true )
				? sanitize_text_field( wp_unslash( $_POST['cartflows-legacy-admin'] ) )
				: 'disable';

			update_option( 'cartflows-legacy-admin', $legacy_admin );

			if ( 'enable' === $legacy_admin && ! $prev_was_enabled ) {
				$this->pending_redirect = admin_url( 'admin.php?page=' . CARTFLOWS_SLUG );
			}
		}
	}

	/**
	 * Save common settings of CartFlows. Like Default Page Builder and Store Checkout.
	 *
	 * Note: Called from save_global_settings function.
	 *
	 * @return void
	 */
	public function save_general_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_common'] ) ) {
			// Ignoring the sanitization rule as we are sanitizing input using our function.
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_common'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$common_settings = AdminHelper::get_admin_settings_option( '_cartflows_common', false, false );
		$new_settings    = wp_parse_args( $new_settings, $common_settings );

		AdminHelper::update_admin_settings_option( '_cartflows_common', $new_settings, false );
	}

	/**
	 * Remove cf caps.
	 *
	 * @param object $user_role_obj user role object.
	 *
	 * @return void
	 */
	public function remove_all_cf_cap( $user_role_obj ) {

		$cf_cap = array(
			'cartflows_manage_settings',
			'cartflows_manage_flows_steps',
		);

		foreach ( $cf_cap as $cap ) {
			$user_role_obj->remove_cap( $cap );
		}
	}

	/**
	 * Add cf caps.
	 *
	 * @param object $user_role_obj user role object.
	 * @param string $access_key access key.
	 *
	 * @return void
	 */
	public function add_selected_cf_cap( $user_role_obj, $access_key ) {

		// Security: Validate access_key against allowlist to prevent privilege escalation.
		$allowed_keys = array( 'access_to_cartflows', 'access_to_flows_and_step', 'no_access' );

		if ( ! in_array( $access_key, $allowed_keys, true ) ) {
			return;
		}

		switch ( $access_key ) {

			case 'access_to_cartflows':
				$user_role_obj->add_cap( 'cartflows_manage_settings' );
				$user_role_obj->add_cap( 'cartflows_manage_flows_steps' );
				break;

			case 'access_to_flows_and_step':
				$user_role_obj->add_cap( 'cartflows_manage_flows_steps' );
				break;

			default:
				// No capabilities to add for 'no_access'.
				break;

		}
	}

	/**
	 * Add / Remove custom capability to the user role.
	 *
	 * @param array $new_settings Array of user role capability settings.
	 * @param array $old_settings Array of old user role capability settings.
	 *
	 * @return void
	 */
	public function user_role_management( $new_settings, $old_settings ) {

		// Security: Protect administrator role from modification via this endpoint.
		$protected_roles = array( 'administrator' );

		foreach ( $new_settings as $user_role => $access_key ) {

			if ( in_array( $user_role, $protected_roles, true ) ) {
				continue;
			}

			if ( ! isset( $old_settings[ $user_role ] ) || $old_settings[ $user_role ] !== $access_key ) {

				$user_role_obj = get_role( $user_role );

				if ( $user_role_obj ) {

					$this->remove_all_cf_cap( $user_role_obj );

					$this->add_selected_cf_cap( $user_role_obj, $access_key );

				}
			}
		}
	}

	/**
	 * Save user role settings.
	 *
	 * Note: Called from save_global_settings function.
	 *
	 * @return void
	 */
	public function save_user_roles_management_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		$new_settings = array();

		if ( isset( $_POST['_cartflows_roles'] ) ) {
			$new_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_roles'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$old_settings = AdminHelper::get_admin_settings_option( '_cartflows_roles' );

		$new_settings = wp_parse_args( $new_settings, $old_settings );

		AdminHelper::update_admin_settings_option( '_cartflows_roles', $new_settings, false );

		// Add/Remove capability.
		$this->user_role_management( $new_settings, $old_settings );
	}

	/**
	 * Save the permalink settings.
	 *
	 * Note: Called from save_global_settings function.
	 *
	 * @return void
	 */
	public function save_permalink_settings() {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_save_global_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		if ( isset( $_POST['reset'] ) ) {
			$_POST['_cartflows_permalink'] = array(
				'permalink'           => CARTFLOWS_STEP_PERMALINK_SLUG,
				'permalink_flow_base' => CARTFLOWS_FLOW_PERMALINK_SLUG,
				'permalink_structure' => '',
			);

		}
		$new_settings = array();
		if ( isset( $_POST['_cartflows_permalink'] ) ) {
			$cartflows_permalink_settings = $this->sanitize_form_inputs( wp_unslash( $_POST['_cartflows_permalink'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( empty( $cartflows_permalink_settings['permalink'] ) ) {
				$new_settings['permalink'] = CARTFLOWS_STEP_PERMALINK_SLUG;
			} else {
				$new_settings['permalink'] = $cartflows_permalink_settings['permalink'];
			}

			if ( empty( $cartflows_permalink_settings['permalink_flow_base'] ) ) {
				$new_settings['permalink_flow_base'] = CARTFLOWS_FLOW_PERMALINK_SLUG;
			} else {
				$new_settings['permalink_flow_base'] = $cartflows_permalink_settings['permalink_flow_base'];
			}

			$new_settings['permalink_structure'] = $cartflows_permalink_settings['permalink_structure'];

		}

		AdminHelper::update_admin_settings_option( '_cartflows_permalink', $new_settings, true );

		update_option( 'cartflows_permalink_refresh', true );
	}


	/**
	 * Save settings.
	 *
	 * @param array $input_settings settimg data.
	 */
	public function sanitize_form_inputs( $input_settings = array() ) {
		$new_settings = array();
		foreach ( $input_settings as $key => $val ) {

			if ( is_array( $val ) ) {
				foreach ( $val as $k => $v ) {
					$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
				}
			} else {
				$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
			}
		}
		return $new_settings;
	}
	
	/**
	 * Track KB search terms.
	 *
	 * @return void
	 */
	public function track_kb_search() {
		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( array( 'message' => $this->get_error_msg( 'permission' ) ) );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_track_kb_search', 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce validation failed', 'cartflows' ) );
			wp_send_json_error( $response_data );
		}

		// Get search term.
		$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';
		
		if ( empty( $search_term ) ) {
			wp_send_json_error( array( 'message' => 'Search term is empty' ) );
		}

		// Store the search term for BSF Analytics.
		$kb_searches = get_option( 'cartflows_kb_searches', array() );
		
		// Add the current search term to the list.
		$kb_searches[] = $search_term;
		
		// Keep only the last 20 searches to avoid data overload.
		$kb_searches = array_slice( $kb_searches, -20 );
		
		// Update the option.
		update_option( 'cartflows_kb_searches', $kb_searches );

		wp_send_json_success( array( 'message' => 'Search term tracked successfully' ) );
	}

	/**
	 * Handle on-demand custom script migration triggered by user clicking "Migrate Data".
	 *
	 * Verifies nonce and capabilities, calls the migration logic from Cartflows_Update,
	 * and returns a JSON response with the migrated post count.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function migrate_custom_scripts() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! check_ajax_referer( 'cartflows_migrate_custom_scripts', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$migrated_count = \Cartflows_Update::get_instance()->migrate_custom_scripts_on_demand();

		wp_send_json_success(
			array(
				/* translators: %d: number of posts migrated */
				'message'        => sprintf( __( 'Migration completed successfully. %d post(s) migrated.', 'cartflows' ), $migrated_count ),
				'migrated_count' => $migrated_count,
			)
		);
	}
}
