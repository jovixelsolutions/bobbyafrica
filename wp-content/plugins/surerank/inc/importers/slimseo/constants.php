<?php
/**
 * Slim SEO Constants
 *
 * Defines constants and utility functions for Slim SEO plugin importer.
 *
 * @package SureRank\Inc\Importers
 * @since   1.7.0
 */

namespace SureRank\Inc\Importers\Slimseo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Constants
 */
class Constants {
	/**
	 * Human-readable plugin name.
	 */
	public const PLUGIN_NAME = 'Slim SEO';

	/**
	 * Plugin slug.
	 */
	public const PLUGIN_SLUG = 'slimseo';

	/**
	 * Slim SEO plugin file path.
	 */
	public const PLUGIN_FILE = 'slim-seo/slim-seo.php';

	/**
	 * Prefix for Slim SEO meta keys.
	 */
	public const META_KEY_PREFIX = 'slim_seo';

	/**
	 * Slim SEO option name for global settings.
	 */
	public const OPTION_NAME = 'slim_seo';

	/**
	 * Allowed post and term types for import.
	 */
	public const NOT_ALLOWED_TYPES = [
		'elementor_library',
		'product_shipping_class',
	];

	/**
	 * Mapping of Slim SEO meta fields to SureRank meta fields for posts/terms.
	 */
	public const META_MAPPING = [
		'title'          => 'page_title',
		'description'    => 'page_description',
		'canonical'      => 'canonical_url',
		'facebook_image' => 'facebook_image_url',
		'twitter_image'  => 'twitter_image_url',
	];

	/**
	 * Mapping of Slim SEO robots to SureRank robots.
	 * Slim SEO only uses 'noindex' (boolean 1/0).
	 */
	public const ROBOTS_MAPPING = [
		'noindex' => 'post_no_index',
	];

	/**
	 * Meta keys to exclude from detection.
	 */
	public const EXCLUDED_META_KEYS = [];

	/**
	 * Mapping for social settings.
	 *
	 * Note: Slim SEO has separate default_facebook_image and default_twitter_image,
	 * but SureRank uses a single 'fallback_image' for both. We prioritize Facebook image.
	 */
	public const SOCIAL_SETTINGS_MAPPING = [
		'twitter_site'           => 'twitter_username',
		'default_facebook_image' => 'fallback_image',
	];

	/**
	 * Mapping of Slim SEO features to SureRank settings.
	 *
	 * Maps Slim SEO's 'features' array keys to SureRank setting keys.
	 * Only features that have direct SureRank setting mappings are listed.
	 */
	public const FEATURE_MAPPING = [
		// Free feature toggles.
		'schema'     => 'enable_schemas',
		'sitemaps'   => 'enable_xml_sitemap',
		'images_alt' => 'auto_set_image_alt',
		// Note: Breadcrumbs and Redirection are handled separately in Pro migration.
	];

	/**
	 * Mapping of Slim SEO placeholders to SureRank placeholders.
	 *
	 * Only maps placeholders that exist in BOTH Slim SEO and SureRank.
	 */
	public const PLACEHOLDERS_MAPPING = [
		'site.title'              => '%site_name%',
		'site.description'        => '%tagline%',
		'post_type.labels.plural' => '%archive_title%',
		'post_type.plural'        => '%archive_title%',
		'post.title'              => '%title%',
		'post.excerpt'            => '%excerpt%',
		'post.content'            => '%content%',
		'post.auto_description'   => '%content%',
		'post.date'               => '%published%',
		'post.modified_date'      => '%modified%',
		'term.name'               => '%term_title%',
		'term.description'        => '%term_description%',
		'term.auto_description'   => '%term_description%',
		'author.display_name'     => '%author_name%',
		'author.description'      => '%author_name%',
		'author.auto_description' => '%author_name%',
		'current.year'            => '%currentyear%',
		'current.month'           => '%currentmonth%',
	];

	/**
	 * Get Slim SEO meta data for a post or term.
	 *
	 * @param int  $id          The ID of the post or term.
	 * @param bool $is_taxonomy Whether it is a taxonomy term.
	 * @return array<string, mixed> The Slim SEO meta data.
	 */
	public static function get_slim_seo_meta( int $id, bool $is_taxonomy ): array {
		if ( $is_taxonomy ) {
			$meta = get_term_meta( $id, self::META_KEY_PREFIX, true );
		} else {
			$meta = get_post_meta( $id, self::META_KEY_PREFIX, true );
		}

		return is_array( $meta ) ? $meta : [];
	}

	/**
	 * Get global settings from Slim SEO.
	 *
	 * @return array<string, mixed> The global settings.
	 */
	public static function get_global_settings(): array {
		$settings = get_option( self::OPTION_NAME, [] );
		return is_array( $settings ) ? $settings : [];
	}

	/**
	 * Replace Slim SEO placeholders with SureRank placeholders in a given value.
	 *
	 * Unsupported placeholders are removed entirely to prevent broken templates.
	 *
	 * @param string|array<string> $value The value containing placeholders to replace.
	 * @param string               $separator Separator string used by Slim SEO {{ sep }}.
	 * @return string
	 */
	public static function replace_placeholders( $value, string $separator = ' - ' ): string {
		if ( is_array( $value ) ) {
			$replaced = array_map(
				static fn( $item) => self::replace_placeholders( $item, $separator ),
				$value
			);
			return implode( ', ', $replaced );
		}

		if ( ! is_string( $value ) ) {
			return (string) $value;
		}

		// Replace all placeholders, including unsupported ones (return empty string).
		$result = preg_replace_callback(
			'/\{\{\s*([^}]+?)\s*\}\}/',
			static function ( array $matches ) use ( $separator ): string {
				$key = trim( $matches[1] );
				if ( 'sep' === $key ) {
					return $separator;
				}

				// Return mapped value or empty string for unsupported placeholders.
				return self::PLACEHOLDERS_MAPPING[ $key ] ?? '';
			},
			$value
		);

		// Ensure $result is a string, fallback to original value if replacement fails.
		if ( ! is_string( $result ) ) {
			$result = $value;
		}

		// Clean up extra separators that may remain after removing unsupported placeholders.
		$quoted_sep = preg_quote( $separator, '/' );
		$result     = (string) preg_replace( '/(?:\s*' . $quoted_sep . '\s*)+/', $separator, $result ); // Collapse duplicates.
		$result     = (string) preg_replace( '/^(?:\s*' . $quoted_sep . '\s*)+/', '', $result );        // Remove leading separator.
		$result     = (string) preg_replace( '/(?:\s*' . $quoted_sep . '\s*)+$/', '', $result );        // Remove trailing separator.
		$result     = (string) preg_replace( '/\s+/', ' ', $result );                                   // Normalize whitespace.

		return trim( $result );
	}
}
