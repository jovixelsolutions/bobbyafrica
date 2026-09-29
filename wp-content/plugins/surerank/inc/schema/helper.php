<?php
/**
 * Helper
 *
 * This file will handle functionality for all Helper.
 *
 * @package surerank
 * @since 1.0.0
 */

namespace SureRank\Inc\Schema;

use SureRank\Inc\Traits\Get_Instance;

/**
 * Helper
 * This class will handle functionality for all Helper.
 *
 * @since 1.0.0
 */
class Helper {

	use Get_Instance;

	public const UNSUPPORTED_TAXONOMIES = [
		'wp_theme',
		'wp_template_part_area',
		'link_category',
		'nav_menu',
		'post_format',
		'mb-views-category',
	];

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Get Taxonomies
	 *
	 * @param array<string, mixed> $args Arguments.
	 * @return array<string, mixed>|array<int, array<string, string>>
	 * @since 1.0.0
	 */
	public function get_taxonomies( $args = [] ) {
		$unsupported = self::UNSUPPORTED_TAXONOMIES;
		$taxonomies  = get_taxonomies( $args, 'objects' );
		if ( empty( $taxonomies ) ) {
			return [];
		}
		$taxonomies = array_diff_key( $taxonomies, array_flip( $unsupported ) );
		$taxonomies = array_map(
			static function( $taxonomy ) {
				return [
					'slug' => esc_attr( $taxonomy->name ),
					'name' => esc_html( $taxonomy->label ),
				];
			},
			$taxonomies
		);

		return array_values( $taxonomies );
	}

	/**
	 * Normalize
	 *
	 * @param string $key key.
	 * @return string
	 * @since 1.0.0
	 */
	public function normalize( $key ) {
		return str_replace( '-', '_', $key );
	}

	/**
	 * Get property
	 *
	 * Retrieves a specific property based on the provided name and arguments.
	 *
	 * @param string               $name The name of the property to retrieve.
	 * @param array<string, mixed> $args Optional. Additional arguments to customize the property retrieval. Default is an empty array.
	 * @return array<string, mixed> The retrieved property.
	 * @since 1.0.0
	 */
	public function get_property( $name, $args = [] ) {
		return Properties::get( $name, $args );
	}

}
