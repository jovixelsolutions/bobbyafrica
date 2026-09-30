<?php
/**
 * API Validator
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_API_Validator' ) ) {

	/**
	 * Validates REST API request payloads before they reach the handlers.
	 *
	 * Used as the `validate_callback` for route args registered in HT_CTC_Rest_API.
	 */
	class HT_CTC_API_Validator {

		/**
		 * Maximum length (characters) of a single remove path string.
		 *
		 * Sanity bound only — real paths are short (a few schema keys plus
		 * brackets); anything near this is malformed or abusive.
		 *
		 * @var int
		 */
		const MAX_REMOVE_PATH_LENGTH = 200;

		/**
		 * Allowed top-level option groups.
		 *
		 * Populated from HT_CTC_Settings_Data::get_settings_groups() so the validator,
		 * settings handler, and PRO filter registrations stay in sync.
		 *
		 * @var array
		 */
		protected $allowed_groups = array();

		/**
		 * Constructor.
		 *
		 * Loads HT_CTC_Settings_Data (defensively, in case the validator is
		 * instantiated before the REST handler's own load_route_dependencies() runs)
		 * and snapshots the allowed-groups list once for the lifetime of the instance.
		 */
		public function __construct() {
			if ( ! class_exists( 'HT_CTC_Utils' ) ) {
				require_once HT_CTC_PLUGIN_DIR . 'new/inc/utils/class-ht-ctc-utils.php';
			}

			HT_CTC_Utils::load_class( 'new/inc/commons/class-ht-ctc-settings-data.php', 'HT_CTC_Settings_Data' );

			$this->allowed_groups = HT_CTC_Settings_Data::get_settings_groups();
		}

		/**
		 * Validate that every top-level key of the settings payload is in the allowed-groups list.
		 *
		 * Wired as the `validate_callback` for the `settings` argument of POST /save-settings/.
		 * Returning a WP_Error here causes WordPress to short-circuit the request with a 400
		 * before the handler runs.
		 *
		 * @param mixed $settings_data Expected to be an array shaped { option_group_name => option_values }.
		 * @return true|WP_Error True if every top-level key is allowed; WP_Error with code
		 *                       `invalid_data` (not an array) or `invalid_group` (unknown key) otherwise.
		 */
		public function validate_settings_groups( $settings_data ) {

			if ( ! is_array( $settings_data ) ) {
				return new WP_Error( 'invalid_data', 'The settings payload must be an object/array.' );
			}

			foreach ( $settings_data as $option_group_name => $option_group_value ) {
				if ( ! in_array( $option_group_name, $this->allowed_groups, true ) ) {
					// If one invalid group is found, return an error immediately without checking the rest, to avoid overwhelming the user with multiple errors when they just need to fix one typo in the group name.
					return new WP_Error( 'invalid_group', 'Invalid settings group: ' . esc_html( $option_group_name ), array( 'status' => 400 ) );
				}
			}

			/**
			 * Sanitize values.
			 *
			 * But if value can be string? e.g. ht_ctc_r_sqx option value can be string. have to check.
			 */
			// if ( ! is_array( $option_group_value ) ) {
			// return new WP_Error(
			// 'invalid_group_payload',
			// sprintf(
			// * translators: %s is the settings group name */
			// __( 'Settings for group %s must be an object/array.', 'your-textdomain' ),
			// esc_html( $option_group_name )
			// ),
			// array( 'status' => 400 )
			// );
			// }

			return true;
		}

		/**
		 * Validate the optional `remove` argument of POST /save-settings/.
		 *
		 * Expected shape: { option_group => [ "key", "parent[child]", ... ] } — each path is a
		 * bracket-notation key path relative to the option group. This validates structure and
		 * syntax only; per-key allow-listing against the group schema happens later in
		 * HT_CTC_Sanitizer::sanitize_remove_paths() (unknown keys are silently dropped there,
		 * matching how unknown keys in the settings payload are handled).
		 *
		 * @param mixed $remove Expected to be an array shaped { option_group => string[] }.
		 * @return true|WP_Error True when valid; WP_Error with code `invalid_remove` (bad
		 *                       structure/syntax) or `invalid_group` (unknown group) otherwise.
		 */
		public function validate_remove_paths( $remove ) {

			if ( ! is_array( $remove ) ) {
				return new WP_Error( 'invalid_remove', 'The remove payload must be an object/array.', array( 'status' => 400 ) );
			}

			foreach ( $remove as $option_group_name => $paths ) {

				if ( ! in_array( $option_group_name, $this->allowed_groups, true ) ) {
					return new WP_Error( 'invalid_group', 'Invalid settings group: ' . esc_html( $option_group_name ), array( 'status' => 400 ) );
				}

				if ( ! is_array( $paths ) ) {
					return new WP_Error( 'invalid_remove', 'Remove paths for a group must be an array.', array( 'status' => 400 ) );
				}

				foreach ( $paths as $path ) {
					// Bracket-notation path: "key" or "parent[child][grandchild]".
					if ( ! is_string( $path )
						|| '' === $path
						|| strlen( $path ) > self::MAX_REMOVE_PATH_LENGTH
						|| ! preg_match( '/^[a-zA-Z0-9_\-]+(\[[a-zA-Z0-9_\-]+\])*$/', $path )
					) {
						return new WP_Error( 'invalid_remove', 'Invalid remove path.', array( 'status' => 400 ) );
					}
				}
			}

			return true;
		}
	}

}
