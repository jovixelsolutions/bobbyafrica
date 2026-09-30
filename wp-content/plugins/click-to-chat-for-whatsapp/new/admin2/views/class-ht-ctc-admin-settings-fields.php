<?php
/**
 * Admin Settings Fields
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Settings_Fields' ) ) {

	/**
	 * Admin settings fields class.
	 */
	class HT_CTC_Admin_Settings_Fields {

		/**
		 * REST group identifier => static method on this class.
		 *
		 * This is the single source of truth for which field-fetch methods are reachable
		 * via the REST `/get-fields/` endpoint. When you add a new tab method below, add
		 * one matching entry here — both edits stay in this file.
		 *
		 * PRO and other extensions can extend the map via the
		 * `ht_ctc_fh_admin_settings_fields_groups` filter.
		 *
		 * @return array<string,string>
		 */
		public static function get_group_method_map() {
			$groups = array(
				'general-settings'          => 'general_settings',
				'display-settings'          => 'display_settings',
				'greetings-settings'        => 'greetings_settings',
				'analytics-settings'        => 'analytics_settings',
				'advanced-settings'         => 'advanced_settings',

				/*
				 * Customize: settings now open inline at the style picker, via the
				 * Contextual Settings Panel below. Unmapped, customize_settings()
				 * commented out below, and the tab unregistered in HT_CTC_Admin_Dashboard
				 * — restore all three together, or none. HT_CTC_Settings_Customize itself
				 * is untouched and still holds the full field list.
				 *
				 * 'customize-settings' => 'customize_settings',
				 */
				'contextual-styles'         => 'contextual_styles',
				'contextual-greetings'      => 'contextual_greetings',
				'group-settings'            => 'group_settings',
				'share-settings'            => 'share_settings',
				'woo-settings'              => 'woo_settings',
				'woo-add-whatsapp-settings' => 'woo_add_whatsapp_settings',
				'woo-overwrite-settings'    => 'woo_overwrite_settings',
				'support-settings'          => 'support_settings',
				'pro-features'              => 'pro_features',
			);

			/**
			 * Filter the REST group → method map for HT_CTC_Admin_Settings_Fields.
			 *
			 * Each mapped value must be a public static method on this class (or a subclass)
			 * that returns an array of field definitions.
			 *
			 * @param array<string,string> $groups Group slug => method name.
			 */
			return apply_filters( 'ht_ctc_fh_admin_settings_fields_groups', $groups );
		}

		/**
		 * Helper to lazy load tab classes and fetch fields safely.
		 *
		 * @param string $tab The tab file identifier.
		 * @param string $class_name The expected class name.
		 * @return array
		 */
		private static function get_tab_fields( $tab, $class_name ) {
			HT_CTC_Utils::load_class( 'new/admin2/views/tabs/class-ht-ctc-settings-' . $tab . '.php', $class_name );

			if ( is_callable( array( $class_name, 'fields' ) ) ) {
				return $class_name::fields();
			}

			return array();
		}

		/**
		 * Helper to lazy load contextual classes and fetch fields safely.
		 *
		 * @param string $context The contextual file identifier.
		 * @param string $class_name The expected class name.
		 * @return array
		 */
		private static function get_contextual_fields( $context, $class_name ) {
			HT_CTC_Utils::load_class( 'new/admin2/views/contextual/class-ht-ctc-contextual-' . $context . '.php', $class_name );

			if ( is_callable( array( $class_name, 'fields' ) ) ) {
				return $class_name::fields();
			}

			return array();
		}

		/**
		 * General Settings
		 *
		 * @return array
		 */
		public static function general_settings() {
			return self::get_tab_fields( 'general', 'HT_CTC_Settings_General' );
		}

		/**
		 * Display Settings
		 *
		 * @return array
		 */
		public static function display_settings() {
			return self::get_tab_fields( 'display', 'HT_CTC_Settings_Display' );
		}

		/**
		 * Greetings Settings
		 *
		 * @return array
		 */
		public static function greetings_settings() {
			return self::get_tab_fields( 'greetings', 'HT_CTC_Settings_Greetings' );
		}

		/**
		 * Analytics Settings
		 *
		 * @return array
		 */
		public static function analytics_settings() {
			return self::get_tab_fields( 'analytics', 'HT_CTC_Settings_Analytics' );
		}

		/**
		 * Advanced Settings
		 *
		 * @return array
		 */
		public static function advanced_settings() {
			return self::get_tab_fields( 'advanced', 'HT_CTC_Settings_Advanced' );
		}

		/**
		 * Customize Settings
		 *
		 * @deprecated 4.43 Will be replaced by Contextual Settings Panel fields loader.
		 * @return array
		 */
		// public static function customize_settings() {
		// return self::get_tab_fields( 'customize', 'HT_CTC_Settings_Customize' );
		// }

		/**
		 * Contextual Styles Settings Group
		 *
		 * @return array
		 */
		public static function contextual_styles() {
			return self::get_contextual_fields( 'styles', 'HT_CTC_Contextual_Styles' );
		}

		/**
		 * Contextual Greetings Settings Group
		 *
		 * @return array
		 */
		public static function contextual_greetings() {
			return self::get_contextual_fields( 'greetings', 'HT_CTC_Contextual_Greetings' );
		}

		/**
		 * Group Settings
		 *
		 * @return array
		 */
		public static function group_settings() {
			return self::get_tab_fields( 'group', 'HT_CTC_Settings_Group' );
		}

		/**
		 * Share Settings
		 *
		 * @return array
		 */
		public static function share_settings() {
			return self::get_tab_fields( 'share', 'HT_CTC_Settings_Share' );
		}

		/**
		 * WooCommerce Settings
		 *
		 * @return array
		 */
		public static function woo_settings() {
			return self::get_tab_fields( 'woo', 'HT_CTC_Settings_Woo' );
		}

		/**
		 * WooCommerce Settings Advanced
		 *
		 * @return array
		 */
		public static function woo_add_whatsapp_settings() {
			HT_CTC_Utils::load_class( 'new/admin2/views/tabs/class-ht-ctc-settings-woo.php', 'HT_CTC_Settings_Woo' );
			return HT_CTC_Settings_Woo::fields_advanced();
		}

		/**
		 * WooCommerce Settings Overwrite
		 *
		 * @return array
		 */
		public static function woo_overwrite_settings() {
			HT_CTC_Utils::load_class( 'new/admin2/views/tabs/class-ht-ctc-settings-woo.php', 'HT_CTC_Settings_Woo' );
			return HT_CTC_Settings_Woo::fields_overwrite();
		}

		/**
		 * Support Settings
		 *
		 * @return array
		 */
		public static function support_settings() {
			return self::get_tab_fields( 'support', 'HT_CTC_Settings_Support' );
		}

		/**
		 * Pro Features Pitch Tab
		 *
		 * @return array
		 */
		public static function pro_features() {
			return self::get_tab_fields( 'pro-features', 'HT_CTC_Settings_Pro_Features' );
		}

		/**
		 * License Settings (Pro only — fields injected via filter).
		 *
		 * @return array
		 */
		public static function license_settings() {
			return apply_filters( 'ht_ctc_fh_settings_fields_license', array() );
		}
	}
}
