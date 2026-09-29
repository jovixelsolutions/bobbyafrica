<?php
/**
 * AIOSEO Constants
 *
 * Defines constants and utility functions for AIOSEO plugin importer.
 *
 * @package SureRank\Inc\Importers
 * @since   1.7.0
 */

namespace SureRank\Inc\Importers\Aioseo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Constants
 *
 * @since 1.7.0
 */
class Constants {

	/**
	 * Human-readable plugin name.
	 *
	 * @since 1.7.0
	 */
	public const PLUGIN_NAME = 'All in One SEO';

	/**
	 * Plugin Slug.
	 *
	 * @since 1.7.0
	 */
	public const PLUGIN_SLUG = 'aioseo';

	/**
	 * AIOSEO plugin file path.
	 *
	 * @since 1.7.0
	 */
	public const PLUGIN_FILE = 'all-in-one-seo-pack/all_in_one_seo_pack.php';

	/**
	 * AIOSEO Pro plugin file path.
	 *
	 * @since 1.7.0
	 */
	public const PLUGIN_FILE_PRO = 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php';

	/**
	 * Prefix for AIOSEO meta keys.
	 *
	 * @since 1.7.0
	 */
	public const META_KEY_PREFIX = '_aioseo_';

	/**
	 * AIOSEO global robots settings default.
	 *
	 * @since 1.7.0
	 */
	public const GLOBAL_ROBOTS = [
		'noindex'   => 'no',
		'nofollow'  => 'no',
		'noarchive' => 'no',
	];

	/**
	 * Post types to exclude from import.
	 *
	 * @since 1.7.0
	 */
	public const NOT_ALLOWED_TYPES = [
		'elementor_library',
		'product_shipping_class',
		'aioseo-location',
	];

	/**
	 * Meta keys to exclude during detection.
	 *
	 * @since 1.7.0
	 */
	public const EXCLUDED_META_KEYS = [
		'_aioseo_keywords',
	];

	/**
	 * Mapping of AIOSEO robots to SureRank robots.
	 *
	 * @since 1.7.0
	 */
	public const ROBOTS_MAPPING = [
		'noindex'   => 'post_no_index',
		'nofollow'  => 'post_no_follow',
		'noarchive' => 'post_no_archive',
	];

	/**
	 * Mapping of AIOSEO placeholders to SureRank placeholders.
	 *
	 * @since 1.7.0
	 */
	public const PLACEHOLDERS_MAPPING = [
		'#site_title'           => '%site_name%',
		'#tagline'              => '%tagline%',
		'#separator_sa'         => '-',
		'#post_title'           => '%title%',
		'#post_excerpt'         => '%excerpt%',
		'#post_excerpt_only'    => '%excerpt%',
		'#post_content'         => '%excerpt%',
		'#post_date'            => '%published%',
		'#author_name'          => '%author_name%',
		'#author_first_name'    => '%author_name%',
		'#author_last_name'     => '',
		'#author_bio'           => '%author_bio%',
		'#taxonomy_title'       => '%term_title%',
		'#taxonomy_description' => '%term_description%',
		'#category_title'       => '%term_title%',
		'#category_description' => '%term_description%',
		'#tag_title'            => '%term_title%',
		'#current_year'         => '%currentyear%',
		'#current_date'         => '%currentdate%',
		'#current_day'          => '%currentday%',
		'#current_month'        => '%currentmonth%',
		'#search_term'          => '%search_query%',
		'#archive_date'         => '%published%',
		'#page_number'          => '%page%',
	];

	/**
	 * Mapping of AIOSEO social meta to SureRank social meta.
	 *
	 * @since 1.7.0
	 */
	public const SOCIAL_MAPPING = [
		'og_title'                 => [ '', 'facebook_title' ],
		'og_description'           => [ '', 'facebook_description' ],
		'og_image_custom_url'      => [ '', 'facebook_image_url' ],
		'og_image_custom_id'       => [ '', 'facebook_image_id' ],
		'twitter_title'            => [ '', 'twitter_title' ],
		'twitter_description'      => [ '', 'twitter_description' ],
		'twitter_image_custom_url' => [ '', 'twitter_image_url' ],
		'twitter_image_custom_id'  => [ '', 'twitter_image_id' ],
	];

	/**
	 * Mapping for global title and description settings.
	 *
	 * @since 1.7.0
	 */
	private const TITLE_DESC_MAPPING = [
		'siteTitle'       => 'home_page_title',
		'metaDescription' => 'home_page_description',
	];

	/**
	 * Mapping for archive settings.
	 *
	 * @since 1.7.0
	 */
	private const ARCHIVE_SETTINGS_MAPPING = [
		'author_show' => 'author_archive',
		'date_show'   => 'date_archive',
	];

	/**
	 * Mapping for sitemap settings.
	 *
	 * @since 1.7.0
	 */
	private const SITEMAP_MAPPING = [
		'enable' => 'enable_xml_sitemap',
	];

	/**
	 * Mapping for social settings.
	 *
	 * @since 1.7.0
	 */
	private const SOCIAL_SETTINGS_MAPPING = [
		'facebookPageUrl' => 'facebook_page_url',
		'twitterUrl'      => 'twitter_profile_username',
		'instagramUrl'    => 'instagram',
		'pinterestUrl'    => 'pinterest',
		'youtubeUrl'      => 'youtube',
		'linkedinUrl'     => 'linkedin',
		'tiktokUrl'       => 'tiktok',
	];

	/**
	 * Get AIOSEO meta data from custom table for a specific post or term.
	 *
	 * @since 1.7.0
	 *
	 * @param int    $id          Post or Term ID.
	 * @param bool   $is_taxonomy Whether the ID is for a taxonomy term.
	 * @param string $type        Post type or taxonomy name.
	 * @return array<string, mixed> AIOSEO meta data.
	 */
	public static function aioseo_meta_data( int $id, bool $is_taxonomy, string $type = '' ): array {
		global $wpdb;

		$meta_data  = [];
		$table_name = $is_taxonomy ? $wpdb->prefix . 'aioseo_terms' : $wpdb->prefix . 'aioseo_posts';
		$id_column  = $is_taxonomy ? 'term_id' : 'post_id';

		$tables    = self::tables_exist();
		$table_key = $is_taxonomy ? 'terms' : 'posts';

		if ( $tables[ $table_key ] ) {
			$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE {$id_column} = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$id
				),
				ARRAY_A
			);

			if ( $row ) {
				$meta_data = $row;
			}
		}

		/* Fallback to post_meta/term_meta for localized data */
		if ( $is_taxonomy ) {
			$term_meta = get_term_meta( $id );
			if ( is_array( $term_meta ) ) {
				foreach ( $term_meta as $key => $value ) {
					if ( str_starts_with( $key, self::META_KEY_PREFIX ) && ! isset( $meta_data[ $key ] ) ) {
						$meta_data[ $key ] = is_array( $value ) && isset( $value[0] ) ? $value[0] : $value;
					}
				}
			}
		} else {
			$post_meta = get_post_meta( $id );
			if ( is_array( $post_meta ) ) {
				foreach ( $post_meta as $key => $value ) {
					if ( str_starts_with( $key, self::META_KEY_PREFIX ) && ! isset( $meta_data[ $key ] ) ) {
						$meta_data[ $key ] = is_array( $value ) && isset( $value[0] ) ? $value[0] : $value;
					}
				}
			}
		}

		$aioseo_options         = self::get_aioseo_options();
		$meta_data['separator'] = $aioseo_options['searchAppearance']['global']['separator'] ?? '-';

		return $meta_data;
	}

	/**
	 * Get AIOSEO options from database.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed> AIOSEO options.
	 */
	public static function get_aioseo_options(): array {
		$options = get_option( 'aioseo_options', '' );

		if ( empty( $options ) ) {
			return [];
		}

		$decoded = json_decode( $options, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Get AIOSEO Pro options from database.
	 *
	 * AIOSEO Pro stores Pro-specific settings (video sitemap, news sitemap, etc.)
	 * in a separate option called 'aioseo_options_pro'.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed> AIOSEO Pro options.
	 */
	public static function get_aioseo_pro_options(): array {
		$options = get_option( 'aioseo_options_pro', '' );

		if ( empty( $options ) ) {
			return [];
		}

		$decoded = json_decode( $options, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Get AIOSEO dynamic options from database.
	 *
	 * AIOSEO stores post type and taxonomy title/description templates
	 * in a separate option called 'aioseo_options_dynamic'.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed> AIOSEO dynamic options.
	 */
	public static function get_aioseo_dynamic_options(): array {
		$options = get_option( 'aioseo_options_dynamic', '' );

		if ( empty( $options ) ) {
			return [];
		}

		$decoded = json_decode( $options, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Get mapped robots settings.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $aioseo_data AIOSEO post/term data.
	 * @return array<string, string> Mapped robots.
	 */
	public static function get_mapped_robots( array $aioseo_data ): array {
		$mapped_robots = self::GLOBAL_ROBOTS;

		if ( ! empty( $aioseo_data['robots_default'] ) ) {
			return $mapped_robots;
		}

		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $robot ) {
			$key = 'robots_' . $robot;
			if ( isset( $aioseo_data[ $key ] ) ) {
				$mapped_robots[ $robot ] = ! empty( $aioseo_data[ $key ] ) ? 'yes' : 'no';
			}
		}

		return $mapped_robots;
	}

	/**
	 * Replace AIOSEO placeholders with SureRank placeholders.
	 *
	 * @since 1.7.0
	 *
	 * @param string|array<string> $value     The value containing placeholders.
	 * @param string|null          $separator Optional separator.
	 * @return string The value with placeholders replaced.
	 */
	public static function replace_placeholders( $value, ?string $separator = null ): string {
		if ( is_array( $value ) ) {
			$replaced = array_map( static fn( $item ) => self::replace_placeholders( $item, $separator ), $value );
			return implode( ', ', $replaced );
		}

		if ( ! is_string( $value ) || empty( $value ) ) {
			return '';
		}

		$placeholders = self::PLACEHOLDERS_MAPPING;

		if ( null !== $separator ) {
			$placeholders['#separator_sa'] = $separator;
		}

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $value );
	}

	/**
	 * Get page title and description from global settings.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type        The post type or taxonomy.
	 * @param bool   $is_taxonomy Whether this is a taxonomy.
	 * @return array<string, string> Title and description templates.
	 */
	public static function get_page_title_description( string $type, bool $is_taxonomy = false ): array {
		if ( empty( $type ) ) {
			return [
				'page_title'       => '',
				'page_description' => '',
			];
		}

		// Use dynamic options - AIOSEO stores post type/taxonomy templates in aioseo_options_dynamic.
		$dynamic_options   = self::get_aioseo_dynamic_options();
		$search_appearance = $dynamic_options['searchAppearance'] ?? [];

		if ( $is_taxonomy ) {
			$tax_settings = $search_appearance['taxonomies'][ $type ] ?? [];
			return [
				'page_title'       => $tax_settings['title'] ?? '',
				'page_description' => $tax_settings['metaDescription'] ?? '',
			];
		}

		$pt_settings = $search_appearance['postTypes'][ $type ] ?? [];
		return [
			'page_title'       => $pt_settings['title'] ?? '',
			'page_description' => $pt_settings['metaDescription'] ?? '',
		];
	}

	/**
	 * Check if AIOSEO custom tables exist.
	 *
	 * @since 1.7.0
	 *
	 * @return array{posts: bool, terms: bool} Table existence status.
	 */
	public static function tables_exist(): array {
		global $wpdb;

		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$posts_table = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'aioseo_posts' )
		);

		$terms_table = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'aioseo_terms' )
		);

		$cache = [
			'posts' => ! empty( $posts_table ),
			'terms' => ! empty( $terms_table ),
		];

		return $cache;
	}

	/**
	 * Get social mapping with filter support.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, array<int, string>> Social mapping array.
	 */
	public static function get_social_mapping(): array {
		return apply_filters( 'surerank_aioseo_social_mapping', self::SOCIAL_MAPPING );
	}

	/**
	 * Get title and description mapping with filter support.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string> Title and description mapping array.
	 */
	public static function get_title_desc_mapping(): array {
		return apply_filters( 'surerank_aioseo_title_desc_mapping', self::TITLE_DESC_MAPPING );
	}

	/**
	 * Get archive settings mapping with filter support.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string> Archive settings mapping array.
	 */
	public static function get_archive_settings_mapping(): array {
		return apply_filters( 'surerank_aioseo_archive_settings_mapping', self::ARCHIVE_SETTINGS_MAPPING );
	}

	/**
	 * Get sitemap mapping with filter support.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string> Sitemap mapping array.
	 */
	public static function get_sitemap_mapping(): array {
		return apply_filters( 'surerank_aioseo_sitemap_mapping', self::SITEMAP_MAPPING );
	}

	/**
	 * Get social settings mapping with filter support.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string> Social settings mapping array.
	 */
	public static function get_social_settings_mapping(): array {
		return apply_filters( 'surerank_aioseo_social_settings_mapping', self::SOCIAL_SETTINGS_MAPPING );
	}

	/**
	 * Get robots mapping with filter support.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string> Robots mapping array.
	 */
	public static function get_robots_mapping(): array {
		return apply_filters( 'surerank_aioseo_robots_mapping', self::ROBOTS_MAPPING );
	}
}
