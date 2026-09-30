<?php
/**
 * Data Processor
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Data_Processor' ) ) {

	/**
	 * Data processor class.
	 *
	 * DESIGN NOTE: Settings Saving Strategy
	 * In general, saving all settings at once would be ideal. However, because some settings
	 * reside in multiple tabs, and those tabs might not be rendered to the DOM when saving,
	 * we cannot save all settings at once.
	 * To make saving easy and prevent accidental data loss of un-rendered tabs/fields, we
	 * employ different mechanisms:
	 *
	 * 1. `is_replace_entire_group`: Used where it is possible to overwrite the entire option
	 *    group directly (e.g. greetings options or custom collection groups).
	 *
	 * 2. `is_replace_as_list`: Used when a group of settings (like a list/array of repeater fields,
	 *    e.g., analytics parameters) needs to be replaced wholesale, rather than the entire
	 *    option group. This ensures rows deleted or reordered in the UI are persisted correctly
	 *    without losing other keys in the same option.
	 *
	 * 3. `remove` payload / attributes: For other individual settings (like checkboxes, dynamic
	 *    business hours logic, or random numbers logic), we send data to the remove attribute.
	 *    In the UI, fields can define a `data-remove` attribute or use checkboxes to pass specific
	 *    path keys to the remove payload, which tells the processor to explicitly delete them.
	 */
	class HT_CTC_Data_Processor {

		/**
		 * Maximum nesting depth processed while merging settings into existing options.
		 *
		 * The incoming payload is already depth-capped by HT_CTC_Sanitizer before it
		 * reaches here; this is an independent guard in case merge_recursive() is ever
		 * reached from another path with un-capped data.
		 *
		 * @var int
		 */
		const MAX_MERGE_DEPTH = 15;

		/**
		 * Save sanitized settings to database.
		 *
		 * CLIENT-SIDE CHANGE TRACKING INTEGRATION:
		 * On the client side (see `SettingsManager.js`), to minimize payload size and avoid database churn,
		 * only modified inputs marked with `data-changed="true"` are submitted.
		 * - For normal fields, their new value goes to the `settings` channel (received here as `$sanitized`).
		 * - For unchecked checkboxes, emptied repeater lists, or other fields explicitly deleted
		 *   (e.g., in business hours or random numbers logic), their paths are sent to the `remove` channel
		 *   (received here as `$removals`).
		 *
		 * Deletions arrive on their own channel ($removals — see the `remove` REST arg),
		 * never inside $sanitized: data and instructions are kept separate, so a literal
		 * user-typed value can never act as a delete command.
		 *
		 * @param array $sanitized Sanitized settings array { group => values }.
		 * @param array $removals  Sanitized removal paths { group => [ [segment, ...], ... ] },
		 *                         as produced by HT_CTC_Sanitizer::sanitize_remove_paths().
		 */
		public function save_settings_to_db( $sanitized, $removals = array() ) {
			$saved_groups      = array();
			$saved_groups_data = array();
			$removed_paths     = array();
			$errors            = array();

			if ( ! is_array( $removals ) ) {
				$removals = array();
			}

			// Defense in depth: re-verify each group against the canonical allowlist before
			// calling update_option(). The validator and sanitizer already drop unknown
			// groups upstream, so this guards the invariant against a future refactor of
			// either step rather than against a currently-reachable bypass.
			if ( class_exists( 'HT_CTC_Utils' ) ) {
				HT_CTC_Utils::load_class( 'new/inc/commons/class-ht-ctc-settings-data.php', 'HT_CTC_Settings_Data' );
			}
			$allowed_groups = class_exists( 'HT_CTC_Settings_Data' ) ? HT_CTC_Settings_Data::get_settings_groups() : array();

			// Iterate the union of groups so a removal-only save (e.g. a single unchecked
			// checkbox, nothing else changed) still reaches update_option for its group.
			$groups = array_unique( array_merge( array_keys( $sanitized ), array_keys( $removals ) ) );

			foreach ( $groups as $group ) {

				// check top-level groups against allowlist before processing each group; skip and log any unknown groups
				if ( ! in_array( $group, $allowed_groups, true ) ) {
					if ( class_exists( 'HT_CTC_Utils' ) ) {
						HT_CTC_Utils::debug_log(
							'save_settings_to_db: skipped non-allowed group',
							array( 'group' => $group )
						);
					}
					continue;
				}

				$new_values     = isset( $sanitized[ $group ] ) ? $sanitized[ $group ] : array();
				$group_removals = isset( $removals[ $group ] ) ? $removals[ $group ] : array();

				// each group options values have to be an array
				$existing = get_option( $group, array() );
				if ( ! is_array( $existing ) ) {
					$existing = array();
				}

				if ( $this->is_replace_entire_group( $group ) ) {
					// Group "replace" strategy — the option-group sibling of is_replace_as_list().
					// The card carries .ctc-group-sync so the client always sends the WHOLE
					// group; we overwrite instead of merge, so agent/field row deletes &
					// reorders persist (a deep merge can't shrink a list).
					//
					// Removal paths are irrelevant here: replace groups express deletion by
					// absence, and the whole group is overwritten anyway.
					//
					// Empty-group guard: never wipe an entire option with an empty / non-array
					// payload (a JS bug or a fully-collapsed form). Skip and keep existing.
					if ( ! is_array( $new_values ) || empty( $new_values ) ) {
						if ( class_exists( 'HT_CTC_Utils' ) ) {
							HT_CTC_Utils::debug_log(
								'save_settings_to_db: replace group skipped (empty payload)',
								array( 'group' => $group )
							);
						}
						continue;
					}

					$merged = $new_values;

				} elseif ( is_array( $new_values ) ) {
					// Removals first, then merge the incoming (partial) values into the
					// existing option. Remove-then-set order means a key present in both
					// channels ends up SET (settings win), and "replace this subtree" is
					// expressible as remove + set.
					// merge_recursive deep-merges associative objects (e.g. display) and
					// replaces list values wholesale (e.g. the analytics params, r_nums)
					// so repeater row deletes/reorders persist.
					$applied = array();
					$base    = $this->apply_removals( $existing, $group_removals, $applied );
					$merged  = $this->merge_recursive( $base, $new_values );

					// Collect the paths that actually unset something, echoed back in the
					// response so the client / tests can tell an applied removal from one
					// silently dropped upstream (unknown key, bad segment, missing path).
					if ( ! empty( $applied ) ) {
						$removed_paths[ $group ] = $applied;
					}
				} else {
					$merged = $new_values;
				}

				$merged = $this->normalize_structure( $merged );

				// Register multilingual(wpml/polylang) strings
				$this->register_multilingual_strings( $group, $merged );

				$ok = update_option( $group, $merged );

				if ( ! $ok && get_option( $group, null ) === null ) {
					if ( class_exists( 'HT_CTC_Utils' ) ) {
						HT_CTC_Utils::debug_log(
							'save_settings_to_db: update_option failed',
							array( 'group' => $group )
						);
					}
					$errors[] = $group;
				} else {
					$saved_groups[] = $group;
				}
				$saved_groups_data[ $group ] = $merged;
			}

			if ( ! empty( $errors ) ) {
				return new WP_Error(
					'save_failed',
					'Could not save settings for: ' . implode( ', ', $errors ),
					array( 'status' => 500 )
				);
			}

			/**
			 * Action triggered after settings are saved.
			 *
			 * @param array $saved_groups      List of group keys that were updated.
			 * @param array $saved_groups_data The full data that was saved for each group.
			 *
			 * not required to send details.
			 */
			// do_action( 'ht_ctc_ah_admin_after_save_settings', $saved_groups, $saved_groups_data );
			do_action( 'ht_ctc_ah_admin_after_save_settings' );

			// `success: true` is added by the envelope (HT_CTC_API_Responses::success)
			// when the settings handler wraps this return value.
			$response = array(
				'message'      => __( 'Settings saved successfully.', 'click-to-chat-for-whatsapp' ),
				'saved_groups' => $saved_groups,
				// Removal paths that were actually applied, in bracket notation per group,
				// e.g. { ht_ctc_chat_options: [ "hide_mobile", "display[home]" ] }.
				// A requested path absent here was a no-op or dropped by the sanitizer.
				'removed'      => $removed_paths,
				'settings'     => $saved_groups_data,
			);

			return $response;
		}

		/**
		 * Unset explicit removal paths from an option value.
		 *
		 * Deletions are sent on their own channel (the `remove` REST arg) as bracket-notation
		 * paths, sanitized into segment arrays by HT_CTC_Sanitizer::sanitize_remove_paths().
		 * Each path is walked segment by segment; a path whose intermediate segment is
		 * missing or not an array is a no-op (nothing to remove).
		 *
		 * Runs BEFORE merge_recursive() (remove-then-set): a key present in both channels
		 * ends up set, and "replace this subtree" is expressible as remove + set.
		 *
		 * @param array $values  The current option value.
		 * @param array $paths   Removal paths: array of segment arrays, e.g.
		 *                       [ [ 'hide_mobile' ], [ 'display', 'home' ] ].
		 * @param array $applied Optional, by reference. Filled with the paths that
		 *                       actually unset a key, in bracket notation (e.g.
		 *                       'display[home]') — echoed back in the save response.
		 * @return array The option value with the paths unset.
		 */
		protected function apply_removals( $values, $paths, &$applied = array() ) {

			if ( ! is_array( $values ) || ! is_array( $paths ) || empty( $paths ) ) {
				return $values;
			}

			foreach ( $paths as $segments ) {

				if ( ! is_array( $segments ) || empty( $segments ) ) {
					continue;
				}

				$ref  = &$values;
				$last = count( $segments ) - 1;

				foreach ( $segments as $i => $segment ) {
					if ( ! is_array( $ref ) || ! array_key_exists( $segment, $ref ) ) {
						continue 2; // Path doesn't exist — nothing to remove.
					}
					if ( $i === $last ) {
						unset( $ref[ $segment ] );

						// Rebuild 'parent[child]' notation for the response echo.
						$applied[] = $segments[0] . array_reduce(
							array_slice( $segments, 1 ),
							static function ( $carry, $seg ) {
								return $carry . '[' . $seg . ']';
							},
							''
						);
					} else {
						$ref = &$ref[ $segment ];
					}
				}

				unset( $ref );
			}

			return $values;
		}

		/**
		 * Non-strict recursive array merge with list-replace support.
		 *
		 * The incoming payload is partial (only changed fields are sent), so settings
		 * objects must be deep-merged to preserve untouched siblings. But list / repeater
		 * values (analytics params, r_nums, hook_v, ..) are always sent COMPLETE, and must
		 * REPLACE wholesale — otherwise a deep merge would resurrect deleted rows.
		 *
		 * The two cases are told apart structurally by is_replace_as_list():
		 *   - associative object (string keys, e.g. `display`)         → deep-merge
		 *   - list (empty or all-integer keys, e.g. `g_an_params`)     → replace wholesale
		 *
		 * This replaces the old cleanup_repeater_fields()/get_repeater_config() pass: no
		 * second traversal, no prefix-substring matching (which over-matched, e.g.
		 * 'hook_' vs 'hook_url'), and no per-key config to keep in sync.
		 *
		 * Key deletion is NOT expressed here: deletions arrive as explicit paths on the
		 * `remove` channel and are applied by apply_removals() before this merge runs.
		 * Values are pure data — a literal string can never act as a delete command.
		 *
		 * @param array $existing   The current option value.
		 * @param array $new_values New (partial) values from the request.
		 * @param int   $depth      Optional. Current nesting depth.
		 * @return array The merged array.
		 */
		protected function merge_recursive( $existing, $new_values, $depth = 0 ) {
			$merged = $existing;

			// Defense in depth: stop merging beyond a sane nesting depth (see MAX_MERGE_DEPTH).
			if ( $depth > self::MAX_MERGE_DEPTH ) {
				return $merged;
			}

			foreach ( $new_values as $key => $value ) {

				if ( is_array( $value ) ) {
					// Lists replace wholesale so row deletes / reorders persist.
					if ( $this->is_replace_as_list( $key, $value ) ) {
						$merged[ $key ] = $value;
						continue;
					}

					// Associative objects deep-merge to preserve untouched siblings.
					if ( ! isset( $merged[ $key ] ) || ! is_array( $merged[ $key ] ) ) {
						$merged[ $key ] = array();
					}
					$merged[ $key ] = $this->merge_recursive( $merged[ $key ], $value, $depth + 1 );
				} else {
					$merged[ $key ] = $value;
				}
			}
			return $merged;
		}

		/**
		 * Decide whether an incoming array value is a "list" (replace wholesale) or an
		 * associative settings object (deep-merge).
		 *
		 * Why we use this: Since all settings cannot be saved at once (due to un-rendered tabs),
		 * we perform partial merges. For repeater lists, merge_recursive would merge instead of
		 * replace, meaning deleted or reordered rows would not be saved properly. This method
		 * identifies array lists (such as analytics params, hook_v, r_nums) and replaces them
		 * wholesale to ensure correct data state.
		 *
		 * A value is treated as a list when it is empty (a cleared repeater) or every key
		 * is integer-like. This matches all repeaters in the schema — the analytics params
		 * ( { 0:.., 1:.., 1781675442226:.. } ), r_nums, hook_v, display_countries_list, etc.
		 * — whose keys are positional / time-based indexes, while settings objects such as
		 * `display` or business-hours `*_times` use string keys and fall through to merge.
		 *
		 * Numeric string keys are accepted too: on 32-bit PHP a time-based key larger than
		 * the int range stays a string, so checking only is_int() would misclassify it.
		 *
		 * An explicit override list ( ht_ctc_fh_replace_keys ) lets add-ons force a key to
		 * replace wholesale even if it does not look like a list.
		 *
		 * @param int|string $key   The key being merged.
		 * @param array      $value The incoming array value.
		 * @return bool True to replace wholesale, false to deep-merge.
		 */
		protected function is_replace_as_list( $key, $value ) {

			/**
			 * Keys that must always replace wholesale, regardless of their structure.
			 *
			 * @param array $keys Default empty — the structural check below already covers
			 *                     the core repeaters.
			 */
			$list = array(
				'g_an_params',
				'pixel_params',
				'gtm_params',
				'hook_v',
			);

			$force_replace = apply_filters( 'ht_ctc_fh_replace_keys', $list );

			if ( is_array( $force_replace ) && in_array( $key, $force_replace, true ) ) {
				return true;
			}

			// Empty array = a cleared repeater; replace so the rows are removed.
			if ( empty( $value ) ) {
				return true;
			}

			foreach ( array_keys( $value ) as $k ) {
				if ( ! is_int( $k ) && ! ( is_string( $k ) && ctype_digit( $k ) ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Whether an option group is saved by the "replace" strategy (overwrite whole
		 * group) instead of the default deep-merge.
		 *
		 * Why we use this: Since all settings cannot be saved at once (due to un-rendered tabs),
		 * we perform partial merges. For cohesive settings groups where the complete data is
		 * sent by the client, we overwrite the whole option group rather than merging. This prevents
		 * deleted or reordered items (like agents/fields) from persisting after they are removed.
		 *
		 * The option-group sibling of is_replace_as_list(): these are cohesive collection groups
		 * whose flat tracker + sibling shape can't express row deletion via merge. The
		 * client sends the COMPLETE group (its card carries .ctc-group-sync), and the server
		 * overwrites. The group is already allow-list checked by the caller, so this only
		 * decides merge-vs-replace.
		 *
		 * DECISION (kept alongside the `remove` channel, on purpose): the remove channel
		 * could technically express these deletions too (merge + remove paths per agent/
		 * field), which would leave a single save model. We keep replace instead because
		 * wholesale overwrite is the stronger semantic for cohesive collections — the
		 * client sends a complete snapshot, so a deleted row can NEVER be resurrected by
		 * a missed remove path; there is no client bug that leaves orphaned agent_N /
		 * field_N blobs in the DB. Do not "simplify" this into merge+remove.
		 *
		 * @param string $group The option group key.
		 * @return bool True to overwrite wholesale, false to deep-merge.
		 */
		protected function is_replace_entire_group( $group ) {

			if ( ! class_exists( 'HT_CTC_Settings_Data' ) ) {
				return false;
			}

			// The list and its filter live on HT_CTC_Settings_Data: HT_CTC_Sanitizer needs
			// the same answer at an earlier stage, and a second copy here would drift.
			return HT_CTC_Settings_Data::is_replace_group( $group );
		}

		/**
		 * Normalize data structure by recursively converting objects to arrays.
		 *
		 * @param mixed $value The value to normalize.
		 * @return mixed The normalized value.
		 */
		protected function normalize_structure( $value ) {

			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					$value[ $k ] = $this->normalize_structure( $v );
				}
				return $value;
			}

			if ( is_object( $value ) ) {
				return $this->normalize_structure( (array) $value );
			}

			return $value;
		}

		/**
		 * Register strings for translation with WPML/Polylang.
		 *
		 * @param string $group  The option group key.
		 * @param array  $values The option values being saved.
		 */
		protected function register_multilingual_strings( $group, $values ) {

			if ( ! is_array( $values ) ) {
				return;
			}

			// Chat options
			if ( 'ht_ctc_chat_options' === $group ) {
				$keys = array( 'number', 'pre_filled', 'call_to_action' );
				foreach ( $keys as $key ) {
					if ( isset( $values[ $key ] ) ) {
						do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key, $values[ $key ] );
					}
				}
			}

			// WooCommerce options
			if ( 'ht_ctc_woo_options' === $group ) {
				$keys = array( 'woo_pre_filled', 'woo_call_to_action', 'woo_shop_pre_filled', 'woo_shop_call_to_action' );
				foreach ( $keys as $key ) {
					if ( isset( $values[ $key ] ) ) {
						do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key, $values[ $key ] );
					}
				}
			}

			// Share options
			if ( 'ht_ctc_share' === $group ) {
				$keys = array( 'share_text', 'call_to_action' );
				foreach ( $keys as $key ) {
					if ( isset( $values[ $key ] ) ) {
						do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key . '__share', $values[ $key ] );
					}
				}
			}

			// Group options
			if ( 'ht_ctc_group' === $group ) {
				$keys = array( 'group_id', 'call_to_action' );
				foreach ( $keys as $key ) {
					if ( isset( $values[ $key ] ) ) {
						do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key . '__group', $values[ $key ] );
					}
				}
			}

			// Greetings options (handles ht_ctc_greetings_options, ht_ctc_greetings_pro_1, etc.)
			// Dev Reference/Issue:
			// For a new user (or after DB clear) who switches the UI and selects a greetings design (e.g. g form pro-1 on the grid select),
			// if no changes are made to the greetings form, those strings are not sent to the server.
			// As per the ADMIN2026 UI, only changes are sent and saved. String translations are only registered on save.
			// Consequently, Polylang/WPML translations will not be available until the greetings form is saved with changes.
			if ( strpos( $group, 'ht_ctc_greetings_' ) === 0 ) {
				$local = array(
					'header_content',
					'main_content',
					'bottom_content',
					'call_to_action',
					'opt_in',
				);
				// $local = apply_filters( 'ht_ctc_fh_greetings_setting_local_values', $local );

				foreach ( $local as $key ) {
					if ( isset( $values[ $key ] ) ) {
						do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', "greetings_$key", $values[ $key ] );
					}
				}

				do_action( 'ht_ctc_ah_admin_localization_greetings_page', $values );

			}
		}
	}

}
