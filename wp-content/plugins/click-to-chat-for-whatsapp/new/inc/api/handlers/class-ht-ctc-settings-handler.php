<?php
/**
 * Settings Handler
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Handler' ) ) {

	/**
	 * Settings handler class.
	 */
	class HT_CTC_Settings_Handler {




		/**
		 * Allowed top-level option groups.
		 *
		 * Populated from HT_CTC_Settings_Data::get_settings_groups() in the constructor.
		 * Used as the defense-in-depth allow list inside seed_group_defaults()
		 * and passed to HT_CTC_Sanitizer::sanitize_settings().
		 *
		 * @var array
		 */
		protected $allowed_groups = array();

		/**
		 * Constructor.
		 */
		public function __construct() {

			if ( ! class_exists( 'HT_CTC_Utils' ) ) {
				require_once HT_CTC_PLUGIN_DIR . 'new/inc/utils/class-ht-ctc-utils.php';
			}

			HT_CTC_Utils::load_class( 'new/inc/commons/class-ht-ctc-settings-data.php', 'HT_CTC_Settings_Data' );

			$this->allowed_groups = HT_CTC_Settings_Data::get_settings_groups();
		}

		/**
		 * Save settings endpoint.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function save_settings( $request ) {

			// Nonce and capability are already verified by check_admin_settings_access (permission_callback -  $request object is unchanged since then)
			// before this handler runs. The capability check below is defense-in-depth only —
			if ( ! current_user_can( 'manage_options' ) ) {
				return HT_CTC_API_Responses::error( 'forbidden', 'You do not have permission.', 403 );
			}

			$raw_settings = $request->get_param( 'settings' );

			if ( ! is_array( $raw_settings ) ) {
				return HT_CTC_API_Responses::error( 'invalid_data', 'The settings payload must be an object/array.', 400 );
			}

			// Group names were already validated by the route's validate_callback
			// (HT_CTC_API_Validator::validate_settings_groups) before this
			// handler runs — WP short-circuits with a 400 otherwise. wp_unslash()
			// (map_deep) touches values only, never keys, so the validated group
			// names are unchanged here. Unknown groups are additionally dropped by
			// the sanitizer and re-checked in save_settings_to_db (defense in depth).
			$raw_settings = wp_unslash( $raw_settings );

			// (Already validated) Initial validation of group names.
			// // returns true or WP_Error (~with code 'invalid_data' (not an array) or 'invalid_group' (unknown key))
			// $validator = new HT_CTC_API_Validator();
			// $valid     = $validator->validate_settings_groups( $raw_settings );
			// if ( is_wp_error( $valid ) ) {
			// return $valid;
			// }

			// 1. If option group is not set in db then add with default/fallback values.
			$this->seed_group_defaults( array_keys( $raw_settings ) );

			// 2. Sanitize input recursively, with field-specific callbacks where configured.
			HT_CTC_Utils::load_class( 'new/inc/api/utils/class-ht-ctc-sanitizer.php', 'HT_CTC_Sanitizer' );
			$sanitized = HT_CTC_Sanitizer::sanitize_settings( $raw_settings, $this->allowed_groups );

			// 3. Explicit deletion channel (optional `remove` arg): bracket-notation key
			// paths per group, e.g. { ht_ctc_chat_options: [ "hide_mobile", "display[home]" ] }.
			// Syntax is validated by the route's validate_callback; here each path is
			// allow-list checked against the group schema and split into segments.
			$raw_remove = $request->get_param( 'remove' );
			$removals   = array();
			if ( is_array( $raw_remove ) ) {
				$removals = HT_CTC_Sanitizer::sanitize_remove_paths( wp_unslash( $raw_remove ), $this->allowed_groups );

				// Seed defaults for removal-only groups too: unsetting one key in a
				// never-saved group must still leave the rest of that group's defaults.
				$this->seed_group_defaults( array_keys( $removals ) );
			}

			HT_CTC_Utils::load_class( 'new/inc/api/utils/class-ht-ctc-data-processor.php', 'HT_CTC_Data_Processor' );
			$data_processor = new HT_CTC_Data_Processor();
			return HT_CTC_API_Responses::success(
				$data_processor->save_settings_to_db( $sanitized, $removals )
			);
		}


		/**
		 * Seed default values into the database for any listed option group that
		 * doesn't yet have a row in wp_options.
		 *
		 * Runs before sanitize/save so that subsequent recursive merges have a complete
		 * baseline to merge against — without this, a save (or removal) that touches a
		 * small subset of fields in a never-saved group would strip all the unmentioned
		 * defaults.
		 *
		 * Exceptions are caught and routed to debug_log; this method never raises to the caller.
		 *
		 * @param string[] $group_keys Top-level option group names (e.g. from
		 *                             array_keys() of the settings or remove payload).
		 * @throws InvalidArgumentException If the input is not an array.
		 */
		public function seed_group_defaults( $group_keys ) {
			try {
				if ( ! is_array( $group_keys ) ) {
					throw new InvalidArgumentException( 'Expected $group_keys to be an array.' );
				}

				foreach ( $group_keys as $option_key ) {
					try {
						if ( empty( $option_key ) || ! is_string( $option_key ) ) {
							continue;
						}

						// Defense in depth: only initialize allowed groups.
						if ( ! in_array( $option_key, $this->allowed_groups, true ) ) {
							continue;
						}

						// Use a unique default to distinguish between 'not set' and 'false'
						$existing_value = get_option( $option_key, '__ht_ctc_not_set__' );

						// If option does not exist in DB, initialize it with defaults
						if ( '__ht_ctc_not_set__' === $existing_value ) {
							if ( ! class_exists( 'HT_CTC_Utils' ) ) {
								require_once HT_CTC_PLUGIN_DIR . 'new/inc/utils/class-ht-ctc-utils.php';
							}
							$defaults = HT_CTC_Utils::get_option( $option_key );

							if ( ! empty( $defaults ) ) {
								update_option( $option_key, $defaults );
							}
						}
					} catch ( Exception $inner ) {
						HT_CTC_Utils::debug_log(
							'seed_group_defaults: per-option exception',
							array(
								'option' => $option_key,
								'error'  => $inner->getMessage(),
							)
						);
					}
				}
			} catch ( Exception $e ) {
				HT_CTC_Utils::debug_log(
					'seed_group_defaults: outer exception',
					array( 'error' => $e->getMessage() )
				);
			}
		}
	}

}
