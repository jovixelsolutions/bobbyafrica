<?php
/**
 * Modern Cart MCP Helper.
 *
 * @package modern-cart
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MCW_ZipWP_Helper.
 *
 * This class provides read and write access to Modern Cart settings
 * for MCP (Model Context Protocol) integrations. It acts as a stable API
 * layer that delegates to existing Modern Cart helper methods.
 *
 * @since 1.0.0
 */
class MCW_ZipWP_Helper {

	/**
	 * Get all Modern Cart settings.
	 *
	 * This method is intended for MCP / Abilities usage.
	 * Returns all Modern Cart settings by delegating to the existing
	 * Helper::get_option() method for each setting group.
	 *
	 * @return array<string, mixed> Complete array of Modern Cart settings organized by groups.
	 * @since 1.0.0
	 */
	public static function get_settings() {
		// Get the Helper singleton instance.
		$helper = \ModernCart\Inc\Helper::get_instance();

		// Return all settings grouped by their option keys.
		return array(
			// Fetch main feature toggle settings.
			MODERNCART_MAIN_SETTINGS       => $helper->get_option( MODERNCART_MAIN_SETTINGS ),
			// Fetch cart behavior and content settings.
			MODERNCART_SETTINGS            => $helper->get_option( MODERNCART_SETTINGS ),
			// Fetch floating cart icon settings.
			MODERNCART_FLOATING_SETTINGS   => $helper->get_option( MODERNCART_FLOATING_SETTINGS ),
			// Fetch visual styling settings.
			MODERNCART_APPEARANCE_SETTINGS => $helper->get_option( MODERNCART_APPEARANCE_SETTINGS ),
		);
	}

	/**
	 * Update Modern Cart settings.
	 *
	 * This method is intended for MCP / Abilities usage.
	 * Updates settings by delegating validation and sanitization to Modern Cart's
	 * existing Helper class with schema-based validation.
	 *
	 * @param array<string, mixed> $settings Array of settings organized by group.
	 * @param array<string, mixed> $context Optional context information.
	 * @return array<string, mixed> Result with applied, rejected, and skipped settings.
	 * @since 1.0.0
	 */
	public static function update_settings( $settings, $context = array() ) {
		// Validate that settings is a non-empty array.
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			// Return empty result with error message.
			return array(
				'applied'  => array(),
				'rejected' => array(),
				'skipped'  => array(),
				'messages' => array( __( 'No valid settings provided', 'modern-cart' ) ),
			);
		}

		// Get the Helper singleton instance.
		$helper = \ModernCart\Inc\Helper::get_instance();
		// Retrieve schema with type information.
		$defaults = $helper->get_defaults( true );

		// Initialize array for successfully applied settings.
		$applied = array();
		// Initialize array for rejected settings with reasons.
		$rejected = array();
		// Initialize array for skipped settings.
		$skipped = array();
		// Initialize array for status messages.
		$messages = array();

		// Loop through each setting group.
		foreach ( $settings as $setting_group => $group_values ) {
			// Skip if group values is not an array.
			if ( ! is_array( $group_values ) ) {
				// Add to skipped list.
				$skipped[] = $setting_group;
				continue;
			}

			// Get the WordPress option name for this setting group.
			$option_name = self::get_option_name( $setting_group );

			// Reject if option name is invalid.
			if ( ! $option_name ) {
				// Add rejection reason.
				$rejected[ $setting_group ] = __( 'Invalid setting group', 'modern-cart' );
				continue;
			}

			// Reject if schema doesn't exist for this option.
			if ( ! isset( $defaults[ $option_name ] ) ) {
				// Add rejection reason.
				$rejected[ $setting_group ] = __( 'Setting group not found in schema', 'modern-cart' );
				continue;
			}

			// Get the schema definition for this setting group.
			/**
			 * The schema for this option: an array where each key maps to a type/value pair.
			 *
			 * @var array<string, array{type: string, value: mixed}>
			 */
			$schema = $defaults[ $option_name ];

			/**
			 * Current values stored in the database for this option.
			 *
			 * @var array<string, mixed>
			 */
			$current_values = get_option( $option_name, array() );
			// Track which keys were successfully updated.
			$updated_keys = array();

			// Loop through each individual setting in the group.
			foreach ( $group_values as $key => $value ) {
				// Reject if key doesn't exist in schema.
				if ( ! isset( $schema[ $key ] ) ) {
					// Add rejection reason with full path.
					$rejected[ "{$setting_group}.{$key}" ] = __( 'Key not found in schema', 'modern-cart' );
					continue;
				}

				// Get the field schema entry and ensure it is a valid array.
				$field_schema = $schema[ $key ];
				if ( ! is_array( $field_schema ) ) {
					$rejected[ "{$setting_group}.{$key}" ] = __( 'Invalid schema entry', 'modern-cart' );
					continue;
				}

				// Get the data type from schema.
				$type = $field_schema['type'];
				// Get the default value from schema.
				$field_default = isset( $field_schema['value'] ) ? $field_schema['value'] : '';
				// Sanitize value based on its type.
				$sanitized_value = self::sanitize_value( $value, $type, $field_default );

				// Update the value in current settings array.
				$current_values[ $key ] = $sanitized_value;
				// Track this key as updated.
				$updated_keys[] = $key;
			}

			// Save to database if any keys were updated.
			if ( ! empty( $updated_keys ) ) {
				// Persist the updated values.
				update_option( $option_name, $current_values );
				// Track which keys were applied for this group.
				$applied[ $setting_group ] = $updated_keys;
			}
		}

		// Add success message if any settings were applied.
		if ( ! empty( $applied ) ) {
			// Append success message.
			$messages[] = __( 'Settings updated successfully', 'modern-cart' );
		}

		// Return result summary.
		return array(
			'applied'  => $applied,
			'rejected' => $rejected,
			'skipped'  => $skipped,
			'messages' => $messages,
		);
	}

	/**
	 * Check if onboarding is complete.
	 *
	 * @return bool True if onboarding is complete, false otherwise.
	 * @since 1.0.0
	 */
	public static function is_onboarding_complete() {
		// Check if the onboarding completion flag is set in database.
		return (bool) get_option( 'moderncart_onboarding_complete', false );
	}

	/**
	 * Check if Modern Cart Pro is active.
	 *
	 * @return bool True if Pro is active, false otherwise.
	 * @since 1.0.0
	 */
	public static function is_pro_active() {
		// Get the Helper singleton instance.
		$helper = \ModernCart\Inc\Helper::get_instance();
		// Check if Pro plugin status is active.
		return 'active' === $helper->get_pro_status();
	}

	/**
	 * Get option name from setting group.
	 *
	 * @param string $setting_group Setting group identifier.
	 * @return string|false Option name or false if invalid.
	 */
	private static function get_option_name( $setting_group ) {
		// Define mapping of setting group identifiers to WordPress option names.
		$mapping = array(
			// Main feature toggle settings option name.
			'moderncart_setting'    => MODERNCART_MAIN_SETTINGS,
			// Cart behavior and content settings option name.
			'moderncart_cart'       => MODERNCART_SETTINGS,
			// Floating cart icon settings option name.
			'moderncart_floating'   => MODERNCART_FLOATING_SETTINGS,
			// Visual styling settings option name.
			'moderncart_appearance' => MODERNCART_APPEARANCE_SETTINGS,
		);

		// Return option name if exists, otherwise false.
		return isset( $mapping[ $setting_group ] ) ? $mapping[ $setting_group ] : false;
	}

	/**
	 * Sanitize value based on type.
	 *
	 * @param mixed  $value Value to sanitize.
	 * @param string $type Type of value.
	 * @param mixed  $default Default value to use if sanitization fails.
	 * @return mixed Sanitized value.
	 */
	private static function sanitize_value( $value, $type, $default ) {
		// Sanitize based on the data type.
		switch ( $type ) {
			case 'boolean':
				// Cast to boolean.
				return (bool) $value;
			case 'number':
				// Convert to integer if numeric, otherwise use default.
				return is_numeric( $value ) ? (int) $value : $default;
			case 'hex':
				// Sanitize hex color value.
				$sanitized = sanitize_hex_color( is_scalar( $value ) ? (string) $value : '' );
				// Return sanitized color or default if invalid.
				return $sanitized ? $sanitized : $default;
			case 'string':
			default:
				// Sanitize text field for strings and unknown types.
				return sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		}
	}
}
