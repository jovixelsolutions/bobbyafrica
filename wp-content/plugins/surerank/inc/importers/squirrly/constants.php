<?php
/**
 * Squirrly SEO Constants
 *
 * Defines constants and utility functions for the Squirrly SEO plugin importer.
 *
 * @package SureRank\Inc\Importers
 * @since   1.6.6
 */

namespace SureRank\Inc\Importers\Squirrly;

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
	public const PLUGIN_NAME = 'Squirrly SEO';

	/**
	 * Plugin slug used as the registry key in Migrations.
	 */
	public const PLUGIN_SLUG = 'squirrly';

	/**
	 * Squirrly main plugin file (relative to wp-content/plugins/).
	 */
	public const PLUGIN_FILE = 'squirrly-seo/squirrly.php';

	/**
	 * Custom database table name (without the WP table prefix).
	 * Squirrly stores all per-URL SEO data here instead of wp_postmeta / wp_termmeta.
	 */
	public const QSS_TABLE = 'qss';

	/**
	 * WP Options key for Squirrly global settings (JSON-encoded).
	 */
	public const OPTIONS_KEY = 'sq_options';

	/**
	 * Prefix used by Squirrly's legacy/fallback wp_postmeta keys.
	 * Only used when the qss table has no row for a given post.
	 */
	public const META_KEY_PREFIX = '_sq_';

	/**
	 * Post meta keys that act as a secondary data source when qss has no row.
	 * Maps internal field name → actual meta_key string.
	 */
	public const FALLBACK_META_KEYS = [
		'title'       => '_sq_title',
		'description' => '_sq_description',
		'keywords'    => '_sq_keywords',
	];

	/**
	 * Post / term types that must not be imported.
	 */
	public const NOT_ALLOWED_TYPES = [
		'elementor_library',
		'product_shipping_class',
	];

	/**
	 * Maps Squirrly qss.seo robot fields → SureRank meta keys.
	 */
	public const ROBOTS_MAPPING = [
		'noindex'  => 'post_no_index',
		'nofollow' => 'post_no_follow',
	];

	/**
	 * Maps Squirrly {{variable}} placeholder syntax → SureRank %variable% syntax.
	 *
	 * Squirrly uses double-brace {{ }} while SureRank (and all other supported
	 * importers) use single-percent % %. This mapping is applied to every title
	 * and description string during import.
	 */
	public const PLACEHOLDERS_MAPPING = [
		'{{title}}'            => '%title%',
		'{{sitename}}'         => '%site_name%',
		'{{sitedesc}}'         => '%tagline%',
		'{{excerpt}}'          => '%excerpt%',
		'{{date}}'             => '%published%',
		'{{modified}}'         => '%modified%',
		'{{page}}'             => '%page%',
		'{{term_title}}'       => '%term_title%',
		'{{term_description}}' => '%term_description%',
		'{{name}}'             => '%author_name%',
		'{{currentdate}}'      => '%currentdate%',
		'{{currentday}}'       => '%currentday%',
		'{{currentmonth}}'     => '%currentmonth%',
		'{{currentyear}}'      => '%currentyear%',
		'{{org_name}}'         => '%org_name%',
		'{{org_url}}'          => '%org_url%',
		'{{org_logo}}'         => '%org_logo%',
		'{{keyword}}'          => '%focus_keyword%',
	];

	/**
	 * Maps Squirrly separator codes (stored in patterns.*.sep) to actual characters.
	 */
	public const SEPARATOR_MAP = [
		'sc-dash'   => '-',
		'sc-ndash'  => '–',
		'sc-mdash'  => '—',
		'sc-middot' => '·',
		'sc-bull'   => '•',
		'sc-star'   => '*',
		'sc-pipe'   => '|',
		'sc-tilde'  => '~',
		'sc-laquo'  => '«',
		'sc-raquo'  => '»',
		'sc-lt'     => '<',
		'sc-gt'     => '>',
	];

	/**
	 * Maps sq_options.socials keys → SureRank global setting keys.
	 */
	private const SOCIAL_SETTINGS_MAPPING = [
		'twitter_site'      => 'twitter_profile_username',
		'facebook_site'     => 'facebook_page_url',
		'twitter_card_type' => 'twitter_card_type',
	];

	/**
	 * Maps sq_options top-level keys → SureRank global setting keys.
	 * Boolean flags are normalised to 1 / 0.
	 */
	private const GLOBAL_SETTINGS_MAPPING = [
		'sq_og_image'      => 'fallback_image',
		'sq_auto_sitemap'  => 'enable_xml_sitemap',
		'sq_auto_facebook' => 'open_graph_tags',
		'sq_auto_twitter'  => 'twitter_meta_tags',
	];

	// -------------------------------------------------------------------------
	// Data retrieval
	// -------------------------------------------------------------------------

	/**
	 * Get SEO data for a post or term from the qss table.
	 *
	 * Primary source: {prefix}qss looked up by the exact hash formula Squirrly
	 * uses when it records a page visit (see models/Frontend.php::getPostDetails).
	 * Fallback (posts only): wp_postmeta keys _sq_title / _sq_description / _sq_keywords.
	 *
	 * Hash formula (derived from Squirrly Frontend.php):
	 *  Posts  – standard types (post/page/product/attachment/cartflows_step): md5( post_ID )
	 *         – all other CPTs:                                               md5( post_type . post_ID )
	 *  Terms  – post_tag:                   md5( 'tag'      . term_id )
	 *         – category:                   md5( 'category' . term_id )
	 *         – any other taxonomy (e.g. product_cat): md5( 'tax-' . taxonomy . term_id )
	 *
	 * @param int  $id          Post ID or term ID.
	 * @param bool $is_taxonomy Whether the ID refers to a taxonomy term.
	 * @return array<string, mixed> Normalised SEO data array, empty array when nothing found.
	 */
	public static function get_seo_data( int $id, bool $is_taxonomy = false ): array {
		global $wpdb;

		// Guard: verify the qss table exists before querying.
		if ( ! self::table_exists() ) {
			return $is_taxonomy ? [] : self::get_postmeta_fallback( $id );
		}

		// Compute the hash Squirrly stored for this entity.
		if ( $is_taxonomy ) {
			$term = get_term( $id );
			if ( ! $term || is_wp_error( $term ) ) {
				return [];
			}
			$taxonomy = $term->taxonomy;
			if ( 'post_tag' === $taxonomy ) {
				$hash_input = 'tag' . (string) $id;
			} elseif ( 'category' === $taxonomy ) {
				$hash_input = 'category' . (string) $id;
			} else {
				// Custom taxonomies: Squirrly sets post_type = 'tax-{taxonomy}'.
				$hash_input = 'tax-' . $taxonomy . (string) $id;
			}
		} else {
			$post_type = get_post_type( $id );
			if ( ! $post_type ) {
				return [];
			}
			// Standard post types use md5( post_ID ) only; everything else prepends the type.
			$standard_types = [ 'post', 'page', 'product', 'attachment', 'cartflows_step' ];
			if ( in_array( $post_type, $standard_types, true ) ) {
				$hash_input = (string) $id;
			} else {
				$hash_input = $post_type . (string) $id;
			}
		}

		$url_hash = md5( $hash_input );
		$blog_id  = get_current_blog_id();
		$table    = $wpdb->prefix . self::QSS_TABLE;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT seo FROM `{$table}` WHERE blog_id = %d AND url_hash = %s",
				$blog_id,
				$url_hash
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! $row || empty( $row->seo ) ) {
			return $is_taxonomy ? [] : self::get_postmeta_fallback( $id );
		}

		return self::parse_seo_column( $row->seo );
	}

	/**
	 * Parse a serialised qss.seo column value into a plain PHP array.
	 *
	 * When Squirrly is active, maybe_unserialize() returns a SQ_Models_Domain_Sq
	 * object. That class extends SQ_Models_Abstract_Domain which exposes all
	 * protected $_property fields via a magic __get($name) → get{Name}() chain.
	 * We therefore access each field by its unprefixed name (e.g. $obj->og_title)
	 * rather than relying on toArray(), because toArray() calls current() on any
	 * array-type property and thus discards all but the first element.
	 *
	 * @param string $raw Raw serialised value from the seo column.
	 * @return array<string, mixed>
	 */
	public static function parse_seo_column( string $raw ): array {
		$seo = maybe_unserialize( $raw );

		if ( ! $seo ) {
			return [];
		}

		// Squirrly stores the seo column via toArray() → maybe_serialize(), which always
		// produces a plain PHP associative array.  The object branch below is kept as a
		// safety fallback for hypothetical future format changes.
		if ( is_object( $seo ) && ! ( $seo instanceof \__PHP_Incomplete_Class ) ) {
			return [
				'title'          => (string) ( $seo->title ?? '' ),
				'description'    => (string) ( $seo->description ?? '' ),
				'keywords'       => (string) ( $seo->keywords ?? '' ),
				'canonical'      => (string) ( $seo->canonical ?? '' ),
				'noindex'        => (int) ( $seo->noindex ?? 0 ),
				'nofollow'       => (int) ( $seo->nofollow ?? 0 ),
				'og_title'       => (string) ( $seo->og_title ?? '' ),
				'og_description' => (string) ( $seo->og_description ?? '' ),
				'og_media'       => (string) ( $seo->og_media ?? '' ),
				'tw_title'       => (string) ( $seo->tw_title ?? '' ),
				'tw_description' => (string) ( $seo->tw_description ?? '' ),
				'tw_media'       => (string) ( $seo->tw_media ?? '' ),
				'tw_type'        => (string) ( $seo->tw_type ?? '' ),
			];
		}

		// Normal path: plain associative array from SQ_Models_Domain_Sq::toArray().
		// og_media / tw_media are plain URL strings per the Squirrly source.
		if ( is_array( $seo ) ) {
			return [
				'title'          => (string) ( $seo['title'] ?? '' ),
				'description'    => (string) ( $seo['description'] ?? '' ),
				'keywords'       => (string) ( $seo['keywords'] ?? '' ),
				'canonical'      => (string) ( $seo['canonical'] ?? '' ),
				'noindex'        => (int) ( $seo['noindex'] ?? 0 ),
				'nofollow'       => (int) ( $seo['nofollow'] ?? 0 ),
				'og_title'       => (string) ( $seo['og_title'] ?? '' ),
				'og_description' => (string) ( $seo['og_description'] ?? '' ),
				'og_media'       => (string) ( $seo['og_media'] ?? '' ),
				'tw_title'       => (string) ( $seo['tw_title'] ?? '' ),
				'tw_description' => (string) ( $seo['tw_description'] ?? '' ),
				'tw_media'       => (string) ( $seo['tw_media'] ?? '' ),
				'tw_type'        => (string) ( $seo['tw_type'] ?? '' ),
			];
		}

		return [];
	}

	/**
	 * Read SEO data from wp_postmeta fallback keys (_sq_title, _sq_description, _sq_keywords).
	 *
	 * This path is taken when a post has no row in the qss table — typically posts
	 * that were created before Squirrly's qss table existed or that only had
	 * the title/description snippet saved manually.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	public static function get_postmeta_fallback( int $post_id ): array {
		$data = [];

		foreach ( self::FALLBACK_META_KEYS as $field => $meta_key ) {
			$value = get_post_meta( $post_id, $meta_key, true );
			if ( ! empty( $value ) ) {
				$data[ $field ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Return the decoded sq_options array.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_sq_options(): array {
		$raw = get_option( self::OPTIONS_KEY, '' );
		if ( empty( $raw ) ) {
			return [];
		}
		$decoded = json_decode( $raw, true );
		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Replace Squirrly {{variable}} placeholders with SureRank %variable% equivalents.
	 *
	 * Squirrly uses {{double_brace}} syntax; all other supported importers use
	 * %%double_percent%% or %single_percent%. A str_replace pass is sufficient
	 * because the delimiters are unambiguous.
	 *
	 * @param string|array<string> $value     Value containing placeholders to replace.
	 * @param string|null          $separator Resolved separator character for {{sep}}.
	 * @return string
	 */
	public static function replace_placeholders( $value, ?string $separator = null ): string {
		if ( is_array( $value ) ) {
			$replaced = array_map( static fn( $item ) => self::replace_placeholders( $item, $separator ), $value );
			return implode( ', ', $replaced );
		}

		if ( ! is_string( $value ) ) {
			return (string) $value;
		}

		$placeholders            = self::PLACEHOLDERS_MAPPING;
		$placeholders['{{sep}}'] = $separator ?? '-';

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $value );
	}

	/**
	 * Check whether the {prefix}qss table exists in the current database.
	 *
	 * Called before every qss query so the importer degrades gracefully if the
	 * table has been dropped (e.g. after Squirrly uninstall). Result is memoized
	 * per blog_id for the lifetime of the request to avoid redundant SHOW TABLES
	 * queries on large paginated migrations.
	 *
	 * @return bool
	 */
	public static function table_exists(): bool {
		static $cache = [];

		global $wpdb;

		$blog_id = get_current_blog_id();

		if ( ! array_key_exists( $blog_id, $cache ) ) {
			$table = $wpdb->prefix . self::QSS_TABLE;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$cache[ $blog_id ] = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->esc_like( $table ) . "'" ) === $table;
		}

		return $cache[ $blog_id ];
	}

	// -------------------------------------------------------------------------
	// Batch ID extraction (used by get_count_and_posts / get_count_and_terms)
	// -------------------------------------------------------------------------

	/**
	 * Fetch all post-related entries from the qss table for the current blog.
	 *
	 * Each row's `post` column is a serialised SQ_Models_Domain_Post. Entries
	 * that have an empty taxonomy are posts/pages (not terms).
	 *
	 * @return array<int, array{id: int, post_type: string}>
	 */
	public static function get_qss_post_entries(): array {
		$rows = self::fetch_qss_post_column();
		if ( empty( $rows ) ) {
			return [];
		}

		$entries = [];
		foreach ( $rows as $row ) {
			if ( empty( $row->post ) ) {
				continue;
			}
			$post_data = maybe_unserialize( $row->post );
			if ( ! $post_data ) {
				continue;
			}

			[ $id, $post_type, , $taxonomy ] = self::extract_qss_post_fields( $post_data );

			// Entries with an empty taxonomy belong to posts / pages.
			if ( $id > 0 && '' === $taxonomy ) {
				$entries[] = [
					'id'        => $id,
					'post_type' => $post_type,
				];
			}
		}

		return $entries;
	}

	/**
	 * Fetch all term-related entries from the qss table for the current blog.
	 *
	 * Entries that have a non-empty taxonomy (and term_id > 0) are taxonomy terms.
	 *
	 * @return array<int, array{term_id: int, taxonomy: string}>
	 */
	public static function get_qss_term_entries(): array {
		$rows = self::fetch_qss_post_column();
		if ( empty( $rows ) ) {
			return [];
		}

		$entries = [];
		foreach ( $rows as $row ) {
			if ( empty( $row->post ) ) {
				continue;
			}
			$post_data = maybe_unserialize( $row->post );
			if ( ! $post_data ) {
				continue;
			}

			[ , , $term_id, $taxonomy ] = self::extract_qss_post_fields( $post_data );

			// Entries with a non-empty taxonomy are terms.
			if ( $term_id > 0 && '' !== $taxonomy ) {
				$entries[] = [
					'term_id'  => $term_id,
					'taxonomy' => $taxonomy,
				];
			}
		}

		return $entries;
	}

	// -------------------------------------------------------------------------
	// Filtered mapping getters (allow customisation via WordPress filters)
	// -------------------------------------------------------------------------

	/**
	 * Get the social settings mapping, allowing customisation via filter.
	 *
	 * @return array<string, string>
	 */
	public static function get_social_settings_mapping(): array {
		return apply_filters( 'surerank_squirrly_social_settings_mapping', self::SOCIAL_SETTINGS_MAPPING );
	}

	/**
	 * Get the global settings mapping, allowing customisation via filter.
	 *
	 * @return array<string, string>
	 */
	public static function get_global_settings_mapping(): array {
		return apply_filters( 'surerank_squirrly_global_settings_mapping', self::GLOBAL_SETTINGS_MAPPING );
	}

	/**
	 * Extract the four key fields from a deserialised qss.post value.
	 *
	 * Handles both the object form (SQ_Models_Domain_Post, accessed via magic
	 * __get) and a plain array (older serialised format).
	 *
	 * @param object|array<string, mixed> $post_data Deserialised qss.post value.
	 * @return array{int, string, int, string} [post_id, post_type, term_id, taxonomy]
	 */
	public static function extract_qss_post_fields( $post_data ): array {
		if ( is_object( $post_data ) && ! ( $post_data instanceof \__PHP_Incomplete_Class ) ) {
			$id       = isset( $post_data->ID ) ? (int) $post_data->ID : (int) ( $post_data->id ?? 0 );
			$type     = (string) ( $post_data->post_type ?? '' );
			$term_id  = (int) ( $post_data->term_id ?? 0 );
			$taxonomy = (string) ( $post_data->taxonomy ?? '' );
		} elseif ( is_array( $post_data ) ) {
			$id       = isset( $post_data['ID'] ) ? (int) $post_data['ID'] : (int) ( $post_data['id'] ?? 0 );
			$type     = (string) ( $post_data['post_type'] ?? '' );
			$term_id  = (int) ( $post_data['term_id'] ?? 0 );
			$taxonomy = (string) ( $post_data['taxonomy'] ?? '' );
		} else {
			return [ 0, '', 0, '' ];
		}

		return [ $id, $type, $term_id, $taxonomy ];
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Execute a single query to retrieve every `post` column value from qss for
	 * the current blog. Result is memoized per blog_id for the duration of the
	 * request so that get_qss_post_entries() and get_qss_term_entries() can both
	 * call this without issuing a duplicate DB query. The cache is safe because
	 * the migration process only reads from qss — it never modifies its rows.
	 *
	 * @return array<object>|null
	 */
	private static function fetch_qss_post_column(): ?array {
		static $cache = [];

		global $wpdb;

		$blog_id = get_current_blog_id();

		if ( array_key_exists( $blog_id, $cache ) ) {
			return $cache[ $blog_id ];
		}

		if ( ! self::table_exists() ) {
			$cache[ $blog_id ] = null;
			return null;
		}

		$table = $wpdb->prefix . self::QSS_TABLE;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT post FROM `{$table}` WHERE blog_id = %d",
				$blog_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$cache[ $blog_id ] = $result;

		return $result;
	}
}
