<?php
/**
 * Utils class
 * Handles utility functions for the SureRank plugin API's
 *
 * @package SureRank\Inc\API
 */

namespace SureRank\Inc\API;

/**
 * Class Utils
 *
 * Provides utility functions for the SureRank plugin API's.
 */
class Utils {

	/**
	 * Recursively decode HTML entities in arrays, objects or strings.
	 *
	 * @param mixed $value Array, object, string or other.
	 * @return mixed Decoded value of the same type.
	 */
	public static function decode_html_entities_recursive( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = self::decode_html_entities_recursive( $item );
			}
			return $value;
		}

		if ( is_object( $value ) ) {
			foreach ( get_object_vars( $value ) as $prop => $item ) {
				$value->{$prop} = self::decode_html_entities_recursive( $item );
			}
			return $value;
		}

		if ( is_string( $value ) ) {
			return html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		// leave ints, bools, null, etc. untouched.
		return $value;
	}

	/**
	 * Process options data and return new option values
	 *
	 * @param array<string, mixed> $all_options All available options.
	 * @param array<string, mixed> $data Data to process.
	 * @param int                  $object_id Post ID or Term ID.
	 * @param string               $object_type Post type or taxonomy name.
	 * @param bool                 $is_taxonomy Whether this is a taxonomy.
	 * @return array<string, mixed> Processed option values.
	 */
	public static function process_option_values( array $all_options, array $data, int $object_id = 0, string $object_type = '', bool $is_taxonomy = false ): array {
		$processed_options = [];

		foreach ( $all_options as $option_name => $option_value ) {
			$new_option_value = self::process_single_option_value( $option_name, $option_value, $data, $object_id, $object_type, $is_taxonomy );

			if ( ! empty( $new_option_value ) ) {
				$processed_options[ $option_name ] = $new_option_value;
			}
		}

		return $processed_options;
	}

	/**
	 * Get extended meta template values
	 *
	 * @param int    $object_id Post ID or Term ID.
	 * @param string $object_type Post type or taxonomy name.
	 * @param bool   $is_taxonomy Whether this is a taxonomy.
	 * @return array<string, mixed> Extended meta template values.
	 * @since 1.6.2
	 */
	public static function get_extended_meta_values( int $object_id, string $object_type, bool $is_taxonomy ): array {
		if ( $object_id <= 0 || empty( $object_type ) ) {
			return [];
		}

		$global_values = \SureRank\Inc\Functions\Settings::get();
		return apply_filters(
			'surerank_prep_post_meta_extended_values',
			[],
			$object_type,
			$is_taxonomy,
			$global_values,
			$object_id
		);
	}

	/**
	 * Process a single option value
	 *
	 * @param string               $option_name Option name.
	 * @param mixed                $option_value Option value.
	 * @param array<string, mixed> $data Data to process.
	 * @param int                  $object_id Post ID or Term ID.
	 * @param string               $object_type Post type or taxonomy name.
	 * @param bool                 $is_taxonomy Whether this is a taxonomy.
	 * @return mixed Processed option value.
	 * @since 1.6.2
	 */
	private static function process_single_option_value( string $option_name, $option_value, array $data, int $object_id = 0, string $object_type = '', bool $is_taxonomy = false ) {
		if ( is_array( $option_value ) ) {
			return self::process_array_option_value( $option_name, $option_value, $data, $object_id, $object_type, $is_taxonomy );
		}

		return self::process_scalar_option_value( $option_name, $data );
	}

	/**
	 * Process array option value
	 *
	 * @param string               $option_name Option name.
	 * @param array<string, mixed> $option_value Option value.
	 * @param array<string, mixed> $data Data to process.
	 * @param int                  $object_id Post ID or Term ID.
	 * @param string               $object_type Post type or taxonomy name.
	 * @param bool                 $is_taxonomy Whether this is a taxonomy.
	 * @return array<string, mixed>
	 */
	private static function process_array_option_value( string $option_name, array $option_value, array $data, int $object_id = 0, string $object_type = '', bool $is_taxonomy = false ): array {
		if ( empty( $option_value ) ) {
			return [ $option_name => $data[ $option_name ] ?? $option_value ];
		}

		// Get extended meta templates if we have context.
		$extended_meta_values = self::get_extended_meta_values( $object_id, $object_type, $is_taxonomy );

		$new_option_value = [];
		foreach ( $option_value as $key => $value ) {
			if ( isset( $data[ $key ] ) ) {
				// If user provided value, use it. Otherwise use extended template or base default.
				$fallback_value           = isset( $extended_meta_values[ $key ] ) && $extended_meta_values[ $key ] !== '' ? $extended_meta_values[ $key ] : $value;
				$new_option_value[ $key ] = $data[ $key ] !== '' ? $data[ $key ] : $fallback_value;
			}
		}

		return $new_option_value;
	}

	/**
	 * Process scalar option value
	 *
	 * @param string               $option_name Option name.
	 * @param array<string, mixed> $data Data to process.
	 * @return mixed
	 */
	private static function process_scalar_option_value( string $option_name, array $data ) {
		if ( ! isset( $data[ $option_name ] ) ) {
			return null;
		}

		return $data[ $option_name ] === '' ? false : $data[ $option_name ];
	}
}
