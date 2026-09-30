<?php
/**
 * Fields Handler
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Fields_Handler' ) ) {


	/**
	 * Fields handler class.
	 */
	class HT_CTC_Fields_Handler {

		/**
		 * Upper bound on how many groups one request may ask for.
		 *
		 * Comfortably above the tab count, so it never bites a real preload — it is
		 * here to bound the work an arbitrary caller can ask of a single request.
		 *
		 * @var int
		 */
		const MAX_GROUPS_PER_REQUEST = 25;

		/**
		 * Get settings fields endpoint.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function get_settings_fields( $request ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return HT_CTC_API_Responses::error( 'forbidden', 'You do not have permission.', 403 );
			}

			/*
			 * Two shapes, one route:
			 *   ?group=greetings-settings              -> { fields: [ ... ] }
			 *   ?groups=greetings-settings,display-... -> { groups: { greetings_settings: [ ... ] } }
			 *
			 * The plural form exists for the background preload, which used to ask for
			 * every inactive tab in its own request — ten round trips, each one a full
			 * WordPress boot, for a payload that is a few tens of KB in total. Opening
			 * one tab still uses the singular form.
			 */
			$requested = $this->requested_groups( $request );

			if ( empty( $requested ) ) {
				return HT_CTC_API_Responses::error( 'invalid_group', 'Invalid group', 400 );
			}

			// Load the fields class so we can read its REST surface and call the methods.
			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-settings-fields.php' );

			// Allowlist lives on HT_CTC_Admin_Settings_Fields::get_group_method_map() so the map
			// stays next to the methods it gates — when a new tab method is added, the
			// matching map entry is added in the same file.
			if ( ! class_exists( 'HT_CTC_Admin_Settings_Fields' ) || ! is_callable( array( 'HT_CTC_Admin_Settings_Fields', 'get_group_method_map' ) ) ) {
				return HT_CTC_API_Responses::error( 'invalid_group', 'Invalid group', 400 );
			}

			$group_method_map = HT_CTC_Admin_Settings_Fields::get_group_method_map();

			if ( ! is_array( $group_method_map ) ) {
				return HT_CTC_API_Responses::error( 'invalid_group', 'Invalid group', 400 );
			}

			$is_batch = $this->is_batch_request( $request );
			$results  = array();

			foreach ( $requested as $group ) {
				$fields = $this->fields_for_group( $group, $group_method_map );

				if ( empty( $fields ) ) {
					/*
					 * A batch skips what it cannot build rather than failing whole.
					 * These are speculative preloads for tabs the user has not opened,
					 * and one unknown group must not cost the other nine their cache.
					 * The client re-asks for anything missing when the tab is opened.
					 */
					if ( $is_batch ) {
						continue;
					}

					return HT_CTC_API_Responses::error( 'invalid_group', 'Invalid group', 400 );
				}

				// Keyed the way the client caches them: general_settings, not general-settings.
				$results[ str_replace( '-', '_', $group ) ] = $fields;
			}

			if ( empty( $results ) ) {
				return HT_CTC_API_Responses::error( 'invalid_group', 'Invalid group', 400 );
			}

			// `success: true` is added by the envelope (HT_CTC_API_Responses::success).
			if ( $is_batch ) {
				return HT_CTC_API_Responses::success(
					array(
						'groups' => $results,
					)
				);
			}

			return HT_CTC_API_Responses::success(
				array(
					'fields' => reset( $results ),
				)
			);
		}

		/**
		 * Whether the request is the plural (batch) form.
		 *
		 * Two readers depend on this answer — the envelope shape returned by
		 * get_settings_fields() and the slug list built by requested_groups() — and they
		 * must agree. Asking here rather than in each keeps them from drifting into the
		 * state where a request is read as singular but answered as a batch.
		 *
		 * An empty `groups` counts as absent: `?groups=&group=general-settings` is the
		 * singular form with a stray parameter, not an empty batch.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return bool
		 */
		private function is_batch_request( $request ) {

			$raw = $request->get_param( 'groups' );

			return ( null !== $raw && '' !== $raw );
		}

		/**
		 * Read and sanitize the requested group slugs.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return string[] Sanitized, de-duplicated group slugs.
		 */
		private function requested_groups( $request ) {

			if ( $this->is_batch_request( $request ) ) {
				$raw = $request->get_param( 'groups' );
			} else {
				$raw = (string) $request->get_param( 'group' );
			}

			$groups = is_array( $raw ) ? $raw : explode( ',', (string) $raw );

			$clean = array();

			foreach ( $groups as $group ) {
				if ( ! is_scalar( $group ) ) {
					continue;
				}

				// Strict sanitization and validation.
				$group = sanitize_file_name( sanitize_key( (string) $group ) );

				if ( '' !== $group ) {
					$clean[] = $group;
				}
			}

			// Cap the batch: the allow list is the real gate, this just bounds the work
			// a single request can ask for.
			return array_slice( array_unique( $clean ), 0, self::MAX_GROUPS_PER_REQUEST );
		}

		/**
		 * Build one group's fields, or an empty array if the group is not allowed.
		 *
		 * @param string $group            Sanitized group slug.
		 * @param array  $group_method_map Allow list: slug => method name.
		 * @return array
		 */
		private function fields_for_group( $group, $group_method_map ) {

			if ( ! isset( $group_method_map[ $group ] ) ) {
				HT_CTC_Utils::debug_log(
					'get_settings_fields: invalid or empty settings group requested',
					array( 'group' => $group )
				);
				return array();
			}

			$method_name = $group_method_map[ $group ];

			if ( ! is_callable( array( 'HT_CTC_Admin_Settings_Fields', $method_name ) ) ) {
				return array();
			}

			$fields = HT_CTC_Admin_Settings_Fields::$method_name();

			return is_array( $fields ) ? $fields : array();
		}
	}

}
