<?php
/**
 * Settings data helpers.
 *
 * Source of truth for the allowed option groups and per-group schema (allowed
 * keys, key patterns, sanitization callbacks) consumed by the REST save/validate
 * pipeline (HT_CTC_API_Validator, HT_CTC_Sanitizer, HT_CTC_Data_Processor).
 *
 * @package Click_To_Chat
 * @subpackage commons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Data' ) ) {

	/**
	 * Provides computed settings data for frontend usage.
	 */
	class HT_CTC_Settings_Data {


		// public function __construct() {
		// }


		/**
		 * Allowed settings groups.
		 *
		 * @usedby HT_CTC_Settings_Handler by $allowed_groups to validate settings groups while saving.
		 * @usedby HT_CTC_Admin_Scripts by $initial_settings to get settings data and pass it to ht_ctc_admin_var.initialSettings
		 * @return array
		 */
		public static function get_settings_groups() {
			$values = array(
				'ht_ctc_chat_options',
				'ht_ctc_othersettings',
				'ht_ctc_code_blocks',
				'ht_ctc_greetings_options',
				'ht_ctc_greetings_1',
				'ht_ctc_greetings_2',
				'ht_ctc_greetings_settings',
				'ht_ctc_admin_settings',
				'ht_ctc_s1',
				'ht_ctc_s2',
				'ht_ctc_s3',
				'ht_ctc_s3_1',
				'ht_ctc_s4',
				'ht_ctc_s5',
				'ht_ctc_s6',
				'ht_ctc_s7',
				'ht_ctc_s7_1',
				'ht_ctc_s8',
				'ht_ctc_s99',
				'ht_ctc_cs_options',
				'ht_ctc_woo_options',
				'ht_ctc_r_sqx',
				'ht_ctc_group',
				'ht_ctc_share',
			);

			/**
			 * Filter the allowed settings groups.
			 *
			 * Pro plugin uses this to register its own option groups,
			 * e.g. 'ht_ctc_greetings_pro_1', 'ht_ctc_greetings_pro_2'.
			 *
			 * @param array $values Allowed settings groups.
			 */
			$values = apply_filters( 'ht_ctc_fh_settings_data_groups', $values );

			return $values;
		}


		/**
		 * Option groups saved by wholesale replace instead of deep merge.
		 *
		 * The client sends the COMPLETE group (its card carries .ctc-group-sync) and the
		 * server overwrites, so a deleted row can never survive as a stale key. See
		 * HT_CTC_Data_Processor::is_replace_entire_group() for why this beats merge+remove
		 * for cohesive collections.
		 *
		 * Lives here rather than on the data processor because two very different stages
		 * need the same answer: the processor (overwrite vs merge) and HT_CTC_Sanitizer
		 * (whether to grandfather keys already in the DB). Two copies of this list would
		 * drift, and the failure that follows is silent data loss.
		 *
		 * @return array Replace-strategy group keys.
		 */
		public static function get_replace_groups() {

			$values = array();

			/**
			 * Option groups saved by wholesale replace. Pro registers its collection groups
			 * here, e.g. 'ht_ctc_greetings_pro_1', 'ht_ctc_greetings_pro_2'.
			 *
			 * @param array $values Replace-strategy group keys.
			 */
			$values = apply_filters( 'ht_ctc_fh_replace_groups', $values );

			return is_array( $values ) ? $values : array();
		}

		/**
		 * Whether an option group is saved by the replace strategy.
		 *
		 * @param string $group Option group name.
		 * @return bool
		 */
		public static function is_replace_group( $group ) {
			return in_array( $group, self::get_replace_groups(), true );
		}

		/**
		 * Full per-group settings schema (explicit build-all).
		 *
		 * Aggregator: builds the whole schema map by calling get_schema() for each
		 * settings group. The per-group definitions live in HT_CTC_Settings_Schema
		 * (one method per option group). Groups that declare no schema
		 * are skipped here. NOTE: an absent schema does NOT mean
		 * "accept any key" — HT_CTC_Sanitizer fails closed and drops every key of an
		 * array payload for a schema-less group; only scalar group values pass
		 * through the default sanitizer (see get_schema() docblock).
		 *
		 * Call this only when the entire map is genuinely needed. To read one group,
		 * use get_schema( $group ) — it dispatches a single method and never builds all.
		 *
		 * Each group's slice describes:
		 *   - allowed_keys           (string[]): allow-listed top-level keys for the group.
		 *   - allowed_keys_patterns  (string[]): regex patterns for dynamic/repeating top-level keys.
		 *   - sanitization_callbacks (array<string,string>): field key => sanitizer callback.
		 *
		 * @return array Schema keyed by option group.
		 */
		public static function get_settings_schema() {
			$values = array();

			foreach ( self::get_settings_groups() as $group ) {
				$slice = self::get_schema( $group );

				// Skip groups that declare no schema (pass-through, e.g. ht_ctc_r_sqx).
				if ( empty( $slice['allowed_keys'] ) && empty( $slice['allowed_keys_patterns'] ) && empty( $slice['sanitization_callbacks'] ) ) {
					continue;
				}

				$values[ $group ] = $slice;
			}

			/**
			 * Filter the full per-group settings schema.
			 *
			 * Pro plugin / add-ons use this to register allowed keys, key patterns and
			 * sanitization callbacks for their own option groups, or to extend existing ones.
			 * For a single group, prefer the 'ht_ctc_fh_settings_schema_per_group' filter.
			 *
			 * @param array $values Per-group settings schema.
			 */
			$values = apply_filters( 'ht_ctc_fh_settings_schema', $values );

			return $values;
		}


		/**
		 * Easy accessor for a single option group's schema — the "get_option() for schema".
		 *
		 * Mirrors HT_CTC_Utils::get_option(): lazy-loads + instantiates HT_CTC_Settings_Schema
		 * once, then dispatches ONLY the per-group method named after $group (e.g.
		 * ht_ctc_chat_options()) and returns just that slice — it never builds the whole
		 * schema. The result is cached per group for the rest of the request. Dispatch is
		 * guarded to ht_ctc_*-prefixed methods.
		 *
		 * Consumers (save handler / validator / sanitizer) work one group at a time, so this
		 * is the spot-call path: ask for the group you need, get just that slice. For the
		 * rare case the full map is needed, call get_settings_schema() explicitly.
		 *
		 * @param string $group Option group name (e.g. 'ht_ctc_chat_options').
		 * @return array The group's schema slice. Unknown groups return a well-formed empty
		 *               slice so callers never need isset() guards. NOTE: HT_CTC_Sanitizer
		 *               fails CLOSED on an empty slice — an array payload for a group with no
		 *               allowed_keys/patterns has every key dropped (only a scalar group value
		 *               passes through the default sanitizer). So a group registered via
		 *               'ht_ctc_fh_settings_data_groups' (e.g. by PRO) must also register its
		 *               schema (e.g. via 'ht_ctc_fh_settings_schema_per_group'), or its array
		 *               settings will silently save as empty.
		 */
		public static function get_schema( $group ) {
			static $cache = array();
			if ( is_string( $group ) && isset( $cache[ $group ] ) ) {
				return $cache[ $group ];
			}

			$slice = array(
				'allowed_keys'           => array(),
				'allowed_keys_patterns'  => array(),
				'sanitization_callbacks' => array(),
			);

			// Dynamic dispatch to the per-group method on HT_CTC_Settings_Schema
			// (e.g. ht_ctc_chat_options()), mirroring HT_CTC_Utils::get_option().
			// Guard: only ht_ctc_*-prefixed methods are dispatchable.
			$schema = self::schema_instance();
			if ( $schema && is_string( $group ) && 0 === strpos( $group, 'ht_ctc_' ) && is_callable( array( $schema, $group ) ) ) {
				$group_schema = $schema->$group();
				if ( is_array( $group_schema ) ) {
					if ( isset( $group_schema['allowed_keys'] ) && is_array( $group_schema['allowed_keys'] ) ) {
						$slice['allowed_keys'] = $group_schema['allowed_keys'];
					}
					if ( isset( $group_schema['allowed_keys_patterns'] ) && is_array( $group_schema['allowed_keys_patterns'] ) ) {
						$slice['allowed_keys_patterns'] = $group_schema['allowed_keys_patterns'];
					}
					if ( isset( $group_schema['sanitization_callbacks'] ) && is_array( $group_schema['sanitization_callbacks'] ) ) {
						$slice['sanitization_callbacks'] = $group_schema['sanitization_callbacks'];
					}
				}
			}

			/**
			 * Filter any single option group's schema slice (generic, fires for every group).
			 *
			 * Pro plugin / add-ons register schema for their own option groups here (incl.
			 * groups that have no method), or extend an existing group's allowed keys /
			 * patterns / sanitization callbacks. For a specific built-in group, the
			 * per-group hook 'ht_ctc_fh_settings_schema_<group>' (declared in
			 * HT_CTC_Settings_Schema) is also available.
			 *
			 * @param array  $slice Schema slice (allowed_keys, allowed_keys_patterns, sanitization_callbacks).
			 * @param string $group Option group name.
			 */
			$slice = apply_filters( 'ht_ctc_fh_settings_schema_per_group', $slice, $group );

			if ( is_string( $group ) ) {
				$cache[ $group ] = $slice;
			}

			return $slice;
		}

		/**
		 * Lazy-load and cache the HT_CTC_Settings_Schema instance.
		 *
		 * Mirrors how HT_CTC_Utils::get_option() loads and caches HT_CTC_Defaults:
		 * loaded once, with a false sentinel to avoid repeated load attempts.
		 *
		 * @return HT_CTC_Settings_Schema|false Instance, or false if it cannot be loaded.
		 */
		private static function schema_instance() {
			static $instance = null;

			if ( null !== $instance ) {
				return $instance;
			}

			if ( ! class_exists( 'HT_CTC_Settings_Schema' ) ) {
				if ( class_exists( 'HT_CTC_Utils' ) ) {
					HT_CTC_Utils::load_class( 'new/inc/commons/class-ht-ctc-settings-schema.php', 'HT_CTC_Settings_Schema' );
				} elseif ( defined( 'HT_CTC_PLUGIN_DIR' ) && file_exists( HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-settings-schema.php' ) ) {
					require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-settings-schema.php';
				}
			}

			$instance = class_exists( 'HT_CTC_Settings_Schema' ) ? new HT_CTC_Settings_Schema() : false;

			return $instance;
		}

		/**
		 * Allowed top-level keys for a single option group.
		 *
		 * @param string $group Option group name.
		 * @return string[] Allowed keys, or empty array if the group declares none.
		 */
		public static function get_allowed_keys( $group ) {
			$slice = self::get_schema( $group );
			return isset( $slice['allowed_keys'] ) && is_array( $slice['allowed_keys'] ) ? $slice['allowed_keys'] : array();
		}

		/**
		 * Allowed top-level key regex patterns for a single option group.
		 *
		 * @param string $group Option group name.
		 * @return string[] Allowed key patterns, or empty array if the group declares none.
		 */
		public static function get_allowed_keys_patterns( $group ) {
			$slice = self::get_schema( $group );
			return isset( $slice['allowed_keys_patterns'] ) && is_array( $slice['allowed_keys_patterns'] ) ? $slice['allowed_keys_patterns'] : array();
		}

		/**
		 * Field => sanitizer-callback map for a single option group.
		 *
		 * @param string $group Option group name.
		 * @return array<string, string> Field key => sanitizer callback, or empty array.
		 */
		public static function get_sanitization_callbacks( $group ) {
			$slice = self::get_schema( $group );
			return isset( $slice['sanitization_callbacks'] ) && is_array( $slice['sanitization_callbacks'] ) ? $slice['sanitization_callbacks'] : array();
		}


		/**
		 * Settings groups to load initially.
		 *
		 * @return array
		 */
		public static function get_initial_settings_groups() {
			$values = array(
				'ht_ctc_chat_options',
			);

			return $values;
		}

		/**
		 * Map of field => sanitizer callback hint.
		 *
		 * @deprecated This method is deprecated. now sanitizations added by option group name instead of key name.
		 * @return array
		 */
		public static function get_sanitize_map() {
			$values = array(

				// general (ht_ctc_chat_options)
				// In admin (2026 UI) we are formatting (wa_number) the number on save to DB also.
				'number'                   => 'ctc_sanitize_whatsapp_number',
				'r_nums'                   => 'ctc_sanitize_whatsapp_number',
				// 'whatsapp_number'          => 'ctc_sanitize_whatsapp_number',
				'call_to_action'           => 'ctc_sanitize_emoji_text',
				'pre_filled'               => 'ctc_sanitize_emoji_textarea',

				// general - position / layout (ht_ctc_chat_options)
				'side_1_value'             => 'ctc_sanitize_normalize_css_suffix',
				'side_2_value'             => 'ctc_sanitize_normalize_css_suffix',
				'mobile_side_1_value'      => 'ctc_sanitize_normalize_css_suffix',
				'mobile_side_2_value'      => 'ctc_sanitize_normalize_css_suffix',

				// advanced (ht_ctc_othersettings / other-settings)
				'custom_css'               => 'ctc_sanitize_custom_css',

				// customize (ht_ctc_s1 - ht_ctc_s99)
				// Defaults for some customize fields are handled in ctc_sanitize_normalize_css_suffix using the field key.
				's1_icon_size'             => 'ctc_sanitize_normalize_css_suffix',
				's2_img_size'              => 'ctc_sanitize_normalize_css_suffix',
				's3_img_size'              => 'ctc_sanitize_normalize_css_suffix',
				's3_padding'               => 'ctc_sanitize_normalize_css_suffix',
				's4_img_size'              => 'ctc_sanitize_normalize_css_suffix',
				's5_img_height'            => 'ctc_sanitize_normalize_css_suffix',
				's5_img_width'             => 'ctc_sanitize_normalize_css_suffix',
				's5_content_height'        => 'ctc_sanitize_normalize_css_suffix',
				's5_content_width'         => 'ctc_sanitize_normalize_css_suffix',
				's7_icon_size'             => 'ctc_sanitize_normalize_css_suffix',
				's7_border_size'           => 'ctc_sanitize_normalize_css_suffix',
				's7_border_radius'         => 'ctc_sanitize_normalize_css_suffix',
				's8_text_size'             => 'ctc_sanitize_normalize_css_suffix',
				's8_icon_size'             => 'ctc_sanitize_normalize_css_suffix',
				's99_desktop_img_height'   => 'ctc_sanitize_normalize_css_suffix',
				's99_desktop_img_width'    => 'ctc_sanitize_normalize_css_suffix',
				's99_mobile_img_height'    => 'ctc_sanitize_normalize_css_suffix',
				's99_mobile_img_width'     => 'ctc_sanitize_normalize_css_suffix',
				// URL fields must use esc_url_raw to reject javascript:/data: URIs.
				// Without this they would fall through to sanitize_text_field which
				// silently accepts dangerous schemes (stored XSS on style-99 output).
				's99_dekstop_img_url'      => 'ctc_sanitize_url',
				's99_mobile_img_url'       => 'ctc_sanitize_url',
				'cta_font_size'            => 'ctc_sanitize_normalize_css_suffix',

				// greetings (ht_ctc_greetings_options, ht_ctc_greetings_1, ht_ctc_greetings_2, ht_ctc_greetings_settings)
				'header_content'           => 'ctc_sanitize_text_editor',
				'main_content'             => 'ctc_sanitize_text_editor',
				'bottom_content'           => 'ctc_sanitize_text_editor',
				'opt_in'                   => 'ctc_sanitize_text_editor',

				// woocommerce (ht_ctc_woo_options)
				'woo_call_to_action'       => 'sanitize_text_field',
				'woo_shop_call_to_action'  => 'sanitize_text_field',
				'woo_pre_filled'           => 'sanitize_textarea_field',
				'woo_shop_pre_filled'      => 'sanitize_textarea_field',
				'woo_single_margin_top'    => 'ctc_sanitize_normalize_css_suffix',
				'woo_single_margin_bottom' => 'ctc_sanitize_normalize_css_suffix',
				'woo_single_margin_left'   => 'ctc_sanitize_normalize_css_suffix',
				'woo_single_margin_right'  => 'ctc_sanitize_normalize_css_suffix',
				'woo_shop_margin_top'      => 'ctc_sanitize_normalize_css_suffix',
				'woo_shop_margin_bottom'   => 'ctc_sanitize_normalize_css_suffix',
				'woo_shop_margin_left'     => 'ctc_sanitize_normalize_css_suffix',
				'woo_shop_margin_right'    => 'ctc_sanitize_normalize_css_suffix',
			);

			$values = apply_filters( 'ht_ctc_fh_settings_sanitize_map', $values );

			return $values;
		}
	}

	// new HT_CTC_Settings_Data();

} // END class_exists check
