<?php
/**
 * HT_CTC_Sanitizer
 *
 * Recursively sanitizes the settings payload sent by the 2026 admin REST API
 * (see HT_CTC_Settings_Handler::save_settings). Two independent layers run:
 *
 *   1. Structural allow-listing — top-level option groups must appear in the
 *      caller's $allowed_groups, and every key (at every nesting depth) must be
 *      declared in that group's schema. Unknown groups and keys are dropped.
 *   2. Value sanitizing — each leaf value is passed through a field-specific
 *      callback resolved from the group's schema, defaulting to
 *      sanitize_text_field().
 *
 * The per-group schema (allowed keys, key patterns, field callbacks) lives in
 * HT_CTC_Settings_Schema and is read one group at a time via
 * HT_CTC_Settings_Data::get_schema().
 *
 * @package Click_To_Chat
 * @subpackage API
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Sanitizer' ) ) {

	/**
	 * Settings payload sanitizer.
	 */
	class HT_CTC_Sanitizer {

		/**
		 * Maximum nesting depth processed while sanitizing a settings payload.
		 *
		 * Legit settings nest ~3-4 levels; WordPress' json_decode already caps request
		 * bodies at 512 levels, so this is belt-and-suspenders against a pathologically
		 * deep (DoS-shaped) admin payload exhausting the PHP stack.
		 *
		 * @var int
		 */
		const MAX_RECURSION_DEPTH = 10;

		/**
		 * Maximum number of segments in a single remove path (see sanitize_remove_paths()).
		 *
		 * Mirrors MAX_RECURSION_DEPTH: a removal can never point deeper than the
		 * settings tree the sanitizer is willing to walk.
		 *
		 * @var int
		 */
		const MAX_REMOVE_PATH_SEGMENTS = 10;

		/**
		 * Default leaf sanitizer used when the schema declares none for a field.
		 *
		 * @var string
		 */
		const DEFAULT_CALLBACK = 'sanitize_text_field';

		/**
		 * Sanitizer method names this class is allowed to dispatch to.
		 *
		 * Defense in depth: sanitize_value() only invokes a callback that appears here
		 * AND exists as a method, so a schema / PRO-filter mistake can't reach an
		 * unintended (e.g. private) method. Add 'ctc_sanitize_color' here when that
		 * method is uncommented (gradient color picker).
		 *
		 * @var string[]
		 */
		private static $allowed_sanitizers = array(
			'sanitize_text_field',
			'sanitize_textarea_field',
			'ctc_sanitize_emoji_text',
			'ctc_sanitize_emoji_textarea',
			'ctc_sanitize_text_editor',
			'ctc_sanitize_custom_css',
			'ctc_sanitize_url',
			'ctc_sanitize_whatsapp_number',
			'ctc_sanitize_normalize_css_suffix',
			// 'ctc_sanitize_color', // enable with ctc_sanitize_color() (gradient color picker).
		);

		/**
		 * Sanitize a full settings payload.
		 *
		 * Walks each top-level option group, drops groups absent from $allowed_groups,
		 * then sanitizes the group's contents against its schema. Array groups are
		 * sanitized recursively; the (rare) scalar group is run through its default
		 * leaf sanitizer.
		 *
		 * @param array $raw_settings   Associative array of { option_group => values }.
		 * @param array $allowed_groups Allowed top-level option group names.
		 * @return array Sanitized settings, keyed by allowed option group.
		 */
		public static function sanitize_settings( $raw_settings, $allowed_groups = array() ) {

			if ( ! is_array( $raw_settings ) ) {
				return array();
			}

			self::ensure_settings_data_loaded();

			$sanitized = array();

			foreach ( $raw_settings as $group_key => $group_values ) {

				// E.g. ht_ctc_chat_options, ht_ctc_othersettings.
				$group_key = self::sanitize_key( $group_key );

				// Drop unknown / disallowed top-level groups.
				if ( '' === $group_key || ! in_array( $group_key, $allowed_groups, true ) ) {
					continue;
				}

				$schema = self::get_group_schema( $group_key );

				// Replace groups additionally tolerate keys already stored in the DB —
				// see sanitize_tree()'s $stored parameter. Read once per group, and only
				// for those groups, so the common merge path costs nothing extra.
				$stored = null;
				if ( class_exists( 'HT_CTC_Settings_Data' ) && HT_CTC_Settings_Data::is_replace_group( $group_key ) ) {
					$stored = get_option( $group_key, array() );
					if ( ! is_array( $stored ) ) {
						$stored = null;
					}
				}

				// Array: sanitize_tree walks each key and calls sanitize_value on every leaf.
				// Scalar: sanitize_value called directly here.
				if ( is_array( $group_values ) ) {
					$sanitized[ $group_key ] = self::sanitize_tree( $group_values, $schema, $group_key, 0, '', $stored );
				} else {
					$sanitized[ $group_key ] = self::sanitize_value( $group_values, $group_key, $schema['sanitization_callbacks'] );
				}
			}

			return $sanitized;
		}

		/**
		 * Recursively sanitize one option group's value tree.
		 *
		 * Allow-listing is enforced at EVERY depth (flat allow-anywhere): a key is kept
		 * only if it is in the group's allowed_keys or matches one of its patterns —
		 * unknown keys are dropped before their value is ever sanitized. This blocks an
		 * admin (or a compromised admin session) from stuffing arbitrary keys into a
		 * valid option group. A group whose schema declares no allowed_keys and no
		 * patterns therefore drops every key.
		 *
		 * @param array  $values          Associative array to sanitize.
		 * @param array  $schema          Group schema: allowed_keys, allowed_keys_patterns,
		 *                                sanitization_callbacks.
		 * @param string $parent_key      Full key path of the parent (for callback lookup),
		 *                                e.g. 'ht_ctc_chat_options[display]'.
		 * @param int    $depth           Current nesting depth.
		 * @param string $parent_bare_key Bare (unqualified) key of the immediate parent
		 *                                container, e.g. 'r_nums'. Lets a flat list whose
		 *                                items carry no field name of their own (indices
		 *                                0, 1, 2, ...) still resolve a callback — see
		 *                                sanitize_value()'s container-key fallback.
		 * @param array  $stored          REPLACE GROUPS ONLY (null elsewhere): the node of the
		 *                                currently-stored option value at this same path. A key
		 *                                present here is admitted even when the schema does not
		 *                                name it — see the grandfathering note below.
		 * @return array
		 */
		private static function sanitize_tree( $values, $schema, $parent_key = '', $depth = 0, $parent_bare_key = '', $stored = null ) {

			$sanitized = array();

			// Stop descending into pathologically deep payloads (DoS guard); see MAX_RECURSION_DEPTH.
			if ( $depth > self::MAX_RECURSION_DEPTH || ! is_array( $values ) ) {
				return $sanitized;
			}

			foreach ( $values as $key => $value ) {

				// e.g. 'call_to_action', 'position'.
				$key = self::sanitize_key( $key );

				if ( '' === $key ) {
					continue;
				}

				// Drop keys not in this group's allow-list / patterns (checked at every depth),
				// UNLESS this is a replace group and the key already exists in the stored
				// option — see is_grandfathered().
				if ( ! self::is_key_allowed( $key, $schema ) && ! self::is_grandfathered( $key, $stored ) ) {
					continue;
				}

				// e.g. ht_ctc_chat_options[display][home].
				$full_key = ( '' !== $parent_key ) ? "{$parent_key}[{$key}]" : $key;

				if ( is_array( $value ) ) {
					// Descend the stored tree in lock-step so grandfathering is evaluated
					// against the value at THIS path, never against a same-named key
					// somewhere else in the option.
					$stored_child = ( is_array( $stored ) && isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ) ? $stored[ $key ] : null;

					$sanitized[ $key ] = self::sanitize_tree( $value, $schema, $full_key, $depth + 1, $key, $stored_child );
				} else {
					$sanitized[ $key ] = self::sanitize_value( $value, $key, $schema['sanitization_callbacks'], $full_key, $parent_bare_key );
				}
			}

			return $sanitized;
		}

		/**
		 * Whether a key survives because the option already contains it.
		 *
		 * ## The problem this solves
		 *
		 * A replace group is overwritten wholesale by what the client submits, so every
		 * key has to be re-submitted to survive. That includes keys this build cannot
		 * render an editor for, because the extension that registered them is not
		 * currently active. An admin screen can re-emit such values as hidden inputs and
		 * still lose them here: this allow-list is assembled from the schemas of the
		 * plugins that are RUNNING, and an inactive extension registers nothing. Its keys
		 * would be stripped after the form submitted them perfectly, and the overwrite
		 * would then delete settings the user never touched.
		 *
		 * The alternative was for this plugin to hardcode every extension's key names
		 * forever — a maintenance treadmill, and a layering break.
		 *
		 * ## Why this stays safe
		 *
		 * Admission is not widened to "anything": a key qualifies only by ALREADY EXISTING
		 * at this exact path in the stored option. Nothing can be introduced this way —
		 * to be in the option a key must have passed this same allow-list earlier, while
		 * some plugin legitimately declared it. So the tolerated set is exactly "keys an
		 * extension really wrote", it shrinks by itself when a key is deliberately removed,
		 * and an injected `foo` is still dropped because no stored `foo` exists.
		 *
		 * Only KEY admission changes. The value still goes through the normal leaf
		 * sanitizer, so a grandfathered key cannot carry unsanitized content.
		 *
		 * Merge groups never reach this ($stored is null): they keep absent keys anyway,
		 * so there is nothing to preserve and no reason to widen anything.
		 *
		 * @param string $key    Sanitized key being considered.
		 * @param array  $stored Stored node at this path, or null when not applicable.
		 * @return bool
		 */
		private static function is_grandfathered( $key, $stored ) {
			return is_array( $stored ) && array_key_exists( $key, $stored );
		}

		/**
		 * Sanitize the explicit-deletion (`remove`) payload of a save request.
		 *
		 * Input shape: { option_group => [ "key", "parent[child]", ... ] } (bracket-notation
		 * paths, already syntax-validated by HT_CTC_API_Validator::validate_remove_paths).
		 * Output shape: { option_group => [ [ 'key' ], [ 'parent', 'child' ], ... ] } —
		 * paths split into segment arrays, ready for HT_CTC_Data_Processor::apply_removals().
		 *
		 * The same allow-listing as sanitize_settings() applies: unknown groups are dropped,
		 * and EVERY path segment must pass the group's key allow-list / patterns — a path
		 * with any disallowed segment is dropped whole. Segment count is capped by
		 * MAX_REMOVE_PATH_SEGMENTS, mirroring the settings tree cap.
		 *
		 * check: is_replace_entire_group, is_replace_as_list may be we can skip it from remove.(js or here)
		 *
		 * @param mixed $remove         Raw remove payload from the request.
		 * @param array $allowed_groups Allowed top-level option group names.
		 * @return array Sanitized removal paths, keyed by allowed option group.
		 */
		public static function sanitize_remove_paths( $remove, $allowed_groups = array() ) {

			if ( ! is_array( $remove ) ) {
				return array();
			}

			self::ensure_settings_data_loaded();

			$sanitized = array();

			// e.g. remove: {ht_ctc_othersettings: ["g_an", "gtm"]}
			foreach ( $remove as $group_key => $paths ) {

				$group_key = self::sanitize_key( $group_key );

				if ( '' === $group_key || ! in_array( $group_key, $allowed_groups, true ) || ! is_array( $paths ) ) {
					continue;
				}

				$schema      = self::get_group_schema( $group_key );
				$clean_paths = array();

				foreach ( $paths as $path ) {

					if ( ! is_string( $path ) || '' === $path ) {
						continue;
					}

					// "parent[child]" → [ 'parent', 'child' ].
					if ( ! preg_match_all( '/[^\[\]]+/', $path, $matches ) ) {
						continue;
					}

					$segments = $matches[0];

					if ( count( $segments ) > self::MAX_REMOVE_PATH_SEGMENTS ) {
						continue;
					}

					$clean_segments = array();
					foreach ( $segments as $segment ) {
						$segment = self::sanitize_key( $segment );
						if ( '' === $segment || ! self::is_key_allowed( $segment, $schema ) ) {
							$clean_segments = null;
							break;
						}
						$clean_segments[] = $segment;
					}

					if ( ! empty( $clean_segments ) ) {
						$clean_paths[] = $clean_segments;
					}
				}

				if ( ! empty( $clean_paths ) ) {
					$sanitized[ $group_key ] = $clean_paths;
				}
			}

			return $sanitized;
		}

		/**
		 * Whether a key passes the group's allow-list.
		 *
		 * Flat allow-anywhere: matches against the group's full key list and patterns
		 * regardless of nesting depth.
		 *
		 * @param string $key    Sanitized key.
		 * @param array  $schema Group schema (allowed_keys, allowed_keys_patterns).
		 * @return bool
		 */
		private static function is_key_allowed( $key, $schema ) {

			if ( ! empty( $schema['allowed_keys'] ) && in_array( $key, $schema['allowed_keys'], true ) ) {
				return true;
			}

			if ( ! empty( $schema['allowed_keys_patterns'] ) ) {
				foreach ( $schema['allowed_keys_patterns'] as $pattern ) {
					if ( preg_match( $pattern, $key ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Sanitize a single leaf value: pick its sanitizer, guard it, run it.
		 *
		 * This is the final step a value passes through. It does three things in order:
		 *   1. Resolve which sanitizer the schema wants using a priority fallback:
		 *      Full Path > Direct Key > Immediate Parent Key.
		 *      Example: When sanitizing `ht_ctc_chat_options[r_nums][0]`
		 *      - Checks full path: 'ht_ctc_chat_options[r_nums][0]'
		 *      - Checks direct key: '0'
		 *      - Checks immediate parent key: 'r_nums' (This lets flat lists inherit their parent's sanitizer)
		 *   2. Defense in depth: fall back to the default unless that name is explicitly
		 *      allow-listed AND exists, so a schema / PRO-filter mistake can never reach
		 *      an unintended (e.g. private) method.
		 *   3. Call the resolved method and return the sanitized value.
		 *
		 * @param mixed  $value                  The value to sanitize.
		 * @param string $key                    The bare field key (e.g. 'call_to_action').
		 * @param array  $sanitization_callbacks Field key => sanitizer-method-name map for the group.
		 * @param string $full_key               Full key path (e.g. 'group[parent][key]'), optional.
		 * @param string $parent_key             Bare key of the immediate parent container
		 *                                       (e.g. 'r_nums'), optional. A schema can map
		 *                                       this key to a callback so every item of a
		 *                                       flat repeater list (whose items have no field
		 *                                       name of their own) gets sanitized uniformly.
		 * @return mixed Sanitized value.
		 */
		public static function sanitize_value( $value, $key, $sanitization_callbacks, $full_key = '', $parent_key = '' ) {

			// 1. Which sanitizer does the schema ask for?
			// Priority fallback: Full Path > Direct Key > Immediate Parent Key
			// Example: 'ht_ctc_chat_options[r_nums][0]' > '0' > 'r_nums'
			$callback = self::DEFAULT_CALLBACK;
			if ( is_array( $sanitization_callbacks ) ) {
				if ( '' !== $full_key && isset( $sanitization_callbacks[ $full_key ] ) ) {
					$callback = $sanitization_callbacks[ $full_key ];
				} elseif ( isset( $sanitization_callbacks[ $key ] ) ) {
					$callback = $sanitization_callbacks[ $key ];
				} elseif ( '' !== $parent_key && isset( $sanitization_callbacks[ $parent_key ] ) ) {
					$callback = $sanitization_callbacks[ $parent_key ];
				}
			}

			// 2. Only dispatch to an allow-listed method that exists; otherwise default.
			if ( ! in_array( $callback, self::$allowed_sanitizers, true ) || ! method_exists( self::class, $callback ) ) {
				$callback = self::DEFAULT_CALLBACK;
			}

			// 3. Sanitize.
			return self::$callback( $value, $key );
		}

		/**
		 * Read one option group's schema slice, guaranteeing the three expected arrays.
		 *
		 * @param string $group Option group name.
		 * @return array { allowed_keys, allowed_keys_patterns, sanitization_callbacks }.
		 */
		private static function get_group_schema( $group ) {

			$schema = array(
				'allowed_keys'           => array(),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(),
			);

			if ( ! class_exists( 'HT_CTC_Settings_Data' ) ) {
				return $schema;
			}

			$slice = HT_CTC_Settings_Data::get_schema( $group );

			foreach ( array_keys( $schema ) as $part ) {
				if ( isset( $slice[ $part ] ) && is_array( $slice[ $part ] ) ) {
					$schema[ $part ] = $slice[ $part ];
				}
			}

			return $schema;
		}

		/**
		 * Ensure HT_CTC_Settings_Data is loaded (it provides per-group schema).
		 *
		 * @return void
		 */
		private static function ensure_settings_data_loaded() {
			if ( ! class_exists( 'HT_CTC_Settings_Data' ) && class_exists( 'HT_CTC_Utils' ) ) {
				HT_CTC_Utils::load_class( 'new/inc/commons/class-ht-ctc-settings-data.php', 'HT_CTC_Settings_Data' );
			}
		}

		/**
		 * Sanitize a key part (option group key or array key).
		 *
		 * E.g. 'ht_ctc_chat_options', 'call_to_action'. Strips everything except
		 * alphanumerics, underscore and hyphen.
		 *
		 * @param string $key The key to sanitize.
		 * @return string
		 */
		public static function sanitize_key( $key ) {
			$key = sanitize_text_field( $key );
			return preg_replace( '/[^a-zA-Z0-9_\-]/', '', $key );
		}

		/*
		 * ---------------------------------------------------------------------
		 * Leaf sanitizers — dispatched by name (see $allowed_sanitizers).
		 * Each accepts ( $value, $key ) so key-aware sanitizers can branch on it.
		 * ---------------------------------------------------------------------
		 */

		/**
		 * Sanitize a text field.
		 *
		 * @param mixed  $value The value to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function sanitize_text_field( $value, $key = '' ) {
			unset( $key );
			return sanitize_text_field( $value );
		}

		/**
		 * Sanitize a textarea field.
		 *
		 * @param mixed  $value The value to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function sanitize_textarea_field( $value, $key = '' ) {
			unset( $key );

			// sanitize_textarea_field() exists since WP 4.7; fall back just in case.
			if ( function_exists( 'sanitize_textarea_field' ) ) {
				return sanitize_textarea_field( $value );
			}

			return sanitize_text_field( $value );
		}

		/**
		 * Sanitize a text field and encode emojis.
		 *
		 * @param mixed  $value The value to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function ctc_sanitize_emoji_text( $value, $key = '' ) {
			unset( $key );
			return self::sanitize_text_field( self::encode_emoji( $value ) );
		}

		/**
		 * Sanitize a textarea field and encode emojis.
		 *
		 * @param mixed  $value The value to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function ctc_sanitize_emoji_textarea( $value, $key = '' ) {
			unset( $key );
			return self::sanitize_textarea_field( self::encode_emoji( $value ) );
		}

		/**
		 * Sanitize editor (TinyMCE / code block) content.
		 *
		 * Pipeline: html_entity_decode → encode_emoji → wp_kses (post) → htmlentities
		 * → sanitize_textarea_field. The decode neutralizes any prior encoding; the
		 * trailing htmlentities adds a second escaping layer over the kses-filtered HTML.
		 *
		 * @param mixed  $value The value to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function ctc_sanitize_text_editor( $value, $key = '' ) {
			unset( $key );

			if ( empty( $value ) ) {
				return $value;
			}

			// Decode first to neutralize any previous encoding, then re-secure.
			$value = html_entity_decode( $value );
			$value = self::encode_emoji( $value );
			$value = wp_kses( $value, wp_kses_allowed_html( 'post' ) );

			// Double-encode the kses-filtered HTML for an extra escaping layer.
			$value = htmlentities( $value );

			return self::sanitize_textarea_field( $value );
		}

		/**
		 * Sanitize custom CSS code.
		 *
		 * @param string $value The CSS code to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function ctc_sanitize_custom_css( $value = '', $key = '' ) {
			unset( $key );

			if ( ! empty( $value ) ) {
				$value = wp_kses( $value, wp_kses_allowed_html( 'post' ) );
			}

			return self::sanitize_textarea_field( $value );
		}

		/**
		 * Sanitize a URL for safe storage.
		 *
		 * Uses esc_url_raw, which validates the scheme (rejecting javascript:/data: and
		 * other dangerous URIs) and storage-safe-encodes the URL. Use this for any URL
		 * field; sanitize_text_field would silently accept malformed/dangerous URIs.
		 *
		 * @param mixed  $value The URL to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function ctc_sanitize_url( $value, $key = '' ) {
			unset( $key );

			if ( ! is_string( $value ) ) {
				return '';
			}

			return esc_url_raw( $value );
		}

		/**
		 * Sanitize and format a WhatsApp phone number.
		 *
		 * @param mixed  $value The number to sanitize.
		 * @param string $key   Unused; kept for a uniform sanitizer signature.
		 * @return string
		 */
		public static function ctc_sanitize_whatsapp_number( $value, $key = '' ) {
			unset( $key );

			$value = self::sanitize_text_field( $value );

			if ( ! class_exists( 'HT_CTC_Formatting' ) && defined( 'HT_CTC_PLUGIN_DIR' ) && file_exists( HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-formatting.php' ) ) {
				require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-formatting.php';
			}

			if ( class_exists( 'HT_CTC_Formatting' ) && method_exists( 'HT_CTC_Formatting', 'wa_number' ) ) {
				$value = HT_CTC_Formatting::wa_number( $value );
			}

			return $value;
		}

		/**
		 * Normalize a simple CSS dimension (e.g. 10 -> "10px", "2em" -> "2em").
		 *
		 * Rules:
		 *  - Empty input → key-specific default, else '' .
		 *  - Pure number → append "px".
		 *  - number + known unit (px, em, rem, %, vh, vw) → kept (unit lowercased).
		 *  - number + unknown unit → number + "px".
		 *  - anything else (garbage) → "0px".
		 *
		 * @param mixed  $value The value to normalize.
		 * @param string $key   Field key, used to pick empty-input defaults.
		 * @return string
		 */
		public static function ctc_sanitize_normalize_css_suffix( $value, $key = '' ) {

			$value = self::sanitize_text_field( $value );

			// Strip spaces, as done in the old admin UI.
			$value = str_replace( ' ', '', $value );

			// Empty input → key-specific default, or blank.
			if ( '' === $value ) {
				return self::css_suffix_default( $key );
			}

			// Pure number → add px.
			if ( is_numeric( $value ) ) {
				return $value . 'px';
			}

			$value = trim( (string) $value );

			$allowed_units = array( 'px', 'em', 'rem', '%', 'vh', 'vw' );

			// number (optionally signed/decimal) + alphabetic-or-% unit.
			// e.g. "10.5px" → [ '10.5px', '10.5', 'px' ].
			if ( preg_match( '/^(-?\d+(?:\.\d+)?)([a-z%]+)$/i', $value, $matches ) ) {
				$number = $matches[1];
				$unit   = strtolower( $matches[2] );

				// Known unit kept as-is; any unknown unit (1r, 1e, 1p, …) defaults to px.
				return in_array( $unit, $allowed_units, true ) ? $number . $unit : $number . 'px';
			}

			// Not a number and not number+unit (e.g. "!@#") → zero-pixel safe default.
			return '0px';
		}

		/**
		 * Default value for an empty CSS-dimension field, keyed by field name.
		 *
		 * @param string $key Field key.
		 * @return string Default dimension, or '' when the field has none.
		 */
		private static function css_suffix_default( $key ) {

			// static
			$defaults = array(
				's5_img_height'       => '70px',
				's5_img_width'        => '70px',
				's5_content_height'   => '70px',
				's5_content_width'    => '270px',
				's7_icon_size'        => '24px',
				's7_border_size'      => '12px',
				's7_border_radius'    => '4px',
				'side_1_value'        => '0px',
				'side_2_value'        => '0px',
				'mobile_side_1_value' => '0px',
				'mobile_side_2_value' => '0px',
			);

			return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
		}

		/**
		 * Encode 4-byte emoji for database storage.
		 *
		 * Only encodes when DB_CHARSET is utf8 (4-byte emoji need encoding on utf8
		 * tables; utf8mb4 stores them natively).
		 *
		 * @param string $value Value to encode.
		 * @return string
		 */
		private static function encode_emoji( $value ) {
			if ( defined( 'DB_CHARSET' ) && 'utf8' === DB_CHARSET && function_exists( 'wp_encode_emoji' ) ) {
				$value = wp_encode_emoji( $value );
			}
			return $value;
		}

		/**
		 * Sanitize a CSS color value.
		 *
		 * NOTE: Intentionally commented out — not yet wired into the schema.
		 * A gradient color picker is planned (later stage); a strict color sanitizer
		 * would strip gradient values, so until that ships the color fields keep using
		 * the default sanitize_text_field. Their output is already esc_attr-escaped
		 * inside style="" attributes, so enabling this is a robustness/normalization
		 * improvement, not a security fix. When enabled, also add 'ctc_sanitize_color'
		 * to $allowed_sanitizers.
		 *
		 * When enabled, accepts: '', hex (#rgb / #rgba / #rrggbb / #rrggbbaa),
		 * rgb()/rgba()/hsl()/hsla(), and CSS named colors. Anything else -> ''.
		 *
		 * Note (gradient picker): also accept linear-gradient()/radial-gradient()/
		 * conic-gradient() syntax before wiring this in (else gradients get dropped).
		 *
		 * @param mixed  $value The color value to sanitize.
		 * @param string $key   The key associated with the value.
		 * @return string Sanitized color, or '' if not a recognized color.
		 */
		// public static function ctc_sanitize_color( $value, $key = '' ) {
		//
		// unset( $key );
		//
		// if ( ! is_string( $value ) ) {
		// return '';
		// }
		//
		// $value = trim( $value );
		//
		// if ( '' === $value ) {
		// return '';
		// }
		//
		// // Hex: #rgb, #rgba, #rrggbb, #rrggbbaa.
		// if ( preg_match( '/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
		// return $value;
		// }
		//
		// // Functional notations: rgb()/rgba()/hsl()/hsla().
		// if ( preg_match( '/^(rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i', $value ) ) {
		// return $value;
		// }
		//
		// // CSS named colors (e.g. red, transparent, currentColor).
		// if ( preg_match( '/^[a-zA-Z]+$/', $value ) ) {
		// return strtolower( $value );
		// }
		//
		// // Note (gradient picker): accept *-gradient(...) here before enabling.
		//
		// return '';
		// }

		/**
		 * Normalize boolean-ish values: 1 / true / yes / on → true, else false.
		 *
		 * Currently unused. Reserved for future use.
		 *
		 * @param mixed $v The value to normalize.
		 * @return bool
		 */
		// protected function normalize_bool( $v ) {
		// if ( is_bool( $v ) ) {
		// return $v;
		// }
		// $v = is_string( $v ) ? strtolower( $v ) : $v;
		// return in_array( $v, array( 1, '1', 'true', 'yes', 'on' ), true );
		// }
	}

} // END class_exists check
